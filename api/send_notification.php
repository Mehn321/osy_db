<?php

/**
 * AJAX API – Broadcast Notification
 * Handles notification logging + optional SMS/Email to recipients.
 * Supports all template variables: {{name}}, {{barangay}}, {{opportunity}},
 * {{company}}, {{course}}, {{percentage}}
 * Returns JSON.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

$title        = trim($_POST['title']        ?? '');
$message_text = trim($_POST['message']      ?? '');
$type         = $_POST['type']              ?? 'System';
$target_group = $_POST['target_group']      ?? 'All';
$smsEnabled   = !empty($_POST['send_sms']);
$emailEnabled = !empty($_POST['send_email']);

// ── Broadcast-wide template variables (same for all recipients) ───────────────
$tplOpportunity = trim($_POST['tpl_opportunity'] ?? '');
$tplCompany     = trim($_POST['tpl_company']     ?? '');
$tplCourse      = trim($_POST['tpl_course']      ?? '');
$tplPercentage  = trim($_POST['tpl_percentage']  ?? '');

if (!$title || !$message_text) {
  echo json_encode(['success' => false, 'message' => 'Title and message are required.']);
  exit;
}

// ── Build recipient list ──────────────────────────────────────────────────────
require_once __DIR__ . '/../Classes/OSYProfile.php';
$osyClass    = new OSYProfile($database);
$allProfiles = $osyClass->getAll();

$recipients        = [];
$recipient_type_db = 'Specific';

switch ($target_group) {
  case 'All':
    $recipients = $allProfiles;
    $recipient_type_db = 'All';
    break;
  case 'OSY':
    $recipients = array_values(array_filter($allProfiles, fn($p) => $p['profile_type'] === 'OSY'));
    $recipient_type_db = 'OSY';
    break;
  case 'Non-OSY':
    $recipients = array_values(array_filter($allProfiles, fn($p) => $p['profile_type'] !== 'OSY'));
    $recipient_type_db = 'Non-OSY';
    break;
  case 'Unemployed':
    $recipients = array_values(array_filter($allProfiles, fn($p) => ($p['status'] ?? '') === 'Unemployed'));
    $recipient_type_db = 'OSY';
    break;
  case 'Employed':
    $recipients = array_values(array_filter($allProfiles, fn($p) => ($p['status'] ?? '') === 'Employed'));
    $recipient_type_db = 'OSY';
    break;
  case 'In Training':
    $recipients = array_values(array_filter($allProfiles, fn($p) => ($p['status'] ?? '') === 'In Training'));
    $recipient_type_db = 'OSY';
    break;
  case 'Matched':
    $matched    = $database->fetchAll("SELECT DISTINCT osy_id FROM osy_matches WHERE match_score >= 70");
    $matchedIds = array_column($matched, 'osy_id');
    $recipients = array_values(array_filter($allProfiles, fn($p) => in_array($p['id'], $matchedIds)));
    $recipient_type_db = 'OSY';
    break;
  case 'Specific':
    $specificIds = $_POST['specific_ids'] ?? [];
    $recipients  = array_values(array_filter($allProfiles, fn($p) => in_array($p['id'], $specificIds)));
    break;
}

/**
 * Replace all template variables in a message string for a given recipient.
 */
function replaceTemplateVars(string $text, array $rec, array $broadcastVars): string
{
  // Per-recipient (personalized)
  $fullName = trim(($rec['first_name'] ?? '') . ' ' . ($rec['last_name'] ?? ''));
  $text = str_replace('{{name}}',  $fullName ?: ($rec['first_name'] ?? ''), $text);
  $text = str_replace('{{barangay}}', $rec['barangay'] ?? '', $text);

  // Broadcast-wide (same for everyone)
  $text = str_replace('{{opportunity}}', $broadcastVars['opportunity'], $text);
  $text = str_replace('{{company}}',     $broadcastVars['company'],     $text);
  $text = str_replace('{{course}}',      $broadcastVars['course'],      $text);
  $text = str_replace('{{percentage}}',  $broadcastVars['percentage'],  $text);

  return $text;
}

$broadcastVars = [
  'opportunity' => $tplOpportunity,
  'company'     => $tplCompany,
  'course'      => $tplCourse,
  'percentage'  => $tplPercentage,
];

/**
 * Build the styled HTML email body.
 */
