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

class EmailService
{
  private $db;
  private $user;
  private $pass;
  private $host = 'smtp.gmail.com';
  private $port = 587;

  public function __construct($database)
  {
    $this->db = $database;
    $this->loadSettings();
  }

  private function loadSettings()
  {
    $dbUser = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_user'"
    );
    $dbPass = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_app_password'"
    );

    $this->user = trim((string) ($dbUser['setting_value'] ?? ''));
    $this->pass = trim((string) ($dbPass['setting_value'] ?? ''));

    // Fallbacks for deployed environments that store SMTP credentials in env vars instead of the database.
    if ($this->user === '') {
      $this->user = trim((string) (getenv('GMAIL_USER') ?: ($_ENV['GMAIL_USER'] ?? $_SERVER['GMAIL_USER'] ?? '')));
    }
    if ($this->user === '' && function_exists('getConfiguredValue')) {
      $this->user = trim((string) getConfiguredValue('GMAIL_USER', ''));
    }

    if ($this->pass === '') {
      $this->pass = trim((string) (getenv('GMAIL_APP_PASSWORD') ?: ($_ENV['GMAIL_APP_PASSWORD'] ?? $_SERVER['GMAIL_APP_PASSWORD'] ?? '')));
    }
    if ($this->pass === '' && function_exists('getConfiguredValue')) {
      $this->pass = trim((string) getConfiguredValue('GMAIL_APP_PASSWORD', ''));
    }
  }

  public function buildStyledEmail($title, $message, $ctaText = null, $ctaUrl = null)
  {
    $ctaHtml = '';
    if ($ctaText && $ctaUrl) {
      $ctaHtml = '<p style="margin: 24px 0 0;"><a href="' . htmlspecialchars($ctaUrl) . '" style="background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 999px; display: inline-block; font-weight: 700;">' . htmlspecialchars($ctaText) . '</a></p>';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{$title}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7fb; font-family:Inter, Helvetica, Arial, sans-serif; color:#0f172a;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7fb; padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" max-width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 12px 40px rgba(15,23,42,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg, #0f2c6b, #2563eb); padding:28px 32px; color:#ffffff;">
              <h1 style="margin:0; font-size:24px; font-weight:700;">{$title}</h1>
              <p style="margin:8px 0 0; opacity:0.9; font-size:14px;">Youth Profiling System</p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px; line-height:1.6; font-size:15px;">
              {$message}
              {$ctaHtml}
            </td>
          </tr>
          <tr>
            <td style="padding:24px 32px 32px; border-top:1px solid #e2e8f0; font-size:12px; color:#64748b;">
              This is an automated message from the Youth Profiling System.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
  }

  /**
   * Send email via Gmail SMTP using PHPMailer
   * 
   * @param string $to Recipient email
   * @param string $subject Email subject
   * @param string $message Email body (HTML supported)
   * @return array Success status and message
   */
  public function send($to, $subject, $message)
  {
    if (empty($this->user) || empty($this->pass)) {
      return ['success' => false, 'message' => 'Gmail SMTP credentials not configured. Add GMAIL_USER and GMAIL_APP_PASSWORD in the environment or set them in Settings > Notifications.'];
    }

    if (empty($to)) {
      return ['success' => false, 'message' => 'Recipient email address is missing.'];
    }

    $attempts = [
      ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS, 'label' => 'STARTTLS on 587'],
      ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS, 'label' => 'SSL on 465'],
    ];

    $lastError = '';

    foreach ($attempts as $attempt) {
      $mail = new PHPMailer(true); // Enable exceptions

      try {
        $mail->isSMTP();
        $mail->Host       = $this->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->user;
        $mail->Password   = $this->pass;
        $mail->SMTPSecure = $attempt['secure'];
        $mail->Port       = $attempt['port'];
        $mail->Timeout    = 20;

        $mail->setFrom($this->user, 'Youth Profiling System');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $message));

        $mail->send();

        $logEntry = date('Y-m-d H:i:s') . " | Email SENT | To: $to | Subject: $subject\n";
        @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

        return ['success' => true, 'message' => 'Email sent successfully via Gmail.'];
      } catch (PHPMailerException $e) {
        $lastError = $mail->ErrorInfo ?: $e->getMessage();

        $logEntry = date('Y-m-d H:i:s') . " | Email FAILED | To: $to | Attempt: {$attempt['label']} | Error: {$lastError}\n";
        @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

        if (stripos($lastError, 'Connection timed out') !== false || stripos($lastError, 'Failed to connect to server') !== false) {
          continue;
        }

        return ['success' => false, 'message' => 'Email error: ' . $lastError];
      }
    }

    if (stripos($lastError, 'Connection timed out') !== false || stripos($lastError, 'Failed to connect to server') !== false) {
      return [
        'success' => false,
        'message' => 'Email error: SMTP connection timed out. This usually means the deployed host is blocking outbound SMTP on port 587/465 or the server cannot reach smtp.gmail.com.'
      ];
    }

    return ['success' => false, 'message' => 'Email error: ' . $lastError];
  }
}
