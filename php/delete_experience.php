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
    // Önce bu deneyimin gerçekten bu kullanıcıya ait olup olmadığını kontrol et
    $check = $db->prepare("SELECT id FROM teacher_experience WHERE id = :id AND user_id = :user_id");
    $check->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$check->fetch()) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu deneyim kaydına erişim yetkiniz yok veya kayıt bulunamadı.'
        ]);
        exit;
    }
    
    // Deneyimi sil
    $stmt = $db->prepare("DELETE FROM teacher_experience WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Deneyim başarıyla silindi.'
    ]);
    
} catch (PDOException $e) {
    error_log("Experience delete error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Deneyim silinirken bir hata oluştu.'
    ]);
} 