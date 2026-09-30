<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id', 'room_id', 'bed_id',
        'employee_code', 'biometric_access', 'last_sync_at',
        'access_enabled_at', 'access_disabled_at',
        'resident_code', 'name', 'phone', 'parentsphone', 'email',
        'aadhaar_no', 'address', 'dob',
        'profile_image', 'aadhar_document', 'application_document',
        'joining_date', 'vacate_date', 'food_status',
        'rent_amount', 'deposit_amount', 'status',
    ];

    protected $casts = [
        'joining_date'       => 'date',
        'vacate_date'        => 'date',
        'dob'                => 'date',
        'rent_amount'        => 'decimal:2',
        'deposit_amount'     => 'decimal:2',
        'biometric_access'   => 'boolean',
        'last_sync_at'       => 'datetime',
        'access_enabled_at'  => 'datetime',
        'access_disabled_at' => 'datetime',
    ];

    public function hostel()     { return $this->belongsTo(Hostel::class); }
    public function room()       { return $this->belongsTo(Room::class); }
    public function bed()        { return $this->belongsTo(Bed::class); }
    public function payments()   { return $this->hasMany(Payment::class); }
    public function complaints() { return $this->hasMany(Complaint::class); }
}
