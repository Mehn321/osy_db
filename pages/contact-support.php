<?php
$pageTitle = 'Contact Support';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = (string) ($_SESSION['role'] ?? '');
$isLydo = $role === 'lydo';
$fixedLydoSupportEmail = 'aclonhemday@gmail.com';
$supportSetting = $database->fetchOne(
    "SELECT setting_value FROM system_settings WHERE setting_key = 'contact_support_email' LIMIT 1"
);
$supportEmail = $isLydo
    ? $fixedLydoSupportEmail
    : trim((string) ($supportSetting['setting_value'] ?? $fixedLydoSupportEmail));

$message = '';
$messageType = '';
$subject = '';
$details = '';
$senderEmail = trim((string) ($_SESSION['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $details = trim((string) ($_POST['details'] ?? ''));
    $senderEmail = trim((string) ($_POST['sender_email'] ?? ''));

    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Your session expired. Refresh the page and submit again.';
        $messageType = 'error';
    } elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter a valid email address so support can reply.';
        $messageType = 'error';
    } elseif ($subject === '' || strlen($subject) > 150 || $details === '' || strlen($details) > 10000) {
        $message = 'Add a subject and message. The subject must be 150 characters or fewer and the message 10,000 characters or fewer.';
        $messageType = 'error';
    } elseif (!filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'The support email is not configured correctly. Please contact the system administrator.';
        $messageType = 'error';
    } else {
        require_once __DIR__ . '/../Classes/EmailService.php';

        $senderName = trim((string) ($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Portal user'));
        $safeName = htmlspecialchars($senderName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeEmail = htmlspecialchars($senderEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeRole = htmlspecialchars(ucwords(str_replace('_', ' ', $role)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDetails = nl2br(htmlspecialchars($details, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $emailBody = "<h2>Support request</h2><p><strong>From:</strong> {$safeName}</p><p><strong>Reply email:</strong> {$safeEmail}</p><p><strong>Role:</strong> {$safeRole}</p><p><strong>Subject:</strong> {$safeSubject}</p><hr><p>{$safeDetails}</p>";

        $result = (new EmailService($database))->send(
            $supportEmail,
            '[Youth Profiling System] ' . str_replace(["\r", "\n"], '', $subject),
            $emailBody
        );
        $message = $result['success']
            ? 'Your support request was sent. The support team will reply to the email address you provided.'
            : 'Your support request could not be sent right now. ' . $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        if ($result['success']) {
            $subject = '';
            $details = '';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mx-auto max-w-3xl">
    <div class="mb-8">
        <p class="mb-2 text-xs font-bold uppercase tracking-widest text-blue-700 dark:text-blue-400">Help Center</p>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Contact Support</h1>
        <p class="mt-2 text-slate-600 dark:text-slate-400">Send a message about your account or using the Youth Profiling System.</p>
    </div>

    <?php if ($message): ?>
        <div role="status" class="mb-6 rounded-xl border p-4 <?php echo $messageType === 'success' ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200'; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-7">
        <p class="mb-6 break-all text-sm text-slate-600 dark:text-slate-300">
            Sending to <strong class="text-slate-900 dark:text-white"><?php echo htmlspecialchars($supportEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong>
            <?php if ($isLydo): ?>
                <span class="block mt-1">This LYDO support address is fixed and cannot be changed here.</span>
            <?php endif; ?>
        </p>

        <form method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
            <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">

            <div>
                <label for="sender_email" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Your reply email</label>
                <input id="sender_email" name="sender_email" type="email" required maxlength="254" autocomplete="email" value="<?php echo htmlspecialchars($senderEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:ring-2 focus:ring-blue-700 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>

            <div>
                <label for="subject" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Subject</label>
                <input id="subject" name="subject" type="text" required maxlength="150" value="<?php echo htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:ring-2 focus:ring-blue-700 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>

            <div>
                <label for="details" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">How can we help?</label>
                <textarea id="details" name="details" rows="7" required maxlength="10000" class="w-full resize-y rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:ring-2 focus:ring-blue-700 dark:border-slate-600 dark:bg-slate-700 dark:text-white"><?php echo htmlspecialchars($details, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="help.php" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-5 py-3 font-semibold text-slate-800 dark:bg-slate-700 dark:text-white">Back to Help Center</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-900 px-5 py-3 font-bold text-white hover:bg-blue-800">
                    <span class="material-symbols-outlined text-base">send</span>Send request
                </button>
            </div>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>