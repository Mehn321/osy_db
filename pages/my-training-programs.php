<?php
$pageTitle = 'My Training Programs';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['training_provider', 'lydo']);

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

require_once __DIR__ . '/../includes/header.php';

// Note: Heavy DB querying removed, using AJAX now
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
            <h3 id="statTotal" class="text-3xl font-black text-slate-900 dark:text-white"><div class="skeleton-pulse h-8 w-16 rounded mt-1"></div></h3>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-center gap-4">
        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl text-emerald-900 dark:text-emerald-200">
            <span class="material-symbols-outlined text-3xl">verified</span>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Enrollment</p>
            <h3 id="statActive" class="text-3xl font-black text-slate-900 dark:text-white"><div class="skeleton-pulse h-8 w-16 rounded mt-1"></div></h3>
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
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="programsTableBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php for ($i=0; $i<3; $i++): ?>
                <tr class="skeleton-row">
                    <td class="px-6 py-4"><div class="skeleton-pulse h-5 w-40 rounded mb-1"></div><div class="skeleton-pulse h-3 w-32 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-16 rounded"></div></td>
                    <td class="px-6 py-4 flex justify-end gap-2"><div class="skeleton-pulse h-8 w-8 rounded"></div><div class="skeleton-pulse h-8 w-8 rounded"></div></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    fetch('../api/get_opportunities_data.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            
            // Filter to only show Training/Scholarship types for this specific page
            const programs = (res.opportunities || []).filter(o => o.type !== 'Job Opening');
            let active = 0;
            
            let html = '';
            if (programs.length === 0) {
                html = `<tr><td colspan="4" class="px-6 py-20 text-center text-slate-500"><span class="material-symbols-outlined text-5xl opacity-20 mb-4 block">school</span><p class="text-lg font-medium">You haven't posted any training programs yet.</p><a href="training-programs.php?create=1" class="text-indigo-900 font-bold hover:underline mt-2 inline-block">Post your first program</a></td></tr>`;
            } else {
                programs.forEach(prog => {
                    if (prog.status === 'Open') active++;
                    const deadline = prog.deadline ? new Date(prog.deadline).toLocaleDateString('en-US', {month:'short',day:'2-digit',year:'numeric'}) : 'Open';
                    
                    html += `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900 dark:text-white">${prog.title}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Deadline: ${deadline}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">${prog.type}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                            <span class="font-bold">${prog.total_slots}</span> Slots
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="matching.php?opportunity_id=${prog.id}" class="p-2 text-slate-400 hover:text-indigo-900 transition-colors" title="View Applicants"><span class="material-symbols-outlined text-xl">group</span></a>
                                <a href="training-programs.php?edit_id=${prog.id}" class="p-2 text-slate-400 hover:text-indigo-900 transition-colors" title="Edit Program"><span class="material-symbols-outlined text-xl">edit</span></a>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            
            document.getElementById('programsTableBody').innerHTML = html;
            document.getElementById('statTotal').textContent = programs.length;
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