<?php
require_once 'init.php';
$conn = $database->getConnection();
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_row()) echo $row[0].PHP_EOL;
