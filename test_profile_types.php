<?php
include 'config/database.php';

// Check profile data
$result = $conn->query('SELECT id, CONCAT(first_name, " ", last_name) as name, profile_type, status FROM osy_profiles ORDER BY id');

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
