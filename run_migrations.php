<?php

/**
 * run_migrations.php
 * Run this ONCE to fix all database schema issues.
 * It is safe to run multiple times (uses IF NOT EXISTS / column checks).
 */
require_once __DIR__ . '/init.php';

$errors = [];
$success = [];

// ─── 1. messages table ────────────────────────────────────────────────────────
try {
    $database->getConnection()->query("
        CREATE TABLE IF NOT EXISTS `messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender_type` ENUM('admin', 'osy') NOT NULL,
            `sender_id` int(11) NOT NULL,
            `recipient_type` ENUM('admin', 'osy') NOT NULL,
            `recipient_id` int(11) NOT NULL,
            `message` TEXT NOT NULL,
            `sms_status` ENUM('none','pending','success','failed') DEFAULT 'none',
            `email_status` ENUM('none','pending','success','failed') DEFAULT 'none',
            `sms_error` TEXT DEFAULT NULL,
            `email_error` TEXT DEFAULT NULL,
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_messages_recipient` (`recipient_type`, `recipient_id`),
            KEY `idx_messages_sender` (`sender_type`, `sender_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "✔ messages table OK";
} catch (Exception $e) {
    $errors[] = "✘ messages table: " . $e->getMessage();
}

// Add missing columns to messages if it already existed without them
$colChecks = [
    'sms_status'   => "ALTER TABLE `messages` ADD COLUMN `sms_status` ENUM('none','pending','success','failed') DEFAULT 'none' AFTER `message`",
    'email_status' => "ALTER TABLE `messages` ADD COLUMN `email_status` ENUM('none','pending','success','failed') DEFAULT 'none' AFTER `sms_status`",
    'sms_error'    => "ALTER TABLE `messages` ADD COLUMN `sms_error` TEXT DEFAULT NULL AFTER `email_status`",
    'email_error'  => "ALTER TABLE `messages` ADD COLUMN `email_error` TEXT DEFAULT NULL AFTER `sms_error`",
];

try {
    $cols = $database->fetchAll("SHOW COLUMNS FROM `messages`");
    $existingCols = array_column($cols, 'Field');
    foreach ($colChecks as $col => $sql) {
        if (!in_array($col, $existingCols)) {
            $database->getConnection()->query($sql);
            $success[] = "✔ Added messages.$col column";
        } else {
            $success[] = "✔ messages.$col already exists";
        }
    }
} catch (Exception $e) {
    $errors[] = "✘ messages column migration: " . $e->getMessage();
}

// ─── 2. system_settings table ────────────────────────────────────────────────
try {
    $database->getConnection()->query("
        CREATE TABLE IF NOT EXISTS `system_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL UNIQUE,
            `setting_value` TEXT DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "✔ system_settings table OK";
} catch (Exception $e) {
    $errors[] = "✘ system_settings table: " . $e->getMessage();
}

// Seed default rows for system_settings
$defaultSettings = [
    'traccar_token'    => '',
    'gmail_user'       => '',
    'gmail_app_password' => '',
    'system_name'      => 'Youth Profiling System',
    'system_version'   => '1.0.0',
];
foreach ($defaultSettings as $key => $value) {
    try {
        $database->getConnection()->query(
            "INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) 
             VALUES ('" . $database->escape($key) . "', '" . $database->escape($value) . "')"
        );
    } catch (Exception $e) {
        $errors[] = "✘ system_settings seed ($key): " . $e->getMessage();
    }
}
$success[] = "✔ system_settings default rows seeded";

echo "<h2>Migration Results</h2>";
echo "<h3 style='color:green'>Success (" . count($success) . ")</h3><ul>";
foreach ($success as $s) echo "<li>$s</li>";
echo "</ul>";
if ($errors) {
    echo "<h3 style='color:red'>Errors (" . count($errors) . ")</h3><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul>";
} else {
    echo "<p style='color:green;font-weight:bold'>All migrations completed successfully! You can delete this file now.</p>";
}
