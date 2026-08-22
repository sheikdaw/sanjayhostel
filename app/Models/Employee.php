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
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'advance_deduct' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Advance ledger accessors / helpers
    |--------------------------------------------------------------------------
    | Backed by the advance_transactions table (see AdvanceTransaction model).
    | The static advance_amount / advance_deduct columns on this table are
    | legacy snapshot fields from the original employee-creation form; the
    | ledger below is the live source of truth used by the Advances module.
    */

    /**
     * Outstanding balance = total advances taken - total amount deducted (all time)
     * Used in index.blade.php and history.blade.php
     */
    public function getAdvanceBalanceAttribute()
    {
        $taken = $this->advanceTransactions()->advances()->sum('amount');
        $deducted = $this->advanceTransactions()->deductions()->sum('amount');

        return round($taken - $deducted, 2);
    }

    /**
     * Advance taken in a given month (format: Y-m, e.g. "2026-08")
     * Used in index.blade.php: $employee->getMonthlyAdvanceTaken($month)
     */
    public function getMonthlyAdvanceTaken(string $month)
    {
        return $this->advanceTransactions()
            ->advances()
            ->where('month', $month)
            ->sum('amount');
    }

    /**
     * Deduction made in a given month (format: Y-m)
     * Used in index.blade.php: $employee->getMonthlyDeduction($month)
     */
    public function getMonthlyDeduction(string $month)
    {
        return $this->advanceTransactions()
            ->deductions()
            ->where('month', $month)
            ->sum('amount');
    }
}