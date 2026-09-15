<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!in_array($_SESSION['role'] ?? '', ['employer', 'training_provider'], true)) {
    echo json_encode(['success' => false, 'message' => 'Only employers and training providers can make candidate decisions.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Read JSON input or standard POST data
$input = json_decode(file_get_contents('php://input'), true);
$match_id = $input['match_id'] ?? $_POST['match_id'] ?? null;
$status = $input['status'] ?? $_POST['status'] ?? null;

if (!$match_id || !in_array($status, ['Accepted', 'Rejected', 'Pending'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

require_once __DIR__ . '/../Classes/Matching.php';
$matching = new Matching($database);

$ownedMatch = $database->fetchOne(
    "SELECT m.id FROM osy_matches m JOIN opportunities o ON o.id = m.opportunity_id WHERE m.id = ? AND o.provider_id = ? LIMIT 1",
    [intval($match_id), (int)$_SESSION['user_id']],
    'ii'
);
if (!$ownedMatch) {
    echo json_encode(['success' => false, 'message' => 'You can only update matches for your own opportunities.']);
    exit;
}

$result = $matching->updateMatchStatus(intval($match_id), $status);

echo json_encode($result);
