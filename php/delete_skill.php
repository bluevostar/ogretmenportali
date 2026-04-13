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
    // Önce bu yeteneğin gerçekten bu kullanıcıya ait olup olmadığını kontrol et
    $check = $db->prepare("SELECT id FROM teacher_skills WHERE id = :id AND user_id = :user_id");
    $check->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if (!$check->fetch()) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu yetenek kaydına erişim yetkiniz yok veya kayıt bulunamadı.'
        ]);
        exit;
    }
    
    // Yeteneği sil
    $stmt = $db->prepare("DELETE FROM teacher_skills WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Yetenek başarıyla silindi.'
    ]);
    
} catch (PDOException $e) {
    error_log("Skill delete error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Yetenek silinirken bir hata oluştu.'
    ]);
} 