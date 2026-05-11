<?php
$pageTitle = 'Provider Approvals';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';

requireLogin();
requireRole('lydo');

$userModel = new User($database);
$auditLog = new AuditLog($database);
$notification = new Notification($database);

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_action'])) {
    $providerId = intval($_POST['provider_id']);
    $action = $_POST['provider_action'] === 'approve' ? 'Active' : 'Declined';
    $remark = trim($_POST['remark'] ?? '');

    $result = $userModel->approveProvider($providerId, $action, $remark);
    if ($result['success']) {
        $message = 'Provider account has been ' . ($action === 'Active' ? 'approved' : 'declined') . '.';
        $messageType = 'success';

        $auditLog->logAction(
            $_SESSION['user_id'],
            $_SESSION['role'],
            $action === 'Active' ? 'Approved provider account' : 'Declined provider account',
            'User',
            $providerId,
            json_encode(['status' => $action, 'remark' => $remark])
        );

        $notification->create([
            'title' => 'Provider Account ' . ($action === 'Active' ? 'Approved' : 'Declined'),
            'message' => 'Your provider registration has been ' . strtolower($action) . ($remark ? ': ' . $remark : ''),
            'type' => 'System',
            'recipient_type' => 'Specific',
            'recipient_id' => $providerId
        ]);
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

$pendingEmployers = $userModel->getUsersByRole('employer', ['status' => 'Pending']);
$pendingProviders = $userModel->getUsersByRole('training_provider', ['status' => 'Pending']);
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Administration</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">Provider Approvals</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Provider Account Approval</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Review and approve employer and training provider accounts before they can post opportunities and training programs.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<div class="space-y-8">
    <?php if (empty($pendingEmployers) && empty($pendingProviders)): ?>
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">No pending provider accounts</h2>
            <p class="text-sm text-slate-500 mt-3">There are currently no employer or training provider accounts awaiting approval.</p>
        </div>
    <?php else: ?>
        <?php if (!empty($pendingEmployers)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Pending Employers</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-semibold uppercase">Company / Name</th>
                                <th class="px-4 py-3 font-semibold uppercase">Email</th>
                                <th class="px-4 py-3 font-semibold uppercase">Barangay</th>
                                <th class="px-4 py-3 font-semibold uppercase">Submitted</th>
                                <th class="px-4 py-3 font-semibold uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingEmployers as $provider): ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['fullname']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['email']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['barangay'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['created_at']); ?></td>
                                    <td class="px-4 py-4">
                                        <form method="POST" class="flex flex-wrap gap-2">
                                            <input type="hidden" name="provider_id" value="<?php echo intval($provider['id']); ?>">
                                            <textarea name="remark" rows="1" placeholder="Optional remark" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-2 px-3 text-sm text-slate-900 dark:text-white"></textarea>
                                            <button type="submit" name="provider_action" value="approve" class="rounded-2xl bg-green-700 text-white px-4 py-2 text-xs font-semibold hover:bg-green-600 transition">Approve</button>
                                            <button type="submit" name="provider_action" value="decline" class="rounded-2xl bg-red-700 text-white px-4 py-2 text-xs font-semibold hover:bg-red-600 transition">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($pendingProviders)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Pending Training Providers</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-300">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 font-semibold uppercase">Provider Name</th>
                                <th class="px-4 py-3 font-semibold uppercase">Email</th>
                                <th class="px-4 py-3 font-semibold uppercase">Barangay</th>
                                <th class="px-4 py-3 font-semibold uppercase">Submitted</th>
                                <th class="px-4 py-3 font-semibold uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingProviders as $provider): ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['fullname']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['email']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['barangay'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($provider['created_at']); ?></td>
                                    <td class="px-4 py-4">
                                        <form method="POST" class="flex flex-wrap gap-2">
                                            <input type="hidden" name="provider_id" value="<?php echo intval($provider['id']); ?>">
                                            <textarea name="remark" rows="1" placeholder="Optional remark" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-2 px-3 text-sm text-slate-900 dark:text-white"></textarea>
                                            <button type="submit" name="provider_action" value="approve" class="rounded-2xl bg-green-700 text-white px-4 py-2 text-xs font-semibold hover:bg-green-600 transition">Approve</button>
                                            <button type="submit" name="provider_action" value="decline" class="rounded-2xl bg-red-700 text-white px-4 py-2 text-xs font-semibold hover:bg-red-600 transition">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>