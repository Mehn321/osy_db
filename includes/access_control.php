<?php

/**
 * Access control helpers for RBAC enforcement.
 */

function requireLogin()
{
    global $user;
    if (!isset($user) || !$user->isLoggedIn()) {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        header('Location: ' . ($basePath === '/' ? '' : $basePath) . '/pages/login.php');
        exit;
    }
}

function requireRole($roles)
{
    global $user;
    if (!isset($user) || !$user->isLoggedIn()) {
        header('Location: pages/login.php');
        exit;
    }

    if (is_string($roles)) {
        $roles = [$roles];
    }

    $currentRole = $_SESSION['role'] ?? null;
    if (!in_array($currentRole, $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied.';
        exit;
    }
}

function requireAnyRole(array $roles)
{
    requireRole($roles);
}

function currentUser()
{
    global $user;
    return isset($user) ? $user->getCurrentUser() : null;
}

function isLYDO()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lydo';
}

function isSKChairman()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'sk_chairman';
}

function isYouth()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'youth';
}

function isEmployer()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

function isTrainingProvider()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'training_provider';
}

function authorizeBarangay($barangay)
{
    if (isSKChairman()) {
        $assigned = $_SESSION['barangay'] ?? null;
        if (!$assigned || strcasecmp(trim($barangay), trim($assigned)) !== 0) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied.';
            exit;
        }
    }
}
