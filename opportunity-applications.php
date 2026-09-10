<?php
$pageTitle = 'Opportunity Applications';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../includes/rate_limit.php'; // rate limiting helper

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure only providers, LYDO, or SK Chairman can access this page
requireRole(['training_provider', 'employer', 'lydo', 'sk_chairman']);

// Get opportunity ID from query string
$opportunityId = isset($_GET['opportunity_id']) && ctype_digit($_GET['opportunity_id'])
    ? (int)$_GET['opportunity_id']
    : 0;
if ($opportunityId <= 0) {
    die('Invalid opportunity ID');
}

/**
 * Handle single status updates (Accept / Reject) submitted via POST
 */
$updateMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['single_action'])) {
    if (!checkRateLimit(30)) {
        $updateMessage = 'Rate limit exceeded. Please wait before making more changes.';
    } elseif (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
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

/**
 * Handle bulk status updates (Accept / Reject) submitted via POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!checkRateLimit(30)) {
        $updateMessage = 'Rate limit exceeded. Please wait before making more changes.';
    } elseif (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $updateMessage = 'Invalid form submission.';
    } else {
        $action   = $_POST['bulk_action']; // Expected values: Accepted, Rejected
        $idsRaw   = $_POST['selected_ids'] ?? '';
        $idsArray = array_filter(array_map('intval', explode(',', $idsRaw)));
        if (!empty($idsArray) && in_array($action, ['Accepted', 'Rejected'])) {
            $matching = new Matching($database);
            $res = $matching->bulkUpdateStatus($idsArray, $action);
            $updateMessage = $res['message'];
        } else {
            $updateMessage = 'No valid selections or action provided.';
        }
    }
}

// Fetch opportunity details (optional, for header)
$opportunity = $database->fetchOne('SELECT * FROM opportunities WHERE id = ?', [$opportunityId], 'i');
if (!$opportunity) {
    die('Opportunity not found');
}

// Pagination parameters
$perPageOptions = [25, 50, 100];
$perPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $perPageOptions) ? (int)$_GET['per_page'] : 25;
$page    = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset  = ($page - 1) * $perPage;

$matching = new Matching($database);
$totalApplications = $matching->countMatchesForOpportunity($opportunityId);
$totalPages = (int)ceil($totalApplications / $perPage);
$applications = $matching->getMatchesForOpportunity($opportunityId, 0, $perPage, $offset);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-5xl mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Applications for "<?= htmlspecialchars($opportunity['title']) ?>"</h1>

    <?php if (!empty($updateMessage)): ?>
        <div class="p-4 mb-4 <?= strpos($updateMessage, 'success') !== false ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' ?> rounded" role="alert" aria-live="polite">
            <p class="text-sm <?= strpos($updateMessage, 'success') !== false ? 'text-green-800' : 'text-red-800' ?>">
                <?= htmlspecialchars($updateMessage) ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (empty($applications)): ?>
        <p class="text-gray-600 dark:text-gray-400">No applications submitted yet.</p>
    <?php else: ?>
        <!-- Filters & Export -->
        <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-lg shadow border border-slate-200 dark:border-slate-700 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1" for="applicant_search_main">Search Applicants</label>
                <input type="text" id="applicant_search_main" data-target=".applicant-row" data-filter-type="search" placeholder="Search by name, email, phone or skill..."
                    class="client-filter w-full bg-slate-100 dark:bg-slate-700 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 border border-transparent transition-all" aria-label="Search Applicants" />
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1" for="applicant_status_main">Filter by Status</label>
                <select id="applicant_status_main" data-target=".applicant-row" data-filter-type="exact" data-filter-attr="status"
                    class="client-filter bg-slate-100 dark:bg-slate-700 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 border border-transparent transition-all" aria-label="Filter by Status">
                    <option value="All">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1" for="per_page_select">Rows per page</label>
                <select id="per_page_select" name="per_page" class="bg-slate-100 dark:bg-slate-700 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 border border-transparent transition-all" aria-label="Rows per page" onchange="location.href='?opportunity_id=<?= $opportunityId ?>&per_page=' + this.value;">
                    <?php foreach ($perPageOptions as $opt): ?>
                        <option value="<?= $opt ?>" <?= $perPage === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2 ml-auto">
                <form method="GET" action="export-applications.php" class="inline-flex m-0">
                    <input type="hidden" name="opportunity_id" value="<?= $opportunityId ?>" />
                    <input type="hidden" name="search" value="" id="export_search" />
                    <input type="hidden" name="status" value="" id="export_status" />
                    <button type="submit" name="format" value="csv" class="px-3 py-2 bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">Export CSV</button>
                    <button type="submit" name="format" value="excel" class="ml-2 px-3 py-2 bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">Export Excel</button>
                    <button type="submit" name="format" value="json" class="ml-2 px-3 py-2 bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">Export JSON</button>
                </form>
            </div>
        </div>

        <!-- Bulk Action Toolbar -->
        <?php if ($_SESSION['role'] !== 'youth'): ?>
        <form method="POST" id="bulkForm" class="mb-4 hidden" aria-hidden="true">
            <input type="hidden" name="form_nonce" value="<?= htmlspecialchars(getFormNonce()) ?>" />
            <input type="hidden" name="selected_ids" id="bulk_selected_ids" value="" />
            <input type="hidden" name="bulk_action" id="bulk_action_input" value="" />
        </form>
        <div class="mb-4 flex gap-2">
            <button type="button" class="px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" onclick="triggerBulk('Accepted')">Accept Selected</button>
            <button type="button" class="px-3 py-2 bg-rose-600 text-white rounded hover:bg-rose-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2" onclick="triggerBulk('Rejected')">Reject Selected</button>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full table-auto bg-white dark:bg-slate-800 rounded-lg overflow-hidden shadow" role="table">
                <thead class="bg-gray-50 dark:bg-slate-700">
                    <tr>
                        <?php if ($_SESSION['role'] !== 'youth'): ?>
                        <th scope="col" class="px-4 py-3 text-left"><input type="checkbox" id="select_all" onclick="toggleSelectAll(this)" class="form-checkbox text-blue-600 rounded focus:ring-blue-600 dark:bg-slate-600 dark:border-slate-500" aria-label="Select All" /></th>
                        <?php endif; ?>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Applicant</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Age</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Primary Skill</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Status</th>
                        <?php if ($_SESSION['role'] !== 'youth'): ?>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    <?php foreach ($applications as $app): ?>
                        <tr class="applicant-row hover:bg-slate-50 dark:hover:bg-slate-700/50" data-status="<?= htmlspecialchars($app['status']) ?>">
                            <?php if ($_SESSION['role'] !== 'youth'): ?>
                            <td class="px-4 py-3">
                                <?php if ($app['status'] === 'Pending'): ?>
                                <input type="checkbox" class="row-checkbox form-checkbox text-blue-600 rounded focus:ring-blue-600 dark:bg-slate-600 dark:border-slate-500" value="<?= $app['id'] ?>" aria-label="Select applicant <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>" />
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?><br>
                                <span class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($app['email'] . ' | ' . $app['phone']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center"><?= htmlspecialchars($app['age'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($app['primary_skill'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-sm">
                                <?php
                                $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                if ($app['status'] === 'Accepted') $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
                                elseif ($app['status'] === 'Rejected') $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200';
                                elseif ($app['status'] === 'Pending') $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </td>
                            <?php if ($_SESSION['role'] !== 'youth'): ?>
                            <td class="px-4 py-3 text-center">
                                <?php if ($app['status'] === 'Pending'): ?>
                                    <form method="POST" class="inline m-0" onsubmit="return confirmAction('Accepted', event);">
                                        <input type="hidden" name="form_nonce" value="<?= htmlspecialchars(getFormNonce()) ?>" />
                                        <input type="hidden" name="match_id" value="<?= $app['id'] ?>" />
                                        <input type="hidden" name="new_status" value="Accepted" />
                                        <input type="hidden" name="single_action" value="1" />
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-emerald-700 bg-emerald-100 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-emerald-800 dark:text-emerald-100 dark:hover:bg-emerald-700">Accept</button>
                                    </form>
                                    <form method="POST" class="inline m-0" onsubmit="return confirmAction('Rejected', event);">
                                        <input type="hidden" name="form_nonce" value="<?= htmlspecialchars(getFormNonce()) ?>" />
                                        <input type="hidden" name="match_id" value="<?= $app['id'] ?>" />
                                        <input type="hidden" name="new_status" value="Rejected" />
                                        <input type="hidden" name="single_action" value="1" />
                                        <button type="submit" class="inline-flex items-center ml-1 px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-rose-700 bg-rose-100 hover:bg-rose-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 dark:bg-rose-800 dark:text-rose-100 dark:hover:bg-rose-700">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400 dark:text-gray-500" aria-label="Decision already taken" title="Decision already taken">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1 || $totalApplications > 0): ?>
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing <span class="font-medium"><?= min($totalApplications, $offset + 1) ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalApplications) ?></span> of <span class="font-medium"><?= $totalApplications ?></span> applications
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?opportunity_id=<?= $opportunityId ?>&page=<?= $page - 1 ?>&per_page=<?= $perPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-600">
                            <span class="sr-only">Previous</span>
                            &laquo; Prev
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    // Simple pagination display (show a few around current)
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($p = $startPage; $p <= $endPage; $p++):
                        if ($p === $page):
                    ?>
                        <span aria-current="page" class="z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 text-blue-600 dark:text-blue-200 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $p ?>
                        </span>
                    <?php else: ?>
                        <a href="?opportunity_id=<?= $opportunityId ?>&page=<?= $p ?>&per_page=<?= $perPage ?>" class="bg-white dark:bg-slate-700 border-gray-300 dark:border-slate-600 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $p ?>
                        </a>
                    <?php endif; endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?opportunity_id=<?= $opportunityId ?>&page=<?= $page + 1 ?>&per_page=<?= $perPage ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-600">
                            <span class="sr-only">Next</span>
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="mt-6">
        <a href="opportunities.php" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Opportunities
        </a>
    </div>
</div>

<!-- Custom Tailwind Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <!-- Modal panel -->
    <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10" id="modalIcon">
            <svg class="h-6 w-6 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Confirm Action</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500 dark:text-gray-300" id="modalMessage">Are you sure?</p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 dark:bg-slate-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" id="modalConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Confirm</button>
        <button type="button" id="modalCancelBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-500 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentCallback = null;

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    currentCallback = null;
}

document.getElementById('modalConfirmBtn').addEventListener('click', function() {
    if (currentCallback) currentCallback();
});

// For individual Accept/Reject forms
function confirmAction(action, event) {
    event.preventDefault();
    const form = event.target;
    
    document.getElementById('modal-title').textContent = action === 'Accepted' ? 'Accept Application' : 'Reject Application';
    document.getElementById('modalMessage').textContent = `Are you sure you want to ${action.toLowerCase()} this application? This action cannot be undone.`;
    
    const icon = document.getElementById('modalIcon');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    if (action === 'Accepted') {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900 sm:mx-0 sm:h-10 sm:w-10';
        icon.innerHTML = '<svg class="h-6 w-6 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
        confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm';
    } else {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10';
        icon.innerHTML = '<svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm';
    }
    
    currentCallback = function() {
        form.submit();
    };
    
    document.getElementById('confirmModal').classList.remove('hidden');
    return false;
}

// Select all checkboxes
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

// Trigger bulk actions
function triggerBulk(action) {
    const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one application.');
        return;
    }
    
    document.getElementById('modal-title').textContent = action === 'Accepted' ? 'Bulk Accept' : 'Bulk Reject';
    document.getElementById('modalMessage').textContent = `Are you sure you want to ${action.toLowerCase()} the selected ${selected.length} application(s)?`;
    
    const icon = document.getElementById('modalIcon');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    if (action === 'Accepted') {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 dark:bg-emerald-900 sm:mx-0 sm:h-10 sm:w-10';
        icon.innerHTML = '<svg class="h-6 w-6 text-emerald-600 dark:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
        confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm';
    } else {
        icon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10';
        icon.innerHTML = '<svg class="h-6 w-6 text-red-600 dark:text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm';
    }
    
    currentCallback = function() {
        document.getElementById('bulk_selected_ids').value = selected.join(',');
        document.getElementById('bulk_action_input').value = action;
        document.getElementById('bulkForm').submit();
    };
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

// Sync export filters with the search/status inputs
const searchInput = document.getElementById('applicant_search_main');
const statusInput = document.getElementById('applicant_status_main');
const exportSearch = document.getElementById('export_search');
const exportStatus = document.getElementById('export_status');

if (searchInput) searchInput.addEventListener('input', function() {
    exportSearch.value = this.value;
});
if (statusInput) statusInput.addEventListener('change', function() {
    exportStatus.value = this.value;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>