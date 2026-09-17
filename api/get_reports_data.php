<?php
/**
 * API: Reports Data
 * Serves analytics and statistics for reports.php (LYDO only).
 * Wraps the existing report_stats.php logic with caching.
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn() || ($_SESSION['role'] ?? '') !== 'lydo') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$cache = new Cache(120); // 2 minute cache — reports are heavy but don't need realtime

require_once __DIR__ . '/../Classes/Report.php';
$report = new Report($database);

try {
    $filters = [
        'barangay'            => $_GET['barangay']            ?? '',
        'gender'              => $_GET['gender']              ?? '',
        'profile_type'        => $_GET['profile_type']        ?? '',
        'education'           => $_GET['education']           ?? '',
        'status'              => $_GET['status']              ?? '',
        'verification_status' => $_GET['verification_status'] ?? '',
        'start_date'          => $_GET['start_date']          ?? '',
        'end_date'            => $_GET['end_date']            ?? '',
    ];

    $cacheKey = 'reports_' . md5(serialize($filters));

    $data = $cache->remember($cacheKey, function () use ($report, $filters) {
        return [
            'osy_report'       => $report->generateOSYReport($filters),
            'opportunity_report'=> $report->generateOpportunityReport($filters),
            'matching_stats'   => $report->generateMatchingStats($filters),
            'monthly_activity' => $report->generateMonthlyActivityReport(),
        ];
    }, 120);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error generating report data']);
}
