<?php
$pageTitle = 'KK Profiles';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$osyProfile = new OSYProfile($database);
$message = '';

// Handle form submissions first so redirect headers work
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['create_profile'])) {
        $result = $osyProfile->create([
            'profile_type' => $_POST['profile_type'] ?? 'OSY',
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civil_status'] ?? 'Single',
            'education_level' => $_POST['education_level'],
            'barangay' => $_POST['barangay'],
            'primary_skill' => $_POST['primary_skill'],
            'skills' => $_POST['primary_skill'],
            'interests' => null,
            'govt_id_type' => null,
            'govt_id_number' => null,
            'reason_for_not_in_school' => null,
            'engagement_status' => null,
            'status' => $_POST['status'] ?? 'Active',
            'registration_status' => 'Submitted',
            'date_of_birth' => $_POST['date_of_birth'] ?? null
        ]);
        $message = $result['message'];
        if ($result['success']) {
            header('Location: profiles.php');
            exit;
        }
    } elseif (isset($_POST['update_profile'])) {
        $result = $osyProfile->update($_POST['profile_id'], [
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'age' => $_POST['age'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civil_status'] ?? 'Single',
            'education_level' => $_POST['education_level'],
            'barangay' => $_POST['barangay'],
            'primary_skill' => $_POST['primary_skill'],
            'skills' => $_POST['skills'] ?? '',
            'interests' => $_POST['interests'] ?? '',
            'govt_id_type' => $_POST['govt_id_type'] ?? null,
            'govt_id_number' => $_POST['govt_id_number'] ?? null,
            'engagement_status' => $_POST['engagement_status'] ?? null,
            'status' => $_POST['status'],
            'date_of_birth' => $_POST['date_of_birth'] ?? null
        ]);
        $message = $result['message'];
        if ($result['success']) {
            header('Location: profiles.php');
            exit;
        }
    } elseif (isset($_POST['delete_profile'])) {
        $result = $osyProfile->delete($_POST['profile_id']);
        $message = $result['message'];
        if ($result['success']) {
            header('Location: profiles.php');
            exit;
        }
    }
}

// Get filters
$filters = [
    'profile_type' => $_GET['profile_type'] ?? 'All Types',
    'barangay' => $_GET['barangay'] ?? 'All Barangays',
    'gender' => $_GET['gender'] ?? 'All Genders',
    'education' => $_GET['education'] ?? 'Any Level',
    'status' => $_GET['status'] ?? 'All Status',
    'search' => $_GET['search'] ?? ''
];

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$totalFiltered = $osyProfile->getFilteredCount($filters);
$profiles = $osyProfile->getAll($filters, $limit, $offset);
$totalPages = ceil($totalFiltered / $limit);

require_once __DIR__ . '/../Classes/Reference.php';
$reference = new Reference($database);
$govtIdTypes = $reference->getByCategory('govt_id_type');
$barangays = $reference->getByCategory('barangay');
$eduLevels = $reference->getByCategory('education_level');
$reasons = $reference->getByCategory('reason');

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-600 mb-2">
            <span>Directory</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900 font-semibold">KK Profiles</span>
        </nav>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">KK Profile Management</h2>
        <p class="text-slate-600 mt-1">Manage and track all youth profiles (15-30) in your jurisdiction.</p>
    </div>
    <a href="member-registry.php" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-900 to-blue-800 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined">person_add</span>
        Add New Profile
    </a>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo strpos($message, 'successfully') !== false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo strpos($message, 'successfully') !== false ? 'text-green-800' : 'text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<!-- Filters -->
