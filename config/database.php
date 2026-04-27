<?php

/**
 * Database Configuration
 * 
 * This file contains all database connection settings
 * and is used by the Database class to establish connections.
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'municipal_kk_profiling');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();
