<?php
$pageTitle = 'Edit Profile';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['lydo', 'sk_chairman']);

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$osyProfile = new OSYProfile($database);
$message = '';
$messageType = '';

$profile = $osyProfile->getById($profile_id);

if (!$profile) {
    header('Location: dashboard.php?error=Profile not found');
    exit;
}

// Scoping check for SK Chairman
if ($_SESSION['role'] === 'sk_chairman') {
    if ($profile['barangay'] !== $_SESSION['barangay']) {
        header('Location: sk-barangay-youth.php?error=unauthorized_barangay');
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
    $data = [
        'first_name' => $_POST['first_name'],
        'middle_name' => $_POST['middle_name'] ?? null,
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'] ?? null,
        'phone' => $_POST['phone'] ?? null,
        'age' => $_POST['age'],
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'],
        'civil_status' => $_POST['civil_status'],
        'barangay' => $_POST['barangay'],
        'education_level' => $_POST['education_level'],
        'primary_skill' => $_POST['primary_skill'],
        'skills' => $_POST['skills'] ?? null,
        'interests' => $_POST['interests'] ?? null,
        'govt_id_type' => $_POST['govt_id_type'] ?? null,
        'govt_id_number' => $_POST['govt_id_number'] ?? null,
        'status' => $_POST['status'] ?? $profile['status'],
        'engagement_status' => $_POST['engagement_status'] ?? $profile['engagement_status']
    ];

    $result = $osyProfile->update($profile_id, $data);
    if ($result['success']) {
        $returnPage = ($_SESSION['role'] === 'sk_chairman') ? 'sk-barangay-youth.php' : 'profiles.php';
        header("Location: profile-detail.php?id={$profile_id}&success=updated");
        exit;
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
    }
}

// Fetch references
require_once __DIR__ . '/../Classes/Reference.php';
$ref = new Reference($database);
$barangays = $ref->getByCategory('barangay');
$eduLevels = $ref->getByCategory('education_level');
$govtIdTypes = $ref->getByCategory('govt_id_type');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-600 dark:text-slate-400 mb-6">
        <a href="dashboard.php" class="hover:text-blue-900">Dashboard</a>
        <span class="mx-2">›</span>
        <?php if ($_SESSION['role'] === 'sk_chairman'): ?>
            <a href="sk-barangay-youth.php" class="hover:text-blue-900">My Barangay</a>
        <?php else: ?>
            <a href="profiles.php" class="hover:text-blue-900">Profiles</a>
        <?php endif; ?>
        <span class="mx-2">›</span>
        <a href="profile-detail.php?id=<?php echo $profile_id; ?>" class="hover:text-blue-900"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></a>
        <span class="mx-2">›</span>
        <span class="text-blue-900 font-semibold">Edit</span>
    </nav>

    <?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl text-sm">
        <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
    </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Profile Details</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Update personal and professional information for <?php echo htmlspecialchars($profile['first_name']); ?>.</p>
        </div>

        <form method="POST" class="p-8 space-y-6">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Age</label>
                    <input type="number" name="age" value="<?php echo htmlspecialchars($profile['age']); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gender</label>
                    <select name="gender" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Male" <?php echo ($profile['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($profile['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($profile['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Civil Status</label>
                    <select name="civil_status" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Single" <?php echo ($profile['civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($profile['civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo ($profile['civil_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Solo Parent" <?php echo ($profile['civil_status'] === 'Solo Parent') ? 'selected' : ''; ?>>Solo Parent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Barangay</label>
                    <select name="barangay" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" <?php echo ($_SESSION['role'] === 'sk_chairman') ? 'disabled' : ''; ?>>
                        <?php foreach ($barangays as $b): ?>
                            <option value="<?php echo htmlspecialchars($b); ?>" <?php echo ($profile['barangay'] === $b) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($_SESSION['role'] === 'sk_chairman'): ?>
                        <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($profile['barangay']); ?>">
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Education Level</label>
                    <select name="education_level" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <?php foreach ($eduLevels as $lvl): ?>
                            <option value="<?php echo htmlspecialchars($lvl); ?>" <?php echo ($profile['education_level'] === $lvl) ? 'selected' : ''; ?>><?php echo htmlspecialchars($lvl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Primary Skill</label>
                    <input type="text" name="primary_skill" value="<?php echo htmlspecialchars($profile['primary_skill'] ?? ''); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Engagement Status</label>
                    <select name="engagement_status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Studying" <?php echo ($profile['engagement_status'] === 'Studying') ? 'selected' : ''; ?>>Studying</option>
                        <option value="Working" <?php echo ($profile['engagement_status'] === 'Working') ? 'selected' : ''; ?>>Working</option>
                        <option value="Self-Employed" <?php echo ($profile['engagement_status'] === 'Self-Employed') ? 'selected' : ''; ?>>Self-Employed</option>
                        <option value="Seeking Employment" <?php echo ($profile['engagement_status'] === 'Seeking Employment') ? 'selected' : ''; ?>>Seeking Employment</option>
                        <option value="Unemployed" <?php echo ($profile['engagement_status'] === 'Unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Platform Status</label>
                    <select name="status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Active" <?php echo ($profile['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Employed" <?php echo ($profile['status'] === 'Employed') ? 'selected' : ''; ?>>Employed</option>
                        <option value="In Training" <?php echo ($profile['status'] === 'In Training') ? 'selected' : ''; ?>>In Training</option>
                        <option value="Inactive" <?php echo ($profile['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="profile-detail.php?id=<?php echo $profile_id; ?>" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800 shadow-lg shadow-blue-900/20 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
