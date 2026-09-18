<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn() || ($_SESSION['role'] ?? '') !== 'youth') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Classes/Reference.php';
$osyProfile = new OSYProfile($database);
$profile = $osyProfile->getByUserId($_SESSION['user_id']);

if (!$profile) {
    echo json_encode(['error' => 'No profile found']);
    exit;
}

$matching = new Matching($database);
$applications = [];
if (($profile['verification_status'] ?? '') === 'Verified') {
    $applications = $matching->getMatchesForOSY($profile['id']);
}

$pendingTransfer = null;
try {
    $pendingTransfer = $database->fetchOne(
        "SELECT * FROM youth_barangay_transfers WHERE profile_id = ? AND status = 'Pending' LIMIT 1",
        [$profile['id']],
        'i'
    );
} catch (Exception $e) {
    $pendingTransfer = null;
}

echo json_encode([
    'profile' => $profile,
    'applications' => $applications,
    'pendingTransfer' => $pendingTransfer,
    'session_status' => $_SESSION['status'] ?? 'Pending',
    'needsAction' => in_array($profile['verification_status'] ?? '', ['Declined', 'Rejected', 'Action Required'], true),
    'isVerified' => ($profile['verification_status'] ?? '') === 'Verified',
    'photoSrc' => !empty($profile['image_path']) ? '../' . ltrim($profile['image_path'], '/') : ''
]);
