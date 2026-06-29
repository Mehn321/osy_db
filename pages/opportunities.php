<?php
$pageTitle = 'Opportunities';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole('lydo');

require_once __DIR__ . '/../includes/header.php';

$opportunity = new Opportunity($database);
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                'deadline' => $_POST['deadline']
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

if (isset($_GET['success'])) {
    $actions = ['created' => 'Opportunity created successfully!', 'updated' => 'Opportunity updated successfully!', 'deleted' => 'Opportunity deleted successfully!'];
    $message = $actions[$_GET['success']] ?? 'Action completed!';
}

// Get opportunities based on user role
if (in_array($_SESSION['role'], ['employer', 'training_provider'])) {
    // Providers see their own opportunities
    $opportunities = $opportunity->getByProvider();
    $isProvider = true;
    $canCreate = ($_SESSION['status'] === 'Active');
} elseif ($_SESSION['role'] === 'youth' && $_SESSION['status'] === 'Active') {
    // Youth see available opportunities to apply
    $filters = [
        'search' => $_GET['search'] ?? '',
        'type' => $_GET['type'] ?? 'All',
        'location' => $_GET['location'] ?? '',
        'skill' => $_GET['skill'] ?? ''
    ];

    // If skill filter is applied, filter by required skills
    if (!empty($filters['skill'])) {
        $opportunities = $opportunity->getByRequiredSkill($filters['skill'], ['type' => $filters['type']]);
        // Then apply other filters
        if (!empty($filters['location'])) {
            $opportunities = array_filter($opportunities, function ($opp) use ($filters) {
                return stripos($opp['location'], $filters['location']) !== false;
            });
        }
        if (!empty($filters['search'])) {
            $opportunities = array_filter($opportunities, function ($opp) use ($filters) {
                return stripos($opp['title'], $filters['search']) !== false ||
                    stripos($opp['description'], $filters['search']) !== false;
            });
        }
    } else {
        $opportunities = $opportunity->getForYouth($filters);
    }
    $isProvider = false;
    $canCreate = false;
} else {
    // LYDO and SK Chairman see all opportunities
    $filters = [
        'search' => $_GET['search'] ?? '',
        'type' => $_GET['type'] ?? 'All',
        'status' => $_GET['status'] ?? 'All'
    ];
    $opportunities = $opportunity->getAll($filters);
    $isProvider = false;
    $canCreate = false;
}
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
            <p class="text-slate-600 max-w-2xl">Browse and apply to job openings, training programs, and scholarships that match your skills.</p>
        <?php else: ?>
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">Opportunity Management</h1>
            <p class="text-slate-600 max-w-2xl">Curate and manage vocational training, employment roles, and scholarships for the youth community.</p>
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
    <form method="GET" class="grid grid-cols-1 lg:grid-cols-<?php echo $_SESSION['role'] === 'youth' ? '5' : '4'; ?> gap-4 items-end">
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Search</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search opportunities..." class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Type</label>
            <select name="type" class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All" <?php echo $filters['type'] === 'All' ? ' selected' : ''; ?>>All Types</option>
                <option value="Job Opening" <?php echo $filters['type'] === 'Job Opening' ? ' selected' : ''; ?>>Job Opening</option>
                <option value="Vocational Training" <?php echo $filters['type'] === 'Vocational Training' ? ' selected' : ''; ?>>Vocational Training</option>
                <option value="Scholarship" <?php echo $filters['type'] === 'Scholarship' ? ' selected' : ''; ?>>Scholarship</option>
            </select>
        </div>
        <?php if ($_SESSION['role'] === 'youth'): ?>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Required Skill</label>
                <input type="text" name="skill" value="<?php echo htmlspecialchars($filters['skill'] ?? ''); ?>" placeholder="Filter by required skill..." class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
            </div>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($filters['location'] ?? ''); ?>" placeholder="Filter by location..." class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
            </div>
        <?php elseif (!$isProvider): ?>
            <div>
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Status</label>
                <select name="status" class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                    <option value="All" <?php echo $filters['status'] === 'All' ? ' selected' : ''; ?>>All Status</option>
                    <option value="Open" <?php echo $filters['status'] === 'Open' ? ' selected' : ''; ?>>Open</option>
                    <option value="Closed" <?php echo $filters['status'] === 'Closed' ? ' selected' : ''; ?>>Closed</option>
                </select>
            </div>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
        <div class="flex gap-3">
            <button type="submit" class="w-full py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800 transition-all">Apply Filters</button>
            <a href="opportunities.php" class="w-full py-3 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl font-bold text-sm text-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">Reset</a>
        </div>
    </form>
</div>

