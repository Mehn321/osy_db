<?php

/**
 * Update test and LYDO accounts to the shared test email.
 */

// Get database config
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Classes/Database.php';

$db = new Database();

$targetEmail = 'dummyseeker22@gmail.com';
$emailAliasTemplate = 'dummyseeker22+{alias}@gmail.com';

$credentials = [];
$results = [];

echo "=== SYSTEM USER CREDENTIALS (Demo Sample) ===\n\n";

// Test accounts use the test prefix or dev suffix. LYDO accounts are included
// because they are part of the local demo login set.
$query = "SELECT id, username, email, password, fullname, role FROM users
          WHERE username LIKE 'test%' OR username LIKE '%_dev' OR role = 'lydo'
          ORDER BY id";
foreach ($db->fetchAll($query) as $user) {
    $credentials[] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'fullname' => $user['fullname'],
        'role' => $user['role'],
        'password_hash' => $user['password']
    ];
    echo "Account: {$user['username']} ({$user['role']})\n";
    echo "├─ Current Email: {$user['email']}\n";
    echo "└─ ID: {$user['id']}\n\n";
}

echo "\n=== UPDATING EMAILS TO: " . $targetEmail . " ===\n\n";

// Update emails
foreach ($credentials as $user) {
    $updateQuery = "UPDATE users SET email = ? WHERE id = ?";
    $alias = preg_replace('/[^a-z0-9]+/i', '', strtolower($user['username'])) . $user['id'];
    $accountEmail = str_replace('{alias}', $alias, $emailAliasTemplate);
    $db->execute($updateQuery, [$accountEmail, $user['id']], "si");

    echo "Updated {$user['username']} (ID: {$user['id']}) email to {$accountEmail}\n";
}

echo "\n=== VERIFICATION: UPDATED USERS ===\n\n";

// Verify updates
foreach ($credentials as $user) {
    $verifyQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $updated = $db->fetchOne($verifyQuery, [$user['id']], "i");

    if ($updated) {
        echo $updated['username'] . ": " . $updated['email'] . "\n";
    }
}

echo "\n=== IMPORTANT NOTE ===\n";
echo "Passwords are securely stored as BCRYPT hashes and cannot be retrieved.\n";
echo "You have the following options:\n";
echo "1. Use password reset functionality if available in the system\n";
echo "2. Set temporary passwords manually via admin panel\n";
echo "3. Generate new temporary passwords via the system\n";
