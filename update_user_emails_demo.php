<?php
/**
 * Demo: Get one user from each role and update their email
 */

// Get database config
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Classes/Database.php';

$db = new Database();

$targetEmail = 'aclonhemday@gmail.com';

// Define roles and get one user from each
$roles = ['lydo', 'sk_chairman', 'youth', 'employer', 'training_provider', 'admin'];

$credentials = [];
$results = [];

echo "=== SYSTEM USER CREDENTIALS (Demo Sample) ===\n\n";

foreach ($roles as $role) {
    // Get one user of this role
    $query = "SELECT id, username, email, password, fullname, role FROM users WHERE role = ? LIMIT 1";
    $user = $db->fetchOne($query, [$role], "s");
    
    if ($user) {
        $credentials[$role] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'fullname' => $user['fullname'],
            'role' => $user['role'],
            'password_hash' => $user['password']
        ];
        
        echo "Role: " . strtoupper($role) . "\n";
        echo "├─ Username: " . $user['username'] . "\n";
        echo "├─ Fullname: " . $user['fullname'] . "\n";
        echo "├─ Current Email: " . $user['email'] . "\n";
        echo "├─ Password: [BCRYPT HASHED - SEE NOTE BELOW]\n";
        echo "└─ ID: " . $user['id'] . "\n\n";
    }
}

echo "\n=== UPDATING EMAILS TO: " . $targetEmail . " ===\n\n";

// Update emails
foreach ($credentials as $role => $user) {
    $updateQuery = "UPDATE users SET email = ? WHERE id = ?";
    $db->execute($updateQuery, [$targetEmail, $user['id']], "si");
    
    echo "✓ Updated {$user['username']} (ID: {$user['id']}) email to {$targetEmail}\n";
}

echo "\n=== VERIFICATION: UPDATED USERS ===\n\n";

// Verify updates
foreach ($credentials as $role => $user) {
    $verifyQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $updated = $db->fetchOne($verifyQuery, [$user['id']], "i");
    
    if ($updated) {
        echo $role . ": " . $updated['username'] . " - " . $updated['email'] . "\n";
    }
}

echo "\n=== IMPORTANT NOTE ===\n";
echo "Passwords are securely stored as BCRYPT hashes and cannot be retrieved.\n";
echo "You have the following options:\n";
echo "1. Use password reset functionality if available in the system\n";
echo "2. Set temporary passwords manually via admin panel\n";
echo "3. Generate new temporary passwords via the system\n";

?>
