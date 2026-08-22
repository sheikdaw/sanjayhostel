<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'status',
        'check_in',
        'check_out',
        'working_hours',
        'source',
        'remarks'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'working_hours' => 'decimal:2'
    ];

    const STATUSES = [
        'present' => 'Present',
        'absent' => 'Absent',
        'leave' => 'Leave',
        'half_day' => 'Half Day',
        'holiday' => 'Holiday',
        'weekly_off' => 'Weekly Off'
    ];

    const SOURCES = [
        'automatic' => 'Automatic',
        'manual' => 'Manual'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => 'success',
            'absent' => 'danger',
            'leave' => 'warning',
            'half_day' => 'info',
            'holiday' => 'primary',
            'weekly_off' => 'secondary'
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getCheckInFormattedAttribute()
    {
        return $this->check_in ? date('h:i A', strtotime($this->check_in)) : '-';
    }

    public function getCheckOutFormattedAttribute()
    {
        return $this->check_out ? date('h:i A', strtotime($this->check_out)) : '-';
    }
}