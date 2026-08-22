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

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->employee_code . ')';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active' ? 'success' : 'danger';
    }

    public function getNetSalaryAttribute()
    {
        return $this->salary - ($this->advance_amount - $this->advance_deduct);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }
}