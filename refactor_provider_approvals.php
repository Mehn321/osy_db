<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/provider-approvals.php');

// Remove synchronous fetch
$c = preg_replace('/\$pendingEmployers\s*=\s*\$userModel->getUsersByRole\(\'employer\'.*?\]\);/s', '', $c);
$c = preg_replace('/\$pendingProviders\s*=\s*\$userModel->getUsersByRole\(\'training_provider\'.*?\]\);/s', '', $c);

$skeletons = '<div id="approvalsContainer" class="space-y-8">
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Pending Accounts</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-semibold uppercase">Company / Name</th>
                        <th class="px-4 py-3 font-semibold uppercase">Type</th>
                        <th class="px-4 py-3 font-semibold uppercase">Email</th>
                        <th class="px-4 py-3 font-semibold uppercase">Address</th>
                        <th class="px-4 py-3 font-semibold uppercase">Proof</th>
                        <th class="px-4 py-3 font-semibold uppercase">Submitted</th>
                        <th class="px-4 py-3 font-semibold uppercase">Action</th>
                    </tr>
                </thead>
                <tbody id="approvalsTableBody">
                    <?php for ($i=0; $i<3; $i++): ?>
                        <tr class="border-t border-slate-200 dark:border-slate-700">
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-32 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-16 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                            <td class="px-4 py-4"><div class="skeleton-pulse h-8 w-32 rounded"></div></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>';

$c = preg_replace('/<div class="space-y-8">.*?<\?php endif; \?>\n<\/div>/s', $skeletons, $c);

$js = '
<script>
(function() {
    function loadApprovals() {
        fetch(`../api/get_system_data.php?view=provider_approvals&status=Pending`)
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById(\'approvalsTableBody\');
                if (!res.success || !res.providers || res.providers.length === 0) {
                    document.getElementById(\'approvalsContainer\').innerHTML = `<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8"><h2 class="text-xl font-bold text-slate-900 dark:text-white">No pending provider accounts</h2><p class="text-sm text-slate-500 mt-3">There are currently no employer or training provider accounts awaiting approval.</p></div>`;
                    return;
                }
                
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                const csrf = \'<?php echo htmlspecialchars(getCsrfToken()); ?>\';
                
                tbody.innerHTML = res.providers.map(provider => {
                    const roleLabel = provider.role === \'employer\' ? \'Employer\' : \'Training Provider\';
                    const proofHtml = provider.document_path ? `<a href="provider-document.php?provider_id=${provider.id}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline">View document</a>` : `<span class="text-red-600">Missing</span>`;
                    
                    return `
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td class="px-4 py-4">${provider.fullname}</td>
                        <td class="px-4 py-4">${roleLabel}</td>
                        <td class="px-4 py-4">${provider.email}</td>
                        <td class="px-4 py-4">${provider.barangay || \'N/A\'}</td>
                        <td class="px-4 py-4">${proofHtml}</td>
                        <td class="px-4 py-4">${provider.created_at}</td>
                        <td class="px-4 py-4">
                            <form method="POST" class="flex flex-col gap-2">
                                <input type="hidden" name="provider_id" value="${provider.id}">
                                <input type="hidden" name="csrf_token" value="${csrf}">
                                <input type="hidden" name="form_nonce" value="${nonce}">
                                <textarea name="remark" rows="1" placeholder="Optional remark" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-2 px-3 text-sm text-slate-900 dark:text-white"></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" name="provider_action" value="approve" class="flex-1 rounded-2xl bg-green-700 text-white px-3 py-2 text-xs font-semibold hover:bg-green-600 transition">Approve</button>
                                    <button type="submit" name="provider_action" value="decline" class="flex-1 rounded-2xl bg-red-700 text-white px-3 py-2 text-xs font-semibold hover:bg-red-600 transition">Decline</button>
                                </div>
                            </form>
                        </td>
                    </tr>`;
                }).join(\'\');
            });
    }
    
    loadApprovals();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/provider-approvals.php', $c);
echo "Done provider-approvals.php\n";
