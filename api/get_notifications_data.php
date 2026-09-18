<?php

/**
 * API: Notifications Data
 * Serves notification lists for:
 *   - notifications.php (LYDO — sent notifications)
 *   - my-notifications.php (All users — their inbox)
 *   - notification-templates.php (LYDO — templates)
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role   = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

session_write_close(); // Release session lock for parallel AJAX requests

$cache  = new Cache(20); // Short TTL — notifications should be near-realtime

$notification = new Notification($database);

try {
    $view = $_GET['view'] ?? 'inbox'; // 'inbox' | 'sent' | 'templates'

    if ($view === 'inbox') {
        // Every user fetches their own inbox — no cache (realtime)
        $limit         = (int)($_GET['limit'] ?? 50);
        $notifications = $notification->getUserNotifications($userId, $limit);
        $unread_count  = $notification->getUnreadCount($userId);

        echo json_encode([
            'success'      => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count,
        ]);
        exit;
    }

    if ($view === 'sent' && $role === 'lydo') {
        // LYDO sent notifications — cached briefly
        $cacheKey = 'notifications_sent';
        $data = $cache->remember($cacheKey, function () use ($database) {
            return $database->fetchAll(
                "SELECT n.*, u.fullname as recipient_name
                 FROM notifications n
                 LEFT JOIN users u ON u.id = n.recipient_id
                 ORDER BY n.created_at DESC
                 LIMIT 200"
            );
        }, 20);

        echo json_encode(['success' => true, 'notifications' => $data, 'total' => count($data)]);
        exit;
    }

    if ($view === 'templates' && $role === 'lydo') {
        $cacheKey = 'notification_templates';
        $data = $cache->remember($cacheKey, function () use ($database) {
            return $database->fetchAll(
                "SELECT * FROM notification_templates ORDER BY created_at DESC"
            );
        }, 60);

        echo json_encode(['success' => true, 'templates' => $data]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid view or unauthorized']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching notifications']);
}
