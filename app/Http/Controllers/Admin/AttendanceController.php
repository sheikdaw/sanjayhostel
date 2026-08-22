<?php
// app/Http/Controllers/Admin/AdvanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AdvanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdvanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $employeeId = $request->employee_id;

        $query = Employee::with(['advanceTransactions' => function($q) use ($month) {
            $q->where('month', $month);
        }]);

        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate(20)->withQueryString();
        $allEmployees = Employee::active()->get();

        // Summary
        $summary = [
            'total_advance' => AdvanceTransaction::where('month', $month)
                ->where('transaction_type', 'advance')
                ->sum('amount'),
            'total_deduction' => AdvanceTransaction::where('month', $month)
                ->where('transaction_type', 'deduction')
                ->sum('deducted_amount'),
            'total_outstanding' => Employee::sum('advance_amount') - Employee::sum('advance_deduct'),
        ];

        $months = $this->getAvailableMonths();

        return view('admin.advances.index', compact('employees', 'allEmployees', 'month', 'summary', 'months'));
    }

    public function history($id, Request $request)
    {
        $employee = Employee::with('hostel')->findOrFail($id);
        $month = $request->month ?? Carbon::now()->format('Y-m');

        $transactions = $employee->advanceTransactions()
            ->where('month', $month)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $availableMonths = $this->getAvailableMonths($employee->id);

        return view('admin.advances.history', compact('employee', 'transactions', 'month', 'availableMonths'));
    }

    public function takeAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|date_format:Y-m',
            'remarks' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee_id);
        $transaction = $employee->takeAdvance($request->amount, $request->month, $request->remarks);

        return response()->json([
            'success' => true,
            'message' => 'Advance taken successfully',
            'data' => [
                'transaction' => $transaction,
                'new_balance' => $employee->advance_balance
            ]
        ]);
    }

    public function deductAdvance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'month' => 'required|date_format:Y-m',
            'remarks' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::findOrFail($request->employee_id);
        
        if ($request->amount > $employee->advance_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deduct more than outstanding balance'
            ], 422);
        }

        $transaction = $employee->deductAdvance($request->amount, $request->month, $request->remarks);

        return response()->json([
            'success' => true,
            'message' => 'Advance deducted successfully',
            'data' => [
                'transaction' => $transaction,
                'new_balance' => $employee->advance_balance
            ]
        ]);
    }

    public function processMonthly(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $employees = Employee::where('status', 'active')->get();

        $results = [];
        foreach ($employees as $employee) {
            $monthlyAdvance = $employee->getMonthlyAdvanceTaken($month);
            $monthlyDeduction = $employee->getMonthlyDeduction($month);
            
            $results[] = [
                'employee' => $employee,
                'advance_taken' => $monthlyAdvance,
                'advance_deducted' => $monthlyDeduction,
                'advance_balance' => $employee->advance_balance,
                'salary' => $employee->salary,
                'net_salary' => $employee->net_salary,
                'month' => $month
            ];
        }

        return view('admin.advances.monthly', compact('results', 'month'));
    }

    private function getAvailableMonths($employeeId = null)
    {
        $query = AdvanceTransaction::selectRaw('DISTINCT month')
            ->orderBy('month', 'desc');
        
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->pluck('month')->toArray();
    }
}