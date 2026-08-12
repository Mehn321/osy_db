<?php

/**
 * SmsService Class
 * 
 * Handles sending SMS via Traccar cloud or Semaphore gateways.
 *
 * Traccar Cloud mode: SMS is sent via JSON POST to
 * https://www.traccar.org/sms/ with an Authorization header containing the
 * cloud token obtained from the Traccar Android app.
 */
class SmsService
{
    private $db;
    private $token;
    private $semaphoreApiKey;
    private $apiUrl;

    public function __construct($database)
    {
        $this->db = $database;
        $this->loadSettings();
    }

    /**
     * Load settings from environment variables and system_settings table.
     */
    private function loadSettings()
    {
        $this->token = trim(getenv('SMS_API_TOKEN') ?: getenv('TRACCAR_SMS_TOKEN') ?: '');
        $this->semaphoreApiKey = trim(getenv('SEMAPHORE_API_KEY') ?: '');
        $this->apiUrl = 'https://www.traccar.org/sms/';

        if ($this->db) {
            // Load Traccar token – the settings page stores this under 'traccar_token'
            if (empty($this->token)) {
                try {
                    $result = $this->db->fetchOne(
                        "SELECT setting_value FROM system_settings WHERE setting_key = 'traccar_token' LIMIT 1"
                    );
                    $this->token = trim($result['setting_value'] ?? '');
                } catch (Exception $e) {
                    $this->token = '';
                }
            }

            // Load Semaphore API key
            if (empty($this->semaphoreApiKey)) {
                try {
                    $result = $this->db->fetchOne(
                        "SELECT setting_value FROM system_settings WHERE setting_key = 'semaphore_api_key' LIMIT 1"
                    );
                    $this->semaphoreApiKey = trim($result['setting_value'] ?? '');
                } catch (Exception $e) {
                    $this->semaphoreApiKey = '';
                }
            }
        }
    }

    /**
     * Send SMS via the configured gateway.
     * 
     * @param string $to Phone number
     * @param string $message Message content
     * @return array Success status and message
     */
    public function send($to, $message)
    {

        if (empty($to)) {
            return ['success' => false, 'message' => 'Recipient phone number is missing.'];
        }

        if (empty($this->token) && empty($this->semaphoreApiKey)) {
            return ['success' => false, 'message' => 'SMS gateway credentials are not configured. Please go to Settings > Notifications and enter your Traccar cloud token.'];
        }

        if (empty($this->apiUrl)) {
            return ['success' => false, 'message' => 'No Traccar cloud gateway URL is configured.'];
        }

        // Clean phone number by keeping digits only.
        $cleanPhone = preg_replace('/[^0-9]/', '', $to);

        // Convert local mobile format (0912...) to international digits (63912...)
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '63' . substr($cleanPhone, 1);
        }

        // If the number already includes the country code without plus, keep it.
        // If the number begins with +, strip the plus for gateway compatibility.
        if (substr($cleanPhone, 0, 1) === '+') {
            $cleanPhone = substr($cleanPhone, 1);
        }

        // If it starts with 63, keep as-is. Otherwise prefix 63 for international mobile.
        if (substr($cleanPhone, 0, 2) !== '63') {
            $cleanPhone = '63' . ltrim($cleanPhone, '0');
        }

        // Try Semaphore first if configured
        if (!empty($this->semaphoreApiKey)) {
            $semaphoreUrl = 'https://api.semaphore.co/api/v4/messages';
            $semaphoreData = [
                'number' => preg_replace('/^\+/', '', $cleanPhone),
                'message' => $message,
                'apikey' => $this->semaphoreApiKey
            ];

            $ch = curl_init($semaphoreUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($semaphoreData));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $logEntry = date('Y-m-d H:i:s') . " | Try Semaphore | To: $cleanPhone | Code: $httpCode | Resp: $response | Error: $curlError\n";
            file_put_contents(__DIR__ . '/../sms_log.txt', $logEntry, FILE_APPEND);

            if ($httpCode === 200 || $httpCode === 202) {
                return ['success' => true, 'message' => 'SMS queued successfully via Semaphore.'];
            }
        }

        $lastError = '';
        $lastHttpCode = 0;
        $lastResponse = '';

        if (empty($this->token)) {
            return [
                'success' => false,
                'message' => 'SMS gateway credentials are not configured. Please enter your Traccar cloud token in Settings.'
            ];
        }

        $cloudUrl = $this->apiUrl;
        $ch = curl_init($cloudUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $postData = json_encode([
            'to' => $cleanPhone,
            'message' => $message
        ]);
        $headers = ['Content-Type: application/json'];
        $headers[] = 'Authorization: ' . $this->token;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $logEntry = date('Y-m-d H:i:s') . " | Traccar Cloud | URL: $cloudUrl | To: $cleanPhone | Code: $httpCode | Resp: $response | Error: $curlError\n";
        file_put_contents(__DIR__ . '/../sms_log.txt', $logEntry, FILE_APPEND);

        if ($httpCode === 200 || $httpCode === 202) {
            return ['success' => true, 'message' => 'SMS queued successfully via Traccar Cloud.'];
        }

        return [
            'success' => false,
            'message' => 'SMS delivery failed. ' . ($httpCode ? 'Last HTTP code: ' . $httpCode : '') . ($response ? ' | Response: ' . $response : '') . ($curlError ? ' | CURL: ' . $curlError : '')
        ];
    }
}
