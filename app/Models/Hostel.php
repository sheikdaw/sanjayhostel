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
    ];

    // Relationships
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

    // Accessors
    public function getTypeLabelAttribute()
    {
        return $this->hostel_type == 'MEN' ? '👤 Men' : '👩 Women';
    }

    public function getStatusBadgeAttribute()
    {
        return strtolower($this->status);
    }

    // Biometric Accessors
    public function getBiometricDeviceUrlAttribute()
    {
        if ($this->biometric_ip_address && $this->biometric_port) {
            return "http://{$this->biometric_ip_address}:{$this->biometric_port}/webservice.asmx";
        }
        return null;
    }

    public function getEmployeeCodePrefixAttribute()
    {
        return $this->attributes['employee_code_prefix'] ?? 'H' . $this->id;
    }

    public function getBiometricStatusAttribute()
    {
        if (!$this->biometric_device_id) {
            return 'Not Configured';
        }
        if ($this->biometric_ip_address) {
            return 'Configured ✅';
        }
        return 'Pending Configuration';
    }

    public function getBiometricStatusBadgeAttribute()
    {
        if (!$this->biometric_device_id) {
            return 'secondary';
        }
        if ($this->biometric_ip_address) {
            return 'success';
        }
        return 'warning';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeMen($query)
    {
        return $query->where('hostel_type', 'MEN');
    }

    public function scopeWomen($query)
    {
        return $query->where('hostel_type', 'WOMEN');
    }

    public function scopeHasBiometric($query)
    {
        return $query->whereNotNull('biometric_device_id');
    }

    public function scopeBiometricConfigured($query)
    {
        return $query->whereNotNull('biometric_device_id')
                    ->whereNotNull('biometric_ip_address');
    }
}