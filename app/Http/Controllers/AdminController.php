<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Resident;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $hostelIds = $hostels->pluck('id')->toArray();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

        if (empty($hostelIds)) {
            $hostelIds = [0];
        }

        // ============================================================
        // STATISTICS
        // ============================================================

        $totalHostels = $hostels->count();
        $totalRooms = Room::whereIn('hostel_id', $hostelIds)->count();
        $totalBeds = Bed::whereHas('room', fn($q) => $q->whereIn('hostel_id', $hostelIds))->count();
        $totalResidents = Resident::whereIn('hostel_id', $hostelIds)->where('status', 'ACTIVE')->count();
        $totalVacated = Resident::whereIn('hostel_id', $hostelIds)->where('status', 'VACATED')->count();

        $occupiedBeds = Bed::whereHas('room', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->where('status', 'OCCUPIED')->count();
        $vacantBeds = Bed::whereHas('room', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->where('status', 'VACANT')->count();
        $blockedBeds = Bed::whereHas('room', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->where('status', 'BLOCKED')->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        // ============================================================
        // PAYMENT STATISTICS — CURRENT MONTH
        // (Uses the SAME logic as PaymentController@buildRows)
        // ============================================================

        $now = Carbon::now();
        $currentMonth = (int) $now->month;
        $currentYear  = (int) $now->year;
        $monthLabel   = Carbon::create($currentYear, $currentMonth, 1)->format('F Y');

        // ✅ 1) Fetch active residents
        $activeResidents = Resident::whereIn('hostel_id', $hostelIds)
            ->where('status', 'ACTIVE')
            ->with(['room', 'hostel'])
            ->get();

        $residentIds = $activeResidents->pluck('id')->toArray();

        // ✅ 2) Bulk-load ALL payments for these residents (not just current month)
        //    We need previous months too, for accurate previous pending
        $allPayments = Payment::whereIn('resident_id', $residentIds)->get();

        // Group by resident → month key
        $paymentsByResident = [];
        foreach ($allPayments as $p) {
            $key = $p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT);
            $paymentsByResident[$p->resident_id][$key][] = $p;
        }

        $selectedKey = $currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT);

        $totalPending = 0;
        $pendingResidents = 0;
        $paidResidents = 0;
        $partialResidents = 0;
        $totalRentForActiveResidents = 0;
        $totalCollected = 0;
        $pendingDetails = [];

        foreach ($activeResidents as $resident) {
            $currentRent = (float) ($resident->rent_amount ?? 0);
            $totalRentForActiveResidents += $currentRent;

            // ===== Previous Pending (walk from joining_date) =====
            $previousPending = 0;

            $joinMonth = Carbon::parse($resident->joining_date)->startOfMonth();
            $selectedMonthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();

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
                }

                $cursor->addMonth();
            }

            // ===== Current Month =====
            $currentPayments = $paymentsByResident[$resident->id][$selectedKey] ?? [];

            $currentPaid = 0;
            $currentBalanceFromRecords = 0;

            foreach ($currentPayments as $p) {
                $currentPaid += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                $currentBalanceFromRecords += (float) $p->balance_amount;
            }

            if (count($currentPayments) > 0) {
                $currentBalance = $currentBalanceFromRecords;
            } else {
                $currentBalance = $currentRent;
            }

            // ===== Status =====
            $status = 'UNPAID';
            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (count($currentPayments) === 0) {
                $status = 'UNPAID';
            } elseif ($currentBalance > 0) {
                $status = 'PARTIAL';
            } else {
                $status = 'PAID';
            }

            // ===== Accumulate =====
            $totalCollected += $currentPaid;

            if ($status === 'PAID') {
                $paidResidents++;
            } elseif ($status === 'PARTIAL') {
                $partialResidents++;
                $totalPending += $currentBalance;
                $pendingDetails[] = [
                    'resident' => $resident->name,
                    'rent'     => $currentRent,
                    'balance'  => $currentBalance,
                    'status'   => 'PARTIAL',
                ];
            } elseif ($status === 'PENDING') {
                $pendingResidents++;
                $totalPending += $previousPending + $currentBalance;
                $pendingDetails[] = [
                    'resident' => $resident->name,
                    'rent'     => $currentRent,
                    'balance'  => $previousPending + $currentBalance,
                    'status'   => 'PENDING',
                ];
            } elseif ($status === 'UNPAID') {
                $pendingResidents++;
                $totalPending += $currentRent;
                $pendingDetails[] = [
                    'resident' => $resident->name,
                    'rent'     => $currentRent,
                    'balance'  => $currentRent,
                    'status'   => 'NO_PAYMENT',
                ];
            }
        }

        // Add "previous pending" for PAID residents too (they might have had old dues)
        foreach ($activeResidents as $resident) {
            // Already counted in loop above
            // Just ensure we don't double count
        }

        $totalPayments = Payment::whereHas('resident', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->count();

        $paidCount = $paidResidents;
        $pendingCount = $pendingResidents;
        $partialCount = $partialResidents;

        $totalBalance = $totalPending;
        $totalRent = $totalRentForActiveResidents;

        $totalPendingAlternative = $totalRentForActiveResidents - $totalCollected;

        // ============================================================
        // MONTHLY PAYMENTS CHART (Last 6 months)
        // ============================================================

        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

        $monthlyPayments = Payment::whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            })
            ->where('payment_date', '>=', $sixMonthsAgo)
            ->get()
            ->groupBy(fn($p) => $p->payment_date->format('Y-m'))
            ->map(function ($group) {
                $collected = $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount');
                $rent      = $group->sum('rent_amount');
                return [
                    'month'           => $group->first()->payment_date->month,
                    'year'            => $group->first()->payment_date->year,
                    'total_collected' => $collected,
                    'total_rent'      => $rent,
                    'total_balance'   => max($rent - $collected, 0),
                ];
            })
            ->values()
            ->sortBy(fn($item) => $item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT))
            ->take(6);

        $months = [];
        $collections = [];
        $balances = [];
        foreach ($monthlyPayments as $payment) {
            $months[]      = date('M', mktime(0, 0, 0, $payment['month'], 1));
            $collections[] = round($payment['total_collected'] / 100000, 1);
            $balances[]    = round($payment['total_balance'] / 100000, 1);
        }

        if (empty($months)) {
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $months[] = $date->format('M');
                $collections[] = 0;
                $balances[] = 0;
            }
        }

        // ============================================================
        // RECENT TRANSACTIONS
        // ============================================================

        $recentPayments = Payment::with(['resident', 'resident.hostel'])
            ->whereHas('resident', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentResidents = Resident::with(['hostel', 'room', 'bed'])
            ->whereIn('hostel_id', $hostelIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================================
        // HOSTEL WISE STATISTICS
        // ============================================================

        $hostelStats = [];
        foreach ($hostels as $hostel) {
            $residentCount = Resident::where('hostel_id', $hostel->id)->where('status', 'ACTIVE')->count();
            $roomCount = Room::where('hostel_id', $hostel->id)->count();
            $bedCount = Bed::whereHas('room', fn($q) => $q->where('hostel_id', $hostel->id))->count();
            $occupiedCount = Bed::whereHas('room', fn($q) => $q->where('hostel_id', $hostel->id))
                ->where('status', 'OCCUPIED')->count();

            $hostelActiveResidents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->get();

            $hostelResidentIds = $hostelActiveResidents->pluck('id')->toArray();

            $hostelAllPayments = Payment::whereIn('resident_id', $hostelResidentIds)->get();
            $hostelPaymentsByResident = [];
            foreach ($hostelAllPayments as $p) {
                $key = $p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT);
                $hostelPaymentsByResident[$p->resident_id][$key][] = $p;
            }

            $hostelCollected = 0;
            $hostelPending = 0;
            $hostelPaidCount = 0;
            $hostelPendingCount = 0;
            $hostelPartialCount = 0;

            foreach ($hostelActiveResidents as $resident) {
                $currentRent = (float) ($resident->rent_amount ?? 0);

                // Previous pending
                $prevPending = 0;
                $joinMonth = Carbon::parse($resident->joining_date)->startOfMonth();
                $selStart = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
                $cursor = $joinMonth->copy();
                while ($cursor->lt($selStart)) {
                    $key = $cursor->format('Y-m');
                    $payments = $hostelPaymentsByResident[$resident->id][$key] ?? [];
                    if (count($payments) > 0) {
                        $due = 0;
                        foreach ($payments as $p) $due += (float) $p->balance_amount;
                    } else {
                        $due = (float) $resident->rent_amount;
                    }
                    if ($due > 0) $prevPending += $due;
                    $cursor->addMonth();
                }

                // Current
                $currentPayments = $hostelPaymentsByResident[$resident->id][$selectedKey] ?? [];
                $paid = 0;
                $bal = 0;
                foreach ($currentPayments as $p) {
                    $paid += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                    $bal  += (float) $p->balance_amount;
                }
                $currentBalance = count($currentPayments) > 0 ? $bal : $currentRent;

                $hostelCollected += $paid;

                // Status
                if ($prevPending > 0) {
                    $hostelPendingCount++;
                    $hostelPending += $prevPending + $currentBalance;
                } elseif (count($currentPayments) === 0) {
                    $hostelPendingCount++;
                    $hostelPending += $currentRent;
                } elseif ($currentBalance > 0) {
                    $hostelPartialCount++;
                    $hostelPending += $currentBalance;
                } else {
                    $hostelPaidCount++;
                }
            }

            $hostelStats[] = [
                'name'            => $hostel->hostel_name,
                'code'            => $hostel->hostel_code,
                'residents'       => $residentCount,
                'rooms'           => $roomCount,
                'beds'            => $bedCount,
                'occupied'        => $occupiedCount,
                'occupancy_rate'  => $bedCount > 0 ? round(($occupiedCount / $bedCount) * 100, 1) : 0,
                'collected'       => $hostelCollected,
                'pending'         => $hostelPending,
                'paid_count'      => $hostelPaidCount,
                'pending_count'   => $hostelPendingCount,
                'partial_count'   => $hostelPartialCount,
            ];
        }

        // ============================================================
        // ROOM TYPE / BED TYPE / STATUS DISTRIBUTION
        // ============================================================

        $roomTypeDistribution = RoomType::whereHas('hostel', fn($q) => $q->whereIn('id', $hostelIds))
            ->select('room_type_name', DB::raw('count(*) as total'))
            ->groupBy('room_type_name')
            ->get();

        $bedTypeDistribution = Bed::whereHas('room', fn($q) => $q->whereIn('hostel_id', $hostelIds))
            ->select('bed_type', DB::raw('count(*) as total'))
            ->groupBy('bed_type')
            ->get();

        $statusDistribution = Resident::whereIn('hostel_id', $hostelIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // ============================================================
        // CALCULATION SUMMARY
        // ============================================================

        $calculationSummary = [
            'month'                             => $monthLabel,
            'total_active_residents'            => $activeResidents->count(),
            'total_rent_for_active_residents'   => $totalRentForActiveResidents,
            'total_collected'                   => $totalCollected,
            'total_pending'                     => $totalPending,
            'total_pending_alternative'         => $totalPendingAlternative,
            'paid_count'                        => $paidCount,
            'pending_count'                     => $pendingCount,
            'partial_count'                     => $partialCount,
            'payment_count'                     => $totalPayments,
            'residents_with_payments'           => count(array_unique(array_column($pendingDetails, 'resident'))),
            'pending_details'                   => $pendingDetails,
        ];

        $currentUser = auth()->user();

        return view('main.admin.dashboard', compact(
            'hostels',
            'totalHostels',
            'totalRooms',
            'totalBeds',
            'totalResidents',
            'totalVacated',
            'occupiedBeds',
            'vacantBeds',
            'blockedBeds',
            'occupancyRate',
            'totalPayments',
            'totalCollected',
            'totalPending',
            'totalBalance',
            'totalRent',
            'pendingCount',
            'partialCount',
            'paidCount',
            'months',
            'collections',
            'balances',
            'recentPayments',
            'recentResidents',
            'hostelStats',
            'roomTypeDistribution',
            'bedTypeDistribution',
            'statusDistribution',
            'currentUser',
            'calculationSummary'
        ));
    }
}
