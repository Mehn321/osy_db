<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/manage-sk-chairmen.php');

// Remove synchronous fetch
$c = preg_replace('/\$chairmen\s*=\s*\$userModel->getUsersByRole\(\'sk_chairman\'\);/s', '', $c);

// Replace count with span that can be updated
$c = preg_replace('/<\?php echo count\(\$chairmen\); \?> chairmen/s', '<span id="chairmenCount">...</span> chairmen', $c);

// Replace empty state check and PHP foreach with skeletons
$skeletons = '<div class="overflow-x-auto">
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
        <tbody id="chairmenTableBody">
            <?php for ($i=0; $i<3; $i++): ?>
                <tr class="border-t border-slate-200 dark:border-slate-700">
                    <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-32 rounded"></div></td>
                    <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                    <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                    <td class="px-4 py-4"><div class="skeleton-pulse h-6 w-16 rounded-full"></div></td>
                    <td class="px-4 py-4"><div class="skeleton-pulse h-4 w-20 rounded"></div></td>
                    <td class="px-4 py-4 text-right"><div class="skeleton-pulse h-8 w-24 rounded float-right"></div></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>';

$c = preg_replace('/<\?php if \(empty\(\$chairmen\)\): \?>.*?<\?php endif; \?>/s', $skeletons, $c);

// Also remove `document.querySelectorAll('.edit-chairman-btn')` from JS because it's replaced by AJAX
$c = preg_replace('/document\.querySelectorAll\(\'\.edit-chairman-btn\'\)\.forEach\(button => \{.*?\n\s*\}\);\n/s', '', $c);

// Replace JS
$js = '
<script>
(function() {
    function loadChairmen() {
        fetch(`../api/get_system_data.php?view=sk_chairmen`)
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById(\'chairmenTableBody\');
                if (!res.success || !res.sk_chairmen || res.sk_chairmen.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No SK Chairman accounts have been created yet.</td></tr>`;
                    document.getElementById(\'chairmenCount\').textContent = \'0\';
                    return;
                }
                
                document.getElementById(\'chairmenCount\').textContent = res.sk_chairmen.length;
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                
                tbody.innerHTML = res.sk_chairmen.map(chairman => {
                    const statusClass = chairman.status === \'Active\' ? \'bg-green-100 text-green-800\' : \'bg-red-100 text-red-800\';
                    
                    return `
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td class="px-4 py-4">${chairman.fullname}</td>
                        <td class="px-4 py-4">${chairman.username}</td>
                        <td class="px-4 py-4">${chairman.barangay || \'N/A\'}</td>
                        <td class="px-4 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">${chairman.status}</span></td>
                        <td class="px-4 py-4">${chairman.created_at || \'-\'}</td>
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <button type="button" onclick="openEditChairman(${chairman.id}, \'${chairman.fullname.replace(/\'/g, "\\\'")}\', \'${chairman.username.replace(/\'/g, "\\\'")}\', \'${(chairman.email || \'\').replace(/\'/g, "\\\'")}\', \'${(chairman.barangay || \'\').replace(/\'/g, "\\\'")}\')" class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold transition"><span class="material-symbols-outlined text-base">edit</span>Edit</button>
                            <form method="POST" class="inline-block ml-2" onsubmit="return confirm(\'Permanently delete this SK Chairman account? This cannot be undone.\');">
                                <input type="hidden" name="form_nonce" value="${nonce}">
                                <input type="hidden" name="delete_chairman" value="1">
                                <input type="hidden" name="chairman_id" value="${chairman.id}">
                                <button type="submit" class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 font-semibold transition"><span class="material-symbols-outlined text-base">delete</span>Delete</button>
                            </form>
                        </td>
                    </tr>`;
                }).join(\'\');
            });
    }
    
    window.openEditChairman = function(id, fullname, username, email, barangay) {
        document.getElementById(\'editChairmanId\').value = id;
        document.getElementById(\'editFullname\').value = fullname;
        document.getElementById(\'editUsername\').value = username;
        document.getElementById(\'editEmail\').value = email;
        document.getElementById(\'editBarangay\').value = barangay;
        document.getElementById(\'editChairmanModal\').classList.replace(\'hidden\', \'flex\');
        document.getElementById(\'editFullname\').focus();
    };
    
    loadChairmen();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/manage-sk-chairmen.php', $c);
echo "Done manage-sk-chairmen.php\n";
