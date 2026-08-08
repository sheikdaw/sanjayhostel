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
        'status'
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'cash_paid_amount' => 'decimal:2',
        'upi_paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'payment_date' => 'date',
        'month' => 'integer',
        'year' => 'integer'
    ];

    // Relationships
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    // Accessors
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

    public function getFormattedDiscountAttribute()
    {
        return '₹' . number_format($this->discount_amount, 2);
    }

    public function getFormattedFineAttribute()
    {
        return '₹' . number_format($this->fine_amount, 2);
    }

    public function getFormattedCashAttribute()
    {
        return '₹' . number_format($this->cash_paid_amount, 2);
    }

    public function getFormattedUpiAttribute()
    {
        return '₹' . number_format($this->upi_paid_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return '₹' . number_format($this->balance_amount, 2);
    }

    public function getFormattedTotalPaidAttribute()
    {
        return '₹' . number_format($this->total_paid, 2);
    }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function getReceiptLinkAttribute()
    {
        return route('admin.payments.receipt', $this->id);
    }

    public function isPastMonth()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($this->year < $currentYear) return true;
        if ($this->year == $currentYear && $this->month < $currentMonth) return true;
        return false;
    }

    // Scopes
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

    public function scopeHasPendingPreviousMonth($query, $residentId, $currentMonth, $currentYear)
    {
        return $query->where('resident_id', $residentId)
            ->where(function($q) use ($currentMonth, $currentYear) {
                $q->where('year', '<', $currentYear)
                  ->orWhere(function($q2) use ($currentMonth, $currentYear) {
                      $q2->where('year', $currentYear)
                         ->where('month', '<', $currentMonth);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL']);
    }
}