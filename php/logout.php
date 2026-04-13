<?php
require_once dirname(__DIR__) . '/includes/config.php';
// Oturum bilgilerini temizle
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fromAdminPanel = isset($_SESSION['role']) && in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_COUNTRY_ADMIN]);

$_SESSION = array();

// Oturum çerezini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
// Oturumu sonlandır
session_destroy();

// Kullanıcıyı login sayfasına yönlendir
if ($fromAdminPanel) {
    redirect(BASE_URL . '/php/admin_login.php');
} else {
    redirect(BASE_URL . '/php/login.php');
}

?> 