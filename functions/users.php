<?php
require_once '../config/db_connect.php';

/**
 * Yeni kullanıcı kayıt etme
 */
function registerUser($name, $surname, $email, $password, $phone, $branch = null, $role = 'teacher') {
    $conn = getDbConnection();
    
    // Verileri temizle
    $name = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, $phone);
    $branch = $branch ? mysqli_real_escape_string($conn, $branch) : null;
    $role = mysqli_real_escape_string($conn, $role);
    
    // E-posta adresi kontrolü
    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        return [
            'success' => false,
            'message' => 'Bu e-posta adresi zaten kullanılıyor.'
        ];
    }
    
    // Şifreyi hashle
    $hashed_password = md5($password); // Gerçek uygulamada daha güvenli bir hash kullanılmalı
    
    // Branş alanı SQL sorgusu için
    $branch_field = $branch ? "'$branch'" : "NULL";
    
    $sql = "INSERT INTO users (name, surname, email, password, phone, branch, role, created_at) 
            VALUES ('$name', '$surname', '$email', '$hashed_password', '$phone', $branch_field, '$role', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Kayıt başarıyla tamamlandı.',
            'user_id' => mysqli_insert_id($conn)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kayıt sırasında bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Kullanıcı girişi kontrolü
 */
function loginUser($email, $password) {
    $conn = getDbConnection();
    
    $email = mysqli_real_escape_string($conn, $email);
    $hashed_password = md5($password); // Gerçek uygulamada daha güvenli bir hash kullanılmalı
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$hashed_password'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Son giriş zamanını güncelle
        $user_id = $user['id'];
        $update_sql = "UPDATE users SET last_login = NOW() WHERE id = '$user_id'";
        mysqli_query($conn, $update_sql);
        
        return [
            'success' => true,
            'message' => 'Giriş başarılı.',
            'user' => $user
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Geçersiz e-posta veya şifre.'
        ];
    }
}

/**
 * Kullanıcı detaylarını getir
 */
function getUserDetails($user_id) {
    $conn = getDbConnection();
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Şifre alanını temizle
        unset($user['password']);
        return $user;
    }
    
    return null;
}

/**
 * Kullanıcı bilgilerini güncelle
 */
function updateUserProfile($user_id, $name, $surname, $phone, $branch = null, $cv_path = null) {
    $conn = getDbConnection();
    
    // Verileri temizle
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $name = mysqli_real_escape_string($conn, $name);
    $surname = mysqli_real_escape_string($conn, $surname);
    $phone = mysqli_real_escape_string($conn, $phone);
    
    $additional_fields = [];
    
    if ($branch !== null) {
        $branch = mysqli_real_escape_string($conn, $branch);
        $additional_fields[] = "branch = '$branch'";
    }
    
    if ($cv_path !== null) {
        $cv_path = mysqli_real_escape_string($conn, $cv_path);
        $additional_fields[] = "cv_path = '$cv_path'";
    }
    
    $additional_sql = count($additional_fields) > 0 ? ", " . implode(", ", $additional_fields) : "";
    
    $sql = "UPDATE users 
            SET name = '$name', surname = '$surname', phone = '$phone' $additional_sql
            WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Profil bilgileri güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Profil bilgileri güncellenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Kullanıcı şifresini güncelle
 */
function updateUserPassword($user_id, $current_password, $new_password) {
    $conn = getDbConnection();
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $current_hashed = md5($current_password);
    
    // Önce mevcut şifreyi kontrol et
    $check_sql = "SELECT * FROM users WHERE id = '$user_id' AND password = '$current_hashed'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) != 1) {
        return [
            'success' => false,
            'message' => 'Mevcut şifre yanlış.'
        ];
    }
    
    // Yeni şifreyi hashle
    $new_hashed = md5($new_password);
    
    $sql = "UPDATE users SET password = '$new_hashed' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Şifre başarıyla güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Şifre güncellenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Tüm kullanıcıları getir (admin için)
 */
function getAllUsers($role = null) {
    $conn = getDbConnection();
    
    $where_sql = "";
    if ($role) {
        $role = mysqli_real_escape_string($conn, $role);
        $where_sql = " WHERE role = '$role'";
    }
    
    $sql = "SELECT id, name, surname, email, phone, branch, role, created_at, last_login 
            FROM users $where_sql 
            ORDER BY surname, name ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $users = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Kullanıcı rolünü güncelle (admin için)
 */
function updateUserRole($user_id, $new_role) {
    $conn = getDbConnection();
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $new_role = mysqli_real_escape_string($conn, $new_role);
    
    // Geçerli roller: teacher, school_admin, admin
    $valid_roles = ['teacher', 'school_admin', 'admin'];
    
    if (!in_array($new_role, $valid_roles)) {
        return [
            'success' => false,
            'message' => 'Geçersiz rol belirtildi.'
        ];
    }
    
    $sql = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Kullanıcı rolü güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kullanıcı rolü güncellenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Kullanıcı sil (admin için)
 */
function deleteUser($user_id) {
    $conn = getDbConnection();
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    // Kullanıcıya ait başvuruları kontrol et
    $check_sql = "SELECT COUNT(*) as count FROM applications WHERE user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if ($check_result) {
        $row = mysqli_fetch_assoc($check_result);
        if ($row['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Bu kullanıcıya ait başvurular bulunduğu için silinemez. Önce başvuruları silin.'
            ];
        }
    }
    
    // Kullanıcının yönettiği okulları kontrol et
    $check_schools_sql = "SELECT COUNT(*) as count FROM schools WHERE manager_id = '$user_id'";
    $check_schools_result = mysqli_query($conn, $check_schools_sql);
    
    if ($check_schools_result) {
        $row = mysqli_fetch_assoc($check_schools_result);
        if ($row['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Bu kullanıcı bazı okulların yöneticisi olarak atanmış. Önce bu atamayı kaldırın.'
            ];
        }
    }
    
    $sql = "DELETE FROM users WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Kullanıcı başarıyla silindi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Kullanıcı silinirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * E-posta ile kullanıcı ara
 */
function getUserByEmail($email) {
    $conn = getDbConnection();
    
    $email = mysqli_real_escape_string($conn, $email);
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // Şifre alanını temizle
        unset($user['password']);
        return $user;
    }
    
    return null;
}

/**
 * Branşlara göre öğretmenleri getir
 */
function getTeachersByBranch($branch) {
    $conn = getDbConnection();
    
    $branch = mysqli_real_escape_string($conn, $branch);
    
    $sql = "SELECT id, name, surname, email, phone, branch, created_at, last_login 
            FROM users 
            WHERE role = 'teacher' AND branch = '$branch' 
            ORDER BY surname, name ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $teachers = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $teachers[] = $row;
        }
    }
    
    return $teachers;
}

/**
 * Tüm branşları getir
 */
function getAllBranches() {
    $conn = getDbConnection();
    
    $sql = "SELECT DISTINCT branch FROM users WHERE branch IS NOT NULL ORDER BY branch ASC";
    $result = mysqli_query($conn, $sql);
    
    $branches = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $branches[] = $row['branch'];
        }
    }
    
    return $branches;
}
?> 