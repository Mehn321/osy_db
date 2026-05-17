<?php
/**
 * seed_sk_chairmen.php
 * 
 * Seeds dummy SK Chairman accounts + LYDO account into the database.
 * Run once via browser: http://localhost/osy_db/seed_sk_chairmen.php
 * 
 * SK Chairman password: SKChair@123
 * LYDO password: LYDO@123
 */

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/Classes/User.php';

$userModel = new User($database);
$conn = $database->getConnection();

// ── Step 1: Delete old dummy SK chairmen with wrong barangays ───────────────
$oldUsernames = ['sk_brgy1', 'sk_brgy2', 'sk_brgy3', 'sk_brgy4', 'sk_brgy5'];
foreach ($oldUsernames as $oldUsername) {
    $conn->query("DELETE FROM users WHERE username = '" . $database->escape($oldUsername) . "'");
}
echo "<h2>Seeding LYDO & SK Chairman Accounts</h2>";
echo "<pre>";
echo "Cleaned up old dummy accounts.\n\n";

// ── Step 2: Create LYDO account ─────────────────────────────────────────────
$lydoData = [
    'username'  => 'lydo_admin',
    'email'     => 'lydo@civichorizon.ph',
    'fullname'  => 'Maria Teresa Lim',
    'role'      => 'lydo',
    'password'  => 'LYDO@123',
    'status'    => 'Active',
    'temp_password_required' => 0,
];

$result = $userModel->createUser($lydoData);
if ($result['success']) {
    echo "[OK]  Created LYDO: {$lydoData['username']} ({$lydoData['fullname']}) — Password: LYDO@123\n";
} else {
    echo "[SKIP] LYDO {$lydoData['username']}: {$result['message']}\n";
}

echo "\n";

// ── Step 3: Create SK Chairman accounts with real barangay names ────────────
// Password for all accounts: SKChair@123
$chairmen = [
    [
        'username'  => 'sk_baga',
        'email'     => 'sk.baga@civichorizon.ph',
        'fullname'  => 'Carlos Reyes',
        'role'      => 'sk_chairman',
        'password'  => 'SKChair@123',
        'barangay'  => 'Baga',
        'status'    => 'Active',
        'temp_password_required' => 0,
    ],
    [
        'username'  => 'sk_bangko',
        'email'     => 'sk.bangko@civichorizon.ph',
        'fullname'  => 'Angela Mendoza',
        'role'      => 'sk_chairman',
        'password'  => 'SKChair@123',
        'barangay'  => 'Bangko',
        'status'    => 'Active',
        'temp_password_required' => 0,
    ],
    [
        'username'  => 'sk_camanucan',
        'email'     => 'sk.camanucan@civichorizon.ph',
        'fullname'  => 'Jerome Villanueva',
        'role'      => 'sk_chairman',
        'password'  => 'SKChair@123',
        'barangay'  => 'Camanucan',
        'status'    => 'Active',
        'temp_password_required' => 0,
    ],
    [
        'username'  => 'sk_delapaz',
        'email'     => 'sk.delapaz@civichorizon.ph',
        'fullname'  => 'Patricia Santos',
        'role'      => 'sk_chairman',
        'password'  => 'SKChair@123',
        'barangay'  => 'Dela Paz',
        'status'    => 'Active',
        'temp_password_required' => 0,
    ],
    [
        'username'  => 'sk_poblacion',
        'email'     => 'sk.poblacion@civichorizon.ph',
        'fullname'  => 'Marco Dela Cruz',
        'role'      => 'sk_chairman',
        'password'  => 'SKChair@123',
        'barangay'  => 'Poblacion',
        'status'    => 'Active',
        'temp_password_required' => 0,
    ],
];

$created = 0;
$skipped = 0;

foreach ($chairmen as $data) {
    $result = $userModel->createUser($data);
    
    if ($result['success']) {
        echo "[OK]  Created SK Chairman: {$data['username']} ({$data['fullname']}) — {$data['barangay']}\n";
        $created++;
    } else {
        echo "[SKIP] {$data['username']}: {$result['message']}\n";
        $skipped++;
    }
}

echo "\n──────────────────────────────────────────\n";
echo "SK Chairmen — Created: {$created} | Skipped: {$skipped}\n";
echo "\n";
echo "LYDO login:         lydo_admin / LYDO@123\n";
echo "SK Chairman login:  sk_baga, sk_bangko, sk_camanucan, sk_delapaz, sk_poblacion / SKChair@123\n";
echo "</pre>";
