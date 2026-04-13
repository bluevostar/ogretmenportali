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
    $application_id = isset($_POST['application_id']) && !empty($_POST['application_id']) ? intval($_POST['application_id']) : null;
    $school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : null;
    $position_id = isset($_POST['position_id']) ? intval($_POST['position_id']) : null;
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    
    // Zorunlu alanları kontrol et
    if (empty($school_id) || empty($position_id)) {
        $_SESSION['error'] = "Lütfen okul ve pozisyon seçiniz.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=application');
        exit;
    }
    
    try {
        // Aynı okul ve pozisyona daha önce başvuru yapılmış mı kontrol et
        if (!$application_id) {
            $check = $db->prepare("SELECT id FROM applications WHERE teacher_id = :teacher_id AND school_id = :school_id AND position_id = :position_id AND status = 'pending'");
            $check->execute([
                ':teacher_id' => $_SESSION['teacher_id'],
                ':school_id' => $school_id,
                ':position_id' => $position_id
            ]);
            
            if ($check->fetch()) {
                $_SESSION['error'] = "Bu okul ve pozisyona zaten başvuru yapmışsınız ve başvurunuz halen değerlendiriliyor.";
                redirect(BASE_URL . '/php/teacher_panel.php?action=application');
                exit;
            }
        }
        
        // Yeni kayıt mı güncelleme mi?
        if ($application_id) {
            // Önce bu başvurunun gerçekten bu kullanıcıya ait olup olmadığını kontrol et
            // ve durumun "pending" olup olmadığını kontrol et
            $check = $db->prepare("SELECT id FROM applications WHERE id = :id AND teacher_id = :teacher_id AND status = 'pending'");
            $check->execute([
                ':id' => $application_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            if (!$check->fetch()) {
                $_SESSION['error'] = "Bu başvuru kaydına erişim yetkiniz yok, kayıt bulunamadı veya başvuru artık düzenlenemez durumda.";
                redirect(BASE_URL . '/php/teacher_panel.php?action=application');
                exit;
            }
            
            // Başvuru bilgisini güncelle
            $stmt = $db->prepare("UPDATE applications SET 
                school_id = :school_id,
                position_id = :position_id,
                cover_letter = :cover_letter,
                updated_at = NOW()
                WHERE id = :id AND teacher_id = :teacher_id AND status = 'pending'");
                
            $stmt->execute([
                ':school_id' => $school_id,
                ':position_id' => $position_id,
                ':cover_letter' => $cover_letter,
                ':id' => $application_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            $_SESSION['success'] = "Başvuru bilgisi başarıyla güncellenmiştir.";
        } else {
            // Yeni başvuru ekle
            $stmt = $db->prepare("INSERT INTO applications 
                (teacher_id, school_id, position_id, cover_letter, status, created_at, updated_at) 
                VALUES 
                (:teacher_id, :school_id, :position_id, :cover_letter, 'pending', NOW(), NOW())");
                
            $stmt->execute([
                ':teacher_id' => $_SESSION['teacher_id'],
                ':school_id' => $school_id,
                ':position_id' => $position_id,
                ':cover_letter' => $cover_letter
            ]);
            
            $_SESSION['success'] = "Başvurunuz başarıyla oluşturulmuştur.";
        }
        
        redirect(BASE_URL . '/php/teacher_panel.php?action=application');
    } catch (PDOException $e) {
        error_log("Application save error: " . $e->getMessage());
        $_SESSION['error'] = "Başvuru kaydedilirken bir hata oluştu.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=application');
    }
} else {
    // POST isteği değilse ana sayfaya yönlendir
    redirect(BASE_URL . '/php/teacher_panel.php');
} 