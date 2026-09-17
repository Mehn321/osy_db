<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/sk-barangay-youth.php');

// Keep count but remove fetching $allYouth
$c = preg_replace('/\$page\s*=\s*max\(1,.*?\$offset\s*=\s*\(\$page\s*-\s*1\)\s*\*\s*\$limit;\s*\$allYouth\s*=\s*\$database->fetchAll\(.*?\"sii\"\s*\);/s', '', $c);
$c = preg_replace('/\$totalPages\s*=\s*\(int\)ceil\(\$totalYouth\s*\/\s*\$limit\);/s', '', $c);

// Replace tbody with skeleton
$skeletons = '<tbody id="registryTableBody" class="divide-y divide-slate-100 dark:divide-slate-700">
    <?php for ($i=0; $i<5; $i++): ?>
    <tr class="bg-white dark:bg-slate-800">
        <td class="px-6 py-4"><div class="flex gap-3"><div class="skeleton-pulse w-9 h-9 rounded-full"></div><div><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></div></div></td>
        <td class="px-6 py-4"><div class="skeleton-pulse h-5 w-16 rounded"></div></td>
        <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></td>
        <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
        <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-20 rounded-full"></div></td>
        <td class="px-6 py-4"><div class="skeleton-pulse h-8 w-24 rounded float-right"></div></td>
    </tr>
    <?php endfor; ?>
</tbody>';

$c = preg_replace('/<tbody class="divide-y divide-slate-100 dark:divide-slate-700">.*?<\/tbody>/s', $skeletons, $c);

// Replace pagination with container
$c = preg_replace('/<\?php if \(\$totalPages > 1\): \?>.*?<\?php endif; \?>/s', '<div id="paginationContainer" class="p-6 border-t border-slate-200 dark:border-slate-700 flex justify-center hidden"></div>', $c);

$js = '
<script>
(function() {
    let currentPage = 1;
    
    function loadRegistry(page = 1) {
        currentPage = page;
        const tbody = document.getElementById(\'registryTableBody\');
        
        tbody.innerHTML = Array(5).fill(0).map(() => `
            <tr class="bg-white dark:bg-slate-800">
                <td class="px-6 py-4"><div class="flex gap-3"><div class="skeleton-pulse w-9 h-9 rounded-full"></div><div><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></div></div></td>
                <td class="px-6 py-4"><div class="skeleton-pulse h-5 w-16 rounded"></div></td>
                <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded mb-1"></div><div class="skeleton-pulse h-3 w-20 rounded"></div></td>
                <td class="px-6 py-4"><div class="skeleton-pulse h-4 w-24 rounded"></div></td>
                <td class="px-6 py-4"><div class="skeleton-pulse h-6 w-20 rounded-full"></div></td>
                <td class="px-6 py-4"><div class="skeleton-pulse h-8 w-24 rounded float-right"></div></td>
            </tr>
        `).join(\'\');
        
        fetch(`../api/get_profiles_data.php?limit=10&page=${page}&verification_status=verified`) // We also want Action Required, so we fetch all non-pending
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.profiles || res.profiles.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-16 text-center text-slate-400"><span class="material-symbols-outlined text-4xl opacity-20 block mb-3">groups_3</span><p class="font-medium">No verified youth found.</p></td></tr>`;
                    document.getElementById(\'paginationContainer\').classList.add(\'hidden\');
                    return;
                }
                
                tbody.innerHTML = res.profiles.map(person => {
                    const vStatus = person.verification_status || \'Drafting\';
                    let badgeClass = \'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300\';
                    if (vStatus === \'Verified\') badgeClass = \'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400\';
                    else if (vStatus === \'Action Required\') badgeClass = \'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400\';
                    
                    const typeClass = person.profile_type === \'OSY\' ? \'bg-orange-100 text-orange-700\' : \'bg-blue-100 text-blue-700\';
                    
                    return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-slate-400 text-sm">person</span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">${person.last_name}, ${person.first_name}</p>
                                    <p class="text-xs text-slate-400">${person.age || \'-\'} yrs · ${person.gender || \'-\'}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest ${typeClass}">${person.profile_type || \'OSY\'}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <p class="text-slate-700 dark:text-slate-300">${person.email || \'—\'}</p>
                            <p class="text-xs text-slate-400">${person.phone || \'—\'}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">${person.primary_skill || \'—\'}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${badgeClass}">
                                <span class="material-symbols-outlined text-[11px]">${vStatus === \'Verified\' ? \'verified\' : \'info\'}</span>
                                ${vStatus}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="profile-detail.php?id=${person.id}" class="text-blue-700 dark:text-blue-400 text-sm font-bold hover:underline inline-flex items-center gap-1">View <span class="material-symbols-outlined text-xs">open_in_new</span></a>
                                <a href="edit-profile.php?id=${person.id}" class="inline-flex items-center gap-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 text-xs font-bold shadow-sm transition-colors"><span class="material-symbols-outlined text-xs">edit</span> Edit</a>
                            </div>
                        </td>
                    </tr>`;
                }).join(\'\');
                
                // Pagination
                const pag = document.getElementById(\'paginationContainer\');
                if (res.totalPages > 1) {
                    pag.classList.remove(\'hidden\');
                    let pagHtml = `<nav class="flex gap-2">`;
                    for (let i = 1; i <= res.totalPages; i++) {
                        pagHtml += `<button onclick="window.loadRegistryPage(${i})" class="w-10 h-10 flex items-center justify-center rounded-lg font-bold transition-all ${i === page ? \'bg-blue-900 text-white shadow-md\' : \'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200\'}">${i}</button>`;
                    }
                    pagHtml += `</nav>`;
                    pag.innerHTML = pagHtml;
                } else {
                    pag.classList.add(\'hidden\');
                }
            });
    }
    
    // Expose to global for pagination buttons
    window.loadRegistryPage = loadRegistry;
    
    // Initialize
    loadRegistry(1);
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/sk-barangay-youth.php', $c);
echo "Done sk-barangay-youth.php\n";
