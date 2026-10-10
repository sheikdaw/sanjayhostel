<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'bed_no', 'bed_type', 'status'];

    public function room()      { return $this->belongsTo(Room::class); }
    public function residents() { return $this->hasMany(Resident::class); }
    public function resident()
{
    return $this->hasOne(Resident::class, 'bed_id')
        ->where('status', 'ACTIVE');
}
}
