<?php
require_once 'init.php';
$res = $database->getConnection()->query("SHOW COLUMNS FROM osy_profiles");
while($row = $res->fetch_row()) echo $row[0].PHP_EOL;
