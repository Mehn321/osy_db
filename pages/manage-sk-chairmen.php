<?php
$pageTitle = 'Manage SK Chairmen';
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/User.php';
require_once __DIR__ . '/../Classes/Reference.php';
require_once __DIR__ . '/../Classes/AuditLog.php';
require_once __DIR__ . '/../Classes/Notification.php';

requireLogin();
requireRole('lydo');

$reference = new Reference($database);
$barangays = $reference->getByCategory('barangay');
$userModel = new User($database);
$auditLog = new AuditLog($database);

$message = '';
$messageType = 'success';
$newChairmanPassword = null;

// Handle DELETE chairman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chairman'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Invalid or expired form submission.';
        $messageType = 'error';
    } else {
        try {
            $chairmanId = intval($_POST['chairman_id']);
            $database->execute("DELETE FROM users WHERE id = ? AND role = 'sk_chairman'", [$chairmanId], "i");
            $message = 'SK Chairman account deleted successfully.';
            $messageType = 'success';
            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                'Deleted SK Chairman account',
                'User',
                $chairmanId,
                ''
            );
        } catch (Exception $e) {
            $message = 'Error deleting SK Chairman: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Handle EDIT chairman details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_chairman'])) {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Invalid or expired form submission.';
        $messageType = 'error';
    } else {
        try {
            $chairmanId = intval($_POST['chairman_id']);
            $fullname = trim($_POST['fullname'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $barangay = trim($_POST['barangay'] ?? '');
            if ($fullname === '' || $username === '' || $email === '' || $barangay === '') {
                throw new Exception('All chairman details are required.');
            }
            $existing = $database->fetchOne(
                "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND id != ? AND status != 'Deleted' LIMIT 1",
                [$barangay, $chairmanId],
                'si'
            );
            if ($existing) {
                throw new Exception('Another SK Chairman is already assigned to this barangay.');
            }
            $database->execute(
                "UPDATE users SET fullname = ?, username = ?, email = ?, barangay = ? WHERE id = ? AND role = 'sk_chairman'",
                [$fullname, $username, $email, $barangay, $chairmanId],
                'ssssi'
            );
            $message = 'SK Chairman details updated successfully.';
            $messageType = 'success';
            $auditLog->logAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                'Updated SK Chairman details',
                'User',
                $chairmanId,
                ''
            );
        } catch (Exception $e) {
            $message = 'Error updating SK Chairman status: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_chairman'])) {
    // Prevent duplicate submissions using server-side form nonce
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'This form has already been submitted or the session expired. Please refresh and try again.';
        $messageType = 'error';
    } else {
        try {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $fullname = trim($_POST['fullname']);
            $barangay = trim($_POST['barangay']);

            // CHECK: Prevent duplicate SK Chairman per barangay
            $existingChairman = $database->fetchOne(
                "SELECT id FROM users WHERE role = 'sk_chairman' AND barangay = ? AND status != 'Deleted' LIMIT 1",
                [$barangay],
                "s"
            );
            if ($existingChairman) {
                throw new Exception("A SK Chairman is already assigned to Barangay " . htmlspecialchars($barangay) . ". Please deactivate or delete the existing chairman first.");
            }

            $result = $userModel->createUser([
                'username' => $username,
                'email' => $email,
                'fullname' => $fullname,
                'role' => 'sk_chairman',
                'status' => 'Active',
                'barangay' => $barangay,
                'temp_password_required' => 1,
                'created_by' => $_SESSION['user_id']
            ]);

            if ($result['success']) {
                $newChairmanPassword = $result['password'] ?? null;
                $message = 'SK Chairman account created successfully.';
                $messageType = 'success';

                $createdId = $result['id'] ?? $result['user_id'] ?? null;

                // 1. Create a system notification for the new SK Chairman
                $notifObj = new Notification($database);
                $notifObj->sendToUser(
                    $createdId,
                    'Account Created',
                    "Welcome to the Youth Profiling System! Your SK Chairman account has been created by the LYDO. Your temporary password is: $newChairmanPassword. Please change it on your first login.",
                    'System',
                    $_SESSION['user_id']
                );

                // 2. Send welcome email with login credentials
                try {
                    require_once __DIR__ . '/../Classes/EmailService.php';
                    $emailService = new EmailService($database);
                    $emailBody = "
                    <h3>Welcome to the Municipal Youth Profiling System</h3>
                    <p>Hello <strong>" . htmlspecialchars($fullname) . "</strong>,</p>
                    <p>Your SK Chairman account for Barangay <strong>" . htmlspecialchars($barangay) . "</strong> has been created by the LYDO.</p>
                    <p>Here are your temporary credentials to log in:</p>
                    <ul>
                        <li><strong>Username:</strong> " . htmlspecialchars($username) . "</li>
                        <li><strong>Temporary Password:</strong> " . htmlspecialchars($newChairmanPassword) . "</li>
                    </ul>
                    <p>Please log in and change your password to continue using the system.</p>
                ";
                    $emailService->send($email, 'SK Chairman Account Created', $emailBody);
                } catch (Exception $ex) {
                    // Silently log or capture email delivery error so it doesn't block the UI
                    $message .= ' (Email notification could not be sent)';
                }

                $auditLog->logAction(
                    $_SESSION['user_id'],
                    $_SESSION['role'],
                    'Created SK Chairman account',
                    'User',
                    $createdId,
                    json_encode(['username' => $username, 'barangay' => $barangay])
                );
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$chairmen = $userModel->getUsersByRole('sk_chairman');
$editId = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$editChairman = $editId > 0 ? $database->fetchOne("SELECT * FROM users WHERE id = ? AND role = 'sk_chairman'", [$editId], 'i') : null;
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Administration</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">SK Chairmen</span>
    </nav>
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Manage SK Chairmen</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2 max-w-2xl">Create, update, and remove SK Chairman accounts for barangay-level youth verification.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-900/20 px-4 py-2 text-sm font-bold text-blue-800 dark:text-blue-300">
            <span class="material-symbols-outlined text-base">groups</span><?php echo count($chairmen); ?> chairmen
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'error' ? 'text-red-800' : 'text-green-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'error' ? 'error' : 'check_circle'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<?php if ($newChairmanPassword): ?>
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <p class="text-sm font-semibold text-blue-900">Temporary password generated:</p>
        <p class="mt-2 text-xl font-bold text-slate-900 bg-slate-100 p-3 rounded-xl break-all"><?php echo htmlspecialchars($newChairmanPassword); ?></p>
        <p class="mt-2 text-xs text-slate-500">Share this temporary password with the SK Chairman. They will be prompted to reset it on first login.</p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_420px] gap-8">
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">New SK Chairman Account</h2>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="create_chairman" value="1">
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Full Name</label>
                    <input name="fullname" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="Juan dela Cruz">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Username</label>
                    <input name="username" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="skchairman01">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
                    <input type="email" name="email" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white" placeholder="chairman@example.com">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Assigned Barangay</label>
                    <select name="barangay" required class="w-full mt-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white">
                        <option value="">Select Barangay</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?php echo htmlspecialchars($barangay); ?>"><?php echo htmlspecialchars($barangay); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-900 text-white py-3 px-6 text-sm font-semibold hover:bg-blue-800 transition">Create SK Chairman</button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Current SK Chairmen</h2>
            <?php if (empty($chairmen)): ?>
                <p class="text-sm text-slate-500">No SK Chairman accounts have been created yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
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
                        <tbody>
                            <?php foreach ($chairmen as $chairman): ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['fullname']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['username']); ?></td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['barangay'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $chairman['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($chairman['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4"><?php echo htmlspecialchars($chairman['created_at']); ?></td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap">
                                        <button type="button" class="edit-chairman-btn inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold transition" data-id="<?php echo (int)$chairman['id']; ?>" data-fullname="<?php echo htmlspecialchars($chairman['fullname'], ENT_QUOTES); ?>" data-username="<?php echo htmlspecialchars($chairman['username'], ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($chairman['email'], ENT_QUOTES); ?>" data-barangay="<?php echo htmlspecialchars($chairman['barangay'] ?? '', ENT_QUOTES); ?>"><span class="material-symbols-outlined text-base">edit</span>Edit</button>
                                        <form method="POST" class="inline-block ml-2" onsubmit="return confirm('Permanently delete this SK Chairman account? This cannot be undone.');">
                                            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                                            <input type="hidden" name="delete_chairman" value="1">
                                            <input type="hidden" name="chairman_id" value="<?php echo intval($chairman['id']); ?>">
                                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-800 font-semibold transition"><span class="material-symbols-outlined text-base">delete</span>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="editChairmanModal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4" role="dialog" aria-modal="true" aria-labelledby="editChairmanTitle">
    <div class="w-full max-w-2xl rounded-3xl bg-white dark:bg-slate-800 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 dark:border-slate-700 px-6 py-5">
            <div>
                <h2 id="editChairmanTitle" class="text-xl font-bold text-slate-900 dark:text-white">Edit SK Chairman</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Update account details and barangay assignment.</p>
            </div>
            <button type="button" id="closeEditChairman" class="text-slate-500 hover:text-slate-900 dark:hover:text-white" title="Close edit dialog"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
            <input type="hidden" name="edit_chairman" value="1">
            <input type="hidden" name="chairman_id" id="editChairmanId">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
            <div><label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="editFullname">Full Name</label><input id="editFullname" name="fullname" required class="w-full mt-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white"></div>
            <div><label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="editUsername">Username</label><input id="editUsername" name="username" required class="w-full mt-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white"></div>
            <div><label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="editEmail">Email Address</label><input id="editEmail" type="email" name="email" required class="w-full mt-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white"></div>
            <div><label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="editBarangay">Assigned Barangay</label><select id="editBarangay" name="barangay" required class="w-full mt-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 py-3 px-4 text-sm text-slate-900 dark:text-white"><?php foreach ($barangays as $barangay): ?><option value="<?php echo htmlspecialchars($barangay); ?>"><?php echo htmlspecialchars($barangay); ?></option><?php endforeach; ?></select></div>
            <div class="md:col-span-2 flex justify-end gap-3 pt-2"><button type="button" id="cancelEditChairman" class="rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 py-3 px-6 text-sm font-semibold">Cancel</button><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-900 text-white py-3 px-6 text-sm font-semibold hover:bg-blue-800"><span class="material-symbols-outlined text-base">save</span>Save Details</button></div>
        </form>
    </div>
</div>

<script>
    const editChairmanModal = document.getElementById('editChairmanModal');
    const closeEditChairman = () => editChairmanModal?.classList.replace('flex', 'hidden');
    document.querySelectorAll('.edit-chairman-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('editChairmanId').value = button.dataset.id;
            document.getElementById('editFullname').value = button.dataset.fullname;
            document.getElementById('editUsername').value = button.dataset.username;
            document.getElementById('editEmail').value = button.dataset.email;
            document.getElementById('editBarangay').value = button.dataset.barangay;
            editChairmanModal.classList.replace('hidden', 'flex');
            document.getElementById('editFullname').focus();
        });
    });
    document.getElementById('closeEditChairman')?.addEventListener('click', closeEditChairman);
    document.getElementById('cancelEditChairman')?.addEventListener('click', closeEditChairman);
    editChairmanModal?.addEventListener('click', event => {
        if (event.target === editChairmanModal) closeEditChairman();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeEditChairman();
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>