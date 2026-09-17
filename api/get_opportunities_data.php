<?php
/**
 * API: Opportunities Data
 * Serves opportunity lists for:
 *   - opportunities.php (LYDO — all)
 *   - job-openings.php (Youth — jobs only)
 *   - training-programs.php (Youth — training only)
 *   - my-job-openings.php (Employer — their own)
 *   - my-training-programs.php (Training Provider — their own)
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

$cache  = new Cache(60);
$opportunity = new Opportunity($database);

try {
    $filters = [
        'type'     => $_GET['type']     ?? '',
        'status'   => $_GET['status']   ?? 'Open',
        'search'   => $_GET['search']   ?? '',
        'location' => $_GET['location'] ?? '',
    ];

    // Single opportunity detail
    if (isset($_GET['opportunity_id'])) {
        $id   = (int)$_GET['opportunity_id'];
        $item = $opportunity->getById($id);
        echo json_encode(['success' => true, 'opportunity' => $item]);
        exit;
    }

    $cacheKey = 'opportunities_' . md5($role . $userId . serialize($filters));

    $data = $cache->remember($cacheKey, function () use ($opportunity, $role, $userId, $filters, $database) {
        if (in_array($role, ['employer', 'training_provider'])) {
            // Provider sees only their own postings
            return $opportunity->getByProvider($userId);
        } elseif ($role === 'youth') {
            // Youth sees public opportunities (filtered by type if set)
            $opportunities = $opportunity->getForYouth($filters);
            
            $profile = $database->fetchOne("SELECT id, age FROM osy_profiles WHERE created_by = ? LIMIT 1", [$userId], 'i');
            if ($profile) {
                // Fetch matches
                $youthMatches = [];
                $matches = $database->fetchAll("SELECT opportunity_id, status, match_score FROM osy_matches WHERE osy_id = ?", [$profile['id']], 'i');
                foreach ($matches as $m) {
                    $youthMatches[(int)$m['opportunity_id']] = $m;
                }
                
                require_once __DIR__ . '/../Classes/Matching.php';
                $matching = new Matching($database);

                // Process Match Scores & Application Statuses
                foreach ($opportunities as &$opp) {
                    $oppId = (int)$opp['id'];
                    $isJob = ($opp['type'] === 'Job Opening');
                    if (isset($youthMatches[$oppId])) {
                        $opp['application_status'] = $youthMatches[$oppId]['status'];
                        $opp['match_score'] = $isJob ? $youthMatches[$oppId]['match_score'] : null;
                    } else {
                        $opp['application_status'] = null;
                        $opp['match_score'] = $isJob ? $matching->calculateMatchScore($profile['id'], $oppId) : null;
                    }
                }
                unset($opp);

                // Filter by Age
                $opportunities = array_filter($opportunities, function ($opp) use ($profile) {
                    if (!empty($opp['age_min']) || !empty($opp['age_max'])) {
                        $age = intval($profile['age'] ?? 0);
                        $min = intval($opp['age_min'] ?? 0);
                        $max = intval($opp['age_max'] ?? 0);
                        if ($age > 0) {
                            if ($min > 0 && $age < $min) return false;
                            if ($max > 0 && $age > $max) return false;
                        }
                    }
                    return true;
                });
                
                // Sort by match score (highest first)
                usort($opportunities, function($a, $b) {
                    $scoreA = isset($a['match_score']) ? $a['match_score'] : -1;
                    $scoreB = isset($b['match_score']) ? $b['match_score'] : -1;
                    if ($scoreA !== $scoreB) {
                        return $scoreB <=> $scoreA;
                    }
                    return $b['id'] <=> $a['id'];
                });
                
                return array_values($opportunities);
            }
            return $opportunities;
        } else {
            // LYDO sees everything
            return $opportunity->getAll($filters);
        }
    }, 60);

    echo json_encode(['success' => true, 'opportunities' => $data, 'total' => count($data)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching opportunities']);
}
