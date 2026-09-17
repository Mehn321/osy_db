<?php
$pageTitle = 'Youth Verification';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/OSYProfile.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/Reference.php';

// Bug 8 fix: Youth verification is SK Chairman only — LYDO uses the full profiles admin panel
requireLogin();
requireRole(['sk_chairman']);

$osyProfile = new OSYProfile($database);
$auditLog = new AuditLog($database);
$notification = new Notification($database);
$userClass = new User($database);

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_youth'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        $profileId = intval($_POST['profile_id']);

        // Barangay scoping: SK Chairman can only verify youth from their barangay
        $checkProfile = $osyProfile->getById($profileId);
        if (!$checkProfile || $checkProfile['barangay'] !== $_SESSION['barangay']) {
            die("Access Denied: Cannot verify youth from another barangay.");
        }

        // Bug 3 fix: three distinct action states
        $rawAction = $_POST['action'] ?? '';
        if ($rawAction === 'approve') {
            $action = 'Verified';
        } elseif ($rawAction === 'reject') {
            $action = 'Rejected';
        } else {
            $action = 'Action Required'; // "Return for Correction"
        }

        $remark = trim($_POST['remark'] ?? '');

        $result = $osyProfile->setVerificationStatus($profileId, $action, $remark, $_SESSION['user_id']);

        if ($result['success']) {
            $profile = $osyProfile->getById($profileId);

            if ($profile && isset($profile['created_by'])) {
                $userId = $profile['created_by'];

                // Bug 4 fix: set user status based on action and CHECK the result
                if ($action === 'Verified') {
                    $newUserStatus = 'Active';
                } elseif ($action === 'Rejected') {
                    $newUserStatus = 'Declined'; // block login, allow re-registration with new account
                } else {
                    $newUserStatus = 'Action Required'; // returned for correction, allow login to fix & resubmit
                }

                $updateResult = $database->execute(
                    "UPDATE users SET status = ? WHERE id = ?",
                    [$newUserStatus, $userId],
                    "si"
                );

                if (!$updateResult) {
                    $message = 'Profile status was updated, but the user account status could not be updated. Please contact a system administrator.';
                    $messageType = 'error';
                } else {
                    // Send notification to the youth with appropriate message
                    if ($action === 'Verified') {
                        $notifTitle   = 'Registration Approved ✅';
                        $notifBody    = 'Your youth registration has been approved by your SK Chairman. You can now log in and access job opportunities and training programs.';
                    } elseif ($action === 'Rejected') {
                        $notifTitle   = 'Registration Rejected ❌';
                        $notifBody    = 'Your youth registration has been rejected by your SK Chairman.'
                            . ($remark ? ' Reason: ' . $remark : '')
                            . ' You may re-register with a new account or contact your SK Chairman for further guidance.';
                    } else {
                        $notifTitle   = 'Registration Needs Correction ⚠️';
                        $notifBody    = 'Your youth registration needs corrections before it can be approved.'
                            . ($remark ? ' Reason: ' . $remark : ' Please review and update your profile.');
                    }

                    $notification->sendToUser($userId, $notifTitle, $notifBody, 'SK Chairman');

                    $actionLabel = $action === 'Verified' ? 'approved' : ($action === 'Rejected' ? 'rejected' : 'returned for correction');
                    $message = 'Youth profile has been ' . $actionLabel . '.';
                    $messageType = 'success';
                }
            }

            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                'SK Chairman ' . ($action === 'Verified' ? 'approved' : ($action === 'Rejected' ? 'rejected' : 'returned')) . ' youth verification',
                'OSYProfile',
                $profileId,
                json_encode(['action' => $action, 'remark' => $remark])
            );
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}


