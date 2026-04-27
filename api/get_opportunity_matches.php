<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Matching.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$opportunity_id = isset($_GET['opportunity_id']) ? intval($_GET['opportunity_id']) : 0;

if (!$opportunity_id) {
    echo json_encode(['success' => false, 'message' => 'Opportunity ID is required']);
    exit;
}

$matching = new Matching($database);
$opportunityObj = new Opportunity($database);

// Ensure matches are generated for this opportunity
$matching->generateMatches($opportunity_id);

// Get matches and opportunity details
$matches = $matching->getMatchesForOpportunity($opportunity_id, 0);
$opportunity = $opportunityObj->getById($opportunity_id);

echo json_encode([
    'success' => true,
    'data' => $matches,
    'opportunity' => $opportunity
]);
