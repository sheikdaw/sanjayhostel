<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'hostel_id', 'resident_id', 'complaint_number',
        'name', 'phone', 'email', 'room_number',
        'category', 'priority', 'description',
        'image', 'status', 'admin_remark', 'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->complaint_number)) {
                $complaint->complaint_number = 'CMP-' . date('Ymd') . '-'
                    . strtoupper(substr(uniqid(), -5));
            }
        });
    }
}
