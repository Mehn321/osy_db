<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Reference.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole('lydo');

$dateMode = $_GET['date_mode'] ?? ($_POST['date_mode'] ?? 'all_time');
$dateMode = $dateMode === 'date_range' ? 'date_range' : 'all_time';
$startDate = trim((string) ($_GET['start_date'] ?? ($_POST['start_date'] ?? '')));
$endDate = trim((string) ($_GET['end_date'] ?? ($_POST['end_date'] ?? '')));
if ($dateMode === 'all_time') {
    $startDate = '';
    $endDate = '';
}
$dateError = '';
if ($dateMode === 'date_range') {
    $startDateObject = $startDate !== '' ? DateTime::createFromFormat('!Y-m-d', $startDate) : false;
    $endDateObject = $endDate !== '' ? DateTime::createFromFormat('!Y-m-d', $endDate) : false;
    $startDateValid = $startDate === '' || ($startDateObject && $startDateObject->format('Y-m-d') === $startDate);
    $endDateValid = $endDate === '' || ($endDateObject && $endDateObject->format('Y-m-d') === $endDate);

    if (!$startDateValid || !$endDateValid) {
        $dateError = 'Please enter valid dates using the date fields.';
        $startDate = '';
        $endDate = '';
    } elseif ($startDate !== '' && $endDate !== '' && $startDate > $endDate) {
        $dateError = 'The start date cannot be later than the end date.';
    }
}
$filterBarangay = $_GET['barangay'] ?? ($_POST['barangay'] ?? 'All Barangays');
$filterGender = $_GET['gender'] ?? ($_POST['gender'] ?? 'All Genders');
$filterProfileType = $_GET['profile_type'] ?? ($_POST['profile_type'] ?? 'All Types');
$filterEducation = $_GET['education'] ?? ($_POST['education'] ?? 'Any Level');
$filterStatus = $_GET['status'] ?? ($_POST['status'] ?? 'All Status');
$filterVerification = $_GET['verification_status'] ?? ($_POST['verification_status'] ?? 'All Verification');

$profilingFilters = [
    'start_date' => $startDate,
    'end_date' => $endDate,
    'barangay' => $filterBarangay,
    'gender' => $filterGender,
    'profile_type' => $filterProfileType,
    'education' => $filterEducation,
    'status' => $filterStatus,
    'verification_status' => $filterVerification,
];

$report = new Report($database); $stats = ["total_matches_made"=>"...", "pending_matches"=>"...", "average_match_score"=>"...", "employment_success_rate"=>"..."];
$message = $dateError;
$messageType = $dateError !== '' ? 'error' : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export_panaon_profiling'])) {
    $result = $report->exportPanaonYouthProfiling($profilingFilters);
    if (!empty($result['success']) && !empty($result['filepath']) && is_file($result['filepath'])) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Content-Length: ' . filesize($result['filepath']));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');
        readfile($result['filepath']);
        unlink($result['filepath']);
        exit;
    }
    $message = $result['message'] ?? 'Unable to generate the Panaon Youth Profiling report.';
    $messageType = 'error';
}

require_once __DIR__ . '/../includes/header.php';

$reference = new Reference($database);
$barangays = $reference->getByCategory('barangay');
$eduLevels = $reference->getByCategory('education_level');
$profilingCount = 0;

// Handle CSV report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['export_panaon_profiling'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        if (isset($_POST['generate_report'])) {
            $reportType = $_POST['report_type'];
            $data = [];
            $filename = 'Report';

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

