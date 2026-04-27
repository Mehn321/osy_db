<?php

/**
 * SmsService Class
 * 
 * Handles sending SMS via Traccar SMS Gateway (Cloud Mode)
 */
class SmsService {
    private $db;
    private $token;
    private $apiUrl;

    public function __construct($database) {

        $this->db = $database;
        $this->loadSettings();
    }

    /**
     * Load settings - override with user's local endpoints & token
     */
    private function loadSettings() {
        $this->token = 'b6e22e03-4fcd-492a-9363-2ee7c0aa7a54';
        $this->apiUrl = [
            "http://192.168.100.41:8082/",
            "http://172.18.11.218:8082/",
            "https://www.traccar.org/sms/" // Keep cloud fallback just in case
        ];
    }


    /**
     * Send SMS via Traccar Local Gateway
     * 
     * @param string $to Phone number
     * @param string $message Message content
     * @return array Success status and message
     */
    public function send($to, $message) {

        if (empty($to)) {
            return ['success' => false, 'message' => 'Recipient phone number is missing.'];
        }

        // Clean phone number (keep digits only first)
        $cleanPhone = preg_replace('/[^0-9]/', '', $to);
        
        // Convert local format (0xx) to international (+63xx)
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = '+63' . substr($cleanPhone, 1);
        } else if (substr($cleanPhone, 0, 2) === '63') {
            $cleanPhone = '+' . $cleanPhone; // Missing '+'
        } else if (substr($cleanPhone, 0, 1) !== '+') {
            $cleanPhone = '+' . $cleanPhone; // Generic fallback
        }
        
        // Traccar Cloud API expects JSON
        $data = [
            'to' => $cleanPhone,
            'message' => $message
        ];

        $lastError = 'No endpoints configured.';
        $lastHttpCode = 0;
        $lastResponse = '';

        foreach ($this->apiUrl as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Connect timeout so it skips unreachable IPs fast
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $this->token
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Logging for troubleshooting
            $logEntry = date('Y-m-d H:i:s') . " | Try URL: $url | To: $cleanPhone | Code: $httpCode | Resp: $response | Error: $curlError\n";
            file_put_contents(__DIR__ . '/../sms_log.txt', $logEntry, FILE_APPEND);

            if ($httpCode === 200 || $httpCode === 202) {
                return [
                    'success' => true,
                    'message' => "SMS queued successfully via $url"
                ];
            }
            
            $lastError = $curlError;
            $lastHttpCode = $httpCode;
            $lastResponse = $response;
        }

        // If loop finishes, all endpoints failed
        return [
            'success' => false,
            'message' => 'Traccar Error (' . $lastHttpCode . '): ' . ($lastResponse ?: 'Empty response') . ($lastError ? ' | CURL: ' . $lastError : '')
        ];

    }
}