function buildEmailHtml(string $recipientName, string $personalMsg, string $title): string
{
  $safeMsg  = nl2br(htmlspecialchars($personalMsg));
  $safeTitle = htmlspecialchars($title);
  $safeRecipient = htmlspecialchars($recipientName);
  $year = date('Y');
  return "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f1f5f9;padding:32px 16px'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)'>

        <!-- Header -->
        <tr>
          <td style='background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 100%);padding:32px 40px'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr>
                <td>
                  <p style='margin:0 0 4px 0;font-size:11px;font-weight:700;letter-spacing:2px;color:#93c5fd;text-transform:uppercase'>Youth Profiling</p>
                  <h1 style='margin:0;font-size:24px;font-weight:800;color:#ffffff;line-height:1.2'>Youth Registry System</h1>
                </td>
                <td align='right' style='vertical-align:middle'>
                  <div style='width:48px;height:48px;background:rgba(255,255,255,0.15);border-radius:12px;display:inline-flex;align-items:center;justify-content:center'>
                    <span style='font-size:28px;color:#ffffff'>🔔</span>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Subject Banner -->
        <tr>
          <td style='background:#eff6ff;padding:14px 40px;border-bottom:1px solid #dbeafe'>
            <p style='margin:0;font-size:13px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:1px'>" . htmlspecialchars($title) . "</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style='padding:36px 40px'>
            <p style='margin:0 0 20px 0;font-size:16px;color:#374151'>Dear <strong style='color:#1e3a8a'>{$safeRecipient}</strong>,</p>
            <div style='font-size:15px;line-height:1.8;color:#4b5563;background:#f8fafc;border-left:4px solid #1d4ed8;border-radius:0 8px 8px 0;padding:20px 24px;margin-bottom:28px'>
              {$safeMsg}
            </div>
            <p style='margin:0;font-size:13px;color:#6b7280'>If you have questions, please contact your assigned youth coordinator or visit the Youth Profiling System support.</p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style='background:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px'>
            <p style='margin:0 0 4px 0;font-size:12px;color:#9ca3af;text-align:center'>This is an official notification from the Youth Profiling System.</p>
            <p style='margin:0;font-size:12px;color:#9ca3af;text-align:center'>Please do not reply to this email. © {$year} Youth Profiling System</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";
}

// ── Dispatch SMS / Email ──────────────────────────────────────────────────────
$sms_sent = 0;
$sms_failed = 0;
$email_sent = 0;
$email_failed = 0;
$errors = [];

if ($smsEnabled || $emailEnabled) {
  if ($smsEnabled) {
    require_once __DIR__ . '/../Classes/SmsService.php';
    $sms = new SmsService($database);
  }
  if ($emailEnabled) {
    require_once __DIR__ . '/../Classes/EmailService.php';
    $emailSvc = new EmailService($database);
  }

  foreach ($recipients as $rec) {
    $personalMsg  = replaceTemplateVars($message_text, $rec, $broadcastVars);
    $recipientName = trim(($rec['first_name'] ?? '') . ' ' . ($rec['last_name'] ?? ''));

    if ($smsEnabled && !empty($rec['phone'])) {
      // SMS: strip HTML and send plain text
      $smsText = strip_tags($personalMsg);
      $r = $sms->send($rec['phone'], $smsText);
      $r['success'] ? $sms_sent++ : $sms_failed++;
    }

    if ($emailEnabled && !empty($rec['email'])) {
      $htmlBody = buildEmailHtml($recipientName, $personalMsg, $title);
      $r = $emailSvc->send($rec['email'], $title, $htmlBody);
      $r['success'] ? $email_sent++ : $email_failed++;
      if (!$r['success']) $errors[] = $rec['email'] . ': ' . $r['message'];
    }
  }
}

// ── Log to DB ─────────────────────────────────────────────────────────────────
$notification = new Notification($database);
$result = $notification->create([
  'title'          => $title,
  'message'        => $message_text,
  'type'           => $type,
  'recipient_type' => $recipient_type_db,
]);

$summary = 'Notification broadcast successfully to ' . count($recipients) . ' recipient(s).';
if ($smsEnabled || $emailEnabled) {
  $parts = [];
  if ($smsEnabled)   $parts[] = "SMS: {$sms_sent} sent, {$sms_failed} failed";
  if ($emailEnabled) $parts[] = "Email: {$email_sent} sent, {$email_failed} failed";
  $summary .= ' | ' . implode(' | ', $parts);
}

echo json_encode([
  'success'      => $result['success'],
  'message'      => $result['success'] ? $summary : $result['message'],
  'sms_sent'     => $sms_sent,
  'sms_failed'   => $sms_failed,
  'email_sent'   => $email_sent,
  'email_failed' => $email_failed,
  'errors'       => array_slice($errors, 0, 5),
  'notification' => $result['success'] ? [
    'id'             => $result['id'],
    'title'          => htmlspecialchars($title),
    'message'        => htmlspecialchars($message_text),
    'type'           => htmlspecialchars($type),
    'recipient_type' => $recipient_type_db,
    'status'         => 'Sent',
    'created_at'     => date('Y-m-d H:i:s'),
  ] : null,
]);
