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
        // PAYMENT STATISTICS - FIXED
        // ============================================================

        // Get all payments for this hostel
        $payments = Payment::whereHas('resident', function($q) use ($hostelIds) {
            $q->whereIn('hostel_id', $hostelIds);
        })->get();

        $totalPayments = $payments->count();

        // Total Collected (Cash + UPI)
        $totalCollected = $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount');

        // Total Rent Amount (from all payments)
        $totalRent = $payments->sum('rent_amount');

        // Total Balance (sum of all balance amounts)
        $totalBalance = $payments->sum('balance_amount');

        // Total Pending = SUM of balance_amount where status is PENDING or PARTIAL
        // This is the amount still due from residents
        $totalPending = $payments->whereIn('status', ['PENDING', 'PARTIAL'])->sum('balance_amount');

        // Alternative calculation: Total Pending = Total Rent - Total Collected
        // But we use the balance_amount sum as it's more accurate
        // $totalPendingAlternative = $totalRent - $totalCollected;

        // Count of pending payments
        $pendingCount = $payments->where('status', 'PENDING')->count();

        // Count of partial payments
        $partialCount = $payments->where('status', 'PARTIAL')->count();

        // Count of paid payments
        $paidCount = $payments->where('status', 'PAID')->count();

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

            // Hostel wise payment summary
            $hostelPayments = Payment::whereHas('resident', function($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->get();

            $hostelCollected = $hostelPayments->sum('cash_paid_amount') + $hostelPayments->sum('upi_paid_amount');
            $hostelPending = $hostelPayments->whereIn('status', ['PENDING', 'PARTIAL'])->sum('balance_amount');
            $hostelPaidCount = $hostelPayments->where('status', 'PAID')->count();
            $hostelPendingCount = $hostelPayments->where('status', 'PENDING')->count();
            $hostelPartialCount = $hostelPayments->where('status', 'PARTIAL')->count();

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
            'total_rent' => $totalRent,
            'total_collected' => $totalCollected,
            'total_balance' => $totalBalance,
            'total_pending' => $totalPending,
            'pending_count' => $pendingCount,
            'partial_count' => $partialCount,
            'paid_count' => $paidCount,
            'payment_count' => $totalPayments,
            // Formula: Total Pending = Total Rent - Total Collected
            'pending_by_formula' => $totalRent - $totalCollected
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