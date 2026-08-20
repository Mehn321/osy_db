<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Matching.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['lydo', 'employer'], true)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$opportunity_id = isset($_GET['opportunity_id']) ? intval($_GET['opportunity_id']) : 0;

if (!$opportunity_id) {
    echo json_encode(['success' => false, 'message' => 'Opportunity ID is required']);
    exit;
}

$matching = new Matching($database);
$opportunityObj = new Opportunity($database);

$opportunity = $opportunityObj->getById($opportunity_id);
if (!$opportunity || ($role === 'employer' && (int)$opportunity['provider_id'] !== (int)$_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Opportunity not found or not owned by this employer']);
    exit;
}

// Get matches and opportunity details
$matches = $matching->getMatchesForOpportunity($opportunity_id, 0);
echo json_encode([
    'success' => true,
    'data' => $matches,
    'opportunity' => $opportunity
]);
