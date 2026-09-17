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

$savedGroups = $database->fetchAll("SELECT * FROM notification_groups WHERE created_by = ? ORDER BY name ASC", [$user->getCurrentUserId()], 'i');

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


?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">Notifications Management</h2>
        <p class="text-slate-600 dark:text-slate-400">Manage and broadcast notifications to OSY and staff members.</p>
    </div>
    <button onclick="openSendNotificationModal()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined">add_circle</span>
        Send New Notification
    </button>
    <a href="notification-templates.php" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-800 text-blue-900 dark:text-blue-300 text-sm font-bold hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
        <span class="material-symbols-outlined text-base">description</span> Message Templates
    </a>
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
    <div class="divide-y divide-slate-200 dark:divide-slate-700" id="notificationsList">
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
</div>
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
                <input type="hidden" id="selected_template_id" name="template_id" value="">
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
                                <option value="<?php echo htmlspecialchars($t['body']); ?>" data-template-id="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">Message Body</label>
                        </div>
                        <textarea id="customMessageArea" name="message" rows="4" required class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white font-mono">Hello, this is a new update.</textarea>
                    </div>
                </div>

                <!-- Live Preview Area -->
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
                    <!-- Search input for specific recipients -->
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" id="specific_search" placeholder="Search individuals..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" oninput="filterSpecificRecipients()" />
                    </div>
                    <!-- Group creation UI -->
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" id="new_group_name" placeholder="Group name" class="flex-1 bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" />
                        <button type="button" onclick="saveGroup()" class="px-4 py-2 bg-blue-900 text-white rounded-xl font-medium hover:bg-blue-800">Save Group</button>
                    </div>
                    <!-- Existing groups dropdown -->
                    <div class="flex items-center gap-2 mb-2">
                        <select id="existing_groups" class="flex-1 bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white" onchange="loadGroup()">
                            <option value="">-- Load Saved Group --</option>
                            <?php foreach ($savedGroups as $g): ?>
                                <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="updateGroup()" class="px-3 py-2 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition" title="Update Selected Group">
                            <span class="material-symbols-outlined text-sm block">save</span>
                        </button>
                        <button type="button" onclick="deleteGroup()" class="px-3 py-2 bg-red-600 text-white rounded-xl font-medium hover:bg-red-700 transition" title="Delete Selected Group">
                            <span class="material-symbols-outlined text-sm block">delete</span>
                        </button>
                    </div>
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
                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Delivery Methods</h4>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_in_app" value="1" checked class="w-4 h-4 text-blue-900 bg-slate-100 border-slate-300 rounded focus:ring-blue-900">
                            <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">In-system notification</span>
                        </label>
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
(function(){
                // Live Preview JS
                window.applyTemplate = function() {
                    const sel = document.getElementById('templateSelect');
                    const selected = sel.options[sel.selectedIndex];
                    document.getElementById('selected_template_id').value = selected?.dataset.templateId || '';
                    if (sel.value) {
                        document.getElementById('customMessageArea').value = sel.value;
                    }
                }

                // Open send notification modal with fresh form nonce
                window.openSendNotificationModal = function() {
                    // Refresh form nonce
                    fetch('../api/get_form_nonce.php')
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector('[name="form_nonce"]').value = data.form_nonce;
                            }
                        })
                        .catch(() => {}); // Silently fail if nonce refresh fails

                    // Clear form fields
                    document.querySelector('[name="title"]').value = '';
                    document.getElementById('customMessageArea').value = 'Hello, this is a new update.';
                    document.querySelector('[name="type"]').value = 'System';
                    document.getElementById('target_group_select').value = 'All';
                    document.getElementById('templateSelect').value = '';
                    document.getElementById('selected_template_id').value = '';
                    document.querySelectorAll('[name="specific_ids[]"]').forEach(cb => cb.checked = false);
                    document.querySelector('[name="send_in_app"]').checked = true;
                    document.querySelector('[name="send_sms"]').checked = false;
                    document.querySelector('[name="send_email"]').checked = false;
                    document.getElementById('specific_recipients_container').style.display = 'none';

                    // Show modal
                    document.getElementById('sendModal').classList.remove('hidden');
                }

                document.addEventListener("DOMContentLoaded", () => {});


                window.toggleSpecificRecipients = function() {
                    const group = document.getElementById('target_group_select').value;
                    document.getElementById('specific_recipients_container').style.display = (group === 'Specific') ? 'block' : 'none';
                }

                // Filter specific recipients list based on search input
                window.filterSpecificRecipients = function() {
                    const query = document.getElementById('specific_search').value.toLowerCase();
                    const container = document.getElementById('specific_recipients_container');
                    const labels = container.querySelectorAll('label');
                    labels.forEach(label => {
                        const text = label.textContent.toLowerCase();
                        if (text.includes(query)) {
                            label.style.display = '';
                        } else {
                            label.style.display = 'none';
                        }
                    });
                }

                // Save a new group of selected individuals
                window.saveGroup = function() {
                    const groupName = document.getElementById('new_group_name').value.trim();
                    if (!groupName) {
                        showNotifToast('Please enter a group name.', 'error');
                        return;
                    }
                    // Gather selected IDs
                    const selected = [];
                    document.querySelectorAll('[name="specific_ids[]"]:checked').forEach(cb => selected.push(cb.value));
                    if (selected.length === 0) {
                        showNotifToast('Select at least one individual to save a group.', 'error');
                        return;
                    }
                    const formData = new FormData();
                    formData.append('group_name', groupName);
                    selected.forEach(id => formData.append('profile_ids[]', id));
                    fetch('../api/save_notification_group.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                // Add to dropdown
                                const dropdown = document.getElementById('existing_groups');
                                const option = document.createElement('option');
                                option.value = data.group_id;
                                option.textContent = groupName;
                                dropdown.appendChild(option);
                                showNotifToast('Group saved successfully.', 'success');
                                // Clear input
                                document.getElementById('new_group_name').value = '';
                            } else {
                                showNotifToast(data.message || 'Failed to save group.', 'error');
                            }
                        })
                        .catch(() => showNotifToast('Network error while saving group.', 'error'));
                }

                // Load selected group members and check the corresponding checkboxes
                window.loadGroup = function() {
                    const dropdown = document.getElementById('existing_groups');
                    const groupId = dropdown.value;
                    if (!groupId) return;

                    // Auto-fill the new group name input to allow easy renaming/updating
                    const selectedOption = dropdown.options[dropdown.selectedIndex];
                    document.getElementById('new_group_name').value = selectedOption.text;

                    fetch('../api/get_notification_group_members.php?group_id=' + encodeURIComponent(groupId))
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && Array.isArray(data.profile_ids)) {
                                // Uncheck all first
                                document.querySelectorAll('[name="specific_ids[]"]').forEach(cb => cb.checked = false);
                                // Check the ones in the group
                                data.profile_ids.forEach(id => {
                                    const cb = document.querySelector('[name="specific_ids[]"][value="' + id + '"]');
                                    if (cb) cb.checked = true;
                                });
                            } else {
                                showNotifToast(data.message || 'Failed to load group.', 'error');
                            }
                        })
                        .catch(() => showNotifToast('Network error while loading group.', 'error'));
                }

                window.updateGroup = function() {
                    const dropdown = document.getElementById('existing_groups');
                    const groupId = dropdown.value;
                    if (!groupId) {
                        showNotifToast('Please load a group first to update it.', 'error');
                        return;
                    }

                    const groupName = document.getElementById('new_group_name').value.trim();
                    if (!groupName) {
                        showNotifToast('Group name cannot be empty.', 'error');
                        return;
                    }

                    const selected = [];
                    document.querySelectorAll('[name="specific_ids[]"]:checked').forEach(cb => selected.push(cb.value));
                    if (selected.length === 0) {
                        showNotifToast('Select at least one individual.', 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('group_id', groupId);
                    formData.append('group_name', groupName);
                    selected.forEach(id => formData.append('profile_ids[]', id));

                    fetch('../api/update_notification_group.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                dropdown.options[dropdown.selectedIndex].text = groupName;
                                showNotifToast('Group updated successfully.', 'success');
                            } else {
                                showNotifToast(data.message || 'Failed to update group.', 'error');
                            }
                        })
                        .catch(() => showNotifToast('Network error while updating group.', 'error'));
                }

                window.deleteGroup = function() {
                    const dropdown = document.getElementById('existing_groups');
                    if (!dropdown.value) {
                        showNotifToast('Please select a group to delete.', 'error');
                        return;
                    }
                    // Show the custom delete modal instead of native confirm()
                    document.getElementById('deleteGroupModal').classList.remove('hidden');
                }

                window.executeDeleteGroup = function() {
                    const dropdown = document.getElementById('existing_groups');
                    const groupId = dropdown.value;

                    // Hide the modal immediately
                    document.getElementById('deleteGroupModal').classList.add('hidden');

                    if (!groupId) return;

                    const formData = new FormData();
                    formData.append('group_id', groupId);

                    fetch('../api/delete_notification_group.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                dropdown.remove(dropdown.selectedIndex);
                                dropdown.value = '';
                                document.getElementById('new_group_name').value = '';
                                document.querySelectorAll('[name="specific_ids[]"]').forEach(cb => cb.checked = false);
                                showNotifToast('Group deleted successfully.', 'success');
                            } else {
                                showNotifToast(data.message || 'Failed to delete group.', 'error');
                            }
                        })
                        .catch(() => showNotifToast('Network error while deleting group.', 'error'));
                }

                window.submitNotificationAjax = function() {
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
                    formData.append('template_id', document.getElementById('selected_template_id')?.value || '');
                    formData.append('form_nonce', document.querySelector('[name="form_nonce"]')?.value || '');
                    if (sendSms) formData.append('send_sms', '1');
                    if (sendEmail) formData.append('send_email', '1');
                    if (document.querySelector('[name="send_in_app"]')?.checked) formData.append('send_in_app', '1');


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
                                // Refresh form nonce for next submission
                                fetch('../api/get_form_nonce.php')
                                    .then(r => r.json())
                                    .then(nonceData => {
                                        if (nonceData.success) {
                                            document.querySelector('[name="form_nonce"]').value = nonceData.form_nonce;
                                        }
                                    })
                                    .catch(() => {}); // Silently fail if nonce refresh fails

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

                window.escapeHtml = function(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }

                window.showNotifToast = function(msg, type) {
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
            })();
