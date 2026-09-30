<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id', 'room_type_id', 'room_no',
        'normal_cot_count', 'bunker_cot_count', 'status',
    ];

    protected $casts = [
        'normal_cot_count' => 'integer',
        'bunker_cot_count' => 'integer',
    ];

    public function hostel()    { return $this->belongsTo(Hostel::class); }
    public function roomType()  { return $this->belongsTo(RoomType::class); }
    public function beds()      { return $this->hasMany(Bed::class); }
    public function residents() { return $this->hasMany(Resident::class); }
}
