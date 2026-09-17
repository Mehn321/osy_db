<?php
/**
 * API: Matching Data
 * Serves match lists and application data for:
 *   - matching.php (LYDO — all matches)
 *   - opportunity-applications.php (Employer/Provider — matches for their opportunities)
 *   - my-profile.php (Youth — their own matches)
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role   = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

session_write_close(); // Release session lock for parallel AJAX requests

$cache  = new Cache(30);

require_once __DIR__ . '/../Classes/Matching.php';
$matching    = new Matching($database);
$opportunity = new Opportunity($database);

try {
    // Matches for a specific opportunity (Employer/Provider view)
    if (isset($_GET['opportunity_id'])) {
        $oppId  = (int)$_GET['opportunity_id'];
        $limit  = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $cacheKey = 'matches_opp_' . $oppId . '_' . $limit . '_' . $offset;
        $data = $cache->remember($cacheKey, function () use ($matching, $oppId, $limit, $offset) {
            return [
                'matches' => $matching->getMatchesForOpportunity($oppId, 0, $limit, $offset),
                'total'   => $matching->countMatchesForOpportunity($oppId),
            ];
        }, 30);

        echo json_encode(['success' => true] + $data);
        exit;
    }

    // Matches for a specific OSY profile (Youth view)
    if (isset($_GET['osy_id'])) {
        $osyId    = (int)$_GET['osy_id'];
        $cacheKey = 'matches_osy_' . $osyId;
        $matches  = $cache->remember($cacheKey, fn() => $matching->getMatchesForOSY($osyId), 30);
        echo json_encode(['success' => true, 'matches' => $matches]);
        exit;
    }

    // LYDO global match overview
    if ($role === 'lydo') {
        $filters = [
            'status'         => $_GET['status']         ?? '',
            'opportunity_id' => $_GET['opportunity_id'] ?? '',
            'search'         => $_GET['search']         ?? '',
        ];
        $cacheKey = 'matches_global_' . md5(serialize($filters));

        $data = $cache->remember($cacheKey, function () use ($database, $filters) {
            $where  = ' WHERE 1=1';
            $params = [];
            $types  = '';

            if (!empty($filters['status'])) {
                $where   .= ' AND m.status = ?';
                $params[] = $filters['status'];
                $types   .= 's';
            }
            if (!empty($filters['opportunity_id'])) {
                $where   .= ' AND m.opportunity_id = ?';
                $params[] = (int)$filters['opportunity_id'];
                $types   .= 'i';
            }

            $sql = "SELECT m.*, 
                        CONCAT(p.first_name,' ',p.last_name) as youth_name,
                        p.primary_skill, p.barangay,
                        o.title as opportunity_title, o.type as opportunity_type
                    FROM osy_matches m
                    LEFT JOIN osy_profiles p ON p.id = m.osy_id
                    LEFT JOIN opportunities o ON o.id = m.opportunity_id
                    {$where}
                    ORDER BY m.match_score DESC
                    LIMIT 200";

            return !empty($params)
                ? $database->fetchAll($sql, $params, $types)
                : $database->fetchAll($sql);
        }, 30);

        echo json_encode(['success' => true, 'matches' => $data, 'total' => count($data)]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unauthorized role']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching matching data']);
}
