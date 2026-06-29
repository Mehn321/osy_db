<?php
$pageTitle = 'My Job Openings';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['employer', 'lydo']); // LYDO can also see their own or manage others if needed

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

require_once __DIR__ . '/../includes/header.php';

// Fetch only "Job Opening" type opportunities created by this user
$query = "SELECT * FROM opportunities WHERE created_by = ? AND type = 'Job Opening' ORDER BY created_at DESC";
$jobs = $database->fetchAll($query, [$userId]);

// Handle dynamic counts
$totalJobs = count($jobs);
$activeJobs = 0;
foreach ($jobs as $j) if ($j['status'] === 'Open') $activeJobs++;
?>

<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">My Job Openings</h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Manage and monitor the employment opportunities you have posted.</p>
        </div>
        <a href="job-openings.php?create=1" class="inline-flex items-center gap-2 bg-blue-900 dark:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/20">
            <span class="material-symbols-outlined">add</span> Post New Job
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-blue-100 dark:bg-blue-900/30 rounded-2xl text-blue-900 dark:text-blue-200">
            <span class="material-symbols-outlined text-3xl">work</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Postings</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $totalJobs; ?></h3>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl text-emerald-900 dark:text-emerald-200">
            <span class="material-symbols-outlined text-3xl">check_circle</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Slots</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white"><?php echo $activeJobs; ?></h3>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Title</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Applicants</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job):
                        // Fetch applicant count for this specific job
                        $appCountRes = $database->fetchOne("SELECT COUNT(*) as cnt FROM osy_matches WHERE opportunity_id = ?", [$job['id']]);
                        $appCount = $appCountRes['cnt'] ?? 0;
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($job['title']); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Posted on <?php echo date('M d, Y', strtotime($job['created_at'])); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                                <div class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">location_on</span>
                                    <?php echo htmlspecialchars($job['location'] ?? 'Remote'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-blue-900 dark:text-blue-400"><?php echo $appCount; ?></span>
                                    <span class="text-xs text-slate-500">Candidates</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php echo $job['status'] === 'Open' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'; ?>">
                                    <?php echo $job['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="matching.php?opportunity_id=<?php echo $job['id']; ?>" class="p-2 text-slate-400 hover:text-blue-900 transition-colors" title="View Applicants">
                                        <span class="material-symbols-outlined text-xl">group</span>
                                    </a>
                                    <a href="job-openings.php?edit_id=<?php echo $job['id']; ?>" class="p-2 text-slate-400 hover:text-blue-900 transition-colors" title="Edit Job">
                                        <span class="material-symbols-outlined text-xl">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center text-slate-500">
                            <span class="material-symbols-outlined text-5xl opacity-20 mb-4 block">work_off</span>
                            <p class="text-lg font-medium">You haven't posted any job openings yet.</p>
                            <a href="job-openings.php?create=1" class="text-blue-900 font-bold hover:underline mt-2 inline-block">Create your first posting</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>