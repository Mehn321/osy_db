<?php
/**
 * API: Profiles Data
 * Serves paginated/filtered profile lists for:
 *   - profiles.php (LYDO — all profiles)
 *   - sk-barangay-youth.php (SK Chairman — barangay-scoped)
 *   - profile-detail.php (single profile by ID)
 *   - member-registry.php (LYDO)
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role     = $_SESSION['role'] ?? '';
$barangay = $_SESSION['barangay'] ?? '';
$cache    = new Cache(60);
$osyProfile = new OSYProfile($database);

try {
    // ── Single profile detail ──────────────────────────────────────────────
    if (isset($_GET['profile_id'])) {
        $id = (int)$_GET['profile_id'];
        $profile = $osyProfile->getById($id);
        if (!$profile) {
            echo json_encode(['success' => false, 'message' => 'Profile not found']);
            exit;
        }
        echo json_encode(['success' => true, 'profile' => $profile]);
        exit;
    }

    // ── Filtered list ──────────────────────────────────────────────────────
    $filters = [
        'profile_type' => $_GET['profile_type'] ?? 'All Types',
        'barangay'     => $_GET['barangay']      ?? 'All Barangays',
        'gender'       => $_GET['gender']        ?? 'All Genders',
        'education'    => $_GET['education']     ?? 'Any Level',
        'status'       => $_GET['status']        ?? 'All Status',
        'search'       => $_GET['search']        ?? '',
        'verification_status' => $_GET['verification_status'] ?? '',
    ];

    // SK chairman is always scoped to their barangay
    if ($role === 'sk_chairman') {
        $filters['barangay'] = $barangay;
    }

    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = min(100, max(10, (int)($_GET['limit'] ?? 50)));
    $offset = ($page - 1) * $limit;

    // Build a deterministic cache key from filters + pagination
    $cacheKey = 'profiles_' . md5($role . $barangay . serialize($filters) . $page . $limit);

    $result = $cache->remember($cacheKey, function () use ($osyProfile, $filters, $limit, $offset) {
        $total    = $osyProfile->getFilteredCount($filters);
        $profiles = $osyProfile->getAll($filters, $limit, $offset);
        return ['total' => $total, 'profiles' => $profiles];
    }, 60);

    echo json_encode([
        'success'    => true,
        'page'       => $page,
        'limit'      => $limit,
        'total'      => $result['total'],
        'totalPages' => (int)ceil($result['total'] / $limit),
        'profiles'   => $result['profiles'],
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching profiles']);
}
