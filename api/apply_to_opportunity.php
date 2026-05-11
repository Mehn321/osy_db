<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$opportunity_id = $_POST['opportunity_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$opportunity_id || $action !== 'apply') {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Only verified youth can apply
if ($_SESSION['role'] !== 'youth' || $_SESSION['status'] !== 'Active') {
    echo json_encode(['success' => false, 'message' => 'Only verified youth can apply to opportunities']);
    exit;
}

require_once __DIR__ . '/../Classes/Matching.php';
$matching = new Matching($database);

// Check if youth already applied
$existingMatch = $matching->getMatchByYouthAndOpportunity($_SESSION['user_id'], $opportunity_id);
if ($existingMatch) {
    echo json_encode(['success' => false, 'message' => 'You have already applied to this opportunity']);
    exit;
}

// Create new match/application
$result = $matching->createMatchFromArray([
    'profile_id' => $_SESSION['user_id'],
    'opportunity_id' => $opportunity_id,
    'status' => 'Pending'
]);

if ($result['success']) {
    // Log the application in audit trail
    $auditLog = new AuditLog($database);
    $auditLog->logAction(
        $_SESSION['user_id'],
        $_SESSION['role'],
        'applied_to_opportunity',
        'opportunity',
        $opportunity_id,
        ['action' => 'youth_application']
    );

    // Send notification to provider
    require_once __DIR__ . '/../Classes/Opportunity.php';
    $opportunityClass = new Opportunity($database);
    $opp = $opportunityClass->getById($opportunity_id);

    if ($opp && $opp['provider_id']) {
        require_once __DIR__ . '/../Classes/Notification.php';
        $notification = new Notification($database);

        // Get youth profile info
        require_once __DIR__ . '/../Classes/OSYProfile.php';
        $osyProfile = new OSYProfile($database);
        $profile = $osyProfile->getByUserId($_SESSION['user_id']);

        if (!$profile) {
            echo json_encode(['success' => false, 'message' => 'Youth profile not found.']);
            exit;
        }

        $youthName = trim($profile['first_name'] . ' ' . $profile['last_name']);

        $notification->sendToUser(
            $opp['provider_id'],
            'New Application Received',
            $youthName . ' has applied to your opportunity: ' . $opp['title'],
            'Opportunity'
        );
    }
}

echo json_encode($result);
