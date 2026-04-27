<?php

/**
 * Database Creation Script
 *
 * Creates the civic_horizon_osy database
 */

try {
    // Connect to MySQL without specifying a database
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS civic_horizon_osy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'civic_horizon_osy' created successfully!\n";

    // Import schema and sample data if dump exists
    $dumpFile = __DIR__ . '/database_dump.sql';
    if (file_exists($dumpFile)) {
        $pdo->exec("USE civic_horizon_osy");
        $sql = file_get_contents($dumpFile);
        if ($sql !== false) {
            $queries = array_filter(array_map('trim', preg_split('/;\s*(?=\n|$)/', $sql)));
            foreach ($queries as $query) {
                if (stripos($query, '--') === 0 || $query === '') {
                    continue;
                }
                $pdo->exec($query);
            }
            echo "Database schema imported from database_dump.sql\n";
        } else {
            echo "Warning: Could not read database_dump.sql\n";
        }
    } else {
        echo "Warning: database_dump.sql not found, schema not imported.\n";
    }
} catch (PDOException $e) {
    echo "Database creation failed: " . $e->getMessage() . "\n";
}
