<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing fields
        'hostel_id',
        'room_id',
        'bed_id',
        'resident_code',
        'name',
        'phone',
        'parentsphone',
        'email',
        'aadhaar_no',
        'address',
        'joining_date',
        'vacate_date',
        'food_status',
        'rent_amount',
        'dob',
        'deposit_amount',
        'status',

        // Biometric fields (NEW)
        'employee_code',
        'biometric_access',
        'last_sync_at',
        'access_enabled_at',
        'access_disabled_at',

        // Document fields (Existing)
        'profile_image',
        'aadhar_document',
        'application_document',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'vacate_date' => 'date',
          'dob' => 'date', // NEW: Cast DOB to date
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'biometric_access' => 'boolean',
        'last_sync_at' => 'datetime',
        'access_enabled_at' => 'datetime',
        'access_disabled_at' => 'datetime',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================
  public function getFormattedDobAttribute(): string
    {
        return $this->dob ? $this->dob->format('d M Y') : 'N/A';
    }

    /**
     * Get age from DOB
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->dob) {
            return null;
        }
        return $this->dob->age;
    }

    /**
     * Get DOB for input field (YYYY-MM-DD)
     */
    public function getDobInputAttribute(): ?string
    {
        return $this->dob ? $this->dob->format('Y-m-d') : null;
    }
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function currentPayment()
    {
        return $this->hasOne(Payment::class)
            ->where('month', now()->month)
            ->where('year', now()->year);
    }

    // ============================================
    // ACCESSORS - Document URLs
    // ============================================

    public function getProfileImageUrlAttribute(): ?string
    {
        if ($this->profile_image && file_exists(public_path($this->profile_image))) {
            return asset($this->profile_image);
        }
        return null;
    }

    public function getAadharDocumentUrlAttribute(): ?string
    {
        if ($this->aadhar_document && file_exists(public_path($this->aadhar_document))) {
            return asset($this->aadhar_document);
        }
        return null;
    }

    public function getApplicationDocumentUrlAttribute(): ?string
    {
        if ($this->application_document && file_exists(public_path($this->application_document))) {
            return asset($this->application_document);
        }
        return null;
    }

    public function getProfileImageThumbAttribute(): string
    {
        if ($this->profile_image && file_exists(public_path($this->profile_image))) {
            return asset($this->profile_image);
        }
        // Default avatar based on name
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=c5a028&color=fff&size=128';
    }

    // ============================================
    // ACCESSORS - Biometric Status
    // ============================================

    public function getBiometricStatusAttribute(): string
    {
        if (!$this->employee_code) {
            return 'Not Generated';
        }
        return $this->biometric_access ? 'Enabled' : 'Disabled';
    }

    public function getBiometricStatusClassAttribute(): string
    {
        if (!$this->employee_code) {
            return 'secondary';
        }
        return $this->biometric_access ? 'success' : 'danger';
    }

    public function getBiometricStatusIconAttribute(): string
    {
        if (!$this->employee_code) {
            return 'bi-clock';
        }
        return $this->biometric_access ? 'bi-check-circle' : 'bi-x-circle';
    }

    public function getIsSyncedAttribute(): bool
    {
        return !is_null($this->employee_code);
    }

    public function getHasBiometricAccessAttribute(): bool
    {
        return $this->biometric_access && !is_null($this->employee_code);
    }

    // ============================================
    // ACCESSORS - Food & Status
    // ============================================

    public function getFoodStatusLabelAttribute(): string
    {
        return $this->food_status == 'WITH_FOOD' ? 'With Food 🍽️' : 'Without Food 🍞';
    }

    public function getFoodStatusBadgeAttribute(): string
    {
        return $this->food_status == 'WITH_FOOD' ? 'success' : 'secondary';
    }

    public function getFoodStatusIconAttribute(): string
    {
        return $this->food_status == 'WITH_FOOD' ? '🍽️' : '🍞';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status == 'ACTIVE' ? 'Active ✅' : 'Vacated ❌';
    }

    public function getStatusBadgeAttribute(): string
    {
        return strtolower($this->status);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->hostel) $parts[] = $this->hostel->hostel_name;
        if ($this->room) $parts[] = 'Room #' . $this->room->room_no;
        if ($this->bed) $parts[] = 'Bed #' . $this->bed->bed_no;
        return implode(', ', $parts);
    }

    public function getFormattedRentAttribute(): string
    {
        return '₹' . number_format($this->rent_amount ?? 0, 2);
    }

    public function getFormattedDepositAttribute(): string
    {
        return '₹' . number_format($this->deposit_amount ?? 0, 2);
    }

    // ============================================
    // ACCESSORS - Document Icons
    // ============================================

    public function getDocumentIcon(string $documentType): string
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

    public function getHasDocumentsAttribute(): bool
    {
        return !is_null($this->profile_image) ||
               !is_null($this->aadhar_document) ||
               !is_null($this->application_document);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeVacated($query)
    {
        return $query->where('status', 'VACATED');
    }

    public function scopeWithFood($query)
    {
        return $query->where('food_status', 'WITH_FOOD');
    }

    public function scopeWithoutFood($query)
    {
        return $query->where('food_status', 'WITHOUT_FOOD');
    }

    public function scopeBiometricEnabled($query)
    {
        return $query->where('biometric_access', true);
    }

    public function scopeBiometricDisabled($query)
    {
        return $query->where('biometric_access', false);
    }

    public function scopeSynced($query)
    {
        return $query->whereNotNull('employee_code');
    }

    public function scopeNotSynced($query)
    {
        return $query->whereNull('employee_code');
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

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Enable biometric access for this resident
     */
    public function enableBiometricAccess(): void
    {
        $this->biometric_access = true;
        $this->access_enabled_at = now();
        $this->access_disabled_at = null;
        $this->save();
    }

    /**
     * Disable biometric access for this resident
     */
    public function disableBiometricAccess(): void
    {
        $this->biometric_access = false;
        $this->access_disabled_at = now();
        $this->access_enabled_at = null;
        $this->save();
    }

    /**
     * Sync this resident to biometric system
     */
    public function syncToBiometric(string $employeeCode): void
    {
        $this->employee_code = $employeeCode;
        $this->last_sync_at = now();
        $this->save();
    }

    /**
     * Check if resident has paid for current month
     */
    public function hasPaidCurrentMonth(): bool
    {
        return Payment::where('resident_id', $this->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('status', 'PAID')
            ->exists();
    }

    /**
     * Get payment status for current month
     */
    public function getCurrentPaymentStatus(): array
    {
        $payment = Payment::where('resident_id', $this->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        if (!$payment) {
            return [
                'has_paid' => false,
                'status' => 'PENDING',
                'label' => 'No Payment',
                'amount' => $this->rent_amount ?? 0,
                'paid' => 0,
                'balance' => $this->rent_amount ?? 0,
            ];
        }

        $paid = ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0);
        return [
            'has_paid' => $payment->status == 'PAID',
            'status' => $payment->status,
            'label' => $payment->status == 'PAID' ? 'Paid ✅' : 'Pending ❌',
            'amount' => $payment->rent_amount,
            'paid' => $paid,
            'balance' => $payment->balance_amount ?? 0,
        ];
    }
  public function generateEmployeeCode()
    {
        $hostelId = $this->hostel_id;

        if (!$hostelId) {
            throw new \Exception('Hostel ID is required');
        }

        return ($hostelId * 10000) + $this->id;
    }
}
