<?php
require_once __DIR__ . '/init.php';

if (!$user->isLoggedIn() || $_SESSION['role'] !== 'youth') {
    echo "Not logged in as youth\n";
    exit;
}

$userId = $_SESSION['user_id'];
echo "User ID: " . $userId . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'N/A') . "\n";

// Check database directly
$result = $database->fetchOne(
    "SELECT id, user_id, first_name, last_name, verification_status FROM osy_profile WHERE user_id = ? LIMIT 1",
    [$userId],
    "i"
);

if ($result) {
    echo "Profile found:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "No profile in database for this user_id\n";
}

// Check how many profiles exist
$count = $database->fetchOne("SELECT COUNT(*) as cnt FROM osy_profile", [], "");
echo "\nTotal profiles in DB: " . ($count['cnt'] ?? 0) . "\n";
