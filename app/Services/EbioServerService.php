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
        $this->baseUrl = config('ebioserver.url', 'http://ebioservernew.esslsecurity.com:99/webservice.asmx');
        $this->username = config('ebioserver.username', 'essl');
        $this->password = config('ebioserver.password', 'essl');
        $this->locationCode = config('ebioserver.location_code', 'HOSTEL_MAIN');
    }

    /**
     * Generate SOAP request XML for eBioServer
     */
    protected function generateSoapRequest(string $method, array $params): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $xml .= 'xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '<soap:Body>';
        $xml .= "<{$method} xmlns=\"http://tempuri.org/\">";
        
        // Add credentials (required for all eBioServer methods)
        $xml .= "<UserName>{$this->username}</UserName>";
        $xml .= "<Password>{$this->password}</Password>";
        
        // Add additional parameters
        foreach ($params as $key => $value) {
            $xml .= "<{$key}>" . htmlspecialchars((string)$value) . "</{$key}>";
        }
        
        $xml .= "</{$method}>";
        $xml .= '</soap:Body>';
        $xml .= '</soap:Envelope>';
        
        return $xml;
    }

    /**
     * Send SOAP request to eBioServer
     */
    protected function sendRequest(string $method, array $params): array
    {
        try {
            $soapBody = $this->generateSoapRequest($method, $params);
            
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => "http://tempuri.org/{$method}",
                'Content-Length' => strlen($soapBody),
            ])->timeout(30)->send('POST', $this->baseUrl, [
                'body' => $soapBody,
            ]);

            if ($response->failed()) {
                Log::error('eBioServer connection failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to connect to eBioServer',
                    'error' => $response->body()
                ];
            }

            // Parse SOAP response
            $responseBody = $response->body();
            
            // Handle potential XML parsing issues
            $xml = simplexml_load_string($responseBody);
            if (!$xml) {
                // Try to extract from raw response
                if (preg_match('/<([^>]+)Result[^>]*>([^<]+)<\/[^>]+>/', $responseBody, $matches)) {
                    $result = $matches[2] ?? '';
                    return [
                        'success' => !str_contains(strtolower($result), 'error'),
                        'message' => $result,
                        'data' => $result,
                        'raw' => $responseBody
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Invalid response from eBioServer',
                    'raw' => $responseBody
                ];
            }

            // Extract result from SOAP response
            $namespaces = $xml->getNamespaces(true);
            $soapBody = $xml->children($namespaces['soap'] ?? 'http://schemas.xmlsoap.org/soap/envelope/')->Body;
            
            if (!$soapBody) {
                return [
                    'success' => false,
                    'message' => 'Invalid response structure',
                    'raw' => $responseBody
                ];
            }
            
            // Try to find the result element
            $responseElement = $soapBody->children('http://tempuri.org/');
            $resultElement = $responseElement->{$method . 'Result'} ?? $responseElement->children()->{$method . 'Result'} ?? $responseElement;
            
            $result = (string) $resultElement;

            // Check if result indicates success
            $isSuccess = !str_contains(strtolower($result), 'error') 
                && !str_contains(strtolower($result), 'fail')
                && !empty($result);

            return [
                'success' => $isSuccess,
                'message' => $result,
                'data' => $result,
                'raw' => $responseBody
            ];

        } catch (Exception $e) {
            Log::error('eBioServer API Error: ' . $e->getMessage(), [
                'method' => $method,
                'params' => $params
            ]);
            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================
     * EMPLOYEE MANAGEMENT METHODS
     * ============================================
     */

    /**
     * Add or Update Employee
     * 
     * @param array $data Required: employee_code, employee_name
     *                    Optional: location, role, verification_type, card_number
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
     * Update Employee with Expiry Dates
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
     * 
     * @param string $employeeCode
     * @param string $photoBase64 Base64 encoded image
     */
    public function updateEmployeePhoto(string $employeeCode, string $photoBase64): array
    {
        return $this->sendRequest('UpdateEmployeePhoto', [
            'EmployeeCode' => $employeeCode,
            'EmployeePhoto' => $photoBase64,
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
     * Get Employee Details
     */
    public function getEmployeeDetails(string $employeeCode): array
    {
        return $this->sendRequest('GetEmployeeDetails', [
            'EmployeeCode' => $employeeCode,
        ]);
    }

    /**
     * Get Employee Codes
     */
    public function getEmployeeCodes(): array
    {
        return $this->sendRequest('GetEmployeeCodes', []);
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
     * ============================================
     * LOCATION MANAGEMENT METHODS
     * ============================================
     */

    /**
     * Update Location
     */
    public function updateLocation(string $locationCode, string $locationName): array
    {
        return $this->sendRequest('UpdateLocation', [
            'LocationCode' => $locationCode,
            'LocationName' => $locationName,
        ]);
    }

    /**
     * Delete Location
     */
    public function deleteLocation(string $locationCode): array
    {
        return $this->sendRequest('DeleteLocation', [
            'LocationCode' => $locationCode,
        ]);
    }

    /**
     * ============================================
     * DEVICE MANAGEMENT METHODS
     * ============================================
     */

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
     * Update Device
     */
    public function updateDevice(array $data): array
    {
        return $this->sendRequest('UpdateDevice', [
            'DeviceSerialNumber' => $data['serial_number'],
            'DeviceName' => $data['device_name'] ?? '',
            'Location' => $data['location'] ?? $this->locationCode,
            'IPAddress' => $data['ip_address'] ?? '',
            'Port' => $data['port'] ?? '4370',
        ]);
    }

    /**
     * Delete Device
     */
    public function deleteDevice(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeleteDevice', [
            'DeviceSerialNumber' => $deviceSerialNumber,
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
     * Get Device Illegal Logs
     */
    public function getDeviceIllegalLogs(string $date, ?string $location = null): array
    {
        return $this->sendRequest('GetDeviceIllegalLogs', [
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
     * ============================================
     * DEVICE COMMAND METHODS
     * ============================================
     */

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
     * Enroll Fingerprint
     */
    public function enrollFingerprint(string $deviceSerialNumber, string $employeeCode, string $fingerIndex = '1'): array
    {
        return $this->sendRequest('DeviceCommand_EnrollFP', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
            'FingerIndex' => $fingerIndex,
        ]);
    }

    /**
     * Enroll Face
     */
    public function enrollFace(string $deviceSerialNumber, string $employeeCode): array
    {
        return $this->sendRequest('DeviceCommand_EnrollFace', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
        ]);
    }

    /**
     * Block/Unblock User
     */
    public function blockUnblockUser(string $deviceSerialNumber, string $employeeCode, bool $block = true): array
    {
        return $this->sendRequest('DeviceCommand_BlockUnBlockUser', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
            'Block' => $block ? '1' : '0',
        ]);
    }

    /**
     * Unlock Door
     */
    public function unlockDoor(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_UnlockDoor', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Change Web Server Address
     */
    public function changeWebServerAddress(string $deviceSerialNumber, string $newAddress): array
    {
        return $this->sendRequest('DeviceCommand_ChangeWebServerAddress', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'NewAddress' => $newAddress,
        ]);
    }

    /**
     * Change Web Server Port
     */
    public function changeWebServerPort(string $deviceSerialNumber, string $newPort): array
    {
        return $this->sendRequest('DeviceCommand_ChangeWebServerPort', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'NewPort' => $newPort,
        ]);
    }

    /**
     * Get Device Logs via Command
     */
    public function getDeviceCommandLogs(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_GetDeviceLogs', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * ============================================
     * VISITOR MANAGEMENT
     * ============================================
     */

    /**
     * Validate Visitor Desk
     */
    public function validateVisitorDesk(string $visitorCode): array
    {
        return $this->sendRequest('ValidateVisitorDesk', [
            'VisitorCode' => $visitorCode,
        ]);
    }

    /**
     * ============================================
     * UTILITY METHODS
     * ============================================
     */

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

        return $types[$type] ?? '17';
    }

    /**
     * Test connection to eBioServer
     */
    public function testConnection(): array
    {
        try {
            $result = $this->getDeviceList();
            return [
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Connected successfully' : 'Connection failed',
                'details' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
}