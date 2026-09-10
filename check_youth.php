<?php
// Use an existing youth with a profile from the database
// From database_dump.sql, user 'rsantos' has role 'lydo' but there's also an osy_profile
// Let me check which actual youth accounts exist in database

require_once __DIR__ . '/init.php';

// For debugging - logout current user and login as a different youth
if ($_GET['action'] ?? '' === 'list_youth') {
    $result = $database->fetchAll("SELECT id, username, role, status FROM users WHERE role = 'youth' LIMIT 10", [], "");
    echo "<h2>Youth Users:</h2><pre>";
    print_r($result);
    echo "</pre>";
    
    $profiles = $database->fetchAll("SELECT id, first_name, last_name, created_by FROM osy_profiles LIMIT 10", [], "");
    echo "<h2>OSY Profiles (first 10):</h2><pre>";
    print_r($profiles);
    echo "</pre>";
}
?>
