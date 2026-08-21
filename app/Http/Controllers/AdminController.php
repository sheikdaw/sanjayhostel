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

        // If no hostels, return empty dashboard
        if (empty($hostelIds)) {
            $hostelIds = [0]; // Prevent SQL errors
        }

        // ============================================================
        // STATISTICS
        // ============================================================

        // Hostel Statistics
        $totalHostels = $hostels->count();
        $totalRooms = Room::whereIn('hostel_id', $hostelIds)->count();
        $totalBeds = Bed::whereHas('room', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })->count();
        $totalResidents = Resident::whereIn('hostel_id', $hostelIds)->where('status', 'ACTIVE')->count();
        $totalVacated = Resident::whereIn('hostel_id', $hostelIds)->where('status', 'VACATED')->count();

        // Bed occupancy stats
        $occupiedBeds = Bed::whereHas('room', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })->where('status', 'OCCUPIED')->count();
        $vacantBeds = Bed::whereHas('room', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })->where('status', 'VACANT')->count();
        $blockedBeds = Bed::whereHas('room', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })->where('status', 'BLOCKED')->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        // ============================================================
        // PAYMENT STATISTICS - FIXED FOR CURRENT MONTH
        // ============================================================

        // Get current month range
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        // Get all ACTIVE residents for this hostel
        $activeResidents = Resident::whereIn('hostel_id', $hostelIds)
            ->where('status', 'ACTIVE')
            ->with(['room', 'hostel']) // Eager load relationships
            ->get();

        // Get payments for current month
        $currentMonthPayments = Payment::whereHas('resident', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })
        ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
        ->get();

        // Total payments count for current month
        $totalPayments = $currentMonthPayments->count();

        // Total Collected (Cash + UPI) for current month
        $totalCollected = $currentMonthPayments->sum('cash_paid_amount') + $currentMonthPayments->sum('upi_paid_amount');

        // Calculate pending amount for each active resident
        $totalPending = 0;
        $pendingResidents = 0;
        $paidResidents = 0;
        $partialResidents = 0;
        $totalRentForActiveResidents = 0;
        $pendingDetails = []; // For debugging

        foreach ($activeResidents as $resident) {
            // Get the resident's rent amount
            $rentAmount = $resident->room->rent_amount ?? 0;
            $totalRentForActiveResidents += $rentAmount;

            // Get this month's payment for this resident
            $monthlyPayment = $currentMonthPayments->firstWhere('resident_id', $resident->id);
            
            if ($monthlyPayment) {
                $balance = $monthlyPayment->balance_amount;
                
                if ($balance > 0) {
                    // Partial payment or pending
                    if ($monthlyPayment->status === 'PARTIAL') {
                        $partialResidents++;
                        $totalPending += $balance;
                        $pendingDetails[] = [
                            'resident' => $resident->name,
                            'rent' => $rentAmount,
                            'balance' => $balance,
                            'status' => 'PARTIAL'
                        ];
                    } elseif ($monthlyPayment->status === 'PENDING') {
                        $pendingResidents++;
                        $totalPending += $balance;
                        $pendingDetails[] = [
                            'resident' => $resident->name,
                            'rent' => $rentAmount,
                            'balance' => $balance,
                            'status' => 'PENDING'
                        ];
                    } else {
                        // If status is PAID but balance > 0 (shouldn't happen, but just in case)
                        $partialResidents++;
                        $totalPending += $balance;
                        $pendingDetails[] = [
                            'resident' => $resident->name,
                            'rent' => $rentAmount,
                            'balance' => $balance,
                            'status' => 'PAID_WITH_BALANCE'
                        ];
                    }
                } else {
                    // Fully paid
                    $paidResidents++;
                }
            } else {
                // No payment made this month - FULL PENDING
                $pendingResidents++;
                $totalPending += $rentAmount;
                $pendingDetails[] = [
                    'resident' => $resident->name,
                    'rent' => $rentAmount,
                    'balance' => $rentAmount,
                    'status' => 'NO_PAYMENT'
                ];
            }
        }

        // Alternative calculation: Total Pending = (Total Rent for all active residents) - Total Collected
        $totalPendingAlternative = $totalRentForActiveResidents - $totalCollected;

        // If totalPending is 0 but there are residents, check if we have any payments at all
        if ($totalPending == 0 && $activeResidents->count() > 0 && $currentMonthPayments->count() == 0) {
            // No payments made this month at all - all residents are pending
            $totalPending = $totalRentForActiveResidents;
            $pendingResidents = $activeResidents->count();
            $paidResidents = 0;
        }

        // Payment status counts for current month
        $paidCount = $paidResidents;
        $pendingCount = $pendingResidents;
        $partialCount = $partialResidents;

        // Total balance for current month
        $totalBalance = $currentMonthPayments->sum('balance_amount');
        $totalRent = $currentMonthPayments->sum('rent_amount');

        // ============================================================
        // MONTHLY PAYMENTS CHART (Last 6 months)
        // ============================================================

        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

        $monthlyPayments = Payment::whereHas('resident', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })
        ->where('payment_date', '>=', $sixMonthsAgo)
        ->get()
        ->groupBy(function($payment) {
            return $payment->payment_date->format('Y-m');
        })
        ->map(function($group) {
            return [
                'month' => $group->first()->payment_date->month,
                'year' => $group->first()->payment_date->year,
                'total_collected' => $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount'),
                'total_rent' => $group->sum('rent_amount'),
                'total_balance' => $group->sum('balance_amount')
            ];
        })
        ->values()
        ->sortBy(function($item) {
            return $item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT);
        })
        ->take(6);

        $months = [];
        $collections = [];
        $balances = [];
        foreach ($monthlyPayments as $payment) {
            $months[] = date('M', mktime(0, 0, 0, $payment['month'], 1));
            $collections[] = round($payment['total_collected'] / 100000, 1); // In lakhs
            $balances[] = round($payment['total_balance'] / 100000, 1); // In lakhs
        }

        // If no data, show last 6 months with zero values
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
            ->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ============================================================
        // RECENT RESIDENTS
        // ============================================================

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
            $bedCount = Bed::whereHas('room', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->count();
            $occupiedCount = Bed::whereHas('room', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->where('status', 'OCCUPIED')->count();

            // Hostel wise payment summary for current month
            $hostelActiveResidents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->with('room')
                ->get();
            
            $hostelCurrentMonthPayments = Payment::whereHas('resident', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->get();

            $hostelCollected = $hostelCurrentMonthPayments->sum('cash_paid_amount') + $hostelCurrentMonthPayments->sum('upi_paid_amount');
            
            // Calculate hostel pending
            $hostelPending = 0;
            $hostelPaidCount = 0;
            $hostelPendingCount = 0;
            $hostelPartialCount = 0;
            
            foreach ($hostelActiveResidents as $resident) {
                $rentAmount = $resident->room->rent_amount ?? 0;
                $monthlyPayment = $hostelCurrentMonthPayments->firstWhere('resident_id', $resident->id);
                
                if ($monthlyPayment) {
                    if ($monthlyPayment->balance_amount > 0) {
                        if ($monthlyPayment->status === 'PARTIAL') {
                            $hostelPartialCount++;
                            $hostelPending += $monthlyPayment->balance_amount;
                        } elseif ($monthlyPayment->status === 'PENDING') {
                            $hostelPendingCount++;
                            $hostelPending += $monthlyPayment->balance_amount;
                        }
                    } else {
                        $hostelPaidCount++;
                    }
                } else {
                    $hostelPendingCount++;
                    $hostelPending += $rentAmount;
                }
            }

            $hostelStats[] = [
                'name' => $hostel->hostel_name,
                'code' => $hostel->hostel_code,
                'residents' => $residentCount,
                'rooms' => $roomCount,
                'beds' => $bedCount,
                'occupied' => $occupiedCount,
                'occupancy_rate' => $bedCount > 0 ? round(($occupiedCount / $bedCount) * 100, 1) : 0,
                'collected' => $hostelCollected,
                'pending' => $hostelPending,
                'paid_count' => $hostelPaidCount,
                'pending_count' => $hostelPendingCount,
                'partial_count' => $hostelPartialCount
            ];
        }

        // ============================================================
        // ROOM TYPE DISTRIBUTION
        // ============================================================

        $roomTypeDistribution = RoomType::whereHas('hostel', function($q) use ($hostelIds) {
            $q->whereIn('id', $hostelIds);
        })
        ->select('room_type_name', DB::raw('count(*) as total'))
        ->groupBy('room_type_name')
        ->get();

        // ============================================================
        // BED TYPE DISTRIBUTION
        // ============================================================

        $bedTypeDistribution = Bed::whereHas('room', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })
        ->select('bed_type', DB::raw('count(*) as total'))
        ->groupBy('bed_type')
        ->get();

        // ============================================================
        // STATUS DISTRIBUTION
        // ============================================================

        $statusDistribution = Resident::whereIn('hostel_id', $hostelIds)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // ============================================================
        // CALCULATION SUMMARY FOR DEBUGGING
        // ============================================================

        $calculationSummary = [
            'month' => $currentMonthStart->format('F Y'),
            'total_active_residents' => $activeResidents->count(),
            'total_rent_for_active_residents' => $totalRentForActiveResidents,
            'total_collected' => $totalCollected,
            'total_pending' => $totalPending,
            'total_pending_alternative' => $totalPendingAlternative,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'partial_count' => $partialCount,
            'payment_count' => $totalPayments,
            'residents_with_payments' => $currentMonthPayments->pluck('resident_id')->unique()->count(),
            'pending_details' => $pendingDetails
        ];

        // Get current user
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