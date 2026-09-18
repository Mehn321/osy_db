<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$osyProfile = new OSYProfile($database);

$profile = $osyProfile->getById($profile_id);

if (!$profile) {
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

if ($_SESSION['role'] === 'sk_chairman') {
    if ($profile['barangay'] !== $_SESSION['barangay']) {
        echo json_encode(['error' => 'unauthorized_barangay']);
        exit;
    }
}

$matching = new Matching($database);
$matches = $matching->getMatchesForOSY($profile_id);

echo json_encode([
    'success' => true,
    'profile' => $profile,
    'matches' => $matches
]);
