<?php
$pageTitle = 'My Training Programs';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['training_provider', 'lydo']);

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

require_once __DIR__ . '/../includes/header.php';

// Fetch only "Vocational Training" or "Scholarship" type opportunities created by this user
$query = "SELECT * FROM opportunities WHERE created_by = ? AND type IN ('Vocational Training', 'Scholarship') ORDER BY created_at DESC";
$programs = $database->fetchAll($query, [$userId]);

$totalPrograms = count($programs);
$activePrograms = 0;
foreach ($programs as $p) if ($p['status'] === 'Open') $activePrograms++;
?>

<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">My Training Programs</h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Manage your educational and vocational training offerings.</p>
        </div>
        <a href="training-programs.php?create=1" class="inline-flex items-center gap-2 bg-indigo-900 dark:bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-800 transition-all shadow-lg shadow-indigo-900/20">
            <span class="material-symbols-outlined">school</span> Add New Program
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-indigo-100 dark:bg-indigo-900/30 rounded-2xl text-indigo-900 dark:text-indigo-200">
            <span class="material-symbols-outlined text-3xl">auto_stories</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Programs</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $totalPrograms; ?></h3>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl text-emerald-900 dark:text-emerald-200">
            <span class="material-symbols-outlined text-3xl">verified</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Enrollment</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $activePrograms; ?></h3>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Program Title</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Slots</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Applicants</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($programs)): ?>
                    <?php foreach ($programs as $prog):
                        $appCountRes = $database->fetchOne("SELECT COUNT(*) as cnt FROM osy_matches WHERE opportunity_id = ?", [$prog['id']]);
                        $appCount = $appCountRes['cnt'] ?? 0;
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($prog['title']); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Deadline: <?php echo $prog['deadline'] ? date('M d, Y', strtotime($prog['deadline'])) : 'Open'; ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($prog['type']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-bold"><?php echo $prog['total_slots']; ?></span> Slots
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-indigo-900 dark:text-indigo-400"><?php echo $appCount; ?> Enrolled</span>
                                    <div class="w-24 h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                        <?php $fill = $prog['total_slots'] > 0 ? ($appCount / $prog['total_slots']) * 100 : 0; ?>
                                        <div class="h-full bg-indigo-500" style="width: <?php echo min(100, $fill); ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="matching.php?opportunity_id=<?php echo $prog['id']; ?>" class="p-2 text-slate-400 hover:text-indigo-900 transition-colors" title="View Applicants">
                                        <span class="material-symbols-outlined text-xl">group</span>
                                    </a>
                                    <a href="training-programs.php?edit_id=<?php echo $prog['id']; ?>" class="p-2 text-slate-400 hover:text-indigo-900 transition-colors" title="Edit Program">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-slate-500">
                            <span class="material-symbols-outlined text-5xl opacity-20 mb-4 block">school</span>
                            <p class="text-lg font-medium">You haven't posted any training programs yet.</p>
                            <a href="training-programs.php?create=1" class="text-indigo-900 font-bold hover:underline mt-2 inline-block">Post your first program</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>