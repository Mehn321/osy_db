<?php
/**
 * Database Migration: Rebrand OSY System to Municipal KK Profiling System
 * - Adds profile_type (OSY/Regular) field
 * - Rename middle_initial to middle_name
 * - Add govt_id_number and govt_id_image fields
 * - Add engagement_status for Non-OSY members
 */

require_once __DIR__ . '/config/database.php';

echo "🔄 Starting Municipal KK Profiling System migration...\n\n";

try {
    // First, connect to MySQL server without specifying database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error);
    }

    // Check if database exists, if not create it
    $dbExists = $conn->select_db(DB_NAME);
    if (!$dbExists) {
        echo "📦 Creating database '" . DB_NAME . "'...\n";
        if ($conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
            $conn->select_db(DB_NAME);
            echo "✓ Database created\n";
        } else {
            throw new Exception("Failed to create database: " . $conn->error);
        }
    }

    // 1. Add profile_type column
    $query = "ALTER TABLE osy_profiles ADD COLUMN profile_type ENUM('OSY','Regular') DEFAULT 'Regular' AFTER id";
    if ($conn->query($query)) {
        echo "✓ Column 'profile_type' added\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "✓ Column 'profile_type' already exists\n";
        } else {
            throw new Exception($conn->error);
        }
    }

    // 2. Add middle_name column (new field)
    $query = "ALTER TABLE osy_profiles ADD COLUMN middle_name VARCHAR(50) DEFAULT NULL AFTER first_name";
    if ($conn->query($query)) {
        echo "✓ Column 'middle_name' added\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "✓ Column 'middle_name' already exists\n";
        } else {
            throw new Exception($conn->error);
        }
    }

    // 3. Add govt_id_number column
    $query = "ALTER TABLE osy_profiles ADD COLUMN govt_id_number VARCHAR(100) DEFAULT NULL AFTER govt_id_type";
    if ($conn->query($query)) {
        echo "✓ Column 'govt_id_number' added\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "✓ Column 'govt_id_number' already exists\n";
        } else {
            throw new Exception($conn->error);
        }
    }

    // 4. Add govt_id_image column
    $query = "ALTER TABLE osy_profiles ADD COLUMN govt_id_image VARCHAR(255) DEFAULT NULL AFTER govt_id_number";
    if ($conn->query($query)) {
        echo "✓ Column 'govt_id_image' added\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "✓ Column 'govt_id_image' already exists\n";
        } else {
            throw new Exception($conn->error);
        }
    }

    // 5. Add engagement_status column
    $query = "ALTER TABLE osy_profiles ADD COLUMN engagement_status VARCHAR(100) DEFAULT NULL AFTER reason_for_not_in_school";
    if ($conn->query($query)) {
        echo "✓ Column 'engagement_status' added\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "✓ Column 'engagement_status' already exists\n";
        } else {
            throw new Exception($conn->error);
        }
    }

    // 6. Migrate data: Use middle_name from middle_initial if exists
    $query = "UPDATE osy_profiles SET middle_name = middle_initial WHERE middle_initial IS NOT NULL AND middle_name IS NULL";
    $result = $conn->query($query);
    if ($result) {
        echo "✓ Migrated middle_initial data to middle_name\n";
    }

    // 7. Set existing profiles as OSY (to preserve backwards compatibility)
    $query = "UPDATE osy_profiles SET profile_type = 'OSY' WHERE profile_type IS NULL OR profile_type = ''";
    $result = $conn->query($query);
    if ($result) {
        echo "✓ Set existing profiles as OSY type\n";
    }

    // 8. Ensure upload directories exist
    $dirs = [
        __DIR__ . '/uploads/profiles',
        __DIR__ . '/uploads/govt_ids'
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Directory created: $dir\n";
        } else {
            echo "✓ Directory exists: $dir\n";
        }
    }

    echo "\n✅ Municipal KK Profiling System migration completed successfully!\n";
    echo "\n📋 Summary:\n";
    echo "- System rebranded from OSY to Municipal KK Profiling\n";
    echo "- Profile type tracking: OSY vs Regular\n";
    echo "- Enhanced name fields: middle_name (optional)\n";
    echo "- Government ID tracking: number + document image\n";
    echo "- Non-OSY engagement status tracking\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>