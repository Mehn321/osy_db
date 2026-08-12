<?php

/**
 * AJAX API – Send Chat Message
 * Handles message save + optional SMS/Email dispatch.
 * Returns JSON.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../init.php';

// Auth guard
if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$allowedRoles = ['lydo', 'sk_chairman'];
if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true) || ($_SESSION['status'] ?? '') !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'You are not authorized to send messages.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$osy_id       = intval($_POST['osy_id'] ?? 0);
$message_text = trim($_POST['message_content'] ?? '');
$trigger_sms  = !empty($_POST['trigger_sms']);
$trigger_email = !empty($_POST['trigger_email']);

if (!$osy_id || $message_text === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Load OSY profile for contact info
require_once __DIR__ . '/../Classes/OSYProfile.php';
$osyClass   = new OSYProfile($database);
$chatUser   = $osyClass->getById($osy_id);

if (!$chatUser) {
    echo json_encode(['success' => false, 'message' => 'Recipient not found.']);
    exit;
}

$sms_status   = 'none';
$email_status = 'none';
$sms_error    = null;
$email_error  = null;
$notifications = [];

// ── SMS ──────────────────────────────────────────────────────────────────────
if ($trigger_sms) {
    require_once __DIR__ . '/../Classes/SmsService.php';
    $sms = new SmsService($database);
    $phone = $chatUser['phone'] ?? '';
    $smsResp = $sms->send($phone, $message_text);
    $sms_status = $smsResp['success'] ? 'success' : 'failed';
    if (!$smsResp['success']) {
        $sms_error = $smsResp['message'];
        $notifications[] = '⚠ SMS: ' . $smsResp['message'];
    }
}

// ── Email ─────────────────────────────────────────────────────────────────────
if ($trigger_email) {
    require_once __DIR__ . '/../Classes/EmailService.php';
    $email = new EmailService($database);
    $recipientEmail = $chatUser['email'] ?? '';
    $recipientName  = htmlspecialchars($chatUser['first_name'] . ' ' . $chatUser['last_name']);
    $emailBody = "
<div style='font-family:sans-serif;max-width:600px;margin:0 auto'>
  <div style='background:#1e3a8a;padding:20px 30px;border-radius:12px 12px 0 0'>
    <h2 style='color:white;margin:0'>Youth Profiling System</h2>
  </div>
  <div style='background:#f8fafc;padding:30px;border:1px solid #e2e8f0;border-radius:0 0 12px 12px'>
    <p style='color:#374151'>Dear <strong>{$recipientName}</strong>,</p>
    <p style='color:#374151;white-space:pre-wrap'>" . nl2br(htmlspecialchars($message_text)) . "</p>
    <hr style='border:0;border-top:1px solid #e2e8f0;margin:20px 0'>
    <p style='color:#9ca3af;font-size:12px'>This message was sent by the Youth Profiling System. Do not reply to this email.</p>
  </div>
</div>";
    $emailResp = $email->send($recipientEmail, 'Message from Youth Profiling System', $emailBody);
    $email_status = $emailResp['success'] ? 'success' : 'failed';
    if (!$emailResp['success']) {
        $email_error = $emailResp['message'];
        $notifications[] = '⚠ Email: ' . $emailResp['message'];
    }
}

// ── Save to DB ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../Classes/Messages.php';
$messagesObj = new Messages($database);
$result = $messagesObj->sendMessage([
    'sender_type'    => 'admin',
    'sender_id'      => $_SESSION['user_id'],
    'recipient_type' => 'osy',
    'recipient_id'   => $osy_id,
    'message'        => $message_text,
    'sms_status'     => $sms_status,
    'email_status'   => $email_status,
    'sms_error'      => $sms_error,
    'email_error'    => $email_error,
]);

if ($result['success']) {
    echo json_encode([
        'success'       => true,
        'message'       => $message_text,
        'sms_status'    => $sms_status,
        'email_status'  => $email_status,
        'warnings'      => $notifications,
        'time'          => date('h:i A'),
        'message_id'    => $result['id'] ?? null,
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $result['message']]);
}
