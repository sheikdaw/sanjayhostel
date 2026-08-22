<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advance extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'hostel_id',
        'amount',
        'deducted_amount',
        'advance_date',
        'deduction_date',
        'description',
        'remarks',
        'status',
        'deduction_month',
        'deduction_year',
        'payment_id',
        'completed_at',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'advance_date' => 'date',
        'deduction_date' => 'date',
        'completed_at' => 'datetime',
        'deduction_month' => 'integer',
        'deduction_year' => 'integer'
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - ($this->deducted_amount ?? 0);
    }

    public function getIsFullyDeductedAttribute(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'Pending ⏳',
            'COMPLETED' => 'Completed ✅',
            'CANCELLED' => 'Cancelled ❌',
            default => $this->status
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'warning',
            'COMPLETED' => 'success',
            'CANCELLED' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getFormattedDeductedAmountAttribute(): string
    {
        return '₹' . number_format($this->deducted_amount ?? 0, 2);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return '₹' . number_format($this->remaining_amount, 2);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'CANCELLED');
    }

    public function scopeByResident($query, $residentId)
    {
        return $query->where('resident_id', $residentId);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByMonth($query, $month, $year)
    {
        return $query->where('advance_date', '>=', "$year-$month-01")
            ->where('advance_date', '<=', "$year-$month-31");
    }
}