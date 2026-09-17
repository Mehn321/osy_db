<?php
$pageTitle = 'Opportunity Applications';
require_once __DIR__ . '/../init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only the listing owner or LYDO may view applicants or make decisions.
requireRole(['training_provider', 'employer', 'lydo']);

// Get opportunity ID from query string
$opportunityId = isset($_GET['opportunity_id']) && ctype_digit($_GET['opportunity_id'])
    ? (int)$_GET['opportunity_id']
    : 0;
if ($opportunityId <= 0) {
    die('Invalid opportunity ID');
}

// Handle status updates (Accept / Reject) submitted via POST
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $updateMessage = 'Invalid form submission.';
    } else {
        $matchId   = intval($_POST['match_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        if ($matchId > 0 && in_array($newStatus, ['Accepted', 'Rejected'])) {
            $matching = new Matching($database);
            $result   = $matching->updateMatchStatus($matchId, $newStatus);
            $updateMessage = $result['message'];
        } else {
            $updateMessage = 'Invalid data provided.';
        }
    }
}

// Fetch opportunity details (optional, for header)
$opportunity = $database->fetchOne('SELECT * FROM opportunities WHERE id = ?', [$opportunityId], 'i');
if (!$opportunity) {
    die('Opportunity not found');
}
if ($_SESSION['role'] !== 'lydo' && (int) $opportunity['provider_id'] !== (int) $_SESSION['user_id']) {
    http_response_code(403);
    exit('Access denied. You can only view applications for your own opportunities.');
}

// Retrieve applications query removed, using AJAX now
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Applications for "<?= htmlspecialchars($opportunity['title']) ?>"</h1>

    <?php if (!empty($updateMessage)): ?>
        <div class="p-4 mb-4 <?= strpos($updateMessage, 'success') !== false ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' ?> rounded">
            <p class="text-sm <?= strpos($updateMessage, 'success') !== false ? 'text-green-800' : 'text-red-800' ?>">
                <?= htmlspecialchars($updateMessage) ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Client-side Filters -->
    <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-lg shadow border border-slate-200 dark:border-slate-700 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Search Applicants</label>
            <input type="text" id="applicant_search" placeholder="Search by name, email, phone or skill..."
                class="w-full bg-slate-100 dark:bg-slate-700 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Filter by Status</label>
            <select id="applicant_status"
                class="bg-slate-100 dark:bg-slate-700 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto bg-white dark:bg-slate-800 rounded-lg overflow-hidden shadow">
            <thead class="bg-gray-50 dark:bg-slate-700">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Applicant</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Age</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Primary Skill</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Status</th>
                    <?php if ($_SESSION['role'] !== 'youth'): ?>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="applicationsTableBody">
                <?php for ($i=0; $i<4; $i++): ?>
                <tr class="skeleton-row border-b border-gray-200 dark:border-slate-700">
                    <td class="px-4 py-2"><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-48 rounded"></div></td>
                    <td class="px-4 py-2 text-center"><div class="skeleton-pulse h-4 w-8 rounded mx-auto"></div></td>
                    <td class="px-4 py-2"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                    <td class="px-4 py-2"><div class="skeleton-pulse h-6 w-16 rounded"></div></td>
                    <?php if ($_SESSION['role'] !== 'youth'): ?>
                    <td class="px-4 py-2 flex justify-center gap-2"><div class="skeleton-pulse h-6 w-12 rounded"></div><div class="skeleton-pulse h-6 w-12 rounded"></div></td>
                    <?php endif; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        <a href="opportunities.php" class="text-blue-600 hover:underline">← Back to Opportunities</a>
    </div>
</div>

<script>
(function() {
    const oppId = <?php echo $opportunityId; ?>;
    const isYouth = <?php echo $_SESSION['role'] === 'youth' ? 'true' : 'false'; ?>;
    const csrfToken = "<?php echo htmlspecialchars(getCsrfToken()); ?>";
    const formNonce = "<?php echo htmlspecialchars(getFormNonce()); ?>";
    let allApps = [];

    function renderApps() {
        const search = document.getElementById('applicant_search').value.toLowerCase();
        const status = document.getElementById('applicant_status').value;
        const tbody = document.getElementById('applicationsTableBody');
        
        const filtered = allApps.filter(app => {
            if (status !== 'All' && app.status !== status) return false;
            if (search) {
                const text = `${app.first_name} ${app.last_name} ${app.email} ${app.phone} ${app.primary_skill}`.toLowerCase();
                if (!text.includes(search)) return false;
            }
            return true;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${isYouth ? 4 : 5}" class="px-4 py-8 text-center text-gray-500">No applications match your criteria.</td></tr>`;
            return;
        }

        let html = '';
        filtered.forEach(app => {
            const statusClass = app.status === 'Accepted' ? 'bg-emerald-100 text-emerald-800' : (app.status === 'Rejected' ? 'bg-rose-100 text-rose-800' : 'bg-yellow-100 text-yellow-800');
            
            let actions = '—';
            if (!isYouth && app.status === 'Pending') {
                actions = `
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="form_nonce" value="${formNonce}">
                    <input type="hidden" name="match_id" value="${app.id}">
                    <input type="hidden" name="new_status" value="Accepted">
                    <button type="submit" class="px-2 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-xs">Accept</button>
                </form>
                <form method="POST" class="inline m-0">
                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                    <input type="hidden" name="form_nonce" value="${formNonce}">
                    <input type="hidden" name="match_id" value="${app.id}">
                    <input type="hidden" name="new_status" value="Rejected">
                    <button type="submit" class="px-2 py-1 bg-rose-600 text-white rounded hover:bg-rose-700 text-xs">Reject</button>
                </form>`;
            }

            html += `
            <tr class="border-b border-gray-200 dark:border-slate-700">
                <td class="px-4 py-2">
                    ${app.first_name} ${app.last_name}<br>
                    <span class="text-sm text-gray-500 dark:text-gray-400">${app.email} | ${app.phone}</span>
                </td>
                <td class="px-4 py-2 text-center">${app.age || '-'}</td>
                <td class="px-4 py-2">${app.primary_skill || '-'}</td>
                <td class="px-4 py-2">
                    <span class="inline-block px-2 py-1 text-xs rounded ${statusClass}">${app.status}</span>
                </td>
                ${!isYouth ? `<td class="px-4 py-2 text-center">${actions}</td>` : ''}
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    fetch(`../api/get_matching_data.php?opportunity_id=${oppId}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.matches) {
                allApps = res.matches;
                renderApps();
            } else {
                document.getElementById('applicationsTableBody').innerHTML = `<tr><td colspan="${isYouth ? 4 : 5}" class="px-4 py-8 text-center text-red-500">Failed to load data.</td></tr>`;
            }
        })
        .catch(() => {
            document.getElementById('applicationsTableBody').innerHTML = `<tr><td colspan="${isYouth ? 4 : 5}" class="px-4 py-8 text-center text-red-500">Network error.</td></tr>`;
        });

    document.getElementById('applicant_search').addEventListener('input', renderApps);
    document.getElementById('applicant_status').addEventListener('change', renderApps);
})();
</script>

<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
