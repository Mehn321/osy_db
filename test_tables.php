<?php
require_once __DIR__ . '/init.php';
$tables = $database->fetchAll('SHOW TABLES');
print_r($tables);
