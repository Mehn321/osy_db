<?php

/**
 * Migration: Create opportunity_required_skills table
 * 
 * This table links opportunities to required skills for better matching algorithm.
 * Allows providers to specify what skills are needed for each opportunity.
 * 
 * Run: php migrate_opportunity_skills.php
 */

require_once __DIR__ . '/init.php';

$connection = $database->getConnection();

echo "Starting migration: Create opportunity_required_skills table...\n";

try {
    // Check if table already exists
    $result = $connection->query("SHOW TABLES LIKE 'opportunity_required_skills'");
    if ($result->num_rows > 0) {
        echo "✓ Table 'opportunity_required_skills' already exists.\n";
    } else {
        // Create the table
        $sql = "
            CREATE TABLE `opportunity_required_skills` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `opportunity_id` INT(11) NOT NULL,
                `skill` VARCHAR(255) NOT NULL COMMENT 'Skill name or description',
                `importance_level` ENUM('Required', 'Preferred', 'Nice to have') DEFAULT 'Required',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_opportunity_skills` (`opportunity_id`),
                FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        if ($connection->query($sql) === TRUE) {
            echo "✓ Table 'opportunity_required_skills' created successfully.\n";
        } else {
            echo "✗ Error creating table: " . $connection->error . "\n";
            exit(1);
        }
    }

    // Also create opportunity_skills table for junction mapping if using skill references
    $result = $connection->query("SHOW TABLES LIKE 'opportunity_skills'");
    if ($result->num_rows === 0) {
        $sql2 = "
            CREATE TABLE `opportunity_skills` (
                `opportunity_id` INT(11) NOT NULL,
                `skill_id` INT(11) NOT NULL,
                `importance_level` ENUM('Required', 'Preferred', 'Nice to have') DEFAULT 'Required',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`opportunity_id`, `skill_id`),
                FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        if ($connection->query($sql2) === TRUE) {
            echo "✓ Table 'opportunity_skills' created successfully.\n";
        }
    }

    echo "\n✓ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$connection->close();