<!-- Opportunities Cards Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (!empty($opportunities)): ?>
        <?php foreach ($opportunities as $opp): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1"><?php echo htmlspecialchars($opp['type']); ?></p>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($opp['title']); ?></h3>
                        </div>
                        <span class="px-3 py-1 <?php echo $opp['status'] === 'Open' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'; ?> rounded-full text-xs font-bold">
                            <?php echo htmlspecialchars($opp['status']); ?>
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">location_on</span>
                        <?php echo htmlspecialchars($opp['location']); ?>
                    </p>
                </div>

                <div class="p-6 space-y-4">
                    <?php if ($opp['description']): ?>
                        <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2"><?php echo htmlspecialchars($opp['description']); ?></p>
                    <?php endif; ?>

                    <?php if ($opp['compensation']): ?>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Compensation</p>
                            <p class="text-lg font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($opp['compensation']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($opp['certification']): ?>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Certification</p>
                            <p class="text-sm text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($opp['certification']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Deadline</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo !empty($opp['deadline']) ? date('M d, Y', strtotime($opp['deadline'])) : 'No deadline'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Slots</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white"><?php echo $opp['total_slots']; ?> available</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex gap-2">
                    <button onclick="viewOpportunityDetail(<?php echo $opp['id']; ?>)" class="flex-1 py-2 px-3 bg-blue-900 text-white rounded-lg text-sm font-semibold hover:bg-blue-800 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        View Details
                    </button>
                    <?php if ($isProvider && $opp['provider_id'] == $_SESSION['user_id']): ?>
                        <button onclick="openEditModal(<?php echo $opp['id']; ?>)" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button onclick="deleteOpportunity(<?php echo $opp['id']; ?>)" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    <?php elseif ($_SESSION['role'] === 'youth' && $opp['status'] === 'Open'): ?>
                        <button onclick="applyToOpportunity(<?php echo $opp['id']; ?>)" class="py-2 px-3 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">send</span>
                            Apply
                        </button>
                    <?php elseif ($_SESSION['role'] === 'lydo' || $_SESSION['role'] === 'sk_chairman'): ?>
                        <button onclick="viewApplications(<?php echo $opp['id']; ?>)" class="py-2 px-3 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-base">people</span>
                            View Applications
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="lg:col-span-2 text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-3">work_off</span>
            <p class="text-slate-500 font-semibold text-lg">No opportunities found</p>
            <p class="text-sm text-slate-400 mt-1">Create a new job opening or training program to get started.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Opportunity Modal -->
<div id="opportunityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Create New Opportunity</h3>
            </div>
            <form id="opportunityForm" method="POST" class="p-6 space-y-4">
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
                        <option value="Vocational Training">Vocational Training</option>
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

<!-- View Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Opportunity Details</h3>
                <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
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
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Delete Opportunity</h3>
                <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6">Are you sure? This action cannot be undone.</p>
                <form method="POST" class="flex gap-3">
                    <input type="hidden" id="deleteOpportunityId" name="opportunity_id">
                    <input type="hidden" name="delete_opportunity" value="1">
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
    <?php foreach ($opportunities as $opp): ?>
        opportunitiesData[<?php echo $opp['id']; ?>] = <?php echo json_encode($opp); ?>;
    <?php endforeach; ?>

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
            document.getElementById('opp_status').value = opp.status;

            document.getElementById('opportunityModal').classList.remove('hidden');
        }
    }

    function viewOpportunityDetail(id) {
        const opp = opportunitiesData[id];
        if (opp) {
            let html = `
                <div><p class="text-xs font-bold text-slate-500 uppercase">Title</p><p class="font-bold text-slate-900 dark:text-white text-lg">${opp.title}</p></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Type</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.type}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Status</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.status}</p></div>
                </div>
                <div><p class="text-xs font-bold text-slate-500 uppercase">Location</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.location}</p></div>
            `;
            if (opp.description) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Description</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.description}</p></div>`;
            if (opp.compensation) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Compensation</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.compensation}</p></div>`;
            if (opp.benefits) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Benefits</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.benefits}</p></div>`;
            if (opp.certification) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Certification</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.certification}</p></div>`;
            html += `
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Slots</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.total_slots}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Deadline</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.deadline}</p></div>
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
        if (!confirm('Are you sure you want to apply for this opportunity?')) {
            return;
        }

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
                } else {
                    showToast(data.message || 'Unable to submit application.', 'error');
                }
            })
            .catch(() => {
                showToast('Unable to submit application. Please try again later.', 'error');
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
        // Redirect to applications view page (we'll create this)
        window.location.href = 'opportunity-applications.php?opportunity_id=' + id;
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>