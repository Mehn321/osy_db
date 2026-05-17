<?php
$pageTitle = 'Profile Details';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
requireRole(['lydo', 'sk_chairman']);

require_once __DIR__ . '/../includes/header.php';

$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$osyProfile = new OSYProfile($database);
$message = '';
$messageType = '';

$profile = $osyProfile->getById($profile_id);

if (!$profile) {
    header('Location: ' . ($_SESSION['role'] === 'sk_chairman' ? 'sk-barangay-youth.php' : 'profiles.php') . '?error=Profile not found');
    exit;
}

// Barangay scoping for SK Chairman
if ($_SESSION['role'] === 'sk_chairman') {
    if ($profile['barangay'] !== $_SESSION['barangay']) {
        header('Location: sk-barangay-youth.php?error=unauthorized_barangay');
        exit;
    }
}

// Handle delete (Restricted to LYDO only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    requireRole('lydo'); // Double check role for destructive action
    $result = $osyProfile->delete(intval($_POST['profile_id']));
    if ($result['success']) {
        header('Location: profiles.php?success=deleted');
        exit;
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

$matching = new Matching($database);
$matches = $matching->getMatchesForOSY($profile_id);
?>

<!-- Breadcrumb -->
<nav class="text-sm text-slate-600 dark:text-slate-400 mb-6">
    <a href="dashboard.php" class="hover:text-blue-900 dark:hover:text-blue-400">Dashboard</a>
    <span class="mx-2">›</span>
    <?php if ($_SESSION['role'] === 'sk_chairman'): ?>
        <a href="sk-barangay-youth.php" class="hover:text-blue-900 dark:hover:text-blue-400">My Barangay</a>
    <?php else: ?>
        <a href="profiles.php" class="hover:text-blue-900 dark:hover:text-blue-400">Profiles</a>
    <?php endif; ?>
    <span class="mx-2">›</span>
    <span class="text-blue-900 dark:text-blue-400 font-semibold"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></span>
</nav>

<?php if ($message): ?>
<div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
    <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?>"><?php echo htmlspecialchars($message); ?></p>
</div>
<?php endif; ?>

<!-- Profile Header -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 mb-8">
    <div class="flex flex-col md:flex-row items-start gap-8">
        <!-- Profile Image -->
        <div class="flex-shrink-0">
            <?php if ($profile['image_path']): ?>
                <img src="<?php echo htmlspecialchars($profile['image_path']); ?>" alt="Profile" class="w-24 h-24 rounded-full object-cover">
            <?php else: ?>
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-4xl font-bold">
                <?php echo strtoupper(substr($profile['first_name'], 0, 1) . substr($profile['last_name'], 0, 1)); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Profile Info -->
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-4xl font-bold text-slate-900 dark:text-white">
                    <?php echo htmlspecialchars($profile['first_name'] . ' ' . ($profile['middle_name'] ? $profile['middle_name'] . ' ' : '') . $profile['last_name']); ?>
                </h1>
                <span class="text-xs font-semibold px-2 py-1 <?php echo $profile['profile_type'] === 'OSY' ? 'bg-orange-100 text-orange-900' : 'bg-green-100 text-green-900'; ?> rounded-md">
                    <?php echo htmlspecialchars($profile['profile_type']); ?>
                </span>
            </div>
            <div class="flex items-center gap-4 mb-6">
                <span class="inline-flex px-4 py-2 rounded-lg font-semibold text-sm <?php 
                    if ($profile['status'] == 'Active') echo 'bg-green-100 text-green-900';
                    elseif ($profile['status'] == 'Employed') echo 'bg-blue-100 text-blue-900';
                    elseif ($profile['status'] == 'In Training') echo 'bg-orange-100 text-orange-900';
                    else echo 'bg-slate-100 text-slate-900';
                ?>">
                    <?php echo htmlspecialchars($profile['status']); ?>
                </span>
                <span class="text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($profile['age']); ?> years old</span>
                <?php if ($profile['gender']): ?>
                <span class="text-slate-600 dark:text-slate-400">• <?php echo htmlspecialchars($profile['gender']); ?></span>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Email:</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['email'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Phone:</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['phone'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Civil Status:</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['civil_status'] ?? 'N/A'); ?></p>
                </div>
                <?php if ($profile['date_of_birth']): ?>
                <div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Date of Birth:</span>
                    <p class="text-slate-900 dark:text-white"><?php echo date('M d, Y', strtotime($profile['date_of_birth'])); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($profile['govt_id_type']): ?>
                <div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">ID Type:</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['govt_id_type']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Profile Details Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Main Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Education & Skills -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Education & Skills</h3>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Education Level</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['education_level'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Primary Skill</span>
                    <p class="text-slate-900 dark:text-white font-semibold text-blue-900 dark:text-blue-400"><?php echo htmlspecialchars($profile['primary_skill'] ?? 'N/A'); ?></p>
                </div>
                <?php if ($profile['skills']): ?>
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">All Skills</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['skills']); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($profile['interests']): ?>
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Interests</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['interests']); ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Barangay</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['barangay'] ?? 'N/A'); ?></p>
                </div>
                <?php if ($profile['reason_for_not_in_school']): ?>
                <div>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Reason for Not in School</span>
                    <p class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['reason_for_not_in_school']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Matched Opportunities -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Matched Opportunities</h3>
            <?php if (!empty($matches)): ?>
                <div class="space-y-3">
                    <?php foreach ($matches as $match): ?>
                        <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($match['title'] ?? 'Unknown Opportunity'); ?></h4>
                                <span class="px-2 py-1 text-xs font-bold rounded 
                                <?php
                                if ($match['status'] == 'Accepted') echo 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                                elseif ($match['status'] == 'Pending') echo 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                                elseif ($match['status'] == 'Rejected') echo 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                                ?>">
                                    <?php echo htmlspecialchars($match['status']); ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($match['type'] ?? 'N/A'); ?></span>
                                <span class="text-sm font-bold text-blue-900 dark:text-blue-400">Match Score: <?php echo $match['match_score']; ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-600 dark:text-slate-400 text-center py-8">No matched opportunities yet</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Quick Stats</h3>
            <div class="space-y-4">
                <div>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Age</span>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-400"><?php echo $profile['age']; ?></p>
                </div>
                <div>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Total Matches</span>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-400"><?php echo count($matches); ?></p>
                </div>
                <div>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Status</span>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['status']); ?></p>
                </div>
                <div>
                    <span class="text-sm text-slate-600 dark:text-slate-400">Registered</span>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo date('M d, Y', strtotime($profile['created_at'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Actions</h3>
            <div class="space-y-3">
                <a href="edit-profile.php?id=<?php echo $profile['id']; ?>" class="block w-full px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg font-semibold text-sm transition-colors text-center">
                    <span class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">edit</span>
                        Edit Profile
                    </span>
                </a>
                <?php if ($_SESSION['role'] === 'lydo'): ?>
                <a href="matching.php" class="block w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm transition-colors text-center">
                    <span class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">psychology</span>
                        Find Matches
                    </span>
                </a>
                <?php endif; ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this profile? This cannot be undone.')">
                    <input type="hidden" name="profile_id" value="<?php echo $profile['id']; ?>">
                    <button type="submit" name="delete_profile" value="1" class="w-full px-4 py-2 bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg font-semibold text-sm transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">delete</span>
                            Delete Profile
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>