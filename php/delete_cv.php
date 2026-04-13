<?php
require_once '../includes/config.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // CV dosyasının yolunu al
    $query = "SELECT cv_file FROM teacher_profiles WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $cvFile = $stmt->fetchColumn();
    
    if ($cvFile && file_exists('../' . $cvFile)) {
        // Dosyayı sil
        unlink('../' . $cvFile);
    }
    
    // Veritabanını güncelle
    $query = "UPDATE teacher_profiles SET cv_file = NULL WHERE user_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$userId])) {
        echo json_encode(['success' => true, 'message' => 'CV başarıyla silindi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'CV silinirken bir hata oluştu']);
    }
} catch (PDOException $e) {
    error_log('CV silme hatası: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>