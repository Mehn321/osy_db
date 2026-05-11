<?php
$pageTitle = 'My Notifications';
require_once __DIR__ . '/../includes/header.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$notification = new Notification($database);
$message = '';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notificationId = intval($_POST['notification_id']);
    $result = $notification->markAsRead($notificationId, $_SESSION['user_id']);
    if ($result['success']) {
        $message = 'Notification marked as read.';
    }
}

// Get user's notifications
$notifications = $notification->getUserNotifications($_SESSION['user_id']);
$unreadCount = $notification->getUnreadCount($_SESSION['user_id']);
?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Main</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">My Notifications</span>
    </nav>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Your Notifications</h1>
        <?php if ($unreadCount > 0): ?>
            <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-sm font-semibold">
                <span class="material-symbols-outlined text-base">notifications_active</span>
                <?php echo $unreadCount; ?> unread
            </span>
        <?php endif; ?>
    </div>
    <p class="text-slate-600 mt-2 max-w-2xl">Stay updated with the latest announcements, approvals, and opportunities.</p>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
        <p class="text-green-800 flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<?php if (empty($notifications)): ?>
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
        <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">notifications_off</span>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No notifications yet</h2>
        <p class="text-slate-500">You'll receive notifications about your applications, approvals, and new opportunities here.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($notifications as $notif): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 <?php echo $notif['status'] === 'Sent' ? 'border-l-4 border-l-blue-500' : ''; ?>">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($notif['title']); ?></h3>
                            <?php if ($notif['status'] === 'Sent'): ?>
                                <span class="inline-flex items-center rounded-full bg-blue-100 text-blue-800 px-2 py-1 text-xs font-semibold">New</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 mb-3"><?php echo htmlspecialchars($notif['message']); ?></p>
                        <div class="flex items-center gap-4 text-sm text-slate-500">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">schedule</span>
                                <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                            </span>
                            <?php if ($notif['sender_name']): ?>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-base">person</span>
                                    <?php echo htmlspecialchars($notif['sender_name']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded text-xs font-semibold">
                                <?php echo htmlspecialchars($notif['type']); ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($notif['status'] === 'Sent'): ?>
                        <form method="POST" class="flex-shrink-0">
                            <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                            <button type="submit" name="mark_read" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                                Mark as Read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>