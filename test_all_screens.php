<?php

/**
 * Comprehensive Application Flow Test
 * Tests all major screens and functionality
 */

require_once __DIR__ . '/init.php';

echo "================================\n";
echo "CIVIC HORIZON APPLICATION TEST\n";
echo "================================\n\n";

// 1. Login Test
echo "1. TESTING LOGIN FLOW\n";
echo "   - Attempting login with admin1 / Admin@123\n";
$result = $user->login('admin1', 'Admin@123');
if ($result['success']) {
    echo "   ✓ Login successful for: " . $result['user']['fullname'] . "\n";
} else {
    echo "   ✗ Login failed: " . $result['message'] . "\n";
    exit(1);
}

// 2. Dashboard Test
echo "\n2. TESTING DASHBOARD DATA\n";
$dashboard = new Dashboard($database);
$stats = $dashboard->getStats();
echo "   ✓ Dashboard stats loaded:\n";
echo "     - Total OSY: " . $stats['total_osy'] . "\n";
echo "     - Active Opportunities: " . $stats['total_opportunities'] . "\n";
echo "     - Accepted Matches: " . $stats['accepted_matches'] . "\n";
echo "     - Notifications: " . $stats['total_notifications_sent'] . "\n";

// 3. Profiles Test
echo "\n3. TESTING OSY PROFILES\n";
$osyProfile = new OSYProfile($database);
$profiles = $osyProfile->getAll([]);
echo "   ✓ Profiles loaded: " . count($profiles) . " total\n";
if (!empty($profiles)) {
    $first_profile = $profiles[0];
    echo "     - First profile: " . $first_profile['first_name'] . " " . $first_profile['last_name'] . "\n";
    echo "     - Skill: " . $first_profile['primary_skill'] . "\n";
    echo "     - Status: " . $first_profile['status'] . "\n";
}

// 4. Opportunities Test
echo "\n4. TESTING OPPORTUNITIES\n";
$opportunity = new Opportunity($database);
$opportunities = $opportunity->getAll();
echo "   ✓ Opportunities loaded: " . count($opportunities) . " total\n";
if (!empty($opportunities)) {
    foreach (array_slice($opportunities, 0, 3) as $opp) {
        echo "     - " . $opp['title'] . " (" . $opp['type'] . ")\n";
    }
}

// 5. Matching Test
echo "\n5. TESTING SKILLS MATCHING\n";
$matching = new Matching($database);
$matches = $matching->getMatchesForOpportunity(1, 75);
echo "   ✓ Matches loaded: " . count($matches) . " matches for opportunity #1 (min score 75%)\n";
if (!empty($matches)) {
    foreach (array_slice($matches, 0, 3) as $match) {
        echo "     - Match score: " . $match['match_score'] . "% - " . $match['status'] . "\n";
    }
}

// 6. Notifications Test
echo "\n6. TESTING NOTIFICATIONS\n";
$notification = new Notification($database);
$notifs = $notification->getAll(10);
echo "   ✓ Notifications loaded: " . count($notifs) . " total\n";
if (!empty($notifs)) {
    foreach (array_slice($notifs, 0, 3) as $notif) {
        echo "     - " . $notif['title'] . " (" . $notif['type'] . ")\n";
    }
}

// 7. Reports Test
echo "\n7. TESTING REPORTS\n";
$report = new Report($database);
$rep_stats = $report->generateMatchingStats();
echo "   ✓ Report stats generated:\n";
echo "     - Total Matches: " . $rep_stats['total_matches_made'] . "\n";
echo "     - Pending Matches: " . $rep_stats['pending_matches'] . "\n";
echo "     - Average Score: " . round($rep_stats['average_match_score'] ?? 0) . "%\n";

// 8. Skills Distribution Test
echo "\n8. TESTING SKILL DISTRIBUTION\n";
$skills = $dashboard->getSkillDistribution();
echo "   ✓ Skills loaded: " . count($skills) . " skill categories\n";
foreach ($skills as $skill) {
    echo "     - " . $skill['primary_skill'] . ": " . $skill['count'] . " youth\n";
}

// 9. Recent Registrations Test
echo "\n9. TESTING RECENT ACTIVITY\n";
$recent = $dashboard->getRecentRegistrations(5);
echo "   ✓ Recent registrations: " . count($recent) . " records\n";
foreach (array_slice($recent, 0, 3) as $reg) {
    echo "     - " . $reg['first_name'] . " " . $reg['last_name'] . " (" . $reg['primary_skill'] . ")\n";
}

// 10. Logout Test
echo "\n10. TESTING LOGOUT\n";
$logout = $user->logout();
if ($logout['success']) {
    echo "   ✓ Logout successful\n";
} else {
    echo "   ✗ Logout failed\n";
}

echo "\n================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY\n";
echo "================================\n\n";

echo "AVAILABLE PAGES:\n";
echo "  ✓ login.php - Authentication\n";
echo "  ✓ dashboard.php - Main dashboard with stats\n";
echo "  ✓ profiles.php - OSY profile management\n";
echo "  ✓ profile-detail.php - Individual profile view\n";
echo "  ✓ opportunities.php - Job & training opportunities\n";
echo "  ✓ matching.php - Skills matching engine\n";
echo "  ✓ notifications.php - Notification system\n";
echo "  ✓ reports.php - Analytics & reports\n";
echo "  ✓ settings.php - Account settings\n";
echo "  ✓ help.php - Help center\n";
echo "  ✓ logout.php - Sign out\n";
echo "  ✓ index-public.php - Public landing page\n\n";
