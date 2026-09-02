<?php

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
        // Changed to eTimeTrackLite URL
        $this->baseUrl = config('ebioserver.url', 'http://etime.esslsecurity.com:3366/WebAPIService.asmx');
        $this->username = config('ebioserver.username', 'essl');
        $this->password = config('ebioserver.password', 'essl');
        $this->locationCode = config('ebioserver.location_code', 'HOSTEL_MAIN');
    }

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function setLocationCode(string $locationCode): void
    {
        $this->locationCode = $locationCode;
    }

    public function setCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function setHostelConfig($hostel): void
    {
        if ($hostel->biometric_ip_address && $hostel->biometric_port) {
            // Changed to use WebAPIService.asmx for eTimeTrackLite
            $this->setBaseUrl("http://{$hostel->biometric_ip_address}:{$hostel->biometric_port}/WebAPIService.asmx");
        }
        
        if ($hostel->biometric_location_code) {
            $this->setLocationCode($hostel->biometric_location_code);
        }
    }

    protected function generateSoapRequest(string $method, array $params): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $xml .= 'xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '<soap:Body>';
        $xml .= "<{$method} xmlns=\"http://tempuri.org/\">";
        
        $xml .= "<UserName>{$this->username}</UserName>";
        $xml .= "<Password>{$this->password}</Password>";
        
        foreach ($params as $key => $value) {
            $xml .= "<{$key}>" . htmlspecialchars((string)$value) . "</{$key}>";
        }
        
        $xml .= "</{$method}>";
        $xml .= '</soap:Body>';
        $xml .= '</soap:Envelope>';
        
        return $xml;
    }

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
                Log::error('eTimeTrackLite connection failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to connect to eTimeTrackLite',
                    'error' => $response->body()
                ];
            }

            $responseBody = $response->body();
            
            $xml = simplexml_load_string($responseBody);
            if (!$xml) {
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
                    'message' => 'Invalid response from eTimeTrackLite',
                    'raw' => $responseBody
                ];
            }

            $namespaces = $xml->getNamespaces(true);
            $soapBody = $xml->children($namespaces['soap'] ?? 'http://schemas.xmlsoap.org/soap/envelope/')->Body;
            
            if (!$soapBody) {
                return [
                    'success' => false,
                    'message' => 'Invalid response structure',
                    'raw' => $responseBody
                ];
            }
            
            $responseElement = $soapBody->children('http://tempuri.org/');
            $resultElement = $responseElement->{$method . 'Result'} ?? $responseElement->children()->{$method . 'Result'} ?? $responseElement;
            
            $result = (string) $resultElement;

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
            Log::error('eTimeTrackLite API Error: ' . $e->getMessage(), [
                'method' => $method,
                'params' => $params
            ]);
            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    // EMPLOYEE MANAGEMENT
    // ============================================

    /**
     * Add Employee - Maps to AddEmployee in eTimeTrackLite
     */
    public function updateEmployee(array $data): array
    {
        $params = [
            'EmployeeCode' => $data['employee_code'],
            'EmployeeName' => $data['employee_name'],
            'Location' => $data['employee_location'] ?? $this->locationCode,  // Fixed: use employee_location
            'EmployeeRole' => $data['employee_role'] ?? 'Normal Users',
            'EmployeeVerificationType' => $data['verification_type'] ?? '17',
            'EmployeeCardNumber' => $data['card_number'] ?? '',
        ];

        // eTimeTrackLite uses AddEmployee instead of UpdateEmployee
        return $this->sendRequest('AddEmployee', $params);
    }

    /**
     * Add Multiple Employees - New method for eTimeTrackLite
     */
    public function addMultipleEmployees(array $employees): array
    {
        // Convert to XML string for multiple employees
        $employeesXml = $this->generateMultipleEmployeesXml($employees);
        
        return $this->sendRequest('AddMultipleEmployees', [
            'EmployeesXML' => $employeesXml
        ]);
    }

   protected function generateMultipleEmployeesXml(array $employees): string
{
    $xml = '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<Employees>';

    foreach ($employees as $emp) {

        $employeeCode = htmlspecialchars(
            (string) ($emp['employee_code'] ?? ''),
            ENT_XML1,
            'UTF-8'
        );

        $employeeName = htmlspecialchars(
            (string) ($emp['employee_name'] ?? ''),
            ENT_XML1,
            'UTF-8'
        );

        $location = $emp['employee_location']
            ?? $emp['location']
            ?? $this->locationCode;

        $location = htmlspecialchars(
            (string) $location,
            ENT_XML1,
            'UTF-8'
        );

        $employeeRole = htmlspecialchars(
            (string) ($emp['employee_role'] ?? 'Normal Users'),
            ENT_XML1,
            'UTF-8'
        );

        $verificationType = htmlspecialchars(
            (string) ($emp['verification_type'] ?? '17'),
            ENT_XML1,
            'UTF-8'
        );

        $cardNumber = htmlspecialchars(
            (string) ($emp['card_number'] ?? ''),
            ENT_XML1,
            'UTF-8'
        );

        $xml .= '<Employee>';
        $xml .= '<EmployeeCode>' . $employeeCode . '</EmployeeCode>';
        $xml .= '<EmployeeName>' . $employeeName . '</EmployeeName>';
        $xml .= '<Location>' . $location . '</Location>';
        $xml .= '<EmployeeRole>' . $employeeRole . '</EmployeeRole>';
        $xml .= '<EmployeeVerificationType>' . $verificationType . '</EmployeeVerificationType>';
        $xml .= '<EmployeeCardNumber>' . $cardNumber . '</EmployeeCardNumber>';
        $xml .= '</Employee>';
    }

    $xml .= '</Employees>';

    return $xml;
}
    public function updateEmployeeWithExpiry(array $data): array
    {
        // eTimeTrackLite might not support expiry dates directly
        // Use AddEmployee or update as needed
        $params = [
            'EmployeeCode' => $data['employee_code'],
            'EmployeeName' => $data['employee_name'],
            'Location' => $data['employee_location'] ?? $this->locationCode,  // Fixed: use employee_location
            'EmployeeRole' => $data['employee_role'] ?? 'Normal Users',
            'EmployeeVerificationType' => $data['verification_type'] ?? '17',
            'EmployeeCardNumber' => $data['card_number'] ?? '',
        ];

        return $this->sendRequest('AddEmployee', $params);
    }

    /**
     * Delete User - Maps to DeleteUser in eTimeTrackLite
     */
    public function deleteEmployee(string $employeeCode): array
    {
        return $this->sendRequest('DeleteUser', [
            'EmployeeCode' => $employeeCode,
        ]);
    }

    /**
     * Delete Multiple Employees - New method for eTimeTrackLite
     */
    public function deleteMultipleEmployees(array $employeeCodes): array
    {
        $codesXml = '';
        foreach ($employeeCodes as $code) {
            $codesXml .= "<EmployeeCode>{$code}</EmployeeCode>";
        }
        
        return $this->sendRequest('DeleteMultipleEmployees', [
            'EmployeesXML' => "<Employees>{$codesXml}</Employees>"
        ]);
    }

    public function getEmployeeDetails(string $employeeCode): array
    {
        return $this->sendRequest('GetEmployeeDetails', [
            'EmployeeCode' => $employeeCode,
        ]);
    }

    public function getEmployeeCodes(): array
    {
        return $this->sendRequest('GetEmployeeCodes', []);
    }

    public function getEmployeePunchLogs(string $employeeCode, string $date): array
    {
        return $this->sendRequest('GetEmployeePunchLogs', [
            'EmployeeCode' => $employeeCode,
            'AttendanceDate' => $date,
        ]);
    }

    // ============================================
    // DEVICE MANAGEMENT
    // ============================================

    public function getDeviceList(?string $location = null): array
    {
        return $this->sendRequest('GetDeviceList', [
            'Location' => $location ?? $this->locationCode,
        ]);
    }

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

    public function deleteDevice(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeleteDevice', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Get Transactions Log - Maps to GetTransactionsLog in eTimeTrackLite
     */
    public function getDeviceLogs(string $date, ?string $location = null): array
    {
        return $this->sendRequest('GetTransactionsLog', [
            'Location' => $location ?? $this->locationCode,
            'LogDate' => $date,
        ]);
    }

    public function getDeviceIllegalLogs(string $date, ?string $location = null): array
    {
        return $this->sendRequest('GetDeviceIllegalLogs', [
            'Location' => $location ?? $this->locationCode,
            'LogDate' => $date,
        ]);
    }

    public function getDeviceLastPing(string $deviceSerialNumber): array
    {
        return $this->sendRequest('GetDeviceLastPing', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    // ============================================
    // DEVICE COMMANDS
    // ============================================

    /**
     * Block/Unblock User - Maps to BlockUnblockUser in eTimeTrackLite
     */
    public function blockUnblockUser(string $deviceSerialNumber, string $employeeCode, bool $block = true): array
    {
        return $this->sendRequest('BlockUnblockUser', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
            'Block' => $block ? '1' : '0',
        ]);
    }

    public function rebootDevice(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_Reboot', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    public function clearDeviceLogs(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ClearLogs', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    public function resetOpStamp(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ResetOPStamp', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    public function resetTransactionStamp(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_ResetTransactionStamp', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    /**
     * Enroll User Fingerprint - Maps to EnrollUserFP in eTimeTrackLite
     */
    public function enrollFingerprint(string $deviceSerialNumber, string $employeeCode, string $fingerIndex = '1'): array
    {
        return $this->sendRequest('EnrollUserFP', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
            'FingerIndex' => $fingerIndex,
        ]);
    }

    /**
     * Enroll User Face - Maps to EnrollUserFace in eTimeTrackLite
     */
    public function enrollFace(string $deviceSerialNumber, string $employeeCode): array
    {
        return $this->sendRequest('EnrollUserFace', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'EmployeeCode' => $employeeCode,
        ]);
    }

    public function unlockDoor(string $deviceSerialNumber): array
    {
        return $this->sendRequest('DeviceCommand_UnlockDoor', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    public function changeWebServerAddress(string $deviceSerialNumber, string $newAddress): array
    {
        return $this->sendRequest('DeviceCommand_ChangeWebServerAddress', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'NewAddress' => $newAddress,
        ]);
    }

    public function changeWebServerPort(string $deviceSerialNumber, string $newPort): array
    {
        return $this->sendRequest('DeviceCommand_ChangeWebServerPort', [
            'DeviceSerialNumber' => $deviceSerialNumber,
            'NewPort' => $newPort,
        ]);
    }

    /**
     * Get Command Status - Maps to GetCommandStatus in eTimeTrackLite
     */
    public function getDeviceCommandLogs(string $deviceSerialNumber): array
    {
        return $this->sendRequest('GetCommandStatus', [
            'DeviceSerialNumber' => $deviceSerialNumber,
        ]);
    }

    // ============================================
    // LOCATION MANAGEMENT
    // ============================================

    public function updateLocation(string $locationCode, string $locationName): array
    {
        return $this->sendRequest('UpdateLocation', [
            'LocationCode' => $locationCode,
            'LocationName' => $locationName,
        ]);
    }

    public function deleteLocation(string $locationCode): array
    {
        return $this->sendRequest('DeleteLocation', [
            'LocationCode' => $locationCode,
        ]);
    }

    // ============================================
    // VISITOR MANAGEMENT
    // ============================================

    public function validateVisitorDesk(string $visitorCode): array
    {
        return $this->sendRequest('ValidateVisitorDesk', [
            'VisitorCode' => $visitorCode,
        ]);
    }

    // ============================================
    // UTILITY METHODS
    // ============================================

    public function imageToBase64(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new Exception('Image file not found: ' . $imagePath);
        }
        
        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

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

    public function testConnection(): array
    {
        try {
            $result = $this->getDeviceList();
            return [
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'Connected to eTimeTrackLite successfully' : 'Connection failed',
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