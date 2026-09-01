<?php
/**
 * Set Test Passwords for Demo Accounts
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Classes/Database.php';

$db = new Database();

// Define test passwords for each role
$testAccounts = [
    [
        'id' => 1,
        'username' => 'admin1',
        'role' => 'LYDO (Admin)',
        'test_password' => 'LydoAdmin@2026'
    ],
    [
        'id' => 26,
        'username' => 'sk_baga',
        'role' => 'SK CHAIRMAN',
        'test_password' => 'SkChairman@2026'
    ],
    [
        'id' => 10,
        'username' => 'testyouth',
        'role' => 'YOUTH',
        'test_password' => 'YouthUser@2026'
    ],
    [
        'id' => 18,
        'username' => 'nhempharmacy',
        'role' => 'EMPLOYER',
        'test_password' => 'Employer@2026'
    ],
    [
        'id' => 31,
        'username' => 'test_provider',
        'role' => 'TRAINING PROVIDER',
        'test_password' => 'Provider@2026'
    ]
];

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        DEMO ACCOUNT CREDENTIALS - ONE FROM EACH ROLE       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

foreach ($testAccounts as $account) {
    // Hash the test password
    $hashed_password = password_hash($account['test_password'], PASSWORD_BCRYPT);
    
    // Update password in database
    $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
    $db->execute($updateQuery, [$hashed_password, $account['id']], "si");
    
    // Get updated user info
    $userQuery = "SELECT id, username, email, role FROM users WHERE id = ?";
    $user = $db->fetchOne($userQuery, [$account['id']], "i");
    
    if ($user) {
        echo "╔═ " . $account['role'] . "\n";
        echo "║\n";
        echo "║  📧 Email:    " . $user['email'] . "\n";
        echo "║  👤 Username: " . $user['username'] . "\n";
        echo "║  🔐 Password: " . $account['test_password'] . "\n";
        echo "║\n";
        echo "║  ⚠️  Test Credentials - Change password on first login!\n";
        echo "║\n";
        echo "╚════════════════════════════════════════════════════════════\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "All passwords have been set for testing.\n";
echo "IMPORTANT: Change these passwords immediately after login!\n";
echo "═══════════════════════════════════════════════════════════════\n";

?>
