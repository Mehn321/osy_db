<?php
$pageTitle = 'Training Programs';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole(['lydo', 'training_provider']);

require_once __DIR__ . '/../includes/header.php';

$opportunity = new Opportunity($database);
$osyProfile = new OSYProfile($database);
$notification = new Notification($database);
$templates = $notification->getAllTemplates();
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
    } else {
        if (isset($_POST['broadcast_training'])) {
            $opp_id = intval($_POST['opportunity_id']);
            $opp = $opportunity->getById($opp_id);

            if (!$opp) {
                $message = 'Training opportunity not found.';
            } elseif ($_SESSION['role'] !== 'lydo' && (int) $opp['provider_id'] !== (int) $_SESSION['user_id']) {
                $message = 'You can only send broadcasts for your own training programs.';
            } elseif (!in_array($opp['type'], ['Vocational Training', 'Scholarship'], true)) {
                $message = 'Broadcasts can only be sent for training programs or scholarships.';
            } else {
                $message_text = $_POST['custom_message'];
                $target_group = $_POST['target_group'];
                $send_sms = isset($_POST['send_sms']);
                $send_email = isset($_POST['send_email']);

                // Determine Recipients
                $recipients = [];
                if ($target_group === 'Specific' && !empty($_POST['specific_ids'])) {
                    foreach ($_POST['specific_ids'] as $id) {
                        $p = $osyProfile->getById(intval($id));
                        if ($p) $recipients[] = $p;
                    }
                } else {
                    // Map frontend groups to OSYProfile filters
                    $filters = [];
                    if ($target_group === 'OSY') {
                        $filters['profile_type'] = 'OSY';
                    } elseif ($target_group === 'Non-OSY') {
                        $filters['profile_type'] = 'Regular';
                    } elseif ($target_group === 'Unemployed') {
                        $filters['status'] = 'Unemployed';
                    } elseif ($target_group === 'In Training') {
                        $filters['status'] = 'In Training';
                    } elseif ($target_group === 'Employed') {
                        $filters['status'] = 'Employed';
                    }
                    $recipients = $osyProfile->getAll($filters);
                }

                require_once __DIR__ . '/../Classes/SmsService.php';
                require_once __DIR__ . '/../Classes/EmailService.php';
                $sms = new SmsService($database);
                $email = new EmailService($database);

                $sentCount = 0;
                foreach ($recipients as $r) {
                    $personalMsg = str_replace(['{{name}}', '{{opportunity}}'], [$r['first_name'], $opp['title']], $message_text);

                    if ($send_sms && !empty($r['phone'])) {
                        $sms->send($r['phone'], $personalMsg);
                    }
                    if ($send_email && !empty($r['email'])) {
                        $email->send($r['email'], 'New Vocational Opportunity: ' . $opp['title'], "<p>" . nl2br(htmlspecialchars($personalMsg)) . "</p>");
                    }
                    $sentCount++;
                }

                if ($target_group === 'Specific') {
                    foreach ($recipients as $r) {
                        if (empty($r['created_by'])) {
                            continue;
                        }
                        $notification->create([
                            'title' => 'Training Broadcast: ' . $opp['title'],
                            'message' => str_replace(['{{name}}', '{{opportunity}}'], [$r['first_name'], $opp['title']], $message_text),
                            'type' => 'Opportunity',
                            'recipient_type' => 'Specific',
                            'recipient_id' => $r['created_by']
                        ]);
                    }
                } else {
                    $notification->create([
                        'title' => 'Training Broadcast: ' . $opp['title'],
                        'message' => $message_text,
                        'type' => 'Opportunity',
                        'recipient_type' => $target_group === 'All' ? 'All' : 'OSY'
                    ]);
                }

                $message = "Broadcast deployed to $sentCount recipients successfully!";
            }
        } elseif (isset($_POST['create_opportunity'])) {
            $result = $opportunity->create([
                'title' => $_POST['title'],
                'type' => $_POST['type'],
                'location' => $_POST['location'],
                'compensation' => $_POST['compensation'] ?? null,
                'benefits' => $_POST['benefits'] ?? null,
                'certification' => $_POST['certification'] ?? null,
                'description' => $_POST['description'] ?? null,
                'total_slots' => $_POST['total_slots'],
                'deadline' => $_POST['deadline']
            ]);
            $message = $result['message'];
            if ($result['success']) {
                header('Location: training-programs.php?success=created');
                exit;
            }
        } elseif (isset($_POST['update_opportunity'])) {
            $result = $opportunity->update($_POST['opportunity_id'], [
                'title' => $_POST['title'],
                'type' => $_POST['type'],
                'location' => $_POST['location'],
                'compensation' => $_POST['compensation'] ?? null,
                'benefits' => $_POST['benefits'] ?? null,
                'certification' => $_POST['certification'] ?? null,
                'description' => $_POST['description'] ?? null,
                'total_slots' => $_POST['total_slots'],
                'deadline' => $_POST['deadline'],
                'status' => $_POST['status']
            ]);
            $message = $result['message'];
            if ($result['success']) {
                header('Location: training-programs.php?success=updated');
                exit;
            }
        } elseif (isset($_POST['delete_opportunity'])) {
            $result = $opportunity->delete($_POST['opportunity_id']);
            $message = $result['message'];
            if ($result['success']) {
                header('Location: training-programs.php?success=deleted');
                exit;
            }
        }
    }
}

