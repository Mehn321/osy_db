<?php
require_once __DIR__ . '/../init.php';

requireLogin();
requireRole('lydo');

$providerId = isset($_GET['provider_id']) && ctype_digit($_GET['provider_id']) ? (int) $_GET['provider_id'] : 0;
if ($providerId <= 0) {
    http_response_code(400);
    exit('Invalid provider document request.');
}

$provider = $database->fetchOne(
    "SELECT provider_document_path FROM users WHERE id = ? AND role IN ('employer', 'training_provider') LIMIT 1",
    [$providerId],
    'i'
);
if (!$provider || empty($provider['provider_document_path'])) {
    http_response_code(404);
    exit('Provider document not found.');
}

$documentName = basename((string) $provider['provider_document_path']);
$documentDirectory = realpath(__DIR__ . '/../uploads/providers');
$documentPath = $documentDirectory ? realpath($documentDirectory . DIRECTORY_SEPARATOR . $documentName) : false;
if (!$documentPath || strpos($documentPath, $documentDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($documentPath)) {
    http_response_code(404);
    exit('Provider document not found.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($documentPath);
if (!in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
    http_response_code(415);
    exit('Unsupported provider document.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($documentPath));
header('Content-Disposition: inline; filename="provider-proof-' . $providerId . '.' . pathinfo($documentName, PATHINFO_EXTENSION) . '"');
header('X-Content-Type-Options: nosniff');
readfile($documentPath);
exit;
