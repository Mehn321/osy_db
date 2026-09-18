<?php
require_once __DIR__ . '/../init.php';
requireLogin();
requireRole('lydo');

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID not specified']);
    exit;
}
$userId = (int)$_GET['user_id'];

$member = $database->fetchOne("SELECT * FROM users WHERE id = ? AND role != 'lydo'", [$userId], 'i');
if (!$member) {
    http_response_code(404);
    echo json_encode(['error' => 'Member not found']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['member' => $member]);
