<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole('lydo');

require_once __DIR__ . '/../includes/header.php';

$report = new Report($database);
$stats = $report->generateMatchingStats();
$message = '';
$messageType = '';

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        if (isset($_POST['generate_report'])) {
            $reportType = $_POST['report_type'];
            $data = [];

            switch ($reportType) {
                case 'profiles':
                    $data = $report->generateOSYReport();
                    $filename = 'KK_Profile_Report';
                    break;
                case 'opportunities':
                    $data = $report->generateOpportunityReport();
                    $filename = 'Opportunity_Report';
                    break;
                case 'matching':
                    $data = $database->fetchAll("SELECT m.id, p.first_name, p.last_name, p.primary_skill, o.title as opportunity, m.match_score, m.status, m.created_at FROM osy_matches m JOIN osy_profiles p ON m.osy_id = p.id JOIN opportunities o ON m.opportunity_id = o.id ORDER BY m.match_score DESC");
                    $filename = 'Matching_Report';
                    break;
                case 'monthly':
                    $data = $database->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as registrations FROM osy_profiles GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12");
                    $filename = 'Monthly_Activity_Report';
                    break;
            }

            if (!empty($data)) {
                $result = $report->exportToCSV($filename, $data);
                if ($result['success']) {
                    // Send CSV for download
                    $filepath = $result['filepath'];

                    // Clear output buffer before sending headers
                    ob_end_clean();

                    // Set proper headers for CSV download
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                    header('Content-Length: ' . filesize($filepath));
                    header('Cache-Control: no-cache, no-store, must-revalidate');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    header('X-Content-Type-Options: nosniff');

                    // Output file content
                    readfile($filepath);

                    // Clean up
                    unlink($filepath);
                    exit;
                }
            } else {
                $message = 'No data available for this report type.';
                $messageType = 'error';
            }
        }
    }
}
?>

<!-- Page Header -->
<div class="mb-10">
    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Reports & Analytics</h2>
    <p class="text-slate-600 dark:text-slate-400">Generate and view reports on youth profiles, opportunities, and matches.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Total Matches</p>
        <h3 class="text-4xl font-black text-blue-900 dark:text-blue-400"><?php echo $stats['total_matches_made']; ?></h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Accepted matches</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Pending</p>
        <h3 class="text-4xl font-black text-orange-600 dark:text-orange-400"><?php echo $stats['pending_matches']; ?></h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Awaiting response</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Avg Score</p>
        <h3 class="text-4xl font-black text-green-600 dark:text-green-400"><?php echo round($stats['average_match_score'] ?? 0); ?>%</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Average match score</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Success Rate</p>
        <h3 class="text-4xl font-black text-purple-600 dark:text-purple-400"><?php echo $stats['employment_success_rate']; ?>%</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Employment success</p>
    </div>
</div>

<!-- Report Options -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- OSY Report -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-900 dark:text-blue-400">
                <span class="material-symbols-outlined">groups</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">KK Profile Report</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Comprehensive youth member data and statistics</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="report_type" value="profiles">
            <button type="submit" name="generate_report" value="1" class="w-full py-3 px-4 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">download</span>
                Download CSV Report
            </button>
        </form>
    </div>

    <!-- Opportunity Report -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-700 dark:text-green-400">
                <span class="material-symbols-outlined">work</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Opportunity Report</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Job and training opportunity analysis</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="report_type" value="opportunities">
            <button type="submit" name="generate_report" value="1" class="w-full py-3 px-4 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">download</span>
                Download CSV Report
            </button>
        </form>
    </div>

    <!-- Matching Report -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-700 dark:text-purple-400">
                <span class="material-symbols-outlined">handshake</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Matching Report</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Skills matching and placement data</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="report_type" value="matching">
            <button type="submit" name="generate_report" value="1" class="w-full py-3 px-4 bg-purple-700 text-white rounded-lg font-semibold hover:bg-purple-600 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">download</span>
                Download CSV Report
            </button>
        </form>
    </div>

    <!-- Monthly Activity Report -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-700 dark:text-orange-400">
                <span class="material-symbols-outlined">calendar_month</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Monthly Activity</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Monthly trends and activities</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="report_type" value="monthly">
            <button type="submit" name="generate_report" value="1" class="w-full py-3 px-4 bg-orange-700 text-white rounded-lg font-semibold hover:bg-orange-600 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">download</span>
                Download CSV Report
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>