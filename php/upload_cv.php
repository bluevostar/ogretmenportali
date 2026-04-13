<?php
require_once '../includes/config.php';

// Oturum kontrolü
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_file'])) {
    $file = $_FILES['cv_file'];
    $userId = $_SESSION['teacher_id'];
    
    // Dosya kontrolleri
    if ($file['type'] !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'Sadece PDF dosyaları yüklenebilir']);
        exit;
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        echo json_encode(['success' => false, 'message' => 'Dosya boyutu 10MB\'dan büyük olamaz']);
        exit;
    }
    
    // Dosya yükleme klasörü kontrolü
    $uploadDir = '../uploads/cv/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Benzersiz dosya adı oluştur
    $filename = 'cv_' . $userId . '_' . time() . '.pdf';
    $filepath = $uploadDir . $filename;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Eski CV'yi sil
        $query = "SELECT cv_file FROM teacher_profiles WHERE teacher_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        $oldCV = $stmt->fetchColumn();
        
        if ($oldCV && file_exists('../' . $oldCV)) {
            unlink('../' . $oldCV);
        }
        
        // Veritabanını güncelle
        $relativePath = 'uploads/cv/' . $filename;
        
        // Önce teacher_profiles kaydı var mı kontrol et
        $query = "SELECT id FROM teacher_profiles WHERE teacher_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            $query = "UPDATE teacher_profiles SET cv_file = ? WHERE teacher_id = ?";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([$relativePath, $userId]);
        } else {
            $query = "INSERT INTO teacher_profiles (teacher_id, cv_file) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([$userId, $relativePath]);
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'CV başarıyla yüklendi']);
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