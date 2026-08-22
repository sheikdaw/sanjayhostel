<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee.hostel');

        // Filter by date
        if ($request->filled('date')) {
            $query->where('attendance_date', $request->date);
        } else {
            $query->where('attendance_date', now()->toDateString());
        }

        // Filter by hostel
        if ($request->filled('hostel_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search employee
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('employee_code', 'LIKE', "%{$search}%");
            });
        }

        $attendances = $query->latest()->paginate(20)->withQueryString();
        $hostels = Hostel::active()->get();
        $employees = Employee::active()->get();

        return view('admin.attendances.index', compact('attendances', 'hostels', 'employees'));
    }

    public function create()
    {
        $employees = Employee::active()->with('hostel')->get();
        return view('admin.attendances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,leave,half_day,holiday,weekly_off',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check for duplicate
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Attendance already exists for this employee on this date.')
                ->withInput();
        }

        // Calculate working hours if check_in and check_out are provided
        $workingHours = null;
        if ($request->check_in && $request->check_out) {
            $workingHours = $this->calculateWorkingHours($request->check_in, $request->check_out);
        }

        Attendance::create([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'working_hours' => $workingHours,
            'source' => 'manual',
            'remarks' => $request->remarks
        ]);

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load('employee.hostel');
        return view('admin.attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::active()->with('hostel')->get();
        return view('admin.attendances.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,leave,half_day,holiday,weekly_off',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check for duplicate (excluding current record)
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)
            ->where('id', '!=', $attendance->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Attendance already exists for this employee on this date.')
                ->withInput();
        }

        // Calculate working hours if check_in and check_out are provided
        $workingHours = $attendance->working_hours;
        if ($request->check_in && $request->check_out) {
            $workingHours = $this->calculateWorkingHours($request->check_in, $request->check_out);
        }

        $attendance->update([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'status' => $request->status,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'working_hours' => $workingHours,
            'remarks' => $request->remarks
        ]);

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('admin.attendances.index')
            ->with('success', 'Attendance deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if ($ids) {
            Attendance::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Attendances deleted successfully.']);
        }
        return response()->json(['success' => false, 'message' => 'No attendances selected.']);
    }

    public function markBulkAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'status' => 'required|in:present,absent,leave,half_day,holiday,weekly_off'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $count = 0;
        foreach ($request->employee_ids as $employeeId) {
            $exists = Attendance::where('employee_id', $employeeId)
                ->where('attendance_date', $request->date)
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'employee_id' => $employeeId,
                    'attendance_date' => $request->date,
                    'status' => $request->status,
                    'source' => 'manual',
                    'remarks' => 'Bulk attendance marking'
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.attendances.index', ['date' => $request->date])
            ->with('success', "{$count} attendance records marked successfully.");
    }

    public function report(Request $request)
    {
        $hostels = Hostel::active()->get();
        
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $hostelId = $request->hostel_id;

        $query = Employee::active();

        if ($hostelId) {
            $query->byHostel($hostelId);
        }

        $employees = $query->with(['attendances' => function($q) use ($month, $year) {
            $q->whereMonth('attendance_date', $month)
              ->whereYear('attendance_date', $year);
        }])->get();

        $summary = [];
        foreach ($employees as $employee) {
            $summary[$employee->id] = [
                'employee' => $employee,
                'present' => $employee->attendances->where('status', 'present')->count(),
                'absent' => $employee->attendances->where('status', 'absent')->count(),
                'leave' => $employee->attendances->where('status', 'leave')->count(),
                'half_day' => $employee->attendances->where('status', 'half_day')->count(),
                'holiday' => $employee->attendances->where('status', 'holiday')->count(),
                'weekly_off' => $employee->attendances->where('status', 'weekly_off')->count(),
                'total' => $employee->attendances->count()
            ];
        }

        return view('admin.attendances.report', compact('summary', 'hostels', 'month', 'year', 'hostelId'));
    }

    private function calculateWorkingHours($checkIn, $checkOut)
    {
        $checkInTime = Carbon::createFromFormat('H:i', $checkIn);
        $checkOutTime = Carbon::createFromFormat('H:i', $checkOut);
        
        $hours = $checkOutTime->diffInHours($checkInTime) + 
                ($checkOutTime->diffInMinutes($checkInTime) % 60) / 60;
        
        // Subtract lunch break (1 hour) if worked more than 6 hours
        if ($hours > 6) {
            $hours -= 1;
        }
        
        // Limit to 8 hours max
        $hours = min($hours, 8);
        
        return round($hours, 2);
    }
}