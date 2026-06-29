<?php

/**
 * Application Autoloader & Initializer
 * 
 * This file loads all necessary classes and sets up the application
 */

// Load configuration
require_once __DIR__ . '/config/database.php';

// Load all classes
require_once __DIR__ . '/Classes/Database.php';
require_once __DIR__ . '/Classes/User.php';
require_once __DIR__ . '/Classes/OSYProfile.php';
require_once __DIR__ . '/Classes/Opportunity.php';
require_once __DIR__ . '/Classes/Matching.php';
require_once __DIR__ . '/Classes/Notification.php';
require_once __DIR__ . '/Classes/AuditLog.php';
require_once __DIR__ . '/Classes/Report.php';
require_once __DIR__ . '/Classes/Dashboard.php';

// Initialize database connection
try {
    $database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        require_once __DIR__ . '/create_db.php';
        $database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
    } else {
        die('Database Connection Error: ' . $e->getMessage());
    }
}

try {
    $database->initializeSchema(__DIR__ . '/database_dump.sql');
} catch (Exception $e) {
    die('Database Initialization Error: ' . $e->getMessage());
}

// Run migrations silently
if (file_exists(__DIR__ . '/migrate.php')) {
    require_once __DIR__ . '/migrate.php';
}

// Initialize core classes
$user = new User($database);
$auditLog = new AuditLog($database);

// Load RBAC helpers
require_once __DIR__ . '/includes/access_control.php';

// CSRF Protection Functions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getCsrfToken()
{
    return $_SESSION['csrf_token'] ?? '';
}

function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Global CSRF Verification for POST requests
// Exclude public pages from CSRF validation
$publicPages = ['login.php', 'youth-signup.php', 'provider-registration.php', 'password-reset.php'];
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isPublicPage = in_array($currentPage, $publicPages);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isPublicPage) {
    $token = '';
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
    }

    if (empty($token)) {
        $token = $_POST['csrf_token'] ?? '';
    }

    // In case of JSON requests, try parsing input if token still empty
    if (empty($token)) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? '';
        }
    }

    if (!validateCsrfToken($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }
}

// Check for forced password reset redirect
if (isset($_SESSION['user_id']) && !empty($_SESSION['temp_password_required'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $isApi = (strpos($requestUri, '/api/') !== false || strpos($_SERVER['PHP_SELF'], '/api/') !== false);

    if ($currentPage !== 'password-reset.php' && $currentPage !== 'logout.php' && $currentPage !== 'login.php' && !$isApi) {
        $inPagesDir = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false);
        if ($inPagesDir) {
            header('Location: password-reset.php');
        } else {
            header('Location: pages/password-reset.php');
        }
        exit;
    }
}
