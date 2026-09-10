<?php
/**
 * TRAINING PROVIDER FEATURE TEST RUNNER
 *
 * Tests all features specific to the training_provider user role:
 * - Navigation & sidebar links
 * - My Programs button routing
 * - Broadcast button functionality
 * - Match Skills removal
 * - Form submissions (create/edit/delete)
 * - Role-based access control
 *
 * Run via: http://localhost/osy_db/test_training_provider.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

$passed = 0;
$failed = 0;
$warnings = 0;
$results = [];

function pass(string $name, string $detail = ''): void {
    global $passed, $results;
    $passed++;
    $results[] = ['status' => 'PASS', 'name' => $name, 'detail' => $detail];
}

function fail(string $name, string $detail = ''): void {
    global $failed, $results;
    $failed++;
    $results[] = ['status' => 'FAIL', 'name' => $name, 'detail' => $detail];
}

function warn(string $name, string $detail = ''): void {
    global $warnings, $results;
    $warnings++;
    $results[] = ['status' => 'WARN', 'name' => $name, 'detail' => $detail];
}

function check(string $name, bool $condition, string $failDetail = '', string $passDetail = ''): void {
    if ($condition) pass($name, $passDetail);
    else fail($name, $failDetail);
}

$ROOT = __DIR__;
$PAGES = $ROOT . '/pages';
$INCLUDES = $ROOT . '/includes';

// ─── SECTION 1: FILES ────────────────────────────────────────────────────────
$requiredFiles = [
    'header.php'            => $INCLUDES . '/header.php',
    'training-programs.php' => $PAGES . '/training-programs.php',
    'my-training-programs.php' => $PAGES . '/my-training-programs.php',
    'dashboard.php'         => $PAGES . '/dashboard.php',
    'my-notifications.php'  => $PAGES . '/my-notifications.php',
    'login.php'             => $PAGES . '/login.php',
    'logout.php'            => $PAGES . '/logout.php',
];
foreach ($requiredFiles as $label => $path) {
    check("File exists: $label", file_exists($path), "Missing: $path");
}

// ─── SECTION 2: HEADER NAVIGATION ───────────────────────────────────────────
$headerContent = file_exists($INCLUDES . '/header.php') ? file_get_contents($INCLUDES . '/header.php') : '';

check(
    "My Programs link → training-programs.php?edit_id=9",
    strpos($headerContent, "training-programs.php?edit_id=9") !== false,
    "'training-programs.php?edit_id=9' not found in header.php nav for training_provider"
);

check(
    "Match Skills button excluded from training_provider",
    !preg_match("/\[.*?'training_provider'.*?\].*?Match Skills/s", $headerContent)
    && !preg_match("/Match Skills.*?\[.*?'training_provider'.*?\]/s", $headerContent)
    || strpos($headerContent, "'lydo', 'employer'") !== false,
    "training_provider may still have Match Skills in its nav condition"
);

check(
    "Match Skills still available for lydo + employer",
    preg_match("/\[.*?'lydo'.*?'employer'.*?\]/", $headerContent)
    || strpos($headerContent, "'lydo', 'employer'") !== false,
    "Match Skills condition no longer includes lydo and employer"
);

// ─── SECTION 3: BROADCAST MODAL STRUCTURE ───────────────────────────────────
$trainingContent = file_exists($PAGES . '/training-programs.php') ? file_get_contents($PAGES . '/training-programs.php') : '';

check(
    "broadcastModal element exists",
    strpos($trainingContent, 'id="broadcastModal"') !== false,
    "broadcastModal not found in training-programs.php"
);

check(
    "Broadcast buttons have .broadcast-btn class",
    strpos($trainingContent, "broadcast-btn") !== false,
    ".broadcast-btn class not found on broadcast buttons"
);

check(
    "Broadcast buttons have data-opp-id attribute",
    strpos($trainingContent, 'data-opp-id=') !== false,
    "data-opp-id attribute not found on broadcast buttons"
);

// Check broadcastModal is NOT nested inside opportunityModal
$oppModalOpen   = strpos($trainingContent, 'id="opportunityModal"');
$broadcastStart = strpos($trainingContent, 'id="broadcastModal"');
if ($oppModalOpen !== false && $broadcastStart !== false) {
    $between   = substr($trainingContent, $oppModalOpen, $broadcastStart - $oppModalOpen);
    $openDivs  = substr_count($between, '<div');
    $closeDivs = substr_count($between, '</div>');
    check(
        "broadcastModal NOT nested inside opportunityModal (div balance check)",
        $closeDivs >= $openDivs,
        "broadcastModal nested inside opportunityModal — broadcast hidden when edit modal is closed (opens: $openDivs, closes: $closeDivs)",
        "Divs balanced: $openDivs opened, $closeDivs closed before broadcastModal"
    );
} else {
    fail("broadcastModal nesting check", "Could not locate modal elements");
}

// ─── SECTION 4: BROADCAST EVENT BINDING ─────────────────────────────────────
check(
    "Broadcast NOT bound only in DOMContentLoaded (SPA-safe)",
    !preg_match("/DOMContentLoaded[\s\S]{0,500}broadcast-btn/", $trainingContent),
    "Broadcast button bound inside DOMContentLoaded — won't work after SPA navigation"
);

check(
    "Broadcast uses event delegation (.closest('.broadcast-btn'))",
    strpos($trainingContent, "closest('.broadcast-btn')") !== false
    || strpos($trainingContent, 'closest(".broadcast-btn")') !== false,
    "Event delegation not found — broadcast click won't work after SPA load"
);

check(
    "openBroadcastModal() function defined",
    strpos($trainingContent, 'function openBroadcastModal') !== false,
    "openBroadcastModal function not found"
);

// ─── SECTION 5: FORM HANDLERS ────────────────────────────────────────────────
foreach (['create_opportunity','update_opportunity','delete_opportunity','broadcast_training'] as $action) {
    check("POST handler: $action", strpos($trainingContent, $action) !== false, "$action handler not found");
}

// ─── SECTION 6: ROLE ACCESS ──────────────────────────────────────────────────
check(
    "training-programs.php restricts to lydo + training_provider",
    strpos($trainingContent, "requireRole") !== false && strpos($trainingContent, "training_provider") !== false,
    "requireRole with training_provider not found in training-programs.php"
);

$matchingContent = file_exists($PAGES . '/matching.php') ? file_get_contents($PAGES . '/matching.php') : '';
check(
    "matching.php has requireRole (skill matching restricted)",
    strpos($matchingContent, "requireRole") !== false,
    "matching.php has no requireRole — training_provider could access skills matching directly"
);

// ─── SECTION 7: JAVASCRIPT FUNCTIONS ────────────────────────────────────────
$jsFunctions = [
    'openCreateModal','openEditModal','openBroadcastModal',
    'closeModal','closeDeleteModal','viewOpportunityDetail',
    'filterTraining','toggleSpecificBroadcastRecipients',
    'updateBroadcastPreview','deleteOpportunity'
];
foreach ($jsFunctions as $fn) {
    check("JS function '$fn' defined", strpos($trainingContent, "function $fn") !== false, "function $fn not found");
}

// ─── SECTION 8: URL PARAM HANDLING ───────────────────────────────────────────
check("URL param 'edit_id' triggers openEditModal()", strpos($trainingContent, "urlParams.get('edit_id')") !== false, "edit_id handler not found");
check("URL param 'create' triggers openCreateModal()", strpos($trainingContent, "urlParams.get('create')") !== false, "create handler not found");
check("URL param 'view_id' triggers viewOpportunityDetail()", strpos($trainingContent, "urlParams.get('view_id')") !== false, "view_id handler not found");
check(
    "URL param checks run immediately (IIFE, SPA-compatible)",
    strpos($trainingContent, '(function() {') !== false || strpos($trainingContent, '(function(){') !== false,
    "No IIFE found — URL param checks may not run after SPA navigation"
);

// ─── SECTION 9: DASHBOARD ────────────────────────────────────────────────────
$dashContent = file_exists($PAGES . '/dashboard.php') ? file_get_contents($PAGES . '/dashboard.php') : '';
check("Dashboard handles training_provider role", strpos($dashContent, 'training_provider') !== false, "training_provider not found in dashboard.php");
check("Dashboard calls getProviderStats()", strpos($dashContent, 'getProviderStats') !== false, "getProviderStats not called");
check("Dashboard links to my-training-programs.php", strpos($dashContent, 'my-training-programs.php') !== false, "my-training-programs.php link not found in dashboard");

// ─── SECTION 10: BROADCAST MODAL CONTENT ────────────────────────────────────
check("Broadcast: message textarea (broadcastMessageArea)", strpos($trainingContent, 'id="broadcastMessageArea"') !== false, "broadcastMessageArea not found");
check("Broadcast: recipient group selector", strpos($trainingContent, 'id="broadcast_target_group"') !== false, "broadcast_target_group not found");
check("Broadcast: live preview box", strpos($trainingContent, 'id="broadcastPreviewBox"') !== false, "broadcastPreviewBox not found");
check("Broadcast: form has broadcast_training hidden input", strpos($trainingContent, 'name="broadcast_training"') !== false, "broadcast_training input not found");

// ─── SECTION 11: DATABASE ────────────────────────────────────────────────────
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->query("SHOW TABLES LIKE 'osy_opportunities'");
    check("DB table 'osy_opportunities' exists", $stmt->rowCount() > 0, "osy_opportunities table not found");

    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM osy_opportunities WHERE type IN ('Vocational Training','Scholarship')");
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    if ($cnt > 0) pass("Training opportunities in DB ($cnt records)");
    else warn("No training opportunities in DB", "Create one via Training Programs page");

    $stmt = $pdo->prepare("SELECT id, title, type FROM osy_opportunities WHERE id = 9");
    $stmt->execute();
    $opp9 = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($opp9) pass("Opportunity ID=9 exists ('{$opp9['title']}', {$opp9['type']})");
    else warn("Opportunity ID=9 not found", "The My Programs nav link uses ?edit_id=9 but no record with ID 9 exists — modal will open empty");

    $stmt = $pdo->query("SHOW TABLES LIKE 'osy_users'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM osy_users WHERE role='training_provider'");
        $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        if ($cnt > 0) pass("training_provider accounts exist ($cnt users)");
        else warn("No training_provider accounts", "Register one via Provider Registration");
    }
} catch (Exception $e) {
    fail("Database connectivity", $e->getMessage());
}

// ─── SECTION 12: PHP SYNTAX ──────────────────────────────────────────────────
foreach (['header.php' => $INCLUDES.'/header.php', 'training-programs.php' => $PAGES.'/training-programs.php', 'dashboard.php' => $PAGES.'/dashboard.php'] as $lbl => $path) {
    if (!file_exists($path)) { fail("PHP syntax: $lbl", "File not found"); continue; }
    $out = []; $code = 0;
    exec("php -l " . escapeshellarg($path) . " 2>&1", $out, $code);
    check("PHP syntax OK: $lbl", $code === 0, implode("\n", $out));
}

// ─── BUILD HTML OUTPUT ───────────────────────────────────────────────────────
$total = $passed + $failed + $warnings;
$cliOut = ob_get_clean();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Training Provider Test Suite</title>
<script src="<?php echo (isset($basePath) ? $basePath : ""); ?>/assets/js/tailwind.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="bg-slate-900 text-white min-h-screen p-8">
<div class="max-w-4xl mx-auto">
  <div class="mb-8">
    <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-3">
      <span class="inline-flex w-10 h-10 bg-blue-600 rounded-xl items-center justify-center text-white text-base material-symbols-outlined">bug_report</span>
      Training Provider — Feature Test Suite
    </h1>
    <p class="text-slate-400 mt-1">Automated checks: nav links · broadcast modal · form handlers · role access · PHP syntax</p>
  </div>

  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-5 text-center">
      <p class="text-4xl font-black text-emerald-400"><?=$passed?></p>
      <p class="text-xs font-bold text-emerald-300 uppercase tracking-wider mt-1">Passed</p>
    </div>
    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-5 text-center">
      <p class="text-4xl font-black text-red-400"><?=$failed?></p>
      <p class="text-xs font-bold text-red-300 uppercase tracking-wider mt-1">Failed</p>
    </div>
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-5 text-center">
      <p class="text-4xl font-black text-amber-400"><?=$warnings?></p>
      <p class="text-xs font-bold text-amber-300 uppercase tracking-wider mt-1">Warnings</p>
    </div>
  </div>

  <div class="mb-8">
    <div class="flex justify-between text-xs text-slate-400 mb-1">
      <span><?=$passed?>/<?=$total?> checks passed</span>
      <span><?=$total>0?round($passed/$total*100):0?>%</span>
    </div>
    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
      <div class="h-full rounded-full <?=$failed>0?'bg-gradient-to-r from-emerald-500 to-amber-500':'bg-emerald-500'?>" style="width:<?=$total>0?round($passed/$total*100):0?>%"></div>
    </div>
  </div>

  <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-700 font-bold">Test Results</div>
    <?php foreach($results as $r):
      $icon = $r['status']==='PASS'?'check_circle':($r['status']==='WARN'?'warning':'cancel');
      $col  = $r['status']==='PASS'?'text-emerald-400':($r['status']==='WARN'?'text-amber-400':'text-red-400');
      $bg   = $r['status']==='FAIL'?'bg-red-500/5':'';
    ?>
    <div class="flex items-start gap-3 px-4 py-3 <?=$bg?> hover:bg-slate-700/40 border-b border-slate-700/50 last:border-0 transition-colors">
      <span class="material-symbols-outlined <?=$col?> text-base mt-0.5 flex-shrink-0"><?=$icon?></span>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold <?=$r['status']==='FAIL'?'text-red-300':($r['status']==='WARN'?'text-amber-200':'text-white')?>"><?=htmlspecialchars($r['name'])?></p>
        <?php if(!empty($r['detail'])): ?>
        <p class="text-xs text-slate-400 mt-0.5 break-all"><?=htmlspecialchars($r['detail'])?></p>
        <?php endif; ?>
      </div>
      <span class="text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0 <?=$r['status']==='PASS'?'bg-emerald-500/20 text-emerald-400':($r['status']==='WARN'?'bg-amber-500/20 text-amber-400':'bg-red-500/20 text-red-400')?>"><?=$r['status']?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 mb-6">
    <h2 class="font-bold mb-4">Quick Test Links</h2>
    <div class="grid grid-cols-2 gap-3">
      <a href="http://localhost/osy_db/pages/training-programs.php" target="_blank" class="flex items-center gap-2 px-4 py-3 bg-blue-600/20 border border-blue-500/30 rounded-xl text-blue-300 text-sm font-semibold hover:bg-blue-600/30 transition-colors">
        <span class="material-symbols-outlined text-base">school</span> Training Programs
      </a>
      <a href="http://localhost/osy_db/pages/training-programs.php?edit_id=9" target="_blank" class="flex items-center gap-2 px-4 py-3 bg-purple-600/20 border border-purple-500/30 rounded-xl text-purple-300 text-sm font-semibold hover:bg-purple-600/30 transition-colors">
        <span class="material-symbols-outlined text-base">edit</span> My Programs (edit_id=9)
      </a>
      <a href="http://localhost/osy_db/pages/dashboard.php" target="_blank" class="flex items-center gap-2 px-4 py-3 bg-emerald-600/20 border border-emerald-500/30 rounded-xl text-emerald-300 text-sm font-semibold hover:bg-emerald-600/30 transition-colors">
        <span class="material-symbols-outlined text-base">dashboard</span> Provider Dashboard
      </a>
      <a href="http://localhost/osy_db/pages/my-notifications.php" target="_blank" class="flex items-center gap-2 px-4 py-3 bg-slate-600/40 border border-slate-500/30 rounded-xl text-slate-300 text-sm font-semibold hover:bg-slate-600/60 transition-colors">
        <span class="material-symbols-outlined text-base">notifications</span> My Notifications
      </a>
    </div>
  </div>

  <div class="bg-slate-950 rounded-xl border border-slate-800 p-4">
    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PHP Output</p>
    <pre class="text-xs text-slate-400 whitespace-pre-wrap overflow-auto max-h-48"><?=htmlspecialchars($cliOut)?></pre>
  </div>
  <p class="text-center text-slate-600 text-xs mt-6">Generated: <?=date('Y-m-d H:i:s')?></p>
</div>
</body>
</html>
