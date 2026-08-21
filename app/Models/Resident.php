<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Generate employee code based on hostel ID
     * Format: HOSTEL_CODE + SEQUENTIAL_NUMBER
     * Example: For Hostel ID 1 (SAN), first resident: SAN001, second: SAN002, etc.
     */
    public function generateEmployeeCode(): string
    {
        // Get hostel code prefix
        $hostelCode = $this->getHostelCodePrefix();
        
        // Get the next sequential number for this hostel
        $lastResident = Resident::where('hostel_id', $this->hostel_id)
            ->whereNotNull('employee_code')
            ->orderBy('employee_code', 'desc')
            ->first();

        if ($lastResident && $lastResident->employee_code) {
            // Extract the number part from the last employee code
            // Example: SAN001 -> 001
            $lastNumber = (int) substr($lastResident->employee_code, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format: HOSTEL_PREFIX + 3-digit number (padded with zeros)
        return $hostelCode . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate employee code based on hostel ID (Alternative - Numeric Only)
     * Format: HOSTEL_ID + SEQUENTIAL_NUMBER
     * Example: For Hostel ID 1, first resident: 10001, second: 10002, etc.
     */
    public function generateEmployeeCodeNumeric(): int
    {
        // Get hostel ID as base (e.g., 1, 2, 3)
        $hostelId = $this->hostel_id ?? 1;
        
        // Get the count of residents in this hostel
        $residentCount = Resident::where('hostel_id', $hostelId)->count();
        
        // Generate code: HostelID * 10000 + (Count + 1)
        // Example: Hostel 1 -> 10001, 10002, 10003...
        // Hostel 2 -> 20001, 20002, 20003...
        $code = ($hostelId * 10000) + ($residentCount + 1);
        
        return $code;
    }

    /**
     * Generate employee code with custom format
     * Format: HOSTEL_CODE + YEAR + SEQUENTIAL_NUMBER
     * Example: SAN2026001, SAN2026002, etc.
     */
    public function generateEmployeeCodeWithYear(): string
    {
        $hostelCode = $this->getHostelCodePrefix();
        $year = date('Y');
        
        $lastResident = Resident::where('hostel_id', $this->hostel_id)
            ->whereNotNull('employee_code')
            ->where('employee_code', 'LIKE', $hostelCode . $year . '%')
            ->orderBy('employee_code', 'desc')
            ->first();

        if ($lastResident && $lastResident->employee_code) {
            $lastNumber = (int) substr($lastResident->employee_code, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $hostelCode . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get hostel code prefix for employee code
     */
    private function getHostelCodePrefix(): string
    {
        $hostel = Hostel::find($this->hostel_id);
        if ($hostel) {
            // Remove spaces and take first 3 characters
            $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $hostel->hostel_code));
            return substr($code, 0, 3);
        }
        
        // Default fallback based on hostel ID
        return 'H' . str_pad($this->hostel_id ?? 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get next employee code for a specific hostel (without saving)
     */
    public static function getNextEmployeeCodeForHostel(int $hostelId): string
    {
        $hostel = Hostel::find($hostelId);
        $hostelCode = $hostel ? substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $hostel->hostel_code)), 0, 3) : 'H' . str_pad($hostelId, 2, '0', STR_PAD_LEFT);
        
        $lastResident = Resident::where('hostel_id', $hostelId)
            ->whereNotNull('employee_code')
            ->orderBy('employee_code', 'desc')
            ->first();

        if ($lastResident && $lastResident->employee_code) {
            $lastNumber = (int) substr($lastResident->employee_code, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $hostelCode . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get next numeric employee code for a specific hostel (without saving)
     */
    public static function getNextNumericEmployeeCodeForHostel(int $hostelId): int
    {
        $residentCount = Resident::where('hostel_id', $hostelId)->count();
        return ($hostelId * 10000) + ($residentCount + 1);
    }

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
}