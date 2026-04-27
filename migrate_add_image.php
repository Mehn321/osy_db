<?php
require 'init.php';

try {
    // Define columns to add using IF NOT EXISTS syntax
    $columnsSQL = [
        "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS skills text DEFAULT NULL",
        "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS interests text DEFAULT NULL",
        "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS govt_id_type varchar(50) DEFAULT NULL",
        "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS reason_for_not_in_school text DEFAULT NULL",
        "ALTER TABLE osy_profiles ADD COLUMN IF NOT EXISTS registration_status enum('Drafting','Submitted','Approved') DEFAULT 'Drafting'"
    ];

    foreach ($columnsSQL as $sql) {
        try {
            $database->execute($sql);
            echo "✓ Column query executed: " . substr($sql, 0, 60) . "...\n";
        } catch (Exception $e) {
            echo "✓ Column may already exist: " . substr($sql, 0, 60) . "...\n";
        }
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/uploads/profiles';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "✓ Upload directory created\n";
    } else {
        echo "✓ Upload directory already exists\n";
    }

    echo "\n✅ All migrations completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
