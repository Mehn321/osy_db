<?php
/**
 * Get one user from each role and update their emails
 * Updated approach: Update each with unique email variant
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Classes/Database.php';

$db = new Database();

$baseEmail = 'aclonhemday@gmail.com';

// Define roles and get one user from each
$roles = ['lydo', 'sk_chairman', 'youth', 'employer', 'training_provider'];

$credentials = [];

echo "=== SYSTEM USER CREDENTIALS (One from Each Role) ===\n\n";

foreach ($roles as $role) {
    // Get one user of this role
    $query = "SELECT id, username, email, password, fullname, role FROM users WHERE role = ? LIMIT 1";
    $user = $db->fetchOne($query, [$role], "s");
    
    if ($user) {
        $credentials[$role] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'old_email' => $user['email'],
            'fullname' => $user['fullname'],
            'role' => $user['role'],
            'password_hash' => $user['password']
        ];
        
        echo "╔═ Role: " . strtoupper(str_replace('_', ' ', $role)) . "\n";
        echo "║\n";
        echo "║  ✓ Username: " . $user['username'] . "\n";
        echo "║  ✓ Fullname: " . $user['fullname'] . "\n";
        echo "║  ✓ User ID: " . $user['id'] . "\n";
        echo "║  ✓ Old Email: " . $user['email'] . "\n";
        echo "║\n";
        echo "║  ⚠ Password: [BCRYPT HASHED - Cannot be retrieved]\n";
        echo "║     → Original password is not accessible\n";
        echo "║     → Use password reset feature to set new password\n";
        echo "╚════════════════════════════════════════\n\n";
    }
}

echo "=== UPDATING EMAILS ===\n";
echo "Note: Email field has UNIQUE constraint.\n";
echo "Using pattern: role_username+aclonhemday@gmail.com\n\n";

// Update emails with unique variants
foreach ($credentials as $role => $user) {
    // Create unique email: role+username variant
    $newEmail = $role . '_' . $user['username'] . '@aclonhemday.com';
    
    try {
        $updateQuery = "UPDATE users SET email = ? WHERE id = ?";
        $db->execute($updateQuery, [$newEmail, $user['id']], "si");
        
        echo "✓ {$user['username']} (ID: {$user['id']})\n";
        echo "  └─ Email: {$user['old_email']} → {$newEmail}\n\n";
        
    } catch (Exception $e) {
        echo "✗ Failed to update {$user['username']}: " . $e->getMessage() . "\n\n";
    }
}

echo "=== FINAL CREDENTIALS SUMMARY ===\n\n";

// Display final list
foreach ($credentials as $role => $user) {
    $verifyQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $updated = $db->fetchOne($verifyQuery, [$user['id']], "i");
    
    if ($updated) {
        echo strtoupper($role) . " Account:\n";
        echo "  Username: " . $updated['username'] . "\n";
        echo "  Email: " . $updated['email'] . "\n";
        echo "  \n";
    }
}

echo "=== PASSWORD RECOVERY ===\n";
echo "All passwords are securely hashed using BCRYPT.\n";
echo "To set passwords for testing:\n\n";
echo "Option 1: Use Admin Panel → Password Reset\n";
echo "Option 2: Run this PHP to generate test passwords:\n";
echo "  \$pwd = password_hash('TestPass123!', PASSWORD_BCRYPT);\n";
echo "  → Update database manually with hash\n\n";

?>
