<?php
// api/update_notification_group.php

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
$groupName = trim($_POST['group_name'] ?? '');
$profileIds = $_POST['profile_ids'] ?? [];

if (!$groupId || $groupName === '' || !is_array($profileIds) || empty($profileIds)) {
    echo json_encode(['success' => false, 'message' => 'Group ID, name, and at least one profile required']);
    exit;
}

try {
    // Verify ownership
    $group = $database->fetchOne("SELECT id FROM notification_groups WHERE id = ? AND created_by = ?", [$groupId, $user->getCurrentUserId()], 'ii');
    if (!$group) {
        echo json_encode(['success' => false, 'message' => 'Group not found or access denied.']);
        exit;
    }

    // Update name
    $database->execute('UPDATE notification_groups SET name = ? WHERE id = ?', [$groupName, $groupId], 'si');

    // Re-insert members (delete existing first)
    $database->execute('DELETE FROM group_members WHERE group_id = ?', [$groupId], 'i');
    
    $placeholders = implode(',', array_fill(0, count($profileIds), '(?, ?)'));
    $params = [];
    foreach ($profileIds as $pid) {
        $params[] = $groupId;
        $params[] = (int)$pid;
    }
    $database->execute('INSERT INTO group_members (group_id, osy_id) VALUES ' . $placeholders, $params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
