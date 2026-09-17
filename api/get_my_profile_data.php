<?php
/**
 * API: My Profile Data (Youth)
 * Serves the logged-in youth user's own profile for:
 *   - my-profile.php
 *   - edit-profile.php
 *   - create-profile.php
 *   - edit-member.php
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'] ?? 0;

session_write_close(); // Release session lock for parallel AJAX requests

$role   = $_SESSION['role'] ?? '';
$cache  = new Cache(30); // shorter TTL — profile edits should reflect quickly

$osyProfile = new OSYProfile($database);

try {
    // LYDO/SK can fetch any profile by ?profile_id=
    if (in_array($role, ['lydo', 'sk_chairman']) && isset($_GET['profile_id'])) {
        $id      = (int)$_GET['profile_id'];
        $profile = $osyProfile->getById($id);
    } else {
        // Youth fetches their own
        $cacheKey = 'my_profile_' . $userId;
        $profile  = $cache->remember($cacheKey, fn() => $osyProfile->getByUserId($userId), 30);
    }

    if (!$profile) {
        echo json_encode(['success' => false, 'message' => 'Profile not found', 'profile' => null]);
        exit;
    }

    // Fetch reference data needed to render edit forms
    require_once __DIR__ . '/../Classes/Reference.php';
    $ref = new Reference($database);

    $refData = $cache->remember('ref_data_all', function () use ($ref) {
        return [
            'barangays'   => $ref->getByCategory('barangay'),
            'edu_levels'  => $ref->getByCategory('education_level'),
            'govt_id_types' => $ref->getByCategory('govt_id_type'),
            'skills'      => $ref->getByCategory('skills'),
            'reasons'     => $ref->getByCategory('reason'),
        ];
    }, 300); // Reference data changes rarely — cache 5 minutes

    echo json_encode([
        'success' => true,
        'profile' => $profile,
        'refs'    => $refData,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching profile data']);
}
