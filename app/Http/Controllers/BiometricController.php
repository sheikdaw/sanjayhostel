<?php
// app/Http/Controllers/BiometricController.php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Payment;
use App\Services\EbioServerService;
use App\Services\MockEbioServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BiometricController extends Controller
{
    protected $ebioService;
    protected $mockService;
    protected $useMock = false; // Set to false to use real eBioServer

    public function __construct(EbioServerService $ebioService, MockEbioServerService $mockService)
    {
        $this->ebioService = $ebioService;
        $this->mockService = $mockService;
    }

    /**
     * Get the appropriate service (mock or real)
     */
    protected function getService()
    {
        return $this->useMock ? $this->mockService : $this->ebioService;
    }

    /**
     * Check if biometric columns exist
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
            throw new \Exception('Missing columns: ' . implode(', ', $missing) . '. Run: php artisan migrate');
        }
    }

    /**
     * Generate employee code: RES_H{hostel_id}_{resident_id+10000}
     */
    private function generateEmployeeCode($resident)
    {
        $hostelId = $resident->hostel_id ?? 1;
        $code = $resident->id + 10000;
        return  $code;
    }

    /**
     * Get or create employee code
     */
    private function getOrCreateEmployeeCode($resident)
    {
        $this->checkBiometricColumns();

        if (empty($resident->employee_code)) {
            $employeeCode = $this->generateEmployeeCode($resident);
            $resident->employee_code = $employeeCode;
            $resident->save();
        }

        return $resident->employee_code;
    }

    /**
     * ============================================
     * RESIDENT SYNC METHODS
     * ============================================
     */

    /**
     * Sync Single Resident
     * GET|POST /api/test/sync-single
     */
    public function syncSingle(Request $request)
    {
        try {
            $this->checkBiometricColumns();

            $residentId = $request->input('resident_id', 1);
            $resident = Resident::find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'error' => 'Resident not found'
                ]);
            }

            $employeeCode = $this->getOrCreateEmployeeCode($resident);
            $resident->refresh();

            $service = $this->getService();
            $result = $service->updateEmployee([
                'employee_code' => $employeeCode,
                'employee_name' => $resident->name,
                'employee_location' => 'HOSTEL_MAIN',
                'employee_role' => 'Normal Users',
                'employee_verification_type' => '17',
            ]);

            if ($result['success'] ?? false) {
                $resident->last_sync_at = now();
                $resident->save();
            }

            return response()->json([
                'success' => $result['success'] ?? false,
                'resident' => $resident->name,
                'employee_code' => $employeeCode,
                'hostel_id' => $resident->hostel_id ?? 1,
                'device_response' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync ALL Residents
     * GET|POST /api/test/sync-all
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
            $service = $this->getService();

            foreach ($residents as $resident) {
                try {
                    $employeeCode = $this->getOrCreateEmployeeCode($resident);
                    $resident->refresh();

                    $result = $service->updateEmployee([
                        'employee_code' => $employeeCode,
                        'employee_name' => $resident->name,
                        'employee_location' => 'HOSTEL_MAIN',
                        'employee_role' => 'Normal Users',
                        'employee_verification_type' => '17',
                    ]);

                    if ($result['success'] ?? false) {
                        $resident->last_sync_at = now();
                        $resident->save();
                        $successCount++;
                    } else {
                        $failureCount++;
                    }

                    $results[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'employee_code' => $employeeCode,
                        'status' => ($result['success'] ?? false) ? 'success' : 'failed',
                        'message' => $result['message'] ?? 'Unknown error'
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
     * ============================================
     * DOOR ACCESS / PUNCH METHODS
     * ============================================
     */

    /**
     * Punch / Door Access with Individual Payment Check
     * POST /api/test/punch
     */
    public function punch(Request $request)
    {
        try {
            $this->checkBiometricColumns();

            $residentId = $request->input('resident_id');
            $resident = Resident::find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'error' => 'Resident not found',
                    'door' => 'LOCKED'
                ]);
            }

            $employeeCode = $this->getOrCreateEmployeeCode($resident);
            $resident->refresh();

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
            $service = $this->getService();

            // DOOR LOGIC
            if ($currentDay <= 10) {
                // ✅ BEFORE 10TH - DOOR ALWAYS OPEN
                $doorStatus = 'OPEN';
                $action = 'Door opened (Before 10th - Free Access)';
                $message = '🚪 Door opened! (Before 10th - No payment required)';

                if (!$resident->biometric_access) {
                    $result = $service->enableEmployee($employeeCode);
                    if ($result['success'] ?? false) {
                        $resident->update([
                            'biometric_access' => true,
                            'access_enabled_at' => now(),
                            'access_disabled_at' => null
                        ]);
                    }
                }
            } elseif ($hasPaid) {
                // ✅ AFTER 10TH AND PAID - DOOR OPEN
                $doorStatus = 'OPEN';
                $action = 'Door opened (Payment verified)';
                $message = '🚪 Door opened! Payment verified ✅';

                if (!$resident->biometric_access) {
                    $result = $service->enableEmployee($employeeCode);
                    if ($result['success'] ?? false) {
                        $resident->update([
                            'biometric_access' => true,
                            'access_enabled_at' => now(),
                            'access_disabled_at' => null
                        ]);
                    }
                }
            } else {
                // ❌ AFTER 10TH AND NOT PAID - DOOR LOCKED
                $doorStatus = 'LOCKED';
                $action = 'Door locked (Payment pending)';
                $message = '🔒 Door locked! Please pay rent for this month';

                if ($resident->biometric_access) {
                    $result = $service->disableEmployee($employeeCode);
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
     * Check Single Resident Payment Status
     * GET /api/test/check-payment/{id}
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
     * Daily Payment Check - All Residents
     * GET /api/test/daily-check
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
                    'message' => 'No residents found',
                    'data' => []
                ]);
            }

            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentDay = now()->day;
            $results = [];
            $service = $this->getService();

            foreach ($residents as $resident) {
                $employeeCode = $this->getOrCreateEmployeeCode($resident);
                $resident->refresh();

                $hasPaid = Payment::where('resident_id', $resident->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->where('status', 'PAID')
                    ->exists();

                $action = null;
                $doorStatus = 'LOCKED';

                if ($currentDay <= 10) {
                    $doorStatus = 'OPEN';
                    if (!$resident->biometric_access) {
                        $result = $service->enableEmployee($employeeCode);
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
                        $result = $service->enableEmployee($employeeCode);
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
                        $result = $service->disableEmployee($employeeCode);
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
                    'day_of_month' => $currentDay
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
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ============================================
     * ATTENDANCE METHODS
     * ============================================
     */

    /**
     * Get Attendance
     * GET /api/test/attendance
     */
    public function attendance(Request $request)
    {
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            $service = $this->getService();
            $result = $service->getDeviceLogs($date);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Employee Punch Logs
     * GET /api/test/employee-punch-logs
     */
    public function employeePunchLogs(Request $request)
    {
        try {
            $employeeCode = $request->input('employee_code');
            $date = $request->input('date', now()->format('Y-m-d'));

            if (!$employeeCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'employee_code required'
                ]);
            }

            $service = $this->getService();
            $result = $service->getEmployeePunchLogs($employeeCode, $date);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ============================================
     * DEVICE MANAGEMENT METHODS
     * ============================================
     */

    /**
     * Get Device Status
     * GET /api/test/device
     */
    public function deviceStatus(Request $request)
    {
        try {
            $deviceSerial = $request->input('device_serial', 'DEV_001');
            $service = $this->getService();

            return response()->json([
                'status' => $service->getDeviceLastPing($deviceSerial),
                'reboot' => $service->rebootDevice($deviceSerial),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Device List
     * GET /api/test/devices
     */
    public function deviceList()
    {
        try {
            $service = $this->getService();
            return response()->json($service->getDeviceList());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Unlock Door
     * POST /api/test/unlock-door
     */
    public function unlockDoor(Request $request)
    {
        try {
            $deviceSerial = $request->input('device_serial', 'DEV_001');
            $service = $this->getService();
            $result = $service->unlockDoor($deviceSerial);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Block/Unblock User
     * POST /api/test/block-user
     */
    public function blockUser(Request $request)
    {
        try {
            $employeeCode = $request->input('employee_code');
            $deviceSerial = $request->input('device_serial', 'DEV_001');
            $block = $request->input('block', true);

            if (!$employeeCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'employee_code required'
                ]);
            }

            $service = $this->getService();
            $result = $service->blockUnblockUser($deviceSerial, $employeeCode, $block);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ============================================
     * DASHBOARD / STATS METHODS
     * ============================================
     */

    /**
     * Get Dashboard Stats
     * GET /api/test/stats
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
                'access_enabled' => $accessEnabled,
                'sync_percentage' => $total > 0 ? round(($synced / $total) * 100, 2) : 0,
                'access_percentage' => $total > 0 ? round(($accessEnabled / $total) * 100, 2) : 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Residents List with Biometric Status
     * GET /api/test/residents
     */
    public function residentsList()
    {
        try {
            $this->checkBiometricColumns();

            $residents = Resident::all()->map(function ($resident) {
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

    /**
     * ============================================
     * EBIOSERVER DIRECT METHODS
     * ============================================
     */

    /**
     * Test eBioServer Connection
     * GET /api/test/connection
     */
    // public function testConnection()
    // {
    //     try {
    //         $service = $this->useMock ? $this->ebioService : $this->getService();
    //         $result = $service->testConnection();
    //         return response()->json($result);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    /**
     * Get Employee Codes
     * GET /api/test/employee-codes
     */
    public function getEmployeeCodes()
    {
        try {
            $service = $this->getService();
            return response()->json($service->getEmployeeCodes());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Employee Details
     * GET /api/test/employee-details
     */
    public function getEmployeeDetails(Request $request)
    {
        try {
            $employeeCode = $request->input('employee_code');
            if (!$employeeCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'employee_code required'
                ]);
            }

            $service = $this->getService();
            return response()->json($service->getEmployeeDetails($employeeCode));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete Employee
     * POST /api/test/delete-employee
     */
    public function deleteEmployee(Request $request)
    {
        try {
            $employeeCode = $request->input('employee_code');
            if (!$employeeCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'employee_code required'
                ]);
            }

            // Also update resident record
            Resident::where('employee_code', $employeeCode)->update([
                'employee_code' => null,
                'biometric_access' => false,
                'last_sync_at' => null
            ]);

            $service = $this->getService();
            $result = $service->deleteEmployee($employeeCode);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ============================================
     * VISITOR METHODS
     * ============================================
     */

    /**
     * Validate Visitor
     * POST /api/test/validate-visitor
     */
    public function validateVisitor(Request $request)
    {
        try {
            $visitorCode = $request->input('visitor_code');
            if (!$visitorCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'visitor_code required'
                ]);
            }

            $service = $this->getService();
            $result = $service->validateVisitorDesk($visitorCode);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function testConnection()
    {
        try {
            $service = $this->useMock ? $this->ebioService : $this->getService();
            $result = $service->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
