<?php
// api/save_notification_group.php
// Saves a new notification group for the LYDO.
// Expects POST fields: group_name (string), profile_ids[] (array of OSY profile IDs).

header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';

// Ensure user is authenticated
if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$groupName = trim($_POST['group_name'] ?? '');
$profileIds = $_POST['profile_ids'] ?? [];

if ($groupName === '' || !is_array($profileIds) || empty($profileIds)) {
    echo json_encode(['success' => false, 'message' => 'Group name and at least one profile required']);
    exit;
}

try {
    // Insert the new group
    $database->execute('INSERT INTO notification_groups (name, created_by) VALUES (?, ?)', [$groupName, $user->getCurrentUserId()]);
    $groupId = $database->lastInsertId();

    // Insert group members
    $placeholders = implode(',', array_fill(0, count($profileIds), '(?, ?)'));
    $params = [];
    foreach ($profileIds as $pid) {
        $params[] = $groupId;
        $params[] = (int)$pid;
    }
    $database->execute('INSERT INTO group_members (group_id, osy_id) VALUES ' . $placeholders, $params);

    echo json_encode(['success' => true, 'group_id' => $groupId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
