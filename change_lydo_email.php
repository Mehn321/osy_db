<?php
require 'init.php';

$database->execute("UPDATE users SET email = 'nhempharmacy@example.com' WHERE username = 'nhempharmacy'");
$database->execute("UPDATE users SET email = 'aclonhemday@gmail.com' WHERE username = 'lydo_admin'");

echo "Done.\n";
