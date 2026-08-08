<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'room_type_name',
        'sharing_count',
        'monthly_rent',
        'deposit_amount',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2'
    ];

    // Relationships
    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    // Accessors
    public function getSharingLabelAttribute()
    {
        return $this->sharing_count . ' Sharing';
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }
}
