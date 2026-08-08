<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\RoleEnum;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'hostel_ids',
        'profile',
        'is_active',
        // New face detection fields
        'face_id',
        'face_registered',
        'face_registered_at',
        'face_image_path',
        'face_encoding',  // Optional: store encoding in DB as JSON
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'hostel_ids' => 'array',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'face_registered' => 'boolean',
        'face_registered_at' => 'datetime',
        'face_encoding' => 'array', // Cast JSON to array
    ];

    // Your existing methods...

    // Get assigned hostels from JSON field
    public function getAssignedHostels()
    {
        if (is_array($this->hostel_ids) && count($this->hostel_ids) > 0) {
            return Hostel::whereIn('id', $this->hostel_ids)->get();
        }
        return collect();
    }

    // Accessors
    public function getRoleLabelAttribute()
    {
        $labels = [
            'admin' => 'Admin',
            'account' => 'Account',
            'stay' => 'Resident'
        ];
        return $labels[$this->role] ?? ucfirst($this->role);
    }

    public function getRoleBadgeAttribute()
    {
        $colors = [
            'admin' => 'danger',
            'account' => 'warning',
            'stay' => 'secondary'
        ];
        return $colors[$this->role] ?? 'secondary';
    }

    public function getRoleIconAttribute()
    {
        $icons = [
            'admin' => 'bi-shield-lock',
            'account' => 'bi-cash-coin',
            'stay' => 'bi-person'
        ];
        return $icons[$this->role] ?? 'bi-person';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    public function getHostelNamesAttribute()
    {
        $hostels = $this->getAssignedHostels();
        return $hostels->pluck('hostel_name')->implode(', ');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->whereJsonContains('hostel_ids', $hostelId);
    }

    // Scope for face registered users
    public function scopeFaceRegistered($query)
    {
        return $query->where('face_registered', true);
    }

    // Helper Methods
    public function hasAccessToHostel($hostelId)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if (is_array($this->hostel_ids)) {
            return in_array($hostelId, $this->hostel_ids);
        }

        return false;
    }

    public function canManageHostel($hostelId)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if (in_array($this->role, ['account'])) {
            return $this->hasAccessToHostel($hostelId);
        }

        return false;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAccount()
    {
        return $this->role === 'account';
    }

    public function isStay()
    {
        return $this->role === 'stay';
    }

    // New face detection helper methods
    public function hasFaceRegistered()
    {
        return $this->face_registered && !is_null($this->face_id);
    }

    public function getFaceImageUrl()
    {
        if ($this->face_image_path) {
            return asset('storage/' . $this->face_image_path);
        }
        return null;
    }
}
