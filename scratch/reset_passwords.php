<?php
require_once __DIR__ . '/../init.php';

$accounts = [
    'nhempharmacy' => 'password123',
    'test_provider' => 'password123',
    'test_youth' => 'password123',
    'sk_baga' => 'password123'
];

foreach ($accounts as $username => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $database->execute("UPDATE users SET password = ? WHERE username = ?", [$hash, $username], "ss");
    echo "Updated $username to $pass\n";
}
?>
