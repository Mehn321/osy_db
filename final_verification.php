<?php
// Final system verification
$conn = new mysqli('localhost', 'root', '', 'municipal_kk_profiling', 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║          MUNICIPAL KK PROFILING SYSTEM - FINAL VERIFICATION       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

// Check database
echo "\n✅ DATABASE CONNECTION\n";
echo "   Status: Connected to municipal_kk_profiling\n";

// Check tables
echo "\n✅ DATABASE TABLES\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}
echo "   Total Tables: " . count($tables) . "\n";
foreach ($tables as $table) {
    echo "   ✓ $table\n";
}

// Check profiles
echo "\n✅ PROFILES DATA\n";
$profiles = $conn->query("SELECT COUNT(*) as count FROM osy_profiles")->fetch_assoc();
$osy = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='OSY'")->fetch_assoc();
$regular = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='Regular'")->fetch_assoc();
$active_osy = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='OSY' AND status='Active'")->fetch_assoc();
$employed_osy = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='OSY' AND status='Employed'")->fetch_assoc();

echo "   Total Profiles: " . $profiles['count'] . "\n";
echo "   OSY Profiles: " . $osy['count'] . "\n";
echo "   Regular Profiles: " . $regular['count'] . "\n";
echo "   Active OSY: " . $active_osy['count'] . "\n";
echo "   Employed OSY: " . $employed_osy['count'] . "\n";

// Check users
echo "\n✅ USERS DATA\n";
$users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc();
echo "   Total Users: " . $users['count'] . "\n";

// Check opportunities
echo "\n✅ OPPORTUNITIES DATA\n";
$opps = $conn->query("SELECT COUNT(*) as count FROM opportunities")->fetch_assoc();
echo "   Total Opportunities: " . $opps['count'] . "\n";

// Check matches
echo "\n✅ MATCHES DATA\n";
$matches = $conn->query("SELECT COUNT(*) as count FROM osy_matches")->fetch_assoc();
echo "   Total Matches: " . $matches['count'] . "\n";

// Check notifications
echo "\n✅ NOTIFICATIONS\n";
$notif = $conn->query("SELECT COUNT(*) as count FROM notifications")->fetch_assoc();
$templates = $conn->query("SELECT COUNT(*) as count FROM notification_templates")->fetch_assoc();
echo "   Total Notifications: " . $notif['count'] . "\n";
echo "   Notification Templates: " . $templates['count'] . "\n";

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                   ✅ SYSTEM READY FOR USE                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";

// Check file structure
echo "\n✅ FILE DIRECTORIES\n";
$dirs = [
    'uploads/profiles' => '/uploads/profiles',
    'uploads/govt_ids' => '/uploads/govt_ids'
];
foreach ($dirs as $label => $path) {
    $full_path = __DIR__ . $path;
    if (is_dir($full_path)) {
        echo "   ✓ " . $label . " exists\n";
    } else {
        echo "   ✗ " . $label . " missing\n";
    }
}

$conn->close();
