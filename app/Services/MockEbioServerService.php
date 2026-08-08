<?php

namespace App\Services;

class MockEbioServerService
{
    protected $employees = [];
    protected $logs = [];
    protected $deviceStatus = [];
    
    public function __construct()
    {
        // Initialize with some mock data
        $this->deviceStatus = [
            'DEV_001' => [
                'online' => true,
                'last_ping' => now(),
                'reboot_count' => 0
            ]
        ];
    }

    /**
     * Update or create employee on device
     */
    public function updateEmployee(array $data): array
    {
        try {
            $employeeCode = $data['employee_code'] ?? null;
            
            if (!$employeeCode) {
                return [
                    'success' => false,
                    'message' => 'Employee code is required'
                ];
            }
            
            // Store in mock database
            $this->employees[$employeeCode] = array_merge(
                $this->employees[$employeeCode] ?? [],
                $data,
                ['synced_at' => now()->toDateTimeString()]
            );
            
            return [
                'success' => true,
                'message' => 'Employee synced successfully',
                'data' => $this->employees[$employeeCode]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Enable employee access - NEW METHOD
     */
    public function enableEmployee(string $employeeCode): array
    {
        try {
            if (!isset($this->employees[$employeeCode])) {
                return [
                    'success' => false,
                    'message' => 'Employee not found on device'
                ];
            }
            
            $this->employees[$employeeCode]['access_enabled'] = true;
            $this->employees[$employeeCode]['enabled_at'] = now()->toDateTimeString();
            
            return [
                'success' => true,
                'message' => 'Employee access enabled successfully',
                'employee_code' => $employeeCode,
                'data' => $this->employees[$employeeCode]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Disable employee access - NEW METHOD
     */
    public function disableEmployee(string $employeeCode): array
    {
        try {
            if (!isset($this->employees[$employeeCode])) {
                return [
                    'success' => false,
                    'message' => 'Employee not found on device'
                ];
            }
            
            $this->employees[$employeeCode]['access_enabled'] = false;
            $this->employees[$employeeCode]['disabled_at'] = now()->toDateTimeString();
            
            return [
                'success' => true,
                'message' => 'Employee access disabled successfully',
                'employee_code' => $employeeCode,
                'data' => $this->employees[$employeeCode]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get device logs (attendance)
     */
    public function getDeviceLogs(string $date): array
    {
        try {
            // Generate mock logs
            $mockLogs = [];
            $logCount = rand(5, 20);
            
            for ($i = 0; $i < $logCount; $i++) {
                $employeeCodes = array_keys($this->employees);
                if (empty($employeeCodes)) {
                    $employeeCodes = ['RES_010001', 'RES_010002', 'RES_010003'];
                }
                
                $mockLogs[] = sprintf(
                    '%s|%s|%s',
                    $date . ' ' . str_pad(rand(8, 20), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    $employeeCodes[array_rand($employeeCodes)],
                    rand(1, 5) // 1 = In, 2 = Out, etc.
                );
            }
            
            return [
                'success' => true,
                'data' => implode(';', $mockLogs),
                'count' => $logCount,
                'date' => $date
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get device last ping
     */
    public function getDeviceLastPing(string $deviceId): array
    {
        try {
            $status = $this->deviceStatus[$deviceId] ?? [
                'online' => false,
                'last_ping' => null
            ];
            
            return [
                'success' => $status['online'] ?? false,
                'device_id' => $deviceId,
                'online' => $status['online'] ?? false,
                'last_ping' => $status['last_ping'] ?? null,
                'message' => $status['online'] ? 'Device is online' : 'Device is offline'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reboot device
     */
    public function rebootDevice(string $deviceId): array
    {
        try {
            if (!isset($this->deviceStatus[$deviceId])) {
                return [
                    'success' => false,
                    'message' => 'Device not found'
                ];
            }
            
            $this->deviceStatus[$deviceId]['reboot_count'] = ($this->deviceStatus[$deviceId]['reboot_count'] ?? 0) + 1;
            $this->deviceStatus[$deviceId]['last_reboot'] = now()->toDateTimeString();
            
            return [
                'success' => true,
                'message' => 'Device reboot command sent successfully',
                'device_id' => $deviceId,
                'reboot_count' => $this->deviceStatus[$deviceId]['reboot_count']
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all employees (for debugging)
     */
    public function getEmployees(): array
    {
        return $this->employees;
    }

    /**
     * Get employee by code
     */
    public function getEmployee(string $employeeCode): ?array
    {
        return $this->employees[$employeeCode] ?? null;
    }
}