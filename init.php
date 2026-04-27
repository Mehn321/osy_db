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
