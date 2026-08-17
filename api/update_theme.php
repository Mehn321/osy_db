<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get raw JSON post data if form fields aren't populated
$theme = $_POST['theme'] ?? null;
if (!$theme) {
    $input = json_decode(file_get_contents('php://input'), true);
    $theme = $input['theme'] ?? null;
}

if ($theme !== 'dark' && $theme !== 'light') {
    echo json_encode(['success' => false, 'message' => 'Invalid theme preference']);
    exit;
}

$_SESSION['theme'] = $theme;

echo json_encode(['success' => true, 'theme' => $theme]);
