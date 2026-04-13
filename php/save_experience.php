<?php
require_once dirname(__DIR__) . '/includes/config.php';

// Oturum kontrolü
if (!is_logged_in() || $_SESSION['role'] !== ROLE_TEACHER) {
    $_SESSION['error'] = "Bu işlemi gerçekleştirmek için yetkiniz bulunmamaktadır.";
    redirect(BASE_URL . '/php/login.php');
    exit;
}

// Form gönderildi mi kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $experience_id = isset($_POST['experience_id']) && !empty($_POST['experience_id']) ? intval($_POST['experience_id']) : null;
    $position = trim($_POST['position']);
    $company_name = trim($_POST['company_name']);
    $start_date = $_POST['start_date'];
    $is_current = isset($_POST['is_current']) ? 1 : 0;
    $end_date = $is_current ? null : $_POST['end_date'];
    $description = trim($_POST['description'] ?? '');
    
    // Zorunlu alanları kontrol et
    if (empty($position) || empty($company_name) || empty($start_date)) {
        $_SESSION['error'] = "Lütfen zorunlu alanları doldurunuz.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=experience');
        exit;
    }
    
    // Tarih kontrolü - Başlangıç tarihi bitiş tarihinden sonra olamaz
    if (!$is_current && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['error'] = "Başlangıç tarihi, bitiş tarihinden sonra olamaz.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=experience');
        exit;
    }
    
    try {
        // Yeni kayıt mı güncelleme mi?
        if ($experience_id) {
            // Önce bu deneyimin gerçekten bu kullanıcıya ait olup olmadığını kontrol et
            $check = $db->prepare("SELECT id FROM teacher_experience WHERE id = :id AND teacher_id = :teacher_id");
            $check->execute([
                ':id' => $experience_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            if (!$check->fetch()) {
                $_SESSION['error'] = "Bu deneyim kaydına erişim yetkiniz yok veya kayıt bulunamadı.";
                redirect(BASE_URL . '/php/teacher_panel.php?action=experience');
                exit;
            }
            
            // Deneyim bilgisini güncelle
            $stmt = $db->prepare("UPDATE teacher_experience SET 
                position = :position,
                company_name = :company_name,
                start_date = :start_date,
                end_date = :end_date,
                is_current = :is_current,
                description = :description,
                updated_at = NOW()
                WHERE id = :id AND teacher_id = :teacher_id");
                
            $stmt->execute([
                ':position' => $position,
                ':company_name' => $company_name,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':is_current' => $is_current,
                ':description' => $description,
                ':id' => $experience_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            $_SESSION['success'] = "Deneyim bilgisi başarıyla güncellenmiştir.";
        } else {
            // Yeni deneyim bilgisi ekle
            $stmt = $db->prepare("INSERT INTO teacher_experience 
                (teacher_id, position, company_name, start_date, end_date, is_current, description, created_at, updated_at) 
                VALUES 
                (:teacher_id, :position, :company_name, :start_date, :end_date, :is_current, :description, NOW(), NOW())");
                
            $stmt->execute([
                ':teacher_id' => $_SESSION['teacher_id'],
                ':position' => $position,
                ':company_name' => $company_name,
                ':start_date' => $start_date,
                ':end_date' => $end_date,
                ':is_current' => $is_current,
                ':description' => $description
            ]);
            
            $_SESSION['success'] = "Deneyim bilgisi başarıyla eklenmiştir.";
        }
        
        redirect(BASE_URL . '/php/teacher_panel.php?action=experience');
    } catch (PDOException $e) {
        error_log("Experience save error: " . $e->getMessage());
        $_SESSION['error'] = "Deneyim bilgisi kaydedilirken bir hata oluştu.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=experience');
    }
} else {
    // POST isteği değilse ana sayfaya yönlendir
    redirect(BASE_URL . '/php/teacher_panel.php');
}
?>