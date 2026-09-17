<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$userRole     = $_SESSION['role'] ?? 'staff';
$userBarangay = $_SESSION['barangay'] ?? '';
$userId       = $_SESSION['user_id'];
$fullname     = htmlspecialchars($_SESSION['fullname'] ?? 'User');
?>

<?php if ($userRole === 'lydo'): ?>
<!-- ═══════════════ LYDO DASHBOARD SKELETON ═══════════════ -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Welcome back, <?= $fullname ?>!</h2>
    <p class="text-slate-600 dark:text-slate-400 font-medium">Monitor youth enrollment, training progress, and employment outcomes in the Youth Profiling System.</p>
</div>

<!-- Summary Cards (skeleton → filled by JS) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10" id="statsGrid">
    <?php foreach (['Total KK Youth','OSY Members','Opportunities','Matches Made','Notifications','AI Scoring'] as $label): ?>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div class="w-full">
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1"><?= $label ?></p>
            <div class="skeleton-pulse h-10 w-24 rounded-lg mb-2"></div>
            <div class="skeleton-pulse h-3 w-32 rounded"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex justify-between items-center mb-8">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Skill Category Distribution</h4>
            <a href="reports.php" class="text-sm font-semibold text-blue-900 dark:text-blue-400 flex items-center gap-1 hover:underline">View Full Report <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
        </div>
        <div id="skillChart" class="flex items-end gap-3 sm:gap-6 h-80 pt-10 px-2">
            <!-- Skeleton bars -->
            <?php foreach ([60,90,45,70,55,40,80,35] as $h): ?>
            <div class="flex-1 flex flex-col items-center gap-4 min-w-[40px]">
                <div class="skeleton-pulse w-full rounded-t-xl" style="height:<?= $h ?>%;"></div>
                <div class="skeleton-pulse h-3 w-10 rounded"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-8">OSY Status</h4>
        <div id="statusBreakdown" class="space-y-4">
            <?php foreach (['Active/Profiling','In Training','Employed'] as $_): ?>
            <div class="flex items-center justify-between">
                <div class="skeleton-pulse h-4 w-28 rounded"></div>
                <div class="skeleton-pulse h-4 w-8 rounded"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Registrations -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Recent Registrations</h4>
        <a href="profiles.php" class="text-sm font-semibold text-blue-900 dark:text-blue-400 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto thin-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Skill</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Date</th>
                </tr>
            </thead>
            <tbody id="recentRegistrationsBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-24 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Loading indicator -->
<div id="dashboardLoading" class="flex items-center justify-center gap-2 mt-6 text-sm text-slate-500 dark:text-slate-400">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading dashboard data…
</div>

<?php elseif ($userRole === 'sk_chairman'): ?>
<!-- ═══════════════ SK CHAIRMAN DASHBOARD SKELETON ═══════════════ -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Barangay <?= htmlspecialchars($userBarangay) ?> Dashboard</h2>
    <p class="text-slate-600 dark:text-slate-400 font-medium">Manage youth verification and monitor registration progress for your barangay.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10" id="skStatsGrid">
    <?php foreach (['Registered Youth','Awaiting Verification','Verified Members'] as $lbl): ?>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div class="w-full">
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1"><?= $lbl ?></p>
            <div class="skeleton-pulse h-10 w-20 rounded-lg mb-2"></div>
            <div class="skeleton-pulse h-3 w-28 rounded"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Recent Submissions (<?= htmlspecialchars($userBarangay) ?>)</h4>
        <a href="sk-barangay-youth.php" class="text-sm font-semibold text-blue-900 dark:text-blue-400 hover:underline">View Barangay Registry</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Skill</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Action</th>
                </tr>
            </thead>
            <tbody id="skRecentBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="dashboardLoading" class="flex items-center justify-center gap-2 mt-6 text-sm text-slate-500 dark:text-slate-400">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading dashboard data…
</div>

