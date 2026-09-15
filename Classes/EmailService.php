<?php

/**
 * EmailService Class
 * 
 * Handles sending emails exclusively via Brevo (formerly Sendinblue) HTTP API.
 * Works over HTTPS (port 443) which avoids SMTP port blocks on Render/Railway.
 * 
 * Credentials are loaded from environment variables or the system_settings database table.
 */

class EmailService
{
  private Database $db;

  // Brevo (Sendinblue) HTTP API credentials
  private string $brevoApiKey = '';
  private string $brevoSenderEmail = '';
  private string $brevoSenderName = 'Youth Profiling System';
  private string $credentialSource = 'missing';

  public function __construct(Database $database)
  {
    $this->db = $database;
    $this->loadSettings();
  }

  private function loadSettings(): void
  {
    // 1. Try database first
    $dbBrevoKey = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'brevo_api_key'"
    );
    $dbBrevoSender = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'brevo_sender_email'"
    );

    $this->brevoApiKey = trim((string) ($dbBrevoKey['setting_value'] ?? ''));
    $this->brevoSenderEmail = trim((string) ($dbBrevoSender['setting_value'] ?? ''));

    if ($this->brevoApiKey !== '' && $this->brevoSenderEmail !== '') {
      $this->credentialSource = 'database';
    }

    // 2. Fallback to environment variables
    if ($this->brevoApiKey === '') {
      $this->brevoApiKey = trim((string) (getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? $_SERVER['BREVO_API_KEY'] ?? '')));
    }
    if ($this->brevoSenderEmail === '') {
      $this->brevoSenderEmail = trim((string) (getenv('BREVO_SENDER_EMAIL') ?: ($_ENV['BREVO_SENDER_EMAIL'] ?? $_SERVER['BREVO_SENDER_EMAIL'] ?? '')));
    }

    if ($this->brevoApiKey !== '' && $this->brevoSenderEmail !== '' && $this->credentialSource === 'missing') {
      $this->credentialSource = 'environment';
    }
  }

  /**
   * Check if Brevo API is configured and available.
   */
  private function hasBrevoCredentials(): bool
  {
    return $this->brevoApiKey !== '' && $this->brevoSenderEmail !== '';
  }

  public function buildStyledEmail(string $title, string $message, ?string $ctaText = null, ?string $ctaUrl = null): string
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
   * Send email via Brevo HTTP API.
   * 
   * @param string $to Recipient email
   * @param string $subject Email subject
   * @param string $message Email body (HTML supported)
   * @return array{success: bool, message: string}
   */
  public function send(string $to, string $subject, string $message): array
  {
    if (empty($to)) {
      return ['success' => false, 'message' => 'Recipient email address is missing.'];
    }

    if (!$this->hasBrevoCredentials()) {
      return [
        'success' => false, 
        'message' => 'Brevo API is not configured. Set BREVO_API_KEY and BREVO_SENDER_EMAIL in environment variables or Settings > Notifications.'
      ];
    }

    $payload = json_encode([
      'sender'      => [
        'name'  => $this->brevoSenderName,
        'email' => $this->brevoSenderEmail,
      ],
      'to'          => [
        ['email' => $to],
      ],
      'subject'     => $subject,
      'htmlContent' => $message,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL            => 'https://api.brevo.com/v3/smtp/email',
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $payload,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 30,
      CURLOPT_HTTPHEADER     => [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . $this->brevoApiKey,
      ],
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log the attempt
    $logEntry = date('Y-m-d H:i:s') . " | Brevo API | HTTP $httpCode | To: $to | Subject: $subject\n";
    @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

    if ($curlError !== '') {
      error_log("Brevo cURL error: $curlError");
      return ['success' => false, 'message' => "Brevo API connection error: $curlError"];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
      return ['success' => true, 'message' => 'Email sent successfully via Brevo.'];
    }

    // Parse the error response
    $decoded = json_decode((string) $response, true);
    $apiMessage = $decoded['message'] ?? ($decoded['error'] ?? $response);
    error_log("Brevo API error (HTTP $httpCode): $apiMessage");

    return [
      'success' => false,
      'message' => "Brevo API error (HTTP $httpCode): $apiMessage",
    ];
  }
}
