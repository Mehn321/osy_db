<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/verify-youth.php');

// Remove synchronous fetch
$c = preg_replace('/\$pendingProfiles\s*=\s*\$osyProfile->getPendingByBarangay\(\$_SESSION\[\'barangay\'\]\);/s', '', $c);

// Replace empty state check and PHP foreach with skeletons and container
$skeletons = '<div id="verifyContainer" class="space-y-6">
    <?php for ($i=0; $i<3; $i++): ?>
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div><div class="skeleton-pulse h-6 w-48 rounded mb-2"></div><div class="skeleton-pulse h-4 w-32 rounded mb-1"></div><div class="skeleton-pulse h-4 w-24 rounded"></div></div>
                <div class="skeleton-pulse h-8 w-32 rounded-full"></div>
            </div>
            <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><div class="skeleton-pulse h-3 w-16 rounded mb-2"></div><div class="skeleton-pulse h-6 w-12 rounded"></div></div>
                <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><div class="skeleton-pulse h-3 w-32 rounded mb-2"></div><div class="skeleton-pulse h-6 w-24 rounded"></div></div>
                <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><div class="skeleton-pulse h-3 w-16 rounded mb-2"></div><div class="skeleton-pulse h-6 w-32 rounded"></div></div>
            </div>
        </div>
    <?php endfor; ?>
</div>';

$c = preg_replace('/<\?php if \(empty\(\$pendingProfiles\)\): \?>.*?<\?php endif; \?>/s', $skeletons, $c);

$js = '
<script>
(function() {
    function loadPendingYouth() {
        fetch(`../api/get_system_data.php?view=verify_youth&verification_status=Pending`)
            .then(r => r.json())
            .then(res => {
                const container = document.getElementById(\'verifyContainer\');
                if (!res.success || !res.profiles || res.profiles.length === 0) {
                    container.innerHTML = `<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8"><h2 class="text-xl font-bold text-slate-900 dark:text-white">No pending youth profiles</h2><p class="text-sm text-slate-500 mt-3">There are no youth profiles awaiting verification for your barangay at the moment.</p></div>`;
                    return;
                }
                
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                
                container.innerHTML = res.profiles.map(profile => {
                    const profImg = profile.image_path ? `../${profile.image_path.replace(/^\\//, \'\')}` : \'\';
                    const idImg = profile.govt_id_image ? `youth-document.php?type=govt_id&profile_id=${profile.id}` : \'\';
                    const certImg = profile.identity_document_path ? `youth-document.php?type=certification&profile_id=${profile.id}` : \'\';
                    
                    let docsHtml = \'\';
                    if (profImg || idImg || certImg) {
                        docsHtml = `<div class="rounded-3xl border border-slate-200 bg-slate-50 dark:bg-slate-900 p-4"><p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Uploaded files</p><div class="mt-4 space-y-4">`;
                        
                        if (profImg) {
                            docsHtml += `<div class="rounded-2xl border border-slate-200 bg-white p-3"><p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Profile Photo</p>`;
                            if (profImg.match(/\\.(jpg|jpeg|png|gif|webp)$/i)) docsHtml += `<img src="${profImg}" class="max-h-48 w-full rounded-xl object-cover border border-slate-200">`;
                            else docsHtml += `<a href="${profImg}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:underline">Open document</a>`;
                            docsHtml += `</div>`;
                        }
                        if (idImg) {
                            docsHtml += `<div class="rounded-2xl border border-slate-200 bg-white p-3"><p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Government ID</p><a href="${idImg}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:underline">Open document</a></div>`;
                        }
                        if (certImg) {
                            docsHtml += `<div class="rounded-2xl border border-slate-200 bg-white p-3"><p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Certification / Document</p><a href="${certImg}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:underline">Open document</a></div>`;
                        }
                        docsHtml += `</div></div>`;
                    }
                    
                    return `
                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">${profile.first_name} ${profile.last_name}</h2>
                                <p class="text-sm text-slate-500 mt-2">Barangay: ${profile.barangay}</p>
                                <p class="text-sm text-slate-500">Profile Type: ${profile.profile_type || \'OSY\'}</p>
                                <p class="text-sm text-slate-500">Registered: ${profile.created_at || \'N/A\'}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-orange-100 text-orange-800 px-3 py-1.5 text-xs font-semibold uppercase">Pending Verification</span>
                        </div>
                        <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Age</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.age || \'-\'}</p></div>
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Educational Attainment</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.education_level || \'-\'}</p></div>
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Phone</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.phone || \'-\'}</p></div>
                        </div>
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 dark:bg-slate-900 p-4">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Submitted information</p>
                                <dl class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Email</dt><dd class="text-right">${profile.email || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Gender</dt><dd class="text-right">${profile.gender || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Birthdate</dt><dd class="text-right">${profile.date_of_birth || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Civil Status</dt><dd class="text-right">${profile.civil_status || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Purok</dt><dd class="text-right">${profile.purok || profile.address || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Barangay</dt><dd class="text-right">${profile.barangay || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Occupation</dt><dd class="text-right">${profile.occupation || \'N/A\'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Primary Skill</dt><dd class="text-right">${profile.primary_skill || \'N/A\'}</dd></div>
                                </dl>
                            </div>
                            ${docsHtml}
                        </div>
                        <div class="mt-6">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Reviewer Note (optional &mdash; required for Reject or Return for Correction)</p>
                            <form method="POST" class="space-y-4 mt-4">
                                <input type="hidden" name="form_nonce" value="${nonce}">
                                <input type="hidden" name="verify_youth" value="1">
                                <input type="hidden" name="profile_id" value="${profile.id}">
                                <textarea name="remark" rows="3" class="w-full rounded-3xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="Add a note for the youth (required when returning or rejecting)..."></textarea>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" name="action" value="approve" class="rounded-2xl bg-green-700 text-white px-6 py-3 text-sm font-semibold hover:bg-green-600 transition flex items-center gap-2">Approve</button>
                                        <button type="submit" name="action" value="return" class="rounded-2xl bg-amber-600 text-white px-6 py-3 text-sm font-semibold hover:bg-amber-500 transition flex items-center gap-2">Return for Correction</button>
                                        <button type="submit" name="action" value="reject" class="rounded-2xl bg-red-700 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition flex items-center gap-2">Reject</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>`;
                }).join(\'\');
            });
    }
    
    loadPendingYouth();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/verify-youth.php', $c);
echo "Done verify-youth.php\n";
