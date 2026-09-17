<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/member-registry.php');

$c = preg_replace('/\$query = "SELECT \* FROM users WHERE role != \'lydo\'";.*\$totalPages = ceil\(\$totalMembers \/ \$limit\);/s', '', $c);

// Also remove empty check and replace with skeletons
$skeletons = '<tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="membersList">
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
</tbody>';

$c = preg_replace('/<tbody class="divide-y divide-slate-200 dark:divide-slate-700">.*?<\/tbody>/s', $skeletons, $c);

$js = '
<script>
(function() {
    const form = document.querySelector(\'form[method="GET"]\');
    if (form) {
        form.addEventListener(\'submit\', function(e) {
            e.preventDefault();
            loadMembers();
        });
    }

    function loadMembers() {
        const search = document.getElementById(\'member-search\')?.value || \'\';
        const role = document.getElementById(\'member-role\')?.value || \'All\';
        const status = document.getElementById(\'member-status\')?.value || \'All\';
        const area = document.getElementById(\'member-area\')?.value || \'\';
        
        const params = new URLSearchParams();
        params.append(\'view\', \'member_registry\');
        if (search) params.append(\'search\', search);
        if (role) params.append(\'role\', role);
        if (status) params.append(\'status\', status);
        if (area) params.append(\'area\', area);
        
        fetch(`../api/get_system_data.php?${params.toString()}`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById(\'membersList\');
                if (!res.success || !res.members || res.members.length === 0) {
                    list.innerHTML = `<tr><td colspan="5" class="px-6 py-20 text-center text-slate-500 italic">No members found matching the criteria.</td></tr>`;
                    return;
                }
                
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                
                list.innerHTML = res.members.map(member => {
                    const roleDisplay = member.role.replace(/_/g, \' \');
                    let areaDisplay = \'General\';
                    if (member.role === \'employer\') areaDisplay = \'All Barangays\';
                    else if (member.barangay) areaDisplay = member.barangay;
                    else if (member.provider_type) areaDisplay = member.provider_type;
                    
                    const mstatus = member.status || \'Active\';
                    const badge = mstatus === \'Active\' ? \'bg-emerald-100 text-emerald-700\' : \'bg-rose-100 text-rose-700\';
                    const nextStatus = mstatus === \'Active\' ? \'Inactive\' : \'Active\';
                    const btnClass = nextStatus === \'Active\' ? \'bg-emerald-600 hover:bg-emerald-700\' : \'bg-rose-600 hover:bg-rose-700\';
                    const btnIcon = nextStatus === \'Active\' ? \'check_circle\' : \'block\';
                    const btnText = nextStatus === \'Active\' ? \'Activate\' : \'Deactivate\';
                    
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
                            <form method="POST" class="inline" onsubmit="return confirm(\'Change this member status to ${nextStatus}?\');">
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
                }).join(\'\');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return \'\';
        return String(str).replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/"/g, \'&quot;\');
    }
    
    loadMembers();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/member-registry.php', $c);
echo "Done member-registry.php\n";
