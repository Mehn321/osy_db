<?php
require_once __DIR__ . '/init.php';
try {
    $conn = $database->getConnection();
    
    $existingNotifCols = array_column($database->fetchAll("SHOW COLUMNS FROM `notifications`"), 'Field');
    echo "existing notifications columns: " . implode(', ', $existingNotifCols) . "<br>";
    
    $notifColMigrations = [
        'recipient_type' => "ALTER TABLE `notifications` ADD COLUMN `recipient_type` ENUM('All','OSY','Staff','Specific') DEFAULT 'All' AFTER `type`",
        'created_by'     => "ALTER TABLE `notifications` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `status`",
    ];
    foreach ($notifColMigrations as $col => $sql) {
        if (!in_array($col, $existingNotifCols)) {
            echo "Running: $sql ... ";
            $conn->query($sql);
            echo "Done.<br>";
        }
    }
    
    $existingTmplCols = array_column($database->fetchAll("SHOW COLUMNS FROM `notification_templates`"), 'Field');
    echo "existing template columns: " . implode(', ', $existingTmplCols) . "<br>";
    
    $tmplColMigrations = [
        'subject'    => "ALTER TABLE `notification_templates` ADD COLUMN `subject` varchar(255) DEFAULT NULL AFTER `name`",
        'type'       => "ALTER TABLE `notification_templates` ADD COLUMN `type` ENUM('SMS','Email','SMS/Email') DEFAULT 'SMS' AFTER `body`",
        'created_by' => "ALTER TABLE `notification_templates` ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `type`",
        'updated_at' => "ALTER TABLE `notification_templates` ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `created_at`",
    ];
    foreach ($tmplColMigrations as $col => $sql) {
        if (!in_array($col, $existingTmplCols)) {
            echo "Running: $sql ... ";
            $conn->query($sql);
            echo "Done.<br>";
        }
    }
    
    echo "All migrations test completed.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
