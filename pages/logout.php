<?php
require_once __DIR__ . '/../init.php';

$redirectUrl = '../index.php';

if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} elseif (isset($_SESSION['pending_auth']['user']['role'])) {
    $role = $_SESSION['pending_auth']['user']['role'];
}

if (isset($role)) {
    if ($role === 'youth') {
        $redirectUrl = 'youth-login.php';
    } elseif ($role === 'lydo') {
        $redirectUrl = 'lydo-login.php';
    } elseif ($role === 'sk_chairman') {
        $redirectUrl = 'sk-login.php';
    } elseif ($role === 'employer' || $role === 'training_provider') {
        $redirectUrl = 'provider-login.php';
    }
}

if ($user->isLoggedIn() || isset($_SESSION['pending_auth'])) {
    $user->logout();
} else {
    session_destroy();
}

header('Location: ' . $redirectUrl);
exit;
