<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'bed_no',
        'bed_type',
        'status'
    ];

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return strtolower($this->status);
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(strtolower($this->status));
    }

    public function getBedTypeLabelAttribute()
    {
        return $this->bed_type == 'NORMAL' ? 'Normal' : 'Bunker';
    }
}
