<?php
$pageTitle = 'Skills Matching';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole(['lydo', 'employer', 'training_provider']);

require_once __DIR__ . '/../includes/header.php';

$matching = new Matching($database);
$opportunityObj = new Opportunity($database);
$notification = new Notification($database);
$message = '';
$messageType = '';

function canManageMatchingOpportunity($opportunity): bool
{
    if (!$opportunity) {
        return false;
    }

    return ($_SESSION['role'] ?? '') === 'lydo'
        || ((int) ($opportunity['provider_id'] ?? 0) === (int) ($_SESSION['user_id'] ?? 0));
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
    if (isset($_POST['accept_match'])) {
        $result = $matching->updateMatchStatus(intval($_POST['match_id']), 'Accepted');
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['reject_match'])) {
        $result = $matching->updateMatchStatus(intval($_POST['match_id']), 'Rejected');
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['generate_matches'])) {
        $targetOpportunity = $opportunityObj->getById(intval($_POST['opportunity_id']));
        $result = canManageMatchingOpportunity($targetOpportunity)
            ? $matching->generateMatches(intval($_POST['opportunity_id']))
            : ['success' => false, 'message' => 'You can only generate matches for your own opportunities.'];
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['broadcast_matches'])) {
        $opp_id = intval($_POST['opportunity_id']);
        $opp = $opportunityObj->getById($opp_id);
        if ($opp && canManageMatchingOpportunity($opp)) {
            $message_text = $_POST['custom_message'] ?? 'You have been matched with the opportunity "' . $opp['title'] . '".';
            $send_sms = isset($_POST['send_sms']);
            $send_email = isset($_POST['send_email']);
            
            $matched = $matching->getMatchesForOpportunity($opp_id, 0); 
            
            require_once __DIR__ . '/../Classes/SmsService.php';
            require_once __DIR__ . '/../Classes/EmailService.php';
            $sms = new SmsService($database);
            $email = new EmailService($database);
            
            $sentCount = 0;
            foreach ($matched as $m) {
                if ($m['status'] !== 'Accepted') continue;
                
                $personalMsg = str_replace(['{{name}}', '{{opportunity}}'], [$m['first_name'], $opp['title']], $message_text);
                
                if ($send_sms && !empty($m['phone'])) {
                    $sms->send($m['phone'], $personalMsg);
                }
                if ($send_email && !empty($m['email'])) {
                    $email->send($m['email'], 'Match Alert: ' . $opp['title'], "<p>" . nl2br(htmlspecialchars($personalMsg)) . "</p>");
                }
                $sentCount++;
            }
            
            $result = $notification->create([
                'title' => 'Mass Match Alert: ' . $opp['title'],
                'message' => $message_text,
                'type' => 'Match',
                'recipient_type' => 'OSY' // Track it as an OSY notification broadly
            ]);
            $message = "Broadcast deployed to $sentCount accepted candidates successfully!";
            $messageType = 'success';
        } elseif (!$opp) {
            $message = 'Opportunity not found.';
            $messageType = 'error';
        } else {
            $message = 'You can only send match notifications for your own opportunities.';
            $messageType = 'error';
        }
    }
    }
}

// Fetch Templates
$templates = $notification->getAllTemplates();

$filters = [
    'opportunity_id' => isset($_GET['opportunity_id']) ? intval($_GET['opportunity_id']) : null,
    'min_score' => isset($_GET['min_score']) ? intval($_GET['min_score']) : 75,
];
$openOpportunities = $_SESSION['role'] === 'lydo'
    ? $opportunityObj->getAll(['status' => 'Open'])
    : array_values(array_filter($opportunityObj->getByProvider($_SESSION['user_id']), fn($opp) => $opp['status'] === 'Open'));
$selectedOpportunityId = $filters['opportunity_id'] ?? ($openOpportunities[0]['id'] ?? null);
$selectedOpportunity = $selectedOpportunityId ? $opportunityObj->getById($selectedOpportunityId) : null;
if (!canManageMatchingOpportunity($selectedOpportunity)) {
    $selectedOpportunity = null;
    $selectedOpportunityId = null;
}
$minScore = $filters['min_score'];
?>

