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
    // Başvuru bilgilerini okul ve pozisyon adları ile birlikte getir
    $stmt = $db->prepare("SELECT a.*, s.school_name, p.position_name 
                         FROM applications a 
                         LEFT JOIN schools s ON a.school_id = s.id 
                         LEFT JOIN positions p ON a.position_id = p.id 
                         WHERE a.id = :id AND a.user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Başvuru bulunamadı veya bu başvuruya erişim yetkiniz yok.'
        ]);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $application
    ]);
    
} catch (PDOException $e) {
    error_log("Get application error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Başvuru bilgisi alınırken bir hata oluştu.'
    ]);
} 