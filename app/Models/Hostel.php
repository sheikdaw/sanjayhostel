<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_code',
        'hostel_name',
        'hostel_type',
        'address',
        'phone',
        'email',
        'status',
        'biometric_device_id',
        'biometric_device_name',
        'biometric_ip_address',
        'biometric_port',
        'biometric_location_code',
        'employee_code_prefix',
        'upi_id',
        'upi_payee_name',
    ];

    protected $casts = [
        'biometric_port' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }
    public function residents()
    {
        return $this->hasMany(Resident::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hostels');
    }
}
