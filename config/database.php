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

define('DB_HOST', $localConfig['db']['host'] ?? '127.0.0.1');
define('DB_USER', $localConfig['db']['user'] ?? 'root');
define('DB_PASS', $localConfig['db']['pass'] ?? '');
define('DB_NAME', $localConfig['db']['name'] ?? 'municipal_kk_profiling');
define('DB_PORT', $localConfig['db']['port'] ?? 3306);
define('DB_CHARSET', $localConfig['db']['charset'] ?? 'utf8mb4');
define('DB_SOCKET', $localConfig['db']['socket'] ?? '');
define('DB_SSL_MODE', $localConfig['db']['ssl_mode'] ?? '');
define('DB_SSL_CA', $localConfig['db']['ssl_ca'] ?? '');
define('DB_SSL_CERT', $localConfig['db']['ssl_cert'] ?? '');
define('DB_SSL_KEY', $localConfig['db']['ssl_key'] ?? '');
define('DB_SSL_VERIFY_SERVER_CERT', $localConfig['db']['ssl_verify_server_cert'] ?? true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
