<?php
// api/get_notification_group_members.php
// Returns members of a saved notification group.

header('Content-Type: application/json');
require_once __DIR__ . '/../init.php';

// Ensure user is authenticated
if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$groupId = $_GET['group_id'] ?? null;
if (!$groupId) {
    echo json_encode(['success' => false, 'message' => 'group_id parameter is required']);
    exit;
}

try {
    $rows = $database->fetchAll('SELECT osy_id FROM group_members WHERE group_id = ?', [(int)$groupId], 'i');
    
    $profileIds = [];
    if ($rows) {
        foreach ($rows as $row) {
            $profileIds[] = $row['osy_id'];
        }
    }
    
    echo json_encode(['success' => true, 'profile_ids' => $profileIds]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
