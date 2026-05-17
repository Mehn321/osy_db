<?php
$pageTitle = 'Member Registry';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('lydo');

require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$roleFilter = $_GET['role'] ?? 'All';

$query = "SELECT * FROM users WHERE role != 'lydo'";
$params = [];
$types = "";

if ($roleFilter !== 'All') {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$members = $database->fetchAll($query, $params, $types);

$countQuery = "SELECT COUNT(*) as total FROM users WHERE role != 'lydo'";
if ($roleFilter !== 'All') {
    $countQuery .= " AND role = '" . $database->escape($roleFilter) . "'";
}
$countRes = $database->fetchOne($countQuery);
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
<div class="mb-8">
    <div class="flex flex-wrap gap-2">
        <?php $roles = ['All', 'sk_chairman', 'employer', 'training_provider', 'youth']; ?>
        <?php foreach ($roles as $r): ?>
            <a href="?role=<?php echo $r; ?>" class="px-4 py-2 rounded-xl text-sm font-bold transition-all <?php echo $roleFilter === $r ? 'bg-blue-900 text-white shadow-lg shadow-blue-900/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'; ?>">
                <?php echo $r === 'All' ? 'All Stakeholders' : ucfirst(str_replace('_', ' ', $r)); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
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
                                <?php echo htmlspecialchars($member['barangay'] ?? ($member['provider_type'] ?? 'General')); ?>
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
                                    <button class="p-2 text-slate-400 hover:text-blue-900"><span class="material-symbols-outlined text-xl">edit</span></button>
                                    <button class="p-2 text-slate-400 hover:text-rose-600"><span class="material-symbols-outlined text-xl">block</span></button>
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