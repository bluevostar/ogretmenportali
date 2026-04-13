<?php
// Session başlat
session_start();
require_once '../config/config.php';
require_once '../functions/db.php';
require_once '../functions/auth.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['teacher_id'])) {
    header('Location: ' . BASE_URL . '/login.php?status=error&message=' . urlencode("Bu sayfaya erişmek için giriş yapmalısınız."));
    exit;
}

// POST verisi var mı kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_SESSION['teacher_id'];
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Tüm alanların doldurulduğunu kontrol et
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Tüm alanları doldurmalısınız."));
        exit;
    }
    
    // Yeni şifrelerin eşleştiğini kontrol et
    if ($new_password !== $confirm_password) {
        header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Yeni şifreler eşleşmiyor."));
        exit;
    }
    
    // Şifre karmaşıklığını kontrol et
    if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Şifre en az 8 karakter olmalı ve büyük harf, küçük harf ve rakam içermelidir."));
        exit;
    }
    
    // Veritabanı bağlantısı oluştur
    $conn = get_db_connection();
    
    // Kullanıcının mevcut şifresini kontrol et
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stored_password = $user['password'];
        
        // Mevcut şifreyi doğrula
        if (md5($current_password) !== $stored_password) {
            header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Mevcut şifre doğru değil."));
            exit;
        }
        
        // Yeni şifreyi kaydet (md5 ile hash'leyerek)
        $hashed_password = md5($new_password);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $teacher_id);
        
        if ($update_stmt->execute()) {
            header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=success&message=' . urlencode("Şifreniz başarıyla değiştirildi."));
            exit;
        } else {
            header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Şifre güncellenirken bir hata oluştu: " . $conn->error));
            exit;
        }
    } else {
        header('Location: ' . BASE_URL . '/teacher_dashboard.php?page=settings&status=error&message=' . urlencode("Kullanıcı bulunamadı."));
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    // POST isteği değilse ana sayfaya yönlendir
    header('Location: ' . BASE_URL);
    exit;
}
?> 