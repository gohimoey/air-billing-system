<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function require_login() {
    if (!current_user()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}

function flash($key = null) {
    if ($key === null) {
        $all = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $all;
    }
    $v = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $v;
}

function set_flash($key, $value) {
    $_SESSION['flash'][$key] = $value;
}