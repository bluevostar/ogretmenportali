<?php
require_once '../includes/config.php';

// Oturum kontrolü
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$userId = $_SESSION['teacher_id'];
$result = ['success' => false, 'message' => 'İşlem başarısız'];

// Debug için log dosyasına yazma
error_log("Avatar güncelleme işlemi başladı. Kullanıcı ID: " . $userId);
error_log("POST verisi: " . print_r($_POST, true));
error_log("Mevcut SESSION değerleri: " . print_r($_SESSION, true));

// Varsayılan avatar seçimi
if (isset($_POST['avatar_type']) && !empty($_POST['avatar_type'])) {
    $avatarType = $_POST['avatar_type'];
    
    error_log("Seçilen avatar tipi: " . $avatarType);
    
    // Cinsiyet bilgisini güncelle
    $query = "UPDATE users SET gender = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$avatarType, $userId])) {
        error_log("Cinsiyet bilgisi veritabanında güncellendi: " . $avatarType);
        
        // Varsa önceki yüklenen özel fotoğrafı sil
        $query = "SELECT profile_photo FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $oldPhoto = $stmt->fetchColumn();
        
        if ($oldPhoto && file_exists('../' . $oldPhoto) && strpos($oldPhoto, 'uploads/profile_photos/') === 0) {
            unlink('../' . $oldPhoto);
            error_log("Eski profil fotoğrafı silindi: " . $oldPhoto);
        }
        
        // Profil fotoğrafı alanını temizle
        $query = "UPDATE users SET profile_photo = NULL WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        // Session değişkenlerini güncelle
        $_SESSION['gender'] = $avatarType;
        unset($_SESSION['profile_photo']);
        
        error_log("SESSION güncellendi: gender=" . $_SESSION['gender']);
        
        $result = ['success' => true, 'message' => 'Avatar başarıyla güncellendi'];
    } else {
        error_log("Avatar güncellenirken veritabanı hatası: " . print_r($stmt->errorInfo(), true));
        $result = ['success' => false, 'message' => 'Avatar güncellenirken bir hata oluştu'];
    }
}
// Özel fotoğraf yükleme
elseif (isset($_FILES['custom_photo']) && $_FILES['custom_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['custom_photo'];
    
    error_log("Özel fotoğraf yükleme başladı. Dosya bilgileri: " . print_r($file, true));
    
    // Dosya kontrolleri
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Geçersiz dosya türü: " . $file['type']);
        echo json_encode(['success' => false, 'message' => 'Sadece JPG, PNG, GIF ve WEBP dosyaları yüklenebilir']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        error_log("Dosya boyutu çok büyük: " . $file['size']);
        echo json_encode(['success' => false, 'message' => 'Dosya boyutu 5MB\'dan büyük olamaz']);
        exit;
    }
    
    // Dosya yükleme klasörü kontrolü
    $uploadDir = '../uploads/profile_photos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        error_log("Yükleme klasörü oluşturuldu: " . $uploadDir);
    }
    
    // Benzersiz dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log("Dosya başarıyla yüklendi: " . $filepath);
        
        // Eski profil fotoğrafını sil
        $query = "SELECT profile_photo FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $oldPhoto = $stmt->fetchColumn();
        
        if ($oldPhoto && file_exists('../' . $oldPhoto) && strpos($oldPhoto, 'uploads/profile_photos/') === 0) {
            unlink('../' . $oldPhoto);
            error_log("Eski profil fotoğrafı silindi: " . $oldPhoto);
        }
        
        // Veritabanını güncelle
        $relativePath = 'uploads/profile_photos/' . $filename;
        $query = "UPDATE users SET profile_photo = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$relativePath, $userId])) {
            // Session değişkenini güncelle
            $_SESSION['profile_photo'] = $relativePath;
            error_log("SESSION güncellendi: profile_photo=" . $_SESSION['profile_photo']);
            
            $result = ['success' => true, 'message' => 'Profil fotoğrafı güncellendi'];
        } else {
            error_log("Profil fotoğrafı güncellenirken veritabanı hatası: " . print_r($stmt->errorInfo(), true));
            $result = ['success' => false, 'message' => 'Veritabanı güncellenirken hata oluştu'];
        }
    } else {
        error_log("Dosya yükleme hatası: " . error_get_last()['message']);
        $result = ['success' => false, 'message' => 'Dosya yüklenirken hata oluştu'];
    }
} else {
    error_log("Herhangi bir avatar seçilmedi veya dosya yüklenmedi");
    $result = ['success' => false, 'message' => 'Lütfen bir avatar seçin veya fotoğraf yükleyin'];
}

error_log("İşlem sonucu: " . print_r($result, true));
echo json_encode($result);
?> 