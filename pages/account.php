<?php
$pageTitle = 'Account Information';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

requireRole(['sk_chairman', 'employer', 'training_provider']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$account = $database->fetchOne(
    'SELECT username, email, fullname, role FROM users WHERE id = ? LIMIT 1',
    [$userId],
    'i'
);

if (!$account) {
    header('Location: logout.php');
    exit;
}

$message = '';
$messageType = '';
$isEditing = isset($_GET['edit']) && $_GET['edit'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This request has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
    } elseif (isset($_POST['update_profile'])) {
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Enter a valid full name and email address.';
            $messageType = 'error';
        } else {
            $result = $user->updateProfile($userId, $fullname, $email);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;
                $account['fullname'] = $fullname;
                $account['email'] = $email;
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($newPassword !== $confirmPassword) {
            $message = 'New password and confirmation do not match.';
            $messageType = 'error';
        } else {
            $result = $user->changePassword($userId, $currentPassword, $newPassword);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-700 dark:text-blue-400 mb-2">Account</p>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white"><?php echo $isEditing ? 'Edit Profile' : 'My Profile'; ?></h1>
                <p class="text-slate-600 dark:text-slate-400 mt-2"><?php echo $isEditing ? 'Update your personal details and account password.' : 'Review your account information.'; ?></p>
            </div>
            <?php if ($isEditing): ?>
                <a href="account.php" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 dark:bg-slate-700 px-5 py-3 font-bold text-slate-800 dark:text-white">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Back to profile
                </a>
            <?php else: ?>
                <a href="account.php?edit=1" class="inline-flex items-center gap-2 rounded-xl bg-blue-900 px-5 py-3 font-bold text-white hover:bg-blue-800">
                    <span class="material-symbols-outlined text-base">edit</span>
                    Edit Profile
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!$isEditing): ?>
        <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Profile Information</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Full name</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($account['fullname'] ?? 'Not provided'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Username</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($account['username'] ?? 'Not provided'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Email address</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($account['email'] ?? 'Not provided'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Account type</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $account['role'] ?? 'User'))); ?></dd>
                </div>
            </dl>
        </section>
    <?php else: ?>
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Personal Details</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                    <div>
                        <label for="fullname" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Full name</label>
                        <input id="fullname" name="fullname" required value="<?php echo htmlspecialchars($account['fullname'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Email address</label>
                        <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($account['email'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Username</label>
                        <input disabled value="<?php echo htmlspecialchars($account['username'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60 px-4 py-3 text-slate-500 dark:text-slate-400">
                    </div>
                    <button type="submit" name="update_profile" value="1" class="w-full rounded-xl bg-blue-900 hover:bg-blue-800 text-white font-bold py-3">Save personal details</button>
                </form>
            </section>

            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Change Password</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Current password</label>
                        <input id="current_password" name="current_password" type="password" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">New password</label>
                        <input id="new_password" name="new_password" type="password" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Confirm new password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-4 py-3 text-slate-900 dark:text-white">
                    </div>
                    <button type="submit" name="change_password" value="1" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold py-3">Change password</button>
                </form>
            </section>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>