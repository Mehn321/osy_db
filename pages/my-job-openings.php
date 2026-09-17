<?php
$pageTitle = 'My Job Openings';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['employer', 'lydo']); // LYDO can also see their own or manage others if needed

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

require_once __DIR__ . '/../includes/header.php';

// Note: Heavy DB querying removed, using AJAX now
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
            <h3 id="statTotal" class="text-3xl font-black text-slate-900 dark:text-white"><div class="skeleton-pulse h-8 w-16 rounded mt-1"></div></h3>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl text-emerald-900 dark:text-emerald-200">
            <span class="material-symbols-outlined text-3xl">check_circle</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Slots</p>
            <h3 id="statActive" class="text-3xl font-black text-slate-900 dark:text-white"><div class="skeleton-pulse h-8 w-16 rounded mt-1"></div></h3>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto thin-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Title</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="jobsTableBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php for ($i=0; $i<3; $i++): ?>
                <tr class="skeleton-row">
                    <td class="px-6 py-4"><div class="skeleton-pulse h-5 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-24 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
                    <td class="px-6 py-4 flex justify-end gap-2"><div class="skeleton-pulse h-8 w-8 rounded"></div><div class="skeleton-pulse h-8 w-8 rounded"></div></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    fetch('../api/get_opportunities_data.php?type=Job+Opening')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            
            const jobs = res.opportunities || [];
            let active = 0;
            
            let html = '';
            if (jobs.length === 0) {
                html = `<tr><td colspan="4" class="px-6 py-20 text-center text-slate-500"><span class="material-symbols-outlined text-5xl opacity-20 mb-4 block">work_off</span><p class="text-lg font-medium">You haven't posted any job openings yet.</p><a href="job-openings.php?create=1" class="text-blue-900 font-bold hover:underline mt-2 inline-block">Create your first posting</a></td></tr>`;
            } else {
                jobs.forEach(job => {
                    if (job.status === 'Open') active++;
                    const created = new Date(job.created_at).toLocaleDateString('en-US', {month:'short',day:'2-digit',year:'numeric'});
                    const loc = job.location || 'Remote';
                    const statusClass = job.status === 'Open' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                    
                    html += `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 dark:text-white">${job.title}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Posted on ${created}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                            <div class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">location_on</span> ${loc}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold ${statusClass}">${job.status}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="matching.php?opportunity_id=${job.id}" class="p-2 text-slate-400 hover:text-blue-900 transition-colors" title="View Applicants"><span class="material-symbols-outlined text-xl">group</span></a>
                                <a href="job-openings.php?edit_id=${job.id}" class="p-2 text-slate-400 hover:text-blue-900 transition-colors" title="Edit Job"><span class="material-symbols-outlined text-xl">edit</span></a>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            
            document.getElementById('jobsTableBody').innerHTML = html;
            document.getElementById('statTotal').textContent = jobs.length;
            document.getElementById('statActive').textContent = active;
        });
})();
</script>

<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>