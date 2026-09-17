<?php
$pageTitle = 'My Notifications';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

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

$unreadCount = 0;
?>

<div class="mb-10">
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-600 tracking-wider uppercase mb-4">
        <span>Main</span>
        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
        <span class="text-blue-900 font-bold">My Notifications</span>
    </nav>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Your Notifications</h1>
        <div id="unread-badge-container">
            <?php if ($unreadCount > 0): ?>
                <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-sm font-semibold">
                    <span class="material-symbols-outlined text-base">notifications_active</span>
                    <?php echo $unreadCount; ?> unread
                </span>
            <?php endif; ?>
        </div>
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

<div id="notificationsList" class="space-y-4">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="notification-card bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="skeleton-pulse h-6 w-48 rounded"></div>
                    </div>
                    <div class="skeleton-pulse h-4 w-full rounded mb-1"></div>
                    <div class="skeleton-pulse h-4 w-3/4 rounded mb-3"></div>
                    <div class="flex items-center gap-4">
                        <div class="skeleton-pulse h-4 w-24 rounded"></div>
                        <div class="skeleton-pulse h-4 w-20 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>
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
                        <form method="POST" class="mark-read-form flex-shrink-0">
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

<script>
(function() {
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.mark-read-form');
        if (!form) return;
        
        e.preventDefault();
        
        const notificationId = form.querySelector('input[name="notification_id"]')?.value;
        if (!notificationId) return;
        
        const btn = form.querySelector('button[name="mark_read"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Marking...';
        }
        
        fetch('../api/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = form.closest('.notification-card');
                if (card) {
                    card.classList.remove('border-l-4', 'border-l-blue-500');
                    card.querySelector('.new-badge')?.remove();
                }
                form.remove();
                
                // Update notification badges in top bar
                const badgeTop = document.querySelector('a[href*="my-notifications.php"] span.absolute');
                const badgePage = document.getElementById('unread-badge-container');
                
                const count = data.unread_count;
                if (count > 0) {
                    if (badgeTop) {
                        badgeTop.textContent = count > 99 ? '99+' : count;
                    }
                    if (badgePage) {
                        badgePage.innerHTML = `
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-sm font-semibold">
                                <span class="material-symbols-outlined text-base">notifications_active</span>
                                ${count} unread
                            </span>
                        `;
                    }
                } else {
                    if (badgeTop) badgeTop.remove();
                    if (badgePage) badgePage.innerHTML = '';
                }
            } else {
                customAlert(data.message || 'Failed to mark notification as read', "Error", "error");
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Mark as Read';
                }
            }
        })
        .catch(err => {
            console.error(err);
            customAlert('Network error occurred.', "Error", "error");
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Mark as Read';
            }
        });
    });
})();
</script>


<script>
(function() {
    function loadMyNotifications() {
        fetch(`../api/get_notifications_data.php?view=inbox`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById('notificationsList');
                
                // Update unread count
                const count = res.unread_count || 0;
                const badgePage = document.getElementById('unread-badge-container');
                const badgeTop = document.querySelector('a[href*="my-notifications.php"] span.absolute');
                
                if (count > 0) {
                    if (badgePage) {
                        badgePage.innerHTML = `<span class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-sm font-semibold"><span class="material-symbols-outlined text-base">notifications_active</span>${count} unread</span>`;
                    }
                    if (badgeTop) {
                        badgeTop.textContent = count > 99 ? '99+' : count;
                    }
                } else {
                    if (badgePage) badgePage.innerHTML = '';
                    if (badgeTop) badgeTop.remove();
                }
                
                if (!res.success || !res.notifications || res.notifications.length === 0) {
                    list.innerHTML = `<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center"><span class="material-symbols-outlined text-6xl text-slate-300 mb-4">notifications_off</span><h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No notifications yet</h2><p class="text-slate-500">You'll receive notifications about your applications, approvals, and new opportunities here.</p></div>`;
                    return;
                }
                
                list.innerHTML = res.notifications.map(notif => {
                    const isNew = notif.status === 'Sent';
                    const borderClass = isNew ? 'border-l-4 border-l-blue-500' : '';
                    const badgeHtml = isNew ? `<span class="new-badge inline-flex items-center rounded-full bg-blue-100 text-blue-800 px-2 py-1 text-xs font-semibold">New</span>` : '';
                    
                    const dt = new Date(notif.created_at);
                    const formattedDate = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                    
                    const senderHtml = notif.sender_name ? `<span class="flex items-center gap-1"><span class="material-symbols-outlined text-base">person</span>${escapeHtml(notif.sender_name)}</span>` : '';
                    
                    const btnHtml = isNew ? `
                        <form method="POST" class="mark-read-form flex-shrink-0">
                            <input type="hidden" name="notification_id" value="${notif.id}">
                            <button type="submit" name="mark_read" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                                Mark as Read
                            </button>
                        </form>
                    ` : '';
                    
                    return `
                    <div class="notification-card bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 ${borderClass}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">${escapeHtml(notif.title)}</h3>
                                    ${badgeHtml}
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 mb-3">${escapeHtml(notif.message)}</p>
                                <div class="flex items-center gap-4 text-sm text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-base">schedule</span>
                                        ${formattedDate}
                                    </span>
                                    ${senderHtml}
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded text-xs font-semibold">
                                        ${escapeHtml(notif.type)}
                                    </span>
                                </div>
                            </div>
                            ${btnHtml}
                        </div>
                    </div>`;
                }).join('');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    loadMyNotifications();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>