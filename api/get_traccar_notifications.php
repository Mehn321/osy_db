<?php

/**
 * API Endpoint: Get Traccar App Notifications
 * 
 * Retrieves notifications for display in the Traccar mobile app.
 * 
 * Query Parameters:
 * - recipient_id : OSY profile ID (required)
 * - unread_only : Set to 1 to get only unread notifications (optional, default: 0)
 * - limit : Maximum number of notifications to return (optional, default: 50)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';

// Get parameters
$recipientId = isset($_GET['recipient_id']) ? intval($_GET['recipient_id']) : null;
$unreadOnly = isset($_GET['unread_only']) ? intval($_GET['unread_only']) : 0;
$limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 50;

if (!$recipientId) {
    echo json_encode(['success' => false, 'message' => 'recipient_id is required']);
    exit;
}

try {
    require_once __DIR__ . '/../Classes/TraccarNotificationService.php';
    $traccarNotif = new TraccarNotificationService($database);

    if ($unreadOnly) {
        $notifications = $traccarNotif->getUnreadNotifications($recipientId, $limit);
        $unreadCount = count($notifications);
    } else {
        $notifications = $traccarNotif->getNotifications($recipientId, $limit);
        $unreadCount = $traccarNotif->getUnreadCount($recipientId);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Notifications retrieved successfully',
        'data' => $notifications,
        'count' => count($notifications),
        'unread_count' => $unreadCount
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
