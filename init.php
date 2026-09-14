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
require_once __DIR__ . '/Classes/PanaonYouthProfilingExport.php';
require_once __DIR__ . '/Classes/Location.php';
require_once __DIR__ . '/Classes/Dashboard.php';

function renderDatabaseStartupError($message, $context = [])
{
    $host = htmlspecialchars($context['host'] ?? DB_HOST ?? 'unknown');
    $port = htmlspecialchars((string)($context['port'] ?? DB_PORT ?? '3306'));
    $database = htmlspecialchars($context['database'] ?? DB_NAME ?? 'unknown');
    $sslMode = htmlspecialchars($context['ssl_mode'] ?? (DB_SSL_MODE ?: 'disabled'));
    $sslVerify = htmlspecialchars((string)($context['ssl_verify_server_cert'] ?? (DB_SSL_VERIFY_SERVER_CERT ? 'true' : 'false')));
    $envHost = getenv('DB_HOST') !== false ? 'set' : 'not set';
    $envName = getenv('DB_NAME') !== false ? 'set' : 'not set';

    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head><meta charset="utf-8"><title>Database Connection Error</title><style>body{font-family:Arial,sans-serif;line-height:1.5;margin:2rem;color:#1f2937;}code{background:#f3f4f6;padding:0.1rem 0.35rem;border-radius:4px;} .box{border:1px solid #e5e7eb;border-radius:8px;padding:1rem 1.25rem;background:#fff7ed;} .muted{color:#6b7280;}</style></head>';
    echo '<body><h1>Database Connection Error</h1><div class="box"><p>The application could not connect to the MySQL server.</p><p><strong>Details:</strong> ' . htmlspecialchars($message) . '</p><ul><li><strong>Host:</strong> ' . $host . '</li><li><strong>Port:</strong> ' . $port . '</li><li><strong>Database:</strong> ' . $database . '</li><li><strong>SSL mode:</strong> ' . $sslMode . '</li><li><strong>SSL verify:</strong> ' . $sslVerify . '</li><li><strong>Environment DB_HOST:</strong> ' . $envHost . '</li><li><strong>Environment DB_NAME:</strong> ' . $envName . '</li></ul><p class="muted">If this deployment is using Aiven for MySQL, verify that the service allows public connections and that Render’s outbound IPs are allowed in the Aiven firewall or allowlist.</p></div></body></html>';
    exit;
}

// Initialize database connection
try {
    $database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        require_once __DIR__ . '/create_db.php';
        $database = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_CHARSET);
    } else {
        renderDatabaseStartupError($e->getMessage(), [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_NAME,
            'ssl_mode' => DB_SSL_MODE ?: 'disabled',
            'ssl_verify_server_cert' => DB_SSL_VERIFY_SERVER_CERT ? 'true' : 'false',
        ]);
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

// FORM NONCE: keep a single nonce per page render, but store it in a pool so the same page
// can render header/meta values and hidden form values without accidentally invalidating the
// submission token on unrelated page renders. This prevents duplicate submissions without false
// "invalid form submission" failures caused by a shared global token.
function getFormNonce()
{
    if (!isset($_SESSION['form_nonce_pool']) || !is_array($_SESSION['form_nonce_pool'])) {
        $_SESSION['form_nonce_pool'] = [];
    }

    if (empty($_SESSION['form_nonce_page'])) {
        try {
            $_SESSION['form_nonce_page'] = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $_SESSION['form_nonce_page'] = bin2hex(openssl_random_pseudo_bytes(16));
        }
    }

    $nonce = (string) $_SESSION['form_nonce_page'];
    $_SESSION['form_nonce_pool'][$nonce] = time();
    $_SESSION['form_nonce'] = $nonce;

    return $nonce;
}

// Consume the form nonce: return true if valid and prevent reuse.
function consumeFormNonce($nonce)
{
    if (empty($nonce)) {
        return false;
    }

    if (isset($_SESSION['form_nonce_pool']) && is_array($_SESSION['form_nonce_pool']) && isset($_SESSION['form_nonce_pool'][(string) $nonce])) {
        unset($_SESSION['form_nonce_pool'][(string) $nonce]);
        unset($_SESSION['form_nonce_page']);
        unset($_SESSION['form_nonce']);
        return true;
    }

    if (isset($_SESSION['form_nonce']) && hash_equals((string) $_SESSION['form_nonce'], (string) $nonce)) {
        unset($_SESSION['form_nonce']);
        unset($_SESSION['form_nonce_page']);
        return true;
    }

    return false;
}

// Global CSRF Verification for POST requests
// Exclude public pages from CSRF validation
// Uses multiple strategies to detect public pages regardless of URL structure or .php extension visibility
$publicPageSlugs = ['youth-login', 'lydo-login', 'sk-login', 'provider-login', 'youth-signup', 'provider-registration', 'password-reset', 'verify-otp', 'verify-signup'];

$serverPhpSelf = $_SERVER['PHP_SELF'] ?? '';
$serverRequestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$serverRequestUri = $_SERVER['REQUEST_URI'] ?? '';

// Build a combined string of all server path hints to check against
$uriPath = (string) parse_url($serverRequestUri, PHP_URL_PATH);
$allPathHints = strtolower($serverPhpSelf . '|' . $uriPath . '|' . ($_GET['page'] ?? '') . '|' . ($_GET['route'] ?? ''));

$isPublicPage = false;
foreach ($publicPageSlugs as $slug) {
    if (strpos($allPathHints, $slug) !== false) {
        $isPublicPage = true;
        break;
    }
}

$currentPage = basename($serverPhpSelf);
$requestPage = basename($uriPath);

if ($serverRequestMethod === 'POST' && !$isPublicPage) {
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
    $isApi = (strpos($serverRequestUri, '/api/') !== false || strpos($serverPhpSelf, '/api/') !== false);

    if ($currentPage !== 'password-reset.php' && $currentPage !== 'logout.php' && !$isApi) {
        $inPagesDir = (strpos($serverPhpSelf, '/pages/') !== false);
        if ($inPagesDir) {
            header('Location: password-reset.php');
        } else {
            header('Location: pages/password-reset.php');
        }
        exit;
    }
}
