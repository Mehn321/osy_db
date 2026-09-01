<?php

/**
 * API Endpoint: Get Fresh Form Nonce
 * 
 * Returns a fresh CSRF/form nonce for subsequent requests.
 * Ensures the nonce is regenerated for the next form submission.
 */

header('Content-Type: application/json');

// Suppress default error output and catch all errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

// Catch uncaught exceptions
set_exception_handler(function ($exception) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $exception->getMessage()]);
    exit;
});

require_once __DIR__ . '/../init.php';

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Generate a fresh nonce
try {
    $_SESSION['form_nonce'] = bin2hex(random_bytes(16));
    $nonce = $_SESSION['form_nonce'];

    echo json_encode([
        'success' => true,
        'form_nonce' => $nonce
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate nonce: ' . $e->getMessage()
    ]);
}