</script>

        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteGroupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white text-center mb-2">Delete Group</h3>
                        <p class="text-slate-600 dark:text-slate-300 text-center text-sm mb-6">Are you sure? This action cannot be undone.</p>
                        <div class="flex gap-3">
                            <button type="button" onclick="document.getElementById('deleteGroupModal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg font-bold text-sm hover:bg-slate-200">
                                Cancel
                            </button>
                            <button type="button" onclick="executeDeleteGroup()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-sm hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
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
(function(){
            

            window.toggleDropdown = function(id) {
                // Close all other dropdowns
                document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                    if (d.id !== 'dropdown-' + id) d.classList.add('hidden');
                });
                document.getElementById('dropdown-' + id).classList.toggle('hidden');
            }

            window.viewNotification = function(id) {
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
            window.filterNotifications = function() {
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

        
<script>
(function() {
    function loadNotifications() {
        fetch(`../api/get_notifications_data.php?view=sent`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById('notificationsList');
                if (!res.success || !res.notifications || res.notifications.length === 0) {
                    list.innerHTML = `<div class="p-12 text-center"><span class="material-symbols-outlined text-4xl text-slate-300 mb-2">notifications_off</span><p class="text-slate-500 font-medium">No notifications yet</p><p class="text-sm text-slate-400 mt-1">Click "Send New Notification" to create one.</p></div>`;
                    return;
                }
                
                const nonce = document.querySelector('input[name="form_nonce"]')?.value || '<?php echo htmlspecialchars(getFormNonce()); ?>';
                window.notificationsData = {};
                
                list.innerHTML = res.notifications.map(notif => {
                    window.notificationsData[notif.id] = notif;
                    
                    let typeBadge = 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
                    if (notif.type === 'Opportunity') typeBadge = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                    else if (notif.type === 'Match') typeBadge = 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                    else if (notif.type === 'Reminder') typeBadge = 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
                    
                    const searchData = `${notif.title} ${notif.message.replace(/[\r\n]+/g, ' ')}`.toLowerCase().replace(/"/g, '&quot;');
                    
                    const dt = new Date(notif.created_at);
                    const formattedDate = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                    
                    return `
                    <div class="notif-item p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors relative group" data-search="${searchData}">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full ${typeBadge}">${notif.type}</span>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300">${notif.status}</span>
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400">${notif.recipient_type || 'All'}</span>
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
                                    <form method="POST" onsubmit="return confirm('Delete this notification?')">
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
                }).join('');
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>