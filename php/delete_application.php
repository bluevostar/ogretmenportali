<?php
require_once dirname(__DIR__) . '/includes/config.php';

// Oturum kontrolü
if (!is_logged_in() || $_SESSION['role'] !== ROLE_TEACHER) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Bu işlemi gerçekleştirmek için yetkiniz bulunmamaktadır.'
    ]);
    exit;
}

// ID parametresi kontrol et
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Geçersiz istek. ID parametresi gerekli.'
    ]);
    exit;
}

$id = intval($_GET['id']);

try {
    // Önce bu başvurunun gerçekten bu kullanıcıya ait olup olmadığını kontrol et
    // ve durumun "pending" olup olmadığını kontrol et (sadece beklemede olan başvurular silinebilir)
    $check = $db->prepare("SELECT id FROM applications WHERE id = :id AND user_id = :user_id AND status = 'pending'");
    $check->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$check->fetch()) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu başvuru kaydına erişim yetkiniz yok, kayıt bulunamadı veya başvuru artık silinemez durumda.'
        ]);
        exit;
    }
    
    // Başvuruyu sil
    $stmt = $db->prepare("DELETE FROM applications WHERE id = :id AND user_id = :user_id AND status = 'pending'");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Başvuru başarıyla silindi.'
    ]);
    
} catch (PDOException $e) {
    error_log("Application delete error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Başvuru silinirken bir hata oluştu.'
    ]);
} 