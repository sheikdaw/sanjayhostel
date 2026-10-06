<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsslService
{
    private const NS = 'http://tempuri.org/';

    private string $baseUrl;
    private array  $auth;
    private int    $connectTimeout;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl        = rtrim((string) config('services.essl.url'), '?');
        $this->connectTimeout = (int) config('services.essl.connect_timeout', 5);
        $this->timeout        = (int) config('services.essl.timeout', 30);

        $this->auth = [
            'api_key'  => (string) config('services.essl.api_key'),
            'username' => (string) config('services.essl.username'),
            'password' => (string) config('services.essl.password'),
        ];
    }

    /**
     * Block or unblock a resident on their hostel's device.
     */
    public function setBlocked(Resident $resident, bool $block): array
    {
        $serial = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return ['success' => false, 'error' => 'Hostel has no device configured.'];
        }

        if (empty($resident->employee_code)) {
            return ['success' => false, 'error' => 'Resident has no employee code.'];
        }

        try {
            $response = $this->soapCall('BlockUnblockUser', [
                'APIKey'       => $this->auth['api_key'],
                'EmployeeCode' => $resident->employee_code,
                'EmployeeName' => $resident->name,
                'SerialNumber' => $serial,
                'IsBlock'      => $block ? 'true' : 'false',
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'CommandId'    => '',
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'HTTP ' . $response->status()];
            }

            $fault = $this->extractTag($response, 'faultstring');
            if ($fault !== null) {
                return ['success' => false, 'error' => 'Fault: ' . $fault];
            }

            $result = $this->extractTag($response, 'BlockUnblockUserResult');

            $ok = $result !== null && (
                strtolower(trim($result)) === 'success'
                || strtolower(trim($result)) === '1'
                || strpos(strtolower($result), 'success') !== false
            );

            if ($ok) {
                $resident->update(['biometric_access' => !$block]);
            }

            return [
                'success' => $ok,
                'error'   => $ok ? null : ($result ?: 'empty response'),
            ];
        } catch (ConnectionException $e) {
            Log::error('eSSL BlockUnblock connection failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Device unreachable: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            Log::error('eSSL BlockUnblock failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function soapCall(string $method, array $params = [], ?int $timeout = null): Response
    {
        if (config('services.essl.mock')) {
            return $this->mockResponse($method);
        }

        $inner = '';
        foreach ($params as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $safe = is_int($value) || is_float($value)
                ? (string) $value
                : htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $inner .= "<{$name}>{$safe}</{$name}>";
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            . '<soap:Body>'
            . "<{$method} xmlns=\"" . self::NS . "\">{$inner}</{$method}>"
            . '</soap:Body>'
            . '</soap:Envelope>';

        return Http::connectTimeout($this->connectTimeout)
            ->timeout($timeout ?? $this->timeout)
            ->withHeaders([
                'SOAPAction'   => '"' . self::NS . $method . '"',
                'Content-Type' => 'text/xml; charset=utf-8',
            ])
            ->withBody($xml, 'text/xml; charset=utf-8')
            ->post($this->baseUrl);
    }

    private function mockResponse(string $method): Response
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            . "<{$method}Response xmlns=\"" . self::NS . "\">"
            . "<{$method}Result>Success (mock)</{$method}Result>"
            . '<CommandId>0</CommandId>'
            . "</{$method}Response></soap:Body></soap:Envelope>";

        return new Response(
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/xml'], $xml)
        );
    }

    private function extractTag(Response $response, string $tag): ?string
    {
        $body = trim($response->body());
        if ($body === '' || strpos($body, '<') === false) {
            return null;
        }
        if (stripos($body, '<html') !== false || stripos($body, '<!doctype html') !== false) {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($body);
        libxml_clear_errors();

        if (!$loaded) {
            if (preg_match("#<(?:\w+:)?{$tag}\b[^>]*>(.*?)</(?:\w+:)?{$tag}>#s", $body, $m)) {
                return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
            return null;
        }

        $nodes = $dom->getElementsByTagNameNS('*', $tag);
        if ($nodes->length > 0) {
            return trim(html_entity_decode($nodes->item(0)->textContent, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }

        $nodes = $dom->getElementsByTagName($tag);
        if ($nodes->length > 0) {
            return trim(html_entity_decode($nodes->item(0)->textContent, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }

        return null;
    }
}
