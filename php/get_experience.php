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
    // Deneyim bilgilerini getir
    $stmt = $db->prepare("SELECT * FROM teacher_experience WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    $experience = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$experience) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Deneyim bulunamadı veya bu deneyime erişim yetkiniz yok.'
        ]);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $experience
    ]);
    
} catch (PDOException $e) {
    error_log("Get experience error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Deneyim bilgisi alınırken bir hata oluştu.'
    ]);
} 