<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'room_id',
        'bed_id',
        'resident_code',
        'name',
        'phone',
        'email',
        'aadhaar_no',
        'address','parentsphone',
        'rent_amount',
        'food_status',
        'joining_date',
        'vacate_date',
        'deposit_amount',
        'status',
        // New document fields
        'face_id',
        'face_registered',
        'face_registered_at',
        'face_image_path',
        'profile_image',
        'aadhar_document',
        'application_document',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'vacate_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'face_registered' => 'boolean',
        'face_registered_at' => 'datetime',
    ];

    // Relationships
    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return strtolower($this->status);
    }

    public function getFullAddressAttribute()
    {
        return $this->address ?? 'N/A';
    }

    public function getFormattedRentAttribute()
    {
        return '₹' . number_format($this->rent_amount ?? 0, 2);
    }

    public function getFormattedDepositAttribute()
    {
        return '₹' . number_format($this->deposit_amount ?? 0, 2);
    }

    public function getFoodStatusLabelAttribute()
    {
        return $this->food_status == 'WITH_FOOD' ? 'With Food' : 'Without Food';
    }

    public function getFoodStatusBadgeAttribute()
    {
        return $this->food_status == 'WITH_FOOD' ? 'success' : 'secondary';
    }

    public function getFoodStatusIconAttribute()
    {
        return $this->food_status == 'WITH_FOOD' ? '🍽️' : '🍞';
    }

    // Document accessors for public path
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset($this->profile_image);
        }
        return null;
    }

    public function getAadharDocumentUrlAttribute()
    {
        if ($this->aadhar_document) {
            return asset($this->aadhar_document);
        }
        return null;
    }

    public function getApplicationDocumentUrlAttribute()
    {
        if ($this->application_document) {
            return asset($this->application_document);
        }
        return null;
    }

    public function getProfileImageThumbAttribute()
    {
        if ($this->profile_image) {
            return asset($this->profile_image);
        }
        // Default avatar based on name
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=C5A028&color=fff&size=128';
    }

    public function getHasDocumentsAttribute()
    {
        return !is_null($this->profile_image) ||
               !is_null($this->aadhar_document) ||
               !is_null($this->application_document);
    }

    // New face detection accessors
    public function getHasFaceRegisteredAttribute()
    {
        return $this->face_registered && !is_null($this->face_id);
    }

    public function getFaceImageUrlAttribute()
    {
        if ($this->face_image_path) {
            return asset($this->face_image_path);
        }
        return null;
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

    // Scopes
    public function scopeWithFood($query)
    {
        return $query->where('food_status', 'WITH_FOOD');
    }

    public function scopeWithoutFood($query)
    {
        return $query->where('food_status', 'WITHOUT_FOOD');
    }

    public function scopeFaceRegistered($query)
    {
        return $query->where('face_registered', true);
    }

    public function scopeFaceUnregistered($query)
    {
        return $query->where('face_registered', false)
                    ->orWhereNull('face_registered');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeHasProfileImage($query)
    {
        return $query->whereNotNull('profile_image');
    }

    public function scopeHasAadhar($query)
    {
        return $query->whereNotNull('aadhar_document');
    }

    public function scopeHasApplication($query)
    {
        return $query->whereNotNull('application_document');
    }

    // Helper method to get document icons
    public function getDocumentIcon($documentType)
    {
        $path = $this->$documentType;
        if (!$path) return 'bi-file-earmark';

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return match(strtolower($extension)) {
            'pdf' => 'bi-file-earmark-pdf',
            'doc', 'docx' => 'bi-file-earmark-word',
            'xls', 'xlsx' => 'bi-file-earmark-excel',
            'jpg', 'jpeg', 'png', 'gif' => 'bi-file-earmark-image',
            default => 'bi-file-earmark',
        };
    }
}