<?php elseif ($userRole === 'employer' || $userRole === 'training_provider'): ?>
<!-- ═══════════════ PROVIDER DASHBOARD SKELETON ═══════════════ -->
<?php $isEmployer = ($userRole === 'employer'); ?>
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Provider Dashboard</h2>
    <p class="text-slate-600 dark:text-slate-400 font-medium">Manage your posted <?= $isEmployer ? 'job openings' : 'training programs' ?> and review applications.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10" id="providerStatsGrid">
    <?php foreach (['Total Posted','Total Applications'] as $lbl): ?>
    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div class="w-full">
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1"><?= $lbl ?></p>
            <div class="skeleton-pulse h-12 w-20 rounded-lg mb-2"></div>
            <div class="skeleton-pulse h-3 w-40 rounded"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Your Latest Postings</h4>
        <a href="opportunities.php" class="text-sm font-semibold text-blue-900 dark:text-blue-400 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Title</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Deadline</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Applications</th>
                </tr>
            </thead>
            <tbody id="providerOppBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-40 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
                    <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="dashboardLoading" class="flex items-center justify-center gap-2 mt-6 text-sm text-slate-500 dark:text-slate-400">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading dashboard data…
</div>

<?php else: ?>
<!-- ═══════════════ YOUTH / DEFAULT (no heavy data, static) ═══════════════ -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Welcome to the Portal</h2>
    <p class="text-slate-600 dark:text-slate-400 font-medium">Find the best opportunities matched for your skills.</p>
</div>
<div class="bg-blue-900 rounded-3xl p-12 text-white flex flex-col md:flex-row items-center justify-between gap-8">
    <div>
        <h3 class="text-3xl font-bold mb-4">Start Your Journey Today</h3>
        <p class="text-blue-100 text-lg max-w-xl mb-8">Browse available job openings and vocational training programs verified by your local government.</p>
        <a href="opportunities.php" class="inline-flex items-center gap-2 bg-white text-blue-900 px-8 py-4 rounded-2xl font-bold hover:bg-blue-50 transition-colors shadow-xl">
            Explore Opportunities <span class="material-symbols-outlined">explore</span>
        </a>
    </div>
    <div class="hidden lg:block opacity-20">
        <span class="material-symbols-outlined text-[200px]">rocket_launch</span>
    </div>
</div>
<?php endif; ?>

<style>
.skeleton-pulse {
    background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.4s ease-in-out infinite;
}
.dark .skeleton-pulse {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%);
    background-size: 200% 100%;
}
@keyframes skeleton-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>

