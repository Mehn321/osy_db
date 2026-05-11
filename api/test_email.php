<?php

/**
 * AJAX API – Test Email / SMS Connection
 * Called from Settings > Notifications testing panel.
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

$action = $_POST['action'] ?? '';

if ($action === 'test_email') {
    $to = trim($_POST['test_email_addr'] ?? '');
    if (empty($to)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a recipient email address.']);
        exit;
    }
    require_once __DIR__ . '/../Classes/EmailService.php';
    $email  = new EmailService($database);
    $result = $email->send(
        $to,
        'System Test – Youth Profiling System',
        "<div style='font-family:sans-serif;max-width:500px;margin:0 auto;padding:30px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0'>
          <h2 style='color:#1e3a8a;margin:0 0 16px'>✅ Email Test Successful!</h2>
          <p style='color:#374151'>Your Gmail SMTP configuration is working correctly.</p>
          <p style='color:#6b7280;font-size:13px;margin-top:20px'>Sent at: " . date('F d, Y h:i A') . "<br>From: Youth Profiling System</p>
        </div>"
    );
    echo json_encode($result);
} elseif ($action === 'test_sms') {
    $phone = trim($_POST['test_phone'] ?? '');
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a phone number.']);
        exit;
    }
    require_once __DIR__ . '/../Classes/SmsService.php';
    $sms    = new SmsService($database);
    $result = $sms->send($phone, 'Profiling System: This is a test SMS message via Traccar.');
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
