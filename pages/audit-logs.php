<?php
$pageTitle = 'Audit Logs';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/AuditLog.php';

requireLogin();
requireRole('lydo');

$auditLog = new AuditLog($database);
$logs = $auditLog->getAll([], 150, 0);

?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Administration</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">Audit Logs</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">System Audit Trail</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Review system events and accountability records for user actions, verification decisions, and approvals.</p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 overflow-x-auto">
    <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-300">
        <thead>
            <tr>
                <th class="px-4 py-3 font-semibold uppercase">Timestamp</th>
                <th class="px-4 py-3 font-semibold uppercase">Actor</th>
                <th class="px-4 py-3 font-semibold uppercase">Role</th>
                <th class="px-4 py-3 font-semibold uppercase">Action</th>
                <th class="px-4 py-3 font-semibold uppercase">Target</th>
                <th class="px-4 py-3 font-semibold uppercase">Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-6 text-slate-500">No audit entries were found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['created_at']); ?></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['actor_id']); ?></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['actor_role']); ?></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['action']); ?></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['target_type'] . ' #' . ($log['target_id'] ?? 'N/A')); ?></td>
                        <td class="px-4 py-4"><?php echo htmlspecialchars($log['metadata']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>