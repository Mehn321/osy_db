<?php

/**
 * EmailService Class
 * 
 * Handles sending emails via:
 *  1. Brevo (formerly Sendinblue) HTTP API — used on deployed platforms (Render, Railway)
 *     where SMTP ports 587/465 are blocked. Works over HTTPS (port 443).
 *  2. Gmail SMTP via PHPMailer — used as fallback (e.g. localhost/XAMPP).
 * 
 * Credentials are loaded from environment variables or the system_settings database table.
 */

// Load PHPMailer library (no Composer required)
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/OAuthTokenProvider.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
  private Database $db;
  private string $user = '';
  private string $pass = '';
  private string $host = 'smtp.gmail.com';
  private int $port = 587;
  private string $credentialSource = 'missing';

  // Brevo (Sendinblue) HTTP API credentials
  private string $brevoApiKey = '';
  private string $brevoSenderEmail = '';
  private string $brevoSenderName = 'Youth Profiling System';

  public function __construct(Database $database)
  {
    $this->db = $database;
    $this->loadSettings();
  }

  private function loadSettings(): void
  {
    // --- Load Gmail SMTP credentials ---
    $dbUser = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_user'"
    );
    $dbPass = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'gmail_app_password'"
    );

    $this->user = trim((string) ($dbUser['setting_value'] ?? ''));
    $this->pass = trim((string) ($dbPass['setting_value'] ?? ''));

    if ($this->user !== '' && $this->pass !== '') {
      $this->credentialSource = 'database';
    }

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

    if ($this->user !== '' && $this->pass !== '' && $this->credentialSource === 'missing') {
      $this->credentialSource = 'environment';
    }

    // --- Load Brevo API credentials ---
    // 1. Try database first
    $dbBrevoKey = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'brevo_api_key'"
    );
    $dbBrevoSender = $this->db->fetchOne(
      "SELECT setting_value FROM system_settings WHERE setting_key = 'brevo_sender_email'"
    );

    $this->brevoApiKey = trim((string) ($dbBrevoKey['setting_value'] ?? ''));
    $this->brevoSenderEmail = trim((string) ($dbBrevoSender['setting_value'] ?? ''));

    // 2. Fallback to environment variables
    if ($this->brevoApiKey === '') {
      $this->brevoApiKey = trim((string) (getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? $_SERVER['BREVO_API_KEY'] ?? '')));
    }
    if ($this->brevoSenderEmail === '') {
      $this->brevoSenderEmail = trim((string) (getenv('BREVO_SENDER_EMAIL') ?: ($_ENV['BREVO_SENDER_EMAIL'] ?? $_SERVER['BREVO_SENDER_EMAIL'] ?? '')));
    }

    // 3. If no dedicated Brevo sender, fall back to the Gmail address
    if ($this->brevoSenderEmail === '' && $this->user !== '') {
      $this->brevoSenderEmail = $this->user;
    }
  }

  /**
   * Check if Brevo API is configured and available.
   */
  private function hasBrevoCredentials(): bool
  {
    return $this->brevoApiKey !== '' && $this->brevoSenderEmail !== '';
  }

  /**
   * Send email via Brevo (Sendinblue) HTTP API.
   * Uses HTTPS (port 443) which is NOT blocked by Render, Railway, etc.
   *
   * @param string $to      Recipient email
   * @param string $subject Email subject
   * @param string $html    Email body (HTML)
   * @return array{success: bool, message: string}
   */
  private function sendViaBrevo(string $to, string $subject, string $html): array
  {
    $payload = json_encode([
      'sender'      => [
        'name'  => $this->brevoSenderName,
        'email' => $this->brevoSenderEmail,
      ],
      'to'          => [
        ['email' => $to],
      ],
      'subject'     => $subject,
      'htmlContent' => $html,
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

  private function getTransportDiagnostics(array $attempt, float $startedAt, array $debugMessages, string $error): array
  {
    $resolvedIps = gethostbynamel($this->host);
    $debug = trim(implode(' | ', $debugMessages));
    $lowerError = strtolower($error . ' ' . $debug);
    $phase = 'smtp';

    if (strpos($lowerError, 'timed out') !== false || strpos($lowerError, 'timeout') !== false) {
      $phase = 'tcp-timeout';
    } elseif (strpos($lowerError, 'could not resolve') !== false || strpos($lowerError, 'getaddrinfo') !== false || $resolvedIps === false) {
      $phase = 'dns';
    } elseif (strpos($lowerError, 'tls') !== false || strpos($lowerError, 'certificate') !== false || strpos($lowerError, 'ssl') !== false) {
      $phase = 'tls';
    } elseif (strpos($lowerError, 'authentication') !== false || strpos($lowerError, 'username') !== false || strpos($lowerError, 'password') !== false || strpos($lowerError, '535') !== false) {
      $phase = 'authentication';
    } elseif (preg_match('/\b[45]\d\d\b/', $error)) {
      $phase = 'smtp-response';
    }

    return [
      'phase' => $phase,
      'host' => $this->host,
      'resolved_ips' => $resolvedIps ?: [],
      'port' => (int) $attempt['port'],
      'encryption' => (string) $attempt['label'],
      'duration_ms' => round((microtime(true) - $startedAt) * 1000),
      'credential_source' => $this->credentialSource,
      'php_version' => PHP_VERSION,
      'openssl' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'unavailable',
      'stream_socket_client' => function_exists('stream_socket_client'),
      'error' => $error,
      'debug' => $debug,
    ];
  }

  private function formatDiagnosticMessage(array $diagnostics): string
  {
    $ips = empty($diagnostics['resolved_ips']) ? 'none' : implode(',', $diagnostics['resolved_ips']);
    $debug = $diagnostics['debug'] !== '' ? ' Debug: ' . $diagnostics['debug'] : '';

    return sprintf(
      'Email error [%s] on %s (%s): %s Diagnostics: dns=%s; port=%d; duration=%dms; credentials=%s; PHP=%s; OpenSSL=%s.%s',
      $diagnostics['phase'],
      $diagnostics['host'],
      $diagnostics['encryption'],
      $diagnostics['error'],
      $ips,
      $diagnostics['port'],
      $diagnostics['duration_ms'],
      $diagnostics['credential_source'],
      $diagnostics['php_version'],
      $diagnostics['openssl'],
      $debug
    );
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
   * Send email — tries Brevo HTTP API first (works on Render/Railway),
   * then falls back to Gmail SMTP (works on localhost/XAMPP).
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

    // ──────────────────────────────────────────────
    // 1. Try Brevo HTTP API first (works on Render)
    // ──────────────────────────────────────────────
    if ($this->hasBrevoCredentials()) {
      $brevoResult = $this->sendViaBrevo($to, $subject, $message);

      if ($brevoResult['success']) {
        return $brevoResult;
      }

      // Log Brevo failure but continue to SMTP fallback
      error_log('Brevo failed, falling back to SMTP: ' . $brevoResult['message']);
    }

    // ──────────────────────────────────────────────
    // 2. Fallback: Gmail SMTP via PHPMailer
    // ──────────────────────────────────────────────
    if (empty($this->user) || empty($this->pass)) {
      // Neither Brevo nor SMTP is configured
      $hint = $this->hasBrevoCredentials()
        ? 'Brevo API failed and Gmail SMTP credentials are not configured.'
        : 'No email provider configured. Set BREVO_API_KEY (recommended for Render) or GMAIL_USER + GMAIL_APP_PASSWORD in environment variables, or configure them in Settings > Notifications.';
      return ['success' => false, 'message' => $hint];
    }

    $attempts = [
      ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS, 'label' => 'STARTTLS on 587'],
      ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS, 'label' => 'SSL on 465'],
    ];

    $lastError = '';
    $lastDiagnostics = null;

    foreach ($attempts as $attempt) {
      $mail = new PHPMailer(true); // Enable exceptions
      $startedAt = microtime(true);
      $debugMessages = [];

      try {
        $mail->isSMTP();
        $mail->Host       = $this->host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->user;
        $mail->Password   = $this->pass;
        $mail->SMTPSecure = $attempt['secure'];
        $mail->Port       = $attempt['port'];
        $mail->Timeout    = 20;
        $mail->SMTPDebug  = SMTP::DEBUG_CONNECTION;
        $mail->Debugoutput = static function (string $message) use (&$debugMessages): void {
          $debugMessages[] = trim(preg_replace('/\s+/', ' ', $message));
        };
        // Allow self-signed certificates for environments without proper CA bundle
        $mail->SMTPOptions = [
          'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
          ],
        ];
        $mail->setFrom($this->user, 'Youth Profiling System');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $message));

        $mail->send();

        $logEntry = date('Y-m-d H:i:s') . " | Email SENT (SMTP) | To: $to | Subject: $subject\n";
        @file_put_contents(__DIR__ . '/../email_log.txt', $logEntry, FILE_APPEND);

        return ['success' => true, 'message' => 'Email sent successfully via Gmail.'];
      } catch (PHPMailerException $e) {
        $lastError = $mail->ErrorInfo ?: $e->getMessage();
        $diagnostics = $this->getTransportDiagnostics($attempt, $startedAt, $debugMessages, $lastError);
        $lastDiagnostics = $diagnostics;

        error_log('Email diagnostics: ' . json_encode([
          'to' => $to,
          'subject' => $subject,
          'diagnostics' => $diagnostics,
        ], JSON_UNESCAPED_SLASHES));

        if (stripos($lastError, 'Connection timed out') !== false || stripos($lastError, 'Failed to connect to server') !== false) {
          continue;
        }

        return ['success' => false, 'message' => $this->formatDiagnosticMessage($diagnostics)];
      }
    }

    if (stripos($lastError, 'Connection timed out') !== false || stripos($lastError, 'Failed to connect to server') !== false) {
      $diagnostics = $lastDiagnostics ?? [
        'phase' => 'unknown',
        'host' => $this->host,
        'resolved_ips' => [],
        'port' => $this->port,
        'encryption' => 'unknown',
        'duration_ms' => 0,
        'credential_source' => $this->credentialSource,
        'php_version' => PHP_VERSION,
        'openssl' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'unavailable',
        'error' => $lastError,
        'debug' => '',
      ];
      return [
        'success' => false,
        'message' => $this->formatDiagnosticMessage($diagnostics)
      ];
    }

    return ['success' => false, 'message' => 'Email error: ' . $lastError];
  }
}
