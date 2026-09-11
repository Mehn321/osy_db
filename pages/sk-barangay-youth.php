<?php
$pageTitle = 'My Barangay Youth Registry';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';

requireLogin();
requireRole('sk_chairman');

$userBarangay = $_SESSION['barangay'] ?? '';
if (!$userBarangay) {
    die("Error: No barangay assigned to your account.");
}

$osyProfile = new OSYProfile($database);
$auditLog   = new AuditLog($database);
$notif      = new Notification($database);

$message     = '';
$messageType = 'success';

// ── Handle inline Approve / Reject from this page ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_youth'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message     = 'Duplicate or expired form submission. Please try again.';
        $messageType = 'error';
    } else {
        $profileId = intval($_POST['profile_id'] ?? 0);
        $actionRaw = strtolower(trim($_POST['action'] ?? ''));
        $remark    = trim($_POST['remark'] ?? '');

        // Security: ensure this profile belongs to the SK's own barangay
        $checkProfile = $osyProfile->getById($profileId);
        if (!$checkProfile || $checkProfile['barangay'] !== $userBarangay) {
            die("Access Denied: You can only act on youth from your barangay.");
        }

        $newStatus = ($actionRaw === 'approve') ? 'Verified' : 'Action Required';
        $result    = $osyProfile->setVerificationStatus($profileId, $newStatus, $remark, $_SESSION['user_id']);

        if ($result['success']) {
            // IMPORTANT: Activate or keep pending the linked user account
            // The youth cannot login unless users.status = 'Active'
            $updateSuccess = false;
            if (!empty($checkProfile['created_by'])) {
                $userStatus = ($newStatus === 'Verified') ? 'Active' : 'Pending';
                try {
                    $updateResult = $database->execute(
                        "UPDATE users SET status = ? WHERE id = ?",
                        [$userStatus, $checkProfile['created_by']],
                        "si"
                    );

                    // Verify the update succeeded
                    if ($updateResult === 1 || $updateResult > 0) {
                        $updateSuccess = true;
                    } else {
                        // Log warning but don't fail the approval
                        error_log("WARNING: Users table update returned {$updateResult} affected rows. User ID: " . $checkProfile['created_by'] . ", Status: {$userStatus}");
                    }
                } catch (Exception $e) {
                    error_log("ERROR: Failed to update user status. Exception: " . $e->getMessage());
                }
            } else {
                error_log("WARNING: Profile created_by is missing or invalid. Profile ID: {$profileId}");
            }

            // Notify the youth
            if (!empty($checkProfile['created_by'])) {
                $notif->sendToUser(
                    $checkProfile['created_by'],
                    'Profile Verification ' . ($newStatus === 'Verified' ? 'Approved ✅' : 'Needs Action ⚠️'),
                    $newStatus === 'Verified'
                        ? "Your registration in Barangay {$userBarangay} has been approved by your SK Chairman. You can now log in to your account."
                        : "Your registration was reviewed. Reason: " . ($remark ?: 'Please update your submitted documents.'),
                    'SK Chairman'
                );
            }

            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                $newStatus === 'Verified' ? 'Approved youth from barangay portal' : 'Flagged youth for action from barangay portal',
                'OSYProfile',
                $profileId,
                json_encode(['remark' => $remark, 'user_update_success' => $updateSuccess])
            );

            $message     = 'Youth profile has been ' . ($newStatus === 'Verified' ? 'approved' : 'returned for action') . '.';
            $messageType = 'success';
        } else {
            $message     = $result['message'];
            $messageType = 'error';
        }
    }
}

// ── Handle receiving-barangay transfer review ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_transfer'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or expired form submission. Please try again.';
        $messageType = 'error';
    } else {
        $approved = ($_POST['action'] ?? '') === 'approve';
        $result = $osyProfile->reviewBarangayTransfer(
            (int) ($_POST['transfer_id'] ?? 0),
            $userBarangay,
            $approved,
            $_SESSION['user_id'],
            trim($_POST['remark'] ?? '')
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        if ($result['success'] && !empty($result['transfer']['requested_by'])) {
            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                $approved ? 'Approved youth barangay transfer' : 'Declined youth barangay transfer',
                'OSYProfile',
                $result['transfer']['profile_id'],
                json_encode([
                    'transfer_id' => (int) ($_POST['transfer_id'] ?? 0),
                    'from_barangay' => $result['transfer']['from_barangay'],
                    'to_barangay' => $userBarangay,
                    'remark' => trim($_POST['remark'] ?? '')
                ])
            );
            $notif->sendToUser(
                $result['transfer']['requested_by'],
                $approved ? 'Barangay Transfer Approved' : 'Barangay Transfer Declined',
                $approved ? "Your profile has been verified and transferred to Barangay {$userBarangay}." : "Your transfer request to Barangay {$userBarangay} was declined. " . (trim($_POST['remark'] ?? '') ?: ''),
                'SK Chairman'
            );
        }
    }
}

