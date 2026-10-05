<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Flat date-based discount rules (edit here anytime)
     *   Day 1-5   → ₹250
     *   Day 6-10  → ₹125
     *   Day 11+   → ₹0
     */
    private const DATE_DISCOUNT_1_5  = 250.0;
    private const DATE_DISCOUNT_6_10 = 125.0;

    /**
     * Payment page
     */
    public function index()
    {
        $user = auth()->user();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }
        $hostels = $hostelQuery->get(['id', 'hostel_name', 'hostel_code']);

        return view('admin.payments.index', compact('hostels'));
    }

    /**
     * Return the date-based discount amount based on TODAY's date.
     */
    private function getDateBasedDiscount(?Carbon $asOf = null): float
    {
        $day = (int) ($asOf ?? Carbon::now())->day;

        if ($day >= 1 && $day <= 5) {
            return self::DATE_DISCOUNT_1_5;
        }
        if ($day >= 6 && $day <= 10) {
            return self::DATE_DISCOUNT_6_10;
        }
        return 0.0;
    }

    /**
     * Build filtered payment rows (used by filter + export).
     *
     * STATUS RULES — a resident can be in MULTIPLE categories:
     *   - hasPreviousPending → count as PENDING
     *   - hasCurrentUnpaid   → count as UNPAID   (no payment row this month)
     *   - hasCurrentPartial  → count as PARTIAL  (paid but balance > 0)
     *   - hasCurrentPaid     → count as PAID     (fully paid, balance = 0)
     */
    private function buildRows(Request $request): array
    {
        $user = auth()->user();

        $month        = (int) ($request->input('month') ?: now()->month);
        $year         = (int) ($request->input('year') ?: now()->year);
        $hostelId     = $request->input('hostel_id');
        $roomNo       = $request->input('room_no');
        $bedNo        = $request->input('bed_no');
        $filterStatus = strtoupper($request->input('status') ?? '');
        $search       = $request->input('search');

        // 1) Active residents
        $residentQuery = Resident::with(['hostel', 'room', 'bed'])
            ->where('status', 'ACTIVE')
            ->orderBy('name');

        if (!$user->isAdmin()) {
            $residentQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }
        if ($hostelId) {
            $residentQuery->where('hostel_id', $hostelId);
        }
        if ($roomNo) {
            $residentQuery->whereHas('room', fn($q) => $q->where('room_no', 'LIKE', "%{$roomNo}%"));
        }
        if ($bedNo) {
            $residentQuery->whereHas('bed', fn($q) => $q->where('bed_no', 'LIKE', "%{$bedNo}%"));
        }
        if ($search) {
            $residentQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $residents = $residentQuery->get();

        // 2) Bulk-load payments
        $residentIds = $residents->pluck('id')->toArray();
        $allPayments = Payment::whereIn('resident_id', $residentIds)->get();

        $paymentsByResident = [];
        foreach ($allPayments as $p) {
            $key = $p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT);
            $paymentsByResident[$p->resident_id][$key][] = $p;
        }

        // 3) Build rows
        $rows  = [];
        $stats = [
            'total' => 0, 'paid' => 0, 'partial' => 0,
            'pending' => 0, 'unpaid' => 0,
            'total_rent' => 0, 'total_paid' => 0,
            'total_balance' => 0,
            'paid_amount' => 0, 'partial_amount' => 0,
            'unpaid_amount' => 0, 'pending_amount' => 0,
            'total_date_discount' => 0,
        ];

        $selectedKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Date discount applies to the CURRENT month only (today's date)
        $dateDiscount = $this->getDateBasedDiscount();

        foreach ($residents as $resident) {
            // Skip if not joined by end of month
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
            if (Carbon::parse($resident->joining_date)->gt($endOfMonth)) {
                continue;
            }

            // Skip if vacated before month
            if ($resident->vacate_date) {
                $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
                if (Carbon::parse($resident->vacate_date)->lt($startOfMonth)) {
                    continue;
                }
            }

            // --- Previous pending ---
            $previousPending = 0;
            $previousPendingMonths = [];

            $joinMonth = Carbon::parse($resident->joining_date)->startOfMonth();
            $selectedMonthStart = Carbon::create($year, $month, 1)->startOfMonth();

            $cursor = $joinMonth->copy();
            while ($cursor->lt($selectedMonthStart)) {
                $key = $cursor->format('Y-m');
                $payments = $paymentsByResident[$resident->id][$key] ?? [];

                if (count($payments) > 0) {
                    $dueThisMonth = 0;
                    foreach ($payments as $p) {
                        $dueThisMonth += (float) $p->balance_amount;
                    }
                } else {
                    $dueThisMonth = (float) $resident->rent_amount;
                }

                if ($dueThisMonth > 0) {
                    $previousPending += $dueThisMonth;
                    $previousPendingMonths[] = [
                        'month'  => (int) $cursor->month,
                        'year'   => (int) $cursor->year,
                        'label'  => $cursor->format('M Y'),
                        'amount' => round($dueThisMonth, 2),
                    ];
                }

                $cursor->addMonth();
            }

            // --- Current month ---
            $currentPayments = $paymentsByResident[$resident->id][$selectedKey] ?? [];

            $currentPaid = 0;
            $currentRent = (float) $resident->rent_amount;
            $currentDiscount = 0;   // manual discount from DB
            $currentFine = 0;
            $currentBalanceFromRecords = 0;
            $paymentId = null;
            $receiptNo = 'N/A';
            $paymentDate = null;
            $paymentType = null;
            $remark = null;

            foreach ($currentPayments as $p) {
                $currentPaid     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                $currentDiscount += (float) $p->discount_amount;
                $currentFine     += (float) $p->fine_amount;
                $currentBalanceFromRecords += (float) $p->balance_amount;
                $paymentId       = $p->id;
                $receiptNo       = $p->receipt_no;
                $paymentDate     = $p->payment_date;
                $paymentType     = $p->payment_type;
                $remark          = $p->remark;
            }

            // ---- Apply date-based discount for current month ----
            // If a payment already exists for this month, we TRUST the DB discount
            // (so admin edited values are not overwritten). Otherwise we apply
            // today's date discount for preview/display.
            if (count($currentPayments) > 0) {
                $dateDiscountForRow = 0;   // already captured in $currentDiscount
            } else {
                $dateDiscountForRow = $dateDiscount;
            }

            $totalDiscount = $currentDiscount + $dateDiscountForRow;
            $currentDue    = max(0, $currentRent + $currentFine - $totalDiscount);

            if (count($currentPayments) > 0) {
                // recompute balance using the (possibly) updated due
                $currentBalance = max(0, $currentDue - $currentPaid);
            } else {
                $currentBalance = $currentDue;
            }

            // FIXED STATUS LOGIC — multiple flags per resident
            $hasPreviousPending = ($previousPending > 0);
            $hasCurrentPayments = (count($currentPayments) > 0);
            $hasCurrentUnpaid   = !$hasCurrentPayments;
            $hasCurrentPartial  = ($hasCurrentPayments && $currentBalance > 0);
            $hasCurrentPaid     = ($hasCurrentPayments && $currentBalance == 0);

            // Primary status
            if ($hasPreviousPending) {
                $status = 'PENDING';
            } elseif ($hasCurrentUnpaid) {
                $status = 'UNPAID';
            } elseif ($hasCurrentPartial) {
                $status = 'PARTIAL';
            } else {
                $status = 'PAID';
            }

            // Status filter
            if ($filterStatus && $filterStatus !== 'ALL') {
                $match = false;
                if ($filterStatus === 'PENDING' && $hasPreviousPending) $match = true;
                if ($filterStatus === 'UNPAID'  && $hasCurrentUnpaid)   $match = true;
                if ($filterStatus === 'PARTIAL' && $hasCurrentPartial)  $match = true;
                if ($filterStatus === 'PAID'    && $hasCurrentPaid)     $match = true;

                if (!$match) {
                    continue;
                }
            }

            $rows[] = [
                'resident_id'       => $resident->id,
                'resident_code'     => $resident->resident_code,
                'resident_name'     => $resident->name,
                'phone'             => $resident->phone,
                'profile_image'     => $resident->profile_image,
                'hostel_name'       => $resident->hostel->hostel_name ?? 'N/A',
                'hostel_id'         => $resident->hostel_id,
                'room_no'           => $resident->room->room_no ?? 'N/A',
                'bed_no'            => $resident->bed->bed_no ?? 'N/A',
                'joining_date'      => $resident->joining_date->format('d M Y'),
                'month'             => $month,
                'year'              => $year,
                'month_label'       => Carbon::create($year, $month, 1)->format('F Y'),
                'payment_id'        => $paymentId,
                'receipt_no'        => $receiptNo,
                'payment_date'      => $paymentDate ? Carbon::parse($paymentDate)->format('d M Y') : null,
                'payment_type'      => $paymentType,
                'remark'            => $remark,

                'rent_amount'       => round($currentRent, 2),
                'manual_discount'   => round($currentDiscount, 2),
                'date_discount'     => round($dateDiscountForRow, 2),
                'total_discount'    => round($totalDiscount, 2),
                'discount_amount'   => round($totalDiscount, 2),  // kept for backward compat
                'fine_amount'       => round($currentFine, 2),
                'current_paid'      => round($currentPaid, 2),
                'current_due'       => round($currentDue, 2),
                'current_balance'   => round($currentBalance, 2),

                'previous_pending'  => round($previousPending, 2),
                'previous_pending_months' => $previousPendingMonths,

                'total_due'         => round($previousPending + $currentBalance, 2),

                'status'               => $status,
                'has_previous_pending' => $hasPreviousPending,
                'has_current_unpaid'   => $hasCurrentUnpaid,
                'has_current_partial'  => $hasCurrentPartial,
                'has_current_paid'     => $hasCurrentPaid,
            ];

            // FIXED STATS — count in EVERY applicable category
            $stats['total']++;
            $stats['total_rent']    += $currentRent;
            $stats['total_paid']    += $currentPaid;
            $stats['total_balance'] += $previousPending + $currentBalance;
            $stats['total_date_discount'] += $dateDiscountForRow;

            if ($hasCurrentPaid) {
                $stats['paid']++;
                $stats['paid_amount'] += $currentPaid;
            }

            if ($hasCurrentPartial) {
                $stats['partial']++;
                $stats['partial_amount'] += $currentBalance;
            }

            if ($hasCurrentUnpaid) {
                $stats['unpaid']++;
                $stats['unpaid_amount'] += $currentDue;
            }

            if ($hasPreviousPending) {
                $stats['pending']++;
                $stats['pending_amount'] += $previousPending;
            }
        }

        // Sort
        $order = ['PENDING' => 1, 'UNPAID' => 2, 'PARTIAL' => 3, 'PAID' => 4];
        usort($rows, function ($a, $b) use ($order) {
            $oa = $order[$a['status']] ?? 99;
            $ob = $order[$b['status']] ?? 99;
            if ($oa === $ob) {
                return strcmp($a['resident_name'], $b['resident_name']);
            }
            return $oa <=> $ob;
        });

        return [
            'rows'        => $rows,
            'stats'       => $stats,
            'month'       => $month,
            'year'        => $year,
            'month_label' => Carbon::create($year, $month, 1)->format('F Y'),
            'hostel_id'   => $hostelId,
            'status'      => $filterStatus,
            'search'      => $search,
            'room_no'     => $roomNo,
            'bed_no'      => $bedNo,
        ];
    }

    /**
     * AJAX: Filter payments
     */
    public function filter(Request $request)
    {
        $result = $this->buildRows($request);

        return response()->json([
            'success'     => true,
            'rows'        => $result['rows'],
            'stats'       => $result['stats'],
            'month'       => $result['month'],
            'year'        => $result['year'],
            'month_label' => $result['month_label'],
        ]);
    }

    /**
     * Build filter label for export header
     */
    private function buildFilterLabel(array $result): string
    {
        $parts = [];
        $parts[] = $result['month_label'];

        if ($result['hostel_id']) {
            $hostel = Hostel::find($result['hostel_id']);
            $parts[] = $hostel ? $hostel->hostel_name : 'Hostel';
        } else {
            $parts[] = 'All Hostels';
        }

        if ($result['status']) {
            $parts[] = ucfirst(strtolower($result['status']));
        } else {
            $parts[] = 'All Statuses';
        }

        if ($result['room_no']) $parts[] = 'Room: ' . $result['room_no'];
        if ($result['bed_no']) $parts[] = 'Bed: ' . $result['bed_no'];
        if ($result['search']) $parts[] = 'Search: ' . $result['search'];

        return implode(' | ', $parts);
    }

    /**
     * Export as CSV (Excel-compatible)
     */
    public function exportCsv(Request $request)
    {
        $result = $this->buildRows($request);
        $rows = $result['rows'];
        $stats = $result['stats'];
        $filterLabel = $this->buildFilterLabel($result);

        $filename = 'payments-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $stats, $filterLabel) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['PAYMENT REPORT']);
            fputcsv($out, ['Filter: ' . $filterLabel]);
            fputcsv($out, ['Generated: ' . now()->format('d M Y H:i A')]);
            fputcsv($out, []);

            fputcsv($out, ['SUMMARY']);
            fputcsv($out, ['Fully Paid', $stats['paid'], '₹' . number_format($stats['paid_amount'], 2)]);
            fputcsv($out, ['Partial', $stats['partial'], '₹' . number_format($stats['partial_amount'], 2)]);
            fputcsv($out, ['Unpaid', $stats['unpaid'], '₹' . number_format($stats['unpaid_amount'], 2)]);
            fputcsv($out, ['Pending (Previous)', $stats['pending'], '₹' . number_format($stats['pending_amount'], 2)]);
            fputcsv($out, ['Date Discount (Total)', '', '₹' . number_format($stats['total_date_discount'], 2)]);
            fputcsv($out, ['Total Residents', $stats['total'], 'Balance: ₹' . number_format($stats['total_balance'], 2)]);
            fputcsv($out, []);

            fputcsv($out, [
                'S.No', 'Resident Code', 'Resident Name', 'Phone', 'Hostel', 'Room', 'Bed',
                'Month', 'Year', 'Rent', 'Manual Discount', 'Date Discount', 'Total Discount',
                'Fine', 'Paid', 'Current Balance', 'Previous Pending', 'Total Due', 'Status',
                'Payment Date', 'Receipt No', 'Remark',
            ]);

            $sno = 1;
            foreach ($rows as $r) {
                fputcsv($out, [
                    $sno++,
                    $r['resident_code'],
                    $r['resident_name'],
                    $r['phone'],
                    $r['hostel_name'],
                    $r['room_no'],
                    $r['bed_no'],
                    $r['month_label'],
                    $r['year'],
                    $r['rent_amount'],
                    $r['manual_discount'],
                    $r['date_discount'],
                    $r['total_discount'],
                    $r['fine_amount'],
                    $r['current_paid'],
                    $r['current_balance'],
                    $r['previous_pending'],
                    $r['total_due'],
                    $r['status'],
                    $r['payment_date'] ?? '—',
                    $r['receipt_no'],
                    $r['remark'] ?? '—',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export as PDF (print-friendly HTML view)
     */
    public function exportPdf(Request $request)
    {
        $result = $this->buildRows($request);
        $rows = $result['rows'];
        $stats = $result['stats'];
        $filterLabel = $this->buildFilterLabel($result);

        return view('admin.payments.export-pdf', [
            'rows'        => $rows,
            'stats'       => $stats,
            'filterLabel' => $filterLabel,
            'generatedAt' => now()->format('d M Y H:i A'),
        ]);
    }

    /**
     * Get rooms for a hostel (dropdown helper)
     */
    public function roomsByHostel($hostelId)
    {
        if (!auth()->user()->hasAccessToHostel($hostelId)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $rooms = Room::where('hostel_id', $hostelId)
            ->orderBy('room_no')
            ->get(['id', 'room_no']);

        return response()->json(['success' => true, 'rooms' => $rooms]);
    }

    /**
     * Get active residents for a room (dropdown helper)
     */
    public function residentsByRoom($roomId)
    {
        $room = Room::findOrFail($roomId);
        if (!auth()->user()->hasAccessToHostel($room->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $residents = Resident::where('room_id', $roomId)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name', 'resident_code', 'rent_amount']);

        return response()->json(['success' => true, 'residents' => $residents]);
    }

    /**
     * Store manual payment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resident_id'      => 'required|exists:residents,id',
            'month'            => 'required|integer|min:1|max:12',
            'year'             => 'required|integer|min:2020|max:2100',
            'rent_amount'      => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'fine_amount'      => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'nullable|numeric|min:0',
            'upi_paid_amount'  => 'nullable|numeric|min:0',
            'payment_date'     => 'required|date',
            'transaction_id'   => 'nullable|string|max:255',
            'remark'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $resident = Resident::findOrFail($request->resident_id);
        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        DB::beginTransaction();
        try {
            $rent     = (float) $request->rent_amount;
            $discount = (float) ($request->discount_amount ?? 0);
            $fine     = (float) ($request->fine_amount ?? 0);
            $cash     = (float) ($request->cash_paid_amount ?? 0);
            $upi      = (float) ($request->upi_paid_amount ?? 0);

            // Auto-add date discount for THIS month (only if admin didn't already include it)
            // Payment date-ku badhila TODAY's date use pannuthu
            $dateDiscount = $this->getDateBasedDiscount();

            $payable   = max(0, $rent + $fine - $discount - $dateDiscount);
            $totalPaid = $cash + $upi;
            $balance   = max(0, $payable - $totalPaid);

            if ($totalPaid <= 0) $status = 'PENDING';
            elseif ($balance > 0) $status = 'PARTIAL';
            else $status = 'PAID';

            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            $paymentType = $cash > 0 && $upi > 0 ? 'both' : ($cash > 0 ? 'cash' : ($upi > 0 ? 'upi' : 'none'));

            $existing = Payment::where('resident_id', $resident->id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            if ($existing) {
                $existing->update([
                    'rent_amount'      => $rent,
                    'discount_amount'  => $discount + $dateDiscount,   // store combined
                    'fine_amount'      => $fine,
                    'cash_paid_amount' => $cash,
                    'upi_paid_amount'  => $upi,
                    'balance_amount'   => $balance,
                    'payment_date'     => $request->payment_date,
                    'transaction_id'   => $request->transaction_id,
                    'payment_type'     => $paymentType,
                    'remark'           => $request->remark,
                    'status'           => $status,
                ]);
                $payment = $existing;
            } else {
                $payment = Payment::create([
                    'resident_id'      => $resident->id,
                    'receipt_no'       => $receiptNo,
                    'month'            => $request->month,
                    'year'             => $request->year,
                    'rent_amount'      => $rent,
                    'discount_amount'  => $discount + $dateDiscount,   // store combined
                    'fine_amount'      => $fine,
                    'cash_paid_amount' => $cash,
                    'upi_paid_amount'  => $upi,
                    'balance_amount'   => $balance,
                    'payment_date'     => $request->payment_date,
                    'transaction_id'   => $request->transaction_id,
                    'payment_type'     => $paymentType,
                    'remark'           => $request->remark,
                    'status'           => $status,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment saved! Receipt: ' . $payment->receipt_no
                             . ($dateDiscount > 0 ? ' (Date discount ₹' . $dateDiscount . ' applied)' : ''),
                'payment' => $payment,
                'date_discount_applied' => $dateDiscount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get single payment
     */
    public function show($id)
    {
        $payment = Payment::with(['resident.hostel', 'resident.room', 'resident.bed'])->findOrFail($id);

        if (!auth()->user()->hasAccessToHostel($payment->resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        return response()->json(['success' => true, 'payment' => $payment]);
    }

    /**
     * Update payment
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        if (!auth()->user()->hasAccessToHostel($payment->resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rent_amount'      => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'fine_amount'      => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'nullable|numeric|min:0',
            'upi_paid_amount'  => 'nullable|numeric|min:0',
            'payment_date'     => 'required|date',
            'transaction_id'   => 'nullable|string|max:255',
            'remark'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $rent     = (float) $request->rent_amount;
            $discount = (float) ($request->discount_amount ?? 0);
            $fine     = (float) ($request->fine_amount ?? 0);
            $cash     = (float) ($request->cash_paid_amount ?? 0);
            $upi      = (float) ($request->upi_paid_amount ?? 0);

            // Date discount is NOT auto-applied on update — admin has full control here
            $payable   = max(0, $rent + $fine - $discount);
            $totalPaid = $cash + $upi;
            $balance   = max(0, $payable - $totalPaid);

            if ($totalPaid <= 0) $status = 'PENDING';
            elseif ($balance > 0) $status = 'PARTIAL';
            else $status = 'PAID';

            $paymentType = $cash > 0 && $upi > 0 ? 'both' : ($cash > 0 ? 'cash' : ($upi > 0 ? 'upi' : 'none'));

            $payment->update([
                'rent_amount'      => $rent,
                'discount_amount'  => $discount,
                'fine_amount'      => $fine,
                'cash_paid_amount' => $cash,
                'upi_paid_amount'  => $upi,
                'balance_amount'   => $balance,
                'payment_date'     => $request->payment_date,
                'transaction_id'   => $request->transaction_id,
                'payment_type'     => $paymentType,
                'remark'           => $request->remark,
                'status'           => $status,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Payment updated!', 'payment' => $payment]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        if (!auth()->user()->hasAccessToHostel($payment->resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }
        $payment->delete();
        return response()->json(['success' => true, 'message' => 'Payment deleted!']);
    }
}
