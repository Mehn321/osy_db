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
$member = []; // Will be fetched via AJAX

// Handle status-only form submission for LYDO.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        die('Invalid CSRF token');
    }
    $status = trim($_POST['status'] ?? ($member['status'] ?? 'Active'));
    if (!in_array($status, ['Active', 'Inactive'], true)) die('Invalid member status');
    $database->execute("UPDATE users SET status = ? WHERE id = ? AND role != 'lydo'", [$status, $userId], 'si');
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
            <div id="member-info-container" class="rounded-lg bg-slate-100 dark:bg-slate-700 px-3 py-2.5 text-slate-900 dark:text-white skeleton-container">
                <div class="h-5 w-32 bg-slate-300 dark:bg-slate-500 rounded animate-pulse mb-1"></div>
                <div class="h-4 w-48 bg-slate-300 dark:bg-slate-500 rounded animate-pulse"></div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1" for="status">Status</label>
            <select id="status" name="status" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2.5 opacity-50" disabled>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Save status</button>
            <a href="member-registry.php" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg font-bold">Cancel</a>
        </div>
    </form>
</div>
<script>
    (function() {
        var userId = <?php echo (int)$userId; ?>;
        fetch('../api/get_member_detail.php?user_id=' + userId)
            .then(function(res) {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(function(data) {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                var member = data.member;

                var infoContainer = document.getElementById('member-info-container');
                infoContainer.innerHTML = '';
                infoContainer.classList.remove('skeleton-container');

                var nameNode = document.createTextNode(member.fullname || '');
                infoContainer.appendChild(nameNode);

                var emailSpan = document.createElement('span');
                emailSpan.className = 'block text-xs text-slate-500 dark:text-slate-400';
                emailSpan.textContent = member.email || '';
                infoContainer.appendChild(emailSpan);

                var statusSelect = document.getElementById('status');
                statusSelect.disabled = false;
                statusSelect.classList.remove('opacity-50');
                statusSelect.value = member.status === 'Inactive' ? 'Inactive' : 'Active';
            })
            .catch(function(err) {
                console.error('Error fetching member:', err);
                var infoContainer = document.getElementById('member-info-container');
                infoContainer.innerHTML = '<span class="text-red-500">Error loading member details.</span>';
            });
    })();
</script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>