<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id', 'receipt_no', 'month', 'year',
        'rent_amount', 'discount_amount', 'fine_amount',
        'cash_paid_amount', 'upi_paid_amount', 'balance_amount',
        'previous_pending_cleared',
        'payment_date', 'transaction_id', 'payment_type', 'remark', 'status',
    ];

    protected $casts = [
        'rent_amount'              => 'decimal:2',
        'discount_amount'          => 'decimal:2',
        'fine_amount'              => 'decimal:2',
        'cash_paid_amount'         => 'decimal:2',
        'upi_paid_amount'          => 'decimal:2',
        'balance_amount'           => 'decimal:2',
        'previous_pending_cleared' => 'decimal:2',
        'payment_date'             => 'date',
    ];

    public function resident() { return $this->belongsTo(Resident::class); }
}
