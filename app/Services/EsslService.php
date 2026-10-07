<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsslService
{
    private const NS        = 'http://tempuri.org/';
    private const INT32_MAX = 2147483647;

    private string $url;
    private string $apiKey;
    private string $username;
    private string $password;
    private int    $connectTimeout;
    private int    $timeout;
    private bool   $mock;

    public function __construct()
    {
        $c = config('services.essl');

        // Graceful: don't throw — let page load with a friendly error
        $this->url            = (string) ($c['url']            ?? '');
        $this->apiKey         = (string) ($c['api_key']        ?? '11');
        $this->username       = (string) ($c['username']       ?? '');
        $this->password       = (string) ($c['password']       ?? '');
        $this->connectTimeout = (int)    ($c['connect_timeout'] ?? 5);
        $this->timeout        = (int)    ($c['timeout']         ?? 30);
        $this->mock           = (bool)   ($c['mock']            ?? false);
    }

    public function isConfigured(): bool
    {
        return $this->url !== ''
            && $this->username !== ''
            && $this->password !== '';
    }

    /* ════════════════ Public API ════════════════ */

    public function addEmployee(string $employeeCode, string $name, string $serial, string $cardNumber = ''): array
    {
        $sentCommandId = (string) random_int(1, self::INT32_MAX);

        $result = $this->call('AddEmployee', [
            'APIKey'       => $this->apiKey,
            'EmployeeCode' => $employeeCode,
            'EmployeeName' => $name,
            'CardNumber'   => $cardNumber !== '' ? $cardNumber : '0',
            'SerialNumber' => $serial,
            'UserName'     => $this->username,
            'UserPassword' => $this->password,
            'CommandId'    => $sentCommandId,
        ]);

        $result['sent_command_id'] = $sentCommandId;
        if (!empty($result['success']) && !empty($result['command_id'])) {
            $result['server_command_id'] = $result['command_id'];
        }
        return $result;
    }

    public function blockUnblock(string $employeeCode, string $name, string $serial, bool $block): array
    {
        $sentCommandId = (string) random_int(1, self::INT32_MAX);

        $result = $this->call('BlockUnblockUser', [
            'APIKey'       => $this->apiKey,
            'EmployeeCode' => $employeeCode,
            'EmployeeName' => $name,
            'SerialNumber' => $serial,
            'IsBlock'      => $block ? 'true' : 'false',
            'UserName'     => $this->username,
            'UserPassword' => $this->password,
            'CommandId'    => $sentCommandId,
        ]);

        $result['sent_command_id'] = $sentCommandId;
        if (!empty($result['success']) && !empty($result['command_id'])) {
            $result['server_command_id'] = $result['command_id'];
        }
        return $result;
    }

    public function addEmployeeToDb(array $emp): array
    {
        $c = config('services.essl');

        $company    = trim((string) ($c['company_sname']    ?? '')) ?: 'Default';
        $department = trim((string) ($c['department_sname'] ?? '')) ?: 'Default';

        $row = [
            'EmployeeCode'    => (string) $emp['code'],
            'EmployeeName'    => (string) $emp['name'],
            'CompanySName'    => $company,
            'DepartmentSName' => $department,
            'SubDepartment'   => (string) ($c['sub_department']  ?? ''),
            'Location'        => (string) ($c['location']        ?? ''),
            'Designation'     => (string) ($c['designation']     ?? ''),
            'Division'        => (string) ($c['division']        ?? ''),
            'Grade'           => (string) ($c['grade']           ?? ''),
            'EmploymentType'  => (string) ($c['employment_type'] ?? 'Permanent'),
            'Gender'          => $emp['gender'] ?? ($c['gender'] ?? 'Male'),
            'DateOfJoin'      => $emp['join_date'],
            'DateOfConfirm'   => $emp['join_date'],
            'EmployeeStatus'  => $emp['status'] ?? 'Working',
            'DateOfResign'    => $emp['resign_date'] ?? '',
        ];

        $missing = $this->validateDbRow($row);
        if ($missing) {
            return [
                'success'    => false,
                'message'    => 'Validation failed: ' . implode(', ', $missing),
                'command_id' => null,
                'raw'        => null,
            ];
        }

        return $this->call('AddMultipleEmployeesToDB', [
            'EmployeesDataInJsonFormat' => json_encode(
                [$row],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'UserName'                  => $this->username,
            'UserPassword'              => $this->password,
            'ErrorMessage'              => '',
        ]);
    }

    public function getCommandStatus(string $commandId): array
    {
        return $this->call('GetCommandStatus', [
            'CommandId'    => $commandId,
            'UserName'     => $this->username,
            'UserPassword' => $this->password,
        ]);
    }

    /* ════════════════ Helpers ════════════════ */

    private function validateDbRow(array $row): array
    {
        $required = [
            'EmployeeCode', 'EmployeeName', 'CompanySName',
            'DepartmentSName', 'EmploymentType', 'Gender',
        ];
        $missing = [];
        foreach ($required as $key) {
            if (empty($row[$key])) $missing[] = $key;
        }
        return $missing;
    }

    /* ════════════════ SOAP core ════════════════ */

    private function call(string $method, array $params): array
    {
        if (!$this->isConfigured() && !$this->mock) {
            return [
                'success'    => false,
                'message'    => 'eSSL not configured (check ESSL_URL / ESSL_USERNAME / ESSL_PASSWORD).',
                'command_id' => null,
                'raw'        => null,
            ];
        }

        if ($this->mock) {
            return [
                'success'    => true,
                'message'    => "MOCK {$method} OK",
                'command_id' => null,
                'raw'        => 'mock',
            ];
        }

        $body = '';
        foreach ($params as $k => $v) {
            $body .= "<{$k}>" . $this->xmlEscape((string) $v) . "</{$k}>";
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
             . '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
             . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
             . 'xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
             . '<soap:Body>'
             . "<{$method} xmlns=\"" . self::NS . "\">{$body}</{$method}>"
             . '</soap:Body></soap:Envelope>';

        try {
            $res = Http::withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'SOAPAction'   => '"' . self::NS . $method . '"',
                ])
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->withBody($xml, 'text/xml; charset=utf-8')
                ->post($this->url);
        } catch (\Throwable $e) {
            Log::error("eSSL {$method} connection error", ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'message'    => 'Cannot reach eSSL server: ' . $e->getMessage(),
                'command_id' => null,
                'raw'        => null,
            ];
        }

        $raw = $res->body();

        $safeXml = preg_replace(
            '/<UserPassword>.*?<\/UserPassword>/s',
            '<UserPassword>***</UserPassword>',
            $xml
        );

        Log::info("eSSL {$method}", [
            'status'   => $res->status(),
            'request'  => $safeXml,
            'response' => $raw,
        ]);

        if (!$res->successful()) {
            $fault = $this->extract($raw, 'faultstring')
                  ?? mb_substr(strip_tags($raw), 0, 300);
            Log::error("eSSL {$method} HTTP {$res->status()}", ['fault' => $fault]);
            return [
                'success'    => false,
                'message'    => "eSSL HTTP {$res->status()}: " . trim($fault),
                'command_id' => null,
                'raw'        => $raw,
            ];
        }

        $result = $this->extract($raw, "{$method}Result")
               ?? $this->extract($raw, 'AddMultipleEmployeeToDBResult')
               ?? $this->extract($raw, 'AddMultipleEmployeesToDBResult');

        $commandId = $this->extract($raw, 'CommandId') ?? $this->extract($raw, 'CommandIds');
        $errStatus = $this->extract($raw, 'ErrorStatus') ?? $this->extract($raw, 'ErrorMessage');

        if ($result === null && $errStatus === null) {
            return [
                'success'    => false,
                'message'    => 'Unexpected eSSL response',
                'command_id' => $commandId,
                'raw'        => $raw,
            ];
        }

        $errStatus = $errStatus !== null ? trim($errStatus) : '';
        $resultStr = trim((string) $result);

        // ── Stronger success detection ──
        $hasError = $errStatus !== '' || $this->looksLikeError($resultStr);

        $ok = !$hasError;

        $message = $resultStr;
        if ($errStatus !== '') {
            $message .= ($message ? ' | ' : '') . 'ErrorStatus: ' . $errStatus;
        }

        return [
            'success'    => $ok,
            'message'    => $message ?: ($ok ? 'Success' : 'Failed'),
            'command_id' => $commandId,
            'raw'        => $raw,
        ];
    }

    /**
     * Detect failure even when ErrorStatus is empty but Result text
     * contains an obvious error phrase.
     */
    private function looksLikeError(string $text): bool
    {
        if ($text === '') return false;

        $needles = [
            'authentication fail',
            'authentication failed',
            'invalid user',
            'invalid password',
            'user not found',
            'employee not found',
            'device not found',
            'serial number not',
            'failed',
            'error',
            'not exist',
        ];

        $lower = strtolower($text);
        foreach ($needles as $n) {
            if (str_contains($lower, $n)) return true;
        }
        return false;
    }

    private function xmlEscape(string $v): string
    {
        return str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $v
        );
    }

    private function extract(string $xml, string $tag): ?string
    {
        $pattern = '/<(?:\w+:)?' . preg_quote($tag, '/') . '\b[^>]*>(.*?)<\/(?:\w+:)?'
                 . preg_quote($tag, '/') . '>/s';
        if (preg_match($pattern, $xml, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
        }
        return null;
    }
}
