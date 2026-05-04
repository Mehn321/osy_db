<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$dashboard = new Dashboard($database);
$stats = $dashboard->getStats();
$skills = $dashboard->getSkillDistribution();
$recent = $dashboard->getRecentRegistrations();

// Get in-training count dynamically
$inTrainingResult = $database->fetchOne("SELECT COUNT(*) as cnt FROM osy_profiles WHERE status = 'In Training'");
$inTrainingCount = $inTrainingResult['cnt'] ?? 0;
?>

<!-- Welcome Header -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</h2>
    <p class="text-slate-600 dark:text-slate-400 font-medium">Monitoring youth enrollment and OSY progress in Municipal KK Profiling System.</p>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Total KK Youth</p>
            <h3 class="text-4xl font-black text-blue-900"><?php echo $stats['total_kk']; ?></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">trending_up</span> All registered youth 15-30
            </p>
        </div>
        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-900">
            <span class="material-symbols-outlined text-3xl">groups</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">OSY Members</p>
            <h3 class="text-4xl font-black text-orange-600"><?php echo $stats['total_osy']; ?></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">
                <?php $osy_pct = $stats['total_kk'] > 0 ? round(($stats['total_osy'] / $stats['total_kk']) * 100, 1) : 0; ?>
                <?php echo $osy_pct; ?>% of total KK
            </p>
        </div>
        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg text-orange-600">
            <span class="material-symbols-outlined text-3xl">person_off</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Opportunities</p>
            <h3 class="text-4xl font-black text-blue-900"><?php echo $stats['total_opportunities']; ?></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">Active training & jobs</p>
        </div>
        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-900">
            <span class="material-symbols-outlined text-3xl">work_history</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Matches Made</p>
            <h3 class="text-4xl font-black text-blue-900"><?php echo $stats['accepted_matches']; ?></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">check_circle</span> <?php echo $stats['pending_matches']; ?> pending
            </p>
        </div>
        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-900">
            <span class="material-symbols-outlined text-3xl">handshake</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex items-start justify-between">
        <div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Notifications</p>
            <h3 class="text-4xl font-black text-blue-900"><?php echo $stats['total_notifications_sent']; ?></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-2">Sent to residents</p>
        </div>
        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg text-orange-900">
            <span class="material-symbols-outlined text-3xl">campaign</span>
        </div>
    </div>

    <?php
        // Get AI scoring coverage for dashboard
        $matchingDash = new Matching($database);
        $syncStatsDash = $matchingDash->getGlobalSyncStats();
        $dashScoringPct = $syncStatsDash['total_possible'] > 0 
            ? round(($syncStatsDash['existing_matches'] / $syncStatsDash['total_possible']) * 100, 1) 
            : 0;
    ?>
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">AI Scoring</p>
                <h3 class="text-4xl font-black <?php echo $dashScoringPct >= 100 ? 'text-emerald-600' : ($dashScoringPct >= 50 ? 'text-amber-600' : 'text-blue-900'); ?>"><?php echo $dashScoringPct; ?>%</h3>
            </div>
            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg text-indigo-700">
                <span class="material-symbols-outlined text-3xl">auto_awesome</span>
            </div>
        </div>
        <div>
            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-indigo-500 rounded-full transition-all duration-700" style="width: <?php echo min(100, $dashScoringPct); ?>%"></div>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-sm"><?php echo $dashScoringPct >= 100 ? 'check_circle' : 'sync'; ?></span>
                <?php echo number_format($syncStatsDash['existing_matches']); ?>/<?php echo number_format($syncStatsDash['total_possible']); ?> scored
            </p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <!-- Skill Distribution Chart -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex justify-between items-center mb-8">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Skill Category Distribution</h4>
            <a href="reports.php" class="text-sm font-semibold text-blue-900 flex items-center gap-1 hover:underline">View Full Report <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
        </div>
        <?php if (!empty($skills)): ?>
            <div class="flex items-end gap-3 sm:gap-6 h-80 pt-10 px-2">
                <?php
                $maxSkill = max(array_column($skills, 'count'));
                $maxSkill = $maxSkill > 0 ? $maxSkill : 1;
                foreach ($skills as $skill):
                    $percentage = ($skill['count'] / $maxSkill) * 100;
                    $skillName = $skill['primary_skill'];
                    $displayName = strlen($skillName) > 12 ? substr($skillName, 0, 10) . '..' : $skillName;
                ?>
                    <div class="flex-1 flex flex-col items-center gap-4 group min-w-[40px]">
                        <div class="w-full bg-blue-600/20 dark:bg-blue-900/40 rounded-t-xl relative transition-all hover:bg-blue-600/40 group-hover:scale-x-110" style="height: <?php echo $percentage; ?>%;">
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[11px] font-bold py-1.5 px-3 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none shadow-xl z-10 whitespace-nowrap">
                                <?php echo $skillName; ?>: <?php echo $skill['count']; ?>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400 text-center leading-tight h-8 flex items-center justify-center"><?php echo htmlspecialchars($displayName); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-center h-64 text-slate-400">
                <div class="text-center">
                    <span class="material-symbols-outlined text-4xl mb-2">bar_chart</span>
                    <p>No skill data available yet</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Status Distribution -->
    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-8">OSY Status</h4>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-900"></span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Active/Profiling</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo $stats['active_osy']; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-orange-400"></span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">In Training</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo $inTrainingCount; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Employed</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white"><?php echo $stats['employed_osy']; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Registrations Table -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Recent Registrations</h4>
        <a href="profiles.php" class="text-sm font-semibold text-blue-900 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Skill</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($recent)): ?>
                    <?php foreach ($recent as $reg): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($reg['email'] ?? 'No email'); ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($reg['primary_skill'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php
                                                                                                    if ($reg['status'] == 'Active') echo 'bg-green-100 text-green-700';
                                                                                                    elseif ($reg['status'] == 'Employed') echo 'bg-blue-100 text-blue-700';
                                                                                                    elseif ($reg['status'] == 'In Training') echo 'bg-orange-100 text-orange-700';
                                                                                                    else echo 'bg-yellow-100 text-yellow-700';
                                                                                                    ?>">
                                    <?php echo htmlspecialchars($reg['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400"><?php echo date('M d, Y', strtotime($reg['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">No registrations yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>