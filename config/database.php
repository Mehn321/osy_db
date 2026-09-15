<?php

/**
 * Database Configuration
 * 
 * This file contains all database connection settings
 * and is used by the Database class to establish connections.
 */

// Load local configuration if available
$localConfigFile = __DIR__ . '/local.php';
$localConfig = file_exists($localConfigFile) ? require $localConfigFile : [];

function loadDotEnvFile($path)
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (($value[0] ?? '') === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        } elseif (($value[0] ?? '') === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

loadDotEnvFile(__DIR__ . '/../.env');

function getConfiguredValue($key, $fallback)
{
    $candidates = [];

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        $candidates[] = $_SERVER[$key];
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        $candidates[] = $_ENV[$key];
    }
    if (getenv($key) !== false && getenv($key) !== '') {
        $candidates[] = getenv($key);
    }

    foreach ($candidates as $candidate) {
        if ($candidate !== null && $candidate !== '') {
            return $candidate;
        }
    }

    return $fallback;
}

$resolvedDbHost = getConfiguredValue('DB_HOST', $localConfig['db']['host'] ?? '127.0.0.1');
if (is_string($resolvedDbHost) && stripos($resolvedDbHost, 'aivencloud.com') !== false && stripos($resolvedDbHost, 'public-') !== 0) {
    $resolvedDbHost = preg_replace('/^mysql-/', 'public-mysql-', $resolvedDbHost, 1);
}
define('DB_HOST', $resolvedDbHost);
define('DB_USER', getConfiguredValue('DB_USER', $localConfig['db']['user'] ?? 'root'));
define('DB_PASS', getConfiguredValue('DB_PASS', $localConfig['db']['pass'] ?? ''));
define('DB_NAME', getConfiguredValue('DB_NAME', $localConfig['db']['name'] ?? 'municipal_kk_profiling'));
define('DB_PORT', (int)getConfiguredValue('DB_PORT', $localConfig['db']['port'] ?? 3306));
define('DB_CHARSET', getConfiguredValue('DB_CHARSET', $localConfig['db']['charset'] ?? 'utf8mb4'));
define('DB_SOCKET', getConfiguredValue('DB_SOCKET', $localConfig['db']['socket'] ?? ''));
define('DB_SSL_MODE', getConfiguredValue('DB_SSL_MODE', $localConfig['db']['ssl_mode'] ?? ''));
define('DB_SSL_CA', getConfiguredValue('DB_SSL_CA', $localConfig['db']['ssl_ca'] ?? ''));
define('DB_SSL_CERT', getConfiguredValue('DB_SSL_CERT', $localConfig['db']['ssl_cert'] ?? ''));
define('DB_SSL_KEY', getConfiguredValue('DB_SSL_KEY', $localConfig['db']['ssl_key'] ?? ''));
$sslVerifyServerCertValue = getConfiguredValue('DB_SSL_VERIFY_SERVER_CERT', $localConfig['db']['ssl_verify_server_cert'] ?? '');
if ($sslVerifyServerCertValue === '') {
    $sslVerifyServerCertValue = (strpos(DB_HOST, 'aivencloud.com') !== false) ? 'false' : 'true';
}
define('DB_SSL_VERIFY_SERVER_CERT', filter_var(
    $sslVerifyServerCertValue,
    FILTER_VALIDATE_BOOLEAN,
    FILTER_NULL_ON_FAILURE
) ?? ((strpos(DB_HOST, 'aivencloud.com') !== false) ? false : true));

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Detect HTTPS even behind a reverse proxy (e.g. Render, Heroku, Cloudflare)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

// Session configuration - must be set BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

if ($isHttps) {
    // On HTTPS (including behind reverse proxy): secure cookie, SameSite=None required for cross-site
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'None');
} else {
    // Local HTTP dev: SameSite=Lax is fine
    ini_set('session.cookie_secure', 0);
    ini_set('session.cookie_samesite', 'Lax');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers (CSP, HSTS, X-Frame-Options, Referrer-Policy, X-Content-Type-Options)
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
// Content-Security-Policy - permissive but solid base
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://fonts.gstatic.com https://unpkg.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net data: blob:; img-src 'self' data: blob: https: http:;");