if (isset($_GET['success'])) {
    $actions = ['created' => 'Opportunity created successfully!', 'updated' => 'Opportunity updated successfully!', 'deleted' => 'Opportunity deleted successfully!'];
    $message = $actions[$_GET['success']] ?? 'Action completed!';
}

$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => 'training',
    'type' => $_GET['type'] ?? 'All',
    'status' => $_GET['status'] ?? 'All'
];

if ($_SESSION['role'] === 'lydo') {
    $opportunities = $opportunity->getAll($filters);
} else {
    $opportunities = array_filter($opportunity->getByProvider($_SESSION['user_id']), function ($opp) use ($filters) {
        if ($opp['type'] !== 'Vocational Training' && $opp['type'] !== 'Scholarship') {
            return false;
        }
        if ($filters['type'] !== 'All' && $opp['type'] !== $filters['type']) {
            return false;
        }
        if ($filters['status'] !== 'All' && $opp['status'] !== $filters['status']) {
            return false;
        }
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            return strpos(strtolower($opp['title'] . ' ' . $opp['location']), $search) !== false;
        }
        return true;
    });
}
$allProfiles = $osyProfile->getAll(); // For the recipient selection
$templates = $notification->getAllTemplates();
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
    <div class="space-y-1">
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-2">
            <span>Opportunities</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900">Training Programs</span>
        </nav>
        <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">Training Programs</h1>
        <p class="text-slate-600 max-w-2xl">Manage vocational training programs and scholarships available for youth
            upskilling.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <button onclick="openCreateModal('Vocational Training')"
            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-500 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">school</span>
            Encode New Training
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div
        class="mb-6 p-4 <?php echo strpos($message, 'successfully') !== false || strpos($message, 'success') !== false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p
            class="<?php echo strpos($message, 'successfully') !== false || strpos($message, 'success') !== false ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Search</label>
            <input type="text" id="train_search" data-target=".train-card" data-filter-type="search" placeholder="Search opportunities..."
                class="client-filter w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Type</label>
            <select id="train_type" data-target=".train-card" data-filter-type="exact" data-filter-attr="type"
                class="client-filter w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All">All Training</option>
                <option value="Vocational Training">Vocational Training</option>
                <option value="Scholarship">Scholarship</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Status</label>
            <select id="train_status" data-target=".train-card" data-filter-type="exact" data-filter-attr="status"
                class="client-filter w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All">All Status</option>
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
        <div class="flex gap-3">
            <a href="training-programs.php"
                class="w-full py-3 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl font-bold text-sm text-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">Reset
                List</a>
        </div>
    </div>
</div>

<!-- Opportunities Cards Grid -->
<div id="progCardsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
            <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
        </div>
    <?php endfor; ?>
</div>
<div id="progsLoading" class="hidden flex items-center justify-center gap-2 mt-4 text-sm text-slate-500">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading programs...
</div>

