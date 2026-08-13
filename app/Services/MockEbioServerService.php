<?php
// app/Services/MockEbioServerService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MockEbioServerService
{
    protected array $employees = [];
    protected array $logs = [];
    protected array $devices = [
        'DEV_001' => [
            'serial' => 'DEV_001',
            'name' => 'Main Door Device',
            'status' => 'online',
            'last_ping' => null
        ],
        'DEV_002' => [
            'serial' => 'DEV_002',
            'name' => 'Backup Device',
            'status' => 'online',
            'last_ping' => null
        ]
    ];

    public function __construct()
    {
        // Initialize with some mock data
        $this->logs = [
            '2024-01-15' => [
                'EMP001' => ['08:00:00', '17:30:00'],
                'EMP002' => ['08:15:00', '17:45:00'],
            ]
        ];
    }

    /**
     * Update Employee
     */
    public function updateEmployee(array $data): array
    {
        $code = $data['employee_code'];
        $this->employees[$code] = [
            'code' => $code,
            'name' => $data['employee_name'],
            'location' => $data['employee_location'] ?? 'HOSTEL_MAIN',
            'role' => $data['employee_role'] ?? 'Normal Users',
            'verification_type' => $data['employee_verification_type'] ?? '17',
            'enabled' => true,
            'synced_at' => now()->toDateTimeString()
        ];

        Log::info('Mock: Employee updated', ['code' => $code]);

        return [
            'success' => true,
            'message' => "Employee {$code} updated successfully",
            'data' => $this->employees[$code]
        ];
    }

    /**
     * Enable Employee Access
     */
    public function enableEmployee(string $employeeCode): array
    {
        if (!isset($this->employees[$employeeCode])) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $this->employees[$employeeCode]['enabled'] = true;
        $this->employees[$employeeCode]['access_enabled_at'] = now()->toDateTimeString();

        return [
            'success' => true,
            'message' => "Employee {$employeeCode} access enabled"
        ];
    }

    /**
     * Disable Employee Access
     */
    public function disableEmployee(string $employeeCode): array
    {
        if (!isset($this->employees[$employeeCode])) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $this->employees[$employeeCode]['enabled'] = false;
        $this->employees[$employeeCode]['access_disabled_at'] = now()->toDateTimeString();

        return [
            'success' => true,
            'message' => "Employee {$employeeCode} access disabled"
        ];
    }

    /**
     * Get Employee Details
     */
    public function getEmployeeDetails(string $employeeCode): array
    {
        if (!isset($this->employees[$employeeCode])) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        return [
            'success' => true,
            'data' => $this->employees[$employeeCode]
        ];
    }

    /**
     * Delete Employee
     */
    public function deleteEmployee(string $employeeCode): array
    {
        if (!isset($this->employees[$employeeCode])) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        unset($this->employees[$employeeCode]);
        return [
            'success' => true,
            'message' => "Employee {$employeeCode} deleted"
        ];
    }

    /**
     * Get Device Logs
     */
    public function getDeviceLogs(string $date): array
    {
        $logs = $this->logs[$date] ?? [];
        $formattedLogs = [];

        foreach ($logs as $code => $times) {
            foreach ($times as $time) {
                $formattedLogs[] = [
                    'employee_code' => $code,
                    'time' => $time,
                    'device' => 'DEV_001',
                    'type' => 'punch'
                ];
            }
        }

        return [
            'success' => true,
            'data' => $formattedLogs,
            'count' => count($formattedLogs)
        ];
    }

    /**
     * Get Device Last Ping
     */
    public function getDeviceLastPing(string $deviceSerialNumber): array
    {
        $device = $this->devices[$deviceSerialNumber] ?? null;

        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        return [
            'success' => true,
            'device' => $deviceSerialNumber,
            'status' => $device['status'],
            'last_ping' => $device['last_ping'] ?? now()->toDateTimeString(),
            'is_online' => $device['status'] === 'online'
        ];
    }

    /**
     * Reboot Device
     */
    public function rebootDevice(string $deviceSerialNumber): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        $this->devices[$deviceSerialNumber]['status'] = 'rebooting';
        $this->devices[$deviceSerialNumber]['last_reboot'] = now()->toDateTimeString();

        // Simulate reboot
        sleep(1);
        $this->devices[$deviceSerialNumber]['status'] = 'online';

        return [
            'success' => true,
            'message' => "Device {$deviceSerialNumber} rebooted successfully"
        ];
    }

    /**
     * Get Device List
     */
    public function getDeviceList(): array
    {
        return [
            'success' => true,
            'data' => array_values($this->devices)
        ];
    }

    /**
     * Block/Unblock User
     */
    public function blockUnblockUser(string $deviceSerialNumber, string $employeeCode, bool $block = true): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        if (!isset($this->employees[$employeeCode])) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        $this->employees[$employeeCode]['blocked'] = $block;
        $this->employees[$employeeCode]['blocked_at'] = now()->toDateTimeString();

        return [
            'success' => true,
            'message' => "Employee {$employeeCode} " . ($block ? 'blocked' : 'unblocked') . " on device {$deviceSerialNumber}"
        ];
    }

    /**
     * Unlock Door
     */
    public function unlockDoor(string $deviceSerialNumber): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        return [
            'success' => true,
            'message' => "Door unlocked on device {$deviceSerialNumber}",
            'device' => $deviceSerialNumber,
            'unlocked_at' => now()->toDateTimeString()
        ];
    }

    /**
     * Clear Device Logs
     */
    public function clearDeviceLogs(string $deviceSerialNumber): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        $this->logs = [];
        return [
            'success' => true,
            'message' => "Device {$deviceSerialNumber} logs cleared"
        ];
    }

    /**
     * Reset OP Stamp
     */
    public function resetOpStamp(string $deviceSerialNumber): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        return [
            'success' => true,
            'message' => "OP Stamp reset for device {$deviceSerialNumber}",
            'employees_synced' => count($this->employees)
        ];
    }

    /**
     * Reset Transaction Stamp
     */
    public function resetTransactionStamp(string $deviceSerialNumber): array
    {
        if (!isset($this->devices[$deviceSerialNumber])) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        return [
            'success' => true,
            'message' => "Transaction Stamp reset for device {$deviceSerialNumber}"
        ];
    }

    /**
     * Add mock log entry
     */
    public function addLog(string $date, string $employeeCode, string $time): void
    {
        if (!isset($this->logs[$date])) {
            $this->logs[$date] = [];
        }
        if (!isset($this->logs[$date][$employeeCode])) {
            $this->logs[$date][$employeeCode] = [];
        }
        $this->logs[$date][$employeeCode][] = $time;
    }

    /**
     * Get all employees
     */
    public function getEmployees(): array
    {
        return $this->employees;
    }
}