<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id', 'room_type_name', 'sharing_count',
        'monthly_rent', 'deposit_amount', 'is_active',
    ];

    protected $casts = [
        'monthly_rent'   => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'is_active'      => 'boolean',
        'sharing_count'  => 'integer',
    ];

    public function hostel() { return $this->belongsTo(Hostel::class); }
    public function rooms()  { return $this->hasMany(Room::class); }
}
