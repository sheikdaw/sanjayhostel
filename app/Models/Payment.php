<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'receipt_no',
        'month',
        'year',
        'rent_amount',
        'discount_amount',
        'fine_amount',
        'cash_paid_amount',
        'upi_paid_amount',
        'balance_amount',
        'payment_date',
        'transaction_id',
        'remark',
        'payment_type',
        'previous_pending_cleared',
        'status'
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'cash_paid_amount' => 'decimal:2',
        'upi_paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'previous_pending_cleared' => 'decimal:2',
        'payment_date' => 'date',
        'month' => 'integer',
        'year' => 'integer'
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->cash_paid_amount + $this->upi_paid_amount;
    }

    public function getStatusBadgeAttribute()
    {
        return strtolower($this->status);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'PAID' => 'Paid',
            'PARTIAL' => 'Partial',
            'PENDING' => 'Pending'
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getFormattedRentAttribute()
    {
        return '₹' . number_format($this->rent_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return '₹' . number_format($this->balance_amount, 2);
    }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function isPastMonth()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->year < $currentYear) return true;
        if ($this->year == $currentYear && $this->month < $currentMonth) return true;
        return false;
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeByResident($query, $residentId)
    {
        return $query->where('resident_id', $residentId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'PARTIAL');
    }
}