<!-- Date Filter Card -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-8">
    <form method="GET" action="reports.php" id="report-filter-form" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Date Scope</label>
                <select name="date_mode" id="date-mode" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="all_time" <?php echo $dateMode === 'all_time' ? 'selected' : ''; ?>>All time</option>
                    <option value="date_range" <?php echo $dateMode === 'date_range' ? 'selected' : ''; ?>>Specific date range</option>
                </select>
            </div>
            <div id="start-date-field">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                <input type="date" name="start_date" id="filter-start-date" value="<?php echo htmlspecialchars($startDate); ?>" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" />
            </div>
            <div id="end-date-field">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                <input type="date" name="end_date" id="filter-end-date" value="<?php echo htmlspecialchars($endDate); ?>" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" />
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Barangay</label>
                <select name="barangay" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="All Barangays" <?php echo $filterBarangay === 'All Barangays' ? 'selected' : ''; ?>>All Barangays</option>
                    <?php foreach ($barangays as $b): ?>
                        <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $filterBarangay === $b ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                <select name="gender" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="All Genders" <?php echo $filterGender === 'All Genders' ? 'selected' : ''; ?>>All Genders</option>
                    <option value="Male" <?php echo $filterGender === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $filterGender === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Classification</label>
                <select name="profile_type" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="All Types" <?php echo $filterProfileType === 'All Types' ? 'selected' : ''; ?>>All Types</option>
                    <option value="OSY" <?php echo $filterProfileType === 'OSY' ? 'selected' : ''; ?>>OSY</option>
                    <option value="Regular" <?php echo $filterProfileType === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Educational Attainment</label>
                <select name="education" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="Any Level" <?php echo $filterEducation === 'Any Level' ? 'selected' : ''; ?>>Any Level</option>
                    <?php foreach ($eduLevels as $e): ?>
                        <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $filterEducation === $e ? 'selected' : ''; ?>><?php echo htmlspecialchars($e); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="All Status" <?php echo $filterStatus === 'All Status' ? 'selected' : ''; ?>>All Status</option>
                    <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Employed" <?php echo $filterStatus === 'Employed' ? 'selected' : ''; ?>>Employed</option>
                    <option value="In Training" <?php echo $filterStatus === 'In Training' ? 'selected' : ''; ?>>In Training</option>
                    <option value="Inactive" <?php echo $filterStatus === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Verification</label>
                <select name="verification_status" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900">
                    <option value="All Verification" <?php echo $filterVerification === 'All Verification' ? 'selected' : ''; ?>>All Verification</option>
                    <option value="Verified" <?php echo $filterVerification === 'Verified' ? 'selected' : ''; ?>>Verified</option>
                    <option value="Pending" <?php echo $filterVerification === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Action Required" <?php echo $filterVerification === 'Action Required' ? 'selected' : ''; ?>>Action Required</option>
                    <option value="Drafting" <?php echo $filterVerification === 'Drafting' ? 'selected' : ''; ?>>Drafting</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" id="apply-filter-button" class="px-6 py-2 bg-blue-900 text-white rounded-lg text-sm font-semibold hover:bg-blue-800 transition-colors inline-flex items-center gap-2 disabled:opacity-70 disabled:cursor-wait">
                <span class="material-symbols-outlined text-base" aria-hidden="true">filter_alt</span>
                <span data-filter-button-label>Apply Filter</span>
            </button>
            <?php if (!empty($startDate) || !empty($endDate) || $filterBarangay !== 'All Barangays' || $filterGender !== 'All Genders' || $filterProfileType !== 'All Types' || $filterEducation !== 'Any Level' || $filterStatus !== 'All Status' || $filterVerification !== 'All Verification'): ?>
                <a href="reports.php" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-800 dark:text-amber-300">
                <span class="material-symbols-outlined">table_view</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Panaon Youth Profiling</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Download the official municipal spreadsheet template filled with youth profiles matching the filters above.</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2"><span id="profilingCountSpan">...</span> profile<span id="profilingCountPlural">s</span> will be included.</p>
            </div>
        </div>
        <form method="GET" action="reports.php" class="w-full lg:w-auto">
            <input type="hidden" name="export_panaon_profiling" value="1">
            <input type="hidden" name="date_mode" value="<?php echo htmlspecialchars($dateMode); ?>">
            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
            <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($filterBarangay); ?>">
            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($filterGender); ?>">
            <input type="hidden" name="profile_type" value="<?php echo htmlspecialchars($filterProfileType); ?>">
            <input type="hidden" name="education" value="<?php echo htmlspecialchars($filterEducation); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
            <input type="hidden" name="verification_status" value="<?php echo htmlspecialchars($filterVerification); ?>">
            <button type="submit" class="w-full lg:w-auto py-3 px-6 bg-amber-700 text-white rounded-lg font-semibold hover:bg-amber-600 transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">download</span>
                Download Excel Report
            </button>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Total Matches</p>
        <h3 class="text-4xl font-black text-blue-900 dark:text-blue-400"><span id="stat_total_matches">...</span></h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Accepted matches</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Pending</p>
        <h3 class="text-4xl font-black text-orange-600 dark:text-orange-400"><span id="stat_pending_matches">...</span></h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Awaiting response</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Avg Score</p>
        <h3 class="text-4xl font-black text-green-600 dark:text-green-400"><span id="stat_avg_score">...</span>%</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Average match score</p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Success Rate</p>
        <h3 class="text-4xl font-black text-purple-600 dark:text-purple-400"><span id="stat_success_rate">...</span>%</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Employment success</p>
    </div>
</div>

<!-- Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <!-- Chart 1: Profiles by Type -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Youth Profiles by Type</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Distribution of youth profile registrations</p>
        </div>
        <div class="relative h-[250px] w-full flex items-center justify-center">
            <canvas id="chart-profile-types"></canvas>
        </div>
    </div>

    <!-- Chart 2: Employment Status -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Employment Status</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Current employment status breakdown</p>
        </div>
        <div class="relative h-[250px] w-full flex items-center justify-center">
            <canvas id="chart-employment-status"></canvas>
        </div>
    </div>

    <!-- Chart 3: Monthly Registrations -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Registrations Over Time</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Monthly trend of profile registrations</p>
        </div>
        <div class="relative h-[250px] w-full flex items-center justify-center">
            <canvas id="chart-monthly-registrations"></canvas>
        </div>
    </div>
