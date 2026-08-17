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

$notificationId = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : null;

// Allow JSON body as fallback
if (!$notificationId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = isset($input['notification_id']) ? intval($input['notification_id']) : null;
}

if (!$notificationId) {
    echo json_encode(['success' => false, 'message' => 'Missing notification_id']);
    exit;
}

require_once __DIR__ . '/../Classes/Notification.php';
$notification = new Notification($database);

$result = $notification->markAsRead($notificationId, $_SESSION['user_id']);

if ($result['success']) {
    // Get new unread count
    $unreadCount = $notification->getUnreadCount($_SESSION['user_id']);
    $result['unread_count'] = $unreadCount;
}

echo json_encode($result);