<!-- Page Header -->
<div>
    <nav class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 font-medium mb-2">
        <a href="dashboard.php" class="hover:text-blue-900 transition-colors">Municipal KK</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
    </nav>
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Skills Alignment Analysis</h1>
        
        <?php 
            $syncStats = $matching->getGlobalSyncStats(); 
            if ($syncStats['missing_matches'] > 0):
        ?>
            <div id="syncAlert" class="flex items-center gap-3 px-4 py-2 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
                <span class="material-symbols-outlined text-amber-600">warning</span>
                <span><strong>Data Gap:</strong> <?php echo $syncStats['missing_matches']; ?> potential matches are missing AI scores.</span>
                <button onclick="triggerGlobalSync()" class="ml-2 px-3 py-1 bg-amber-600 text-white rounded font-bold hover:bg-amber-700 transition-colors">Sync All Now</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
<div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
    <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
        <span class="material-symbols-outlined text-base"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
        <?php echo htmlspecialchars($message); ?>
    </p>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch mb-10">
    <div class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-800 p-8 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <span class="material-symbols-outlined text-8xl">verified</span>
        </div>
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-400 font-bold text-[10px] tracking-widest uppercase rounded-full mb-4">
                Active Opportunity
            </span>
            <h1 id="heroTitle" class="text-3xl font-extrabold tracking-tight text-blue-900 dark:text-white mb-2"><?php echo htmlspecialchars($selectedOpportunity['title'] ?? 'No active opportunity selected'); ?></h1>
            <p id="heroDesc" class="text-slate-600 dark:text-slate-300 max-w-xl mb-6"><?php echo htmlspecialchars($selectedOpportunity['description'] ?? 'Select a live opportunity from the panel to see matched OSY candidates and score breakdowns.'); ?></p>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
                    <span class="material-symbols-outlined text-blue-900 dark:text-blue-400 text-xl">location_on</span>
                    <span id="heroLocation" class="font-semibold text-sm text-slate-900 dark:text-white"><?php echo htmlspecialchars($selectedOpportunity['location'] ?? 'No location'); ?></span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
                    <span class="material-symbols-outlined text-blue-900 dark:text-blue-400 text-xl">schedule</span>
                    <span id="heroDeadline" class="font-semibold text-sm text-slate-900 dark:text-white"><?php echo !empty($selectedOpportunity['deadline']) ? date('M d, Y', strtotime($selectedOpportunity['deadline'])) : 'No deadline'; ?></span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
                    <span class="material-symbols-outlined text-blue-900 dark:text-blue-400 text-xl">people</span>
                    <span id="heroMatchCount" class="font-semibold text-sm text-slate-900 dark:text-white"><span id="totalMatchesCount">0</span> candidates found</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Matching Parameters -->
    <div class="rounded-xl bg-blue-900 text-white p-8 shadow-xl flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-xl mb-6">Matching Parameters</h3>
                <div>
                    <label class="block text-sm font-medium opacity-90 mb-3">Target Opportunity</label>
                    <select id="oppSelect" onchange="handleOpportunityChange(this.value)" class="w-full bg-blue-800/20 border border-blue-700 rounded-xl py-3 px-4 text-sm text-white focus:ring-2 focus:ring-blue-300 transition-all cursor-pointer">
                        <option value="">-- Choose Opportunity --</option>
                        <?php foreach ($openOpportunities as $opportunityList): ?>
                            <option value="<?php echo $opportunityList['id']; ?>" <?php echo $selectedOpportunityId == $opportunityList['id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($opportunityList['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-end">
                        <label class="text-sm font-medium opacity-90">Minimum Match Score</label>
                        <span id="scoreDisplay" class="text-2xl font-black"><?php echo $minScore; ?>%</span>
                    </div>
                    <input type="range" name="min_score" min="0" max="100" value="<?php echo $minScore; ?>" class="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer accent-white" oninput="updateScoreFilter(this.value)" />
                </div>
        </div>
        <div class="flex flex-col gap-3 mt-6">
            <?php if ($selectedOpportunityId): ?>
            <button type="button" id="broadcastBtn" onclick="document.getElementById('broadcastModal').classList.remove('hidden')" class="w-full py-3 bg-white text-blue-900 font-bold rounded-xl flex items-center justify-center gap-2 hover:bg-slate-100 active:scale-[0.98] transition-all focus:outline-none">
                <span class="material-symbols-outlined">send</span>
                Broadcast to Shortlisted
            </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Broadcast Modal -->
<div id="broadcastModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 rounded-t-2xl">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2"><span class="material-symbols-outlined text-blue-600">campaign</span> Configure Broadcast</h3>
            <p class="text-sm text-slate-500 mt-1">Send alerts specifically to candidates you have <span class="font-bold text-green-600">Accepted/Shortlisted</span> for <span id="broadcastOppName"><?php echo htmlspecialchars($selectedOpportunity['title'] ?? ''); ?></span>.</p>
        </div>
            <form method="POST" class="p-6 space-y-6">
            <input type="hidden" name="opportunity_id" id="broadcastOppId" value="<?php echo $selectedOpportunityId ?? ''; ?>">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Choose a Template (Optional)</label>
                    <select id="templateSelect" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" onchange="applyTemplate()">
                        <option value="">-- Custom Message --</option>
                        <?php foreach ($templates as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['body']); ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <div class="flex justify-between mb-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">Message Body</label>
                        <span class="text-xs text-slate-500">Variables: {{name}}, {{opportunity}}</span>
                    </div>
                    <textarea id="customMessageArea" name="custom_message" rows="5" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white font-mono" onkeyup="updateLivePreview()">You have been shortlisted for the opportunity "{{opportunity}}". Please prepare for next steps.</textarea>
                </div>
            </div>

            <!-- Live Preview Area -->
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                <p class="text-xs font-bold text-blue-800 dark:text-blue-400 mb-2 uppercase tracking-wide">Live Preview Example</p>
                <div id="livePreviewBox" class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap"></div>
            </div>

            <!-- Delivery Methods -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">External Delivery Hooks</h4>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_sms" class="w-4 h-4 text-blue-900 bg-slate-100 border-slate-300 rounded focus:ring-blue-900" checked>
                        <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">Auto-send via Traccar SMS</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="send_email" class="w-4 h-4 text-blue-900 bg-slate-100 border-slate-300 rounded focus:ring-blue-900" checked>
                        <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">Auto-send via SMTP Email</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700 mt-6">
                <button type="button" onclick="document.getElementById('broadcastModal').classList.add('hidden')" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                    Cancel
                </button>
                <button type="submit" name="broadcast_matches" value="1" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 shadow-md">
                    Execute Mass Broadcast
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    // Preview Logic Script
    window.sampleName = "<?php echo !empty($acceptedMatches) ? addslashes($acceptedMatches[0]['first_name']) : 'John'; ?>";
    window.oppName = "<?php echo addslashes($selectedOpportunity['title'] ?? 'Sample Job'); ?>";
    
    window.applyTemplate = function() {
        const sel = document.getElementById('templateSelect');
        if (sel.value) {
            document.getElementById('customMessageArea').value = sel.value;
        }
        updateLivePreview();
    }
    
    window.updateLivePreview = function() {
        let text = document.getElementById('customMessageArea').value;
        text = text.replace(/{{name}}/g, sampleName);
        text = text.replace(/{{opportunity}}/g, oppName);
        document.getElementById('livePreviewBox').textContent = text;
    }
    
    // Initial run
    if (currentOpportunityId) { loadMatchesAJAX(currentOpportunityId); }
    updateLivePreview();
})();
</script>