</div>

<!-- Report Options -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
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

<script>
    (function() {
        const filterForm = document.getElementById('report-filter-form');
        const applyFilterButton = document.getElementById('apply-filter-button');
        const filterButtonLabel = applyFilterButton?.querySelector('[data-filter-button-label]');

        filterForm?.addEventListener('submit', function() {
            if (!applyFilterButton) return;

            applyFilterButton.disabled = true;
            applyFilterButton.setAttribute('aria-busy', 'true');
            applyFilterButton.querySelector('.material-symbols-outlined')?.remove();

            const spinner = document.createElement('span');
            spinner.className = 'inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white';
            spinner.setAttribute('aria-hidden', 'true');
            applyFilterButton.prepend(spinner);

            if (filterButtonLabel) {
                filterButtonLabel.textContent = 'Applying filters...';
            }

            filterForm.querySelectorAll('button, select, input').forEach(control => {
                if (control !== applyFilterButton && control.type !== 'hidden') {
                    control.disabled = true;
                }
            });
        });

        const dateMode = document.getElementById('date-mode');
        const dateFields = [
            document.getElementById('start-date-field'),
            document.getElementById('end-date-field')
        ];
        const dateInputs = [
            document.getElementById('filter-start-date'),
            document.getElementById('filter-end-date')
        ];

        function updateDateFields() {
            const useDateRange = dateMode?.value === 'date_range';
            dateFields.forEach(field => field?.classList.toggle('opacity-50', !useDateRange));
            dateInputs.forEach(input => {
                if (input) input.disabled = !useDateRange;
            });
        }

        dateMode?.addEventListener('change', updateDateFields);
        updateDateFields();

        const startDate = document.getElementById('filter-start-date')?.value || '';
        const endDate = document.getElementById('filter-end-date')?.value || '';
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        const filterNames = ['barangay', 'gender', 'profile_type', 'education', 'status', 'verification_status'];
        filterNames.forEach(name => {
            const input = document.querySelector(`[name="${name}"]`);
            if (input?.value) params.append(name, input.value);
        });

        const statsRequest = new XMLHttpRequest();
        statsRequest.open('GET', `../api/report_stats.php?${params.toString()}`, true);
        statsRequest.setRequestHeader('Accept', 'application/json');
        statsRequest.onload = function() {
            if (statsRequest.status < 200 || statsRequest.status >= 300) {
                console.error(`Report statistics request failed: HTTP ${statsRequest.status}`);
                return;
            }

            try {
                const data = JSON.parse(statsRequest.responseText);
                if (!data.success) {
                    console.error(data.message);
                    return;
                }
                renderCharts(data);
            } catch (error) {
                console.error('Unable to read report statistics response.', error);
            }
        };
        statsRequest.onerror = function() {
            console.error('Report statistics request failed.');
        };
        statsRequest.send();

        let charts = {};

        function renderCharts(data) {
            // Destroy existing if any
            Object.keys(charts).forEach(key => charts[key].destroy());

            
            if (data.stats) {
                document.getElementById('stat_total_matches').textContent = data.stats.total_matches_made;
                document.getElementById('stat_pending_matches').textContent = data.stats.pending_matches;
                document.getElementById('stat_avg_score').textContent = Math.round(data.stats.average_match_score || 0);
                document.getElementById('stat_success_rate').textContent = data.stats.employment_success_rate;
            }
            if (data.profilingCount !== undefined) {
                document.getElementById('profilingCountSpan').textContent = data.profilingCount;
                document.getElementById('profilingCountPlural').textContent = data.profilingCount === 1 ? '' : 's';
            }

            // 1. Profile Types Chart
            const typeLabels = data.profile_types.map(item => item.type);
            const typeCounts = data.profile_types.map(item => item.count);
            const ctxTypes = document.getElementById('chart-profile-types')?.getContext('2d');
            if (ctxTypes) {
                charts.profileTypes = new Chart(ctxTypes, {
                    type: 'bar',
                    data: {
                        labels: typeLabels,
                        datasets: [{
                            label: 'Profiles',
                            data: typeCounts,
                            backgroundColor: '#1d4ed8',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // 2. Employment Status Chart
            const statusLabels = data.employment_status.map(item => item.status);
            const statusCounts = data.employment_status.map(item => item.count);
            const ctxStatus = document.getElementById('chart-employment-status')?.getContext('2d');
            if (ctxStatus) {
                charts.employmentStatus = new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusCounts,
                            backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // 3. Monthly Registrations Chart
            const monthlyLabels = data.monthly_registrations.map(item => item.month);
            const monthlyCounts = data.monthly_registrations.map(item => item.count);
            const ctxMonthly = document.getElementById('chart-monthly-registrations')?.getContext('2d');
            if (ctxMonthly) {
                charts.monthlyRegs = new Chart(ctxMonthly, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Registrations',
                            data: monthlyCounts,
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
    })();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>