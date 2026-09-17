<?php
$c = file_get_contents('c:/xampp/htdocs/osy_db/pages/messages.php');

// Remove chat list synchronous call
$c = str_replace('$chatList = $messagesObj->getChatList();', '$chatList = [];', $c);

// We still need current_chat_user for the header and conversation loading if ?chat=123 is present
// Instead of getting it from chatList, we can query it directly
$replacement = <<<PHP
if (\$current_osy_id > 0) {
    \$current_chat_user = \$database->fetch("SELECT p.id, p.first_name, p.last_name, p.phone, p.email, p.image_path FROM osy_profiles p WHERE p.id = ?", [\$current_osy_id], 'i');
}
PHP;
$c = preg_replace('/if \(\$current_osy_id > 0\) \{.*?foreach \(\$chatList as \$chat\).*?break;\s*\}\s*\}/s', $replacement, $c);

// Add skeleton for the chat list sidebar
$skeletons = '<div id="chatListContainer" class="flex-1 overflow-y-auto">
    <?php for ($i=0; $i<6; $i++): ?>
        <div class="block p-4 border-b border-slate-100 dark:border-slate-700/50">
            <div class="flex items-start gap-3">
                <div class="relative flex-shrink-0">
                    <div class="skeleton-pulse w-10 h-10 rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-baseline mb-1">
                        <div class="skeleton-pulse h-4 w-32 rounded"></div>
                        <div class="skeleton-pulse h-3 w-10 rounded"></div>
                    </div>
                    <div class="skeleton-pulse h-3 w-full rounded"></div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>';

$c = preg_replace('/<div id="chatListContainer".*?<\/div>\s*<\/div>/s', $skeletons . "\n    </div>", $c);

// JS logic
$js = '
<script>
(function() {
    function loadChatList() {
        const currentChatId = <?php echo $current_osy_id; ?>;
        fetch(`../api/get_messages_data.php?view=chat_list`)
            .then(r => r.json())
            .then(res => {
                const container = document.getElementById(\'chatListContainer\');
                if (!res.success || !res.chats || res.chats.length === 0) {
                    container.innerHTML = `<div class="p-8 text-center text-slate-400"><span class="material-symbols-outlined text-4xl block mb-2 opacity-30">chat_bubble_outline</span><p class="text-sm">No profiles found.</p></div>`;
                    return;
                }
                
                container.innerHTML = res.chats.map(chat => {
                    const searchName = `${chat.first_name} ${chat.last_name}`.toLowerCase();
                    const activeClass = currentChatId == chat.id ? \'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-900\' : \'\';
                    
                    let imageHtml = \'\';
                    if (chat.image_path) {
                        imageHtml = `<img src="${escapeHtml(chat.image_path)}" class="w-10 h-10 rounded-full object-cover">`;
                    } else {
                        const initials = (chat.first_name.charAt(0) + chat.last_name.charAt(0)).toUpperCase();
                        imageHtml = `<div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">${initials}</div>`;
                    }
                    
                    let unreadHtml = \'\';
                    if (chat.unread_count && chat.unread_count > 0) {
                        unreadHtml = `<span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[9px] flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800">${chat.unread_count}</span>`;
                    }
                    
                    let timeHtml = \'\';
                    if (chat.last_message_time) {
                        const dt = new Date(chat.last_message_time);
                        timeHtml = `<span class="text-[10px] text-slate-400 whitespace-nowrap ml-1">${dt.toLocaleDateString(\'en-US\', { month: \'short\', day: \'numeric\' })}</span>`;
                    }
                    
                    return `
                    <a href="messages.php?chat=${chat.id}" data-name="${searchName}" class="chat-item block p-4 border-b border-slate-100 dark:border-slate-700/50 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors ${activeClass}">
                        <div class="flex items-start gap-3">
                            <div class="relative flex-shrink-0">
                                ${imageHtml}
                                ${unreadHtml}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">${escapeHtml(chat.first_name)} ${escapeHtml(chat.last_name)}</h3>
                                    ${timeHtml}
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">${escapeHtml(chat.last_message || \'Click to start chatting\')}</p>
                            </div>
                        </div>
                    </a>`;
                }).join(\'\');
            });
    }
    
    function escapeHtml(str) {
        if (!str) return \'\';
        return String(str).replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/"/g, \'&quot;\');
    }
    
    loadChatList();
})();
</script>
<style>
.skeleton-pulse { background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200% 100%; animation: skeleton-shimmer 1.4s ease-in-out infinite; display: block; }
.dark .skeleton-pulse { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size: 200% 100%; }
@keyframes skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
';

// filterChats is already global
$c = str_replace('<?php require_once __DIR__ . \'/../includes/footer.php\'; ?>', $js . "\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>", $c);

file_put_contents('c:/xampp/htdocs/osy_db/pages/messages.php', $c);
echo "Done messages.php\n";