<!-- Create/Edit Opportunity Modal -->
<div id="opportunityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Create New Opportunity</h3>
            </div>
            <form id="opportunityForm" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <input type="hidden" id="opportunityId" name="opportunity_id">
                <input type="hidden" id="isUpdate" name="update_opportunity" value="0">

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Title</label>
                    <input type="text" name="title" id="opp_title" required
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Type</label>
                    <select name="type" id="opp_type" required
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Job Opening">Job Opening</option>
                        <option value="Vocational Training">Vocational Training</option>
                        <option value="Scholarship">Scholarship</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Location</label>
                    <input type="text" name="location" id="opp_location" required
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Total
                            Slots</label>
                        <input type="number" name="total_slots" id="opp_total_slots" min="1" required
                            class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Deadline</label>
                        <input type="date" name="deadline" id="opp_deadline" required
                            class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Training Provider /
                        Sponsor</label>
                    <input type="text" name="training_provider" id="opp_training_provider"
                        placeholder="e.g., TESDA, LGU Mayor's Office, DOLE"
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Duration</label>
                        <input type="text" name="duration" id="opp_duration" placeholder="e.g., 6 Months, 4 Weeks"
                            class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Modality</label>
                        <select name="modality" id="opp_modality"
                            class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Modality</option>
                            <option value="Face-to-Face">Face-to-Face</option>
                            <option value="Online">Online</option>
                            <option value="Blended">Blended (Online + On-site)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Certification
                        Granted</label>
                    <input type="text" name="certification" id="opp_certification" placeholder="e.g., TESDA NCII"
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Program
                        Description</label>
                    <textarea name="description" id="opp_description" rows="3"
                        placeholder="Details about the training program..."
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div id="statusContainer" class="hidden">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Status</label>
                    <select name="status" id="opp_status"
                        class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()"
                        class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800">
                        Create Opportunity
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Opportunity Details</h3>
                    <button onclick="document.getElementById('detailModal').classList.add('hidden')"
                        class="text-slate-400 hover:text-slate-600"><span
                            class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-6 space-y-4" id="detailContent"></div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full">
                <div class="p-6">
                    <div
                        class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                        <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Delete Opportunity
                    </h3>
                    <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6">Are you sure? This action
                        cannot be undone.</p>
                    <form method="POST" class="flex gap-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                        <input type="hidden" id="deleteOpportunityId" name="opportunity_id">
                        <input type="hidden" name="delete_opportunity" value="1">
                        <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-sm hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-sm hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
