<?php
$pageTitle = 'My Barangay Youth Registry';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('sk_chairman');

$userBarangay = $_SESSION['barangay'] ?? '';
if (!$userBarangay) {
    die("Error: No barangay assigned to your account.");
}

require_once __DIR__ . '/../includes/header.php';

$osyProfile = new OSYProfile($database);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch youth in this barangay
$query = "SELECT * FROM osy_profiles WHERE barangay = ? ORDER BY last_name, first_name LIMIT ? OFFSET ?";
$youth = $database->fetchAll($query, [$userBarangay, $limit, $offset], "sii");

// Total count for pagination
$countRes = $database->fetchOne("SELECT COUNT(*) as total FROM osy_profiles WHERE barangay = ?", [$userBarangay]);
$totalYouth = $countRes['total'] ?? 0;
$totalPages = ceil($totalYouth / $limit);
?>

<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Barangay Registry: <?php echo htmlspecialchars($userBarangay); ?></h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium">A complete list of all registered youth in your jurisdiction.</p>
        </div>
        <div class="flex items-center gap-3">
             <a href="create-profile.php?type=OSY" class="bg-blue-900 text-white px-4 py-2 rounded-xl border border-blue-800 flex items-center gap-2 hover:bg-blue-800 transition-all shadow-md">
                <span class="material-symbols-outlined">person_add</span>
                <span class="text-sm font-bold">Register Youth</span>
             </a>
             <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-200 px-4 py-2 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-2">
                <span class="material-symbols-outlined">groups</span>
                <span class="font-black text-xl"><?php echo $totalYouth; ?></span>
                <span class="text-sm font-bold opacity-70">Total Members</span>
             </div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Skill/Interest</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Verification</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($youth)): ?>
                    <?php foreach ($youth as $person): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($person['last_name'] . ', ' . $person['first_name']); ?></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo $person['age']; ?> years old • <?php echo htmlspecialchars($person['gender']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest <?php echo $person['profile_type'] === 'OSY' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <?php echo $person['profile_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <p class="text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($person['email'] ?? 'No email'); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($person['phone'] ?? 'No phone'); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <?php echo htmlspecialchars($person['primary_skill'] ?? 'None specified'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    $vStatus = $person['verification_status'] ?? 'Drafting';
                                    $badgeClass = 'bg-slate-100 text-slate-600';
                                    if ($vStatus === 'Verified') $badgeClass = 'bg-emerald-100 text-emerald-700';
                                    elseif ($vStatus === 'Pending') $badgeClass = 'bg-amber-100 text-amber-700';
                                    elseif ($vStatus === 'Action Required') $badgeClass = 'bg-rose-100 text-rose-700';
                                ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold <?php echo $badgeClass; ?>">
                                    <span class="material-symbols-outlined text-xs">
                                        <?php 
                                            if ($vStatus === 'Verified') echo 'verified';
                                            elseif ($vStatus === 'Pending') echo 'hourglass_empty';
                                            else echo 'info';
                                        ?>
                                    </span>
                                    <?php echo $vStatus; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="profile-detail.php?id=<?php echo $person['id']; ?>" class="text-blue-900 dark:text-blue-400 font-bold text-sm hover:underline flex items-center justify-end gap-1">
                                    Full Details <span class="material-symbols-outlined text-xs">open_in_new</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center text-slate-500">
                            <span class="material-symbols-outlined text-5xl opacity-20 mb-4 block">groups_3</span>
                            <p class="text-lg font-medium">No registered youth found in Barangay <?php echo htmlspecialchars($userBarangay); ?>.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex justify-center">
        <nav class="flex gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg font-bold transition-all <?php echo $i === $page ? 'bg-blue-900 text-white shadow-lg shadow-blue-900/30' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
