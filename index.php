<?php
require_once __DIR__ . '/init.php';

// Redirect to login if not logged in
if (!$user->isLoggedIn()) {
    header('Location: pages/login.php');
    exit;
}

// Redirect to dashboard if logged in
header('Location: pages/dashboard.php');
exit;
