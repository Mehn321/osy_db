<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../Classes/Messages.php';
$messagesObj = new Messages($database);

$current_osy_id = isset($_GET['chat']) ? intval($_GET['chat']) : 0;

// No longer processing delivery alerts via URL params (switched to AJAX)
$messageResult = '';
$messageResultType = 'info';

// Pre-fetch chat list
$chatList = $messagesObj->getChatList();
$current_chat_user = null;
if ($current_osy_id > 0) {
    foreach ($chatList as $chat) {
        if ($chat['id'] == $current_osy_id) {
            $current_chat_user = $chat;
            break;
        }
    }
}

// Message sending is now handled via AJAX → api/send_message.php

// Mark as read & load conversation
if ($current_osy_id > 0) {
    $messagesObj->markAsRead($current_osy_id);
}
$conversation = $current_osy_id > 0 ? $messagesObj->getConversation($current_osy_id) : [];

// Check if SMS/Email is configured (to show warnings)
$settings = [];
$raw = $database->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('traccar_token','gmail_user','gmail_app_password')");
foreach ($raw as $s) $settings[$s['setting_key']] = $s['setting_value'];
$smsConfigured   = !empty($settings['traccar_token']);
$emailConfigured = !empty($settings['gmail_user']) && !empty($settings['gmail_app_password']);
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<?php if ($messageResult): ?>
<div id="msgAlertBanner" class="mb-4 p-3 <?php echo $messageResultType === 'error' ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-green-50 border border-green-200 text-green-800'; ?> rounded-xl flex items-center gap-3">
    <span class="material-symbols-outlined text-lg shrink-0"><?php echo $messageResultType === 'error' ? 'error' : 'check_circle'; ?></span>
    <p class="text-sm font-medium flex-1"><?php echo $messageResult; ?></p>
    <button onclick="document.getElementById('msgAlertBanner').remove()" class="text-current opacity-60 hover:opacity-100"><span class="material-symbols-outlined text-lg">close</span></button>
</div>
<?php endif; ?>

