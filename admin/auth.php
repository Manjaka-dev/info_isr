<?php

// Session centralisee pour l'admin
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function adminBaseUrl() {
    return '/optim/info_isr/admin';
}

function getAdminCredentials() {
    $user = getenv('ADMIN_USER') ?: 'manjaka';
    $pass = getenv('ADMIN_PASS');

    if ($pass === false || $pass === '') {
        $pass = 'admin';
    }

    return [$user, $pass];
}

function isAdminAuthenticated() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        header('Location: ' . adminBaseUrl() . '/login.php');
        exit;
    }
}

function attemptAdminLogin($username, $password) {
    list($validUser, $validPass) = getAdminCredentials();

    if (hash_equals($validUser, $username) && hash_equals($validPass, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $validUser;
        return true;
    }

    return false;
}

function adminLogout() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

