<?php
require 'init.php';

// Release the email from lydo_admin
$database->execute("UPDATE users SET email = 'lydo_admin_old@example.com' WHERE username = 'lydo_admin'");

// Update admin1 (the likely default login) to the requested email
$database->execute("UPDATE users SET email = 'aclonhemday@gmail.com' WHERE username = 'admin1'");

$user = $database->fetchOne("SELECT id, username, email FROM users WHERE email = 'aclonhemday@gmail.com'");
echo "Email aclonhemday@gmail.com is now assigned to Username: {$user['username']} (ID: {$user['id']})\n";
