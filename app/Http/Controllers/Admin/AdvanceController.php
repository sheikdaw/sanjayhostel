<?php
// app/Http/Controllers/Admin/AdvanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AdvanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdvanceController extends Controller
{
    /**
     * GET admin/advances  -> admin.advances.index
     * Matches resources/views/admin/advances/index.blade.php
     */
    public function index(Request $request)
    {
        $month = $request->filled('month') ? $request->month : now()->format('Y-m');

        $query = Employee::with('hostel');

        if ($request->filled('employee_id')) {
            $query->where('id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();

        // For the "All Employees" filter dropdown
        $allEmployees = Employee::orderBy('name')->get();

        // Stats for the selected month
        $summary = [
            'total_advance' => AdvanceTransaction::advances()->where('month', $month)->sum('amount'),
            'total_deduction' => AdvanceTransaction::deductions()->where('month', $month)->sum('amount'),
            'total_outstanding' => AdvanceTransaction::advances()->sum('amount')
                - AdvanceTransaction::deductions()->sum('amount'),
        ];

        return view('admin.advances.index', compact('employees', 'allEmployees', 'summary', 'month'));
    }

    /**
     * GET admin/advances/monthly -> admin.advances.monthly
     * Matches resources/views/admin/advances/monthly.blade.php
     * (previously pointed at a non-existent "process-monthly" view)
     */
    public function processMonthly(Request $request)
    {
        $month = $request->filled('month') ? $request->month : now()->format('Y-m');

        $employees = Employee::with('hostel')->orderBy('name')->get();

        $results = [];
        foreach ($employees as $employee) {
            $advanceTaken = $employee->getMonthlyAdvanceTaken($month);
            $advanceDeducted = $employee->getMonthlyDeduction($month);
            $advanceBalance = $employee->advance_balance;
            $salary = $employee->salary;

            $results[] = [
                'employee' => $employee,
                'advance_taken' => $advanceTaken,
                'advance_deducted' => $advanceDeducted,
                'advance_balance' => $advanceBalance,
                'salary' => $salary,
                'net_salary' => $salary - $advanceDeducted,
            ];
        }

        return view('admin.advances.monthly', compact('results', 'month'));
    }

    /**
     * POST admin/advances/take -> admin.advances.take
     * Fields posted by index.blade.php: employee_id, amount, month, remarks
     */
    public function takeAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|date_format:Y-m',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $advance = AdvanceTransaction::create([
                'employee_id' => $request->employee_id,
                'amount' => $request->amount,
                'deducted_amount' => 0,
                'transaction_type' => 'advance',
                'transaction_date' => now()->toDateString(),
                'month' => $request->month,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance taken successfully!',
                'data' => $advance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to take advance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST admin/advances/deduct -> admin.advances.deduct
     * Fields posted by index.blade.php: employee_id, amount, month, remarks
     */
    public function deductAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|date_format:Y-m',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee_id);

        if ($request->amount > $employee->advance_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Deduction amount exceeds the outstanding advance balance (₹' .
                    number_format($employee->advance_balance, 2) . ').',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $deduction = AdvanceTransaction::create([
                'employee_id' => $request->employee_id,
                'amount' => $request->amount,
                'deducted_amount' => $request->amount,
                'transaction_type' => 'deduction',
                'transaction_date' => now()->toDateString(),
                'month' => $request->month,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance deducted successfully!',
                'data' => $deduction,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to deduct advance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET admin/advances/{id}/history -> admin.advances.history
     * Matches resources/views/admin/advances/history.blade.php
     */
    public function history(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // Every distinct month this employee has a transaction in, newest first
        $availableMonths = AdvanceTransaction::where('employee_id', $id)
            ->whereNotNull('month')
            ->distinct()
            ->orderByDesc('month')
            ->pluck('month');

        $month = $request->filled('month')
            ? $request->month
            : ($availableMonths->first() ?? now()->format('Y-m'));

        // Guard against an employee with zero transactions yet
        if ($availableMonths->isEmpty()) {
            $availableMonths = collect([$month]);
        }

        $transactions = AdvanceTransaction::where('employee_id', $id)
            ->where('month', $month)
            ->orderByDesc('transaction_date')
            ->get();

        return view('admin.advances.history', compact('employee', 'availableMonths', 'month', 'transactions'));
    }
}