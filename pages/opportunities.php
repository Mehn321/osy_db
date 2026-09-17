<?php
$pageTitle = 'Opportunities';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Reference.php';
require_once __DIR__ . '/../Classes/Matching.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole(['lydo', 'employer', 'training_provider', 'youth']);

require_once __DIR__ . '/../includes/header.php';

$opportunity = new Opportunity($database);
$ref = new Reference($database);
$matching = new Matching($database);

$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
    } else {
        if (isset($_POST['create_opportunity'])) {
            // Only providers can create opportunities
            if (!in_array($_SESSION['role'], ['employer', 'training_provider']) || $_SESSION['status'] !== 'Active') {
                $message = 'You do not have permission to create opportunities.';
            } else {
                $result = $opportunity->create([
                    'title' => $_POST['title'],
                    'type' => $_POST['type'],
                    'employment_type' => $_POST['employment_type'] ?? null,
                    'work_schedule' => $_POST['work_schedule'] ?? null,
                    'experience_req' => $_POST['experience_req'] ?? null,
                    'training_provider' => $_POST['training_provider'] ?? null,
                    'duration' => $_POST['duration'] ?? null,
                    'modality' => $_POST['modality'] ?? null,
                    'location' => $_POST['location'],
                    'compensation' => $_POST['compensation'] ?? null,
                    'benefits' => $_POST['benefits'] ?? null,
                    'certification' => $_POST['certification'] ?? null,
                    'description' => $_POST['description'] ?? null,
                    'total_slots' => $_POST['total_slots'],
                    'deadline' => $_POST['deadline'],
                    'age_min' => !empty($_POST['age_min']) ? intval($_POST['age_min']) : null,
                    'age_max' => !empty($_POST['age_max']) ? intval($_POST['age_max']) : null
                ]);
                $message = $result['message'];
                if ($result['success']) {
                    header('Location: opportunities.php?success=created');
                    exit;
                }
            }
        } elseif (isset($_POST['update_opportunity'])) {
            $result = $opportunity->update($_POST['opportunity_id'], [
                'title' => $_POST['title'],
                'type' => $_POST['type'],
                'employment_type' => $_POST['employment_type'] ?? null,
                'work_schedule' => $_POST['work_schedule'] ?? null,
                'experience_req' => $_POST['experience_req'] ?? null,
                'training_provider' => $_POST['training_provider'] ?? null,
                'duration' => $_POST['duration'] ?? null,
                'modality' => $_POST['modality'] ?? null,
                'location' => $_POST['location'],
                'compensation' => $_POST['compensation'] ?? null,
                'benefits' => $_POST['benefits'] ?? null,
                'certification' => $_POST['certification'] ?? null,
                'description' => $_POST['description'] ?? null,
                'total_slots' => $_POST['total_slots'],
                'deadline' => $_POST['deadline'],
                'age_min' => !empty($_POST['age_min']) ? intval($_POST['age_min']) : null,
                'age_max' => !empty($_POST['age_max']) ? intval($_POST['age_max']) : null,
                'status' => $_POST['status']
            ]);
            $message = $result['message'];
            if ($result['success']) {
                header('Location: opportunities.php?success=updated');
                exit;
            }
        } elseif (isset($_POST['delete_opportunity'])) {
            $result = $opportunity->delete($_POST['opportunity_id']);
            $message = $result['message'];
            if ($result['success']) {
                header('Location: opportunities.php?success=deleted');
                exit;
            }
        }
    }
}

if (isset($_GET['success'])) {
    $actions = ['created' => 'Opportunity created successfully!', 'updated' => 'Opportunity updated successfully!', 'deleted' => 'Opportunity deleted successfully!'];
    $message = $actions[$_GET['success']] ?? 'Action completed!';
}

// Get roles for header rendering
$isProvider = in_array($_SESSION['role'], ['employer', 'training_provider']);
$canCreate = ($isProvider && $_SESSION['status'] === 'Active');