<script>
    var opportunitiesData = {};
    const userRole = <?php echo json_encode($_SESSION['role']); ?>;

    function renderProgCard(opp) {
        const statusCls = opp.status === 'Open' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        const deadline = opp.deadline ? new Date(opp.deadline).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : 'No deadline';
        
        let html = `
            <div class="prog-card bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">${opp.type}</p>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">${opp.title}</h3>
                        </div>
                        <span class="px-3 py-1 ${statusCls} rounded-full text-xs font-bold">${opp.status}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">location_on</span> ${opp.location}
                    </p>
                </div>
                <div class="p-6 space-y-4">`;
                
        if (opp.description) html += `<p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">${opp.description}</p>`;
        if (opp.training_provider) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Provider</p><p class="text-lg font-bold text-slate-900 dark:text-white">${opp.training_provider}</p></div>`;
        if (opp.certification) html += `<div><p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Certification</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>`;
        
        html += `
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Deadline</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${deadline}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Slots</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots} available</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex gap-2">
                    <button onclick="viewOpportunityDetail(${opp.id})" class="flex-1 py-2 px-3 bg-indigo-900 text-white rounded-lg text-sm font-semibold hover:bg-indigo-800 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span> View Details
                    </button>`;
                    
        if (userRole !== 'lydo') {
            html += `<button onclick="openEditModal(${opp.id})" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors"><span class="material-symbols-outlined">edit</span></button>`;
        }
        html += `
                    <button onclick="deleteOpportunity(${opp.id})" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors"><span class="material-symbols-outlined">delete</span></button>
                </div>
            </div>`;
        return html;
    }

    function loadProgs() {
        const searchVal = document.getElementById('prog_search')?.value || '';
        const statusVal = document.getElementById('prog_status')?.value || 'All';
        
        const loader = document.getElementById('progsLoading');
        const container = document.getElementById('progCardsGrid');
        
        loader.classList.remove('hidden');
        container.innerHTML = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">` + 
            Array(4).fill(0).map(() => `
            <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
                <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
            </div>`).join('') + `</div>`;

        fetch(`../api/get_opportunities_data.php?type=Vocational+Training&search=${encodeURIComponent(searchVal)}&status=${encodeURIComponent(statusVal)}`)
            .then(r => r.json())
            .then(res => {
                loader.classList.add('hidden');
                opportunitiesData = {};
                if (res.success && res.opportunities) {
                    res.opportunities.forEach(opp => { opportunitiesData[opp.id] = opp; });
                }
                
                if (!res.success || !res.opportunities.length) {
                    container.innerHTML = `<div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">school</span><p class="text-slate-500 font-semibold text-lg">No training programs found</p></div>`;
                    return;
                }
                
                container.innerHTML = res.opportunities.map(renderProgCard).join('');
            });
    }

    (function() {
        loadProgs();
        
        var searchEl = document.getElementById('prog_search');
        if (searchEl) {
            let debounce;
            searchEl.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(loadProgs, 400); });
        }
        var statusEl = document.getElementById('prog_status');
        if (statusEl) statusEl.addEventListener('change', loadProgs);
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('create')) setTimeout(() => openCreateModal('Vocational Training'), 500);
        if (urlParams.get('edit_id')) setTimeout(() => openEditModal(parseInt(urlParams.get('edit_id'))), 500);
        if (urlParams.get('view_id')) setTimeout(() => viewOpportunityDetail(parseInt(urlParams.get('view_id'))), 500);
    })();


    function openCreateModal(type) {
            document.getElementById('modalTitle').textContent = 'Create New ' + (type === 'Job Opening' ? 'Job' : (type ===
                'Scholarship' ? 'Scholarship' : 'Training'));
            document.getElementById('opp_type').value = type;
            document.getElementById('isUpdate').value = '0';
            document.getElementById('isUpdate').name = 'create_opportunity';
            document.getElementById('submitBtn').textContent = 'Create Opportunity';
            document.getElementById('statusContainer').classList.add('hidden');
            document.getElementById('opportunityForm').reset();
            document.getElementById('opp_type').value = type;
            document.getElementById('opportunityModal').classList.remove('hidden');
        }

        function openEditModal(id) {
            const opp = opportunitiesData[id];
            if (opp) {
                document.getElementById('modalTitle').textContent = 'Edit Opportunity';
                document.getElementById('opportunityId').value = id;
                document.getElementById('isUpdate').value = '1';
                document.getElementById('isUpdate').name = 'update_opportunity';
                document.getElementById('submitBtn').textContent = 'Update Opportunity';
                document.getElementById('statusContainer').classList.remove('hidden');

                document.getElementById('opp_title').value = opp.title;
                document.getElementById('opp_type').value = opp.type;
                document.getElementById('opp_location').value = opp.location;
                document.getElementById('opp_total_slots').value = opp.total_slots;
                document.getElementById('opp_deadline').value = opp.deadline;
                document.getElementById('opp_training_provider').value = opp.training_provider || '';
                document.getElementById('opp_duration').value = opp.duration || '';
                document.getElementById('opp_modality').value = opp.modality || '';
                document.getElementById('opp_certification').value = opp.certification || '';
                document.getElementById('opp_description').value = opp.description || '';
                document.getElementById('opp_status').value = opp.status;

                document.getElementById('opportunityModal').classList.remove('hidden');
            }
        }

        function viewOpportunityDetail(id) {
            const opp = opportunitiesData[id];
            if (!opp) {
                console.error('Opportunity data not found for ID', id);
                return;
            }
            let html = `
                <div><p class="text-xs font-bold text-slate-500 uppercase">Title</p><p class="font-bold text-slate-900 dark:text-white text-lg">${opp.title}</p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Type</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.type}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Status</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.status}</p></div>
                </div>
                <div><p class="text-xs font-bold text-slate-500 uppercase">Location</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.location}</p></div>
            `;
            if (opp.training_provider) html +=
                `<div><p class="text-xs font-bold text-slate-500 uppercase">Training Provider</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.training_provider}</p></div>`;
            if (opp.duration || opp.modality) {
                html += `<div class="grid grid-cols-2 gap-4 pt-2">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Duration</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.duration || 'N/A'}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Modality</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.modality || 'N/A'}</p></div>
                </div>`;
            }
            if (opp.certification) html +=
                `<div><p class="text-xs font-bold text-slate-500 uppercase">Certification Granted</p><p class="text-sm font-bold text-blue-900 dark:text-blue-400">${opp.certification}</p></div>`;
            if (opp.description) html +=
                `<div><p class="text-xs font-bold text-slate-500 uppercase">Program Description</p><p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">${opp.description}</p></div>`;
            html += `
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Slots</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Deadline</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.deadline}</p></div>
                </div>
            `;
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('opportunityModal').classList.add('hidden');
        }

        

        function deleteOpportunity(id) {
            document.getElementById('deleteOpportunityId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
<style>.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; } .dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; } @keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
