<?php
/**
 * API: System Data
 * Serves administrative lists for:
 *   - manage-sk-chairmen.php (LYDO)
 *   - provider-approvals.php (LYDO)
 *   - verify-youth.php (SK Chairman)
 *   - provider-registration.php (Provider signup — reference data only)
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role     = $_SESSION['role'] ?? '';
$userId   = $_SESSION['user_id'] ?? 0;

session_write_close(); // Release session lock for parallel AJAX requests

$barangay = $_SESSION['barangay'] ?? '';
$cache    = new Cache(60);

try {
    $view = $_GET['view'] ?? '';

    // ── SK Chairmen list (LYDO only) ─────────────────────────────────────
    if ($view === 'sk_chairmen') {
        if ($role !== 'lydo') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }

        $cacheKey = 'sk_chairmen_list';
        $data = $cache->remember($cacheKey, function () use ($database) {
            return $database->fetchAll(
                "SELECT u.id, u.username, u.fullname, u.email, u.barangay, u.status, u.created_at
                 FROM users u WHERE u.role = 'sk_chairman'
                 ORDER BY u.barangay ASC"
            );
        }, 60);

        echo json_encode(['success' => true, 'sk_chairmen' => $data]);
        exit;
    }

    // ── Provider approvals (LYDO only) ───────────────────────────────────
    if ($view === 'provider_approvals') {
        if ($role !== 'lydo') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }

        $status   = $_GET['status'] ?? 'Pending';
        $cacheKey = 'provider_approvals_' . $status;

        $data = $cache->remember($cacheKey, function () use ($database, $status) {
            return $database->fetchAll(
                "SELECT u.id, u.username, u.fullname, u.email, u.role, u.status,
                        u.organization_name, u.created_at, u.document_path
                 FROM users u
                 WHERE u.role IN ('employer','training_provider')
                   AND (? = '' OR u.status = ?)
                 ORDER BY u.created_at DESC",
                [$status, $status],
                'ss'
            );
        }, 60);

        echo json_encode(['success' => true, 'providers' => $data]);
        exit;
    }

    // ── Verify youth (SK Chairman only) ──────────────────────────────────
    if ($view === 'verify_youth') {
        if ($role !== 'sk_chairman') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }

        $verStatus = $_GET['verification_status'] ?? 'Pending';
        $cacheKey  = 'verify_youth_' . md5($barangay . $verStatus);

        $osyProfile = new OSYProfile($database);
        $data = $cache->remember($cacheKey, function () use ($osyProfile, $barangay, $verStatus) {
            $filters = [
                'barangay'            => $barangay,
                'verification_status' => $verStatus,
                'profile_type'        => 'All Types',
                'gender'              => 'All Genders',
                'education'           => 'Any Level',
                'status'              => 'All Status',
                'search'              => '',
            ];
            return $osyProfile->getAll($filters, 100, 0);
        }, 30);

        echo json_encode(['success' => true, 'profiles' => $data]);
        exit;
    }

        // ── Member Registry (LYDO only) ───────────────────────────────────
    if ($view === 'member_registry') {
        if ($role !== 'lydo') { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $roleFilter = $_GET['role'] ?? 'All';
        $statusFilter = $_GET['status'] ?? 'All';
        $searchFilter = trim($_GET['search'] ?? '');
        $areaFilter = trim($_GET['area'] ?? '');

        $query = "SELECT * FROM users WHERE role != 'lydo'";
        $params = [];
        $types = "";

        if ($roleFilter !== 'All') {
            $query .= " AND role = ?";
            $params[] = $roleFilter;
            $types .= "s";
        }
        if ($statusFilter !== 'All') {
            $query .= " AND status = ?";
            $params[] = $statusFilter;
            $types .= 's';
        }
        if ($searchFilter !== '') {
            $query .= " AND (fullname LIKE ? OR email LIKE ?)";
            $term = '%' . $searchFilter . '%';
            $params[] = $term;
            $params[] = $term;
            $types .= 'ss';
        }
        if ($areaFilter !== '') {
            $query .= " AND (role = 'employer' OR barangay LIKE ? OR provider_type LIKE ?)";
            $termArea = '%' . $areaFilter . '%';
            $params[] = $termArea;
            $params[] = $termArea;
            $types .= 'ss';
        }

        // Count total
        $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
        if (empty($params)) {
            $countRes = $database->fetchOne($countQuery);
        } else {
            $countRes = $database->fetchOne($countQuery, $params, $types);
        }
        $totalMembers = $countRes['total'] ?? 0;
        $totalPages = ceil($totalMembers / $limit);

        // Fetch data
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $members = $database->fetchAll($query, $params, $types);
        
        echo json_encode([
            'success' => true,
            'members' => $members,
            'total' => $totalMembers,
            'totalPages' => $totalPages,
            'page' => $page
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown view']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching system data']);
}
