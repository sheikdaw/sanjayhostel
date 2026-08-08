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
        'status'
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
}
