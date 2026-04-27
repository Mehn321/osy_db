<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    
    $query = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_type ENUM('admin', 'osy') NOT NULL,
        sender_id INT NOT NULL,
        recipient_type ENUM('admin', 'osy') NOT NULL,
        recipient_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $result = $db->getConnection()->query($query);
    if ($result) {
        echo "Messages table created successfully.\n";
    } else {
        echo "Failed to create messages table.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
