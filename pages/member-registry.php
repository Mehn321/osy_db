<?php
$pageTitle = 'Member Registry';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('lydo');

// LYDO may only update a member's account status.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['user_id'])) {
    if (!validateFormNonce($_POST['form_nonce'] ?? '')) {
        die('Invalid CSRF token');
    }
    $userId = (int)$_POST['user_id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['Active', 'Inactive'], true)) die('Invalid member status');
    $database->query("UPDATE users SET status = ? WHERE id = ? AND role != 'lydo'", [$status, $userId], 'si');
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$roleFilter = $_GET['role'] ?? 'All';
$statusFilter = $_GET['status'] ?? 'All';
$searchFilter = trim($_GET['search'] ?? '');
$areaFilter = trim($_GET['area'] ?? '');
$roles = ['All', 'sk_chairman', 'employer', 'training_provider', 'youth'];
$statuses = ['All', 'Active', 'Inactive'];
if (!in_array($roleFilter, $roles, true)) $roleFilter = 'All';
if (!in_array($statusFilter, $statuses, true)) $statusFilter = 'All';

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

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$members = $database->fetchAll($query, $params, $types);

$countQuery = "SELECT COUNT(*) as total FROM users WHERE role != 'lydo'";
$countParams = [];
$countTypes = '';
if ($roleFilter !== 'All') {
    $countQuery .= " AND role = ?";
    $countParams[] = $roleFilter;
    $countTypes .= 's';
}
if ($statusFilter !== 'All') {
    $countQuery .= " AND status = ?";
    $countParams[] = $statusFilter;
    $countTypes .= 's';
}
if ($searchFilter !== '') {
    $countQuery .= " AND (fullname LIKE ? OR email LIKE ?)";
    $countParams[] = $term;
    $countParams[] = $term;
    $countTypes .= 'ss';
}
if ($areaFilter !== '') {
    $countQuery .= " AND (role = 'employer' OR barangay LIKE ? OR provider_type LIKE ?)";
    $countParams[] = $termArea;
    $countParams[] = $termArea;
    $countTypes .= 'ss';
}
$countRes = $database->fetchOne($countQuery, $countParams, $countTypes);
$totalMembers = $countRes['total'] ?? 0;
$totalPages = ceil($totalMembers / $limit);
?>

<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Member Account Registry</h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Manage all stakeholder accounts including SK Chairmen, Employers, and Training Providers.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="manage-sk-chairmen.php" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50">Manage SK</a>
            <a href="provider-approvals.php" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50">Provider Approvals</a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="mb-8 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end">
        <div class="xl:col-span-2"><label for="member-search" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Search member</label><input id="member-search" type="search" name="search" value="<?php echo htmlspecialchars($searchFilter); ?>" placeholder="Name or email" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"></div>
        <div><label for="member-role" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Stakeholder</label><select id="member-role" name="role" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"><?php foreach ($roles as $role): ?><option value="<?php echo htmlspecialchars($role); ?>" <?php echo $roleFilter === $role ? 'selected' : ''; ?>><?php echo $role === 'All' ? 'All stakeholders' : ucfirst(str_replace('_', ' ', $role)); ?></option><?php endforeach; ?></select></div>
        <div><label for="member-status" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Status</label><select id="member-status" name="status" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"><?php foreach ($statuses as $status): ?><option value="<?php echo htmlspecialchars($status); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>><?php echo $status === 'All' ? 'All statuses' : $status; ?></option><?php endforeach; ?></select></div>
        <div><label for="member-area" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Assigned area</label><input id="member-area" type="search" name="area" value="<?php echo htmlspecialchars($areaFilter); ?>" placeholder="Barangay or provider type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"></div>
        <div class="md:col-span-2 xl:col-span-5 flex gap-2 justify-end"><a href="member-registry.php" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-bold">Reset</a><button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-bold">Apply filters</button></div>
    </form>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto thin-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stakeholder</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Area</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($member['fullname']); ?></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($member['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                                    <?php echo str_replace('_', ' ', $member['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <?php echo htmlspecialchars($member['role'] === 'employer' ? 'All Barangays' : ($member['barangay'] ?? ($member['provider_type'] ?? 'General'))); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status = $member['status'] ?? 'Active';
                                $badge = $status === 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $badge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php $nextStatus = $status === 'Active' ? 'Inactive' : 'Active'; ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Change this member status to <?php echo $nextStatus; ?>?');">
                                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $nextStatus; ?>">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white <?php echo $nextStatus === 'Active' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'; ?> text-xs font-bold"><span class="material-symbols-outlined text-base"><?php echo $nextStatus === 'Active' ? 'check_circle' : 'block'; ?></span><?php echo $nextStatus === 'Active' ? 'Activate' : 'Deactivate'; ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-slate-500 italic">No members found matching the criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>