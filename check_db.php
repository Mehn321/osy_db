<?php
require_once 'init.php';
require_once 'init.php';
$conn = $database->getConnection();

echo "OSY Profiles Columns:\n";
$res = $conn->query("SHOW COLUMNS FROM osy_profiles");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

echo "\nBarangay References:\n";
$res2 = $conn->query("SELECT * FROM system_references WHERE category = 'barangay'");
while($row = $res2->fetch_assoc()) {
    print_r($row);
}
