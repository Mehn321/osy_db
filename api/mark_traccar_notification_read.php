<?php

/**
 * API Endpoint: Mark Traccar Notification as Read
 * 
 * Marks a notification as read in the Traccar app.
 * 
 * POST Parameters:
 * - notification_id : ID of the notification to mark as read
 * - mark_all : Set to 1 to mark all notifications as read for the recipient (optional)
 * - recipient_id : Required if mark_all is set to 1
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$notificationId = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : null;
$markAll = isset($_POST['mark_all']) ? intval($_POST['mark_all']) : 0;
$recipientId = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : null;

// Support JSON body as fallback
if (!$notificationId && !$markAll) {
    $input = json_decode(file_get_contents('php://input'), true);
    $notificationId = isset($input['notification_id']) ? intval($input['notification_id']) : null;
    $markAll = isset($input['mark_all']) ? intval($input['mark_all']) : 0;
    $recipientId = isset($input['recipient_id']) ? intval($input['recipient_id']) : null;
}

try {
    require_once __DIR__ . '/../Classes/TraccarNotificationService.php';
    $traccarNotif = new TraccarNotificationService($database);

    if ($markAll && $recipientId) {
        // Mark all notifications as read for a recipient
        $result = $traccarNotif->markAllAsRead($recipientId);
    } elseif ($notificationId) {
        // Mark single notification as read
        $result = $traccarNotif->markAsRead($notificationId);
    } else {
        $result = ['success' => false, 'message' => 'Either notification_id or recipient_id (with mark_all=1) is required'];
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