// ── Fetch pending youth awaiting SK approval ───────────────────────────────
$pendingYouth = $database->fetchAll(
    "SELECT p.*, u.email AS email, u.phone AS phone
     FROM osy_profiles p LEFT JOIN users u ON u.id = p.created_by
     WHERE p.barangay = ? AND p.verification_status IN ('Pending', 'Drafting', 'Action Required') ORDER BY p.created_at ASC",
    [$userBarangay],
    "s"
);
$pendingTransfers = $osyProfile->getPendingTransfersToBarangay($userBarangay);

// ── Fetch all (approved + others) for the full registry ───────────────────
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$allYouth = $database->fetchAll(
    "SELECT p.*, u.email AS email, u.phone AS phone
     FROM osy_profiles p LEFT JOIN users u ON u.id = p.created_by
     WHERE p.barangay = ? AND p.verification_status != 'Pending' ORDER BY p.last_name, p.first_name LIMIT ? OFFSET ?",
    [$userBarangay, $limit, $offset],
    "sii"
);

$countRes   = $database->fetchOne(
    "SELECT COUNT(*) as total FROM osy_profiles WHERE barangay = ? AND verification_status != 'Pending'",
    [$userBarangay],
    "s"
);
$totalYouth = $countRes['total'] ?? 0;
$totalPages = (int)ceil($totalYouth / $limit);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Barangay Registry: <span class="text-blue-900 dark:text-blue-400"><?= htmlspecialchars($userBarangay) ?></span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">Approve new youth registrations and view all registered members in your barangay.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="create-profile.php?type=OSY" class="bg-blue-900 text-white px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-blue-800 transition-all shadow-sm text-sm font-bold">
                <span class="material-symbols-outlined text-base">person_add</span>
                Register Youth
            </a>
            <?php if (!empty($pendingYouth)): ?>
                <span class="bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 px-4 py-2 rounded-xl flex items-center gap-2 text-sm font-bold border border-amber-200 dark:border-amber-800">
                    <span class="material-symbols-outlined text-base">hourglass_empty</span>
                    <?= count($pendingYouth) ?> Awaiting Approval
                </span>
            <?php endif; ?>
            <?php if (!empty($pendingTransfers)): ?>
                <span class="bg-violet-100 dark:bg-violet-900/40 text-violet-800 dark:text-violet-200 px-4 py-2 rounded-xl flex items-center gap-2 text-sm font-bold border border-violet-200 dark:border-violet-800">
                    <span class="material-symbols-outlined text-base">swap_horiz</span>
                    <?= count($pendingTransfers) ?> Transfer <?= count($pendingTransfers) === 1 ? 'Request' : 'Requests' ?>
                </span>
            <?php endif; ?>
            <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-200 px-4 py-2 rounded-xl flex items-center gap-2 border border-blue-200 dark:border-blue-800">
                <span class="material-symbols-outlined text-base">groups</span>
                <span class="font-black text-xl"><?= $totalYouth ?></span>
                <span class="text-sm font-bold opacity-70">Verified Members</span>
            </div>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl flex items-center gap-3 <?= $messageType === 'error' ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-green-50 border border-green-200 text-green-800' ?>">
        <span class="material-symbols-outlined"><?= $messageType === 'error' ? 'error' : 'check_circle' ?></span>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($pendingTransfers)): ?>
    <div class="mb-10 rounded-2xl border border-violet-200 dark:border-violet-800 bg-violet-50/60 dark:bg-violet-900/10 p-6">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-violet-700 dark:text-violet-300 text-2xl">swap_horiz</span>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Incoming Barangay Transfers</h2>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-5">Review these youth before accepting them into <?= htmlspecialchars($userBarangay) ?>. Until you approve, each youth remains in their old barangay registry.</p>
        <div class="space-y-4">
            <?php foreach ($pendingTransfers as $transfer): ?>
                <div class="rounded-xl bg-white dark:bg-slate-800 border border-violet-100 dark:border-violet-900 p-5">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div>
                            <p class="font-extrabold text-slate-900 dark:text-white text-lg"><?= htmlspecialchars(trim($transfer['first_name'] . ' ' . ($transfer['middle_name'] ?? '') . ' ' . $transfer['last_name'])) ?></p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">From <strong><?= htmlspecialchars($transfer['from_barangay']) ?></strong> · Requested <?= htmlspecialchars(date('M d, Y', strtotime($transfer['requested_at']))) ?></p>
                            <?php if (!empty($transfer['email']) || !empty($transfer['phone'])): ?><p class="text-xs text-slate-500 mt-2"><?= htmlspecialchars($transfer['email'] ?: $transfer['phone']) ?></p><?php endif; ?>
                            <?php if (!empty($transfer['request_remark'])): ?><p class="mt-3 text-sm text-slate-700 dark:text-slate-200"><strong>Reason:</strong> <?= htmlspecialchars($transfer['request_remark']) ?></p><?php endif; ?>
                        </div>
                        <form method="POST" class="w-full lg:w-96 space-y-3">
                            <input type="hidden" name="review_transfer" value="1">
                            <input type="hidden" name="transfer_id" value="<?= (int) $transfer['id'] ?>">
                            <input type="hidden" name="form_nonce" value="<?= htmlspecialchars(getFormNonce()) ?>">
                            <textarea name="remark" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-sm" placeholder="Review note (optional)"></textarea>
                            <div class="flex gap-2">
                                <button name="action" value="approve" class="flex-1 rounded-xl bg-violet-700 hover:bg-violet-800 text-white px-3 py-2 text-sm font-bold">Verify &amp; Transfer</button>
                                <button name="action" value="reject" class="flex-1 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 px-3 py-2 text-sm font-bold">Decline</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($pendingYouth)): ?>
    <!-- ══ PENDING APPROVALS SECTION ══════════════════════════════════════════ -->
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
            <span class="material-symbols-outlined text-amber-600 text-2xl">pending_actions</span>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Pending Approval <span class="ml-2 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-sm font-bold px-2 py-0.5 rounded-full"><?= count($pendingYouth) ?></span></h2>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">These youth have self-registered and selected your barangay. Review their info and approve or return for correction.</p>

        <div class="space-y-5">
            <?php foreach ($pendingYouth as $p): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden">
                    <!-- Profile Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-amber-50 dark:bg-amber-900/10 border-b border-amber-100 dark:border-amber-900/30">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-amber-700 dark:text-amber-400">person</span>
                            </div>
                            <div>
                                <p class="font-extrabold text-slate-900 dark:text-white text-lg"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Registered <?= htmlspecialchars(date('M j, Y', strtotime($p['created_at']))) ?>
                                    &nbsp;·&nbsp; <?= htmlspecialchars($p['gender'] ?? '') ?>
                                    &nbsp;·&nbsp; <?= htmlspecialchars($p['age'] ?? '-') ?> yrs
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-bold uppercase tracking-wide">
                            <span class="material-symbols-outlined text-xs">hourglass_empty</span>
                            Pending
                        </span>
                    </div>

                    <!-- Profile Details Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-slate-100 dark:bg-slate-700">
                        <div class="bg-white dark:bg-slate-800 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Educational Attainment</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($p['education_level'] ?? '—') ?></p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Primary Skill</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($p['primary_skill'] ?? '—') ?></p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Contact</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($p['phone'] ?? '—') ?></p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-4">
                            <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-1">Civil Status</p>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($p['civil_status'] ?? '—') ?></p>
                        </div>
                    </div>

                    <?php if (!empty($p['reason_for_not_in_school'])): ?>
                        <div class="px-6 py-3 bg-slate-50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-700">
                            <p class="text-xs text-slate-500 dark:text-slate-400"><strong>Reason not in school:</strong> <?= htmlspecialchars($p['reason_for_not_in_school']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $pendingProfileImage = !empty($p['image_path']) ? '../' . ltrim($p['image_path'], '/') : '';
                    $pendingGovtIdImage = !empty($p['govt_id_image']) ? '../' . ltrim($p['govt_id_image'], '/') : '';
                    $pendingCertDoc = !empty($p['identity_document_path']) ? '../' . ltrim($p['identity_document_path'], '/') : '';
                    $pendingDocs = array_filter([
                        ['label' => 'Profile Photo', 'path' => $pendingProfileImage],
                        ['label' => 'Government ID', 'path' => $pendingGovtIdImage],
                        ['label' => 'Certification / Document', 'path' => $pendingCertDoc],
                    ], fn($doc) => !empty($doc['path']));
                    ?>
                    <?php if (!empty($pendingDocs) || !empty($p['govt_id_type']) || !empty($p['govt_id_number'])): ?>
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-700">
                            <?php if (!empty($p['govt_id_type']) || !empty($p['govt_id_number'])): ?>
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-400 text-base">badge</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                        <strong><?= htmlspecialchars($p['govt_id_type'] ?? 'ID') ?>:</strong> <?= htmlspecialchars($p['govt_id_number'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($pendingDocs)): ?>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-3">Submitted documents</p>
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <?php foreach ($pendingDocs as $doc): ?>
                                        <div class="rounded-xl border border-slate-200 bg-white dark:bg-slate-800 p-3">
                                            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= htmlspecialchars($doc['label']) ?></p>
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $doc['path'])): ?>
                                                <img src="<?= htmlspecialchars($doc['path']) ?>" alt="<?= htmlspecialchars($doc['label']) ?>" class="h-28 w-full rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($doc['path']) ?>" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:underline">
                                                    <span class="material-symbols-outlined text-base">description</span>
                                                    Open file
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Approve / Reject Form -->
                    <div class="p-6 border-t border-slate-100 dark:border-slate-700">
                        <form method="POST" class="space-y-3">
                            <input type="hidden" name="form_nonce" value="<?= htmlspecialchars(getFormNonce()) ?>">
                            <input type="hidden" name="verify_youth" value="1">
                            <input type="hidden" name="profile_id" value="<?= intval($p['id']) ?>">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 block mb-1">
                                    Remark / reason (optional — shown to youth if returned)
                                </label>
                                <textarea name="remark" rows="2"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2 text-sm text-slate-800 dark:text-white resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g. Please re-upload a clearer photo of your ID..."></textarea>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" name="action" value="approve"
                                    class="flex items-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white px-6 py-2.5 text-sm font-bold transition-all shadow-sm">
                                    <span class="material-symbols-outlined text-base">check_circle</span>
                                    Approve — This youth lives in my barangay
                                </button>
                                <button type="submit" name="action" value="reject"
                                    class="flex items-center gap-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-rose-100 dark:hover:bg-rose-900/30 text-slate-700 dark:text-slate-300 hover:text-rose-700 dark:hover:text-rose-400 px-5 py-2.5 text-sm font-bold transition-all">
                                    <span class="material-symbols-outlined text-base">undo</span>
                                    Return for Correction
                                </button>
                                <a href="profile-detail.php?id=<?= intval($p['id']) ?>"
                                    class="ml-auto text-xs text-blue-700 dark:text-blue-400 font-bold hover:underline flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">open_in_new</span>Full Profile
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ══ FULL REGISTRY TABLE ═══════════════════════════════════════════════ -->
<div class="mb-4 flex items-center gap-3">
    <span class="material-symbols-outlined text-slate-500 text-xl">list_alt</span>
    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Verified Registry</h2>
    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">(<?= $totalYouth ?> members)</span>
