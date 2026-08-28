<?php
require 'init.php';
$tables = $db->fetchAll("SHOW TABLES");
print_r($tables);
