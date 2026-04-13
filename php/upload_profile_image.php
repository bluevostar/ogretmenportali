<?php
require_once '../includes/config.php';

// Oturum kontrolü
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $userId = $_SESSION['teacher_id'];
    
    // Dosya kontrolleri
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Sadece JPG, PNG ve GIF dosyaları yüklenebilir']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        echo json_encode(['success' => false, 'message' => 'Dosya boyutu 5MB\'dan büyük olamaz']);
        exit;
    }
    
    // Dosya yükleme klasörü kontrolü
    $uploadDir = '../uploads/profile_images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Benzersiz dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Eski profil fotoğrafını sil
        $query = "SELECT profile_image FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $oldImage = $stmt->fetchColumn();
        
        if ($oldImage && file_exists('../' . $oldImage)) {
            unlink('../' . $oldImage);
        }
        
        // Veritabanını güncelle
        $relativePath = 'uploads/profile_images/' . $filename;
        $query = "UPDATE users SET profile_image = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$relativePath, $userId])) {
            echo json_encode(['success' => true, 'message' => 'Profil fotoğrafı güncellendi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Veritabanı güncellenirken hata oluştu']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dosya yüklenirken hata oluştu']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
}
?>