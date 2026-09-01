<?php
$pageTitle = 'Youth Verification';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/OSYProfile.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';
require_once __DIR__ . '/../Classes/User.php';

requireLogin();
requireRole(['lydo', 'sk_chairman']);

$osyProfile = new OSYProfile($database);
$auditLog = new AuditLog($database);
$notification = new Notification($database);
$userClass = new User($database);

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_youth'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        $profileId = intval($_POST['profile_id']);

        // Scoping check for SK Chairman
        if ($_SESSION['role'] === 'sk_chairman') {
            $checkProfile = $osyProfile->getById($profileId);
            if ($checkProfile && $checkProfile['barangay'] !== $_SESSION['barangay']) {
                die("Access Denied: Cannot verify youth from another barangay.");
            }
        }

        $action = $_POST['action'] === 'approve' ? 'Verified' : 'Action Required';
        $remark = trim($_POST['remark'] ?? '');

        $result = $osyProfile->setVerificationStatus($profileId, $action, $remark, $_SESSION['user_id']);

        if ($result['success']) {
            // Get the profile with user_id (created_by)
            $profile = $osyProfile->getById($profileId);

            if ($profile && isset($profile['created_by'])) {
                $userId = $profile['created_by'];

                // Update user status to match verification.
                // For a returned-for-correction profile, keep the user pending so the youth cannot log in yet.
                $newUserStatus = ($action === 'Verified') ? 'Active' : 'Pending';
                $database->execute(
                    "UPDATE users SET status = ? WHERE id = ?",
                    [$newUserStatus, $userId],
                    "si"
                );

                // Send notification to youth
                $notification->sendToUser(
                    $userId,
                    'Profile Verification ' . ($action === 'Verified' ? 'Approved ✅' : 'Needs Action ⚠️'),
                    $action === 'Verified'
                        ? 'Your youth registration has been approved. You can now log in to the system.'
                        : 'Your youth registration needs attention. ' . ($remark ? 'Reason: ' . $remark : 'Please review and update your profile.'),
                    'System'
                );
            }

            $message = 'Youth profile has been ' . ($action === 'Verified' ? 'approved' : 'returned for action') . '.';
            $messageType = 'success';

            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                $action === 'Verified' ? 'Approved youth verification' : 'Returned youth for correction',
                'OSYProfile',
                $profileId,
                json_encode(['remark' => $remark])
            );
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

$pendingProfiles = $osyProfile->getPendingByBarangay($_SESSION['barangay']);
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Verification</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">Youth Verification</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Review Youth Registrations</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Approve or reject youth registration submissions from your barangay before they enter the matching pipeline.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<?php if (empty($pendingProfiles)): ?>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">No pending youth profiles</h2>
        <p class="text-sm text-slate-500 mt-3">There are no youth profiles awaiting verification for your barangay at the moment.</p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($pendingProfiles as $profile): ?>
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h2>
                        <p class="text-sm text-slate-500 mt-2">Barangay: <?php echo htmlspecialchars($profile['barangay']); ?></p>
                        <p class="text-sm text-slate-500">Profile Type: <?php echo htmlspecialchars($profile['profile_type']); ?></p>
                        <p class="text-sm text-slate-500">Registered: <?php echo htmlspecialchars($profile['created_at'] ?? 'N/A'); ?></p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-orange-100 text-orange-800 px-3 py-1.5 text-xs font-semibold uppercase">Pending Verification</span>
                </div>

                <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4">
                        <p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Age</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['age']); ?></p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4">
                        <p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Education</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['education_level']); ?></p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4">
                        <p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Phone</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($profile['phone']); ?></p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Rejection remark (optional)</p>
                    <form method="POST" class="space-y-4 mt-4">
                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                        <input type="hidden" name="verify_youth" value="1">
                        <input type="hidden" name="profile_id" value="<?php echo intval($profile['id']); ?>">
                        <textarea name="remark" rows="3" class="w-full rounded-3xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="Add a note for the youth member if declined..."></textarea>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex gap-3">
                                <button type="submit" name="action" value="approve" class="rounded-2xl bg-green-700 text-white px-6 py-3 text-sm font-semibold hover:bg-green-600 transition">Approve</button>
                                <button type="submit" name="action" value="reject" class="rounded-2xl bg-red-700 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition">Reject</button>
                            </div>
                            <p class="text-xs text-slate-500">Approvals will move the profile forward to the matching queue.</p>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>