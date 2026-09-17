<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/notifications.php');

// Remove synchronous fetch of notifications and templates
// The templates are also used in the modal to populate <select>, but we can keep that since we need it for the form, or we can fetch them via AJAX too. 
// Wait, the prompt said: 
// "notification-templates.php (LYDO — templates)
//   Response: { success: true, templates: [{ id, title, message, ... }] }"
// The templates list is fetched at the top: $templates = $notification->getAllTemplates();

$c = preg_replace('/\$notifications\s*=\s*\$notification->getAll\(20\);/s', '', $c);

// Replace empty state check and PHP foreach with skeletons
$skeletons = '<div class="divide-y divide-slate-200 dark:divide-slate-700" id="notificationsList">
    <?php for ($i=0; $i<4; $i++): ?>
        <div class="p-6 relative">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="skeleton-pulse h-5 w-20 rounded-full"></div>
                        <div class="skeleton-pulse h-5 w-20 rounded-full"></div>
                        <div class="skeleton-pulse h-5 w-20 rounded-full"></div>
                    </div>
                    <div class="skeleton-pulse h-6 w-48 rounded mb-2"></div>
                </div>
            </div>
            <div class="skeleton-pulse h-4 w-full rounded mb-1"></div>
            <div class="skeleton-pulse h-4 w-3/4 rounded mb-4"></div>
            <div class="skeleton-pulse h-3 w-32 rounded"></div>
        </div>
    <?php endfor; ?>
</div>';

$c = preg_replace('/<\?php if \(!empty\(\$notifications\)\): \?>.*?<\?php endif; \?>/s', $skeletons, $c);

// Inject JS and CSS
$js = '
<script>
(function() {
    function loadNotifications() {
        fetch(`../api/get_notifications_data.php?view=sent`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById(\'notificationsList\');
                if (!res.success || !res.notifications || res.notifications.length === 0) {
                    list.innerHTML = `<div class="p-12 text-center"><span class="material-symbols-outlined text-4xl text-slate-300 mb-2">notifications_off</span><p class="text-slate-500 font-medium">No notifications yet</p><p class="text-sm text-slate-400 mt-1">Click "Send New Notification" to create one.</p></div>`;
                    return;
                }
                
                const nonce = document.querySelector(\'input[name="form_nonce"]\')?.value || \'<?php echo htmlspecialchars(getFormNonce()); ?>\';
                window.notificationsData = {};
                
                list.innerHTML = res.notifications.map(notif => {
                    window.notificationsData[notif.id] = notif;
                    
                    let typeBadge = \'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400\';
                    if (notif.type === \'Opportunity\') typeBadge = \'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400\';
                    else if (notif.type === \'Match\') typeBadge = \'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400\';
                    else if (notif.type === \'Reminder\') typeBadge = \'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400\';
                    
                    const searchData = `${notif.title} ${notif.message.replace(/[\\r\\n]+/g, \' \')}`.toLowerCase().replace(/"/g, \'&quot;\');
                    
                    const dt = new Date(notif.created_at);
                    const formattedDate = dt.toLocaleDateString(\'en-US\', { month: \'short\', day: \'numeric\', year: \'numeric\' }) + \' \' + dt.toLocaleTimeString(\'en-US\', { hour: \'2-digit\', minute: \'2-digit\', hour12: false });
                    
                    return `
                    <div class="notif-item p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors relative group" data-search="${searchData}">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full ${typeBadge}">${notif.type}</span>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300">${notif.status}</span>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400">${notif.recipient_type || \'All\'}</span>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-lg">${escapeHtml(notif.title)}</h3>
                            </div>
                            <div class="relative">
                                <button onclick="toggleDropdown(${notif.id})" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                    <span class="material-symbols-outlined">more_vert</span>
                                </button>
                                <div id="dropdown-${notif.id}" class="hidden absolute right-0 top-10 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl py-1 w-44 z-10">
                                    <button onclick="viewNotification(${notif.id})" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                        View Full
                                    </button>
                                    <form method="POST" onsubmit="return confirm(\'Delete this notification?\')">
                                        <input type="hidden" name="notification_id" value="${notif.id}">
                                        <input type="hidden" name="form_nonce" value="${nonce}">
                                        <button type="submit" name="delete_notification" value="1" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-4">${escapeHtml(notif.message)}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">${formattedDate}</p>
                    </div>`;
                }).join(\'\');
            });
    }
    
    loadNotifications();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

// Remove old PHP initialization of notificationsData
$c = preg_replace('/var notificationsData = \{\};\s*<\?php foreach \(\$notifications as \$n\): \?>\s*notificationsData\[<\?php echo \$n\[\'id\'\]; \?>\] = <\?php echo json_encode\(\$n\); \?>;\s*<\?php endforeach; \?>/s', '', $c);

// Avoid duplicate declarations of toggleDropdown and viewNotification and filterNotifications
$c = preg_replace('/function toggleDropdown/', 'window.toggleDropdown = function', $c);
$c = preg_replace('/function viewNotification/', 'window.viewNotification = function', $c);
$c = preg_replace('/function filterNotifications/', 'window.filterNotifications = function', $c);
$c = preg_replace('/function escapeHtml/', 'window.escapeHtml = function', $c);
$c = preg_replace('/function showNotifToast/', 'window.showNotifToast = function', $c);
$c = preg_replace('/function applyTemplate/', 'window.applyTemplate = function', $c);
$c = preg_replace('/function openSendNotificationModal/', 'window.openSendNotificationModal = function', $c);
$c = preg_replace('/function toggleSpecificRecipients/', 'window.toggleSpecificRecipients = function', $c);
$c = preg_replace('/function filterSpecificRecipients/', 'window.filterSpecificRecipients = function', $c);
$c = preg_replace('/function saveGroup/', 'window.saveGroup = function', $c);
$c = preg_replace('/function loadGroup/', 'window.loadGroup = function', $c);
$c = preg_replace('/function updateGroup/', 'window.updateGroup = function', $c);
$c = preg_replace('/function deleteGroup/', 'window.deleteGroup = function', $c);
$c = preg_replace('/function executeDeleteGroup/', 'window.executeDeleteGroup = function', $c);
$c = preg_replace('/function submitNotificationAjax/', 'window.submitNotificationAjax = function', $c);

// Put the scripts in IIFE where necessary or just let them run
$c = str_replace('<script>', "<script>\n(function(){", $c);
$c = str_replace('</script>', "})();\n</script>", $c);
// The last script tags:
$c = str_replace("})();\n</script>\n\n        <?php require_once", "</script>\n\n        <?php require_once", $c);

$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/notifications.php', $c);
echo "Done notifications.php\n";
