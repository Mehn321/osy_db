<?php
/**
 * Comprehensive Youth Approval Diagnostic
 * Shows the complete status of youth registrations and their linking to user accounts
 */

require_once __DIR__ . '/init.php';

$database = new Database();

echo "<h1>Youth Approval Status Diagnostic Report</h1>";
echo "<hr>";

// ============================================================================
// 1. PENDING APPROVALS - Youth waiting for SK approval
// ============================================================================
echo "<h2>1. PENDING APPROVALS (Awaiting SK Chairman Action)</h2>";
$pending = $database->fetchAll(
    "SELECT o.id as profile_id, o.first_name, o.last_name, o.barangay, o.created_by, 
            u.id as user_id, u.username, u.status as user_status, u.role,
            o.verification_status, o.created_at
     FROM osy_profiles o
     LEFT JOIN users u ON o.created_by = u.id
     WHERE o.verification_status = 'Pending'
     ORDER BY o.created_at DESC"
);

if (empty($pending)) {
    echo "<p style='color: green;'><strong>✅ No pending youth - all approvals are current!</strong></p>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Profile ID</th>";
    echo "<th>Youth Name</th>";
    echo "<th>Barangay</th>";
    echo "<th>User ID</th>";
    echo "<th>Username</th>";
    echo "<th>User Status</th>";
    echo "<th>Created By</th>";
    echo "<th>Registered</th>";
    echo "</tr>";
    
    foreach ($pending as $row) {
        $issue = '';
        if (empty($row['user_id'])) {
            $issue = " ❌ NO USER ACCOUNT FOUND";
        } elseif (empty($row['created_by'])) {
            $issue = " ❌ created_by is NULL";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['profile_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . $issue . "</td>";
        echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['username'] ?? 'NULL') . "</td>";
        echo "<td style='background: #FFB6C6; font-weight: bold;'>" . htmlspecialchars($row['user_status'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['created_by'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['created_at'], 0, 10)) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// ============================================================================
// 2. APPROVED YOUTH - Should have verification_status='Verified' AND users.status='Active'
// ============================================================================
echo "<h2>2. APPROVED YOUTH (verification_status = 'Verified')</h2>";
$approved = $database->fetchAll(
    "SELECT o.id as profile_id, o.first_name, o.last_name, o.barangay, o.created_by, 
            u.id as user_id, u.username, u.status as user_status, u.role,
            o.verification_status, o.created_at
     FROM osy_profiles o
     LEFT JOIN users u ON o.created_by = u.id
     WHERE o.verification_status = 'Verified'
     ORDER BY o.created_at DESC
     LIMIT 20"
);

if (empty($approved)) {
    echo "<p style='color: orange;'><strong>⚠️ No approved youth found</strong></p>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Profile ID</th>";
    echo "<th>Youth Name</th>";
    echo "<th>Barangay</th>";
    echo "<th>User ID</th>";
    echo "<th>Username</th>";
    echo "<th>User Status</th>";
    echo "<th>Can Login?</th>";
    echo "<th>Created By</th>";
    echo "</tr>";
    
    foreach ($approved as $row) {
        $canLogin = ($row['user_status'] === 'Active') ? '✅ YES' : '❌ NO (' . $row['user_status'] . ')';
        $bgColor = ($row['user_status'] === 'Active') ? '#C0FFC0' : '#FFD700';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['profile_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['barangay']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['username'] ?? 'NULL') . "</td>";
        echo "<td style='background: {$bgColor}; font-weight: bold;'>" . htmlspecialchars($row['user_status'] ?? 'NULL') . "</td>";
        echo "<td style='background: {$bgColor}; font-weight: bold;'>" . $canLogin . "</td>";
        echo "<td>" . htmlspecialchars($row['created_by'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br>";
    echo "<strong>Summary:</strong>";
    $activeCount = 0;
    foreach ($approved as $row) {
        if ($row['user_status'] === 'Active') $activeCount++;
    }
    $notActiveCount = count($approved) - $activeCount;
    
    echo "<ul>";
    echo "<li>Total Approved Youth: " . count($approved) . "</li>";
    echo "<li style='color: green;'>✅ Can Login (status='Active'): " . $activeCount . "</li>";
    if ($notActiveCount > 0) {
        echo "<li style='color: red;'>❌ CANNOT LOGIN (status!='Active'): " . $notActiveCount . " <strong>⚠️ BUG!</strong></li>";
    }
    echo "</ul>";
}

echo "<hr>";

// ============================================================================
// 3. ACTION REQUIRED YOUTH - Profile needs correction
// ============================================================================
echo "<h2>3. ACTION REQUIRED YOUTH (verification_status = 'Action Required')</h2>";
$actionRequired = $database->fetchAll(
    "SELECT o.id as profile_id, o.first_name, o.last_name, o.barangay, o.created_by, 
            u.id as user_id, u.username, u.status as user_status, u.role,
            o.verification_status, o.verification_remark, o.created_at
     FROM osy_profiles o
     LEFT JOIN users u ON o.created_by = u.id
     WHERE o.verification_status = 'Action Required'
     ORDER BY o.created_at DESC"
);

if (empty($actionRequired)) {
    echo "<p style='color: green;'><strong>✅ No youth requiring action</strong></p>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Profile ID</th>";
    echo "<th>Youth Name</th>";
    echo "<th>User Status</th>";
    echo "<th>Remark</th>";
    echo "</tr>";
    
    foreach ($actionRequired as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['profile_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td style='background: #FFD700;'>" . htmlspecialchars($row['user_status'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['verification_remark'] ?? 'None') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";

// ============================================================================
// 4. SUMMARY STATISTICS
// ============================================================================
echo "<h2>4. SUMMARY STATISTICS</h2>";

$stats = $database->fetchOne(
    "SELECT 
        (SELECT COUNT(*) FROM osy_profiles WHERE verification_status = 'Pending') as pending,
        (SELECT COUNT(*) FROM osy_profiles WHERE verification_status = 'Verified') as verified,
        (SELECT COUNT(*) FROM osy_profiles WHERE verification_status = 'Action Required') as action_required,
        (SELECT COUNT(*) FROM users WHERE role = 'youth' AND status = 'Active') as active_youth_users,
        (SELECT COUNT(*) FROM users WHERE role = 'youth' AND status = 'Pending') as pending_youth_users
     FROM users LIMIT 1"
);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><td><strong>Pending Youth Profiles:</strong></td><td>" . $stats['pending'] . "</td></tr>";
echo "<tr><td><strong>Verified Youth Profiles:</strong></td><td>" . $stats['verified'] . "</td></tr>";
echo "<tr><td><strong>Action Required Profiles:</strong></td><td>" . $stats['action_required'] . "</td></tr>";
echo "<tr><td><strong>Active Youth Users:</strong></td><td style='background: #C0FFC0;'>" . $stats['active_youth_users'] . "</td></tr>";
echo "<tr><td><strong>Pending Youth Users:</strong></td><td style='background: #FFB6C6;'>" . $stats['pending_youth_users'] . "</td></tr>";
echo "</table>";

echo "<hr>";

// ============================================================================
// 5. EXPECTED STATUS MAPPING
// ============================================================================
echo "<h2>5. EXPECTED STATUS MAPPING</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>Scenario</th>";
echo "<th>users.status Should Be</th>";
echo "<th>osy_profiles.verification_status Should Be</th>";
echo "<th>Youth Can Login?</th>";
echo "</tr>";
echo "<tr>";
echo "<td>Youth just registered</td>";
echo "<td style='background: #FFB6C6;'>Pending</td>";
echo "<td>Pending</td>";
echo "<td>❌ NO</td>";
echo "</tr>";
echo "<tr style='background: #C0FFC0;'>";
echo "<td>SK Approved Youth ✅</td>";
echo "<td style='background: #C0FFC0;'><strong>Active</strong></td>";
echo "<td><strong>Verified</strong></td>";
echo "<td>✅ YES</td>";
echo "</tr>";
echo "<tr>";
echo "<td>SK Rejected (Action Required)</td>";
echo "<td style='background: #FFB6C6;'>Pending</td>";
echo "<td>Action Required</td>";
echo "<td>❌ NO (needs to fix and resubmit)</td>";
echo "</tr>";
echo "</table>";

?>