</div>

<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50">
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Full Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Skill</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php if (!empty($allYouth)): ?>
                    <?php foreach ($allYouth as $person): ?>
                        <?php
                        $vStatus    = $person['verification_status'] ?? 'Drafting';
                        $badgeClass = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                        if ($vStatus === 'Verified')         $badgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                        elseif ($vStatus === 'Action Required') $badgeClass = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-slate-400 text-sm">person</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($person['last_name'] . ', ' . $person['first_name']) ?></p>
                                        <p class="text-xs text-slate-400"><?= $person['age'] ?> yrs · <?= htmlspecialchars($person['gender']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest <?= $person['profile_type'] === 'OSY' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= $person['profile_type'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <p class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($person['email'] ?? '—') ?></p>
                                <p class="text-xs text-slate-400"><?= htmlspecialchars($person['phone'] ?? '—') ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                <?= htmlspecialchars($person['primary_skill'] ?? '—') ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold <?= $badgeClass ?>">
                                    <span class="material-symbols-outlined text-[11px]">
                                        <?= $vStatus === 'Verified' ? 'verified' : 'info' ?>
                                    </span>
                                    <?= $vStatus ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="profile-detail.php?id=<?= $person['id'] ?>"
                                        class="text-blue-700 dark:text-blue-400 text-sm font-bold hover:underline inline-flex items-center gap-1">
                                        View <span class="material-symbols-outlined text-xs">open_in_new</span>
                                    </a>
                                    <a href="edit-profile.php?id=<?= $person['id'] ?>"
                                        class="inline-flex items-center gap-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 text-xs font-bold shadow-sm transition-colors">
                                        <span class="material-symbols-outlined text-xs">edit</span>
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                            <span class="material-symbols-outlined text-4xl opacity-20 block mb-3">groups_3</span>
                            <p class="font-medium">No verified youth found in Barangay <?= htmlspecialchars($userBarangay) ?>.</p>
                            <?php if (!empty($pendingYouth)): ?>
                                <p class="text-sm mt-1">Approve the pending registrations above to populate this list.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex justify-center">
            <nav class="flex gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                        class="w-10 h-10 flex items-center justify-center rounded-lg font-bold transition-all <?= $i === $page ? 'bg-blue-900 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>