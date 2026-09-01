<?php

/**
 * Debug script to check youth approval status
 * Run this to see the current state of a youth's approval in the database
 */

require_once __DIR__ . '/init.php';

// This script runs without login required for debugging
$debugMode = true;

echo "<h2>Youth Approval Status Debug</h2>\n";

// Show all youth with their verification and approval status
$query = "
SELECT 
    u.id as user_id,
    u.status as user_status,
    u.username,
    u.fullname,
    u.role,
    o.id as profile_id,
    o.first_name,
    o.last_name,
    o.barangay,
    o.verification_status,
    o.registration_status,
    o.created_by,
    o.created_at
FROM users u
LEFT JOIN osy_profiles o ON u.id = o.created_by
WHERE u.role = 'youth'
ORDER BY o.created_at DESC
LIMIT 20
";

try {
    $results = $database->fetchAll($query);

    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    echo "<th>User ID</th>";
    echo "<th>Username</th>";
    echo "<th>Fullname</th>";
    echo "<th>User Status</th>";
    echo "<th>Profile ID</th>";
    echo "<th>Profile Name</th>";
    echo "<th>Barangay</th>";
    echo "<th>Verification Status</th>";
    echo "<th>Registration Status</th>";
    echo "<th>Created At</th>";
    echo "</tr>";

    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_id'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['username'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname'] ?? '-') . "</td>";
        echo "<td style='background: " . ($row['user_status'] === 'Active' ? '#90EE90' : '#FFB6C6') . "'>" . htmlspecialchars($row['user_status'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['profile_id'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) . "</td>";
        echo "<td>" . htmlspecialchars($row['barangay'] ?? '-') . "</td>";
        echo "<td style='background: " . ($row['verification_status'] === 'Verified' ? '#90EE90' : ($row['verification_status'] === 'Action Required' ? '#FFD700' : '#FFB6C6')) . "'>" . htmlspecialchars($row['verification_status'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['registration_status'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<br><br>";
    echo "<strong>Legend:</strong><br>";
    echo "- User Status: Green = Active (can login), Red = Pending (cannot login)<br>";
    echo "- Verification Status: Green = Verified (approved), Yellow = Action Required (needs fixing), Red = Pending (awaiting approval)<br>";
    echo "<br>";
    echo "For youth to login successfully:<br>";
    echo "1. users.status must be 'Active'<br>";
    echo "2. osy_profiles.verification_status must be 'Verified'<br>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
