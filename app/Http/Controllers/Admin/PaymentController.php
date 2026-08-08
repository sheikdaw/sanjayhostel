<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        // Get all active residents for dropdown
        if ($user->role === 'admin') {
            $residents = Resident::with(['hostel', 'room'])
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $residents = Resident::with(['hostel', 'room'])
                ->whereIn('hostel_id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        }

        // Get payments based on user role with filters
        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        // Apply filters from request
        if (request()->status) {
            $query->where('status', request()->status);
        }
        if (request()->month) {
            $query->where('month', request()->month);
        }
        if (request()->year) {
            $query->where('year', request()->year);
        }
        if (request()->hostel_id) {
            $query->whereHas('resident', function($q) {
                $q->where('hostel_id', request()->hostel_id);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Get statistics
        $stats = [
            'total' => $payments->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'paid' => $payments->where('status', 'PAID')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_discount' => $payments->sum('discount_amount'),
            'total_fine' => $payments->sum('fine_amount'),
            'total_cash' => $payments->sum('cash_paid_amount'),
            'total_upi' => $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount')
        ];

        // Get current month pending payments
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $pendingPayments = Payment::with(['resident', 'resident.hostel'])
            ->where('status', 'PENDING')
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $pendingPayments = $pendingPayments->filter(function($payment) use ($hostelIds) {
                return in_array($payment->resident->hostel_id, $hostelIds);
            });
        }

        // Get monthly summary
        $monthlySummary = Payment::selectRaw('month, year, COUNT(*) as count, SUM(rent_amount) as total_rent, SUM(balance_amount) as total_balance, SUM(cash_paid_amount + upi_paid_amount) as total_collected')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Get hostel-wise summary
        $hostelSummary = Payment::with('resident.hostel')
            ->get()
            ->groupBy('resident.hostel_id')
            ->map(function($group) {
                return [
                    'hostel_name' => $group->first()->resident->hostel->hostel_name ?? 'N/A',
                    'total_count' => $group->count(),
                    'total_rent' => $group->sum('rent_amount'),
                    'total_collected' => $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount'),
                    'total_balance' => $group->sum('balance_amount'),
                    'paid_count' => $group->where('status', 'PAID')->count(),
                    'pending_count' => $group->where('status', 'PENDING')->count(),
                    'partial_count' => $group->where('status', 'PARTIAL')->count()
                ];
            });

        return view('admin.payments.index', compact(
            'payments',
            'hostels',
            'stats',
            'pendingPayments',
            'monthlySummary',
            'residents',
            'hostelSummary',
            'user'
        ));
    }

    /**
     * Get residents by room for cascading dropdown
     */
    public function getResidentsByRoom($roomId)
    {
        $user = auth()->user();

        $query = Resident::where('room_id', $roomId)
            ->where('status', 'ACTIVE')
            ->orderBy('name');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        $residents = $query->get(['id', 'name', 'resident_code', 'hostel_id', 'room_id', 'rent_amount']);

        return response()->json([
            'success' => true,
            'data' => $residents
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $resident = Resident::find($request->resident_id);
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found!'
            ], 404);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add payments for this resident!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'rent_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'required|numeric|min:0',
            'upi_paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:PAID,PARTIAL,PENDING'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if previous months have pending payments
        $hasPendingPrevious = Payment::where('resident_id', $request->resident_id)
            ->where(function($q) use ($request) {
                $q->where('year', '<', $request->year)
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('year', $request->year)
                         ->where('month', '<', $request->month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->exists();

        if ($hasPendingPrevious) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add payment! Previous months have pending payments. Please clear them first.'
            ], 422);
        }

        $totalPaid = $request->cash_paid_amount + $request->upi_paid_amount;
        $totalAmount = $request->rent_amount - ($request->discount_amount ?? 0) + ($request->fine_amount ?? 0);
        $balanceAmount = $totalAmount - $totalPaid;

        $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        while (Payment::where('receipt_no', $receiptNo)->exists()) {
            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        }

        $payment = Payment::create([
            'resident_id' => $request->resident_id,
            'receipt_no' => $receiptNo,
            'month' => $request->month,
            'year' => $request->year,
            'rent_amount' => $request->rent_amount,
            'discount_amount' => $request->discount_amount ?? 0,
            'fine_amount' => $request->fine_amount ?? 0,
            'cash_paid_amount' => $request->cash_paid_amount,
            'upi_paid_amount' => $request->upi_paid_amount,
            'balance_amount' => $balanceAmount,
            'payment_date' => $request->payment_date,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);

        $payment->load(['resident.hostel', 'resident.room']);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully! Receipt: ' . $receiptNo,
            'data' => $payment
        ]);
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit($id)
    {
        $user = auth()->user();
        $payment = Payment::with(['resident', 'resident.hostel', 'resident.room'])->findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this payment!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this payment!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'rent_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'required|numeric|min:0',
            'upi_paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:PAID,PARTIAL,PENDING'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $totalPaid = $request->cash_paid_amount + $request->upi_paid_amount;
        $totalAmount = $request->rent_amount - ($request->discount_amount ?? 0) + ($request->fine_amount ?? 0);
        $balanceAmount = $totalAmount - $totalPaid;

        $payment->update([
            'month' => $request->month,
            'year' => $request->year,
            'rent_amount' => $request->rent_amount,
            'discount_amount' => $request->discount_amount ?? 0,
            'fine_amount' => $request->fine_amount ?? 0,
            'cash_paid_amount' => $request->cash_paid_amount,
            'upi_paid_amount' => $request->upi_paid_amount,
            'balance_amount' => $balanceAmount,
            'payment_date' => $request->payment_date,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);

        $payment->load(['resident.hostel', 'resident.room']);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully!',
            'data' => $payment
        ]);
    }

    /**
     * Remove the specified payment.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this payment!'
                ], 403);
            }
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully!'
        ]);
    }

    /**
     * Get resident's rent amount.
     */
    public function getResidentRent($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        return response()->json([
            'success' => true,
            'data' => [
                'rent_amount' => $resident->rent_amount ?? 0,
                'name' => $resident->name,
                'resident_code' => $resident->resident_code
            ]
        ]);
    }

    /**
     * Check if resident has pending payments from previous months.
     */
    public function checkPreviousPending($residentId, $month, $year)
    {
        $hasPendingPrevious = Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)
                         ->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->exists();

        return response()->json([
            'success' => true,
            'has_pending' => $hasPendingPrevious
        ]);
    }

    /**
     * Get all payments for a specific resident.
     */
    public function getResidentPayments($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $payments = $resident->payments()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $summary = [
            'total_paid' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'pending_count' => $payments->where('status', 'PENDING')->count(),
            'partial_count' => $payments->where('status', 'PARTIAL')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => $summary
        ]);
    }

    /**
     * Get resident's total due amount.
     */
    public function getResidentDue($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $pendingPayments = $resident->payments()
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalDue = $pendingPayments->sum('balance_amount');
        $pendingCount = $pendingPayments->count();

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $residentId,
                'resident_name' => $resident->name,
                'total_due' => $totalDue,
                'pending_count' => $pendingCount,
                'payments' => $pendingPayments
            ]
        ]);
    }

    /**
     * Mark a payment as paid.
     */
    public function markAsPaid($id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this payment!'
                ], 403);
            }
        }

        $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;

        $payment->update([
            'cash_paid_amount' => $totalAmount - $payment->upi_paid_amount,
            'balance_amount' => 0,
            'status' => 'PAID'
        ]);

        $payment->load(['resident.hostel', 'resident.room']);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid successfully!',
            'data' => $payment
        ]);
    }

    /**
     * Bulk create payments for multiple residents.
     */
    public function bulkPayment(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'resident_ids' => 'required|array',
            'resident_ids.*' => 'exists:residents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'payment_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $created = [];
        $errors = [];

        foreach ($request->resident_ids as $residentId) {
            $resident = Resident::find($residentId);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($resident->hostel_id, $hostelIds)) {
                    $errors[] = "No permission for resident: {$resident->name}";
                    continue;
                }
            }

            $exists = Payment::where('resident_id', $residentId)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                $errors[] = "Payment already exists for " . $resident->name;
                continue;
            }

            $hasPendingPrevious = Payment::where('resident_id', $residentId)
                ->where(function($q) use ($request) {
                    $q->where('year', '<', $request->year)
                      ->orWhere(function($q2) use ($request) {
                          $q2->where('year', $request->year)
                             ->where('month', '<', $request->month);
                      });
                })
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->exists();

            if ($hasPendingPrevious) {
                $errors[] = "Previous months pending for " . $resident->name;
                continue;
            }

            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            $payment = Payment::create([
                'resident_id' => $residentId,
                'receipt_no' => $receiptNo,
                'month' => $request->month,
                'year' => $request->year,
                'rent_amount' => $resident->rent_amount ?? 0,
                'discount_amount' => 0,
                'fine_amount' => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount' => 0,
                'balance_amount' => $resident->rent_amount ?? 0,
                'payment_date' => $request->payment_date,
                'status' => 'PENDING'
            ]);

            $created[] = $payment;
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' payments created. ' . count($errors) . ' errors.',
            'data' => $created,
            'errors' => $errors
        ]);
    }

    /**
     * Get monthly summary.
     */
    public function getMonthlySummary()
    {
        $summary = Payment::selectRaw('
            month,
            year,
            COUNT(*) as total_count,
            SUM(rent_amount) as total_rent,
            SUM(discount_amount) as total_discount,
            SUM(fine_amount) as total_fine,
            SUM(cash_paid_amount) as total_cash,
            SUM(upi_paid_amount) as total_upi,
            SUM(balance_amount) as total_balance,
            SUM(cash_paid_amount + upi_paid_amount) as total_collected
        ')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Bulk update payment status.
     */
    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id',
            'status' => 'required|in:PAID,PARTIAL,PENDING'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();
        
        // Check permissions
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to update some payments!'
                    ], 403);
                }
            }
        }

        $updated = 0;
        foreach ($payments as $payment) {
            if ($request->status === 'PAID') {
                $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
                $payment->cash_paid_amount = $totalAmount - $payment->upi_paid_amount;
                $payment->balance_amount = 0;
            } elseif ($request->status === 'PENDING') {
                $payment->cash_paid_amount = 0;
                $payment->upi_paid_amount = 0;
                $payment->balance_amount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
            }
            $payment->status = $request->status;
            $payment->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => $updated . ' payments updated to ' . $request->status
        ]);
    }

    /**
     * Bulk delete payments.
     */
    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();
        
        // Check permissions
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to delete some payments!'
                    ], 403);
                }
            }
        }

        $deleted = Payment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' payments deleted successfully!'
        ]);
    }

    // ============================================================
    // HELPER METHODS FOR CSV EXPORTS
    // ============================================================

    /**
     * Format numbers for CSV without thousands separator.
     */
    private function csvNumber($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Clean string for CSV (remove commas and quotes).
     */
    private function csvString($value): string
    {
        if (is_null($value)) return '';
        $value = str_replace(',', ';', $value);
        $value = str_replace('"', '', $value);
        return $value;
    }

    /**
     * Apply filters to export queries.
     */
    private function applyExportFilters($query, $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }
        return $query;
    }

    // ============================================================
    // EXPORT METHODS - COMPLETE WITH ALL DETAILS
    // ============================================================

    /**
     * EXPORT COMPLETE PAYMENT REPORT WITH ALL DETAILS
     * Shows: Total Residents, Total Resident Amount, Total Paid, Total Balance
     */
    public function exportCompleteReport(Request $request)
    {
        $user = auth()->user();

        // Get all hostels based on role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

        $csv = "==================================================\n";
        $csv .= "COMPLETE PAYMENT REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "User: " . $user->name . " (" . $user->role . ")\n";
        $csv .= "==================================================\n\n";

        // ============================================================
        // GRAND SUMMARY - All Hostels Combined
        // ============================================================
        $csv .= "--- GRAND SUMMARY (ALL HOSTELS) ---\n";
        $csv .= "==================================================\n";

        // Get all residents based on role
        if ($user->role === 'admin') {
            $allResidents = Resident::where('status', 'ACTIVE')->get();
            $allPayments = Payment::with(['resident'])->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $allResidents = Resident::whereIn('hostel_id', $hostelIds)->where('status', 'ACTIVE')->get();
            $allPayments = Payment::whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            })->get();
        }

        $totalResidents = $allResidents->count();
        $totalResidentRent = $allResidents->sum('rent_amount');
        $totalPayments = $allPayments->count();
        $totalRentAmount = $allPayments->sum('rent_amount');
        $totalCollected = $allPayments->sum('cash_paid_amount') + $allPayments->sum('upi_paid_amount');
        $totalBalance = $allPayments->sum('balance_amount');
        $totalDiscount = $allPayments->sum('discount_amount');
        $totalFine = $allPayments->sum('fine_amount');
        $totalCash = $allPayments->sum('cash_paid_amount');
        $totalUPI = $allPayments->sum('upi_paid_amount');
        $paidCount = $allPayments->where('status', 'PAID')->count();
        $pendingCount = $allPayments->where('status', 'PENDING')->count();
        $partialCount = $allPayments->where('status', 'PARTIAL')->count();

        $csv .= "Total Residents: " . $totalResidents . "\n";
        $csv .= "Total Resident Rent Amount: ₹" . $this->csvNumber($totalResidentRent) . "\n";
        $csv .= "--------------------------------------------------\n";
        $csv .= "Total Payments: " . $totalPayments . "\n";
        $csv .= "Total Rent Amount (from payments): ₹" . $this->csvNumber($totalRentAmount) . "\n";
        $csv .= "Total Discount: ₹" . $this->csvNumber($totalDiscount) . "\n";
        $csv .= "Total Fine: ₹" . $this->csvNumber($totalFine) . "\n";
        $csv .= "--------------------------------------------------\n";
        $csv .= "Total Cash Paid: ₹" . $this->csvNumber($totalCash) . "\n";
        $csv .= "Total UPI Paid: ₹" . $this->csvNumber($totalUPI) . "\n";
        $csv .= "Total Amount Collected: ₹" . $this->csvNumber($totalCollected) . "\n";
        $csv .= "Total Balance Due: ₹" . $this->csvNumber($totalBalance) . "\n";
        $csv .= "--------------------------------------------------\n";
        $csv .= "Paid Payments: " . $paidCount . "\n";
        $csv .= "Pending Payments: " . $pendingCount . "\n";
        $csv .= "Partial Payments: " . $partialCount . "\n";
        $csv .= "--------------------------------------------------\n";
        $collectionPercentage = $totalRentAmount > 0 ? round(($totalCollected / $totalRentAmount) * 100, 1) : 0;
        $csv .= "Collection Efficiency: " . $collectionPercentage . "%\n";
        $csv .= "==================================================\n\n";

        // ============================================================
        // HOSTEL-WISE SUMMARY
        // ============================================================
        $csv .= "--- HOSTEL-WISE SUMMARY ---\n";
        $csv .= "Hostel,Total Residents,Total Resident Rent (₹),Total Payments,Total Rent (₹),Total Collected (₹),Total Balance (₹),Paid Count,Pending Count,Partial Count,Collection %\n";

        $grandTotal = [
            'residents' => 0,
            'resident_rent' => 0,
            'payments' => 0,
            'rent' => 0,
            'collected' => 0,
            'balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0
        ];

        foreach ($hostels as $hostel) {
            // Get residents for this hostel
            $residents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->get();
            $residentCount = $residents->count();
            $residentRentTotal = $residents->sum('rent_amount');

            // Get payments for this hostel
            $payments = Payment::whereHas('resident', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->get();

            $totalRent = $payments->sum('rent_amount');
            $totalCollected = $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount');
            $totalBalance = $payments->sum('balance_amount');
            $paidCount = $payments->where('status', 'PAID')->count();
            $pendingCount = $payments->where('status', 'PENDING')->count();
            $partialCount = $payments->where('status', 'PARTIAL')->count();
            
            $collectionPercentage = $totalRent > 0 ? round(($totalCollected / $totalRent) * 100, 1) : 0;

            $csv .= $this->csvString($hostel->hostel_name) . ",";
            $csv .= $residentCount . ",";
            $csv .= $this->csvNumber($residentRentTotal) . ",";
            $csv .= $payments->count() . ",";
            $csv .= $this->csvNumber($totalRent) . ",";
            $csv .= $this->csvNumber($totalCollected) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paidCount . ",";
            $csv .= $pendingCount . ",";
            $csv .= $partialCount . ",";
            $csv .= $collectionPercentage . "%\n";

            $grandTotal['residents'] += $residentCount;
            $grandTotal['resident_rent'] += $residentRentTotal;
            $grandTotal['payments'] += $payments->count();
            $grandTotal['rent'] += $totalRent;
            $grandTotal['collected'] += $totalCollected;
            $grandTotal['balance'] += $totalBalance;
            $grandTotal['paid'] += $paidCount;
            $grandTotal['pending'] += $pendingCount;
            $grandTotal['partial'] += $partialCount;
        }

        // Grand Total Row
        $overallCollectionPercentage = $grandTotal['rent'] > 0 ? round(($grandTotal['collected'] / $grandTotal['rent']) * 100, 1) : 0;
        $csv .= "\nGRAND TOTAL,";
        $csv .= $grandTotal['residents'] . ",";
        $csv .= $this->csvNumber($grandTotal['resident_rent']) . ",";
        $csv .= $grandTotal['payments'] . ",";
        $csv .= $this->csvNumber($grandTotal['rent']) . ",";
        $csv .= $this->csvNumber($grandTotal['collected']) . ",";
        $csv .= $this->csvNumber($grandTotal['balance']) . ",";
        $csv .= $grandTotal['paid'] . ",";
        $csv .= $grandTotal['pending'] . ",";
        $csv .= $grandTotal['partial'] . ",";
        $csv .= $overallCollectionPercentage . "%\n";

        // ============================================================
        // RESIDENT-WISE DETAILED SUMMARY
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- RESIDENT-WISE DETAILED SUMMARY ---\n";
        $csv .= "S.No,Hostel,Room,Resident Name,Phone,Monthly Rent (₹),Total Paid (₹),Total Balance (₹),Payment Count,Status\n";

        $serialNo = 1;
        foreach ($allResidents->sortBy('hostel_id') as $resident) {
            $residentPayments = $allPayments->where('resident_id', $resident->id);
            $totalPaid = $residentPayments->sum('cash_paid_amount') + $residentPayments->sum('upi_paid_amount');
            $totalBalance = $residentPayments->sum('balance_amount');
            $paymentCount = $residentPayments->count();

            // Determine status
            if ($residentPayments->where('status', 'PENDING')->count() > 0) {
                $status = 'PENDING';
            } elseif ($residentPayments->where('status', 'PARTIAL')->count() > 0) {
                $status = 'PARTIAL';
            } elseif ($residentPayments->where('status', 'PAID')->count() > 0) {
                $status = 'PAID';
            } else {
                $status = 'NO PAYMENT';
            }

            $csv .= $serialNo . ",";
            $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
            $csv .= "#" . ($resident->room->room_no ?? 'N/A') . ",";
            $csv .= $this->csvString($resident->name) . ",";
            $csv .= $this->csvString($resident->phone ?? '') . ",";
            $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paymentCount . ",";
            $csv .= $status . "\n";
            $serialNo++;
        }

        // ============================================================
        // MONTHLY PAYMENT SUMMARY
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- MONTHLY PAYMENT SUMMARY ---\n";
        $csv .= "Month,Year,Total Payments,Total Rent (₹),Total Paid (₹),Total Balance (₹),Paid Count,Pending Count,Partial Count\n";

        $monthlySummary = $allPayments->groupBy(function($item) {
            return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
        })->sortKeysDesc();

        foreach ($monthlySummary as $key => $group) {
            $month = substr($key, 5, 2);
            $year = substr($key, 0, 4);
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            
            $totalRent = $group->sum('rent_amount');
            $totalPaid = $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount');
            $totalBalance = $group->sum('balance_amount');
            $paidCount = $group->where('status', 'PAID')->count();
            $pendingCount = $group->where('status', 'PENDING')->count();
            $partialCount = $group->where('status', 'PARTIAL')->count();

            $csv .= $monthName . ",";
            $csv .= $year . ",";
            $csv .= $group->count() . ",";
            $csv .= $this->csvNumber($totalRent) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paidCount . ",";
            $csv .= $pendingCount . ",";
            $csv .= $partialCount . "\n";
        }

        // ============================================================
        // PAYMENT STATUS BREAKDOWN
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- PAYMENT STATUS BREAKDOWN ---\n";
        $total = $allPayments->count() > 0 ? $allPayments->count() : 1;
        $csv .= "Total Payments: " . $allPayments->count() . "\n";
        $csv .= "  - PAID: " . $allPayments->where('status', 'PAID')->count() . " (" . round(($allPayments->where('status', 'PAID')->count() / $total) * 100, 1) . "%)\n";
        $csv .= "  - PENDING: " . $allPayments->where('status', 'PENDING')->count() . " (" . round(($allPayments->where('status', 'PENDING')->count() / $total) * 100, 1) . "%)\n";
        $csv .= "  - PARTIAL: " . $allPayments->where('status', 'PARTIAL')->count() . " (" . round(($allPayments->where('status', 'PARTIAL')->count() / $total) * 100, 1) . "%)\n";

        // ============================================================
        // PAYMENT DETAILS (All Transactions)
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- ALL PAYMENT TRANSACTIONS ---\n";
        $csv .= "Receipt No,Resident,Hostel,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Balance (₹),Status,Payment Date,Transaction ID\n";

        foreach ($allPayments->sortByDesc('payment_date') as $payment) {
            $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
            $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;

            $csv .= $this->csvString($payment->receipt_no) . ",";
            $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
            $csv .= $this->csvString($payment->resident->hostel->hostel_name ?? 'N/A') . ",";
            $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
            $csv .= $monthName . ",";
            $csv .= $payment->year . ",";
            $csv .= $this->csvNumber($payment->rent_amount) . ",";
            $csv .= $this->csvNumber($payment->discount_amount) . ",";
            $csv .= $this->csvNumber($payment->fine_amount) . ",";
            $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
            $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($payment->balance_amount) . ",";
            $csv .= $payment->status . ",";
            $csv .= $payment->payment_date . ",";
            $csv .= $this->csvString($payment->transaction_id ?? '') . "\n";
        }

        $csv .= "\n==================================================\n";
        $csv .= "END OF REPORT\n";
        $csv .= "==================================================\n";

        $filename = 'complete-payment-report-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT ALL PAYMENTS
     */
    public function exportAll(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);

        $payments = $query->orderBy('created_at', 'desc')->get();

        $csv = "ALL PAYMENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Records: " . $payments->count() . "\n\n";

        $csv .= "Receipt No,Resident,Hostel,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Balance (₹),Status,Payment Date,Transaction ID\n";

        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;

                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($payment->resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
                $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
                $csv .= $this->csvNumber($totalPaid) . ",";
                $csv .= $this->csvNumber($payment->balance_amount) . ",";
                $csv .= $payment->status . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->transaction_id ?? '') . "\n";
            }
        } else {
            $csv .= "No payments found.\n";
        }

        $filename = 'all-payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT PAID PAYMENTS ONLY
     */
    public function exportPaid(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('status', 'PAID');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $csv = "PAID PAYMENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Paid Records: " . $payments->count() . "\n\n";

        $csv .= "Receipt No,Resident,Hostel,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Payment Date,Transaction ID\n";

        if ($payments->count() > 0) {
            $totalRent = 0;
            $totalCollected = 0;
            
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;
                $totalRent += $payment->rent_amount;
                $totalCollected += $totalPaid;

                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($payment->resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
                $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
                $csv .= $this->csvNumber($totalPaid) . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->transaction_id ?? '') . "\n";
            }

            $csv .= "\n\nSUMMARY\n";
            $csv .= "Total Paid Records: " . $payments->count() . "\n";
            $csv .= "Total Rent Amount: ₹" . $this->csvNumber($totalRent) . "\n";
            $csv .= "Total Collected: ₹" . $this->csvNumber($totalCollected) . "\n";
        } else {
            $csv .= "No paid payments found.\n";
        }

        $filename = 'paid-payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT UNPAID PAYMENTS - COMPLETE REPORT
     * Shows ALL residents with NO payment OR PARTIAL payment
     */
    public function exportUnpaid(Request $request)
    {
        $user = auth()->user();

        // Get filters
        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;
        $statusFilter = $request->filled('status') ? $request->status : null;

        // Get ALL active residents
        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        // Apply user role restrictions
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        // Apply hostel filter
        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $allResidents = $residentsQuery->get();

        // Get payments for the selected month/year
        $payments = Payment::where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('resident_id');

        // Separate into categories
        $unpaidResidents = [];
        $paidResidents = [];
        $partialResidents = [];

        foreach ($allResidents as $resident) {
            if ($payments->has($resident->id)) {
                $payment = $payments->get($resident->id);
                
                if ($payment->status === 'PAID') {
                    $paidResidents[] = [
                        'resident' => $resident,
                        'payment' => $payment,
                        'status' => 'PAID',
                        'due_amount' => 0
                    ];
                } elseif ($payment->status === 'PARTIAL') {
                    $partialResidents[] = [
                        'resident' => $resident,
                        'payment' => $payment,
                        'status' => 'PARTIAL',
                        'due_amount' => $payment->balance_amount,
                        'paid_amount' => $payment->cash_paid_amount + $payment->upi_paid_amount
                    ];
                } elseif ($payment->status === 'PENDING') {
                    $unpaidResidents[] = [
                        'resident' => $resident,
                        'payment' => $payment,
                        'status' => 'PENDING',
                        'due_amount' => $payment->balance_amount,
                        'paid_amount' => 0
                    ];
                }
            } else {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => null,
                    'status' => 'NO PAYMENT',
                    'due_amount' => $resident->rent_amount ?? 0,
                    'paid_amount' => 0
                ];
            }
        }

        // Merge unpaid and partial residents
        $allUnpaid = array_merge($unpaidResidents, $partialResidents);

        // Apply status filter if provided
        if ($statusFilter) {
            $allUnpaid = array_filter($allUnpaid, function($item) use ($statusFilter) {
                return $item['status'] === $statusFilter;
            });
        }

        // Sort by due amount (highest first)
        usort($allUnpaid, function($a, $b) {
            return $b['due_amount'] <=> $a['due_amount'];
        });

        // Build CSV
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $csv = "UNPAID PAYMENTS REPORT - COMPLETE\n";
        $csv .= "==================================================\n";
        $csv .= "Month: {$monthName} {$year}\n";
        if ($hostelId) {
            $hostel = Hostel::find($hostelId);
            $csv .= "Hostel: " . ($hostel ? $hostel->hostel_name : 'All Hostels') . "\n";
        } else {
            $csv .= "Hostel: All Hostels\n";
        }
        if ($statusFilter) {
            $csv .= "Status Filter: " . $statusFilter . "\n";
        }
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Active Residents: " . $allResidents->count() . "\n";
        $csv .= "Paid Residents: " . count($paidResidents) . "\n";
        $csv .= "Unpaid/Partial Residents: " . count($allUnpaid) . "\n";
        $csv .= "==================================================\n\n";

        // UNPAID RESIDENTS LIST
        $csv .= "--- UNPAID RESIDENTS (" . count($allUnpaid) . ") ---\n";
        $csv .= "S.No,Status,Resident Name,Hostel,Room,Phone,Email,Rent Amount (₹),Paid Amount (₹),Due Amount (₹),Receipt No,Payment Date\n";

        if (count($allUnpaid) > 0) {
            $serialNo = 1;
            $totalRent = 0;
            $totalPaid = 0;
            $totalDue = 0;
            $pendingCount = 0;
            $partialCount = 0;
            $noPaymentCount = 0;

            foreach ($allUnpaid as $item) {
                $resident = $item['resident'];
                $status = $item['status'];
                $dueAmount = $item['due_amount'];
                $paidAmount = $item['paid_amount'] ?? 0;
                $payment = $item['payment'] ?? null;
                
                $totalRent += $resident->rent_amount ?? 0;
                $totalPaid += $paidAmount;
                $totalDue += $dueAmount;

                if ($status === 'PENDING') $pendingCount++;
                elseif ($status === 'PARTIAL') $partialCount++;
                elseif ($status === 'NO PAYMENT') $noPaymentCount++;

                $csv .= $serialNo . ",";
                $csv .= $status . ",";
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($resident->room->room_no ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvString($resident->email ?? '') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= $this->csvNumber($paidAmount) . ",";
                $csv .= $this->csvNumber($dueAmount) . ",";
                $csv .= ($payment ? $this->csvString($payment->receipt_no) : '') . ",";
                $csv .= ($payment ? $payment->payment_date : '') . "\n";
                $serialNo++;
            }

            // Summary
            $csv .= "\n\n";
            $csv .= "==================================================\n";
            $csv .= "UNPAID SUMMARY\n";
            $csv .= "==================================================\n";
            $csv .= "Total Unpaid Residents: " . count($allUnpaid) . "\n";
            $csv .= "  - NO PAYMENT: " . $noPaymentCount . "\n";
            $csv .= "  - PENDING: " . $pendingCount . "\n";
            $csv .= "  - PARTIAL: " . $partialCount . "\n";
            $csv .= "\n";
            $csv .= "Total Rent Amount: ₹" . $this->csvNumber($totalRent) . "\n";
            $csv .= "Total Paid Amount: ₹" . $this->csvNumber($totalPaid) . "\n";
            $csv .= "Total Due Amount: ₹" . $this->csvNumber($totalDue) . "\n";
            $csv .= "==================================================\n";

        } else {
            $csv .= "✅ All residents have paid for {$monthName} {$year}!\n";
            $csv .= "No unpaid residents found.\n";
        }

        $filename = 'unpaid-payments-complete-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT PENDING ONLY
     */
    public function exportPendingOnly(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('status', 'PENDING');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);

        $payments = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $csv = "PENDING PAYMENTS ONLY\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Pending: " . $payments->count() . "\n\n";

        $csv .= "S.No,Receipt No,Resident,Hostel,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Total Due (₹),Payment Date,Contact\n";

        if ($payments->count() > 0) {
            $serialNo = 1;
            $totalDue = 0;
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
                $totalDue += $totalAmount;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($payment->resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($totalAmount) . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->resident->phone ?? '') . "\n";
                $serialNo++;
            }

            $csv .= "\nSummary:\n";
            $csv .= "Total Pending Records: " . $payments->count() . "\n";
            $csv .= "Total Due Amount: ₹" . $this->csvNumber($totalDue) . "\n";
        } else {
            $csv .= "No pending payments found.\n";
        }

        $filename = 'pending-only-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT PARTIAL ONLY
     */
    public function exportPartialOnly(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('status', 'PARTIAL');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);

        $payments = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $csv = "PARTIAL PAYMENTS ONLY\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Partial: " . $payments->count() . "\n\n";

        $csv .= "S.No,Receipt No,Resident,Hostel,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Paid (₹),Remaining Due (₹),Payment Date,Contact\n";

        if ($payments->count() > 0) {
            $serialNo = 1;
            $totalPaid = 0;
            $totalRemaining = 0;
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $paid = $payment->cash_paid_amount + $payment->upi_paid_amount;
                $totalPaid += $paid;
                $totalRemaining += $payment->balance_amount;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($payment->resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($paid) . ",";
                $csv .= $this->csvNumber($payment->balance_amount) . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->resident->phone ?? '') . "\n";
                $serialNo++;
            }

            $csv .= "\nSummary:\n";
            $csv .= "Total Partial Records: " . $payments->count() . "\n";
            $csv .= "Total Paid Amount: ₹" . $this->csvNumber($totalPaid) . "\n";
            $csv .= "Total Remaining Due: ₹" . $this->csvNumber($totalRemaining) . "\n";
        } else {
            $csv .= "No partial payments found.\n";
        }

        $filename = 'partial-only-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT MONTHLY UNPAID
     */
    public function exportMonthlyUnpaid(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'hostel_id' => 'nullable|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $month = $request->month;
        $year = $request->year;
        $hostelId = $request->hostel_id;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $allResidents = $residentsQuery->get();

        $payments = Payment::where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('resident_id');

        $unpaidResidents = [];
        $paidResidents = [];

        foreach ($allResidents as $resident) {
            if ($payments->has($resident->id)) {
                $payment = $payments->get($resident->id);
                if ($payment->status === 'PAID') {
                    $paidResidents[] = [
                        'resident' => $resident,
                        'payment' => $payment
                    ];
                } else {
                    $unpaidResidents[] = [
                        'resident' => $resident,
                        'payment' => $payment,
                        'status' => $payment->status,
                        'due_amount' => $payment->balance_amount
                    ];
                }
            } else {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => null,
                    'status' => 'NO PAYMENT RECORD',
                    'due_amount' => $resident->rent_amount ?? 0
                ];
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $csv = "MONTHLY UNPAID PAYMENT REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Month: {$monthName} {$year}\n";
        if ($hostelId) {
            $hostel = Hostel::find($hostelId);
            $csv .= "Hostel: " . ($hostel ? $hostel->hostel_name : 'All Hostels') . "\n";
        } else {
            $csv .= "Hostel: All Hostels\n";
        }
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Residents: " . $allResidents->count() . "\n";
        $csv .= "Paid Residents: " . count($paidResidents) . "\n";
        $csv .= "Unpaid Residents: " . count($unpaidResidents) . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "--- UNPAID RESIDENTS (" . count($unpaidResidents) . ") ---\n";
        $csv .= "S.No,Resident Name,Hostel,Room,Status,Due Amount (₹),Phone,Email\n";

        if (count($unpaidResidents) > 0) {
            $serialNo = 1;
            $totalUnpaid = 0;
            $pendingCount = 0;
            $partialCount = 0;
            $noRecordCount = 0;

            foreach ($unpaidResidents as $item) {
                $resident = $item['resident'];
                $status = $item['status'];
                $dueAmount = $item['due_amount'];
                $totalUnpaid += $dueAmount;

                if ($status === 'PENDING') $pendingCount++;
                elseif ($status === 'PARTIAL') $partialCount++;
                elseif ($status === 'NO PAYMENT RECORD') $noRecordCount++;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($resident->room->room_no ?? 'N/A') . ",";
                $csv .= $status . ",";
                $csv .= $this->csvNumber($dueAmount) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvString($resident->email ?? '') . "\n";
                $serialNo++;
            }

            $csv .= "\n";
            $csv .= "Unpaid Summary:\n";
            $csv .= "  - Total Unpaid Residents: " . count($unpaidResidents) . "\n";
            $csv .= "  - Total Due Amount: ₹" . $this->csvNumber($totalUnpaid) . "\n";
            $csv .= "  - PENDING: " . $pendingCount . "\n";
            $csv .= "  - PARTIAL: " . $partialCount . "\n";
            $csv .= "  - NO PAYMENT RECORD: " . $noRecordCount . "\n";
        } else {
            $csv .= "No unpaid residents found for this month!\n";
        }

        $csv .= "\n\n--- PAID RESIDENTS (" . count($paidResidents) . ") ---\n";
        if (count($paidResidents) > 0) {
            $csv .= "S.No,Resident Name,Hostel,Room,Receipt No,Amount Paid (₹),Payment Date\n";
            $serialNo = 1;
            foreach ($paidResidents as $item) {
                $resident = $item['resident'];
                $payment = $item['payment'];
                $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= "#" . ($resident->room->room_no ?? 'N/A') . ",";
                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvNumber($totalPaid) . ",";
                $csv .= $payment->payment_date . "\n";
                $serialNo++;
            }
        } else {
            $csv .= "No paid residents found for this month.\n";
        }

        $filename = 'monthly-unpaid-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT HOSTEL WISE - COMPLETE REPORT WITH ALL DETAILS
     */
    public function exportHostelWise(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this hostel\'s data!'
                ], 403);
            }
        }

        $hostel = Hostel::find($request->hostel_id);
        
        // Get all active residents for this hostel
        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        // Get all payments for this hostel
        $query = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });

        $this->applyExportFilters($query, $request);

        $payments = $query->get()->groupBy('resident_id');

        $csv = "==================================================\n";
        $csv .= "HOSTEL PAYMENT REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Hostel: " . $this->csvString($hostel->hostel_name) . "\n";
        $csv .= "Hostel Code: " . $this->csvString($hostel->hostel_code) . "\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Residents: " . $residents->count() . "\n";
        $csv .= "Total Payments: " . $query->get()->count() . "\n";
        $csv .= "==================================================\n\n";

        // ============================================================
        // RESIDENT-WISE PAYMENT SUMMARY
        // ============================================================
        $csv .= "--- RESIDENT-WISE PAYMENT SUMMARY ---\n";
        $csv .= "S.No,Resident Name,Room No,Rent Amount (₹),Total Paid (₹),Balance (₹),Payment Count,Status,Last Payment Date\n";

        $serialNo = 1;
        $grandTotalRent = 0;
        $grandTotalPaid = 0;
        $grandTotalBalance = 0;

        foreach ($residents as $resident) {
            $residentPayments = $payments->get($resident->id) ?? collect();
            $totalPaid = $residentPayments->sum('cash_paid_amount') + $residentPayments->sum('upi_paid_amount');
            $totalRent = $residentPayments->sum('rent_amount');
            $totalBalance = $residentPayments->sum('balance_amount');
            $paymentCount = $residentPayments->count();
            $lastPayment = $residentPayments->sortByDesc('payment_date')->first();

            // Determine overall status
            if ($residentPayments->where('status', 'PENDING')->count() > 0) {
                $status = 'PENDING';
            } elseif ($residentPayments->where('status', 'PARTIAL')->count() > 0) {
                $status = 'PARTIAL';
            } elseif ($residentPayments->where('status', 'PAID')->count() > 0) {
                $status = 'PAID';
            } else {
                $status = 'NO PAYMENT';
            }

            $csv .= $serialNo . ",";
            $csv .= $this->csvString($resident->name) . ",";
            $csv .= "#" . ($resident->room->room_no ?? 'N/A') . ",";
            $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paymentCount . ",";
            $csv .= $status . ",";
            $csv .= ($lastPayment ? $lastPayment->payment_date : '') . "\n";

            $grandTotalRent += $resident->rent_amount ?? 0;
            $grandTotalPaid += $totalPaid;
            $grandTotalBalance += $totalBalance;
            $serialNo++;
        }

        $csv .= "\nGRAND TOTAL,,,,,";
        $csv .= $this->csvNumber($grandTotalRent) . ",";
        $csv .= $this->csvNumber($grandTotalPaid) . ",";
        $csv .= $this->csvNumber($grandTotalBalance) . ",,,\n";

        // ============================================================
        // PAYMENT DETAILS
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- DETAILED PAYMENT TRANSACTIONS ---\n";
        $csv .= "Receipt No,Resident,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Balance (₹),Status,Payment Date,Transaction ID\n";

        $allPayments = $query->get();
        if ($allPayments->count() > 0) {
            foreach ($allPayments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;

                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
                $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
                $csv .= $this->csvNumber($totalPaid) . ",";
                $csv .= $this->csvNumber($payment->balance_amount) . ",";
                $csv .= $payment->status . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->transaction_id ?? '') . "\n";
            }
        } else {
            $csv .= "No payment records found.\n";
        }

        // ============================================================
        // MONTHLY SUMMARY
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- MONTHLY SUMMARY ---\n";
        $csv .= "Month,Year,Total Payments,Total Rent (₹),Total Paid (₹),Total Balance (₹),Paid Count,Pending Count,Partial Count\n";

        $monthlySummary = $allPayments->groupBy(function($item) {
            return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
        })->sortKeysDesc();

        foreach ($monthlySummary as $key => $group) {
            $month = substr($key, 5, 2);
            $year = substr($key, 0, 4);
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            
            $totalRent = $group->sum('rent_amount');
            $totalPaid = $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount');
            $totalBalance = $group->sum('balance_amount');
            $paidCount = $group->where('status', 'PAID')->count();
            $pendingCount = $group->where('status', 'PENDING')->count();
            $partialCount = $group->where('status', 'PARTIAL')->count();

            $csv .= $monthName . ",";
            $csv .= $year . ",";
            $csv .= $group->count() . ",";
            $csv .= $this->csvNumber($totalRent) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paidCount . ",";
            $csv .= $pendingCount . ",";
            $csv .= $partialCount . "\n";
        }

        // ============================================================
        // OVERALL SUMMARY
        // ============================================================
        $csv .= "\n\n";
        $csv .= "--- OVERALL SUMMARY ---\n";
        $csv .= "Total Residents: " . $residents->count() . "\n";
        $csv .= "Total Resident Rent: ₹" . $this->csvNumber($residents->sum('rent_amount')) . "\n";
        $csv .= "Total Payments: " . $allPayments->count() . "\n";
        $csv .= "Total Rent Amount (from payments): ₹" . $this->csvNumber($allPayments->sum('rent_amount')) . "\n";
        $csv .= "Total Collected: ₹" . $this->csvNumber($allPayments->sum('cash_paid_amount') + $allPayments->sum('upi_paid_amount')) . "\n";
        $csv .= "Total Balance: ₹" . $this->csvNumber($allPayments->sum('balance_amount')) . "\n";
        $csv .= "Paid Payments: " . $allPayments->where('status', 'PAID')->count() . "\n";
        $csv .= "Pending Payments: " . $allPayments->where('status', 'PENDING')->count() . "\n";
        $csv .= "Partial Payments: " . $allPayments->where('status', 'PARTIAL')->count() . "\n";
        $collectionPercentage = $allPayments->sum('rent_amount') > 0 ? round((($allPayments->sum('cash_paid_amount') + $allPayments->sum('upi_paid_amount')) / $allPayments->sum('rent_amount')) * 100, 1) : 0;
        $csv .= "Collection Efficiency: " . $collectionPercentage . "%\n";
        $csv .= "==================================================\n";

        $filename = 'hostel-' . $hostel->hostel_code . '-complete-report-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT HOSTEL WISE PAID
     */
    public function exportHostelWisePaid(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this hostel\'s data!'
                ], 403);
            }
        }

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            })
            ->where('status', 'PAID');

        $this->applyExportFilters($query, $request);

        $payments = $query->get();
        $hostel = Hostel::find($request->hostel_id);

        $csv = "HOSTEL PAID PAYMENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Hostel: " . $this->csvString($hostel->hostel_name) . "\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Paid Records: " . $payments->count() . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "Receipt No,Resident,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Payment Date,Transaction ID\n";

        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
                $totalPaid = $payment->cash_paid_amount + $payment->upi_paid_amount;

                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
                $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
                $csv .= $this->csvNumber($totalPaid) . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->transaction_id ?? '') . "\n";
            }
        } else {
            $csv .= "No paid payments found for the selected filters.\n";
        }

        $filename = 'hostel-' . $hostel->hostel_code . '-paid-payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT HOSTEL WISE UNPAID
     */
    public function exportHostelWiseUnpaid(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this hostel\'s data!'
                ], 403);
            }
        }

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            })
            ->whereIn('status', ['PENDING', 'PARTIAL']);

        $this->applyExportFilters($query, $request);

        $payments = $query->get();
        $hostel = Hostel::find($request->hostel_id);

        $csv = "HOSTEL UNPAID PAYMENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Hostel: " . $this->csvString($hostel->hostel_name) . "\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Unpaid Records: " . $payments->count() . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "Receipt No,Resident,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Total Due (₹),Cash Paid (₹),UPI Paid (₹),Status,Payment Date,Contact\n";

        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));

                $csv .= $this->csvString($payment->receipt_no) . ",";
                $csv .= $this->csvString($payment->resident->name ?? 'N/A') . ",";
                $csv .= "#" . ($payment->resident->room->room_no ?? 'N/A') . ",";
                $csv .= $monthName . ",";
                $csv .= $payment->year . ",";
                $csv .= $this->csvNumber($payment->rent_amount) . ",";
                $csv .= $this->csvNumber($payment->discount_amount) . ",";
                $csv .= $this->csvNumber($payment->fine_amount) . ",";
                $csv .= $this->csvNumber($payment->balance_amount) . ",";
                $csv .= $this->csvNumber($payment->cash_paid_amount) . ",";
                $csv .= $this->csvNumber($payment->upi_paid_amount) . ",";
                $csv .= $payment->status . ",";
                $csv .= $payment->payment_date . ",";
                $csv .= $this->csvString($payment->resident->phone ?? '') . "\n";
            }

            $csv .= "\n\nSummary\n";
            $csv .= "Total Unpaid Count: " . $payments->count() . "\n";
            $csv .= "Total Due Amount: ₹" . $this->csvNumber($payments->sum('balance_amount')) . "\n";
            $csv .= "Total Rent: ₹" . $this->csvNumber($payments->sum('rent_amount')) . "\n";
            $csv .= "Pending: " . $payments->where('status', 'PENDING')->count() . "\n";
            $csv .= "Partial: " . $payments->where('status', 'PARTIAL')->count() . "\n";
        } else {
            $csv .= "No unpaid payments found for the selected filters.\n";
        }

        $filename = 'hostel-' . $hostel->hostel_code . '-unpaid-payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT PAYMENT SUMMARY
     */
    public function exportPaymentSummary(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

        $csv = "PAYMENT SUMMARY REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "--- HOSTEL-WISE SUMMARY ---\n";
        $csv .= "Hostel,Total Residents,Total Payments,Total Rent (₹),Total Collected (₹),Total Balance (₹),Paid Count,Pending Count,Partial Count\n";

        $grandTotal = [
            'residents' => 0,
            'payments' => 0,
            'rent' => 0,
            'collected' => 0,
            'balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0
        ];

        foreach ($hostels as $hostel) {
            $residents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->count();

            $payments = Payment::whereHas('resident', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->get();

            $totalRent = $payments->sum('rent_amount');
            $totalCollected = $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount');
            $totalBalance = $payments->sum('balance_amount');
            $paidCount = $payments->where('status', 'PAID')->count();
            $pendingCount = $payments->where('status', 'PENDING')->count();
            $partialCount = $payments->where('status', 'PARTIAL')->count();

            $csv .= $this->csvString($hostel->hostel_name) . ",";
            $csv .= $residents . ",";
            $csv .= $payments->count() . ",";
            $csv .= $this->csvNumber($totalRent) . ",";
            $csv .= $this->csvNumber($totalCollected) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $paidCount . ",";
            $csv .= $pendingCount . ",";
            $csv .= $partialCount . "\n";

            $grandTotal['residents'] += $residents;
            $grandTotal['payments'] += $payments->count();
            $grandTotal['rent'] += $totalRent;
            $grandTotal['collected'] += $totalCollected;
            $grandTotal['balance'] += $totalBalance;
            $grandTotal['paid'] += $paidCount;
            $grandTotal['pending'] += $pendingCount;
            $grandTotal['partial'] += $partialCount;
        }

        $csv .= "\nGRAND TOTAL,";
        $csv .= $grandTotal['residents'] . ",";
        $csv .= $grandTotal['payments'] . ",";
        $csv .= $this->csvNumber($grandTotal['rent']) . ",";
        $csv .= $this->csvNumber($grandTotal['collected']) . ",";
        $csv .= $this->csvNumber($grandTotal['balance']) . ",";
        $csv .= $grandTotal['paid'] . ",";
        $csv .= $grandTotal['pending'] . ",";
        $csv .= $grandTotal['partial'] . "\n";

        $filename = 'payment-summary-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}