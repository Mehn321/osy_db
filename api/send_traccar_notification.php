<?php

/**
 * API Endpoint: Send Traccar App Notifications
 * 
 * Sends notifications specifically for display in the Traccar mobile app.
 * Only LYDO, SK (Sangguniang Kabataan), and Opportunity Providers can send these.
 * 
 * POST Parameters:
 * - recipient_ids[] : Array of OSY profile IDs (or single ID)
 * - title : Notification title
 * - message : Notification message
 * - type : Notification type (opportunity, alert, reminder, general)
 * - form_nonce : CSRF token
 */

header('Content-Type: application/json');

// Suppress default error output and catch all errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

// Catch uncaught exceptions
set_exception_handler(function ($exception) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $exception->getMessage()]);
    exit;
});

require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check user role - only LYDO, SK Chairman, and Providers can send Traccar notifications
$userRole = $_SESSION['role'] ?? '';
if (!in_array($userRole, ['lydo', 'sk_chairman', 'provider'])) {
    echo json_encode(['success' => false, 'message' => 'Only LYDO, SK Chairman, and Providers can send notifications.']);
    exit;
}

// Validate form nonce exists (but don't consume yet - only consume after successful processing)
$formNonce = $_POST['form_nonce'] ?? '';
if (empty($formNonce)) {
    echo json_encode(['success' => false, 'message' => 'Missing form nonce.']);
    exit;
}

// Verify nonce is valid (check if it matches session)
if (empty($_SESSION['form_nonce']) || !hash_equals($_SESSION['form_nonce'], $formNonce)) {
    echo json_encode(['success' => false, 'message' => 'This request has already been submitted or the session expired.']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$type = trim($_POST['type'] ?? 'general');
$recipientIds = $_POST['recipient_ids'] ?? [];

// Validate inputs
if (empty($title) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Title and message are required.']);
    exit;
}

if (empty($recipientIds)) {
    echo json_encode(['success' => false, 'message' => 'At least one recipient is required.']);
    exit;
}

// Normalize to array
if (!is_array($recipientIds)) {
    $recipientIds = [$recipientIds];
}

// Convert string IDs to integers
$recipientIds = array_map('intval', array_filter($recipientIds));

if (empty($recipientIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient IDs.']);
    exit;
}

try {
    require_once __DIR__ . '/../Classes/TraccarNotificationService.php';
    $traccarNotif = new TraccarNotificationService($database);

    $senderId = $_SESSION['user_id'];

    // Send based on user role
    if ($userRole === 'lydo') {
        $result = $traccarNotif->sendFromLydo($senderId, $recipientIds, $title, $message, $type);
    } elseif ($userRole === 'sk_chairman') {
        $result = $traccarNotif->sendFromSK($senderId, $recipientIds, $title, $message, $type);
    } elseif ($userRole === 'provider') {
        $result = $traccarNotif->sendFromProvider($senderId, $recipientIds, $title, $message, $type);
    } else {
        $result = ['success' => false, 'message' => 'Invalid role.'];
    }

    // Only consume the nonce after successful processing
    if (!empty($result['success'])) {
        unset($_SESSION['form_nonce']);
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
