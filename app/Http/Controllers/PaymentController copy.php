<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use App\Models\Room;
use App\Http\Controllers\EsslController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private const DATE_DISCOUNT_1_5  = 250.0;
    private const DATE_DISCOUNT_6_10 = 125.0;

    /* =========================================================
     |  PAGE
     ========================================================= */

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

    /* =========================================================
     |  DATE DISCOUNT (based on a given date)
     ========================================================= */

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
     * Date discount for a HISTORICAL month.
     * Uses day-01 of that month → since payment was expected early.
     * If a payment row exists for that month, we trust the DB value instead.
     */
    private function getHistoricalMonthDiscount(int $month, int $year): float
    {
        $asOf = Carbon::create($year, $month, 1);
        return $this->getDateBasedDiscount($asOf);
    }

    /* =========================================================
     |  BUILD ROWS
     ========================================================= */

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

        $residentIds = $residents->pluck('id')->toArray();
        $allPayments = Payment::whereIn('resident_id', $residentIds)->get();

        $paymentsByResident = [];
        foreach ($allPayments as $p) {
            $key = $p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT);
            $paymentsByResident[$p->resident_id][$key][] = $p;
        }

        $rows  = [];
        $stats = [
            'total' => 0,
            'paid' => 0,
            'partial' => 0,
            'pending' => 0,
            'unpaid' => 0,
            'total_rent' => 0,
            'total_paid' => 0,
            'total_balance' => 0,
            'paid_amount' => 0,
            'partial_amount' => 0,
            'unpaid_amount' => 0,
            'pending_amount' => 0,
            'total_date_discount' => 0,
        ];

        $selectedKey   = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $todayDiscount = $this->getDateBasedDiscount();

        foreach ($residents as $resident) {
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
            if (Carbon::parse($resident->joining_date)->gt($endOfMonth)) {
                continue;
            }

            if ($resident->vacate_date) {
                $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
                if (Carbon::parse($resident->vacate_date)->lt($startOfMonth)) {
                    continue;
                }
            }

            /* ────────── Previous pending (with date discount) ────────── */
            $previousPending       = 0;
            $previousPendingMonths = [];

            $joinMonth          = Carbon::parse($resident->joining_date)->startOfMonth();
            $selectedMonthStart = Carbon::create($year, $month, 1)->startOfMonth();

            $cursor = $joinMonth->copy();
            while ($cursor->lt($selectedMonthStart)) {
                $key      = $cursor->format('Y-m');
                $payments = $paymentsByResident[$resident->id][$key] ?? [];

                if (count($payments) > 0) {
                    // Payment exists → read saved balance (already includes discount)
                    $dueThisMonth = 0;
                    foreach ($payments as $p) {
                        $dueThisMonth += (float) $p->balance_amount;
                    }
                } else {
                    // No payment → apply that month's discount (using day-01)
                    $monthDiscount = $this->getHistoricalMonthDiscount(
                        (int) $cursor->month,
                        (int) $cursor->year
                    );
                    $dueThisMonth = max(0, (float) $resident->rent_amount - $monthDiscount);
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

            /* ────────── Current month ────────── */
            $currentPayments = $paymentsByResident[$resident->id][$selectedKey] ?? [];

            $currentPaid    = 0;
            $currentRent    = (float) $resident->rent_amount;
            $storedDiscount = 0;
            $currentFine    = 0;
            $paymentId      = null;
            $receiptNo      = 'N/A';
            $paymentDate    = null;
            $paymentType    = null;
            $remark         = null;

            foreach ($currentPayments as $p) {
                $currentPaid    += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                $storedDiscount += (float) $p->discount_amount;
                $currentFine    += (float) $p->fine_amount;

                $paymentId   = $p->id;
                $receiptNo   = $p->receipt_no;
                $paymentDate = $p->payment_date;
                $paymentType = $p->payment_type;
                $remark      = $p->remark;
            }

            /* ✅ KEY FIX:
             |  - Payment exists → use SAVED discount from DB (no recompute)
             |  - No payment yet → apply TODAY's date discount as preview
             */
            if (count($currentPayments) > 0) {
                $totalDiscount = $storedDiscount;
            } else {
                $totalDiscount = $todayDiscount;
            }

            $currentDue = max(0, $currentRent + $currentFine - $totalDiscount);

            if (count($currentPayments) > 0) {
                $currentBalance = max(0, $currentDue - $currentPaid);
            } else {
                $currentBalance = $currentDue;
            }

            /* ✅ Split for display — frontend combined value handle pannuthu
             |  Logic:
             |    - storedDiscount = 0  → manual = 0, date = 0
             |    - storedDiscount > 0 & savedDateDisc > 0  → date discount included
             |      • storedDiscount >= savedDateDisc → date = savedDateDisc, manual = diff
             |      • storedDiscount <  savedDateDisc → date = storedDiscount, manual = 0
             |    - storedDiscount > 0 & savedDateDisc == 0 → manual = storedDiscount, date = 0
             */
            $manualDiscount = 0;
            $dateDiscount   = 0;

            if (count($currentPayments) > 0) {
                $savedDateDisc = $paymentDate
                    ? $this->getDateBasedDiscount(Carbon::parse($paymentDate))
                    : 0;

                if ($savedDateDisc > 0 && $storedDiscount >= $savedDateDisc) {
                    // Frontend combined value → split it
                    $dateDiscount   = $savedDateDisc;
                    $manualDiscount = $storedDiscount - $savedDateDisc;
                } elseif ($savedDateDisc > 0 && $storedDiscount < $savedDateDisc) {
                    // Less than date discount → treat all as date discount
                    $dateDiscount   = $storedDiscount;
                    $manualDiscount = 0;
                } else {
                    // No date discount applicable → all manual
                    $dateDiscount   = 0;
                    $manualDiscount = $storedDiscount;
                }
            } else {
                $manualDiscount = 0;
                $dateDiscount   = $todayDiscount;
            }

            $hasPreviousPending = ($previousPending > 0);
            $hasCurrentPayments = (count($currentPayments) > 0);
            $hasCurrentUnpaid   = !$hasCurrentPayments;
            $hasCurrentPartial  = ($hasCurrentPayments && $currentBalance > 0);
            $hasCurrentPaid     = ($hasCurrentPayments && $currentBalance == 0);

            if ($hasPreviousPending) {
                $status = 'PENDING';
            } elseif ($hasCurrentUnpaid) {
                $status = 'UNPAID';
            } elseif ($hasCurrentPartial) {
                $status = 'PARTIAL';
            } else {
                $status = 'PAID';
            }

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
                'manual_discount'   => round($manualDiscount, 2),
                'date_discount'     => round($dateDiscount, 2),
                'total_discount'    => round($totalDiscount, 2),
                'discount_amount'   => round($totalDiscount, 2),
                'fine_amount'       => round($currentFine, 2),
                'current_paid'      => round($currentPaid, 2),
                'current_due'       => round($currentDue, 2),
                'current_balance'   => round($currentBalance, 2),

                'previous_pending'        => round($previousPending, 2),
                'previous_pending_months' => $previousPendingMonths,

                'total_due' => round($previousPending + $currentBalance, 2),

                'status'               => $status,
                'has_previous_pending' => $hasPreviousPending,
                'has_current_unpaid'   => $hasCurrentUnpaid,
                'has_current_partial'  => $hasCurrentPartial,
                'has_current_paid'     => $hasCurrentPaid,
            ];

            $stats['total']++;
            $stats['total_rent']    += $currentRent;
            $stats['total_paid']    += $currentPaid;
            $stats['total_balance'] += $previousPending + $currentBalance;
            $stats['total_date_discount'] += $dateDiscount;

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

    /* =========================================================
     |  FILTER / EXPORT
     ========================================================= */

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

    private function buildFilterLabel(array $result): string
    {
        $parts   = [];
        $parts[] = $result['month_label'];

        if ($result['hostel_id']) {
            $hostel  = Hostel::find($result['hostel_id']);
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
        if ($result['bed_no'])  $parts[] = 'Bed: '  . $result['bed_no'];
        if ($result['search'])  $parts[] = 'Search: ' . $result['search'];

        return implode(' | ', $parts);
    }

    public function exportCsv(Request $request)
    {
        $result      = $this->buildRows($request);
        $rows        = $result['rows'];
        $stats       = $result['stats'];
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
            fputcsv($out, ['Fully Paid',            $stats['paid'],    '₹' . number_format($stats['paid_amount'], 2)]);
            fputcsv($out, ['Partial',               $stats['partial'], '₹' . number_format($stats['partial_amount'], 2)]);
            fputcsv($out, ['Unpaid',                $stats['unpaid'],  '₹' . number_format($stats['unpaid_amount'], 2)]);
            fputcsv($out, ['Pending (Previous)',    $stats['pending'], '₹' . number_format($stats['pending_amount'], 2)]);
            fputcsv($out, ['Date Discount (Total)', '',                '₹' . number_format($stats['total_date_discount'], 2)]);
            fputcsv($out, ['Total Residents',       $stats['total'],   'Balance: ₹' . number_format($stats['total_balance'], 2)]);
            fputcsv($out, []);

            fputcsv($out, [
                'S.No',
                'Resident Code',
                'Resident Name',
                'Phone',
                'Hostel',
                'Room',
                'Bed',
                'Month',
                'Year',
                'Rent',
                'Manual Discount',
                'Date Discount',
                'Total Discount',
                'Fine',
                'Paid',
                'Current Balance',
                'Previous Pending',
                'Total Due',
                'Status',
                'Payment Date',
                'Receipt No',
                'Remark',
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

    public function exportPdf(Request $request)
    {
        $result      = $this->buildRows($request);
        $rows        = $result['rows'];
        $stats       = $result['stats'];
        $filterLabel = $this->buildFilterLabel($result);

        return view('admin.payments.export-pdf', [
            'rows'        => $rows,
            'stats'       => $stats,
            'filterLabel' => $filterLabel,
            'generatedAt' => now()->format('d M Y H:i A'),
        ]);
    }

    /* =========================================================
     |  DROPDOWN HELPERS
     ========================================================= */

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

    /* =========================================================
     |  STORE
     ========================================================= */

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

            // Date discount based on payment_date
            $dateDiscount = $this->getDateBasedDiscount(
                $request->payment_date ? Carbon::parse($request->payment_date) : null
            );

            /* ✅ DISCOUNT LOGIC:
             |  - Frontend discount > 0 → trust it (already combined manual + date)
             |  - Frontend discount = 0 → auto-apply date discount
             */
            if ($discount > 0) {
                $totalDiscount = $discount;                    // Frontend combined value
            } else {
                $totalDiscount = $discount + $dateDiscount;   // 0 + auto date
            }

            $payable   = max(0, $rent + $fine - $totalDiscount);
            $totalPaid = $cash + $upi;
            $balance   = max(0, $payable - $totalPaid);

            if ($totalPaid <= 0) {
                $status = 'PENDING';
            } elseif ($balance > 0) {
                $status = 'PARTIAL';
            } else {
                $status = 'PAID';
            }

            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            $paymentType = $cash > 0 && $upi > 0
                ? 'both'
                : ($cash > 0 ? 'cash' : ($upi > 0 ? 'upi' : 'none'));

            $existing = Payment::where('resident_id', $resident->id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            $payload = [
                'rent_amount'      => $rent,
                'discount_amount'  => $totalDiscount,
                'fine_amount'      => $fine,
                'cash_paid_amount' => $cash,
                'upi_paid_amount'  => $upi,
                'balance_amount'   => $balance,
                'payment_date'     => $request->payment_date,
                'transaction_id'   => $request->transaction_id,
                'payment_type'     => $paymentType,
                'remark'           => $request->remark,
                'status'           => $status,
            ];

            if ($existing) {
                $existing->update($payload);
                $payment = $existing;
            } else {
                $payment = Payment::create(array_merge($payload, [
                    'resident_id' => $resident->id,
                    'receipt_no'  => $receiptNo,
                    'month'       => $request->month,
                    'year'        => $request->year,
                ]));
            }

            DB::commit();

            $this->syncAccessAfterPayment($resident);

            // ✅ Response: applied discount info correct-a kaata
            $appliedDateDiscount = ($discount > 0) ? 0 : $dateDiscount;

            return response()->json([
                'success' => true,
                'message' => 'Payment saved! Receipt: ' . $payment->receipt_no
                    . ($appliedDateDiscount > 0 ? ' (Date discount ₹' . $appliedDateDiscount . ' applied)' : ''),
                'payment' => $payment,
                'date_discount_applied' => $appliedDateDiscount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     |  SHOW
     ========================================================= */

    public function show($id)
    {
        $payment = Payment::with(['resident.hostel', 'resident.room', 'resident.bed'])->findOrFail($id);

        if (!auth()->user()->hasAccessToHostel($payment->resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        return response()->json(['success' => true, 'payment' => $payment]);
    }

    /* =========================================================
     |  UPDATE
     ========================================================= */

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

            // Date discount based on ORIGINAL payment_date
            $originalDateDisc = $this->getDateBasedDiscount(
                $payment->payment_date ? Carbon::parse($payment->payment_date) : null
            );

            /* ✅ DISCOUNT LOGIC (same as store):
             |  - Frontend discount > 0 → trust it (already combined manual + date)
             |  - Frontend discount = 0 → auto-apply date discount
             */
            if ($discount > 0) {
                $totalDiscount = $discount;                          // Frontend combined value
            } else {
                $totalDiscount = $discount + $originalDateDisc;     // 0 + auto date
            }

            $payable   = max(0, $rent + $fine - $totalDiscount);
            $totalPaid = $cash + $upi;
            $balance   = max(0, $payable - $totalPaid);

            if ($totalPaid <= 0) {
                $status = 'PENDING';
            } elseif ($balance > 0) {
                $status = 'PARTIAL';
            } else {
                $status = 'PAID';
            }

            $paymentType = $cash > 0 && $upi > 0
                ? 'both'
                : ($cash > 0 ? 'cash' : ($upi > 0 ? 'upi' : 'none'));

            $payment->update([
                'rent_amount'      => $rent,
                'discount_amount'  => $totalDiscount,
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

            $this->syncAccessAfterPayment($payment->resident);

            return response()->json([
                'success' => true,
                'message' => 'Payment updated!',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     |  DELETE
     ========================================================= */

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        if (!auth()->user()->hasAccessToHostel($payment->resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        // ✅ FIX: resident-a delete-ku munnaadi store pannanum
        $resident = $payment->resident;
        $payment->delete();

        if ($resident) {
            $this->syncAccessAfterPayment($resident);
        }

        return response()->json(['success' => true, 'message' => 'Payment deleted!']);
    }

    /* =========================================================
     |  🔑 Auto-Sync Access
     ========================================================= */

    private function syncAccessAfterPayment(Resident $resident): void
    {
        try {
            /** @var EsslController $essl */
            $essl = app(EsslController::class);

            $now          = now();
            $currentMonth = (int) $now->month;
            $currentYear  = (int) $now->year;

            $prevDate  = $now->copy()->subMonthNoOverflow();
            $prevMonth = (int) $prevDate->month;
            $prevYear  = (int) $prevDate->year;

            $currentPaid = $this->isMonthFullyPaid($resident, $currentMonth, $currentYear);

            $joinDate = $resident->joining_date
                ? Carbon::parse($resident->joining_date)
                : null;

            $checkPrevMonth = true;
            if ($joinDate && $joinDate->year === $currentYear && $joinDate->month === $currentMonth) {
                $checkPrevMonth = false;
            }

            $prevPaid = $checkPrevMonth
                ? $this->isMonthFullyPaid($resident, $prevMonth, $prevYear)
                : true;

            $shouldUnblock = $currentPaid && $prevPaid;

            Log::info('syncAccessAfterPayment decision', [
                'resident_id'    => $resident->id,
                'current_paid'   => $currentPaid,
                'prev_paid'      => $prevPaid,
                'should_unblock' => $shouldUnblock,
                'current'        => "{$currentYear}-{$currentMonth}",
                'previous'       => "{$prevYear}-{$prevMonth}",
            ]);

            if ($shouldUnblock) {
                $essl->syncResidentAccess($resident, false);  // UNBLOCK
            } else {
                $essl->syncResidentAccess($resident, true);   // BLOCK
            }
        } catch (\Throwable $e) {
            Log::error('syncAccessAfterPayment failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Is the given month fully paid for the resident?
     */
    private function isMonthFullyPaid(Resident $resident, int $month, int $year): bool
    {
        $payment = Payment::where('resident_id', $resident->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payment) {
            return false;
        }

        $totalPaid = (float) $payment->cash_paid_amount + (float) $payment->upi_paid_amount;
        $balance   = (float) $payment->balance_amount;

        return $balance <= 0 && $totalPaid > 0;
    }
}
