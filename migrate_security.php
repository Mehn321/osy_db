<?php

/**
 * Migration: Security Enhancements Phase 1 & 2
 * Creates tables for password history, 2FA codes, login events, and security settings.
 */

require_once __DIR__ . '/init.php';

try {
    echo "Starting security enhancements migration...\n";

    // 1. user_security_settings
    $database->execute("
        CREATE TABLE IF NOT EXISTS user_security_settings (
            user_id INT PRIMARY KEY,
            two_factor_enabled TINYINT(1) NOT NULL DEFAULT 1,
            alert_email_enabled TINYINT(1) NOT NULL DEFAULT 1,
            last_password_changed_at DATETIME NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created user_security_settings table.\n";

    // 2. user_password_history
    $database->execute("
        CREATE TABLE IF NOT EXISTS user_password_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created user_password_history table.\n";

    // 3. user_login_events
    $database->execute("
        CREATE TABLE IF NOT EXISTS user_login_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(1000) NOT NULL,
            login_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            login_result VARCHAR(50) NOT NULL,
            failure_reason VARCHAR(255) NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created user_login_events table.\n";

    // 4. user_2fa_codes
    $database->execute("
        CREATE TABLE IF NOT EXISTS user_2fa_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            otp_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            consumed_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created user_2fa_codes table.\n";

    $codeColumns = array_column($database->fetchAll("SHOW COLUMNS FROM user_2fa_codes"), 'Field');
    if (!in_array('channel', $codeColumns, true)) {
        $database->execute("ALTER TABLE user_2fa_codes ADD COLUMN channel ENUM('email','phone') NOT NULL DEFAULT 'email' AFTER user_id");
    }
    if (!in_array('purpose', $codeColumns, true)) {
        $database->execute("ALTER TABLE user_2fa_codes ADD COLUMN purpose ENUM('login','signup') NOT NULL DEFAULT 'login' AFTER channel");
    }

    echo "Security migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
