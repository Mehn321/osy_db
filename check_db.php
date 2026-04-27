<?php
require_once 'init.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SHOW COLUMNS FROM osy_profiles LIKE 'purok'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $db->query("SELECT * FROM reference_data WHERE category = 'purok'");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
