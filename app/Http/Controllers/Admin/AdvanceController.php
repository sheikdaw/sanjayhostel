<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Advance;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdvanceController extends Controller
{
    /**
     * Display a listing of advances
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $advances = Advance::with(['resident', 'resident.hostel'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
            $advances = Advance::with(['resident', 'resident.hostel'])
                ->whereHas('resident', function($q) use ($hostelIds) {
                    $q->whereIn('hostel_id', $hostelIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Statistics
        $stats = [
            'total_advances' => $advances->count(),
            'total_amount' => $advances->sum('amount'),
            'pending_amount' => $advances->where('status', 'PENDING')->sum('amount'),
            'completed_amount' => $advances->where('status', 'COMPLETED')->sum('amount'),
            'pending_count' => $advances->where('status', 'PENDING')->count(),
            'completed_count' => $advances->where('status', 'COMPLETED')->count(),
            'cancelled_count' => $advances->where('status', 'CANCELLED')->count(),
        ];

        // Get residents for dropdown
        $residents = Resident::whereIn('hostel_id', $hostelIds ?? [])
            ->where('status', 'ACTIVE')
            ->get();

        return view('admin.advances.index', compact('advances', 'hostels', 'stats', 'residents', 'user'));
    }

    /**
     * Process monthly advances (show form)
     */
    public function processMonthly(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        return view('admin.advances.process-monthly', compact('hostels', 'month', 'year'));
    }

    /**
     * Take advance for a resident
     */
    public function takeAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'amount' => 'required|numeric|min:1',
            'advance_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'deduction_month' => 'nullable|integer|min:1|max:12',
            'deduction_year' => 'nullable|integer|min:2020|max:2100',
            'hostel_id' => 'nullable|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $advance = Advance::create([
                'resident_id' => $request->resident_id,
                'hostel_id' => $request->hostel_id ?? Resident::find($request->resident_id)->hostel_id,
                'amount' => $request->amount,
                'advance_date' => $request->advance_date,
                'description' => $request->description,
                'status' => 'PENDING',
                'deduction_month' => $request->deduction_month,
                'deduction_year' => $request->deduction_year,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance taken successfully!',
                'data' => $advance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to take advance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deduct advance from payment
     */
    public function deductAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advance_id' => 'required|exists:advances,id',
            'deducted_amount' => 'required|numeric|min:1',
            'payment_id' => 'nullable|exists:payments,id',
            'deduction_date' => 'required|date',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $advance = Advance::find($request->advance_id);

            // Check if advance is already completed
            if ($advance->status === 'COMPLETED') {
                return response()->json([
                    'success' => false,
                    'message' => 'This advance is already completed!'
                ], 400);
            }

            // Update advance
            $advance->deducted_amount = ($advance->deducted_amount ?? 0) + $request->deducted_amount;
            $advance->payment_id = $request->payment_id;
            $advance->deduction_date = $request->deduction_date;
            $advance->remarks = $request->remarks;

            // Check if advance is fully deducted
            if ($advance->deducted_amount >= $advance->amount) {
                $advance->status = 'COMPLETED';
                $advance->completed_at = now();
            }

            $advance->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance deducted successfully!',
                'data' => $advance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to deduct advance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advance history for a resident
     */
    public function history(Request $request, $residentId)
    {
        $advances = Advance::where('resident_id', $residentId)
            ->orderBy('advance_date', 'desc')
            ->get();

        $summary = [
            'total' => $advances->sum('amount'),
            'deducted' => $advances->sum('deducted_amount'),
            'pending' => $advances->sum('amount') - $advances->sum('deducted_amount'),
            'count' => $advances->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $advances,
            'summary' => $summary
        ]);
    }

    /**
     * Get resident advance summary
     */
    public function getResidentAdvanceSummary($residentId)
    {
        $advances = Advance::where('resident_id', $residentId)
            ->where('status', 'PENDING')
            ->get();

        $totalPending = $advances->sum('amount') - $advances->sum('deducted_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'pending_advances' => $advances->count(),
                'total_pending_amount' => $totalPending,
                'advances' => $advances
            ]
        ]);
    }
}