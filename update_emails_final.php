<?php
/**
 * Update all demo accounts to use aclonhemday@gmail.com
 * Handles unique constraint by using email aliases
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Classes/Database.php';

$db = new Database();

$baseEmail = 'aclonhemday@gmail.com';
$emailAliases = 'aclonhemday+{alias}@gmail.com';

// Demo accounts to update
$testAccounts = [
    [
        'id' => 1,
        'username' => 'admin1',
        'role' => 'LYDO (Admin)',
        'alias' => 'lydo'
    ],
    [
        'id' => 26,
        'username' => 'sk_baga',
        'role' => 'SK CHAIRMAN',
        'alias' => 'sk'
    ],
    [
        'id' => 10,
        'username' => 'testyouth',
        'role' => 'YOUTH',
        'alias' => 'youth'
    ],
    [
        'id' => 18,
        'username' => 'nhempharmacy',
        'role' => 'EMPLOYER',
        'alias' => 'employer'
    ],
    [
        'id' => 31,
        'username' => 'test_provider',
        'role' => 'TRAINING PROVIDER',
        'alias' => 'provider'
    ]
];

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     UPDATING EMAILS TO aclonhemday@gmail.com (ALIASES)     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  NOTE: Database has UNIQUE constraint on email field.\n";
echo "   Using Gmail aliases (aclonhemday+alias@gmail.com)\n";
echo "   All emails route to: aclonhemday@gmail.com\n\n";

foreach ($testAccounts as $account) {
    // Create alias email
    $aliasEmail = str_replace('{alias}', $account['alias'], $emailAliases);
    
    // Update email in database
    $updateQuery = "UPDATE users SET email = ? WHERE id = ?";
    $db->execute($updateQuery, [$aliasEmail, $account['id']], "si");
    
    // Verify update
    $userQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $user = $db->fetchOne($userQuery, [$account['id']], "i");
    
    if ($user) {
        echo "✓ " . str_pad($account['role'], 25) . " → " . $user['email'] . "\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║              FINAL CREDENTIALS SUMMARY                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

foreach ($testAccounts as $account) {
    $userQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $user = $db->fetchOne($userQuery, [$account['id']], "i");
    
    if ($user) {
        echo "📍 " . strtoupper($account['role']) . "\n";
        echo "   Email:    " . $user['email'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ All emails updated successfully!\n";
echo "📨 All emails route to: aclonhemday@gmail.com\n";
echo "═══════════════════════════════════════════════════════════════\n";

?>
