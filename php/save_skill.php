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
    $skill_id = isset($_POST['skill_id']) && !empty($_POST['skill_id']) ? intval($_POST['skill_id']) : null;
    $skill_name = trim($_POST['skill_name']);
    $category = trim($_POST['category']);
    $level = intval($_POST['level']);
    $description = trim($_POST['description'] ?? '');
    
    // Zorunlu alanları kontrol et
    if (empty($skill_name) || empty($category)) {
        $_SESSION['error'] = "Lütfen zorunlu alanları doldurunuz.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=skills');
        exit;
    }
    
    // Seviye kontrolü
    if ($level < 1 || $level > 5) {
        $level = 3; // Varsayılan değer
    }
    
    try {
        // Yeni kayıt mı güncelleme mi?
        if ($skill_id) {
            // Önce bu yeteneğin gerçekten bu kullanıcıya ait olup olmadığını kontrol et
            $check = $db->prepare("SELECT id FROM teacher_skills WHERE id = :id AND teacher_id = :teacher_id");
            $check->execute([
                ':id' => $skill_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            if (!$check->fetch()) {
                $_SESSION['error'] = "Bu yetenek kaydına erişim yetkiniz yok veya kayıt bulunamadı.";
                redirect(BASE_URL . '/php/teacher_panel.php?action=skills');
                exit;
            }
            
            // Yetenek bilgisini güncelle
            $stmt = $db->prepare("UPDATE teacher_skills SET 
                skill_name = :skill_name,
                category = :category,
                level = :level,
                description = :description,
                updated_at = NOW()
                WHERE id = :id AND teacher_id = :teacher_id");
                
            $stmt->execute([
                ':skill_name' => $skill_name,
                ':category' => $category,
                ':level' => $level,
                ':description' => $description,
                ':id' => $skill_id,
                ':teacher_id' => $_SESSION['teacher_id']
            ]);
            
            $_SESSION['success'] = "Yetenek bilgisi başarıyla güncellenmiştir.";
        } else {
            // Yeni yetenek bilgisi ekle
            $stmt = $db->prepare("INSERT INTO teacher_skills 
                (teacher_id, skill_name, category, level, description, created_at, updated_at) 
                VALUES 
                (:teacher_id, :skill_name, :category, :level, :description, NOW(), NOW())");
                
            $stmt->execute([
                ':teacher_id' => $_SESSION['teacher_id'],
                ':skill_name' => $skill_name,
                ':category' => $category,
                ':level' => $level,
                ':description' => $description
            ]);
            
            $_SESSION['success'] = "Yetenek bilgisi başarıyla eklenmiştir.";
        }
        
        redirect(BASE_URL . '/php/teacher_panel.php?action=skills');
    } catch (PDOException $e) {
        error_log("Skill save error: " . $e->getMessage());
        $_SESSION['error'] = "Yetenek bilgisi kaydedilirken bir hata oluştu.";
        redirect(BASE_URL . '/php/teacher_panel.php?action=skills');
    }
} else {
    // POST isteği değilse ana sayfaya yönlendir
    redirect(BASE_URL . '/php/teacher_panel.php');
} 