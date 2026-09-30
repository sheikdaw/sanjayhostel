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
        // ============================================================

        $now = Carbon::now();
        $currentMonth = (int) $now->month;
        $currentYear  = (int) $now->year;
        $monthLabel   = Carbon::create($currentYear, $currentMonth, 1)->format('F Y');

        // ✅ FIX: Fetch payments by month + year (NOT payment_date)
        $currentMonthPayments = Payment::whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            })
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $totalPayments = $currentMonthPayments->count();
        $totalCollected = $currentMonthPayments->sum('cash_paid_amount') + $currentMonthPayments->sum('upi_paid_amount');

        // Group payments by resident for quick lookup
        $paymentsByResident = [];
        foreach ($currentMonthPayments as $p) {
            if (!isset($paymentsByResident[$p->resident_id])) {
                $paymentsByResident[$p->resident_id] = [];
            }
            $paymentsByResident[$p->resident_id][] = $p;
        }

        $activeResidents = Resident::whereIn('hostel_id', $hostelIds)
            ->where('status', 'ACTIVE')
            ->with(['room', 'hostel'])
            ->get();

        // ============================================================
        // PENDING CALCULATION
        // ============================================================

        $totalPending = 0;
        $pendingResidents = 0;
        $paidResidents = 0;
        $partialResidents = 0;
        $totalRentForActiveResidents = 0;
        $pendingDetails = [];

        foreach ($activeResidents as $resident) {
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            $totalRentForActiveResidents += $rentAmount;

            $payments = $paymentsByResident[$resident->id] ?? [];

            if (count($payments) > 0) {
                // ✅ FIX: Use `balance_amount` from payment record (respects discount)
                $paidSoFar = 0;
                $storedBalance = 0;
                foreach ($payments as $p) {
                    $paidSoFar     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                    $storedBalance += (float) $p->balance_amount;
                }

                // Use stored balance (which already accounts for discount + fine)
                $balance = max($storedBalance, 0);

                if ($balance > 0) {
                    if ($paidSoFar > 0) {
                        $partialResidents++;
                        $pendingDetails[] = [
                            'resident' => $resident->name,
                            'rent'     => $rentAmount,
                            'balance'  => $balance,
                            'status'   => 'PARTIAL',
                        ];
                    } else {
                        $pendingResidents++;
                        $pendingDetails[] = [
                            'resident' => $resident->name,
                            'rent'     => $rentAmount,
                            'balance'  => $balance,
                            'status'   => 'PENDING',
                        ];
                    }
                    $totalPending += $balance;
                } else {
                    $paidResidents++;
                }
            } else {
                // ✅ FIX: No payment row → full rent is due
                $pendingResidents++;
                $totalPending += $rentAmount;
                $pendingDetails[] = [
                    'resident' => $resident->name,
                    'rent'     => $rentAmount,
                    'balance'  => $rentAmount,
                    'status'   => 'NO_PAYMENT',
                ];
            }
        }

        $totalPendingAlternative = $totalRentForActiveResidents - $totalCollected;

        $paidCount     = $paidResidents;
        $pendingCount  = $pendingResidents;
        $partialCount  = $partialResidents;

        $totalBalance = $totalPending;
        $totalRent = $currentMonthPayments->sum('rent_amount');

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
                ->with('room')
                ->get();

            // ✅ FIX: Hostel-wise payments by month/year
            $hostelCurrentMonthPayments = Payment::whereHas('resident', function ($q) use ($hostel) {
                    $q->where('hostel_id', $hostel->id);
                })
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->get();

            $hostelPaymentsByResident = [];
            foreach ($hostelCurrentMonthPayments as $p) {
                $hostelPaymentsByResident[$p->resident_id][] = $p;
            }

            $hostelCollected = $hostelCurrentMonthPayments->sum('cash_paid_amount') + $hostelCurrentMonthPayments->sum('upi_paid_amount');

            $hostelPending = 0;
            $hostelPaidCount = 0;
            $hostelPendingCount = 0;
            $hostelPartialCount = 0;

            foreach ($hostelActiveResidents as $resident) {
                $rentAmount = (float) ($resident->rent_amount ?? 0);
                $payments = $hostelPaymentsByResident[$resident->id] ?? [];

                if (count($payments) > 0) {
                    $paidSoFar = 0;
                    $storedBalance = 0;
                    foreach ($payments as $p) {
                        $paidSoFar     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                        $storedBalance += (float) $p->balance_amount;
                    }
                    $balance = max($storedBalance, 0);

                    if ($balance > 0) {
                        if ($paidSoFar > 0) {
                            $hostelPartialCount++;
                        } else {
                            $hostelPendingCount++;
                        }
                        $hostelPending += $balance;
                    } else {
                        $hostelPaidCount++;
                    }
                } else {
                    $hostelPendingCount++;
                    $hostelPending += $rentAmount;
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
            'residents_with_payments'           => $currentMonthPayments->pluck('resident_id')->unique()->count(),
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
