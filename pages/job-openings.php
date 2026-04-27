<?php
$pageTitle = 'Job Openings';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$opportunity = new Opportunity($database);
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_opportunity'])) {
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
            header('Location: job-openings.php?success=created');
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
            header('Location: job-openings.php?success=updated');
            exit;
        }
    } elseif (isset($_POST['delete_opportunity'])) {
        $result = $opportunity->delete($_POST['opportunity_id']);
        $message = $result['message'];
        if ($result['success']) {
            header('Location: job-openings.php?success=deleted');
            exit;
        }
    }
}

if (isset($_GET['success'])) {
    $actions = ['created' => 'Opportunity created successfully!', 'updated' => 'Opportunity updated successfully!', 'deleted' => 'Opportunity deleted successfully!'];
    $message = $actions[$_GET['success']] ?? 'Action completed!';
}

$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => 'jobs',
    'status' => $_GET['status'] ?? 'All'
];
$opportunities = $opportunity->getAll($filters);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
    <div class="space-y-1">
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-2">
            <span>Opportunities</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900">Job Openings</span>
        </nav>
        <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">Job Openings</h1>
        <p class="text-slate-600 max-w-2xl">Manage employment opportunities and professional roles available for the youth community.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <button onclick="openCreateModal('Job Opening')" class="px-6 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">work</span>
            Encode New Job
        </button>
    </div>
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Search</label>
            <input type="text" id="job_search" oninput="filterJobs()" placeholder="Search job openings..." class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all" />
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Status</label>
            <select id="job_status" onchange="filterJobs()" class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl py-3 px-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900 border border-transparent transition-all">
                <option value="All">All Status</option>
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
        <div class="flex gap-3">
            <a href="job-openings.php" class="w-full py-3 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl font-bold text-sm text-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">Reset List</a>
        </div>
    </div>
</div>

<!-- Opportunities Cards Grid -->
<div id="jobCardsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (!empty($opportunities)): ?>
    <?php foreach ($opportunities as $opp): ?>
        <div class="job-card bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-lg transition-all"
             data-status="<?php echo htmlspecialchars($opp['status']); ?>"
             data-search="<?php echo strtolower(htmlspecialchars($opp['title'] . ' ' . $opp['location'] . ' ' . $opp['type'])); ?>">
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
                <button onclick="openEditModal(<?php echo $opp['id']; ?>)" class="py-2 px-3 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-slate-600 dark:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button onclick="deleteOpportunity(<?php echo $opp['id']; ?>)" class="py-2 px-3 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 transition-colors">
                    <span class="material-symbols-outlined">delete</span>
                </button>
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Employment Type</label>
                        <select name="employment_type" id="opp_employment_type" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Work Schedule</label>
                        <select name="work_schedule" id="opp_work_schedule" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Schedule</option>
                            <option value="Day Shift">Day Shift</option>
                            <option value="Night Shift">Night Shift</option>
                            <option value="Flexible">Flexible</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Experience Required</label>
                        <select name="experience_req" id="opp_experience_req" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Experience</option>
                            <option value="Entry Level">Entry Level (0 yrs)</option>
                            <option value="1-2 Years">1-2 Years</option>
                            <option value="3+ Years">3+ Years</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Compensation</label>
                        <input type="text" name="compensation" id="opp_compensation" placeholder="e.g., ₱15,000/month" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Benefits</label>
                    <textarea name="benefits" id="opp_benefits" rows="2" placeholder="e.g., Health insurance, 13th month pay" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Job Description</label>
                    <textarea name="description" id="opp_description" rows="3" placeholder="Details about the job role..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
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
            document.getElementById('opp_employment_type').value = opp.employment_type || '';
            document.getElementById('opp_work_schedule').value = opp.work_schedule || '';
            document.getElementById('opp_experience_req').value = opp.experience_req || '';
            document.getElementById('opp_compensation').value = opp.compensation || '';
            document.getElementById('opp_benefits').value = opp.benefits || '';
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
            if (opp.employment_type) {
                html += `<div class="grid grid-cols-2 gap-4 pt-2">
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Employment Type</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.employment_type}</p></div>
                    <div><p class="text-xs font-bold text-slate-500 uppercase">Work Schedule</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.work_schedule || 'N/A'}</p></div>
                </div>`;
            }
            if (opp.experience_req) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Experience Required</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.experience_req}</p></div>`;
            if (opp.description) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Job Description</p><p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">${opp.description}</p></div>`;
            if (opp.compensation) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Compensation</p><p class="text-sm font-bold text-slate-900 dark:text-white">${opp.compensation}</p></div>`;
            if (opp.benefits) html += `<div><p class="text-xs font-bold text-slate-500 uppercase">Benefits</p><p class="text-sm text-slate-700 dark:text-slate-300">${opp.benefits}</p></div>`;
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
    function filterJobs() {
        const searchVal = document.getElementById('job_search').value.toLowerCase();
        const statusVal = document.getElementById('job_status').value;
        const cards = document.querySelectorAll('.job-card');
        
        cards.forEach(card => {
            const dataSearch = card.getAttribute('data-search');
            const dataStatus = card.getAttribute('data-status');
            
            let match = true;
            if (searchVal && !dataSearch.includes(searchVal)) match = false;
            if (statusVal !== 'All' && dataStatus !== statusVal) match = false;
            
            card.style.display = match ? '' : 'none';
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>