<?php
/**
 * migrate_purok_to_barangay.php
 * Run this ONCE to migrate from 'purok' to 'barangay' and seed the new Barangays for Panaon.
 */
require_once __DIR__ . '/init.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Rename column in osy_profiles
    $stmt = $db->query("SHOW COLUMNS FROM `osy_profiles` LIKE 'purok'");
    if ($stmt->rowCount() > 0) {
        $db->exec("ALTER TABLE `osy_profiles` CHANGE COLUMN `purok` `barangay` VARCHAR(100) DEFAULT NULL");
        echo "Successfully renamed column 'purok' to 'barangay' in osy_profiles.\n";
    } else {
        echo "Column 'purok' does not exist in osy_profiles. Checking if 'barangay' already exists...\n";
        $stmtCheck = $db->query("SHOW COLUMNS FROM `osy_profiles` LIKE 'barangay'");
        if ($stmtCheck->rowCount() > 0) {
            echo "Column 'barangay' already exists.\n";
        }
    }

    // 2. Clear old purok categories
    $db->exec("DELETE FROM `reference_data` WHERE `category` = 'purok'");
    echo "Successfully deleted old 'purok' records from reference_data.\n";

    // 3. Insert the 16 Barangays of Panaon
    $barangays = [
        'Baga', 'Bangko', 'Camanucan', 'Dela Paz', 'Lutao', 'Magsaysay', 'Map-an', 
        'Mohon', 'Poblacion', 'Punta', 'Salimpuno', 'San Andres', 'San Juan', 
        'San Roque', 'Sumasap', 'Villalin'
    ];

    // Ensure we don't duplicate if script is run multiple times
    $db->exec("DELETE FROM `reference_data` WHERE `category` = 'barangay'");

    $insertQuery = "INSERT INTO `reference_data` (`category`, `value`) VALUES ";
    $values = [];
    foreach ($barangays as $b) {
        $values[] = "('barangay', " . $db->quote($b) . ")";
    }
    $insertQuery .= implode(', ', $values);
    
    $db->exec($insertQuery);
    echo "Successfully seeded 16 Barangays of Panaon into reference_data.\n";

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
