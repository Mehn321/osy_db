<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn() || ($_SESSION['role'] ?? '') !== 'lydo') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$where = " WHERE 1=1";
$params = [];
$types = "";

if (!empty($startDate)) {
    $where .= " AND created_at >= ?";
    $params[] = $startDate . ' 00:00:00';
    $types .= "s";
}
if (!empty($endDate)) {
    $where .= " AND created_at <= ?";
    $params[] = $endDate . ' 23:59:59';
    $types .= "s";
}

try {
    // 1. Profile Types
    $queryTypes = "SELECT COALESCE(NULLIF(TRIM(profile_type), ''), 'Unspecified') as type, COUNT(*) as count FROM osy_profiles {$where} GROUP BY type";
    if (!empty($params)) {
        $profileTypes = $database->fetchAll($queryTypes, $params, $types);
    } else {
        $profileTypes = $database->fetchAll($queryTypes);
    }

    // 2. Employment Status
    $queryStatus = "SELECT COALESCE(NULLIF(TRIM(status), ''), 'Unspecified') as status, COUNT(*) as count FROM osy_profiles {$where} GROUP BY status";
    if (!empty($params)) {
        $employmentStatus = $database->fetchAll($queryStatus, $params, $types);
    } else {
        $employmentStatus = $database->fetchAll($queryStatus);
    }

    // 3. Monthly registrations (last 12 months, or filtered range)
    $queryMonthly = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM osy_profiles {$where} GROUP BY month ORDER BY month ASC";
    if (!empty($params)) {
        $monthlyRegistrations = $database->fetchAll($queryMonthly, $params, $types);
    } else {
        $monthlyRegistrations = $database->fetchAll($queryMonthly);
    }

    echo json_encode([
        'success' => true,
        'profile_types' => $profileTypes,
        'employment_status' => $employmentStatus,
        'monthly_registrations' => $monthlyRegistrations
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
