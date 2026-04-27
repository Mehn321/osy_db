<?php
// Direct database connection
$conn = new mysqli('localhost', 'root', '', 'municipal_kk_profiling', 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Check profile data
$result = $conn->query('SELECT id, CONCAT(first_name, " ", last_name) as name, profile_type, status FROM osy_profiles ORDER BY id');

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "\n=== Profile Type Verification ===\n";
printf("%-3s | %-30s | %-10s | %-12s\n", 'ID', 'Name', 'Type', 'Status');
echo str_repeat('-', 60) . "\n";

$osy_count = 0;
$regular_count = 0;

while ($row = $result->fetch_assoc()) {
    printf(
        "%-3s | %-30s | %-10s | %-12s\n",
        $row['id'],
        substr($row['name'], 0, 28),
        $row['profile_type'],
        $row['status']
    );

    if ($row['profile_type'] === 'OSY') {
        $osy_count++;
    } else {
        $regular_count++;
    }
}

echo str_repeat('-', 60) . "\n";
echo "Total Profiles: " . $result->num_rows . " | OSY: $osy_count | Regular: $regular_count\n";

// Test Dashboard stats
echo "\n=== Dashboard Stats Verification ===\n";
echo "Total KK Members: " . ($osy_count + $regular_count) . "\n";
echo "Total OSY Members: $osy_count\n";
echo "Total Regular Members: $regular_count\n";

// Check for active OSY
$active_osy = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='OSY' AND status='Active'")->fetch_assoc();
echo "Active OSY: " . $active_osy['count'] . "\n";

// Check for employed OSY
$employed_osy = $conn->query("SELECT COUNT(*) as count FROM osy_profiles WHERE profile_type='OSY' AND status='Employed'")->fetch_assoc();
echo "Employed OSY: " . $employed_osy['count'] . "\n";

// Check opportunities
$opps = $conn->query("SELECT COUNT(*) as count FROM opportunities")->fetch_assoc();
echo "Total Opportunities: " . $opps['count'] . "\n";

// Check matches
$matches = $conn->query("SELECT COUNT(*) as count FROM osy_matches")->fetch_assoc();
echo "Total Matches: " . $matches['count'] . "\n";

$conn->close();
