<?php
// api/delete_notification_group.php

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

$groupId = $_POST['group_id'] ?? null;
if (!$groupId) {
    echo json_encode(['success' => false, 'message' => 'Group ID is required']);
    exit;
}

try {
    // Verify ownership
    $group = $database->fetchOne("SELECT id FROM notification_groups WHERE id = ? AND created_by = ?", [$groupId, $user->getCurrentUserId()], 'ii');
    if (!$group) {
        echo json_encode(['success' => false, 'message' => 'Group not found or access denied.']);
        exit;
    }

    $database->execute('DELETE FROM notification_groups WHERE id = ?', [$groupId], 'i');
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
