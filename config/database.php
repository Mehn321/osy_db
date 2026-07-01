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

function getConfiguredValue($key, $fallback)
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        $value = $_ENV[$key] ?? ($_SERVER[$key] ?? null);
    }

    return $value === null || $value === '' ? $fallback : $value;
}

define('DB_HOST', getConfiguredValue('DB_HOST', $localConfig['db']['host'] ?? '127.0.0.1'));
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

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
