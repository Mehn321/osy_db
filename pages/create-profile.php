<?php
$pageTitle = 'Add New Profile';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole(['lydo', 'sk_chairman']);

$message = '';
$messageType = '';
$profileType = $_GET['type'] ?? 'OSY';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_profile'])) {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This form has already been submitted or the session expired. Please refresh the page and try again.';
        $messageType = 'error';
    } else {
        $osyProfile = new OSYProfile($database);

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
            'barangay' => ($_SESSION['role'] === 'sk_chairman') ? $_SESSION['barangay'] : $_POST['barangay'],
            'education_level' => $_POST['education_level'],
            'primary_skill' => $_POST['primary_skill'],
            'skills' => $_POST['skills'] ?? null,
            'interests' => $_POST['interests'] ?? null,
            'govt_id_type' => $_POST['govt_id_type'] ?? null,
            'govt_id_number' => $_POST['govt_id_number'] ?? null,
            'profile_type' => $_POST['profile_type'],
            'created_by' => $_SESSION['user_id'],
            'status' => 'Active',
            'verification_status' => ($_SESSION['role'] === 'sk_chairman') ? 'Verified' : 'Pending' // SK can auto-verify if they encode it
        ];

        $result = $osyProfile->create($data);
        if ($result['success']) {
            $returnPage = ($_SESSION['role'] === 'sk_chairman') ? 'sk-barangay-youth.php' : 'profiles.php';
            header("Location: {$returnPage}?success=created");
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
        <span class="text-blue-900 font-semibold">Add Profile</span>
    </nav>

    <?php if ($message): ?>
        <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl text-sm">
            <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Register New <?php echo $profileType; ?></h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Fill out the form below to add a new youth record to the system.</p>
        </div>

        <form method="POST" class="p-8 space-y-6">
            <input type="hidden" name="create_profile" value="1">
            <input type="hidden" name="profile_type" value="<?php echo htmlspecialchars($profileType); ?>">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">First Name</label>
                    <input type="text" name="first_name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Middle Name</label>
                    <input type="text" name="middle_name" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Last Name</label>
                    <input type="text" name="last_name" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
                    <input type="email" name="email" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone Number</label>
                    <input type="tel" name="phone" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Age</label>
                    <input type="number" name="age" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gender</label>
                    <select name="gender" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Civil Status</label>
                    <select name="civil_status" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Solo Parent">Solo Parent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Barangay</label>
                    <?php if ($_SESSION['role'] === 'sk_chairman'): ?>
                        <input type="text" value="<?php echo htmlspecialchars($_SESSION['barangay']); ?>" disabled class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm text-slate-500">
                        <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($_SESSION['barangay']); ?>">
                    <?php else: ?>
                        <select name="barangay" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="">Select Barangay</option>
                            <?php foreach ($barangays as $b): ?>
                                <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Education Level</label>
                    <select name="education_level" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                        <option value="">Select Education</option>
                        <?php foreach ($eduLevels as $lvl): ?>
                            <option value="<?php echo htmlspecialchars($lvl); ?>"><?php echo htmlspecialchars($lvl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Primary Skill</label>
                    <input type="text" name="primary_skill" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="<?php echo ($_SESSION['role'] === 'sk_chairman') ? 'sk-barangay-youth.php' : 'profiles.php'; ?>" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800 shadow-lg shadow-blue-900/20 transition-all">Register Profile</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>