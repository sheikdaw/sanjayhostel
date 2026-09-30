<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id', 'resident_id', 'complaint_number',
        'name', 'phone', 'email', 'room_number',
        'category', 'priority', 'description', 'image',
        'status', 'admin_remark', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function hostel()   { return $this->belongsTo(Hostel::class); }
    public function resident() { return $this->belongsTo(Resident::class); }
}
