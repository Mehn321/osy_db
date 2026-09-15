<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../Classes/OSYProfile.php';

requireLogin();

$allowedTypes = ['govt_id' => 'govt_id_image', 'certification' => 'identity_document_path'];

$type = $_GET['type'] ?? '';
$profileId = isset($_GET['profile_id']) && ctype_digit($_GET['profile_id']) ? (int) $_GET['profile_id'] : 0;

if (!isset($allowedTypes[$type]) || $profileId <= 0) {
    http_response_code(400);
    exit('Invalid document request.');
}

$osyProfile = new OSYProfile($database);
$profile = $osyProfile->getById($profileId);

if (!$profile) {
    http_response_code(404);
    exit('Document not found.');
}

// Authorization: LYDO can view any profile's documents. SK Chairman only for their own
// barangay. A youth can only view their own documents. Everyone else is denied.
$role = $_SESSION['role'] ?? '';
$authorized = false;

if ($role === 'lydo') {
    $authorized = true;
} elseif ($role === 'sk_chairman' && ($profile['barangay'] ?? null) === ($_SESSION['barangay'] ?? null)) {
    $authorized = true;
} elseif ($role === 'youth' && (int) ($profile['created_by'] ?? 0) === (int) ($_SESSION['user_id'] ?? 0)) {
    $authorized = true;
}

if (!$authorized) {
    http_response_code(403);
    exit('Access denied.');
}

$column = $allowedTypes[$type];
$storedPath = $profile[$column] ?? '';
if (empty($storedPath)) {
    http_response_code(404);
    exit('Document not found.');
}

$subDir = $type === 'govt_id' ? 'govt_ids' : 'certifications';
$documentName = basename($storedPath);
$documentDirectory = realpath(__DIR__ . '/../uploads/' . $subDir);
$documentPath = $documentDirectory ? realpath($documentDirectory . DIRECTORY_SEPARATOR . $documentName) : false;

if (!$documentPath || strpos($documentPath, $documentDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($documentPath)) {
    http_response_code(404);
    exit('Document not found.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($documentPath);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'], true)) {
    http_response_code(415);
    exit('Unsupported document type.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($documentPath));
header('Content-Disposition: inline; filename="' . $type . '-' . $profileId . '.' . pathinfo($documentName, PATHINFO_EXTENSION) . '"');
header('X-Content-Type-Options: nosniff');
readfile($documentPath);
exit;
