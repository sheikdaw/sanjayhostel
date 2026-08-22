<?php
// app/Models/AdvanceTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'deducted_amount',
        'transaction_type',
        'transaction_date',
        'month',
        'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'transaction_date' => 'date'
    ];

    const TYPE_ADVANCE = 'advance';
    const TYPE_DEDUCTION = 'deduction';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeLabelAttribute()
    {
        return $this->transaction_type == 'advance' ? 'Advance Taken' : 'Deduction';
    }
}