<section class="mb-10">
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-700 dark:text-green-400 shadow-sm">
                <span class="material-symbols-outlined">work</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Employment Pipeline</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Real-time candidate sorting and shortlisting.</p>
            </div>
        </div>
        <span class="text-sm font-bold text-slate-500 bg-slate-100 py-1 px-3 rounded-full"><span id="totalMatchesCount">0</span> total matches</span>
    </div>

    <!-- Kanban Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- LEFT COLUMN: SHORTLISTED / REJECTED TABS -->
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 h-[800px] flex flex-col relative">
            <div class="flex border-b border-slate-200 dark:border-slate-700 mb-4">
                <button onclick="switchTab('shortlisted')" id="tabShortlisted" class="flex-1 py-2 text-sm font-bold border-b-2 border-blue-600 text-blue-600">Shortlisted (<span id="pendingCount">0</span>)</button>
                <button onclick="switchTab('rejected')" id="tabRejected" class="flex-1 py-2 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300">Rejected (<span id="rejectedCount">0</span>)</button>
            </div>

            
            <div id="colPending" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
            </div>
            <div id="colRejected" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4 hidden"></div>
        </div>

        <!-- RIGHT COLUMN: ACCEPTED -->
        <div class="bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl p-4 border border-blue-100 dark:border-blue-800/50 h-[800px] flex flex-col">
            <div class="flex justify-between items-center mb-4 px-2">
                <h3 class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
                    Accepted
                </h3>
                <span id="acceptedCount" class="text-xs font-bold text-blue-700 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded-full">0</span>
            </div>
            
            <div id="colAccepted" class="flex-1 overflow-y-auto space-y-4 pr-2 pb-4">
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
                <div class="skeleton-pulse h-32 w-full rounded-xl"></div>
            </div>
        </div>
    </div>
