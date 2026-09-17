<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/api/get_system_data.php');

$registryCode = <<<'PHP'
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
PHP;

$c = str_replace("echo json_encode(['success' => false, 'message' => 'Unknown view']);", $registryCode . "\n\n    echo json_encode(['success' => false, 'message' => 'Unknown view']);", $c);

file_put_contents('c:/xampp/htdocs/osy_db/api/get_system_data.php', $c);
echo "Added member_registry to api\n";