// Note: Heavy opportunities list fetching removed (now done via AJAX)
$filters = [
    'search' => $_GET['search'] ?? '',
    'type' => $_GET['type'] ?? 'All',
    'status' => $_GET['status'] ?? 'All'
];
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
    <div class="space-y-1">
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-2">
            <span>Main</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900">Opportunities</span>
        </nav>
        <?php if ($isProvider): ?>
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">My Opportunities</h1>
            <p class="text-slate-600 max-w-2xl">Manage your posted job openings, training programs, and scholarships.</p>
        <?php elseif ($_SESSION['role'] === 'youth'): ?>
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">Available Opportunities</h1>
            <p class="text-slate-600 max-w-2xl">Browse and apply to job openings, training programs, and scholarships matching your profile.</p>
        <?php else: ?>
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">Opportunity Management</h1>
            <p class="text-slate-600 max-w-2xl">Curate and manage vocational training, employment roles, and scholarships.</p>
        <?php endif; ?>
    </div>
    <?php if ($canCreate): ?>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="openCreateModal('Job Opening')" class="px-6 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">work</span>
                Encode New Job
            </button>
            <button onclick="openCreateModal('Vocational Training')" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-500 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">school</span>
                Encode New Training
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo strpos($message, 'successfully') !== false || strpos($message, 'success') !== false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo strpos($message, 'successfully') !== false || strpos($message, 'success') !== false ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-8">
    <form id="filterForm" method="GET" class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Search</label>
            <input type="text" id="filter_search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search opportunities..." class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Type</label>
            <select id="filter_type" name="type" class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All" <?php echo $filters['type'] === 'All' ? ' selected' : ''; ?>>All Types</option>
                <option value="Job Opening" <?php echo $filters['type'] === 'Job Opening' ? ' selected' : ''; ?>>Job Opening</option>
                <option value="Vocational Training" <?php echo $filters['type'] === 'Vocational Training' ? ' selected' : ''; ?>>Training Opportunity</option>
            </select>
        </div>
        
        <?php if (!$isProvider && $_SESSION['role'] !== 'youth'): ?>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Status</label>
                <select id="filter_status" name="status" class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                    <option value="All" <?php echo $filters['status'] === 'All' ? ' selected' : ''; ?>>All Status</option>
                    <option value="Open" <?php echo $filters['status'] === 'Open' ? ' selected' : ''; ?>>Open</option>
                    <option value="Closed" <?php echo $filters['status'] === 'Closed' ? ' selected' : ''; ?>>Closed</option>
                </select>
            </div>
        <?php endif; ?>
        
        <div class="flex gap-3">
            <button type="button" onclick="resetFilters()" class="w-full py-3 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl font-bold text-sm text-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">Reset</button>
        </div>
    </form>
</div>

