<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('lydo');

$notification = new Notification($database);
require_once __DIR__ . '/../Classes/OSYProfile.php';
$osyClass = new OSYProfile($database);
$allProfiles = $osyClass->getAll();
$templates = $notification->getAllTemplates();

$message = '';
$messageType = '';

// Handle POST actions first to allow redirects

// Notification broadcasting is handled via AJAX → api/send_notification.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!consumeFormNonce($_POST['form_nonce'] ?? '')) {
        $message = 'Duplicate or invalid form submission detected.';
        $messageType = 'error';
    } else {
        if (isset($_POST['delete_notification'])) {
            if (!$user->isLoggedIn()) {
                header('Location: login.php');
                exit;
            }
            requireRole('lydo');
            $result = $notification->delete($_POST['notification_id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                header('Location: notifications.php?deleted=1');
                exit;
            }
        }
    }
}

// Handle GET alerts
if (isset($_GET['sent'])) {
    $message = 'Notification sent successfully!';
    $messageType = 'success';
} elseif (isset($_GET['deleted'])) {
    $message = 'Notification deleted successfully!';
    $messageType = 'success';
}

require_once __DIR__ . '/../includes/header.php';


if (isset($_GET['sent'])) {
    $message = 'Notification sent successfully!';
    $messageType = 'success';
}
if (isset($_GET['deleted'])) {
    $message = 'Notification deleted successfully!';
    $messageType = 'success';
}

$notifications = $notification->getAll(20);
?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Notifications Management</h2>
        <p class="text-slate-600 dark:text-slate-400">Manage and broadcast notifications to OSY and staff members.</p>
    </div>
    <button onclick="document.getElementById('sendModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined">add_circle</span>
        Send New Notification
    </button>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?> rounded-xl">
        <p class="<?php echo $messageType === 'success' ? 'text-green-800' : 'text-red-800'; ?> flex items-center gap-2">
            <span class="material-symbols-outlined text-base"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
            <?php echo htmlspecialchars($message); ?>
        </p>
    </div>
<?php endif; ?>

<!-- Notifications List Filter -->
<div class="mb-6">
    <div class="relative max-w-md">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input type="text" id="notif_search" oninput="filterNotifications()" placeholder="Search notifications by title or message..." class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl py-3 pl-12 pr-4 text-sm focus:ring-2 focus:ring-blue-900 transition-all text-slate-900 dark:text-white border shadow-sm" />
    </div>
</div>

<!-- Notifications List -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
    <?php if (!empty($notifications)): ?>
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($notifications as $notif): ?>
                <div class="notif-item p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors relative group"
                    data-search="<?php echo strtolower(htmlspecialchars($notif['title'] . ' ' . (str_replace(["\r", "\n"], ' ', $notif['message'])))); ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full 
                            <?php
                            if ($notif['type'] == 'Opportunity') echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                            elseif ($notif['type'] == 'Match') echo 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                            elseif ($notif['type'] == 'Reminder') echo 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                            else echo 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
                            ?>
                        ">
                                    <?php echo htmlspecialchars($notif['type']); ?>
                                </span>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300">
                                    <?php echo htmlspecialchars($notif['status']); ?>
                                </span>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400">
                                    <?php echo htmlspecialchars($notif['recipient_type'] ?? 'All'); ?>
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg"><?php echo htmlspecialchars($notif['title']); ?></h3>
                        </div>
                        <div class="relative">
                            <button onclick="toggleDropdown(<?php echo $notif['id']; ?>)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <div id="dropdown-<?php echo $notif['id']; ?>" class="hidden absolute right-0 top-10 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl py-1 w-44 z-10">
                                <button onclick="viewNotification(<?php echo $notif['id']; ?>)" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    View Full
                                </button>
                                <form method="POST" onsubmit="return confirm('Delete this notification?')">
                                    <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                                    <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                                    <button type="submit" name="delete_notification" value="1" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-300 text-sm mb-4"><?php echo htmlspecialchars($notif['message']); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="p-12 text-center">
            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">notifications_off</span>
            <p class="text-slate-500 font-medium">No notifications yet</p>
            <p class="text-sm text-slate-400 mt-1">Click "Send New Notification" to create one.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Send Notification Modal -->
