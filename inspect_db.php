<?php
require 'c:/xampp/htdocs/osy_db/init.php';
$stmt = $database->fetchAll('SELECT * FROM messages ORDER BY id DESC LIMIT 5');
print_r($stmt);
