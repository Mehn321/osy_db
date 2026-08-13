<?php
$pageTitle = 'Manage SK Chairmen';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/Reference.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';

requireLogin();
requireRole('lydo');

$reference = new Reference($database);
$barangays = $reference->getByCategory('barangay');
$userModel = new User($database);
$auditLog = new AuditLog($database);

$message = '';
$messageType = 'success';
$newChairmanPassword = null;

// Handle DELETE chairman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chairman'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Invalid or expired form submission.';
        $messageType = 'error';
    } else {
        try {
            $chairmanId = intval($_POST['chairman_id']);
            $database->execute("DELETE FROM users WHERE id = ? AND role = 'sk_chairman'", [$chairmanId], "i");
            $message = 'SK Chairman account deleted successfully.';
            $messageType = 'success';
            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                'Deleted SK Chairman account',
                'User',
                $chairmanId,
                ''
            );
        } catch (Exception $e) {
            $message = 'Error deleting SK Chairman: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Handle DEACTIVATE chairman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_chairman'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Invalid or expired form submission.';
        $messageType = 'error';
    } else {
        try {
            $chairmanId = intval($_POST['chairman_id']);
            $newStatus = $_POST['new_status'] === 'Active' ? 'Inactive' : 'Active';
            $database->execute("UPDATE users SET status = ? WHERE id = ? AND role = 'sk_chairman'", [$newStatus, $chairmanId], "si");
            $message = 'SK Chairman status updated to ' . $newStatus . '.';
            $messageType = 'success';
            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                'Updated SK Chairman status to ' . $newStatus,
                'User',
                $chairmanId,
                ''
            );
        } catch (Exception $e) {
            $message = 'Error updating SK Chairman status: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_chairman'])) {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This form has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
    } else {
        try {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $fullname = trim($_POST['fullname']);
            $barangay = trim($_POST['barangay']);

            // CHECK: Prevent duplicate SK Chairman per barangay
            $existingChairman = $database->fetchOne(
                "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status != 'Deleted' LIMIT 1",
                [$barangay],
                "s"
            );
            if ($existingChairman) {
                throw new Exception("A SK Chairman is already assigned to Barangay " . htmlspecialchars($barangay) . ". Please deactivate or delete the existing chairman first.");
            }

            $result = $userModel->createUser([
                'username' => $username,
                'email' => $email,
                'fullname' => $fullname,
                'role' => 'sk_chairman',
                'status' => 'Active',
                'barangay' => $barangay,
                'temp_password_required' => 1,
                'created_by' => $_SESSION['user_id']
            ]);

            if ($result['success']) {
                $newChairmanPassword = $result['password'] ?? null;
                $message = 'SK Chairman account created successfully.';
                $messageType = 'success';

                $createdId = $result['id'] ?? $result['user_id'] ?? null;

                // 1. Create a system notification for the new SK Chairman
                $notifObj = new Notification($database);
                $notifObj->sendToUser(
                    $createdId,
                    'Account Created',
                    "Welcome to the Youth Profiling System! Your SK Chairman account has been created by the LYDO. Your temporary password is: $newChairmanPassword. Please change it on your first login.",
                    'System',
                    $_SESSION['user_id']
                );

                // 2. Send welcome email with login credentials
                try {
                    require_once __DIR__ . '/../Classes/EmailService.php';
                    $emailService = new EmailService($database);
                    $emailBody = "
                    <h3>Welcome to the Municipal Youth Profiling System</h3>
                    <p>Hello <strong>" . htmlspecialchars($fullname) . "</strong>,</p>
                    <p>Your SK Chairman account for Barangay <strong>" . htmlspecialchars($barangay) . "</strong> has been created by the LYDO.</p>
                    <p>Here are your temporary credentials to log in:</p>
                    <ul>
                        <li><strong>Username:</strong> " . htmlspecialchars($username) . "</li>
                        <li><strong>Temporary Password:</strong> " . htmlspecialchars($newChairmanPassword) . "</li>
                    </ul>
                    <p>Please log in and change your password to continue using the system.</p>
                ";
                    $emailService->send($email, 'SK Chairman Account Created', $emailBody);
                } catch (Exception $ex) {
                    // Silently log or capture email delivery error so it doesn't block the UI
                    $message .= ' (Email notification could not be sent)';
                }

                $auditLog->logAction(
                    $_SESSION['user_id'],
                    $_SESSION['role'],
                    'Created SK Chairman account',
                    'User',
                    $createdId,
                    json_encode(['username' => $username, 'barangay' => $barangay])
                );
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$chairmen = $userModel->getUsersByRole('sk_chairman');
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Administration</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">SK Chairmen</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manage SK Chairmen</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Create and review SK Chairman accounts for barangay-level youth verification and outreach coordination.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<?php if ($newChairmanPassword): ?>
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <p class="text-sm font-semibold text-blue-900">Temporary password generated:</p>
        <p class="mt-2 text-xl font-bold text-slate-900 bg-slate-100 p-3 rounded-xl break-all"><?php echo htmlspecialchars($newChairmanPassword); ?></p>
        <p class="mt-2 text-xs text-slate-500">Share this temporary password with the SK Chairman. They will be prompted to reset it on first login.</p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_420px] gap-8">
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">New SK Chairman Account</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="create_chairman" value="1">
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Full Name</label>
                    <input name="fullname" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="Juan dela Cruz">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Username</label>
                    <input name="username" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="skchairman01">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                    <input type="email" name="email" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="chairman@example.com">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Assigned Barangay</label>
                    <select name="barangay" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white">
                        <option value="">Select Barangay</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?php echo htmlspecialchars($barangay); ?>"><?php echo htmlspecialchars($barangay); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-900 text-white py-3 px-6 text-sm font-semibold hover:bg-blue-800 transition">Create SK Chairman</button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Current SK Chairmen</h2>
            <?php if (empty($chairmen)): ?>
                <p class="text-sm text-slate-500">No SK Chairman accounts have been created yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-semibold uppercase">Name</th>
                                <th class="px-4 py-3 font-semibold uppercase">Username</th>
                                <th class="px-4 py-3 font-semibold uppercase">Barangay</th>
                                <th class="px-4 py-3 font-semibold uppercase">Status</th>
                                <th class="px-4 py-3 font-semibold uppercase">Created</th>
                                <th class="px-4 py-3 font-semibold uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chairmen as $chairman): ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['fullname']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['username']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['barangay'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $chairman['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($chairman['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['created_at']); ?></td>
                                    <td class="px-4 py-4 text-right">
                                        <form method="POST" style="display: inline-block;" onsubmit="return confirm('<?php echo $chairman['status'] === 'Active' ? 'Deactivate this SK Chairman?' : 'Reactivate this SK Chairman?'; ?>');">
                                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                                            <input type="hidden" name="deactivate_chairman" value="1">
                                            <input type="hidden" name="chairman_id" value="<?php echo intval($chairman['id']); ?>">
                                            <input type="hidden" name="new_status" value="<?php echo htmlspecialchars($chairman['status']); ?>">
                                            <button type="submit" class="text-xs px-2 py-1 rounded-lg <?php echo $chairman['status'] === 'Active' ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-800' : 'bg-green-100 hover:bg-green-200 text-green-800'; ?> font-semibold transition">
                                                <?php echo $chairman['status'] === 'Active' ? '🔒 Deactivate' : '🔓 Reactivate'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline-block; margin-left: 4px;" onsubmit="return confirm('Permanently delete this SK Chairman account? This cannot be undone.');">
                                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                                            <input type="hidden" name="delete_chairman" value="1">
                                            <input type="hidden" name="chairman_id" value="<?php echo intval($chairman['id']); ?>">
                                            <button type="submit" class="text-xs px-2 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 font-semibold transition">🗑️ Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>