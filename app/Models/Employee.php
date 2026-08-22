<?php
// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'employee_code',
        'name',
        'department',
        'designation',
        'mobile',
        'joining_date',
        'salary',
        'advance_amount',
        'advance_deduct',
        'status'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'advance_deduct' => 'decimal:2'
    ];

    // Relationships
    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function advanceTransactions()
    {
        return $this->hasMany(AdvanceTransaction::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->employee_code . ')';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active' ? 'success' : 'danger';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getAdvanceBalanceAttribute()
    {
        return $this->advance_amount - $this->advance_deduct;
    }

    public function getNetSalaryAttribute()
    {
        return $this->salary - $this->advance_balance;
    }

    public function getTotalAdvanceTakenAttribute()
    {
        return $this->advanceTransactions()
            ->where('transaction_type', 'advance')
            ->sum('amount');
    }

    public function getTotalDeductionAttribute()
    {
        return $this->advanceTransactions()
            ->where('transaction_type', 'deduction')
            ->sum('deducted_amount');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    // Advance Methods
    public function getMonthlyAdvanceTransactions($month)
    {
        return $this->advanceTransactions()
            ->where('month', $month)
            ->get();
    }

    public function getMonthlyAdvanceTaken($month)
    {
        return $this->advanceTransactions()
            ->where('month', $month)
            ->where('transaction_type', 'advance')
            ->sum('amount');
    }

    public function getMonthlyDeduction($month)
    {
        return $this->advanceTransactions()
            ->where('month', $month)
            ->where('transaction_type', 'deduction')
            ->sum('deducted_amount');
    }

    public function takeAdvance($amount, $month, $remarks = null)
    {
        $this->advance_amount += $amount;
        $this->save();

        return $this->advanceTransactions()->create([
            'amount' => $amount,
            'deducted_amount' => 0,
            'transaction_type' => 'advance',
            'transaction_date' => now(),
            'month' => $month,
            'remarks' => $remarks
        ]);
    }

    public function deductAdvance($amount, $month, $remarks = null)
    {
        if ($amount > $this->advance_balance) {
            throw new \Exception('Cannot deduct more than outstanding balance');
        }

        $this->advance_deduct += $amount;
        $this->save();

        return $this->advanceTransactions()->create([
            'amount' => 0,
            'deducted_amount' => $amount,
            'transaction_type' => 'deduction',
            'transaction_date' => now(),
            'month' => $month,
            'remarks' => $remarks
        ]);
    }

    // Attendance Methods
    public function getAttendanceForDate($date)
    {
        return $this->attendances()->where('attendance_date', $date)->first();
    }

    public function getMonthlyAttendance($year, $month)
    {
        return $this->attendances()
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get();
    }

    public function getAttendanceSummary($year, $month)
    {
        $attendances = $this->getMonthlyAttendance($year, $month);
        
        return [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'holiday' => $attendances->where('status', 'holiday')->count(),
            'weekly_off' => $attendances->where('status', 'weekly_off')->count(),
            'total_days' => $attendances->count(),
        ];
    }
}