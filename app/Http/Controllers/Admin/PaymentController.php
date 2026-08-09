<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF; // Add this for PDF export

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

        $residents = $query->get(['id', 'name', 'resident_code', 'hostel_id', 'room_id', 'rent_amount', 'bed_number']);

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
    // HELPER METHODS FOR EXPORTS
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

    /**
     * Get room details for export.
     */
    private function getRoomDetails($resident)
    {
        if (!$resident->room) {
            return ['room_no' => 'N/A', 'bed_number' => 'N/A'];
        }
        return [
            'room_no' => $resident->room->room_no ?? 'N/A',
            'bed_number' => $resident->bed_number ?? 'N/A'
        ];
    }

    // ============================================================
    // CSV EXPORT METHODS
    // ============================================================

    /**
     * EXPORT RESIDENT PAYMENT STATUS SUMMARY
     */
    public function exportResidentPaymentStatus(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $csv = "==================================================\n";
        $csv .= "RESIDENT PAYMENT STATUS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Month: {$monthName} {$year}\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Residents: " . $residents->count() . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "S.No,Hostel,Room No,Bed No,Resident Name,Phone,Monthly Rent (₹),Payment Status,Receipt No,Paid Amount (₹),Balance (₹)\n";

        $serialNo = 1;
        $totalResidents = 0;
        $totalRent = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        $paidCount = 0;
        $pendingCount = 0;
        $partialCount = 0;
        $noPaymentCount = 0;

        foreach ($residents as $resident) {
            $roomDetails = $this->getRoomDetails($resident);
            $payment = $payments->get($resident->id);

            $totalResidents++;
            $totalRent += $resident->rent_amount ?? 0;

            if ($payment) {
                $paidAmount = $payment->cash_paid_amount + $payment->upi_paid_amount;
                $status = $payment->status;
                $balance = $payment->balance_amount;

                if ($status === 'PAID') { $paidCount++; $totalPaid += $paidAmount; }
                elseif ($status === 'PARTIAL') { $partialCount++; $totalPaid += $paidAmount; $totalBalance += $balance; }
                elseif ($status === 'PENDING') { $pendingCount++; $totalBalance += $balance; }

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomDetails['room_no'] . ",";
                $csv .= $roomDetails['bed_number'] . ",";
                $csv .= $this->csvString($resident->name) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= $status . ",";
                $csv .= $this->csvString($payment->receipt_no ?? '') . ",";
                $csv .= $this->csvNumber($paidAmount) . ",";
                $csv .= $this->csvNumber($balance) . "\n";
            } else {
                $noPaymentCount++;
                $totalBalance += $resident->rent_amount ?? 0;
                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomDetails['room_no'] . ",";
                $csv .= $roomDetails['bed_number'] . ",";
                $csv .= $this->csvString($resident->name) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= "NO PAYMENT" . ",";
                $csv .= "" . ",";
                $csv .= "0.00" . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . "\n";
            }
            $serialNo++;
        }

        $csv .= "\n\n==================================================\n";
        $csv .= "SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Total Residents: " . $totalResidents . "\n";
        $csv .= "  - PAID: " . $paidCount . "\n";
        $csv .= "  - PENDING: " . $pendingCount . "\n";
        $csv .= "  - PARTIAL: " . $partialCount . "\n";
        $csv .= "  - NO PAYMENT: " . $noPaymentCount . "\n\n";
        $csv .= "Total Rent Amount: ₹" . $this->csvNumber($totalRent) . "\n";
        $csv .= "Total Paid Amount: ₹" . $this->csvNumber($totalPaid) . "\n";
        $csv .= "Total Balance: ₹" . $this->csvNumber($totalBalance) . "\n";
        $csv .= "==================================================\n";

        $filename = 'resident-payment-status-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT PENDING RESIDENTS
     */
    public function exportPendingResidents(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $csv = "==================================================\n";
        $csv .= "PENDING RESIDENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Month: {$monthName} {$year}\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "S.No,Hostel,Room No,Bed No,Resident Name,Phone,Monthly Rent (₹),Payment Status,Due Amount (₹)\n";

        $serialNo = 1;
        $totalPending = 0;
        $totalDue = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $isPending = false;
            $dueAmount = 0;

            if ($payment) {
                if ($payment->status === 'PENDING' || $payment->status === 'PARTIAL') {
                    $isPending = true;
                    $dueAmount = $payment->balance_amount;
                }
            } else {
                $isPending = true;
                $dueAmount = $resident->rent_amount ?? 0;
            }

            if ($isPending) {
                $roomDetails = $this->getRoomDetails($resident);
                $totalPending++;
                $totalDue += $dueAmount;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomDetails['room_no'] . ",";
                $csv .= $roomDetails['bed_number'] . ",";
                $csv .= $this->csvString($resident->name) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= ($payment ? $payment->status : 'NO PAYMENT') . ",";
                $csv .= $this->csvNumber($dueAmount) . "\n";
                $serialNo++;
            }
        }

        $csv .= "\n\n==================================================\n";
        $csv .= "SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Total Pending Residents: " . $totalPending . "\n";
        $csv .= "Total Due Amount: ₹" . $this->csvNumber($totalDue) . "\n";
        $csv .= "==================================================\n";

        $filename = 'pending-residents-' . date('Y-m-d') . '.csv';
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
     * EXPORT UNPAID PAYMENTS
     */
    public function exportUnpaid(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $csv = "UNPAID PAYMENTS REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Month: {$monthName} {$year}\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "S.No,Hostel,Room No,Bed No,Resident Name,Phone,Monthly Rent (₹),Status,Due Amount (₹),Paid Amount (₹)\n";

        $serialNo = 1;
        $totalUnpaid = 0;
        $totalDue = 0;
        $totalPaid = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);

            if (!$payment || $payment->status !== 'PAID') {
                $roomDetails = $this->getRoomDetails($resident);
                $totalUnpaid++;

                if ($payment) {
                    $dueAmount = $payment->balance_amount;
                    $paidAmount = $payment->cash_paid_amount + $payment->upi_paid_amount;
                    $status = $payment->status;
                } else {
                    $dueAmount = $resident->rent_amount ?? 0;
                    $paidAmount = 0;
                    $status = 'NO PAYMENT';
                }

                $totalDue += $dueAmount;
                $totalPaid += $paidAmount;

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomDetails['room_no'] . ",";
                $csv .= $roomDetails['bed_number'] . ",";
                $csv .= $this->csvString($resident->name) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= $status . ",";
                $csv .= $this->csvNumber($dueAmount) . ",";
                $csv .= $this->csvNumber($paidAmount) . "\n";
                $serialNo++;
            }
        }

        $csv .= "\n\n==================================================\n";
        $csv .= "SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Total Unpaid Residents: " . $totalUnpaid . "\n";
        $csv .= "Total Due Amount: ₹" . $this->csvNumber($totalDue) . "\n";
        $csv .= "Total Paid Amount (Partial): ₹" . $this->csvNumber($totalPaid) . "\n";
        $csv .= "==================================================\n";

        $filename = 'unpaid-payments-' . date('Y-m-d') . '.csv';
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
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

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
        $csv .= "==================================================\n\n";

        $csv .= "--- UNPAID RESIDENTS ---\n";
        $csv .= "S.No,Resident Name,Hostel,Room No,Bed No,Status,Due Amount (₹),Phone,Email\n";

        $serialNo = 1;
        $totalUnpaid = 0;
        $totalDue = 0;

        foreach ($allResidents as $resident) {
            $payment = $payments->get($resident->id);

            if (!$payment || $payment->status !== 'PAID') {
                $roomDetails = $this->getRoomDetails($resident);
                $totalUnpaid++;
                $dueAmount = $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0);
                $totalDue += $dueAmount;
                $status = $payment ? $payment->status : 'NO PAYMENT';

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomDetails['room_no'] . ",";
                $csv .= $roomDetails['bed_number'] . ",";
                $csv .= $status . ",";
                $csv .= $this->csvNumber($dueAmount) . ",";
                $csv .= $this->csvString($resident->phone ?? '') . ",";
                $csv .= $this->csvString($resident->email ?? '') . "\n";
                $serialNo++;
            }
        }

        $csv .= "\n\n==================================================\n";
        $csv .= "SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Total Unpaid Residents: " . $totalUnpaid . "\n";
        $csv .= "Total Due Amount: ₹" . $this->csvNumber($totalDue) . "\n";
        $csv .= "==================================================\n";

        $filename = 'monthly-unpaid-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * EXPORT HOSTEL WISE
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

        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $query = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });

        $this->applyExportFilters($query, $request);
        $payments = $query->get();

        $csv = "==================================================\n";
        $csv .= "HOSTEL PAYMENT REPORT\n";
        $csv .= "==================================================\n";
        $csv .= "Hostel: " . $this->csvString($hostel->hostel_name) . "\n";
        $csv .= "Generated: " . now()->format('d M Y h:i A') . "\n";
        $csv .= "Total Residents: " . $residents->count() . "\n";
        $csv .= "Total Payments: " . $payments->count() . "\n";
        $csv .= "==================================================\n\n";

        $csv .= "--- RESIDENT-WISE PAYMENT SUMMARY ---\n";
        $csv .= "S.No,Resident Name,Room No,Bed No,Rent Amount (₹),Total Paid (₹),Balance (₹),Payment Count,Status\n";

        $serialNo = 1;
        $groupedPayments = $payments->groupBy('resident_id');

        foreach ($residents as $resident) {
            $residentPayments = $groupedPayments->get($resident->id) ?? collect();
            $totalPaid = $residentPayments->sum('cash_paid_amount') + $residentPayments->sum('upi_paid_amount');
            $totalBalance = $residentPayments->sum('balance_amount');

            $roomDetails = $this->getRoomDetails($resident);

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
            $csv .= $roomDetails['room_no'] . ",";
            $csv .= $roomDetails['bed_number'] . ",";
            $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
            $csv .= $this->csvNumber($totalPaid) . ",";
            $csv .= $this->csvNumber($totalBalance) . ",";
            $csv .= $residentPayments->count() . ",";
            $csv .= $status . "\n";
            $serialNo++;
        }

        $csv .= "\n\n--- DETAILED PAYMENT TRANSACTIONS ---\n";
        $csv .= "Receipt No,Resident,Room,Month,Year,Rent (₹),Discount (₹),Fine (₹),Cash (₹),UPI (₹),Total Paid (₹),Balance (₹),Status,Payment Date\n";

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
                $csv .= $this->csvNumber($payment->balance_amount) . ",";
                $csv .= $payment->status . ",";
                $csv .= $payment->payment_date . "\n";
            }
        } else {
            $csv .= "No payment records found.\n";
        }

        $filename = 'hostel-' . $hostel->hostel_code . '-report-' . date('Y-m-d') . '.csv';
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

    // ============================================================
    // PDF EXPORT METHODS
    // ============================================================

    /**
     * EXPORT RESIDENT PAYMENT STATUS AS PDF
     */
    public function pdfResidentPaymentStatus(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Resident Payment Status Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'residents' => $residents,
            'payments' => $payments,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.resident-status', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('resident-payment-status-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT PENDING RESIDENTS AS PDF
     */
    public function pdfPendingResidents(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        // Filter pending residents
        $pendingResidents = [];
        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $pendingResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT'
                ];
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Pending Residents Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'pendingResidents' => $pendingResidents,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.pending-residents', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('pending-residents-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT ALL PAYMENTS AS PDF
     */
    public function pdfAllPayments(Request $request)
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

        // Get summary
        $summary = [
            'total' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'paid' => $payments->where('status', 'PAID')->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count()
        ];

        $data = [
            'title' => 'All Payments Report',
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user,
            'filters' => $request->all()
        ];

        $pdf = PDF::loadView('admin.payments.pdf.all-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('all-payments-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT PAID PAYMENTS AS PDF
     */
    public function pdfPaidPayments(Request $request)
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

        $summary = [
            'total' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount')
        ];

        $data = [
            'title' => 'Paid Payments Report',
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.paid-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('paid-payments-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT UNPAID PAYMENTS AS PDF
     */
    public function pdfUnpaidPayments(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $unpaidResidents = [];
        $totalDue = 0;
        $totalUnpaid = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT'
                ];
                $totalDue += $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0);
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Unpaid Payments Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.unpaid-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('unpaid-payments-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT HOSTEL WISE AS PDF
     */
    public function pdfHostelWise(Request $request)
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

        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $query = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });

        $this->applyExportFilters($query, $request);
        $payments = $query->get();
        $groupedPayments = $payments->groupBy('resident_id');

        $residentSummaries = [];
        foreach ($residents as $resident) {
            $residentPayments = $groupedPayments->get($resident->id) ?? collect();
            $residentSummaries[] = [
                'resident' => $resident,
                'payments' => $residentPayments,
                'total_paid' => $residentPayments->sum('cash_paid_amount') + $residentPayments->sum('upi_paid_amount'),
                'total_balance' => $residentPayments->sum('balance_amount'),
                'count' => $residentPayments->count(),
                'status' => $residentPayments->where('status', 'PENDING')->count() > 0 ? 'PENDING' :
                           ($residentPayments->where('status', 'PARTIAL')->count() > 0 ? 'PARTIAL' :
                           ($residentPayments->where('status', 'PAID')->count() > 0 ? 'PAID' : 'NO PAYMENT'))
            ];
        }

        $summary = [
            'total_residents' => $residents->count(),
            'total_payments' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'paid' => $payments->where('status', 'PAID')->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count()
        ];

        $data = [
            'title' => 'Hostel Payment Report',
            'hostel' => $hostel,
            'residentSummaries' => $residentSummaries,
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.hostel-wise', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('hostel-' . $hostel->hostel_code . '-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT PAYMENT SUMMARY AS PDF
     */
    public function pdfPaymentSummary(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

        $hostelSummaries = [];
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

            $hostelSummaries[] = [
                'hostel' => $hostel,
                'residents' => $residents,
                'payments_count' => $payments->count(),
                'total_rent' => $totalRent,
                'total_collected' => $totalCollected,
                'total_balance' => $totalBalance,
                'paid' => $paidCount,
                'pending' => $pendingCount,
                'partial' => $partialCount
            ];

            $grandTotal['residents'] += $residents;
            $grandTotal['payments'] += $payments->count();
            $grandTotal['rent'] += $totalRent;
            $grandTotal['collected'] += $totalCollected;
            $grandTotal['balance'] += $totalBalance;
            $grandTotal['paid'] += $paidCount;
            $grandTotal['pending'] += $pendingCount;
            $grandTotal['partial'] += $partialCount;
        }

        $data = [
            'title' => 'Payment Summary Report',
            'hostelSummaries' => $hostelSummaries,
            'grandTotal' => $grandTotal,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.payment-summary', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payment-summary-' . date('Y-m-d') . '.pdf');
    }

    /**
     * EXPORT MONTHLY UNPAID AS PDF
     */
    public function pdfMonthlyUnpaid(Request $request)
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
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $unpaidResidents = [];
        $totalUnpaid = 0;
        $totalDue = 0;

        foreach ($allResidents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT'
                ];
                $totalDue += $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0);
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Monthly Unpaid Payment Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.monthly-unpaid', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('monthly-unpaid-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * EXPORT SINGLE PAYMENT RECEIPT AS PDF
     */
    public function pdfReceipt($id)
    {
        $user = auth()->user();
        $payment = Payment::with(['resident', 'resident.hostel', 'resident.room'])->findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this receipt!'
                ], 403);
            }
        }

        $data = [
            'payment' => $payment,
            'resident' => $payment->resident,
            'hostel' => $payment->resident->hostel,
            'room' => $payment->resident->room,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.receipt', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('receipt-' . $payment->receipt_no . '.pdf');
    }

    /**
     * EXPORT BULK RECEIPTS AS PDF
     */
    public function pdfBulkReceipts(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payments = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->whereIn('id', $request->ids)
            ->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view some receipts!'
                    ], 403);
                }
            }
        }

        $data = [
            'payments' => $payments,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.bulk-receipts', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('bulk-receipts-' . date('Y-m-d') . '.pdf');
    }
}
