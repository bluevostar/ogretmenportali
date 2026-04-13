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
    // Yetenek bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM teacher_skills WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    $skill = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$skill) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Yetenek bulunamadı veya bu yeteneğe erişim yetkiniz yok.'
        ]);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $skill
    ]);
    
} catch (PDOException $e) {
    error_log("Get skill error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Yetenek bilgisi alınırken bir hata oluştu.'
    ]);
} 