<div class="flex h-[calc(100vh-210px)] bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">

    <!-- ─── Chat List Sidebar ────────────────────────────────────── -->
    <div class="w-80 flex-shrink-0 border-r border-slate-200 dark:border-slate-700 flex flex-col bg-slate-50 dark:bg-slate-800/50">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Messages</h2>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                <input type="text" id="chatSearch" oninput="filterChats(this.value)" placeholder="Search conversations..." class="w-full bg-slate-100 dark:bg-slate-700 border-none rounded-xl py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-blue-900 text-slate-900 dark:text-white">
            </div>
        </div>
        <div id="chatListContainer" class="flex-1 overflow-y-auto">
            <?php if (!empty($chatList)): ?>
                <?php foreach ($chatList as $chat): ?>
                    <a href="messages.php?chat=<?php echo $chat['id']; ?>" data-name="<?php echo strtolower($chat['first_name'] . ' ' . $chat['last_name']); ?>"
                       class="chat-item block p-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors <?php echo $current_osy_id == $chat['id'] ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-900' : ''; ?>">
                        <div class="flex items-start gap-3">
                            <div class="relative flex-shrink-0">
                                <?php if (!empty($chat['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($chat['image_path']); ?>" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                                        <?php echo strtoupper(substr($chat['first_name'], 0, 1) . substr($chat['last_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (($chat['unread_count'] ?? 0) > 0): ?>
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[9px] flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800"><?php echo $chat['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate"><?php echo htmlspecialchars($chat['first_name'] . ' ' . $chat['last_name']); ?></h3>
                                    <?php if (!empty($chat['last_message_time'])): ?>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap ml-1"><?php echo date('M d', strtotime($chat['last_message_time'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5"><?php echo htmlspecialchars($chat['last_message'] ?? 'Click to start chatting'); ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-8 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">chat_bubble_outline</span>
                    <p class="text-sm">No profiles found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ─── Chat Area ────────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0">
        <?php if ($current_osy_id > 0 && $current_chat_user): ?>

            <!-- Chat Header -->
            <div class="flex-shrink-0 p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-white dark:bg-slate-800">
                <div class="flex items-center gap-3">
                    <?php if (!empty($current_chat_user['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($current_chat_user['image_path']); ?>" class="w-10 h-10 rounded-full object-cover border-2 border-blue-100">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                            <?php echo strtoupper(substr($current_chat_user['first_name'], 0, 1) . substr($current_chat_user['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white leading-tight"><?php echo htmlspecialchars($current_chat_user['first_name'] . ' ' . $current_chat_user['last_name']); ?></h3>
                        <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                            <?php echo htmlspecialchars($current_chat_user['phone'] ?? 'No phone'); ?> &nbsp;|&nbsp; <?php echo htmlspecialchars($current_chat_user['email'] ?? 'No email'); ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($current_chat_user['phone'])): ?>
                        <a href="sms:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $current_chat_user['phone'])); ?>" class="px-3 py-1.5 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">sms</span>SMS App
                        </a>
                        <a href="https://messages.google.com/web/conversations/new?to=<?php echo urlencode(preg_replace('/[^0-9+]/', '', $current_chat_user['phone'])); ?>" target="_blank" class="px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">open_in_new</span>Google Web
                        </a>
                    <?php endif; ?>
                    <a href="profile-detail.php?id=<?php echo $current_osy_id; ?>" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg text-xs font-bold transition-colors">View Profile</a>
                </div>
            </div>

            <!-- Messages List (scrollable) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatContainer" style="scroll-behavior: smooth;">
                <?php if (empty($conversation)): ?>
                    <div class="h-full flex flex-col items-center justify-center text-slate-400 pt-20">
                        <span class="material-symbols-outlined text-5xl mb-3 opacity-20">forum</span>
                        <p class="text-sm font-medium">No messages yet</p>
                        <p class="text-xs text-slate-400 mt-1">Send a message below to start the conversation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversation as $msg): ?>
                        <?php $isAdmin = $msg['sender_type'] === 'admin'; ?>
                        <div class="flex <?php echo $isAdmin ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-[72%]">
                                <div class="<?php echo $isAdmin
                                    ? 'bg-blue-900 text-white rounded-2xl rounded-tr-sm'
                                    : 'bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white rounded-2xl rounded-tl-sm border border-slate-200 dark:border-slate-600';
                                ?> px-4 py-3 shadow-sm">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap"><?php echo htmlspecialchars($msg['message']); ?></p>
                                </div>
                                <div class="flex items-center gap-1.5 mt-1 px-1 <?php echo $isAdmin ? 'justify-end' : ''; ?>">
                                    <?php if ($isAdmin && (!empty($msg['sms_status']) && $msg['sms_status'] !== 'none')): ?>
                                        <span class="material-symbols-outlined text-[11px] <?php echo $msg['sms_status'] === 'success' ? 'text-green-500' : 'text-red-400'; ?>" 
                                              title="SMS: <?php echo $msg['sms_status'] === 'success' ? 'Delivered' : htmlspecialchars($msg['sms_error'] ?? 'Failed'); ?>">chat</span>
                                    <?php endif; ?>
                                    <?php if ($isAdmin && (!empty($msg['email_status']) && $msg['email_status'] !== 'none')): ?>
                                        <span class="material-symbols-outlined text-[11px] <?php echo $msg['email_status'] === 'success' ? 'text-green-500' : 'text-red-400'; ?>"
                                              title="Email: <?php echo $msg['email_status'] === 'success' ? 'Sent' : htmlspecialchars($msg['email_error'] ?? 'Failed'); ?>">mail</span>
                                    <?php endif; ?>
                                    <span class="text-[10px] text-slate-400"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                                    <?php if ($isAdmin): ?>
                                        <span class="material-symbols-outlined text-[11px] text-slate-400"><?php echo $msg['is_read'] ? 'done_all' : 'check'; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Message Input -->
            <div class="flex-shrink-0 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                <!-- Notification config warnings -->
                <?php if (!$smsConfigured || !$emailConfigured): ?>
                <div class="px-4 pt-2 flex gap-2 flex-wrap">
                    <?php if (!$smsConfigured): ?>
                    <a href="settings.php?tab=notifications" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-[11px]">warning</span>SMS not configured – <u>Set up in Settings</u>
                    </a>
                    <?php endif; ?>
                    <?php if (!$emailConfigured): ?>
                    <a href="settings.php?tab=notifications" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-1 rounded-full hover:bg-amber-100 transition-colors">
                        <span class="material-symbols-outlined text-[11px]">warning</span>Email not configured – <u>Set up in Settings</u>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <form id="messageForm" class="p-4" onsubmit="sendMessageAjax(event)">
                    <div class="flex items-end gap-3">
                        <!-- Textarea grows up -->
                        <div class="flex-1">
                            <textarea id="messageInput" rows="1"
                                placeholder="Type a message to <?php echo htmlspecialchars($current_chat_user['first_name']); ?>..."
                                class="w-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-2xl py-3 px-4 text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white dark:focus:bg-slate-600 text-slate-900 dark:text-white resize-none transition-all"
                                style="min-height:46px;max-height:120px;overflow-y:auto;"></textarea>
                            <!-- Notification checkboxes below textarea -->
                            <div class="flex items-center gap-4 mt-2 px-1">
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" id="chkSms" class="rounded border-slate-300 text-blue-900 focus:ring-blue-900 w-3.5 h-3.5" <?php echo !$smsConfigured ? 'disabled title="Configure Traccar SMS in Settings"' : ''; ?>>
                                    <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1 <?php echo !$smsConfigured ? 'opacity-40' : ''; ?>">
                                        <span class="material-symbols-outlined text-[13px]">sms</span>Also send SMS
                                    </span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" id="chkEmail" class="rounded border-slate-300 text-blue-900 focus:ring-blue-900 w-3.5 h-3.5" <?php echo !$emailConfigured ? 'disabled title="Configure Gmail SMTP in Settings"' : ''; ?>>
                                    <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1 <?php echo !$emailConfigured ? 'opacity-40' : ''; ?>">
                                        <span class="material-symbols-outlined text-[13px]">email</span>Also send Email
                                    </span>
                                </label>
                            </div>
                        </div>
                        <!-- Send Button -->
                        <button type="submit" id="sendBtn"
                            class="w-12 h-12 rounded-2xl bg-blue-900 flex items-center justify-center text-white hover:bg-blue-800 active:scale-95 transition-all shadow-lg flex-shrink-0 self-start mt-0">
                            <span class="material-symbols-outlined text-[22px]" style="margin-left:2px">send</span>
                        </button>
                    </div>
                </form>
            </div>

            <script>
                var currentOsyId = <?php echo $current_osy_id; ?>;

                // Auto-scroll to bottom
                (function() {
                    var c = document.getElementById('chatContainer');
                    if (c) c.scrollTop = c.scrollHeight;
                })();

                // Auto-resize textarea
                var ta = document.getElementById('messageInput');
                if (ta) {
                    ta.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                    });
                    ta.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                            e.preventDefault();
                            document.getElementById('messageForm').dispatchEvent(new Event('submit'));
                        }
                    });
                }

                function sendMessageAjax(e) {
                    e.preventDefault();
                    var text = document.getElementById('messageInput').value.trim();
                    if (!text) { document.getElementById('messageInput').focus(); return; }

                    var btn = document.getElementById('sendBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>';

                    var formData = new FormData();
                    formData.append('osy_id', currentOsyId);
                    formData.append('message_content', text);
                    if (document.getElementById('chkSms').checked)   formData.append('trigger_sms', '1');
                    if (document.getElementById('chkEmail').checked) formData.append('trigger_email', '1');

                    fetch('../api/send_message.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                // Append message bubble instantly
                                var container = document.getElementById('chatContainer');
                                var smsIcon   = (data.sms_status && data.sms_status !== 'none')
                                    ? `<span class="material-symbols-outlined text-[11px] ${data.sms_status === 'success' ? 'text-green-500' : 'text-red-400'}" title="SMS: ${data.sms_status}">chat</span>` : '';
                                var emailIcon = (data.email_status && data.email_status !== 'none')
                                    ? `<span class="material-symbols-outlined text-[11px] ${data.email_status === 'success' ? 'text-green-500' : 'text-red-400'}" title="Email: ${data.email_status}">mail</span>` : '';

                                var bubble = document.createElement('div');
                                bubble.className = 'flex justify-end';
                                bubble.innerHTML = `
                                    <div class="max-w-[72%]">
                                        <div class="bg-blue-900 text-white rounded-2xl rounded-tr-sm px-4 py-3 shadow-sm">
                                            <p class="text-sm leading-relaxed whitespace-pre-wrap">${escapeHtml(data.message)}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1 px-1 justify-end">
                                            ${smsIcon}${emailIcon}
                                            <span class="text-[10px] text-slate-400">${data.time}</span>
                                            <span class="material-symbols-outlined text-[11px] text-slate-400">check</span>
                                        </div>
                                    </div>`;

                                // Remove empty state if present
                                var emptyState = container.querySelector('.flex.flex-col.items-center');
                                if (emptyState) emptyState.remove();

                                container.appendChild(bubble);
                                container.scrollTop = container.scrollHeight;

                                // Clear input
                                document.getElementById('messageInput').value = '';
                                document.getElementById('messageInput').style.height = 'auto';

                                // Show delivery warnings if any
                                if (data.warnings && data.warnings.length > 0) {
                                    showMsgToast(data.warnings.join(' | '), 'error');
                                }
                            } else {
                                showMsgToast('Error: ' + data.message, 'error');
                            }
                        })
                        .catch(() => showMsgToast('Network error. Please try again.', 'error'))
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<span class="material-symbols-outlined text-[22px]" style="margin-left:2px">send</span>';
                        });
                }

                function escapeHtml(str) {
                    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                }

                function showMsgToast(msg, type) {
                    var toast = document.createElement('div');
                    toast.className = 'fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-sm font-semibold flex items-center gap-2 transition-all '
                        + (type === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white');
                    toast.innerHTML = '<span class="material-symbols-outlined text-base">' + (type === 'error' ? 'error' : 'check_circle') + '</span>' + msg;
                    document.body.appendChild(toast);
                    setTimeout(() => { toast.style.opacity='0'; setTimeout(() => toast.remove(), 400); }, 4000);
                }
            </script>

        <?php else: ?>
            <!-- No chat selected -->
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 bg-slate-50/40 dark:bg-slate-800/20">
                <div class="w-24 h-24 bg-white dark:bg-slate-700 rounded-full flex items-center justify-center shadow-sm mb-6 border border-slate-100 dark:border-slate-600">
                    <span class="material-symbols-outlined text-5xl text-blue-900 dark:text-blue-400">forum</span>
                </div>
                <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300">Select a conversation</h3>
                <p class="text-sm mt-2 max-w-xs text-center leading-relaxed text-slate-500">Choose a youth profile from the list on the left to view messages or start a new conversation.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterChats(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.chat-item').forEach(function(el) {
        var name = el.getAttribute('data-name') || '';
        el.style.display = name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
