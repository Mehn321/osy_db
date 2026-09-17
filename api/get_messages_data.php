<?php
/**
 * API: Messages Data
 * Serves chat lists and conversation history for messages.php
 */
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/Cache.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../Classes/Messages.php';
$messagesObj = new Messages($database);
$cache = new Cache(10); // Very short TTL — messages need to feel realtime

try {
    $view = $_GET['view'] ?? 'chat_list';

    // Chat list (sidebar)
    if ($view === 'chat_list') {
        $chatList = $messagesObj->getChatList();
        echo json_encode(['success' => true, 'chats' => $chatList]);
        exit;
    }

    // Conversation with a specific user
    if ($view === 'conversation' && isset($_GET['with'])) {
        $withId       = (int)$_GET['with'];
        $conversation = $messagesObj->getConversation($withId);
        $messagesObj->markAsRead($withId);
        echo json_encode(['success' => true, 'messages' => $conversation]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown view']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching messages']);
}
