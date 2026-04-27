<?php

/**
 * EmailService Class
 * 
 * Handles sending emails via Gmail SMTP using PHPMailer.
 * Credentials are loaded from the system_settings database table.
 */

// Load PHPMailer library (no Composer required)
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService {
    private $db;
    private $user;
    private $pass;
    private $host = 'smtp.gmail.com';
    private $port = 587;

    public function __construct($database) {
        $this->db = $database;
        $this->loadSettings();
    }

    private function loadSettings() {
        $resultUser = $this->db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_user'"
        );
        $resultPass = $this->db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_app_password'"
        );
        
        $this->user = $resultUser['setting_value'] ?? '';
        $this->pass = $resultPass['setting_value'] ?? '';
    }

    /**
     * Send email via Gmail SMTP using PHPMailer
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML supported)
     * @return array Success status and message
     */
    public function send($to, $subject, $message) {
        if (empty($this->user) || empty($this->pass)) {
            return ['success' => false, 'message' => 'Gmail SMTP credentials not configured. Go to Settings > Notifications to set up.'];
        }

        if (empty($to)) {
            return ['success' => false, 'message' => 'Recipient email address is missing.'];
        }

        $mail = new PHPMailer(true); // Enable exceptions

        try {
            // SMTP server configuration
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->user;
            $mail->Password   = $this->pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;
            $mail->Timeout    = 30;

            // Sender & recipient
            $mail->setFrom($this->user, 'Municipal KK OSY Program');
            $mail->addAddress($to);

            // Email content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $message;
            // Auto-generate a plain-text fallback from the HTML
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $message));

            $mail->send();

            // Log success
            $logEntry = date('Y-m-d H:i:s') . " | Email SENT | To: $to | Subject: $subject\n";
            @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

            return ['success' => true, 'message' => 'Email sent successfully via Gmail.'];

        } catch (PHPMailerException $e) {
            // Log failure
            $logEntry = date('Y-m-d H:i:s') . " | Email FAILED | To: $to | Error: {$mail->ErrorInfo}\n";
            @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

            return ['success' => false, 'message' => 'Email error: ' . $mail->ErrorInfo];
        }
    }
}