<!-- Opportunities Container -->
<div id="opportunitiesContainer">
    <!-- Skeletons will be injected here by JS, followed by AJAX results -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php for ($i=0; $i<4; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="skeleton-pulse h-4 w-24 rounded mb-2"></div>
                <div class="skeleton-pulse h-6 w-48 rounded"></div>
            </div>
            <div class="p-6 space-y-4">
                <div class="skeleton-pulse h-4 w-full rounded"></div>
                <div class="skeleton-pulse h-4 w-3/4 rounded"></div>
                <div class="skeleton-pulse h-16 w-full rounded mt-4"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>
<div id="opportunitiesLoading" class="hidden flex items-center justify-center gap-2 mt-4 text-sm text-slate-500">
    <span class="material-symbols-outlined text-base animate-spin">refresh</span> Loading opportunities…
</div>
<!-- Create/Edit Opportunity Modal (unchanged) -->
<div id="opportunityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Create New Opportunity</h3>
            </div>
            <form id="opportunityForm" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                <input type="hidden" id="opportunityId" name="opportunity_id">
                <input type="hidden" id="isUpdate" name="update_opportunity" value="0">

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Title</label>
                    <input type="text" name="title" id="opp_title" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Type</label>
                    <select name="type" id="opp_type" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Job Opening">Job Opening</option>
                        <option value="Vocational Training">Training Opportunity</option>
                        <option value="Scholarship">Scholarship</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Location</label>
                    <input type="text" name="location" id="opp_location" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Total Slots</label>
                        <input type="number" name="total_slots" id="opp_total_slots" min="1" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Deadline</label>
                        <input type="date" name="deadline" id="opp_deadline" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Compensation</label>
                    <input type="text" name="compensation" id="opp_compensation" placeholder="e.g., ₱15,000 - ₱20,000/month" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Benefits</label>
                    <textarea name="benefits" id="opp_benefits" rows="2" placeholder="e.g., Health insurance, 13th month pay" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Certification Required</label>
                    <input type="text" name="certification" id="opp_certification" placeholder="e.g., TESDA NCII" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Description</label>
                    <textarea name="description" id="opp_description" rows="3" placeholder="Details about the opportunity..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Minimum Age</label>
                        <input type="number" name="age_min" id="opp_age_min" min="1" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Maximum Age</label>
                        <input type="number" name="age_max" id="opp_age_max" min="1" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div id="statusContainer" class="hidden">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Status</label>
                    <select name="status" id="opp_status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800">
                        Create Opportunity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Detail Modal (Improved) -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-700/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">info</span>
                    Opportunity Details
                </h3>
                <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="p-6 space-y-5" id="detailContent"></div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50 flex justify-end">
                <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-slate-200 rounded-lg text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (unchanged) -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Delete Opportunity</h3>
                <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6">Are you sure? This action cannot be undone.</p>
                <form method="POST" class="flex gap-3">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                    <input type="hidden" id="deleteOpportunityId" name="opportunity_id">
                    <input type="hidden" name="delete_opportunity" value="1">
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-sm hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-sm hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Store opportunities data for JS access
    var opportunitiesData = {};

    function openCreateModal(type) {
        document.getElementById('modalTitle').textContent = 'Create New ' + (type === 'Job Opening' ? 'Job' : (type === 'Scholarship' ? 'Scholarship' : 'Training'));
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
            document.getElementById('opp_compensation').value = opp.compensation || '';
            document.getElementById('opp_benefits').value = opp.benefits || '';
            document.getElementById('opp_certification').value = opp.certification || '';
            document.getElementById('opp_description').value = opp.description || '';
            document.getElementById('opp_age_min').value = opp.age_min || '';
            document.getElementById('opp_age_max').value = opp.age_max || '';
            document.getElementById('opp_status').value = opp.status;

            document.getElementById('opportunityModal').classList.remove('hidden');
        }
    }

    function viewOpportunityDetail(id) {
        const opp = opportunitiesData[id];
        if (opp) {
            let html = `
                <div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">${opp.title}</h4>
                    <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-xs font-bold">${opp.type}</span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">location_on</span> Location</p>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-1">${opp.location}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">check_circle</span> Status</p>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-1">${opp.status}</p>
                    </div>
                </div>
            `;
            if (opp.description) {
                html += `
                    <div class="bg-slate-50 dark:bg-slate-900/30 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Description</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">${opp.description}</p>
                    </div>
                `;
            }
            if (opp.compensation || opp.benefits || opp.certification) {
                html += `<div class="space-y-3">`;
                if (opp.compensation) html += `<div><p class="text-xs font-semibold text-slate-500 uppercase">Compensation</p><p class="text-sm font-medium text-slate-900 dark:text-white">${opp.compensation}</p></div>`;
                if (opp.benefits) html += `<div><p class="text-xs font-semibold text-slate-500 uppercase">Benefits</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.benefits}</p></div>`;
                if (opp.certification) html += `<div><p class="text-xs font-semibold text-slate-500 uppercase">Certification Required</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>`;
                html += `</div>`;
            }
            html += `
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase">Total Slots</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">${opp.total_slots} available</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase">Deadline</p>
                        <p class="text-sm font-bold text-red-600 dark:text-red-400 mt-1">${opp.deadline ? new Date(opp.deadline).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : 'No deadline'}</p>
                    </div>
                </div>
            `;
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('detailModal').classList.remove('hidden');
        }
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

    function applyToOpportunity(id) {
        const formData = new FormData();
        formData.append('opportunity_id', id);
        formData.append('action', 'apply');
        
        fetch('../api/apply_to_opportunity.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Application submitted successfully.', 'success');
                    
                    // Reload to reflect state changes like cancel button
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Unable to submit application.', 'error');
                }
            })
            .catch(() => {
                showToast('Unable to submit application. Please try again later.', 'error');
            });
    }

    function cancelApplication(id) {
        const formData = new FormData();
        formData.append('opportunity_id', id);
        formData.append('action', 'cancel');
        
        fetch('../api/apply_to_opportunity.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Application cancelled successfully.', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Unable to cancel application.', 'error');
                }
            })
            .catch(() => {
                showToast('Unable to cancel application. Please try again later.', 'error');
            });
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast fixed right-4 bottom-4 z-50 max-w-sm w-full px-4 py-3 rounded-2xl shadow-xl text-sm font-semibold ${type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 400);
        }, 3200);
    }

    function viewApplications(id) {
        window.location.href = 'opportunity-applications.php?opportunity_id=' + id;
    }
    // AJAX Loading Logic
    const sessionRole = "<?php echo $_SESSION['role']; ?>";
    const sessionUserId = "<?php echo $_SESSION['user_id']; ?>";
    const isProvider = <?php echo $isProvider ? 'true' : 'false'; ?>;

    function renderOppCard(opp) {
        let scoreBadge = '';
        if (sessionRole === 'youth' && opp.match_score !== null && opp.type === 'Job Opening') {
            const score = parseInt(opp.match_score);
            const scoreColor = score >= 70 ? 'bg-emerald-100 text-emerald-800' : (score >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600');
            scoreBadge = `<div class="mb-3"><span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold ${scoreColor}"><span class="material-symbols-outlined text-[14px] mr-1">auto_awesome</span>${score}% Match</span></div>`;
        }

        const typeDisplay = opp.type === 'Vocational Training' ? 'Training Opportunity' : opp.type;
        const statusColor = opp.status === 'Open' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        
        let desc = opp.description ? `<p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">${opp.description}</p>` : '';
        let comp = opp.compensation ? `<div><p class="text-xs font-semibold text-slate-500 uppercase">Compensation</p><p class="text-lg font-bold text-slate-900 dark:text-white">${opp.compensation}</p></div>` : '';
        let cert = opp.certification ? `<div><p class="text-xs font-semibold text-slate-500 uppercase">Certification</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>` : '';
        
        const deadline = opp.deadline ? new Date(opp.deadline).toLocaleDateString('en-US', {month:'short',day:'2-digit',year:'numeric'}) : 'No deadline';

        let buttons = `<button onclick="viewOpportunityDetail(${opp.id})" class="flex-1 py-2 px-3 bg-blue-900 text-white rounded-lg text-sm font-semibold hover:bg-blue-800 transition-colors flex items-center justify-center gap-2"><span class="material-symbols-outlined text-base">visibility</span> View Details</button>`;

        if (isProvider && parseInt(opp.provider_id) === parseInt(sessionUserId)) {
            buttons += `<button onclick="openEditModal(${opp.id})" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors"><span class="material-symbols-outlined">edit</span></button>
                <button onclick="deleteOpportunity(${opp.id})" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors"><span class="material-symbols-outlined">delete</span></button>
                <button onclick="viewApplications(${opp.id})" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors"><span class="material-symbols-outlined">people</span></button>`;
        } else if (sessionRole === 'youth') {
            if (!opp.application_status && opp.status === 'Open') {
                buttons += `<button id="apply-btn-${opp.id}" onclick="applyToOpportunity(${opp.id})" class="py-2 px-3 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-base">send</span> Apply</button>`;
            } else if (opp.application_status === 'Pending') {
                buttons += `<button id="cancel-btn-${opp.id}" onclick="cancelApplication(${opp.id})" class="py-2 px-3 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-base">cancel</span> Cancel Application</button>`;
            } else if (opp.application_status) {
                buttons += `<div class="py-2 px-3 bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-lg text-sm font-semibold flex items-center gap-2 cursor-not-allowed"><span class="material-symbols-outlined text-base">check_circle</span> Applied (${opp.application_status})</div>`;
            } else {
                buttons += `<div class="py-2 px-3 bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-400 rounded-lg text-sm font-semibold flex items-center gap-2 cursor-not-allowed"><span class="material-symbols-outlined text-base">block</span> Closed</div>`;
            }
        } else if (sessionRole === 'lydo' || sessionRole === 'sk_chairman') {
            buttons += `<button onclick="viewApplications(${opp.id})" class="py-2 px-3 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-base">people</span> View Applications</button>`;
        }

        return `
        <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all opp-card">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                ${scoreBadge}
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">${typeDisplay}</p>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">${opp.title}</h3>
                    </div>
                    <span class="px-3 py-1 ${statusColor} rounded-full text-xs font-bold">${opp.status}</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">location_on</span> ${opp.location}
                </p>
            </div>
            <div class="p-6 space-y-4">
                ${desc}
                ${comp}
                ${cert}
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase">Deadline</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">${deadline}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase">Slots</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots} available</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex gap-2">
                ${buttons}
            </div>
        </div>`;
    }

    function loadOpportunities() {
        const type = document.getElementById('filter_type')?.value || 'All';
        const search = document.getElementById('filter_search')?.value || '';
        const status = document.getElementById('filter_status')?.value || 'All';
        
        const params = new URLSearchParams({ type, search, status });
        const container = document.getElementById('opportunitiesContainer');
        const loader = document.getElementById('opportunitiesLoading');
        
        loader.classList.remove('hidden');
        
        // Skeletons
        container.innerHTML = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">` + 
            Array(4).fill(0).map(() => `
            <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700"><div class="skeleton-pulse h-4 w-24 rounded mb-2"></div><div class="skeleton-pulse h-6 w-48 rounded"></div></div>
                <div class="p-6 space-y-4"><div class="skeleton-pulse h-4 w-full rounded"></div><div class="skeleton-pulse h-4 w-3/4 rounded"></div><div class="skeleton-pulse h-16 w-full rounded mt-4"></div></div>
            </div>`).join('') + `</div>`;

        fetch(`../api/get_opportunities_data.php?${params}`)
            .then(r => r.json())
            .then(res => {
                loader.classList.add('hidden');
                
                opportunitiesData = {};
                if (res.success && res.opportunities) {
                    res.opportunities.forEach(opp => {
                        opportunitiesData[opp.id] = opp;
                    });
                }
                
                if (!res.success || !res.opportunities.length) {
                    container.innerHTML = `<div class="text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700"><span class="material-symbols-outlined text-5xl text-slate-300 mb-3">work_off</span><p class="text-slate-500 font-semibold text-lg">No opportunities found</p></div>`;
                    return;
                }
                
                if (type === 'All' && res.opportunities.length > 0) {
                    // Split view
                    const trainingOpps = res.opportunities.filter(o => o.type === 'Vocational Training');
                    const jobOpps = res.opportunities.filter(o => o.type === 'Job Opening');
                    const otherOpps = res.opportunities.filter(o => o.type !== 'Vocational Training' && o.type !== 'Job Opening');
                    
                    let html = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">`;
                    // Left: Training + Other
                    html += `<div><h2 class="text-base font-bold text-purple-700 dark:text-purple-400 mb-4 flex items-center gap-2 uppercase tracking-wide"><span class="material-symbols-outlined text-xl">school</span> Training Opportunities</h2>`;
                    if (trainingOpps.length) {
                        html += `<div class="space-y-6">${trainingOpps.map(renderOppCard).join('')}</div>`;
                    } else {
                        html += `<div class="text-center py-10 bg-white dark:bg-slate-800 rounded-xl border border-slate-200"><span class="material-symbols-outlined text-4xl text-slate-300">school</span><p class="text-slate-400 text-sm mt-2">None available.</p></div>`;
                    }
                    if (otherOpps.length) {
                        html += `<h2 class="text-base font-bold text-yellow-700 dark:text-yellow-400 mt-8 mb-4 flex items-center gap-2 uppercase tracking-wide"><span class="material-symbols-outlined text-xl">workspace_premium</span> Scholarships</h2>`;
                        html += `<div class="space-y-6">${otherOpps.map(renderOppCard).join('')}</div>`;
                    }
                    html += `</div>`;
                    
                    // Right: Jobs
                    html += `<div><h2 class="text-base font-bold text-blue-900 dark:text-blue-400 mb-4 flex items-center gap-2 uppercase tracking-wide"><span class="material-symbols-outlined text-xl">work</span> Job Openings</h2>`;
                    if (jobOpps.length) {
                        html += `<div class="space-y-6">${jobOpps.map(renderOppCard).join('')}</div>`;
                    } else {
                        html += `<div class="text-center py-10 bg-white dark:bg-slate-800 rounded-xl border border-slate-200"><span class="material-symbols-outlined text-4xl text-slate-300">work_off</span><p class="text-slate-400 text-sm mt-2">None available.</p></div>`;
                    }
                    html += `</div></div>`;
                    container.innerHTML = html;
                } else {
                    // Single grid
                    container.innerHTML = `<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">${res.opportunities.map(renderOppCard).join('')}</div>`;
                }
            })
            .catch(() => {
                loader.classList.add('hidden');
                container.innerHTML = `<p class="text-red-500">Failed to load opportunities.</p>`;
            });
    }

    // Initialize
    loadOpportunities();
    
    // Event listeners
    ['filter_type', 'filter_status'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', loadOpportunities);
    });
    var searchEl = document.getElementById('filter_search');
    if (searchEl) {
        let debounce;
        searchEl.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(loadOpportunities, 400); });
    }
    var filterForm = document.getElementById('filterForm');
    if (filterForm) filterForm.addEventListener('submit', e => e.preventDefault());

    function resetFilters() {
        if (searchEl) searchEl.value = '';
        if (document.getElementById('filter_type')) document.getElementById('filter_type').value = 'All';
        if (document.getElementById('filter_status')) document.getElementById('filter_status').value = 'All';
        loadOpportunities();
    }
</script>

<style>
.skeleton-pulse {
    background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.4s ease-in-out infinite;
    display: block;
}
.dark .skeleton-pulse {
    background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%);
    background-size: 200% 100%;
}
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