<form method="GET" id="filterForm" class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4 mb-8">
    <div class="relative mb-2">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input type="text" name="search" id="filter_search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search profiles by name or email (Press Enter)..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 pl-12 pr-4 text-sm focus:ring-2 focus:ring-blue-900 transition-all text-slate-900 dark:text-white" onkeypress="if(event.key === 'Enter') { event.preventDefault(); this.form.submit(); }" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Profile Type</label>
            <select name="profile_type" id="filter_type" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2.5 pl-4 pr-4 text-sm mt-1.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" onchange="this.form.submit()">
                <option value="All Types" <?php echo $filters['profile_type'] === 'All Types' ? 'selected' : ''; ?>>All Types</option>
                <option value="OSY" <?php echo $filters['profile_type'] === 'OSY' ? 'selected' : ''; ?>>OSY</option>
                <option value="Regular" <?php echo $filters['profile_type'] === 'Regular' ? 'selected' : ''; ?>>Regular</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Barangay</label>
            <select name="barangay" id="filter_barangay" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2.5 pl-4 pr-4 text-sm mt-1.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" onchange="this.form.submit()">
                <option value="All Barangays" <?php echo $filters['barangay'] === 'All Barangays' ? 'selected' : ''; ?>>All Barangays</option>
                <?php foreach ($barangays as $b): ?>
                    <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $filters['barangay'] === $b ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Gender</label>
            <select name="gender" id="filter_gender" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2.5 pl-4 pr-4 text-sm mt-1.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" onchange="this.form.submit()">
                <option value="All Genders" <?php echo $filters['gender'] === 'All Genders' ? 'selected' : ''; ?>>All Genders</option>
                <option value="Male" <?php echo $filters['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $filters['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Education</label>
            <select name="education" id="filter_education" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2.5 pl-4 pr-4 text-sm mt-1.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" onchange="this.form.submit()">
                <option value="Any Level" <?php echo $filters['education'] === 'Any Level' ? 'selected' : ''; ?>>Any Level</option>
                <?php foreach ($eduLevels as $e): ?>
                    <option value="<?php echo htmlspecialchars($e); ?>" <?php echo $filters['education'] === $e ? 'selected' : ''; ?>><?php echo htmlspecialchars($e); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase pl-1">Status</label>
            <select name="status" id="filter_status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2.5 pl-4 pr-4 text-sm mt-1.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-900" onchange="this.form.submit()">
                <option value="All Status" <?php echo $filters['status'] === 'All Status' ? 'selected' : ''; ?>>All Status</option>
                <option value="Active" <?php echo $filters['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Employed" <?php echo $filters['status'] === 'Employed' ? 'selected' : ''; ?>>Employed</option>
                <option value="In Training" <?php echo $filters['status'] === 'In Training' ? 'selected' : ''; ?>>In Training</option>
            </select>
        </div>
    </div>
    <input type="submit" class="hidden" />
</form>

<!-- Profiles Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Profile</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Full Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Type</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Age</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Barangay</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Skill</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="profilesTableBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                <?php if (!empty($profiles)): ?>
                    <?php foreach ($profiles as $profile): ?>
                        <tr class="profile-row hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-full bg-blue-200 dark:bg-blue-900 flex items-center justify-center text-blue-900 dark:text-blue-200 font-bold">
                                    <?php echo strtoupper(substr($profile['first_name'], 0, 1)); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($profile['email']); ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-2 py-1 <?php echo $profile['profile_type'] === 'OSY' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-900 dark:text-orange-300' : 'bg-green-100 dark:bg-green-900/30 text-green-900 dark:text-green-300'; ?> rounded-md">
                                    <?php echo htmlspecialchars($profile['profile_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"><?php echo $profile['age']; ?></td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($profile['barangay']); ?></td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-300 rounded-md"><?php echo htmlspecialchars($profile['primary_skill']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold <?php echo ($profile['status'] == 'Active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'); ?>">
                                    <?php echo htmlspecialchars($profile['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="profile-detail.php?id=<?php echo $profile['id']; ?>" class="p-2 text-blue-900 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-all inline-block" title="View">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </a>
                                <button onclick="openEditModal(<?php echo $profile['id']; ?>)" class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-all inline-block" title="Edit">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button onclick="deleteProfile(<?php echo $profile['id']; ?>)" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-all inline-block" title="Delete">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 opacity-30">person_search</span>
                                <p class="font-medium">No profiles found</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 bg-slate-100 dark:bg-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
        <span class="text-sm text-slate-600 dark:text-slate-400">Showing <?php echo count($profiles); ?> of <?php echo $totalFiltered; ?> entries (Page <?php echo $page; ?> of <?php echo max(1, $totalPages); ?>)</span>
        
        <?php if ($totalPages > 1): ?>
        <div class="flex gap-2">
            <?php 
                $queryParams = $_GET; 
                if ($page > 1): 
                    $queryParams['page'] = $page - 1;
            ?>
                <a href="?<?php echo http_build_query($queryParams); ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">Previous</a>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): 
                $queryParams['page'] = $page + 1;
            ?>
                <a href="?<?php echo http_build_query($queryParams); ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create/Edit Profile Modal -->
<div id="profileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Add New Profile</h3>
            </div>
            <form id="profileForm" method="POST" class="p-6 space-y-4">
                <input type="hidden" id="profileId" name="profile_id">
                <input type="hidden" id="isUpdate" name="update_profile" value="0">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">First Name</label>
                        <input type="text" name="first_name" id="first_name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Last Name</label>
                        <input type="text" name="last_name" id="last_name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Email</label>
                        <input type="email" name="email" id="email" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Phone</label>
                        <input type="tel" name="phone" id="phone" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Age</label>
                        <input type="number" name="age" id="age" min="10" max="99" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Gender</label>
                        <select name="gender" id="gender" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Civil Status</label>
                        <select name="civil_status" id="civil_status" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Solo Parent">Solo Parent</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Barangay</label>
                        <select name="barangay" id="barangay" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Barangay</option>
                            <?php foreach ($barangays as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Govt ID Type</label>
                        <select name="govt_id_type" id="govt_id_type" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">None</option>
                            <?php foreach ($govtIdTypes as $idType): ?>
                                <option value="<?php echo htmlspecialchars($idType); ?>"><?php echo htmlspecialchars($idType); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Govt ID Number</label>
                        <input type="text" name="govt_id_number" id="govt_id_number" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Education Level</label>
                        <select name="education_level" id="education_level" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Education</option>
                            <?php foreach ($eduLevels as $lvl): ?>
                                <option value="<?php echo htmlspecialchars($lvl); ?>"><?php echo htmlspecialchars($lvl); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Primary Skill</label>
                        <input type="text" name="primary_skill" id="primary_skill" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Complete Skills</label>
                    <textarea name="skills" id="skills" rows="2" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>
                
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Interests</label>
                    <textarea name="interests" id="interests" rows="2" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white"></textarea>
                </div>

                <div id="statusContainer" class="hidden grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Engagement Status</label>
                        <select name="engagement_status" id="engagement_status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Status</option>
                            <option value="Studying">Studying (School/College)</option>
                            <option value="Working">Working (Employed)</option>
                            <option value="Self-Employed">Self-Employed</option>
                            <option value="Seeking Employment">Seeking Employment</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Homemaker">Homemaker</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Platform Status</label>
                        <select name="status" id="status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="Active">Active</option>
                            <option value="Employed">Employed</option>
                            <option value="In Training">In Training</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800">
                        Add Profile
                    </button>
                </div>
            </form>
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
                <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Delete Profile</h3>
                <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6">Are you sure? This action cannot be undone.</p>
                <form method="POST" class="flex gap-3">
                    <input type="hidden" id="deleteProfileId" name="profile_id">
                    <input type="hidden" name="delete_profile" value="1">
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
    function openCreateModal(type) {
        document.getElementById('modalTitle').textContent = 'Add New Profile (' + type + ')';
        document.getElementById('isUpdate').value = '0';
        document.getElementById('isUpdate').name = 'create_profile';
        
        // Let form pass the hidden input 'profile_type'
        let input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "profile_type");
        input.setAttribute("value", type);
        input.id = "hiddenProfileType";
        
        let oldInp = document.getElementById("hiddenProfileType");
        if(oldInp) oldInp.remove();
        document.getElementById("profileForm").appendChild(input);

        document.getElementById('submitBtn').textContent = 'Save Profile';
        document.getElementById('statusContainer').classList.add('hidden');
        document.getElementById('profileForm').reset();
        document.getElementById('profileModal').classList.remove('hidden');
    }

    function openEditModal(id) {
        const profile = profilesData[id];
        if (profile) {
            document.getElementById('modalTitle').textContent = 'Edit OSY Profile';
            document.getElementById('profileId').value = id;
            document.getElementById('isUpdate').value = '1';
            document.getElementById('isUpdate').name = 'update_profile';
            document.getElementById('submitBtn').textContent = 'Update Profile';
            document.getElementById('statusContainer').classList.remove('hidden');

            document.getElementById('first_name').value = profile.first_name || '';
            document.getElementById('middle_name').value = profile.middle_name || '';
            document.getElementById('last_name').value = profile.last_name || '';
            document.getElementById('email').value = profile.email || '';
            document.getElementById('phone').value = profile.phone || '';
            document.getElementById('age').value = profile.age || '';
            document.getElementById('date_of_birth').value = profile.date_of_birth || '';
            document.getElementById('gender').value = profile.gender || '';
            document.getElementById('civil_status').value = profile.civil_status || 'Single';
            document.getElementById('barangay').value = profile.barangay || '';
            document.getElementById('govt_id_type').value = profile.govt_id_type || '';
            document.getElementById('govt_id_number').value = profile.govt_id_number || '';
            document.getElementById('education_level').value = profile.education_level || '';
            document.getElementById('primary_skill').value = profile.primary_skill || '';
            document.getElementById('skills').value = profile.skills || '';
            document.getElementById('interests').value = profile.interests || '';
            document.getElementById('status').value = profile.status || 'Active';
            document.getElementById('engagement_status').value = profile.engagement_status || '';

            document.getElementById('profileModal').classList.remove('hidden');
        }
    }

    function closeModal() {
        document.getElementById('profileModal').classList.add('hidden');
    }

    function deleteProfile(id) {
        document.getElementById('deleteProfileId').value = id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Store profiles data for JS access
    var profilesData = {};
    <?php foreach ($profiles as $profile): ?>
        profilesData[<?php echo $profile['id']; ?>] = <?php echo json_encode($profile); ?>;
    <?php endforeach; ?>

    // Real-time filtering handled by form submission now
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>