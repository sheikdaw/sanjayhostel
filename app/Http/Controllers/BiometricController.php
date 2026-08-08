<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Payment;
use App\Services\MockEbioServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BiometricController extends Controller
{
    protected $mockService;

    public function __construct(MockEbioServerService $mockService)
    {
        $this->mockService = $mockService;
    }

    /**
     * Check if biometric columns exist in residents table
     */
    private function checkBiometricColumns()
    {
        $columns = ['employee_code', 'biometric_access', 'last_sync_at'];
        $missing = [];
        
        foreach ($columns as $column) {
            if (!Schema::hasColumn('residents', $column)) {
                $missing[] = $column;
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('Missing columns in residents table: ' . implode(', ', $missing) . '. Please run migration: php artisan migrate');
        }
        
        return true;
    }

    /**
     * Generate employee code with hostel ID: RES_H{hostel_id}_{resident_id+10000}
     * Example: RES_H1_010001, RES_H2_010002
     */
    private function generateEmployeeCode($resident)
    {
        $hostelId = $resident->hostel_id ?? 1; // Default to 1 if not set
        $code = $resident->id + 10000;
        return 'RES_H' . $hostelId . '_' . str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get or create employee code for resident
     */
    private function getOrCreateEmployeeCode($resident)
    {
        // Check if columns exist
        $this->checkBiometricColumns();
        
        // If already has employee code, return it
        // if (!empty($resident->employee_code)) {
        //     return $resident->employee_code;
        // }
        
        // Generate new employee code with hostel ID
        $employeeCode = $this->generateEmployeeCode($resident);
        
        // Save to database
        $resident->employee_code = $employeeCode;
        $resident->biometric_access = true;
        $resident->last_sync_at = now();
        $resident->save();
        
        return $employeeCode;
    }

    /**
     * 1. Sync Single Resident
     */
    public function syncSingle()
    {
        try {
            $this->checkBiometricColumns();
            
            $resident = Resident::first();
            
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'error' => 'No resident found'
                ]);
            }
            
            $employeeCode = $this->getOrCreateEmployeeCode($resident);
            
            // Sync to device
            $result = $this->mockService->updateEmployee([
                'employee_code' => $employeeCode,
                'employee_name' => $resident->name,
                'employee_location' => 'HOSTEL_MAIN',
                'employee_role' => 'Normal Users',
                'employee_verification_type' => '17',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Resident synced successfully',
                'employee_code' => $employeeCode,
                'resident' => $resident->name,
                'hostel_id' => $resident->hostel_id ?? 1,
                'device_sync' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 2. Sync ALL Residents
     */
    public function syncAll()
    {
        try {
            $this->checkBiometricColumns();
            
            $residents = Resident::all();
            
            if ($residents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No residents found'
                ]);
            }
            
            $results = [];
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($residents as $resident) {
                try {
                    $employeeCode = $this->getOrCreateEmployeeCode($resident);
                    
                    // Sync to device
                    $result = $this->mockService->updateEmployee([
                        'employee_code' => $employeeCode,
                        'employee_name' => $resident->name,
                        'employee_location' => 'HOSTEL_MAIN',
                        'employee_role' => 'Normal Users',
                        'employee_verification_type' => '17',
                    ]);
                    
                    $successCount++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'hostel_id' => $resident->hostel_id ?? 1,
                        'employee_code' => $employeeCode,
                        'status' => 'success'
                    ];
                    
                } catch (\Exception $e) {
                    $failureCount++;
                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => $failureCount === 0,
                'total' => $residents->count(),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 3. PUNCH / DOOR ACCESS
     */
    public function punch(Request $request)
    {
        try {
            $this->checkBiometricColumns();
            
            $residentId = $request->resident_id;
            $resident = Resident::find($residentId);
            
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'error' => 'Resident not found',
                    'door' => 'LOCKED'
                ]);
            }

            // Get or create employee code
            $employeeCode = $this->getOrCreateEmployeeCode($resident);
            
            // Refresh resident
            $resident->refresh();
            
            // Get current date details
            $currentDay = now()->day;
            $currentMonth = now()->month;
            $currentYear = now()->year;
            
            // INDIVIDUAL PAYMENT CHECK
            $hasPaid = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('status', 'PAID')
                ->exists();
            
            $doorStatus = 'LOCKED';
            $action = null;
            $message = '';
            
            // LOGIC: Before 10th - Always OPEN | After 10th - Check Payment
            if ($currentDay <= 10) {
                $doorStatus = 'OPEN';
                $action = 'Door opened (Before 10th - Free Access)';
                $message = '🚪 Door opened! (Before 10th - No payment required)';
                
                if (!$resident->biometric_access) {
                    $result = $this->mockService->enableEmployee($employeeCode);
                    if ($result['success'] ?? false) {
                        $resident->update([
                            'biometric_access' => true,
                            'access_enabled_at' => now(),
                            'access_disabled_at' => null
                        ]);
                    }
                }
                
            } elseif ($hasPaid) {
                $doorStatus = 'OPEN';
                $action = 'Door opened (Payment verified)';
                $message = '🚪 Door opened! Payment verified ✅';
                
                if (!$resident->biometric_access) {
                    $result = $this->mockService->enableEmployee($employeeCode);
                    if ($result['success'] ?? false) {
                        $resident->update([
                            'biometric_access' => true,
                            'access_enabled_at' => now(),
                            'access_disabled_at' => null
                        ]);
                    }
                }
                
            } else {
                $doorStatus = 'LOCKED';
                $action = 'Door locked (Payment pending)';
                $message = '🔒 Door locked! Please pay rent for this month';
                
                if ($resident->biometric_access) {
                    $result = $this->mockService->disableEmployee($employeeCode);
                    if ($result['success'] ?? false) {
                        $resident->update([
                            'biometric_access' => false,
                            'access_disabled_at' => now()
                        ]);
                    }
                }
            }
            
            // Get payment details
            $payment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();
            
            return response()->json([
                'success' => $doorStatus === 'OPEN',
                'resident' => $resident->name,
                'resident_id' => $resident->id,
                'hostel_id' => $resident->hostel_id ?? 1,
                'employee_code' => $employeeCode,
                'door' => $doorStatus,
                'has_paid' => $hasPaid,
                'payment_details' => $payment ? [
                    'amount' => $payment->rent_amount,
                    'paid' => ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0),
                    'balance' => $payment->balance_amount ?? 0,
                    'status' => $payment->status
                ] : null,
                'day_of_month' => $currentDay,
                'month' => $currentMonth,
                'year' => $currentYear,
                'action' => $action,
                'message' => $message,
                'rule_applied' => $currentDay <= 10 ? 'Before 10th - Free Access' : ($hasPaid ? 'Payment Verified' : 'Payment Pending')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'door' => 'LOCKED'
            ]);
        }
    }

    /**
     * 4. Check Single Resident Payment Status
     */
    public function checkPayment($id)
    {
        try {
            $this->checkBiometricColumns();
            
            $resident = Resident::findOrFail($id);
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentDay = now()->day;
            
            $hasPaid = Payment::where('resident_id', $id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('status', 'PAID')
                ->exists();
            
            $payment = Payment::where('resident_id', $id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();
            
            $doorStatus = 'LOCKED';
            if ($currentDay <= 10) {
                $doorStatus = 'OPEN (Before 10th - Free)';
            } elseif ($hasPaid) {
                $doorStatus = 'OPEN (Paid)';
            } else {
                $doorStatus = 'LOCKED (Payment Pending)';
            }
            
            return response()->json([
                'success' => true,
                'resident' => $resident->name,
                'resident_id' => $resident->id,
                'hostel_id' => $resident->hostel_id ?? 1,
                'employee_code' => $resident->employee_code ?? 'Not synced',
                'has_paid' => $hasPaid,
                'payment_details' => $payment ? [
                    'amount' => $payment->rent_amount,
                    'paid' => ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0),
                    'balance' => $payment->balance_amount ?? 0,
                    'status' => $payment->status
                ] : null,
                'access_enabled' => $resident->biometric_access,
                'day_of_month' => $currentDay,
                'month' => $currentMonth,
                'year' => $currentYear,
                'door_status' => $doorStatus
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 5. Daily Payment Check - All Residents
     */
    public function dailyCheck()
    {
        try {
            $this->checkBiometricColumns();
            
            $residents = Resident::all();
            
            if ($residents->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'total' => 0,
                    'message' => 'No residents found in database',
                    'data' => []
                ]);
            }
            
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentDay = now()->day;
            $results = [];
            
            foreach ($residents as $resident) {
                // Ensure employee code exists
                $employeeCode = $this->getOrCreateEmployeeCode($resident);
                $resident->refresh();
                
                // Check payment
                $hasPaid = Payment::where('resident_id', $resident->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->where('status', 'PAID')
                    ->exists();
                
                $action = null;
                $doorStatus = 'LOCKED';
                
                // Apply rules
                if ($currentDay <= 10) {
                    $doorStatus = 'OPEN';
                    if (!$resident->biometric_access) {
                        $result = $this->mockService->enableEmployee($employeeCode);
                        if ($result['success'] ?? false) {
                            $resident->update([
                                'biometric_access' => true,
                                'access_enabled_at' => now(),
                                'access_disabled_at' => null
                            ]);
                            $action = 'enabled (Before 10th)';
                        }
                    }
                } elseif ($hasPaid) {
                    $doorStatus = 'OPEN';
                    if (!$resident->biometric_access) {
                        $result = $this->mockService->enableEmployee($employeeCode);
                        if ($result['success'] ?? false) {
                            $resident->update([
                                'biometric_access' => true,
                                'access_enabled_at' => now(),
                                'access_disabled_at' => null
                            ]);
                            $action = 'enabled (Payment verified)';
                        }
                    }
                } else {
                    $doorStatus = 'LOCKED';
                    if ($resident->biometric_access) {
                        $result = $this->mockService->disableEmployee($employeeCode);
                        if ($result['success'] ?? false) {
                            $resident->update([
                                'biometric_access' => false,
                                'access_disabled_at' => now()
                            ]);
                            $action = 'disabled (No payment)';
                        }
                    }
                }
                
                $payment = Payment::where('resident_id', $resident->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->first();
                
                $results[] = [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'hostel_id' => $resident->hostel_id ?? 1,
                    'employee_code' => $employeeCode,
                    'has_paid' => $hasPaid,
                    'payment_amount' => $payment ? $payment->rent_amount : 0,
                    'paid_amount' => $payment ? (($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0)) : 0,
                    'balance' => $payment ? $payment->balance_amount : 0,
                    'access_enabled' => $resident->biometric_access,
                    'door_status' => $doorStatus,
                    'action' => $action,
                    'day_of_month' => $currentDay,
                    'month' => $currentMonth,
                    'year' => $currentYear
                ];
            }
            
            return response()->json([
                'success' => true,
                'total' => $residents->count(),
                'day_of_month' => $currentDay,
                'month' => $currentMonth,
                'year' => $currentYear,
                'rule' => $currentDay <= 10 ? 'Before 10th - All Access Open' : 'After 10th - Payment Check Required',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * 6. Attendance
     */
    public function attendance()
    {
        try {
            $result = $this->mockService->getDeviceLogs(now()->format('Y-m-d'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 7. Device Status
     */
    public function deviceStatus()
    {
        try {
            return response()->json([
                'status' => $this->mockService->getDeviceLastPing('DEV_001'),
                'reboot' => $this->mockService->rebootDevice('DEV_001'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 8. Get Dashboard Stats
     */
    public function stats()
    {
        try {
            $this->checkBiometricColumns();
            
            $total = Resident::count();
            $synced = Resident::whereNotNull('employee_code')->count();
            $accessEnabled = Resident::where('biometric_access', true)->count();
            
            return response()->json([
                'success' => true,
                'total' => $total,
                'synced' => $synced,
                'access_enabled' => $accessEnabled
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 9. Get Residents List with Biometric Status
     */
    public function residentsList()
    {
        try {
            $this->checkBiometricColumns();
            
            $residents = Resident::all()->map(function($resident) {
                return [
                    'id' => $resident->id,
                    'name' => $resident->name,
                    'email' => $resident->email ?? 'N/A',
                    'hostel_id' => $resident->hostel_id ?? 1,
                    'employee_code' => $resident->employee_code ?? 'Not synced',
                    'biometric_access' => $resident->biometric_access ? 'Enabled' : 'Disabled',
                    'last_sync_at' => $resident->last_sync_at ? $resident->last_sync_at->format('Y-m-d H:i:s') : 'Never'
                ];
            });
            
            return response()->json([
                'success' => true,
                'total' => $residents->count(),
                'data' => $residents
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}