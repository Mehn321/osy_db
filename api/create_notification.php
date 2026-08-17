<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$recipientId = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : null;

// Allow JSON body as fallback
if (empty($title) || empty($message) || !$recipientId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? $title);
    $message = trim($input['message'] ?? $message);
    $recipientId = isset($input['recipient_id']) ? intval($input['recipient_id']) : $recipientId;
}

if (empty($title) || empty($message) || !$recipientId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters: title, message, and recipient_id']);
    exit;
}

require_once __DIR__ . '/../Classes/Notification.php';
$notification = new Notification($database);

$result = $notification->createForRecipient($title, $message, $recipientId);

echo json_encode($result);
