<?php

/**
 * api/trigger_global_sync.php
 * 
 * AJAX endpoint to trigger a background global sync
 */
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'lydo') {
    echo json_encode(['success' => false, 'message' => 'Only LYDO can start global matching sync.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$scriptPath = realpath(__DIR__ . '/background_global_sync.php');

if ($scriptPath) {
    // Windows background process
    pclose(popen("start /B php " . escapeshellarg($scriptPath), "r"));

    echo json_encode([
        'success' => true,
        'message' => 'Global AI Sync has been started in the background. This may take several minutes to complete depending on the number of profiles.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not find background script.']);
}
