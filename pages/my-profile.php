<?php

/**
 * Youth Personal Profile Management
 * 
 * Allows verified youth to view and edit their own profile information.
 * Shows profile verification status and remarks if declined.
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Only youth can access this page
if ($_SESSION['role'] !== 'youth') {
    header('Location: dashboard.php?error=Unauthorized access');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$osyProfile = new OSYProfile($database);
$notification = new Notification($database);
$message = '';
$messageType = '';

// Get youth's profile
$profile = $osyProfile->getByUserId($_SESSION['user_id']);

if (!$profile) {
    echo '<div class="max-w-4xl mx-auto p-6 text-center">';
    echo '<p class="text-slate-600">No profile found. Please complete your registration first.</p>';
    echo '<a href="../pages/youth-signup-enhanced.php" class="mt-4 inline-block px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">Complete Registration</a>';
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This form has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $data = [
            'first_name' => $_POST['first_name'] ?? $profile['first_name'],
            'middle_name' => $_POST['middle_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? $profile['last_name'],
            'email' => $_POST['email'] ?? $profile['email'],
            'phone' => $_POST['phone'] ?? $profile['phone'],
            'age' => $_POST['age'] ?? $profile['age'],
            'date_of_birth' => $_POST['date_of_birth'] ?? $profile['date_of_birth'],
            'gender' => $_POST['gender'] ?? $profile['gender'],
            'civil_status' => $_POST['civil_status'] ?? $profile['civil_status'],
            'barangay' => $profile['barangay'], // Cannot change barangay
            'education_level' => $_POST['education_level'] ?? $profile['education_level'],
            'primary_skill' => $_POST['primary_skill'] ?? $profile['primary_skill'],
            'skills' => $_POST['skills'] ?? $profile['skills'],
            'interests' => $_POST['interests'] ?? $profile['interests'],
        ];

        $result = $osyProfile->update($profile['id'], $data);
        if ($result['success']) {
            $resubmitted = false;
            if (in_array($profile['verification_status'], ['Rejected', 'Declined', 'Action Required'], true)) {
                $osyProfile->setVerificationStatus($profile['id'], 'Pending', 'Resubmitted by user');

                // Set user status back to Pending
                $database->execute("UPDATE users SET status = 'Pending' WHERE id = ?", [$profile['created_by']], "i");

                $resubmitted = true;
            }

        // Notify SK Chairman of this barangay
        if ($resubmitted) {
            $skChairman = $database->fetchOne(
                "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status = 'Active' LIMIT 1",
                [$profile['barangay']],
                "s"
            );
            if ($skChairman) {
                $notifObj = new Notification($database);
                $notifObj->sendToUser(
                    $skChairman['id'],
                    'Resubmitted Youth Profile',
                    "Youth member {$profile['first_name']} {$profile['last_name']} has updated and resubmitted their profile for verification.",
                    'System',
                    $profile['created_by']
                );
            }
        }

        $message = 'Profile updated successfully!' . ($resubmitted ? ' Your profile has been resubmitted for verification.' : '');
        $messageType = 'success';
        // Refresh profile data
        $profile = $osyProfile->getById($profile['id']);
    } else {
        $message = $result['message'] ?? 'Error updating profile';
        $messageType = 'error';
    }
    }
}

// Get references for dropdowns
require_once __DIR__ . '/../Classes/Reference.php';
$ref = new Reference($database);
$barangays = $ref->getByCategory('barangay');
$eduLevels = $ref->getByCategory('education_level');
$skills = $ref->getByCategory('skills');
$interests = $ref->getByCategory('interests');

// Parse current skills and interests
$currentSkills = $profile['skills'] ? explode(',', $profile['skills']) : [];
$currentInterests = $profile['interests'] ? explode(',', $profile['interests']) : [];
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
    <div class="space-y-1">
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-2">
            <span>Account</span>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-blue-900">My Profile</span>
        </nav>
        <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">My Profile</h1>
        <p class="text-slate-600 max-w-2xl">View and manage your personal profile information.</p>
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

<!-- Profile Status Banner -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Status Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase">Registration Status</h3>
            <span class="material-symbols-outlined text-slate-400">info</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-4xl <?php
                                                            if ($profile['verification_status'] === 'Verified') echo 'text-green-500';
                                                            elseif ($profile['verification_status'] === 'Declined') echo 'text-red-500';
                                                            else echo 'text-yellow-500';
                                                            ?>">
                <?php
                if ($profile['verification_status'] === 'Verified') echo 'verified_user';
                elseif ($profile['verification_status'] === 'Declined') echo 'cancel';
                else echo 'pending';
                ?>
            </span>
            <div>
                <p class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['verification_status'] ?? 'Pending'); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <?php
                    if ($profile['verification_status'] === 'Verified') {
                        echo 'Your profile has been approved';
                    } elseif ($profile['verification_status'] === 'Declined') {
                        echo 'Your profile requires action';
                    } else {
                        echo 'Awaiting approval from SK Chairman';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Account Status Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase mb-4">Account Status</h3>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($_SESSION['status'] ?? 'Pending'); ?></p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                <?php
                if ($_SESSION['status'] === 'Active') {
                    echo 'You can access all features';
                } else {
                    echo 'Limited access until approved';
                }
                ?>
            </p>
        </div>
    </div>

    <!-- Submissions Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
        <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase mb-4">Recent Updates</h3>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo date('M d, Y', strtotime($profile['updated_at'])); ?></p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Last updated</p>
        </div>
    </div>
</div>

<!-- Verification Remarks (if declined) -->
<?php if ($profile['verification_status'] === 'Declined' && $profile['verification_remark']): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-8">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span>
            </div>
            <div>
                <h4 class="font-bold text-red-900 dark:text-red-300 mb-2">Action Required</h4>
                <p class="text-red-800 dark:text-red-200 text-sm mb-3">
                    Your profile was declined with the following remarks. Please review and resubmit your application.
                </p>
                <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-slate-700 dark:text-slate-300 text-sm italic">
                        <?php echo htmlspecialchars($profile['verification_remark']); ?>
                    </p>
                </div>
                <a href="#profile-section" class="inline-block mt-4 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors">
                    Edit and Resubmit
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Profile Details Form -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white" id="profile-section">Personal Information</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Update your profile details below.</p>
    </div>

    <form method="POST" class="p-8 space-y-6">
        <input type="hidden" name="update_profile" value="1">
        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

        <!-- Name Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">First Name *</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Middle Name</label>
                <input type="text" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Last Name *</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($profile['last_name']); ?>" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
        </div>

        <!-- Contact Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
        </div>

        <!-- Demographics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Date of Birth</label>
                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gender</label>
                <select name="gender" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo $profile['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $profile['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo $profile['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Civil Status</label>
                <select name="civil_status" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    <option value="Single" <?php echo $profile['civil_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                    <option value="Married" <?php echo $profile['civil_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                    <option value="Widowed" <?php echo $profile['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                    <option value="Solo Parent" <?php echo $profile['civil_status'] === 'Solo Parent' ? 'selected' : ''; ?>>Solo Parent</option>
                </select>
            </div>
        </div>

        <!-- Education & Skills -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Education Level</label>
                <select name="education_level" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    <option value="">Select Education Level</option>
                    <?php foreach ($eduLevels as $edu): ?>
                        <option value="<?php echo htmlspecialchars($edu['value']); ?>" <?php echo $profile['education_level'] === $edu['value'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($edu['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Primary Skill</label>
                <select name="primary_skill" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                    <option value="">Select Primary Skill</option>
                    <?php foreach ($skills as $skill): ?>
                        <option value="<?php echo htmlspecialchars($skill['value']); ?>" <?php echo $profile['primary_skill'] === $skill['value'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($skill['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Barangay (Read-only) -->
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Barangay</label>
            <input type="text" value="<?php echo htmlspecialchars($profile['barangay']); ?>" disabled class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl py-3 px-4 text-sm text-slate-500 dark:text-slate-400">
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Barangay cannot be changed. Contact support if needed.</p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Save Changes
            </button>
            <a href="dashboard.php" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Applications Section -->
<?php
$matching = new Matching($database);
if ($profile['verification_status'] === 'Verified') {
    $applications = $matching->getMatchesForOSY($profile['id']);
?>
    <div class="mt-12 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">My Applications</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Track your opportunity applications and their status.</p>
        </div>

        <div class="p-8">
            <?php if (!empty($applications)): ?>
                <div class="space-y-4">
                    <?php foreach ($applications as $app): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($app['opp_title'] ?? 'Opportunity'); ?></p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Match Score: <?php echo round($app['match_score']); ?>%</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 <?php echo $app['status'] === 'Accepted' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($app['status'] === 'Rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'); ?> rounded-full text-xs font-bold">
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-slate-500 dark:text-slate-400">You haven't applied to any opportunities yet. <a href="opportunities.php" class="text-blue-600 hover:text-blue-700 font-semibold">Browse opportunities</a></p>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>