<div id="sendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Send New Notification</h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="form_nonce" value="<?php echo htmlspecialchars(getFormNonce()); ?>">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Notification Title</label>
                    <input type="text" name="title" required placeholder="Alert title..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-4 pt-2">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Choose a Template (Optional)</label>
                        <select id="templateSelect" class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" onchange="applyTemplate()">
                            <option value="">-- Custom Message --</option>
                            <?php foreach ($templates as $t): ?>
                                <option value="<?php echo htmlspecialchars($t['body']); ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">Message Body</label>
                            <span class="text-xs text-slate-500" title="Auto-replaced per recipient: {{name}}, {{barangay}} | Set below: {{opportunity}}, {{company}}, {{course}}, {{percentage}}">
                                Variables: <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">{{name}}</code>
                                <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">{{barangay}}</code>
                                <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">{{opportunity}}</code> &amp; more
                            </span>
                        </div>
                        <textarea id="customMessageArea" name="message" rows="4" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white font-mono" onkeyup="updateLivePreview()">Hello {{name}}, this is a new update.</textarea>
                    </div>

                    <!-- Template Variable Fill-ins -->
                    <div class="rounded-xl border border-blue-200 dark:border-blue-800 overflow-hidden">
                        <button type="button" onclick="toggleVarPanel()" class="w-full flex items-center justify-between px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-left">
                            <span class="text-xs font-bold text-blue-800 dark:text-blue-400 uppercase tracking-widest">📝 Fill Template Variables</span>
                            <span class="material-symbols-outlined text-blue-500 text-[18px]" id="varPanelIcon">expand_more</span>
                        </button>
                        <div id="varPanel" class="hidden px-4 pb-4 pt-2 bg-blue-50/50 dark:bg-blue-900/10">
                            <p class="text-[11px] text-slate-500 mb-3">These values replace placeholders in your message when sending. <strong>{{name}}</strong> and <strong>{{barangay}}</strong> are auto-filled from each recipient's profile.</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 block">{{opportunity}}</label>
                                    <input type="text" id="tpl_opportunity" placeholder="e.g. Call Center Agent" class="w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white" oninput="updateLivePreview()">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 block">{{company}}</label>
                                    <input type="text" id="tpl_company" placeholder="e.g. ABC Corporation" class="w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white" oninput="updateLivePreview()">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 block">{{course}}</label>
                                    <input type="text" id="tpl_course" placeholder="e.g. NCII Cookery" class="w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white" oninput="updateLivePreview()">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 block">{{percentage}}</label>
                                    <input type="text" id="tpl_percentage" placeholder="e.g. 85" class="w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg py-2 px-3 text-sm text-slate-900 dark:text-white" oninput="updateLivePreview()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Preview Area -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                    <p class="text-xs font-bold text-blue-800 dark:text-blue-400 mb-2 uppercase tracking-wide">Live Preview Example</p>
                    <div id="livePreviewBox" class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Type</label>
                        <select name="type" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
                            <option value="System">System</option>
                            <option value="Opportunity">Opportunity</option>
                            <option value="Match">Match</option>
                            <option value="Reminder">Reminder</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Recipients Target</label>
                        <select name="target_group" id="target_group_select" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" onchange="toggleSpecificRecipients()">
                            <option value="All">All Youth Members</option>
                            <option value="OSY">All OSY Only</option>
                            <option value="Non-OSY">Non-OSY Only</option>
                            <option value="Matched">Matched OSY (High Score)</option>
                            <option value="Unemployed">Unemployed Youth</option>
                            <option value="Employed">Employed Youth</option>
                            <option value="In Training">Youth In Training</option>
                            <option value="Specific">Specific Individuals...</option>
                        </select>
                    </div>
                </div>

                <!-- Specific Recipients Checkboxes -->
                <div id="specific_recipients_container" class="hidden mt-4">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 block">Select Specific Individuals</label>
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-xl p-4 h-48 overflow-y-auto border border-slate-200 dark:border-slate-600 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <?php foreach ($allProfiles as $p): ?>
                            <label class="flex items-center gap-2 cursor-pointer p-2 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-colors">
                                <input type="checkbox" name="specific_ids[]" value="<?php echo $p['id']; ?>" class="w-4 h-4 text-blue-900 border-slate-300 rounded focus:ring-blue-900">
                                <span class="text-sm text-slate-800 dark:text-slate-200 font-medium">
                                    <?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>
                                    <span class="text-xs text-slate-500">(<?php echo htmlspecialchars($p['profile_type']); ?>)</span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Delivery Methods -->
                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">External Delivery Methods</h4>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_sms" class="w-4 h-4 text-blue-900 bg-slate-100 border-slate-300 rounded focus:ring-blue-900">
                            <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">Auto-send via Traccar SMS</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_email" class="w-4 h-4 text-blue-900 bg-slate-100 border-slate-300 rounded focus:ring-blue-900">
                            <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">Auto-send via SMTP Email</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700 mt-6">
                    <button type="button" onclick="document.getElementById('sendModal').classList.add('hidden')" class="px-6 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600">
                        Cancel
                    </button>
                    <button type="button" id="broadcastBtn" onclick="submitNotificationAjax()" class="px-6 py-3 bg-blue-900 text-white rounded-xl font-bold text-sm hover:bg-blue-800 shadow-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">broadcast_on_personal</span>
                        Broadcast Notification
                    </button>
                </div>
            </form>

            <script>
                // Live Preview JS
                var sampleProfile = {
                    name: "<?php echo !empty($allProfiles) ? addslashes($allProfiles[0]['first_name'] . ' ' . $allProfiles[0]['last_name']) : 'Juan Dela Cruz'; ?>",
                    barangay: "<?php echo !empty($allProfiles) ? addslashes($allProfiles[0]['barangay'] ?? 'Barangay 1') : 'Barangay 1'; ?>"
                };

                function getVarValue(id, fallback) {
                    var el = document.getElementById(id);
                    return el && el.value.trim() ? el.value.trim() : fallback;
                }

                function applyTemplate() {
                    const sel = document.getElementById('templateSelect');
                    if (sel.value) {
                        document.getElementById('customMessageArea').value = sel.value;
                        // Auto-open the variable panel if placeholders detected
                        var hasVars = /\{\{(opportunity|company|course|percentage)\}\}/.test(sel.value);
                        if (hasVars) openVarPanel();
                    }
                    updateLivePreview();
                }

                function toggleVarPanel() {
                    var panel = document.getElementById('varPanel');
                    var icon = document.getElementById('varPanelIcon');
                    if (panel.classList.contains('hidden')) {
                        openVarPanel();
                    } else {
                        panel.classList.add('hidden');
                        icon.textContent = 'expand_more';
                    }
                }

                function openVarPanel() {
                    document.getElementById('varPanel').classList.remove('hidden');
                    document.getElementById('varPanelIcon').textContent = 'expand_less';
                }

                function updateLivePreview() {
                    let text = document.getElementById('customMessageArea').value;
                    // Per-recipient vars replaced with sample profile data
                    text = text.replace(/\{\{name\}\}/g, sampleProfile.name);
                    text = text.replace(/\{\{barangay\}\}/g, sampleProfile.barangay);
                    // Broadcast-wide vars replaced with input values
                    text = text.replace(/\{\{opportunity\}\}/g, getVarValue('tpl_opportunity', '[opportunity]'));
                    text = text.replace(/\{\{company\}\}/g, getVarValue('tpl_company', '[company]'));
                    text = text.replace(/\{\{course\}\}/g, getVarValue('tpl_course', '[course]'));
                    text = text.replace(/\{\{percentage\}\}/g, getVarValue('tpl_percentage', '[percentage]'));
                    document.getElementById('livePreviewBox').textContent = text;
                }

                document.addEventListener("DOMContentLoaded", () => {
                    updateLivePreview();
                });

                function toggleSpecificRecipients() {
                    const group = document.getElementById('target_group_select').value;
                    document.getElementById('specific_recipients_container').style.display = (group === 'Specific') ? 'block' : 'none';
                }

                function submitNotificationAjax() {
                    var titleVal = document.querySelector('[name="title"]').value.trim();
                    var msgVal = document.getElementById('customMessageArea').value.trim();
                    var typeVal = document.querySelector('[name="type"]').value;
                    var groupVal = document.getElementById('target_group_select').value;
                    var sendSms = document.querySelector('[name="send_sms"]').checked;
                    var sendEmail = document.querySelector('[name="send_email"]').checked;

                    if (!titleVal || !msgVal) {
                        showNotifToast('Please fill in the title and message.', 'error');
                        return;
                    }

                    var btn = document.getElementById('broadcastBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">progress_activity</span> Sending...';

                    var formData = new FormData();
                    formData.append('title', titleVal);
                    formData.append('message', msgVal);
                    formData.append('type', typeVal);
                    formData.append('target_group', groupVal);
                    if (sendSms) formData.append('send_sms', '1');
                    if (sendEmail) formData.append('send_email', '1');

                    // Broadcast-wide template variables
                    formData.append('tpl_opportunity', document.getElementById('tpl_opportunity')?.value.trim() || '');
                    formData.append('tpl_company', document.getElementById('tpl_company')?.value.trim() || '');
                    formData.append('tpl_course', document.getElementById('tpl_course')?.value.trim() || '');
                    formData.append('tpl_percentage', document.getElementById('tpl_percentage')?.value.trim() || '');

                    // Specific IDs
                    if (groupVal === 'Specific') {
                        document.querySelectorAll('[name="specific_ids[]"]').forEach(cb => {
                            if (cb.checked) formData.append('specific_ids[]', cb.value);
                        });
                    }

                    fetch('../api/send_notification.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                // Close modal
                                document.getElementById('sendModal').classList.add('hidden');

                                // Prepend new notification row to the list
                                var n = data.notification;
                                if (n) {
                                    var typeBadge = {
                                        'Opportunity': 'bg-blue-100 text-blue-700',
                                        'Match': 'bg-green-100 text-green-700',
                                        'Reminder': 'bg-orange-100 text-orange-700',
                                    } [n.type] || 'bg-purple-100 text-purple-700';

                                    var listEl = document.querySelector('.divide-y');
                                    if (!listEl) {
                                        // Replace empty-state with list
                                        var wrapper = document.querySelector('.bg-white.dark\\:bg-slate-800.rounded-xl');
                                        if (wrapper) wrapper.innerHTML = '<div class="divide-y divide-slate-200 dark:divide-slate-700"></div>';
                                        listEl = document.querySelector('.divide-y');
                                    }
                                    if (listEl) {
                                        var row = document.createElement('div');
                                        row.className = 'notif-item p-6 hover:bg-slate-50 transition-colors relative group';
                                        row.setAttribute('data-search', n.title.toLowerCase() + ' ' + n.message.toLowerCase());
                                        row.innerHTML = `
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full ${typeBadge}">${n.type}</span>
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 text-slate-700">${n.status}</span>
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-100 text-cyan-700">${n.recipient_type}</span>
                                                </div>
                                                <h3 class="font-bold text-slate-900 text-lg">${escapeHtml(n.title)}</h3>
                                            </div>
                                        </div>
                                        <p class="text-slate-600 text-sm mb-4">${escapeHtml(n.message)}</p>
                                        <p class="text-xs text-slate-500">Just now</p>`;
                                        listEl.prepend(row);
                                    }
                                }

                                showNotifToast('✅ ' + data.message, 'success');
                            } else {
                                showNotifToast('❌ ' + data.message, 'error');
                            }
                        })
                        .catch(() => showNotifToast('Network error. Please try again.', 'error'))
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<span class="material-symbols-outlined text-base">broadcast_on_personal</span> Broadcast Notification';
                        });
                }

                function escapeHtml(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }

                function showNotifToast(msg, type) {
                    var toast = document.createElement('div');
                    toast.className = 'fixed bottom-6 right-6 z-50 max-w-sm px-5 py-3 rounded-xl shadow-xl text-sm font-semibold ' +
                        (type === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white');
                    toast.textContent = msg;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 400);
                    }, 5000);
                }
            </script>

        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Notification Details</h3>
                <button onclick="document.getElementById('viewModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6">
                <h4 id="viewTitle" class="font-bold text-lg text-slate-900 dark:text-white mb-3"></h4>
                <p id="viewMessage" class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed"></p>
            </div>
        </div>
    </div>
</div>

<script>
    var notificationsData = {};
    <?php foreach ($notifications as $n): ?>
        notificationsData[<?php echo $n['id']; ?>] = <?php echo json_encode($n); ?>;
    <?php endforeach; ?>

    function toggleDropdown(id) {
        // Close all other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
            if (d.id !== 'dropdown-' + id) d.classList.add('hidden');
        });
        document.getElementById('dropdown-' + id).classList.toggle('hidden');
    }

    function viewNotification(id) {
        var n = notificationsData[id];
        if (n) {
            document.getElementById('viewTitle').textContent = n.title;
            document.getElementById('viewMessage').textContent = n.message;
            document.getElementById('viewModal').classList.remove('hidden');
        }
        // Close dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
    }

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="dropdown-"]') && !e.target.closest('button')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
        }
    });

    // Real-time filtering for notifications
    function filterNotifications() {
        const searchVal = document.getElementById('notif_search').value.toLowerCase();
        const items = document.querySelectorAll('.notif-item');

        items.forEach(item => {
            const dataSearch = item.getAttribute('data-search');
            if (searchVal && !dataSearch.includes(searchVal)) {
                item.style.display = 'none';
            } else {
                item.style.display = 'block';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>