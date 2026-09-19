<?php
require_once __DIR__ . '/includes/auth.php';

if (!current_user()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

header('Location: ' . BASE_URL . '/' . (is_admin() ? 'admin' : 'customer') . '/index.php');
exit;
?>