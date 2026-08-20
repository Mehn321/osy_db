<?php
$pageTitle = 'Edit Member';
require_once __DIR__ . '/../init.php';
requireLogin();
requireRole('lydo');

// Get member ID from query string
if (!isset($_GET['user_id'])) {
    die('User ID not specified');
}
$userId = (int)$_GET['user_id'];

// Fetch member data
$member = $database->fetchOne("SELECT * FROM users WHERE id = ? AND role != 'lydo'", [$userId], 'i');
if (!$member) {
    die('Member not found');
}

// Handle status-only form submission for LYDO.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormNonce($_POST['form_nonce'] ?? '')) {
        die('Invalid CSRF token');
    }
    $status = trim($_POST['status'] ?? ($member['status'] ?? 'Active'));
    if (!in_array($status, ['Active', 'Inactive'], true)) die('Invalid member status');
    $database->query("UPDATE users SET status = ? WHERE id = ? AND role != 'lydo'", [$status, $userId], 'si');
    header('Location: member-registry.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-2xl mx-auto mt-10">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Edit Member Status</h2>
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">LYDO access is limited to changing account status.</p>
    <form method="POST" class="space-y-4">
        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Member</label>
            <div class="rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-2.5 text-slate-900 dark:text-white"><?php echo htmlspecialchars($member['fullname']); ?><span class="block text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($member['email']); ?></span></div>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1" for="status">Status</label>
            <select id="status" name="status" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2.5">
                <?php
                $statuses = ['Active', 'Inactive'];
                foreach ($statuses as $s) {
                    $sel = ($member['status'] ?? 'Active') === $s ? 'selected' : '';
                    echo "<option value=\"$s\" $sel>$s</option>";
                }
                ?>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Save status</button>
            <a href="member-registry.php" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg font-bold">Cancel</a>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>