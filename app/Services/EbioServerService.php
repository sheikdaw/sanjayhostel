<?php
// app/Services/EbioServerService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EbioServerService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $locationCode;

    public function __construct()
    {
        $this->baseUrl = config('ebioserver.url', 'http://localhost/Webservice.asmx');
        $this->username = config('ebioserver.username', 'admin');
        $this->password = config('ebioserver.password', 'admin');
        $this->locationCode = config('ebioserver.location_code', 'LOC_001');
    }

    /**
     * Generate SOAP request XML
     */
    protected function generateSoapRequest(string $method, array $params): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $xml .= 'xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '<soap:Body>';
        $xml .= "<{$method} xmlns=\"http://tempuri.org/\">";
        
        // Always include credentials
        $xml .= "<UserName>{$this->username}</UserName>";
        $xml .= "<Password>{$this->password}</Password>";
        
        // Add additional parameters
        foreach ($params as $key => $value) {
            $xml .= "<{$key}>" . htmlspecialchars($value) . "</{$key}>";
        }
        
        $xml .= "</{$method}>";
        $xml .= '</soap:Body>';
        $xml .= '</soap:Envelope>';
        
        return $xml;
    }

    /**
     * Send SOAP request
     */
    protected function sendRequest(string $method, array $params): array
    {
        try {
            $soapBody = $this->generateSoapRequest($method, $params);
            
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => "http://tempuri.org/{$method}",
                'Content-Length' => strlen($soapBody),
            ])->send('POST', $this->baseUrl, [
                'body' => $soapBody,
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to eBioServer',
                    'error' => $response->body()
                ];
            }

            // Parse SOAP response
            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return [
                    'success' => false,
                    'message' => 'Invalid response from eBioServer'
                ];
            }

            // Extract result from SOAP response
            $namespaces = $xml->getNamespaces(true);
            $soapBody = $xml->children($namespaces['soap'])->Body;
            $responseElement = $soapBody->children('http://tempuri.org/');
            
            if (!$responseElement) {
                return [
                    'success' => false,
                    'message' => 'Invalid response structure'
                ];
            }

            $resultElement = $responseElement->{$method . 'Result'} ?? $responseElement;
            $result = (string) $resultElement;

            // Check if result indicates success
            $isSuccess = !str_contains(strtolower($result), 'error') 
                && !str_contains(strtolower($result), 'fail');

            return [
                'success' => $isSuccess,
                'message' => $result,
                'data' => $result
            ];

        } catch (Exception $e) {
            Log::error('eBioServer API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add or Update Employee
     */
    public function updateEmployee(array $data): array
    {
        $params = [
            'EmployeeCode' => $data['employee_code'],
            'EmployeeName' => $data['employee_name'],
            'EmployeeLocation' => $data['location'] ?? $this->locationCode,
            'EmployeeRole' => $data['employee_role'] ?? 'Normal Users',
            'EmployeeVerificationType' => $data['verification_type'] ?? '17', // Face + Fingerprint
            'EmployeeCardNumber' => $data['card_number'] ?? '',
        ];

        return $this->sendRequest('UpdateEmployee', $params);
    }

    /**
     * Add/Update Employee with Expiry
     */
    public function updateEmployeeWithExpiry(array $data): array
    {
        $params = [
            'EmployeeCode' => $data['employee_code'],
            'EmployeeName' => $data['employee_name'],
            'EmployeeLocation' => $data['location'] ?? $this->locationCode,
            'EmployeeRole' => $data['employee_role'] ?? 'Normal Users',
            'EmployeeVerificationType' => $data['verification_type'] ?? '17',
            'EmployeeExpiryFrom' => $data['expiry_from'] ?? date('Y-m-d'),
            'EmployeeExpiryTo' => $data['expiry_to'] ?? date('Y-m-d', strtotime('+1 year')),
            'EmployeeCardNumber' => $data['card_number'] ?? '',
        ];

        return $this->sendRequest('UpdateEmployeewithExpiryDates', $params);
    }

    /**
     * Update Employee Photo
     */
    public function updateEmployeePhoto(string $employeeCode, string $photoBase64): array
    {
        return $this->sendRequest('UpdateEmployeePhoto', [
            'EmployeeCode' => $employeeCode,
            'EmployeePhoto' => $photoBase64,
        ]);
    }

    /**
     * Get Employee Details
     */
    public function getEmployeeDetails(string $employeeCode): array
    {
        return $this->sendRequest('GetEmployeeDetails', [
            'EmployeeCode' => $employeeCode,
        ]);
    }

    /**
     * Get Employee Punch Logs
     */
    public function getEmployeePunchLogs(string $employeeCode, string $date): array
    {
        return $this->sendRequest('GetEmployeePunchLogs', [
            'EmployeeCode' => $employeeCode,
            'AttendanceDate' => $date,
        ]);
    }

    /**
     * Delete Employee
     */
    public function deleteEmployee(string $employeeCode): array
    {
        return $this->sendRequest('DeleteEmployee', [
            'EmployeeCode' => $employeeCode,
        ]);
    }

    /**
     * Get Device List
     */
    public function getDeviceList(?string $location = null): array
    {
        return $this->sendRequest('GetDeviceList', [
            'Location' => $location ?? $this->locationCode,
        ]);
    }

    /**
     * Get Device Logs
     */
    public function getDeviceLogs(string $date, ?string $location = null): array
    {
        return $this->sendRequest('GetDeviceLogs', [
            'Location' => $location ?? $this->locationCode,
            'LogDate' => $date,
        ]);
    }

    /**
     * Get Device Last Ping
     */
    public function getDeviceLastPing(string $deviceSerialNumber): array
    {
        return $this->sendRequest('GetDeviceLastPing', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Reboot Device
     */
    public function rebootDevice(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_Reboot', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Clear Device Logs
     */
    public function clearDeviceLogs(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ClearLogs', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Reset OP Stamp - Sync employees to device
     */
    public function resetOpStamp(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ResetOPStamp', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Reset Transaction Stamp - Sync all punches
     */
    public function resetTransactionStamp(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ResetTransactionStamp', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Convert image to base64 for photo upload
     */
    public function imageToBase64(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new Exception('Image file not found: ' . $imagePath);
        }
        
        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

    /**
     * Map verification type based on device capabilities
     * Face: 16, Face + Fingerprint: 17, Face + Password: 18, etc.
     */
    public function getVerificationType(string $type): string
    {
        $types = [
            'face' => '16',
            'face_fingerprint' => '17',
            'face_password' => '18',
            'face_card' => '19',
            'face_fingerprint_card' => '20',
            'face_fingerprint_password' => '21',
            'fingerprint' => '2',
            'fingerprint_password' => '6',
            'card' => '4',
            'password' => '3',
        ];

        return $types[$type] ?? '17'; // Default: Face + Fingerprint
    }
}