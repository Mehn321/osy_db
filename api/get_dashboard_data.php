<?php
/**
 * API: Dashboard Data
 * Serves statistics for all dashboard roles: lydo, sk_chairman, provider.
 * Uses file cache (60s TTL) to avoid re-running heavy aggregation queries.
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';
require_once __DIR__ . '/../Classes/Dashboard.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role     = $_SESSION['role'] ?? '';
$userId   = $_SESSION['user_id'] ?? 0;

session_write_close(); // Release session lock for parallel AJAX requests

$barangay = $_SESSION['barangay'] ?? '';

$cache     = new Cache(60);
$dashboard = new Dashboard($database);

try {
    if ($role === 'lydo') {
        $cacheKey = 'dashboard_lydo';
        $data = $cache->remember($cacheKey, function () use ($dashboard) {
            return [
                'stats'                      => $dashboard->getStats(),
                'skill_distribution'         => $dashboard->getSkillDistribution(),
                'status_distribution'        => $dashboard->getStatusDistribution(),
                'recent_registrations'       => $dashboard->getRecentRegistrations(5),
                'recent_opportunities'       => $dashboard->getRecentOpportunities(5),
                'top_matched_osy'            => $dashboard->getTopMatchedOSY(5),
                'opportunities_needing_match'=> $dashboard->getOpportunitiesNeedingMatches(5),
            ];
        }, 60);

    } elseif ($role === 'sk_chairman') {
        $cacheKey = 'dashboard_sk_' . md5($barangay);
        $data = $cache->remember($cacheKey, function () use ($dashboard, $barangay) {
            return [
                'stats'                => $dashboard->getSKStats($barangay),
                'recent_registrations' => $dashboard->getRecentRegistrationsByBarangay($barangay, 5),
            ];
        }, 60);

    } elseif (in_array($role, ['employer', 'training_provider'])) {
        $cacheKey = 'dashboard_provider_' . $userId;
        $data = $cache->remember($cacheKey, function () use ($dashboard, $userId) {
            return [
                'stats'               => $dashboard->getProviderStats($userId),
                'recent_opportunities'=> $dashboard->getRecentOpportunitiesByProvider($userId, 5),
            ];
        }, 60);

    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized role']);
        exit;
    }

    echo json_encode(['success' => true, 'role' => $role, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching dashboard data']);
}
