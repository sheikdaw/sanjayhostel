<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * ============================================================
     * LIST PAYMENTS
     * ============================================================
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Scope hostels by role (same pattern as AdminController)
        if ($user->role === 'admin') {
            $hostelIds = \App\Models\Hostel::where('status', 'ACTIVE')->pluck('id')->toArray();
        } else {
            $hostelIds = $user->hostel_ids ?? [0];
        }

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });

        // Optional filters
        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            // expects "YYYY-MM"
            $start = \Carbon\Carbon::parse($request->month . '-01')->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $query->whereBetween('payment_date', [$start, $end]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhereHas('resident', function ($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%")
                         ->orWhere('resident_code', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20)->withQueryString();

        return view('main.admin.payments.index', compact('payments'));
    }

    /**
     * ============================================================
     * SHOW CREATE FORM
     * ============================================================
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostelIds = \App\Models\Hostel::where('status', 'ACTIVE')->pluck('id')->toArray();
        } else {
            $hostelIds = $user->hostel_ids ?? [0];
        }

        $residents = Resident::whereIn('hostel_id', $hostelIds)
            ->where('status', 'ACTIVE')
            ->with('room')
            ->orderBy('name')
            ->get();

        $selectedResident = null;
        if ($request->filled('resident_id')) {
            $selectedResident = $residents->firstWhere('id', $request->resident_id);
        }

        return view('main.admin.payments.create', compact('residents', 'selectedResident'));
    }

    /**
     * ============================================================
     * STORE NEW PAYMENT  (this is where balance_amount gets fixed)
     * ============================================================
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'resident_id'        => ['required', 'exists:residents,id'],
            'payment_date'       => ['required', 'date'],
            'cash_paid_amount'   => ['nullable', 'numeric', 'min:0'],
            'upi_paid_amount'    => ['nullable', 'numeric', 'min:0'],
            'remarks'            => ['nullable', 'string', 'max:500'],
        ])->validate();

        $resident = Resident::with('room')->findOrFail($validated['resident_id']);

        $rentAmount     = (float) ($resident->room->rent_amount ?? 0);
        $cashPaid       = (float) ($validated['cash_paid_amount'] ?? 0);
        $upiPaid        = (float) ($validated['upi_paid_amount'] ?? 0);
        $totalPaidNow   = $cashPaid + $upiPaid;

        // --- Look up whether a payment already exists for this resident THIS MONTH ---
        $monthStart = \Carbon\Carbon::parse($validated['payment_date'])->startOfMonth();
        $monthEnd   = \Carbon\Carbon::parse($validated['payment_date'])->endOfMonth();

        $existing = Payment::where('resident_id', $resident->id)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->first();

        // Total already paid this month (if adding to an existing partial payment)
        $alreadyPaid = $existing
            ? ((float) $existing->cash_paid_amount + (float) $existing->upi_paid_amount)
            : 0;

        $totalPaidThisMonth = $alreadyPaid + $totalPaidNow;

        // --- The single source of truth for balance & status ---
        $balanceAmount = max($rentAmount - $totalPaidThisMonth, 0);
        $status        = $this->resolveStatus($rentAmount, $totalPaidThisMonth);

        DB::beginTransaction();
        try {
            if ($existing) {
                // Update existing record for the month (accumulate payment)
                $existing->cash_paid_amount = (float) $existing->cash_paid_amount + $cashPaid;
                $existing->upi_paid_amount  = (float) $existing->upi_paid_amount + $upiPaid;
                $existing->rent_amount      = $rentAmount;
                $existing->balance_amount   = $balanceAmount;
                $existing->status           = $status;
                $existing->payment_date     = $validated['payment_date'];
                $existing->remarks          = $validated['remarks'] ?? $existing->remarks;
                $existing->save();

                $payment = $existing;
            } else {
                $payment = Payment::create([
                    'resident_id'      => $resident->id,
                    'receipt_no'       => $this->generateReceiptNo(),
                    'payment_date'     => $validated['payment_date'],
                    'rent_amount'      => $rentAmount,
                    'cash_paid_amount' => $cashPaid,
                    'upi_paid_amount'  => $upiPaid,
                    'balance_amount'   => $balanceAmount,
                    'status'           => $status,
                    'remarks'          => $validated['remarks'] ?? null,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save payment: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.payments.index')
            ->with('success', "Payment recorded for {$resident->name}. Balance: ₹" . number_format($balanceAmount, 2));
    }

    /**
     * ============================================================
     * SHOW EDIT FORM
     * ============================================================
     */
    public function edit(Payment $payment)
    {
        $payment->load(['resident', 'resident.room', 'resident.hostel']);
        return view('main.admin.payments.edit', compact('payment'));
    }

    /**
     * ============================================================
     * UPDATE PAYMENT (also recalculates balance_amount)
     * ============================================================
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = Validator::make($request->all(), [
            'payment_date'      => ['required', 'date'],
            'cash_paid_amount'  => ['nullable', 'numeric', 'min:0'],
            'upi_paid_amount'   => ['nullable', 'numeric', 'min:0'],
            'remarks'           => ['nullable', 'string', 'max:500'],
        ])->validate();

        $resident   = $payment->resident()->with('room')->first();
        $rentAmount = (float) ($resident->room->rent_amount ?? $payment->rent_amount ?? 0);

        $cashPaid = (float) ($validated['cash_paid_amount'] ?? 0);
        $upiPaid  = (float) ($validated['upi_paid_amount'] ?? 0);
        $totalPaid = $cashPaid + $upiPaid;

        $balanceAmount = max($rentAmount - $totalPaid, 0);
        $status        = $this->resolveStatus($rentAmount, $totalPaid);

        $payment->update([
            'payment_date'     => $validated['payment_date'],
            'rent_amount'      => $rentAmount,
            'cash_paid_amount' => $cashPaid,
            'upi_paid_amount'  => $upiPaid,
            'balance_amount'   => $balanceAmount,
            'status'           => $status,
            'remarks'          => $validated['remarks'] ?? $payment->remarks,
        ]);

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment updated. Balance: ₹' . number_format($balanceAmount, 2));
    }

    /**
     * ============================================================
     * DELETE PAYMENT
     * ============================================================
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment record deleted.');
    }

    /**
     * ============================================================
     * SHOW SINGLE PAYMENT / RECEIPT
     * ============================================================
     */
    public function show(Payment $payment)
    {
        $payment->load(['resident', 'resident.room', 'resident.hostel']);
        return view('main.admin.payments.show', compact('payment'));
    }

    /**
     * ============================================================
     * HELPERS
     * ============================================================
     */

    /**
     * Single, consistent place that decides PAID / PARTIAL / PENDING.
     * Never duplicate this logic elsewhere — always call this method.
     */
    private function resolveStatus(float $rentAmount, float $totalPaid): string
    {
        if ($rentAmount <= 0) {
            return 'PAID'; // nothing owed, e.g. free/comped resident
        }

        if ($totalPaid <= 0) {
            return 'PENDING';
        }

        if ($totalPaid >= $rentAmount) {
            return 'PAID';
        }

        return 'PARTIAL';
    }

    private function generateReceiptNo(): string
    {
        $prefix = 'RCPT-' . now()->format('Ym') . '-';
        $last = Payment::where('receipt_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->receipt_no);
            $nextNumber = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}