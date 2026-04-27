<?php
require_once __DIR__ . '/../init.php';

if ($user->isLoggedIn()) {
    $user->logout();
}

header('Location: login.php');
exit;
