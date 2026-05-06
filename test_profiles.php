<?php
require_once __DIR__ . '/init.php';
$_SESSION['user_id'] = 1; // fake login
$_SESSION['role'] = 'admin';

ob_start();
require __DIR__ . '/pages/profiles.php';
$output = ob_get_clean();

if (error_get_last()) {
    print_r(error_get_last());
}

echo substr($output, 0, 500) . "\n...\n" . substr($output, -500);
