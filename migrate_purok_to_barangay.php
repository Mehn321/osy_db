<?php
/**
 * migrate_purok_to_barangay.php
 * Run this ONCE to migrate from 'purok' to 'barangay' and seed the new Barangays for Panaon.
 */
require_once __DIR__ . '/init.php';

try {
    $conn = $database->getConnection();
    
    // 1. Rename column in osy_profiles
    $res = $conn->query("SHOW COLUMNS FROM `osy_profiles` LIKE 'purok'");
    if ($res && $res->num_rows > 0) {
        $conn->query("ALTER TABLE `osy_profiles` CHANGE COLUMN `purok` `barangay` VARCHAR(100) DEFAULT NULL");
        echo "Successfully renamed column 'purok' to 'barangay' in osy_profiles.\n";
    } else {
        echo "Column 'purok' does not exist in osy_profiles. Checking if 'barangay' already exists...\n";
        $resCheck = $conn->query("SHOW COLUMNS FROM `osy_profiles` LIKE 'barangay'");
        if ($resCheck && $resCheck->num_rows > 0) {
            echo "Column 'barangay' already exists.\n";
        }
    }

    // 2. Clear old purok categories
    $conn->query("DELETE FROM `system_references` WHERE `category` = 'purok'");
    echo "Successfully deleted old 'purok' records from system_references.\n";

    // 3. Insert the 16 Barangays of Panaon
    $barangays = [
        'Baga', 'Bangko', 'Camanucan', 'Dela Paz', 'Lutao', 'Magsaysay', 'Map-an', 
        'Mohon', 'Poblacion', 'Punta', 'Salimpuno', 'San Andres', 'San Juan', 
        'San Roque', 'Sumasap', 'Villalin'
    ];

    // Ensure we don't duplicate if script is run multiple times
    $conn->query("DELETE FROM `system_references` WHERE `category` = 'barangay'");

    $insertQuery = "INSERT INTO `system_references` (`category`, `value`) VALUES ";
    $values = [];
    foreach ($barangays as $b) {
        $values[] = "('barangay', '" . $database->escape($b) . "')";
    }
    $insertQuery .= implode(', ', $values);
    
    $conn->query($insertQuery);
    echo "Successfully seeded 16 Barangays of Panaon into system_references.\n";

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