</section>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Candidate Details</h3>
            <button onclick="document.getElementById('detailsModal').classList.add('hidden')" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="block text-slate-500 text-xs">Name</span> <span id="mdlName" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div><span class="block text-slate-500 text-xs">Age</span> <span id="mdlAge" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div><span class="block text-slate-500 text-xs">Gender</span> <span id="mdlGender" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div><span class="block text-slate-500 text-xs">Barangay</span> <span id="mdlBarangay" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div class="col-span-2"><span class="block text-slate-500 text-xs">Educational Attainment</span> <span id="mdlEdu" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div class="col-span-2"><span class="block text-slate-500 text-xs">Primary Skill</span> <span id="mdlPrimarySkill" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div class="col-span-2"><span class="block text-slate-500 text-xs">Other Skills</span> <span id="mdlSkills" class="font-bold text-slate-900 dark:text-white"></span></div>
                <div class="col-span-2"><span class="block text-slate-500 text-xs">Interests</span> <span id="mdlInterests" class="font-bold text-slate-900 dark:text-white"></span></div>
            </div>
        </div>
    </div>
</div>



<!-- Real-time Logic -->
<script>
(function() {
    window.switchTab = function(tab) {
        if (tab === 'shortlisted') {
            document.getElementById('tabShortlisted').className = 'flex-1 py-2 text-sm font-bold border-b-2 border-blue-600 text-blue-600';
            document.getElementById('tabRejected').className = 'flex-1 py-2 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300';
            document.getElementById('colPending').classList.remove('hidden');
            document.getElementById('colRejected').classList.add('hidden');
        } else {
            document.getElementById('tabRejected').className = 'flex-1 py-2 text-sm font-bold border-b-2 border-red-600 text-red-600';
            document.getElementById('tabShortlisted').className = 'flex-1 py-2 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300';
            document.getElementById('colRejected').classList.remove('hidden');
            document.getElementById('colPending').classList.add('hidden');
        }
    }

    window.showDetailsModal = function(match) {
        document.getElementById('mdlName').textContent = match.first_name + ' ' + match.last_name;
        document.getElementById('mdlAge').textContent = match.age || 'N/A';
        document.getElementById('mdlGender').textContent = match.gender || 'N/A';
        document.getElementById('mdlBarangay').textContent = match.barangay || 'N/A';
        document.getElementById('mdlEdu').textContent = match.education_level || 'N/A';
        document.getElementById('mdlPrimarySkill').textContent = match.primary_skill || 'N/A';
        document.getElementById('mdlSkills').textContent = match.skills || 'N/A';
        document.getElementById('mdlInterests').textContent = match.interests || 'N/A';
        document.getElementById('detailsModal').classList.remove('hidden');
    }

    window.currentOpportunityId = <?php echo $selectedOpportunityId ?: 'null'; ?>;
    window.currentOpportunityTitle = "<?php echo addslashes($selectedOpportunity['title'] ?? ''); ?>";

    window.handleOpportunityChange = function(id) {
        if (!id) return;
        window.currentOpportunityId = id;
        
        // Update URL without reload for state persistence
        const url = new URL(window.location);
        url.searchParams.set('opportunity_id', id);
        window.history.pushState({}, '', url);

        loadMatchesAJAX(id);
    }

    async window.loadMatchesAJAX = async function(id) {
        const colPending = document.getElementById('colPending');
        const colRejected = document.getElementById('colRejected');
        const colAccepted = document.getElementById('colAccepted');
        
        // Loading state
        colPending.style.opacity = '0.5';
        if (colRejected) colRejected.style.opacity = '0.5';
        colAccepted.style.opacity = '0.5';
        
        try {
            const res = await fetch(`../api/get_opportunity_matches.php?opportunity_id=${id}`);
            const result = await res.json();
            
            if (!result.success || !Array.isArray(result.data)) {
                console.error('Failed to load matches or malformed response', result);
                return;
            }
            
            // Update Hero information if present
            if (result.opportunity) {
                document.getElementById('heroTitle').textContent = result.opportunity.title;
                document.getElementById('heroDesc').textContent = result.opportunity.description;
                document.getElementById('heroLocation').textContent = result.opportunity.location;
                document.getElementById('heroDeadline').textContent = result.opportunity.deadline ? new Date(result.opportunity.deadline).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No deadline';
                document.getElementById('heroMatchCount').textContent = result.data.length + ' candidates found';

    document.getElementById('totalMatchesCount').textContent = result.data.length;
    document.getElementById('pendingCount').textContent = result.data.filter(m => m.status === 'Pending').length;
    const rejEl = document.getElementById('rejectedCount'); if (rejEl) rejEl.textContent = result.data.filter(m => m.status === 'Rejected').length;
    document.getElementById('acceptedCount').textContent = result.data.filter(m => m.status === 'Accepted').length;
    
    // Check if initial load
    if (!window.initialMatchLoaded && currentOpportunityId) {
        window.initialMatchLoaded = true;
    }

                // Broadcast info
                const bOppName = document.getElementById('broadcastOppName');
                const bOppId = document.getElementById('broadcastOppId');
                if (bOppName) bOppName.textContent = result.opportunity.title;
                if (bOppId) bOppId.value = result.opportunity.id;
                window.oppName = result.opportunity.title;
                updateLivePreview();
            }
            
            // Clear columns
            colPending.innerHTML = result.data.filter(m => m.status === 'Pending').length ? '' : '<p id="pendingEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No shortlisted candidates.</p>';
            if (colRejected) colRejected.innerHTML = result.data.filter(m => m.status === 'Rejected').length ? '' : '<p id="rejectedEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No rejected candidates.</p>';
            colAccepted.innerHTML = result.data.filter(m => m.status === 'Accepted').length ? '' : '<p id="acceptedEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No candidates accepted yet.</p>';
            
            result.data.forEach(match => {
                const html = renderMatchCardJS(match, match.status);
                if (match.status === 'Pending') {
                    colPending.insertAdjacentHTML('beforeend', html);
                } else if (match.status === 'Rejected' && colRejected) {
                    colRejected.insertAdjacentHTML('beforeend', html);
                } else if (match.status === 'Accepted') {
                    colAccepted.insertAdjacentHTML('beforeend', html);
                }
            });
            
            // Update broadcast button visibility
            const broadcastBtn = document.getElementById('broadcastBtn');
            if (broadcastBtn) broadcastBtn.style.display = 'flex';
            
            // Refresh count and score filter
            const scoreInput = document.querySelector('input[name="min_score"]');
            if (scoreInput) updateScoreFilter(scoreInput.value);
        } catch (e) {
            console.error('Failed to load matches:', e);
        } finally {
            colPending.style.opacity = '1';
            colAccepted.style.opacity = '1';
        }
    }

        try {
            const res = await fetch(`../api/get_opportunity_matches.php?opportunity_id=${id}`);
            const result = await res.json();
            
            if (result.success) {
                // Update Hero information
                if (result.opportunity) {
                    document.getElementById('heroTitle').textContent = result.opportunity.title;
                    document.getElementById('heroDesc').textContent = result.opportunity.description;
                    document.getElementById('heroLocation').textContent = result.opportunity.location;
                    document.getElementById('heroDeadline').textContent = result.opportunity.deadline ? new Date(result.opportunity.deadline).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No deadline';
                    document.getElementById('heroMatchCount').textContent = result.data.length + ' candidates found';

    document.getElementById('totalMatchesCount').textContent = result.data.length;
    document.getElementById('pendingCount').textContent = result.data.filter(m => m.status === 'Pending').length;
    const rejEl = document.getElementById('rejectedCount'); if (rejEl) rejEl.textContent = result.data.filter(m => m.status === 'Rejected').length;
    document.getElementById('acceptedCount').textContent = result.data.filter(m => m.status === 'Accepted').length;
    
    // Check if initial load
    if (!window.initialMatchLoaded && currentOpportunityId) {
        window.initialMatchLoaded = true;
    }

                    
                    // Update Broadcast info
                    const bOppName = document.getElementById('broadcastOppName');
                    const bOppId = document.getElementById('broadcastOppId');
                    if (bOppName) bOppName.textContent = result.opportunity.title;
                    if (bOppId) bOppId.value = result.opportunity.id;
                    
                    // Update global JS variable for preview
                    window.oppName = result.opportunity.title;
                    updateLivePreview();
                }

                // Clear columns
                colPending.innerHTML = result.data.filter(m => m.status === 'Pending').length ? '' : '<p id="pendingEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No shortlisted candidates.</p>';
                if(colRejected) colRejected.innerHTML = result.data.filter(m => m.status === 'Rejected').length ? '' : '<p id="rejectedEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No rejected candidates.</p>';
                colAccepted.innerHTML = result.data.filter(m => m.status === 'Accepted').length ? '' : '<p id="acceptedEmptyMsg" class="text-xs text-center text-slate-400 mt-10">No candidates accepted yet.</p>';
                
                result.data.forEach(match => {
                    const html = renderMatchCardJS(match, match.status);
                    if (match.status === 'Pending') {
                        colPending.insertAdjacentHTML('beforeend', html);
                    } else if (match.status === 'Rejected' && colRejected) {
                        colRejected.insertAdjacentHTML('beforeend', html);
                    } else if (match.status === 'Accepted') {
                        colAccepted.insertAdjacentHTML('beforeend', html);
                    }
                });
                
                // Update broadcast button and modal info
                const broadcastBtn = document.getElementById('broadcastBtn');
                if (broadcastBtn) broadcastBtn.style.display = 'flex';
                
                // Refresh count and score filter
                updateScoreFilter(document.querySelector('input[name="min_score"]').value);
            }
        } catch (e) {
            console.error('Failed to load matches:', e);
        } finally {
            colPending.style.opacity = '1';
            colAccepted.style.opacity = '1';
        }
    }

    window.renderMatchCardJS = function(match, status) {
        if (!match) return '';
        const scoreClass = match.match_score >= 85 ? 'text-green-600' : (match.match_score >= 70 ? 'text-yellow-600' : 'text-orange-600');
        const firstLetter = match.first_name ? match.first_name.charAt(0).toUpperCase() : '?';
        const matchData = JSON.stringify(match).replace(/"/g, '&quot;');
        
        let actionBtns = '';
        if (status === 'Pending') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Accepted')" class="flex-1 py-1.5 px-3 bg-green-50 text-green-700 hover:bg-green-600 hover:text-white border border-green-200 hover:border-green-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">how_to_reg</span> Accept
                </button>
                <button onclick="updateMatchStatus(${match.id}, 'Rejected')" class="flex-1 py-1.5 px-3 bg-slate-50 text-slate-600 hover:bg-red-500 hover:text-white border border-slate-200 hover:border-red-500 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">cancel</span> Reject
                </button>
            `;
        } else if (status === 'Rejected') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Accepted')" class="flex-1 py-1.5 px-3 bg-green-50 text-green-700 hover:bg-green-600 hover:text-white border border-green-200 hover:border-green-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">how_to_reg</span> Accept
                </button>
            `;
        } else if (status === 'Accepted') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Pending')" class="flex-1 py-1.5 px-3 bg-orange-50 text-orange-700 hover:bg-orange-600 hover:text-white border border-orange-200 hover:border-orange-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">undo</span> Disapprove
                </button>
            `;
        }
        
        const actionHtml = `
            <div id="actionBtns-${match.id}" class="flex gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button onclick="showDetailsModal(${matchData})" class="flex-1 py-1.5 px-3 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">visibility</span> Details
                </button>
                ${actionBtns}
            </div>
        `;
        
        return `
            <div id="matchCard-${match.id}" class="match-card bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-300 relative overflow-hidden group" data-score="${match.match_score}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-base shadow-sm">
                            ${firstLetter}
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">${match.first_name} ${match.last_name}</h3>
                            <p class="text-[11px] text-slate-500 font-medium tracking-wide uppercase mt-0.5">${match.primary_skill}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black ${scoreClass} tracking-tighter">${match.match_score}%</div>
                    </div>
                </div>
                ${actionHtml}
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button onclick="analyzeMatchAI(${match.id})" id="aiBtn-${match.id}" class="w-full py-2 px-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-600 hover:text-white border border-blue-200 dark:border-blue-800 rounded-lg text-[11px] font-bold transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px] animate-pulse">auto_awesome</span> ${match.ai_insight ? 'View AI Rationale' : 'Analyze with AI'}
                    </button>
                    <div id="aiInsight-${match.id}" class="${match.ai_insight ? '' : 'hidden'} mt-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border-l-4 border-blue-5... (truncated)
        const scoreClass = match.match_score >= 85 ? 'text-green-600' : (match.match_score >= 70 ? 'text-yellow-600' : 'text-orange-600');
        const firstLetter = match.first_name ? match.first_name.charAt(0).toUpperCase() : '?';
        const matchData = JSON.stringify(match).replace(/"/g, '&quot;');
        
        let actionBtns = '';
        if (status === 'Pending') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Accepted')" class="flex-1 py-1.5 px-3 bg-green-50 text-green-700 hover:bg-green-600 hover:text-white border border-green-200 hover:border-green-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">how_to_reg</span> Accept
                </button>
                <button onclick="updateMatchStatus(${match.id}, 'Rejected')" class="flex-1 py-1.5 px-3 bg-slate-50 text-slate-600 hover:bg-red-500 hover:text-white border border-slate-200 hover:border-red-500 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">cancel</span> Reject
                </button>
            `;
        } else if (status === 'Rejected') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Accepted')" class="flex-1 py-1.5 px-3 bg-green-50 text-green-700 hover:bg-green-600 hover:text-white border border-green-200 hover:border-green-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">how_to_reg</span> Accept
                </button>
            `;
        } else if (status === 'Accepted') {
            actionBtns = `
                <button onclick="updateMatchStatus(${match.id}, 'Pending')" class="flex-1 py-1.5 px-3 bg-orange-50 text-orange-700 hover:bg-orange-600 hover:text-white border border-orange-200 hover:border-orange-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">undo</span> Disapprove
                </button>
            `;
        }

        let actionHtml = `
            <div id="actionBtns-${match.id}" class="flex gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button onclick="showDetailsModal(${matchData})" class="flex-1 py-1.5 px-3 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                    <span class="material-symbols-outlined text-[16px]">visibility</span> Details
                </button>
                ${actionBtns}
            </div>
        `;

        return `
            <div id="matchCard-${match.id}" class="match-card bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-300 relative overflow-hidden group"
                 data-score="${match.match_score}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-bold text-base shadow-sm">
                            ${firstLetter}
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">${match.first_name} ${match.last_name}</h3>
                            <p class="text-[11px] text-slate-500 font-medium tracking-wide uppercase mt-0.5">${match.primary_skill}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black ${scoreClass} tracking-tighter">${match.match_score}%</div>
                    </div>
                </div>
                ${actionHtml}

                <!-- AI Insight Section -->
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button onclick="analyzeMatchAI(${match.id})" id="aiBtn-${match.id}" class="w-full py-2 px-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-600 hover:text-white border border-blue-200 dark:border-blue-800 rounded-lg text-[11px] font-bold transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px] animate-pulse">auto_awesome</span> 
                        ${match.ai_insight ? 'View AI Rationale' : 'Analyze with AI'}
                    </button>
                    <div id="aiInsight-${match.id}" class="${match.ai_insight ? '' : 'hidden'} mt-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border-l-4 border-blue-500 text-[12px] text-slate-600 dark:text-slate-300 italic leading-relaxed">
                        ${match.ai_insight || ''}
                    </div>
                </div>
            </div>
        `;
    }

    function updateScoreFilter(val) {
        document.getElementById('scoreDisplay').textContent = val + '%';
        
        const threshold = parseInt(val);
        const cards = document.querySelectorAll('.match-card');
        
        cards.forEach(card => {
            const score = parseInt(card.getAttribute('data-score'));
            if (score < threshold) {
                card.style.display = 'none';
            } else {
                card.style.display = 'block';
            }
        });
        
        updateCounts();
    }

    function updateCounts() {
        const pendingVisible = document.querySelectorAll('#colPending .match-card:not([style*="display: none"])');
        const rejectedVisible = document.querySelectorAll('#colRejected .match-card:not([style*="display: none"])');
        const acceptedVisible = document.querySelectorAll('#colAccepted .match-card:not([style*="display: none"])');
        
        if (document.getElementById('pendingCount')) document.getElementById('pendingCount').textContent = pendingVisible.length;
        if (document.getElementById('rejectedCount')) document.getElementById('rejectedCount').textContent = rejectedVisible.length;
        if (document.getElementById('acceptedCount')) document.getElementById('acceptedCount').textContent = acceptedVisible.length;
        
        const pMsg = document.getElementById('pendingEmptyMsg');
        const rMsg = document.getElementById('rejectedEmptyMsg');
        const aMsg = document.getElementById('acceptedEmptyMsg');
        
        if(pMsg) pMsg.style.display = pendingVisible.length ? 'none' : 'block';
        if(rMsg) rMsg.style.display = rejectedVisible.length ? 'none' : 'block';
        if(aMsg) aMsg.style.display = acceptedVisible.length ? 'none' : 'block';
    }
    // Update match status (accept/reject/pending) with UI feedback
    async function updateMatchStatus(matchId, status) {
        const card = document.getElementById('matchCard-' + matchId);
        const actionBtns = document.getElementById('actionBtns-' + matchId);
        if (card) {
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
        }
        try {
            const res = await fetch('../api/update_match_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ match_id: matchId, status: status })
            });
            const data = await res.json();
            if (data.success) {
                if (actionBtns) actionBtns.remove();
                if (card) {
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => { loadMatchesAJAX(currentOpportunityId); }, 300);
                }
            } else {
                customAlert('Failed to update status: ' + data.message, "Error", "error");
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            }
        } catch (e) {
            customAlert('Network error. Please try again.', "Error", "error");
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        }
    }

    async function analyzeMatchAI(matchId) {
        const btn = document.getElementById('aiBtn-' + matchId);
        const insightBox = document.getElementById('aiInsight-' + matchId);
        
        if (!insightBox.classList.contains('hidden') && insightBox.textContent.trim() !== '') {
            insightBox.classList.add('hidden');
            return;
        }

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Analyzing...';
        
        try {
            const res = await fetch('../api/ai_analyze_match.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ match_id: matchId })
            });
            const data = await res.json();
            
            if (data.success) {
                insightBox.textContent = data.insight;
                insightBox.classList.remove('hidden');
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">auto_awesome</span> View AI Rationale';
            } else {
                customAlert('AI Analysis failed: ' + data.message, "Error", "error");
                btn.innerHTML = originalHtml;
            }
        } catch (e) {
            customAlert('Network error. Could not reach AI service.', "Error", "error");
            btn.innerHTML = originalHtml;
        } finally {
            btn.disabled = false;
        }
    }

    async function triggerGlobalSync() {
        customConfirm("This will start a background process to calculate AI scores for all profiles against all jobs. This may take several minutes. Proceed?", async (confirmed) => {
            if (!confirmed) return;
            
            const alertBox = document.getElementById('syncAlert');
            try {
                const res = await fetch('../api/trigger_global_sync.php');
                const data = await res.json();
                if (data.success) {
                    alertBox.innerHTML = `<span class="material-symbols-outlined text-blue-600 animate-spin">sync</span> 
                                      <span class="text-blue-800">Background Sync Started. Scores will populate automatically over the next few minutes.</span>`;
                    alertBox.className = "flex items-center gap-3 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm";
                } else {
                    customAlert(data.message, "Error", "error");
                }
            } catch (e) {
                customAlert("Failed to trigger sync.", "Error", "error");
            }
        });
    }
})();
</script>

<style>.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; } .dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; } @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
