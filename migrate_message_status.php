<?php
require_once __DIR__ . '/init.php';

try {
    echo "Starting migration: Adding delivery status columns to 'messages' table...\n";
    
    // Check if columns exist first
    $columns = $database->fetchAll("SHOW COLUMNS FROM messages");
    $columnNames = array_column($columns, 'Field');
    
    $queries = [];
    
    if (!in_array('sms_status', $columnNames)) {
        $queries[] = "ALTER TABLE messages ADD COLUMN sms_status ENUM('none', 'pending', 'success', 'failed') DEFAULT 'none' AFTER message";
    }
    
    if (!in_array('email_status', $columnNames)) {
        $queries[] = "ALTER TABLE messages ADD COLUMN email_status ENUM('none', 'pending', 'success', 'failed') DEFAULT 'none' AFTER sms_status";
    }
    
    if (!in_array('sms_error', $columnNames)) {
        $queries[] = "ALTER TABLE messages ADD COLUMN sms_error TEXT AFTER email_status";
    }
    
    if (!in_array('email_error', $columnNames)) {
        $queries[] = "ALTER TABLE messages ADD COLUMN email_error TEXT AFTER sms_error";
    }
    
    if (empty($queries)) {
        echo "All columns already exist. Migration skipped.\n";
    } else {
        foreach ($queries as $q) {
            $database->execute($q);
            echo "Executed: $q\n";
        }
        echo "Migration completed successfully!\n";
    }
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