<script>
(function () {
    const role = <?= json_encode($userRole) ?>;

    // ── Helpers ─────────────────────────────────────────────────────────────
    function statusBadge(status) {
        const map = {
            'Active':      'bg-green-100 text-green-700',
            'Employed':    'bg-blue-100 text-blue-700',
            'In Training': 'bg-orange-100 text-orange-700',
            'Inactive':    'bg-slate-100 text-slate-700',
            'Open':        'bg-green-100 text-green-700',
            'Closed':      'bg-red-100 text-red-700',
        };
        const cls = map[status] || 'bg-yellow-100 text-yellow-700';
        return `<span class="inline-flex px-3 py-1 rounded-full text-xs font-bold ${cls}">${status}</span>`;
    }
    function fmt(d) { return d ? new Date(d).toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'}) : 'N/A'; }
    function hiddenLoading() {
        const el = document.getElementById('dashboardLoading');
        if (el) el.style.display = 'none';
    }

    // ── Fetch ────────────────────────────────────────────────────────────────
    fetch('../api/get_dashboard_data.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const data = res.data;

            if (role === 'lydo') {
                const s = data.stats;

                // Stat cards
                const cards = [
                    { label:'Total KK Youth',   val: s.total_kk,               sub: 'All registered youth 15–30', color: 'text-blue-900 dark:text-blue-200' },
                    { label:'OSY Members',       val: s.total_osy,              sub: (s.total_kk>0?Math.round(s.total_osy/s.total_kk*100):0)+'% of total KK', color:'text-orange-600' },
                    { label:'Opportunities',     val: s.total_opportunities,    sub: 'Active training & jobs', color:'text-blue-900 dark:text-blue-200' },
                    { label:'Matches Made',      val: s.accepted_matches,       sub: s.pending_matches+' pending', color:'text-blue-900 dark:text-blue-200' },
                    { label:'Notifications',     val: s.total_notifications_sent, sub:'Sent to residents', color:'text-blue-900 dark:text-blue-200' },
                    { label:'AI Scoring',        val: '—',                      sub: 'Scoring coverage', color:'text-indigo-600' },
                ];
                const icons = ['groups','person_off','work_history','handshake','campaign','auto_awesome'];
                const iconBg = ['bg-blue-100 text-blue-900','bg-orange-100 text-orange-600','bg-green-100 text-green-900','bg-purple-100 text-purple-900','bg-orange-100 text-orange-900','bg-indigo-100 text-indigo-700'];
                const grid = document.getElementById('statsGrid');
                if (grid) {
                    grid.innerHTML = cards.map((c,i) => `
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">${c.label}</p>
                                <h3 class="text-4xl font-black ${c.color}">${c.val}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">${c.sub}</p>
                            </div>
                            <div class="p-3 ${iconBg[i]} rounded-lg"><span class="material-symbols-outlined text-3xl">${icons[i]}</span></div>
                        </div>`).join('');
                }

                // Skill chart
                const skills = data.skill_distribution || [];
                const skillEl = document.getElementById('skillChart');
                if (skillEl && skills.length) {
                    const max = Math.max(...skills.map(s => +s.count), 1);
                    skillEl.innerHTML = skills.map(s => {
                        const pct = (s.count / max) * 100;
                        const name = s.primary_skill;
                        const disp = name.length > 12 ? name.slice(0,10)+'..' : name;
                        return `<div class="flex-1 flex flex-col items-center gap-4 group min-w-[40px]">
                            <div class="w-full bg-blue-600/20 dark:bg-blue-900/40 rounded-t-xl relative hover:bg-blue-600/40" style="height:${pct}%;">
                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[11px] font-bold py-1.5 px-3 rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none z-10 whitespace-nowrap">${name}: ${s.count}</div>
                            </div>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400 text-center">${disp}</span>
                        </div>`;
                    }).join('');
                }

                // Status breakdown
                const statusEl = document.getElementById('statusBreakdown');
                if (statusEl) {
                    const rows = [
                        { label:'Active/Profiling', color:'bg-blue-900', val: s.active_osy },
                        { label:'In Training',      color:'bg-orange-400', val: '—' },
                        { label:'Employed',         color:'bg-green-500', val: s.employed_osy },
                    ];
                    // find in-training from status_distribution
                    const dist = data.status_distribution || [];
                    const inTr = dist.find(d => d.status === 'In Training');
                    rows[1].val = inTr ? inTr.count : 0;
                    statusEl.innerHTML = rows.map(r => `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full ${r.color}"></span><span class="text-sm font-medium text-slate-700 dark:text-slate-300">${r.label}</span></div>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">${r.val}</span>
                        </div>`).join('');
                }

                // Recent registrations
                const tbody = document.getElementById('recentRegistrationsBody');
                if (tbody) {
                    const regs = data.recent_registrations || [];
                    tbody.innerHTML = regs.length
                        ? regs.map(r => `<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4"><p class="font-bold text-slate-900 dark:text-white">${r.first_name} ${r.last_name}</p><p class="text-xs text-slate-500">${r.email||'No email'}</p></td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">${r.primary_skill||'N/A'}</td>
                            <td class="px-6 py-4">${statusBadge(r.status)}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${fmt(r.created_at)}</td>
                        </tr>`).join('')
                        : '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">No registrations yet</td></tr>';
                }

            } else if (role === 'sk_chairman') {
                const s = data.stats;
                const grid = document.getElementById('skStatsGrid');
                if (grid) {
                    const cards = [
                        { label:'Registered Youth',      val: s.total_kk,            sub: 'Total registered', color:'text-blue-900 dark:text-blue-200' },
                        { label:'Awaiting Verification', val: s.pending_verification, sub: 'Needs your review', color:'text-orange-600',
                          extra:`<a href="verify-youth.php" class="inline-flex items-center text-xs font-bold text-orange-600 hover:underline mt-2">Verify Now <span class="material-symbols-outlined text-xs">arrow_forward</span></a>` },
                        { label:'Verified Members',      val: s.verified_youth,       sub: 'Ready for opportunities', color:'text-green-600' },
                    ];
                    const icons  = ['groups','verified_user','check_circle'];
                    const iconBg = ['bg-blue-100 text-blue-900','bg-orange-100 text-orange-600','bg-green-100 text-green-600'];
                    grid.innerHTML = cards.map((c,i) => `
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between${i===1?' border-l-4 border-l-orange-500':''}">
                            <div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">${c.label}</p>
                                <h3 class="text-4xl font-black ${c.color}">${c.val}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">${c.sub}</p>
                                ${c.extra||''}
                            </div>
                            <div class="p-3 ${iconBg[i]} rounded-lg"><span class="material-symbols-outlined text-3xl">${icons[i]}</span></div>
                        </div>`).join('');
                }
                const tbody = document.getElementById('skRecentBody');
                if (tbody) {
                    const regs = data.recent_registrations || [];
                    tbody.innerHTML = regs.length
                        ? regs.map(r => `<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4"><p class="font-bold text-slate-900 dark:text-white">${r.first_name} ${r.last_name}</p><p class="text-xs text-slate-500">${fmt(r.created_at)}</p></td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">${r.primary_skill||'N/A'}</td>
                            <td class="px-6 py-4">${statusBadge(r.status)}</td>
                            <td class="px-6 py-4"><a href="profile-detail.php?id=${r.id}" class="text-blue-900 hover:underline text-sm font-bold flex items-center gap-1">View Profile <span class="material-symbols-outlined text-xs">visibility</span></a></td>
                        </tr>`).join('')
                        : '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">No youth registered in your barangay yet.</td></tr>';
                }

            } else if (role === 'employer' || role === 'training_provider') {
                const s = data.stats;
                const isEmployer = (role === 'employer');
                const grid = document.getElementById('providerStatsGrid');
                if (grid) {
                    grid.innerHTML = `
                        <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Total Posted</p>
                                <h3 class="text-4xl font-black text-blue-900 dark:text-blue-200">${s.total_posted}</h3>
                                <p class="text-xs text-slate-500 mt-2">Active and closed opportunities</p>
                                <a href="${isEmployer?'my-job-openings.php':'my-training-programs.php'}" class="inline-flex items-center text-xs font-bold text-blue-900 hover:underline mt-4">Manage Postings <span class="material-symbols-outlined text-xs">arrow_forward</span></a>
                            </div>
                            <div class="p-3 bg-blue-100 text-blue-900 rounded-lg"><span class="material-symbols-outlined text-3xl">${isEmployer?'work':'school'}</span></div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Total Applications</p>
                                <h3 class="text-4xl font-black text-purple-600">${s.total_applications}</h3>
                                <p class="text-xs text-slate-500 mt-2">Received through the platform</p>
                            </div>
                            <div class="p-3 bg-purple-100 text-purple-600 rounded-lg"><span class="material-symbols-outlined text-3xl">how_to_reg</span></div>
                        </div>`;
                }
                const tbody = document.getElementById('providerOppBody');
                if (tbody) {
                    const opps = data.recent_opportunities || [];
                    tbody.innerHTML = opps.length
                        ? opps.map(o => `<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4"><p class="font-bold text-slate-900 dark:text-white">${o.title}</p><p class="text-xs text-slate-500">${o.type}</p></td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">${fmt(o.deadline)}</td>
                            <td class="px-6 py-4">${statusBadge(o.status)}</td>
                            <td class="px-6 py-4 text-sm font-bold text-blue-900 dark:text-blue-400">— Applicants</td>
                        </tr>`).join('')
                        : `<tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">You haven't posted any opportunities yet.</td></tr>`;
                }
            }

            hiddenLoading();
        })
        .catch(() => {
            const el = document.getElementById('dashboardLoading');
            if (el) el.innerHTML = '<span class="text-red-500">Failed to load dashboard data. Please refresh.</span>';
        });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>