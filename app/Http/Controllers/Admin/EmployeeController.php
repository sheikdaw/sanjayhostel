<?php
// app/Http/Controllers/Admin/EmployeeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('hostel');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('employee_code', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            });
        }

        $employees = $query->latest()->paginate(12);
        $hostels = Hostel::active()->get();

        // Stats
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'total_salary' => Employee::sum('salary')
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.employees.partials.employees_grid', compact('employees'))->render(),
                'stats' => $stats,
                'pagination' => $employees->links()->render()
            ]);
        }

        return view('admin.employees.index', compact('employees', 'hostels', 'stats'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_code' => 'required|string|max:50|unique:employees',
            'name' => 'required|string|max:150',
            'hostel_id' => 'nullable|exists:hostels,id',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'joining_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'advance_deduct' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee
        ]);
    }

    public function show($id)
    {
        $employee = Employee::with('hostel')->findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $employee
            ]);
        }
        
        return redirect()->route('admin.employees.index');
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $employee
            ]);
        }
        
        return redirect()->route('admin.employees.index');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_code' => 'required|string|max:50|unique:employees,employee_code,' . $id,
            'name' => 'required|string|max:150',
            'hostel_id' => 'nullable|exists:hostels,id',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'joining_date' => 'nullable|date',
            'salary' => 'required|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'advance_deduct' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $employee->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee
        ]);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Check if employee has attendances
        if ($employee->attendances()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete employee with attendance records'
            ], 422);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        $employee = Employee::findOrFail($id);
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . ucfirst($newStatus),
            'data' => $employee
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:employees,id',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        Employee::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' employees updated successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:employees,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for employees with attendance records
        $employeesWithAttendance = Employee::whereIn('id', $request->ids)
            ->has('attendances')
            ->pluck('id')
            ->toArray();

        if (count($employeesWithAttendance) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete employees with attendance records'
            ], 422);
        }

        Employee::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' employees deleted successfully'
        ]);
    }

    public function export(Request $request)
    {
        $query = Employee::with('hostel');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('employee_code', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            });
        }

        $employees = $query->get();

        // Generate CSV
        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, [
            'Employee Code',
            'Name',
            'Hostel',
            'Department',
            'Designation',
            'Mobile',
            'Joining Date',
            'Salary',
            'Advance Amount',
            'Advance Deduct',
            'Status'
        ]);

        foreach ($employees as $employee) {
            fputcsv($handle, [
                $employee->employee_code,
                $employee->name,
                $employee->hostel->hostel_name ?? 'N/A',
                $employee->department ?? '',
                $employee->designation ?? '',
                $employee->mobile ?? '',
                $employee->joining_date ?? '',
                $employee->salary,
                $employee->advance_amount,
                $employee->advance_deduct,
                $employee->status
            ]);
        }

        fclose($handle);
        exit;
    }
}   