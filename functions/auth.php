<?php
session_start();
require_once '../config/db_connect.php';

/**
 * Kullanıcı girişi 
 */
function loginUser($email, $password) {
    $conn = getDbConnection();
    
    $email = mysqli_real_escape_string($conn, $email);
    $password = md5($password); // Şifreyi MD5 ile hash'le
    
    $sql = "SELECT id, name, surname, email, role FROM teachers WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Session'a kullanıcı bilgilerini kaydet
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['surname'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        return true;
    }
    
    return false;
}

/**
 * Kullanıcı kaydı
 */
function registerUser($name, $surname, $email, $password, $branch, $phone, $cv_path, $role = 'teacher') {
    $conn = getDbConnection();
    
    // Güvenlik için verileri temizle
    $name = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $email = mysqli_real_escape_string($conn, $email);
    $hashed_password = md5($password); // Şifreyi MD5 ile hash'le
    $branch = mysqli_real_escape_string($conn, $branch);
    $phone = mysqli_real_escape_string($conn, $phone);
    $cv_path = mysqli_real_escape_string($conn, $cv_path);
    $role = mysqli_real_escape_string($conn, $role);
    
    // Kullanıcı zaten var mı kontrol et
    $check_sql = "SELECT id FROM teachers WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        return [
            'success' => false,
            'message' => 'Bu e-posta adresi zaten kullanılıyor.'
        ];
    }
    
    // Kullanıcıyı kaydet
    $sql = "INSERT INTO teachers (name, surname, email, password, branch, phone, cv_path, role, created_at) 
            VALUES ('$name', '$surname', '$email', '$hashed_password', '$branch', '$phone', '$cv_path', '$role', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Kayıt başarılı! Giriş yapabilirsiniz.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kayıt sırasında bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Kullanıcı çıkışı
 */
function logoutUser() {
    // Session'ı temizle
    session_unset();
    session_destroy();
    
    return true;
}

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Kullanıcı rolünü kontrol et
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['role'] === $role;
}

/**
 * Kullanıcı bilgilerini getir
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['name'],
        'surname' => $_SESSION['surname'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}
?> 