?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Verification</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">Youth Verification</span>
    </nav>
    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Review Youth Registrations</h1>
    <p class="text-slate-600 mt-2 max-w-2xl">Approve or reject youth registration submissions from your barangay before they enter the matching pipeline.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<div id="verifyContainer" class="space-y-6">
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
</div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Reviewer Note (optional — required for Reject or Return for Correction)</p>
                    <form method="POST" class="space-y-4 mt-4">
                        <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                        <input type="hidden" name="verify_youth" value="1">
                        <input type="hidden" name="profile_id" value="<?php echo intval($profile['id']); ?>">
                        <textarea name="remark" rows="3" class="w-full rounded-3xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="Add a note for the youth (required when returning or rejecting)..."></textarea>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" name="action" value="approve" class="rounded-2xl bg-green-700 text-white px-6 py-3 text-sm font-semibold hover:bg-green-600 transition flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">check_circle</span>Approve
                                </button>
                                <button type="submit" name="action" value="return" class="rounded-2xl bg-amber-600 text-white px-6 py-3 text-sm font-semibold hover:bg-amber-500 transition flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">edit_note</span>Return for Correction
                                </button>
                                <button type="submit" name="action" value="reject" class="rounded-2xl bg-red-700 text-white px-6 py-3 text-sm font-semibold hover:bg-red-600 transition flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">block</span>Reject
                                </button>
                            </div>
                            <p class="text-xs text-slate-500">Approvals move the profile to the opportunity matching queue.</p>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<script>
(function() {
    function loadPendingYouth() {
        fetch(`../api/get_system_data.php?view=verify_youth&verification_status=Pending`)
            .then(r => r.json())
            .then(res => {
                const container = document.getElementById('verifyContainer');
                if (!res.success || !res.profiles || res.profiles.length === 0) {
                    container.innerHTML = `<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8"><h2 class="text-xl font-bold text-slate-900 dark:text-white">No pending youth profiles</h2><p class="text-sm text-slate-500 mt-3">There are no youth profiles awaiting verification for your barangay at the moment.</p></div>`;
                    return;
                }
                
                const nonce = document.querySelector('input[name="form_nonce"]')?.value || '<?php echo htmlspecialchars(getFormNonce()); ?>';
                
                container.innerHTML = res.profiles.map(profile => {
                    const profImg = profile.image_path ? `../${profile.image_path.replace(/^\//, '')}` : '';
                    const idImg = profile.govt_id_image ? `youth-document.php?type=govt_id&profile_id=${profile.id}` : '';
                    const certImg = profile.identity_document_path ? `youth-document.php?type=certification&profile_id=${profile.id}` : '';
                    
                    let docsHtml = '';
                    if (profImg || idImg || certImg) {
                        docsHtml = `<div class="rounded-3xl border border-slate-200 bg-slate-50 dark:bg-slate-900 p-4"><p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Uploaded files</p><div class="mt-4 space-y-4">`;
                        
                        if (profImg) {
                            docsHtml += `<div class="rounded-2xl border border-slate-200 bg-white p-3"><p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Profile Photo</p>`;
                            if (profImg.match(/\.(jpg|jpeg|png|gif|webp)$/i)) docsHtml += `<img src="${profImg}" class="max-h-48 w-full rounded-xl object-cover border border-slate-200">`;
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
                                <p class="text-sm text-slate-500">Profile Type: ${profile.profile_type || 'OSY'}</p>
                                <p class="text-sm text-slate-500">Registered: ${profile.created_at || 'N/A'}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-orange-100 text-orange-800 px-3 py-1.5 text-xs font-semibold uppercase">Pending Verification</span>
                        </div>
                        <div class="grid gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Age</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.age || '-'}</p></div>
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Educational Attainment</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.education_level || '-'}</p></div>
                            <div class="rounded-3xl bg-slate-50 dark:bg-slate-900 p-4"><p class="text-xs uppercase font-bold text-slate-500 dark:text-slate-400">Phone</p><p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">${profile.phone || '-'}</p></div>
                        </div>
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 dark:bg-slate-900 p-4">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Submitted information</p>
                                <dl class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Email</dt><dd class="text-right">${profile.email || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Gender</dt><dd class="text-right">${profile.gender || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Birthdate</dt><dd class="text-right">${profile.date_of_birth || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Civil Status</dt><dd class="text-right">${profile.civil_status || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Purok</dt><dd class="text-right">${profile.purok || profile.address || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Barangay</dt><dd class="text-right">${profile.barangay || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Occupation</dt><dd class="text-right">${profile.occupation || 'N/A'}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-medium text-slate-500">Primary Skill</dt><dd class="text-right">${profile.primary_skill || 'N/A'}</dd></div>
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
                }).join('');
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>