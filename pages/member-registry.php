<?php
$pageTitle = 'Member Registry';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('lydo');

// LYDO may only update a member's account status.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['user_id'])) {
    if (!validateFormNonce($_POST['form_nonce'] ?? '')) {
        die('Invalid CSRF token');
    }
    $userId = (int)$_POST['user_id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['Active', 'Inactive'], true)) die('Invalid member status');
    $database->query("UPDATE users SET status = ? WHERE id = ? AND role != 'lydo'", [$status, $userId], 'si');
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$roleFilter = $_GET['role'] ?? 'All';
$statusFilter = $_GET['status'] ?? 'All';
$searchFilter = trim($_GET['search'] ?? '');
$areaFilter = trim($_GET['area'] ?? '');
$roles = ['All', 'sk_chairman', 'employer', 'training_provider', 'youth'];
$statuses = ['All', 'Active', 'Inactive'];
if (!in_array($roleFilter, $roles, true)) $roleFilter = 'All';
if (!in_array($statusFilter, $statuses, true)) $statusFilter = 'All';


?>

<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Member Account Registry</h2>
            <p class="text-slate-600 dark:text-slate-400 font-medium">Manage all stakeholder accounts including SK Chairmen, Employers, and Training Providers.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="manage-sk-chairmen.php" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50">Manage SK</a>
            <a href="provider-approvals.php" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50">Provider Approvals</a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="mb-8 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end">
        <div class="xl:col-span-2"><label for="member-search" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Search member</label><input id="member-search" type="search" name="search" value="<?php echo htmlspecialchars($searchFilter); ?>" placeholder="Name or email" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"></div>
        <div><label for="member-role" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Stakeholder</label><select id="member-role" name="role" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"><?php foreach ($roles as $role): ?><option value="<?php echo htmlspecialchars($role); ?>" <?php echo $roleFilter === $role ? 'selected' : ''; ?>><?php echo $role === 'All' ? 'All stakeholders' : ucfirst(str_replace('_', ' ', $role)); ?></option><?php endforeach; ?></select></div>
        <div><label for="member-status" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Status</label><select id="member-status" name="status" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"><?php foreach ($statuses as $status): ?><option value="<?php echo htmlspecialchars($status); ?>" <?php echo $statusFilter === $status ? 'selected' : ''; ?>><?php echo $status === 'All' ? 'All statuses' : $status; ?></option><?php endforeach; ?></select></div>
        <div><label for="member-area" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-2">Assigned area</label><input id="member-area" type="search" name="area" value="<?php echo htmlspecialchars($areaFilter); ?>" placeholder="Barangay or provider type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2.5 text-sm text-slate-900 dark:text-white"></div>
        <div class="md:col-span-2 xl:col-span-5 flex gap-2 justify-end"><a href="member-registry.php" class="px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-bold">Reset</a><button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-bold">Apply filters</button></div>
    </form>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto thin-scrollbar">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stakeholder</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Area</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="membersList">
    <?php for ($i=0; $i<5; $i++): ?>
        <tr>
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="skeleton-pulse w-10 h-10 rounded-full"></div>
                    <div>
                        <div class="skeleton-pulse h-4 w-32 rounded mb-1"></div>
                        <div class="skeleton-pulse h-3 w-48 rounded"></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4"><div class="skeleton-pulse h-5 w-20 rounded"></div></td>
            <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
            <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
            <td class="px-6 py-4 text-right"><div class="skeleton-pulse h-8 w-24 rounded ml-auto"></div></td>
        </tr>
    <?php endfor; ?>
</tbody>
        </table>
    </div>
</div>


<script>
(function() {
    const form = document.querySelector('form[method="GET"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            loadMembers();
        });
    }

    function loadMembers() {
        const search = document.getElementById('member-search')?.value || '';
        const role = document.getElementById('member-role')?.value || 'All';
        const status = document.getElementById('member-status')?.value || 'All';
        const area = document.getElementById('member-area')?.value || '';
        
        const params = new URLSearchParams();
        params.append('view', 'member_registry');
        if (search) params.append('search', search);
        if (role) params.append('role', role);
        if (status) params.append('status', status);
        if (area) params.append('area', area);
        
        fetch(`../api/get_system_data.php?${params.toString()}`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById('membersList');
                if (!res.success || !res.members || res.members.length === 0) {
                    list.innerHTML = `<tr><td colspan="5" class="px-6 py-20 text-center text-slate-500 italic">No members found matching the criteria.</td></tr>`;
                    return;
                }
                
                const nonce = document.querySelector('input[name="form_nonce"]')?.value || '<?php echo htmlspecialchars(getFormNonce()); ?>';
                
                list.innerHTML = res.members.map(member => {
                    const roleDisplay = member.role.replace(/_/g, ' ');
                    let areaDisplay = 'General';
                    if (member.role === 'employer') areaDisplay = 'All Barangays';
                    else if (member.barangay) areaDisplay = member.barangay;
                    else if (member.provider_type) areaDisplay = member.provider_type;
                    
                    const mstatus = member.status || 'Active';
                    const badge = mstatus === 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                    const nextStatus = mstatus === 'Active' ? 'Inactive' : 'Active';
                    const btnClass = nextStatus === 'Active' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700';
                    const btnIcon = nextStatus === 'Active' ? 'check_circle' : 'block';
                    const btnText = nextStatus === 'Active' ? 'Activate' : 'Deactivate';
                    
                    return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                    <span class="material-symbols-outlined">person</span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">${escapeHtml(member.fullname)}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(member.email)}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                                ${escapeHtml(roleDisplay)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">
                            ${escapeHtml(areaDisplay)}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold ${badge}">
                                ${escapeHtml(mstatus)}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" class="inline" onsubmit="return confirm('Change this member status to ${nextStatus}?');">
                                <input type="hidden" name="form_nonce" value="${nonce}">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="user_id" value="${member.id}">
                                <input type="hidden" name="status" value="${nextStatus}">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white ${btnClass} text-xs font-bold">
                                    <span class="material-symbols-outlined text-base">${btnIcon}</span>${btnText}
                                </button>
                            </form>
                        </td>
                    </tr>`;
                }).join('');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    loadMembers();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>