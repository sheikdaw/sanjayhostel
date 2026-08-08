<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'room_type_id',
        'room_no',
        'normol_cot_count',
        'bunker_cot_count',
        'status'
    ];

    // Relationships
    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
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

    public function getTotalCotsAttribute()
    {
        return ($this->normol_cot_count ?? 0) + ($this->bunker_cot_count ?? 0);
    }

    public function getOccupiedCotsAttribute()
    {
        return $this->beds()->where('status', 'OCCUPIED')->count();
    }

    public function getVacantCotsAttribute()
    {
        return $this->beds()->where('status', 'VACANT')->count();
    }

    public function getOccupancyPercentageAttribute()
    {
        $total = $this->total_cots;
        if ($total == 0) return 0;
        return round(($this->occupied_cots / $total) * 100);
    }

    public function getNormolBedsAttribute()
    {
        return $this->beds()->where('bed_type', 'NORMAL')->count();
    }

    public function getBunkerBedsAttribute()
    {
        return $this->beds()->where('bed_type', 'BUNKER')->count();
    }
}
