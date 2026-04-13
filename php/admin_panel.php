<?php
// Debug için hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Normal sayfa yüklemesi
require_once dirname(__DIR__) . '/includes/config.php';

// Yetkilendirme kontrolü
if (!is_logged_in()) {
    redirect(BASE_URL . '/php/admin_login.php');
}
// Rol tabanlı ViewModel ve bileşen yükleme
$userRole = $_SESSION['role'];
$baseComponentPath = dirname(__DIR__) . '/components/';
$viewModel = null;
$componentPath = '';

if ($userRole === ROLE_ADMIN) {
    require_once dirname(__DIR__) . '/models/viewmodels/AdminViewModel.php';
    $viewModel = new AdminViewModel($db);
    $componentPath = $baseComponentPath . 'admin/';
} elseif ($userRole === ROLE_COUNTRY_ADMIN) {
    require_once dirname(__DIR__) . '/models/viewmodels/CountryAdminViewModel.php';
    $viewModel = new CountryAdminViewModel($db);
    $componentPath = $baseComponentPath . 'country_admin/';
} else {
    // Diğer roller için veya yetkisizse yönlendir
    redirect(BASE_URL . '/php/unauthorized.php');
}

// Sayfa işlemleri - action değişkenini al
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Debug çıktısı kapatıldı: JSON dönen GET isteklerini bozuyordu

// Gelen parametreleri logla
error_log("Country Admin Panel - Gelen parametreler: " . print_r($_GET, true));

// Excel export işlemi
if ($action === 'export' && isset($_POST['export_data'])) {
    $viewModel->exportToExcel();
}

// Başvuru onaylama/reddetme işlemi
if (isset($_POST['approve_application'])) {
    $applicationId = $_POST['application_id'];
    $viewModel->approveApplication($applicationId);
    redirect(BASE_URL . '/php/admin_panel.php?action=applications&status=success&message=Başvuru onaylandı');
}

// Başvuru reddetme işlemi
if (isset($_POST['reject_application'])) {
    $applicationId = $_POST['application_id'];
    $rejectionReason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;
    $viewModel->rejectApplication($applicationId, $rejectionReason);
    redirect(BASE_URL . '/php/admin_panel.php?action=applications&status=success&message=Başvuru reddedildi');
}

// Form tabanlı branş silme işlemi (country_admin için)
if (isset($_POST['delete_branch']) && isset($_POST['branch_id'])) {
    $branchId = (int)$_POST['branch_id'];
    header('Content-Type: application/json');
    if ($branchId > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM branches WHERE id = ?");
            $ok = $stmt->execute([$branchId]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Branş başarıyla silindi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Branş silinirken bir hata oluştu']);
            }
        } catch (PDOException $e) {
            error_log("Branş silme hatası: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz branş ID']);
    }
    exit;
}

// Kullanıcı yönetimi işlemleri
if ($action === 'add_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $userData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'role' => $_POST['role'],
        'phone' => isset($_POST['phone']) ? $_POST['phone'] : '',
        'address' => isset($_POST['address']) ? $_POST['address'] : '',
        'school_id' => $_POST['school_id']
    ];
    
    // Öğretmen ise branş bilgilerini ekle
    if ($userData['role'] === ROLE_TEACHER) {
        $userData['branch'] = $_POST['branch'];
        $userData['education'] = $_POST['education'];
        $userData['experience'] = $_POST['experience'];
    }
    
    // Kullanıcı oluştur
    $result = $viewModel->addUser($userData, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=users&status=success&message=Kullanıcı başarıyla eklendi.');
    } else {
        $_SESSION['error'] = 'Kullanıcı eklenirken bir hata oluştu. E-posta adresi zaten kullanılıyor olabilir.';
    }
}

if ($action === 'edit_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    
    // Form verilerini al
    $userData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'role' => $_POST['role'],
        'phone' => isset($_POST['phone']) ? $_POST['phone'] : '',
        'address' => isset($_POST['address']) ? $_POST['address'] : '',
        'school_id' => $_POST['school_id']
    ];
    
    // Şifre değiştirilecek mi?
    if (!empty($_POST['password'])) {
        $userData['password'] = $_POST['password'];
    }
    
    // Öğretmen ise branş bilgilerini ekle
    if ($userData['role'] === ROLE_TEACHER) {
        $userData['branch'] = $_POST['branch'];
        $userData['education'] = $_POST['education'];
        $userData['experience'] = $_POST['experience'];
    }
    
    // Kullanıcı güncelle
    $result = $viewModel->updateUser($userId, $userData, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=users&status=success&message=Kullanıcı başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Kullanıcı güncellenirken bir hata oluştu. E-posta adresi zaten kullanılıyor olabilir.';
    }
}

// Ayarlar işlemleri
if ($action === 'update_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_title' => $_POST['site_title'],
        'site_description' => $_POST['site_description'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'contact_address' => $_POST['contact_address'],
        'facebook_url' => $_POST['facebook_url'],
        'twitter_url' => $_POST['twitter_url'],
        'instagram_url' => $_POST['instagram_url'],
        'linkedin_url' => $_POST['linkedin_url']
    ];
    
    // Logo yükleme işlemi
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/uploads/logos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        $fileName = 'logo_' . uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $targetPath)) {
            $settings['site_logo'] = 'uploads/logos/' . $fileName;
        }
    }
    
    $result = $viewModel->updateGeneralSettings($settings, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Genel ayarlar başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Genel ayarlar güncellenirken bir hata oluştu.';
    }
}

if ($action === 'update_email_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => $_POST['smtp_port'],
        'smtp_username' => $_POST['smtp_username'],
        'smtp_password' => $_POST['smtp_password'],
        'sender_email' => $_POST['sender_email'],
        'sender_name' => $_POST['sender_name'],
        'smtp_encryption' => isset($_POST['smtp_encryption']) ? '1' : '0'
    ];
    
    $result = $viewModel->updateEmailSettings($settings, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=E-posta ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'E-posta ayarları güncellenirken bir hata oluştu.';
    }
}

if ($action === 'update_security_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'password_min_length' => isset($_POST['password_min_length']),
        'password_uppercase' => isset($_POST['password_uppercase']),
        'password_special' => isset($_POST['password_special']),
        'session_timeout' => isset($_POST['session_timeout']),
        'login_attempts' => isset($_POST['login_attempts']),
        'two_factor_auth' => isset($_POST['two_factor_auth'])
    ];
    
    $result = $viewModel->updateSecuritySettings($settings, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Güvenlik ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Güvenlik ayarları güncellenirken bir hata oluştu.';
    }
}

if ($action === 'update_system_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'maintenance_mode' => isset($_POST['maintenance_mode']),
        'error_reporting' => isset($_POST['error_reporting']),
        'max_upload_size' => $_POST['max_upload_size'],
        'allowed_file_types' => $_POST['allowed_file_types'],
        'auto_backup' => isset($_POST['auto_backup']),
        'backup_frequency' => $_POST['backup_frequency']
    ];
    
    $result = $viewModel->updateSystemSettings($settings, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Sistem ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Sistem ayarları güncellenirken bir hata oluştu.';
    }
}

if ($action === 'update_homepage_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'homepage_hero_title' => $_POST['homepage_hero_title'] ?? '',
        'homepage_hero_subtitle' => $_POST['homepage_hero_subtitle'] ?? '',
        'homepage_stats_teachers' => $_POST['homepage_stats_teachers'] ?? '',
        'homepage_stats_schools' => $_POST['homepage_stats_schools'] ?? '',
        'homepage_stats_success_rate' => $_POST['homepage_stats_success_rate'] ?? '',
        'homepage_features_title' => $_POST['homepage_features_title'] ?? '',
        'homepage_features_subtitle' => $_POST['homepage_features_subtitle'] ?? '',
        'homepage_feature1_title' => $_POST['homepage_feature1_title'] ?? '',
        'homepage_feature1_description' => $_POST['homepage_feature1_description'] ?? '',
        'homepage_feature2_title' => $_POST['homepage_feature2_title'] ?? '',
        'homepage_feature2_description' => $_POST['homepage_feature2_description'] ?? '',
        'homepage_feature3_title' => $_POST['homepage_feature3_title'] ?? '',
        'homepage_feature3_description' => $_POST['homepage_feature3_description'] ?? '',
        'homepage_feature4_title' => $_POST['homepage_feature4_title'] ?? '',
        'homepage_feature4_description' => $_POST['homepage_feature4_description'] ?? '',
        'homepage_stats_title' => $_POST['homepage_stats_title'] ?? '',
        'homepage_stats_teachers_label' => $_POST['homepage_stats_teachers_label'] ?? '',
        'homepage_stats_schools_label' => $_POST['homepage_stats_schools_label'] ?? '',
        'homepage_stats_success_label' => $_POST['homepage_stats_success_label'] ?? '',
        'homepage_advantages_title' => $_POST['homepage_advantages_title'] ?? '',
        'homepage_advantages_subtitle' => $_POST['homepage_advantages_subtitle'] ?? '',
        'homepage_advantage1_title' => $_POST['homepage_advantage1_title'] ?? '',
        'homepage_advantage1_description' => $_POST['homepage_advantage1_description'] ?? '',
        'homepage_advantage2_title' => $_POST['homepage_advantage2_title'] ?? '',
        'homepage_advantage2_description' => $_POST['homepage_advantage2_description'] ?? '',
        'homepage_advantage3_title' => $_POST['homepage_advantage3_title'] ?? '',
        'homepage_advantage3_description' => $_POST['homepage_advantage3_description'] ?? '',
        'homepage_cta_title' => $_POST['homepage_cta_title'] ?? '',
        'homepage_cta_subtitle' => $_POST['homepage_cta_subtitle'] ?? '',
        'homepage_cta_register_text' => $_POST['homepage_cta_register_text'] ?? '',
        'homepage_cta_login_text' => $_POST['homepage_cta_login_text'] ?? ''
    ];
    
    $result = $viewModel->updateHomepageSettings($settings, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Ana sayfa ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Ana sayfa ayarları güncellenirken bir hata oluştu.';
    }
}

// Okul türü - branş eşleştirme (form tabanlı)
if (in_array($action, ['branches', 'school_types']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_school_type_branch_map'])) {
    $redirectAction = $action === 'school_types' ? 'school_types' : 'branches';
    $schoolType = trim($_POST['school_type'] ?? '');
    $branchIds = isset($_POST['branch_ids']) && is_array($_POST['branch_ids']) ? $_POST['branch_ids'] : [];

    if ($schoolType === '' || empty($branchIds)) {
        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=error&message=Okul türü ve en az bir branş seçiniz');
    }

    try {
        // Tablo yoksa oluştur (idempotent)
        $db->exec("
            CREATE TABLE IF NOT EXISTS school_type_branch_map (
                id INT(11) NOT NULL AUTO_INCREMENT,
                school_type VARCHAR(100) NOT NULL,
                branch_id INT(11) NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_school_type_branch (school_type, branch_id),
                KEY idx_school_type (school_type),
                KEY idx_branch_id (branch_id),
                CONSTRAINT fk_school_type_branch_map_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $insert = $db->prepare("
            INSERT INTO school_type_branch_map (school_type, branch_id, is_active)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)
        ");

        foreach ($branchIds as $branchId) {
            $branchId = (int)$branchId;
            if ($branchId > 0) {
                $insert->execute([$schoolType, $branchId]);
            }
        }

        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=success&message=Eşleştirme kaydedildi');
    } catch (Throwable $t) {
        error_log('save_school_type_branch_map error: ' . $t->getMessage());
        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=error&message=Eşleştirme kaydedilemedi');
    }
}

if (in_array($action, ['branches', 'school_types']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_school_type_branch_map'])) {
    $redirectAction = $action === 'school_types' ? 'school_types' : 'branches';
    $mapId = (int)($_POST['map_id'] ?? 0);
    if ($mapId <= 0) {
        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=error&message=Geçersiz eşleştirme ID');
    }

    try {
        $stmt = $db->prepare("DELETE FROM school_type_branch_map WHERE id = ?");
        $stmt->execute([$mapId]);
        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=success&message=Eşleştirme silindi');
    } catch (Throwable $t) {
        error_log('delete_school_type_branch_map error: ' . $t->getMessage());
        redirect(BASE_URL . '/php/admin_panel.php?action=' . $redirectAction . '&status=error&message=Eşleştirme silinemedi');
    }
}

if ($action === 'clear_cache' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $viewModel->clearCache();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

// AJAX: Kullanıcı durumunu değiştir
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (isset($data['action']) && $data['action'] === 'toggle_user_status') {
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $newStatus = isset($data['status']) && in_array($data['status'], ['active','inactive']) ? $data['status'] : null;
        header('Content-Type: application/json');
        if ($userId > 0 && $newStatus) {
            try {
                // Önce status kolonu varsa güncellemeyi dene
                $ok = false;
                try {
                    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $ok = $stmt->execute([$newStatus, $userId]);
                } catch (PDOException $pe) {
                    // status kolonu yoksa (42S22), active kolonuna geri dön
                    if ($pe->getCode() === '42S22') {
                        $stmt = $db->prepare("UPDATE users SET active = ? WHERE id = ?");
                        $ok = $stmt->execute([$newStatus === 'active' ? 1 : 0, $userId]);
                    } else {
                        throw $pe;
                    }
                }
                echo json_encode(['success' => (bool)$ok]);
            } catch (Throwable $t) {
                error_log('toggle_user_status error: ' . $t->getMessage());
                echo json_encode(['success' => false, 'message' => 'Durum güncellenemedi']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz parametre']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'toggle_branch_status') {
        $branchId = isset($data['branch_id']) ? (int)$data['branch_id'] : 0;
        $newStatus = isset($data['status']) && in_array($data['status'], ['active','inactive']) ? $data['status'] : null;
        header('Content-Type: application/json');
        if ($branchId > 0 && $newStatus) {
            $ok = (new AdminModel($db))->updateBranch($branchId, ['status' => $newStatus]);
            echo json_encode(['success' => (bool)$ok]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz parametre']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'toggle_school_status') {
        $schoolId = isset($data['school_id']) ? (int)$data['school_id'] : 0;
        $newStatus = isset($data['status']) && in_array($data['status'], ['active','inactive']) ? $data['status'] : null;
        header('Content-Type: application/json');
        if ($schoolId > 0 && $newStatus) {
            $ok = $viewModel->updateSchoolStatus($schoolId, $newStatus);
            echo json_encode(['success' => (bool)$ok]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz parametre']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_school') {
        $schoolId = isset($data['school_id']) ? (int)$data['school_id'] : 0;
        header('Content-Type: application/json');
        if ($schoolId > 0) {
            try {
                // Okulu veritabanından sil
                $stmt = $db->prepare("DELETE FROM schools WHERE id = ?");
                $ok = $stmt->execute([$schoolId]);
                if ($ok) {
                    echo json_encode(['success' => true, 'message' => 'Okul başarıyla silindi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Okul silinirken bir hata oluştu']);
                }
            } catch (PDOException $e) {
                error_log("Okul silme hatası: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Okul silme hatası: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Beklenmeyen hata: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_selected_schools') {
        $schoolIds = isset($data['school_ids']) && is_array($data['school_ids']) ? $data['school_ids'] : [];
        header('Content-Type: application/json');
        if (empty($schoolIds)) {
            echo json_encode(['success' => false, 'message' => 'Silinecek okul seçilmedi']);
            exit;
        }
        
        try {
            // ID'leri integer'a çevir ve temizle
            $schoolIds = array_map('intval', $schoolIds);
            $schoolIds = array_filter($schoolIds, function($id) { return $id > 0; });
            
            if (empty($schoolIds)) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID\'leri']);
                exit;
            }
            
            // Placeholder'ları oluştur
            $placeholders = str_repeat('?,', count($schoolIds) - 1) . '?';
            $stmt = $db->prepare("DELETE FROM schools WHERE id IN ($placeholders)");
            $ok = $stmt->execute($schoolIds);
            
            if ($ok) {
                $deletedCount = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$deletedCount okul başarıyla silindi", 'deleted_count' => $deletedCount]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Okullar silinirken bir hata oluştu']);
            }
        } catch (PDOException $e) {
            error_log("Toplu okul silme hatası: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        } catch (Exception $e) {
            error_log("Toplu okul silme hatası: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Beklenmeyen hata: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_selected_applications') {
        $applicationIds = isset($data['application_ids']) && is_array($data['application_ids']) ? $data['application_ids'] : [];
        header('Content-Type: application/json');
        if (!validate_csrf_request()) {
            echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
            exit;
        }
        if (empty($applicationIds)) {
            echo json_encode(['success' => false, 'message' => 'Silinecek başvuru seçilmedi']);
            exit;
        }

        try {
            $applicationIds = array_map('intval', $applicationIds);
            $applicationIds = array_filter($applicationIds, function ($id) { return $id > 0; });

            if (empty($applicationIds)) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz başvuru ID\'leri']);
                exit;
            }

            $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
            $stmt = $db->prepare("DELETE FROM applications WHERE id IN ($placeholders)");
            $ok = $stmt->execute($applicationIds);

            if ($ok) {
                $deletedCount = $stmt->rowCount();
                echo json_encode([
                    'success' => true,
                    'message' => "$deletedCount başvuru başarıyla silindi",
                    'deleted_count' => $deletedCount
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Başvurular silinirken bir hata oluştu']);
            }
        } catch (PDOException $e) {
            error_log('Toplu başvuru silme hatası: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_selected_school_admins') {
        $adminIds = isset($data['admin_ids']) && is_array($data['admin_ids']) ? $data['admin_ids'] : [];
        header('Content-Type: application/json');
        if (empty($adminIds)) {
            echo json_encode(['success' => false, 'message' => 'Silinecek yönetici seçilmedi']);
            exit;
        }
        try {
            $adminIds = array_map('intval', $adminIds);
            $adminIds = array_filter($adminIds, function($id){ return $id > 0; });
            if (empty($adminIds)) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID\'leri']);
                exit;
            }
            $placeholders = str_repeat('?,', count($adminIds) - 1) . '?';
            $stmt = $db->prepare("DELETE FROM users WHERE role = ? AND id IN ($placeholders)");
            $ok = $stmt->execute(array_merge([ROLE_SCHOOL_ADMIN], $adminIds));
            if ($ok) {
                $deletedCount = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$deletedCount yönetici silindi", 'deleted_count' => $deletedCount]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Yöneticiler silinirken bir hata oluştu']);
            }
        } catch (PDOException $e) {
            error_log('Toplu okul yöneticisi silme hatası: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_user') {
        header('Content-Type: application/json');
        $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
            exit;
        }
        try {
            // Önce teacher_profiles'den sil (eğer varsa)
            try {
                $stmt = $db->prepare("DELETE FROM teacher_profiles WHERE user_id = ?");
                $stmt->execute([$userId]);
            } catch (PDOException $pe) {
                error_log('Teacher profile delete error: ' . $pe->getMessage());
            }
            
            // Sonra users tablosundan sil
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $ok = $stmt->execute([$userId]);
            if ($ok && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla silindi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kullanıcı silinemedi veya bulunamadı']);
            }
        } catch (PDOException $e) {
            error_log('User delete error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_selected_users') {
        header('Content-Type: application/json');
        $userIds = isset($data['user_ids']) && is_array($data['user_ids']) ? array_map('intval', $data['user_ids']) : [];
        if (empty($userIds)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID\'leri']);
            exit;
        }
        try {
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            
            // Önce teacher_profiles'den sil (eğer varsa)
            try {
                $stmt = $db->prepare("DELETE FROM teacher_profiles WHERE user_id IN ($placeholders)");
                $stmt->execute($userIds);
            } catch (PDOException $pe) {
                error_log('Teacher profiles delete error: ' . $pe->getMessage());
            }
            
            // Sonra users tablosundan sil
            $stmt = $db->prepare("DELETE FROM users WHERE id IN ($placeholders)");
            $ok = $stmt->execute($userIds);
            $deletedCount = $stmt->rowCount();
            if ($ok && $deletedCount > 0) {
                echo json_encode(['success' => true, 'message' => "$deletedCount kullanıcı başarıyla silindi"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kullanıcılar silinemedi']);
            }
        } catch (PDOException $e) {
            error_log('Bulk user delete error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_country_admin') {
        header('Content-Type: application/json');
        $adminId = isset($data['admin_id']) ? (int)$data['admin_id'] : 0;
        if ($adminId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID']);
            exit;
        }
        try {
            // Sadece country_admin rolündeki kullanıcıları sil
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = ?");
            $ok = $stmt->execute([$adminId, ROLE_COUNTRY_ADMIN]);
            if ($ok && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Yönetici başarıyla silindi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Yönetici silinemedi veya bulunamadı']);
            }
        } catch (PDOException $e) {
            error_log('Country admin delete error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_school_admin') {
        header('Content-Type: application/json');
        $adminId = isset($data['admin_id']) ? (int)$data['admin_id'] : 0;
        if ($adminId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID']);
            exit;
        }
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = ?");
            $ok = $stmt->execute([$adminId, ROLE_SCHOOL_ADMIN]);
            if ($ok && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Yönetici başarıyla silindi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Yönetici silinemedi veya bulunamadı']);
            }
        } catch (PDOException $e) {
            error_log('School admin delete error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'create_branch') {
        header('Content-Type: application/json');
        $payload = $data;
        try {
            $name = trim($payload['name'] ?? '');
            if ($name === '') {
                echo json_encode(['success' => false, 'message' => 'Branş adı zorunludur']);
                exit;
            }
            $code = !empty($payload['code']) ? trim($payload['code']) : null;
            $status = in_array(($payload['status'] ?? 'active'), ['active','inactive']) ? $payload['status'] : 'active';
            
            $stmt = $db->prepare("INSERT INTO branches (code, name, status, created_at) VALUES (?,?,?,NOW())");
            $ok = $stmt->execute([$code, $name, $status]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Branş başarıyla eklendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Branş eklenemedi']);
            }
        } catch (PDOException $e) {
            error_log("Branch insert error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'bulk_add_branches') {
        header('Content-Type: application/json');
        $branches = isset($data['branches']) && is_array($data['branches']) ? $data['branches'] : [];
        if (empty($branches)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz veri: branches boş']);
            exit;
        }
        $successCount = 0;
        $errors = [];
        try {
            $insertStmt = $db->prepare("INSERT INTO branches (code, name, status, created_at) VALUES (?,?,?,NOW())");
            $existsStmt = $db->prepare("SELECT id FROM branches WHERE (code IS NOT NULL AND code = ?) OR name = ? LIMIT 1");
            foreach ($branches as $index => $item) {
                $code = isset($item['branch_code']) ? trim($item['branch_code']) : (isset($item['code']) ? trim($item['code']) : '');
                $name = isset($item['name']) ? trim($item['name']) : '';
                if ($name === '') {
                    $errors[] = ($index + 1) . '. satır: Branş adı zorunludur';
                    continue;
                }
                $status = 'active';
                try {
                    $existsStmt->execute([$code ?: null, $name]);
                    $exists = $existsStmt->fetch(PDO::FETCH_ASSOC);
                    if ($exists) {
                        $errors[] = ($index + 1) . ". satır: Zaten mevcut (" . ($code ?: '-') . " / " . $name . ")";
                        continue;
                    }
                    $ok = $insertStmt->execute([$code ?: null, $name, $status]);
                    if ($ok) {
                        $successCount++;
                    } else {
                        $errors[] = ($index + 1) . '. satır: ekleme başarısız';
                    }
                } catch (PDOException $e) {
                    $errors[] = ($index + 1) . '. satır: ' . $e->getMessage();
                }
            }
            if ($successCount > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "$successCount branş başarıyla eklendi",
                    'success_count' => $successCount,
                    'errors' => $errors
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Hiçbir branş eklenemedi',
                    'success_count' => 0,
                    'errors' => $errors
                ]);
            }
        } catch (PDOException $e) {
            error_log('Toplu branş ekleme hatası: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_branch') {
        header('Content-Type: application/json');
        $payload = $data;
        try {
            $branchId = isset($payload['branch_id']) ? (int)$payload['branch_id'] : 0;
            if ($branchId === 0) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz branş ID']);
                exit;
            }
            $name = trim($payload['name'] ?? '');
            if ($name === '') {
                echo json_encode(['success' => false, 'message' => 'Branş adı zorunludur']);
                exit;
            }
            $code = !empty($payload['code']) ? trim($payload['code']) : null;
            $status = in_array(($payload['status'] ?? 'active'), ['active','inactive']) ? $payload['status'] : 'active';
            
            $stmt = $db->prepare("UPDATE branches SET code=?, name=?, status=? WHERE id=?");
            $ok = $stmt->execute([$code, $name, $status, $branchId]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Branş başarıyla güncellendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Branş güncellenemedi']);
            }
        } catch (PDOException $e) {
            error_log("Branch update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_branch') {
        $branchId = isset($data['branch_id']) ? (int)$data['branch_id'] : 0;
        header('Content-Type: application/json');
        if ($branchId > 0) {
            try {
                // Branşı veritabanından sil
                $stmt = $db->prepare("DELETE FROM branches WHERE id = ?");
                $ok = $stmt->execute([$branchId]);
                if ($ok) {
                    echo json_encode(['success' => true, 'message' => 'Branş başarıyla silindi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Branş silinirken bir hata oluştu']);
                }
            } catch (PDOException $e) {
                error_log("Branş silme hatası: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Branş silme hatası: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Beklenmeyen hata: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz branş ID']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_selected_branches') {
        $branchIds = isset($data['branch_ids']) && is_array($data['branch_ids']) ? $data['branch_ids'] : [];
        header('Content-Type: application/json');
        if (empty($branchIds)) {
            echo json_encode(['success' => false, 'message' => 'Silinecek branş seçilmedi']);
            exit;
        }
        
        try {
            // ID'leri integer'a çevir ve temizle
            $branchIds = array_map('intval', $branchIds);
            $branchIds = array_filter($branchIds, function($id) { return $id > 0; });
            
            if (empty($branchIds)) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz branş ID\'leri']);
                exit;
            }
            
            // Placeholder'ları oluştur
            $placeholders = str_repeat('?,', count($branchIds) - 1) . '?';
            $stmt = $db->prepare("DELETE FROM branches WHERE id IN ($placeholders)");
            $ok = $stmt->execute($branchIds);
            
            if ($ok) {
                $deletedCount = $stmt->rowCount();
                echo json_encode(['success' => true, 'message' => "$deletedCount branş başarıyla silindi", 'deleted_count' => $deletedCount]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Branşlar silinirken bir hata oluştu']);
            }
        } catch (PDOException $e) {
            error_log("Toplu branş silme hatası: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        } catch (Exception $e) {
            error_log("Toplu branş silme hatası: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Beklenmeyen hata: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'create_user') {
        header('Content-Type: application/json');
        $payload = $data;
        try {
            $name = trim($payload['name'] ?? '');
            $surname = trim($payload['surname'] ?? '');
            $email = trim($payload['email'] ?? '');
            $password = trim($payload['password'] ?? '');
            $role = trim($payload['role'] ?? 'teacher');
            
            if ($name === '' || $email === '' || $password === '') {
                echo json_encode(['success' => false, 'message' => 'Ad, e-posta ve şifre zorunludur']);
                exit;
            }
            
            $userData = [
                'name' => $name, // Sadece ad, soyad ayrı tutulacak
                'surname' => $surname,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'phone' => isset($payload['phone']) ? trim($payload['phone']) : '',
                'address' => isset($payload['address']) ? trim($payload['address']) : '',
                'school_id' => isset($payload['school_id']) ? (int)$payload['school_id'] : 0,
                'tc_kimlik_no' => isset($payload['tc_kimlik_no']) ? trim($payload['tc_kimlik_no']) : '',
                'city' => isset($payload['city']) ? trim($payload['city']) : '',
                'county' => isset($payload['county']) ? trim($payload['county']) : ''
            ];
            
            // Öğretmen ise branş bilgilerini ekle
            if ($role === ROLE_TEACHER) {
                $userData['branch'] = isset($payload['branch']) ? trim($payload['branch']) : '';
                $userData['education'] = isset($payload['education']) ? trim($payload['education']) : '';
                $userData['experience'] = isset($payload['experience']) ? trim($payload['experience']) : '';
                $userData['university_name'] = isset($payload['university_name']) ? trim($payload['university_name']) : '';
                $userData['department_name'] = isset($payload['department_name']) ? trim($payload['department_name']) : '';
                $userData['graduation'] = isset($payload['graduation']) ? trim($payload['graduation']) : '';
            }
            
            // AdminModel'i kullanarak kullanıcı oluştur
            require_once dirname(__DIR__) . '/models/AdminModel.php';
            $adminModel = new AdminModel($db);
            $userId = $adminModel->createUser($userData);
            
            if ($userId && $userId > 0) {
                // Öğretmen ise teacher_profiles kaydı oluştur
                if ($role === ROLE_TEACHER) {
                    try {
                        $profileData = [];
                        
                        // Branş ID'sini bul
                        if (!empty($userData['branch'])) {
                            $branchStmt = $db->prepare("SELECT id FROM branches WHERE name = ? LIMIT 1");
                            $branchStmt->execute([$userData['branch']]);
                            $branchResult = $branchStmt->fetch(PDO::FETCH_ASSOC);
                            if ($branchResult) {
                                $profileData['application_branch_id'] = $branchResult['id'];
                            }
                        }
                        
                        // tc_kimlik_no artık users tablosunda, teacher_profiles'e eklenmeyecek
                        // Mevcut sütunları kontrol et
                        $columnsStmt = $db->query("SHOW COLUMNS FROM teacher_profiles");
                        $existingColumns = [];
                        while ($col = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                            $existingColumns[] = $col['Field'];
                        }
                        
                        if (!empty($userData['education']) && in_array('education', $existingColumns)) {
                            $profileData['education'] = $userData['education'];
                        }
                        if (!empty($userData['experience']) && in_array('experience', $existingColumns)) {
                            $profileData['experience'] = $userData['experience'];
                        }
                        if (!empty($userData['university_name'])) {
                            $profileData['institution_name'] = $userData['university_name'];
                        }
                        if (!empty($userData['department_name'])) {
                            $profileData['department'] = $userData['department_name'];
                        }
                        if (!empty($userData['graduation'])) {
                            $profileData['graduation_level'] = $userData['graduation'];
                        }
                        
                        // teacher_profiles kaydı oluştur
                        if (!empty($profileData)) {
                            $profileData['user_id'] = $userId;
                            $insertFields = array_keys($profileData);
                            $insertValues = array_values($profileData);
                            $insertSql = "INSERT INTO teacher_profiles (`" . implode('`, `', $insertFields) . "`) VALUES (" . str_repeat('?,', count($insertValues) - 1) . "?)";
                            $insertStmt = $db->prepare($insertSql);
                            $insertStmt->execute($insertValues);
                        }
                    } catch (PDOException $pe) {
                        error_log('Teacher profile creation error: ' . $pe->getMessage());
                        // Profil oluşturma başarısız olsa bile kullanıcı oluşturma başarılı sayılabilir
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla eklendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kullanıcı eklenemedi. E-posta adresi zaten kullanılıyor olabilir.']);
            }
        } catch (Exception $e) {
            error_log('create_user error: ' . $e->getMessage());
            error_log('create_user stack trace: ' . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
            exit;
        } catch (Error $e) {
            error_log('create_user fatal error: ' . $e->getMessage());
            error_log('create_user stack trace: ' . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
            exit;
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_user') {
        // JSON endpoint için hata çıktısını kapat
        $oldErrorReporting = error_reporting(0);
        $oldDisplayErrors = ini_set('display_errors', 0);
        
        header('Content-Type: application/json');
        $payload = $data;
        try {
            $userId = (int)($payload['user_id'] ?? 0);
            
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
                exit;
            }
            
            $userData = [
                'name' => isset($payload['name']) ? trim($payload['name']) : null,
                'surname' => isset($payload['surname']) ? trim($payload['surname']) : null,
                'email' => isset($payload['email']) ? trim($payload['email']) : null,
                'phone' => isset($payload['phone']) ? trim($payload['phone']) : null,
                'status' => isset($payload['status']) ? trim($payload['status']) : null,
                'tc_kimlik_no' => isset($payload['tc_kimlik_no']) ? trim($payload['tc_kimlik_no']) : null,
                'city' => isset($payload['city']) ? trim($payload['city']) : null,
                'county' => isset($payload['county']) ? trim($payload['county']) : null,
            ];
            
            // Şifre değiştirilecek mi?
            if (isset($payload['password']) && !empty(trim($payload['password']))) {
                $userData['password'] = trim($payload['password']);
            }
            
            // Boş alanları temizle
            $userData = array_filter($userData, function($value) {
                return $value !== null;
            });
            
            // Kullanıcı bilgilerini güncelle
            if (!empty($userData)) {
                require_once dirname(__DIR__) . '/models/AdminModel.php';
                $adminModel = new AdminModel($db);
                $result = $adminModel->updateUser($userId, $userData);
                if (!$result) {
                    echo json_encode(['success' => false, 'message' => 'Kullanıcı bilgileri güncellenemedi']);
                    exit;
                }
            }
            
            // Öğretmen ise profil bilgilerini güncelle
            if (isset($payload['branch']) || isset($payload['education']) || isset($payload['experience']) || isset($payload['university_name']) || isset($payload['department_name']) || isset($payload['graduation'])) {
                try {
                    // Önce mevcut profili kontrol et
                    $stmt = $db->prepare("SELECT id FROM teacher_profiles WHERE user_id = ? LIMIT 1");
                    $stmt->execute([$userId]);
                    $existingProfile = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Mevcut sütunları kontrol et
                    $columnsStmt = $db->query("SHOW COLUMNS FROM teacher_profiles");
                    $existingColumns = [];
                    while ($col = $columnsStmt->fetch(PDO::FETCH_ASSOC)) {
                        $existingColumns[] = $col['Field'];
                    }
                    
                    $profileData = [];
                    if (isset($payload['branch']) && !empty(trim($payload['branch']))) {
                        $profileData['application_branch_id'] = (int)trim($payload['branch']);
                    }
                    // education ve experience sütunları varsa ekle
                    if (isset($payload['education']) && in_array('education', $existingColumns)) {
                        $profileData['education'] = trim($payload['education']);
                    }
                    if (isset($payload['experience']) && in_array('experience', $existingColumns)) {
                        $profileData['experience'] = trim($payload['experience']);
                    }
                    if (isset($payload['university_name']) && !empty(trim($payload['university_name']))) {
                        $profileData['institution_name'] = trim($payload['university_name']);
                    }
                    if (isset($payload['department_name']) && !empty(trim($payload['department_name']))) {
                        $profileData['department'] = trim($payload['department_name']);
                    }
                    if (isset($payload['graduation']) && !empty(trim($payload['graduation']))) {
                        $profileData['graduation_level'] = trim($payload['graduation']);
                    }
                    
                    if (!empty($profileData)) {
                        if ($existingProfile) {
                            // Mevcut profili güncelle
                            $updateFields = [];
                            $updateValues = [];
                            foreach ($profileData as $key => $value) {
                                $updateFields[] = "`$key` = ?";
                                $updateValues[] = $value;
                            }
                            $updateValues[] = $existingProfile['id'];
                            $updateSql = "UPDATE teacher_profiles SET " . implode(', ', $updateFields) . " WHERE id = ?";
                            $updateStmt = $db->prepare($updateSql);
                            $updateStmt->execute($updateValues);
                        } else {
                            // Yeni profil oluştur
                            $profileData['user_id'] = $userId;
                            $insertFields = array_keys($profileData);
                            $insertValues = array_values($profileData);
                            $insertSql = "INSERT INTO teacher_profiles (`" . implode('`, `', $insertFields) . "`) VALUES (" . str_repeat('?,', count($insertValues) - 1) . "?)";
                            $insertStmt = $db->prepare($insertSql);
                            $insertStmt->execute($insertValues);
                        }
                    }
                } catch (PDOException $pe) {
                    error_log('Teacher profile update error: ' . $pe->getMessage());
                    error_log('Teacher profile update stack trace: ' . $pe->getTraceAsString());
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Profil güncellenirken hata oluştu: ' . $pe->getMessage()]);
                    exit;
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi']);
        } catch (Exception $e) {
            error_log('update_user error: ' . $e->getMessage());
            error_log('update_user stack trace: ' . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        } catch (Error $e) {
            error_log('update_user fatal error: ' . $e->getMessage());
            error_log('update_user stack trace: ' . $e->getTraceAsString());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        } finally {
            // Hata ayarlarını geri yükle
            if (isset($oldErrorReporting)) {
                error_reporting($oldErrorReporting);
            }
            if (isset($oldDisplayErrors)) {
                ini_set('display_errors', $oldDisplayErrors);
            }
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'create_school') {
        header('Content-Type: application/json');
        $payload = $data;
        try {
            $name = trim($payload['name'] ?? '');
            if ($name === '') {
                echo json_encode(['success' => false, 'message' => 'Okul adı zorunludur']);
                exit;
            }
            $school_code = !empty($payload['school_code']) ? trim($payload['school_code']) : null;
            $school_type = !empty($payload['school_type']) ? trim($payload['school_type']) : null;
            $city        = !empty($payload['city']) ? trim($payload['city']) : null;
            $county      = !empty($payload['county']) ? trim($payload['county']) : null;
            $country     = 'Türkiye'; // Varsayılan değer
            $address     = !empty($payload['address']) ? trim($payload['address']) : null;
            $phone       = !empty($payload['phone']) ? trim($payload['phone']) : null;
            $email       = !empty($payload['email']) ? trim($payload['email']) : null;
            $description = !empty($payload['description']) ? trim($payload['description']) : null;
            $status      = in_array(($payload['status'] ?? 'active'), ['active','inactive']) ? $payload['status'] : 'active';

            // Debug log
            error_log("School insert - county: " . $county . ", city: " . $city);
            
            $stmt = $db->prepare("INSERT INTO schools (school_code, name, school_type, city, county, country, address, phone, email, description, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $ok = $stmt->execute([$school_code, $name, $school_type, $city, $county, $country, $address, $phone, $email, $description, $status]);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Okul başarıyla eklendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Okul eklenemedi']);
            }
        } catch (PDOException $e) {
            error_log('Okul ekleme hatası: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'get_school_types') {
        header('Content-Type: application/json');
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS school_types (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(150) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_school_types_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $db->query("SELECT id, name FROM school_types WHERE is_active = 1 ORDER BY name ASC");
            $types = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            echo json_encode(['success' => true, 'types' => $types]);
        } catch (Throwable $t) {
            error_log('get_school_types error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Okul türleri alınamadı']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'add_school_type') {
        header('Content-Type: application/json');
        try {
            $name = trim((string)($data['name'] ?? ''));
            if ($name === '') {
                echo json_encode(['success' => false, 'message' => 'Okul türü adı zorunludur']);
                exit;
            }

            $db->exec("
                CREATE TABLE IF NOT EXISTS school_types (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(150) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_school_types_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $insert = $db->prepare("
                INSERT INTO school_types (name, is_active) VALUES (?, 1)
                ON DUPLICATE KEY UPDATE is_active = VALUES(is_active), updated_at = CURRENT_TIMESTAMP
            ");
            $insert->execute([$name]);

            echo json_encode(['success' => true, 'message' => 'Okul türü eklendi']);
        } catch (Throwable $t) {
            error_log('add_school_type error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Okul türü eklenemedi']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_school_type') {
        header('Content-Type: application/json');
        try {
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $name = trim((string)($data['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                echo json_encode(['success' => false, 'message' => 'Geçersiz okul türü bilgisi']);
                exit;
            }

            $db->exec("
                CREATE TABLE IF NOT EXISTS school_types (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(150) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_school_types_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $findStmt = $db->prepare("SELECT name FROM school_types WHERE id = ? AND is_active = 1 LIMIT 1");
            $findStmt->execute([$id]);
            $current = $findStmt->fetch(PDO::FETCH_ASSOC);
            if (!$current) {
                echo json_encode(['success' => false, 'message' => 'Okul türü bulunamadı']);
                exit;
            }

            $oldName = trim((string)$current['name']);
            if ($oldName === $name) {
                echo json_encode(['success' => true, 'message' => 'Değişiklik yok']);
                exit;
            }

            $db->beginTransaction();

            $updateTypeStmt = $db->prepare("UPDATE school_types SET name = ? WHERE id = ?");
            $updateTypeStmt->execute([$name, $id]);

            try {
                $updateSchoolsStmt = $db->prepare("UPDATE schools SET school_type = ? WHERE school_type = ?");
                $updateSchoolsStmt->execute([$name, $oldName]);
            } catch (Throwable $t) {
                // schools tablosu yoksa veya school_type kolonu yoksa işlemi bozma
            }

            try {
                $updateMapStmt = $db->prepare("UPDATE school_type_branch_map SET school_type = ? WHERE school_type = ?");
                $updateMapStmt->execute([$name, $oldName]);
            } catch (Throwable $t) {
                // mapping tablosu yoksa işlemi bozma
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Okul türü güncellendi']);
        } catch (Throwable $t) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('update_school_type error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Okul türü güncellenemedi']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_school_type') {
        header('Content-Type: application/json');
        try {
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz okul türü ID']);
                exit;
            }

            $findStmt = $db->prepare("SELECT name FROM school_types WHERE id = ? AND is_active = 1 LIMIT 1");
            $findStmt->execute([$id]);
            $current = $findStmt->fetch(PDO::FETCH_ASSOC);
            if (!$current) {
                echo json_encode(['success' => false, 'message' => 'Okul türü bulunamadı']);
                exit;
            }

            $name = trim((string)$current['name']);
            $db->beginTransaction();

            $deleteTypeStmt = $db->prepare("DELETE FROM school_types WHERE id = ?");
            $deleteTypeStmt->execute([$id]);

            try {
                $clearSchoolsStmt = $db->prepare("UPDATE schools SET school_type = NULL WHERE school_type = ?");
                $clearSchoolsStmt->execute([$name]);
            } catch (Throwable $t) {
                // schools tablosu yoksa işlemi bozma
            }

            try {
                $deleteMapStmt = $db->prepare("DELETE FROM school_type_branch_map WHERE school_type = ?");
                $deleteMapStmt->execute([$name]);
            } catch (Throwable $t) {
                // mapping tablosu yoksa işlemi bozma
            }

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Okul türü silindi']);
        } catch (Throwable $t) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('delete_school_type error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Okul türü silinemedi']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'delete_school_type_branch_links') {
        header('Content-Type: application/json');
        try {
            $schoolType = trim((string)($data['school_type'] ?? ''));
            $branchIds = isset($data['branch_ids']) && is_array($data['branch_ids']) ? $data['branch_ids'] : [];
            $branchIds = array_values(array_filter(array_map('intval', $branchIds), function ($v) { return $v > 0; }));

            if ($schoolType === '' || empty($branchIds)) {
                echo json_encode(['success' => false, 'message' => 'Okul türü ve en az bir branş seçilmelidir']);
                exit;
            }

            $placeholders = implode(',', array_fill(0, count($branchIds), '?'));
            $params = array_merge([$schoolType], $branchIds);
            $stmt = $db->prepare("DELETE FROM school_type_branch_map WHERE school_type = ? AND branch_id IN ($placeholders)");
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Seçili branş eşleştirmeleri silindi']);
        } catch (Throwable $t) {
            error_log('delete_school_type_branch_links error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Eşleştirmeler silinemedi']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'add_school_type_branch_links') {
        header('Content-Type: application/json');
        try {
            $schoolType = trim((string)($data['school_type'] ?? ''));
            $branchIds = isset($data['branch_ids']) && is_array($data['branch_ids']) ? $data['branch_ids'] : [];
            $branchIds = array_values(array_filter(array_map('intval', $branchIds), function ($v) { return $v > 0; }));

            if ($schoolType === '' || empty($branchIds)) {
                echo json_encode(['success' => false, 'message' => 'Okul türü ve en az bir branş seçilmelidir']);
                exit;
            }

            $db->exec("
                CREATE TABLE IF NOT EXISTS school_type_branch_map (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    school_type VARCHAR(100) NOT NULL,
                    branch_id INT(11) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uk_school_type_branch (school_type, branch_id),
                    KEY idx_school_type (school_type),
                    KEY idx_branch_id (branch_id),
                    CONSTRAINT fk_school_type_branch_map_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $insert = $db->prepare("
                INSERT INTO school_type_branch_map (school_type, branch_id, is_active)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)
            ");

            foreach ($branchIds as $branchId) {
                $insert->execute([$schoolType, $branchId]);
            }

            echo json_encode(['success' => true, 'message' => 'Eşleştirmeler kaydedildi']);
        } catch (Throwable $t) {
            error_log('add_school_type_branch_links error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Eşleştirmeler kaydedilemedi']);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'create_country_admin') {
        header('Content-Type: application/json');
        try {
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            if ($name === '' || $email === '' || $password === '') {
                echo json_encode(['success' => false, 'message' => 'Ad, e-posta ve şifre zorunludur']);
                exit;
            }
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => ROLE_COUNTRY_ADMIN,
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'county' => $data['county'] ?? ''
            ];
            $ok = $viewModel->addUser($userData, $_SESSION['user_id']);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Yönetici başarıyla eklendi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Yönetici eklenemedi. E-posta zaten kullanılıyor olabilir.']);
            }
        } catch (Exception $e) {
            error_log('Country admin create error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'create_school_admin') {
        header('Content-Type: application/json');
        try {
            $name = trim($data['name'] ?? '');
            $surname = trim($data['surname'] ?? '');
            $tc_no = trim($data['tc_no'] ?? '');
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
            $school_id = !empty($data['school_id']) ? (int)$data['school_id'] : null;
            
            // Zorunlu alan kontrolü: TC kimlik no, Ad, Soyad, Şifre, Bağlı okul
            if ($name === '' || $surname === '' || $tc_no === '' || $password === '' || !$school_id) {
                echo json_encode(['success' => false, 'message' => 'TC kimlik no, ad, soyad, şifre ve bağlı okul zorunludur']);
                exit;
            }
            
            // TC Kimlik No validasyonu (11 haneli olmalı)
            if (strlen($tc_no) !== 11 || !ctype_digit($tc_no)) {
                echo json_encode(['success' => false, 'message' => 'TC kimlik numarası 11 haneli olmalı ve sadece rakam içermelidir']);
                exit;
            }
            
            $userData = [
                'name' => $name,
                'surname' => $surname,
                'tc_kimlik_no' => $tc_no,
                'email' => $email,
                'password' => $password,
                'role' => ROLE_SCHOOL_ADMIN,
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'county' => $data['county'] ?? '',
                'school_id' => $school_id
            ];
            
            $ok = $viewModel->addUser($userData, $_SESSION['user_id']);
            echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Yönetici başarıyla eklendi' : 'Yönetici eklenemedi']);
        } catch (Exception $e) {
            error_log('School admin create error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'bulk_add_school_admins') {
        header('Content-Type: application/json');
        $admins = isset($data['admins']) && is_array($data['admins']) ? $data['admins'] : [];
        if (empty($admins)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz veri: admins boş']);
            exit;
        }
        $successCount = 0; $errors = [];
        try {
            $existsStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $findSchoolByCode = $db->prepare("SELECT id, school_code, name FROM schools WHERE school_code = ? LIMIT 1");
            $findSchoolByName = $db->prepare("SELECT id, school_code, name FROM schools WHERE name = ? LIMIT 1");
            foreach ($admins as $i => $a) {
                $row = $i + 1;
                $name = trim((string)($a['name'] ?? ''));
                $surname = trim((string)($a['surname'] ?? ''));
                $email = trim((string)($a['email'] ?? ''));
                if ($name === '') { $errors[] = "$row. satır: Ad zorunlu"; continue; }
                // Okul bilgilerini önce al (e-posta oluşturmak için gerekli)
                $schoolCode = isset($a['school_code']) ? trim((string)$a['school_code']) : '';
                $schoolName = isset($a['school_name']) ? trim((string)$a['school_name']) : '';
                // E-posta yoksa otomatik oluştur
                if ($email === '') {
                    $normalizeForEmail = function($str) {
                        return strtolower(str_replace(
                            ['ı', 'İ', 'ş', 'Ş', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'],
                            ['i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'],
                            $str
                        ));
                    };
                    $namePart = preg_replace('/[^a-z0-9]/', '', $normalizeForEmail($name));
                    $surnamePart = preg_replace('/[^a-z0-9]/', '', $normalizeForEmail($surname));
                    if ($schoolCode) {
                        $schoolCodePart = preg_replace('/[^a-z0-9]/', '', $normalizeForEmail($schoolCode));
                        $email = $namePart . '.' . $surnamePart . '@' . $schoolCodePart . '.meb.k12.tr';
                    } else {
                        $email = $namePart . '.' . $surnamePart . '@ogretmenPortali.local';
                    }
                }
                $existsStmt->execute([$email]);
                if ($existsStmt->fetch(PDO::FETCH_ASSOC)) { $errors[] = "$row. satır: E-posta zaten kayıtlı ($email)"; continue; }
                // Okul eşleştirme: önce kod, sonra ad (zorunlu)
                $schoolId = null;
                
                if ($schoolCode !== '') {
                    $findSchoolByCode->execute([$schoolCode]);
                    $s = $findSchoolByCode->fetch(PDO::FETCH_ASSOC); 
                    if ($s) { 
                        $schoolId = (int)$s['id']; 
                    }
                }
                if ($schoolId === null && $schoolName !== '') {
                    $findSchoolByName->execute([$schoolName]);
                    $s = $findSchoolByName->fetch(PDO::FETCH_ASSOC); 
                    if ($s) { 
                        $schoolId = (int)$s['id']; 
                    }
                }
                
                // Okul bulunamazsa hata ver
                if ($schoolId === null) {
                    $schoolInfo = '';
                    if ($schoolCode) $schoolInfo = "Kurum kodu: $schoolCode";
                    if ($schoolName) $schoolInfo .= ($schoolInfo ? ', ' : '') . "Okul adı: $schoolName";
                    $errors[] = "$row. satır: Okul bulunamadı ($schoolInfo). Lütfen okulun sistemde kayıtlı olduğundan emin olun.";
                    continue;
                }
                
                $plainPassword = (string)($a['password'] ?? '');
                $password = $plainPassword !== '' ? $plainPassword : bin2hex(random_bytes(4));
                $userData = [
                    'name' => $name,
                    'surname' => $surname,
                    'email' => $email,
                    'password' => $password,
                    'role' => ROLE_SCHOOL_ADMIN,
                    'phone' => trim((string)($a['phone'] ?? '')),
                    'address' => trim((string)($a['address'] ?? '')),
                    'city' => trim((string)($a['city'] ?? '')),
                    'county' => trim((string)($a['county'] ?? '')),
                    'school_id' => $schoolId
                ];
                try {
                    $ok = $viewModel->addUser($userData, $_SESSION['user_id']);
                    if ($ok) { $successCount++; } else { $errors[] = "$row. satır: ekleme başarısız"; }
                } catch (Throwable $t) {
                    $errors[] = "$row. satır: " . $t->getMessage();
                }
            }
            echo json_encode(['success' => $successCount > 0, 'success_count' => $successCount, 'errors' => $errors, 'message' => $successCount > 0 ? "$successCount yönetici eklendi" : 'Hiçbir yönetici eklenemedi']);
        } catch (Throwable $t) {
            error_log('bulk_add_school_admins error: ' . $t->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $t->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'bulk_add_country_admins') {
        header('Content-Type: application/json');
        $admins = isset($data['admins']) && is_array($data['admins']) ? $data['admins'] : [];
        if (empty($admins)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz veri: admins boş']);
            exit;
        }
        $successCount = 0;
        $errors = [];
        try {
            $existsStmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            // Not: Bazı kurulumlarda users tablosunda 'address' kolonu mevcut olmayabilir.
            // Bu nedenle adresi eklemeden kayıt yapıyoruz.
            $insertStmt = $db->prepare("INSERT INTO users (name, surname, email, password, role, phone, city, county, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())");
            foreach ($admins as $index => $a) {
                $name = trim((string)($a['name'] ?? ''));
                $surname = trim((string)($a['surname'] ?? ''));
                $email = trim((string)($a['email'] ?? ''));
                $phone = trim((string)($a['phone'] ?? ''));
                $city = trim((string)($a['city'] ?? ''));
                $county = trim((string)($a['county'] ?? ''));
                $status = in_array(($a['status'] ?? 'active'), ['active','inactive']) ? $a['status'] : 'active';
                $plainPassword = (string)($a['password'] ?? '');
                if ($name === '' || $email === '') {
                    $errors[] = ($index + 1) . '. satır: Ad ve e-posta zorunlu';
                    continue;
                }
                // E-posta mevcut mu kontrol et
                $existsStmt->execute([$email]);
                if ($existsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $errors[] = ($index + 1) . ". satır: E-posta zaten kayıtlı ($email)";
                    continue;
                }
                // Şifre: Excel’de yoksa rastgele üret ve modern hash ile sakla
                $passwordToUse = $plainPassword !== '' ? $plainPassword : bin2hex(random_bytes(4));
                $passwordHash = password_hash($passwordToUse, PASSWORD_DEFAULT);
                try {
                    $ok = $insertStmt->execute([$name, $surname, $email, $passwordHash, ROLE_COUNTRY_ADMIN, $phone, $city, $county, $status]);
                    if ($ok) {
                        $successCount++;
                    } else {
                        $errors[] = ($index + 1) . '. satır: ekleme başarısız';
                    }
                } catch (PDOException $pe) {
                    $errors[] = ($index + 1) . '. satır: ' . $pe->getMessage();
                }
            }
            if ($successCount > 0) {
                echo json_encode(['success' => true, 'message' => "$successCount yönetici eklendi", 'success_count' => $successCount, 'errors' => $errors]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Hiçbir yönetici eklenemedi', 'success_count' => 0, 'errors' => $errors]);
            }
        } catch (Exception $e) {
            error_log('bulk_add_country_admins error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_country_admin') {
        header('Content-Type: application/json');
        try {
            $userId = isset($data['admin_id']) ? (int)$data['admin_id'] : 0;
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID']);
                exit;
            }
            $payload = [];
            foreach (['name','email','phone','address','city','county','status'] as $k) {
                if (isset($data[$k])) { $payload[$k] = trim((string)$data[$k]); }
            }
            if (isset($data['password']) && $data['password'] !== '') {
                $payload['password'] = $data['password'];
            }
            // Rol sabit
            $payload['role'] = ROLE_COUNTRY_ADMIN;
            $ok = $viewModel->updateUser($userId, $payload, $_SESSION['user_id']);
            echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Yönetici güncellendi' : 'Yönetici güncellenemedi']);
        } catch (Exception $e) {
            error_log('Country admin update error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_school_admin') {
        header('Content-Type: application/json');
        try {
            $userId = isset($data['admin_id']) ? (int)$data['admin_id'] : 0;
            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID']);
                exit;
            }
            $payload = [];
            foreach (['name','surname','email','phone','address','city','county','status'] as $k) {
                if (isset($data[$k])) { $payload[$k] = trim((string)$data[$k]); }
            }
            if (!empty($data['school_id'])) { $payload['school_id'] = (int)$data['school_id']; }
            if (isset($data['password']) && $data['password'] !== '') {
                $payload['password'] = $data['password'];
            }
            $payload['role'] = ROLE_SCHOOL_ADMIN;
            $ok = $viewModel->updateUser($userId, $payload, $_SESSION['user_id']);
            echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Yönetici güncellendi' : 'Yönetici güncellenemedi']);
        } catch (Exception $e) {
            error_log('School admin update error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
        exit;
    }
    if (isset($data['action']) && $data['action'] === 'update_school') {
        $schoolId = isset($data['school_id']) ? (int)$data['school_id'] : 0;
        header('Content-Type: application/json');
        if ($schoolId > 0) {
            try {
                $name = trim($data['name'] ?? '');
                if ($name === '') {
                    echo json_encode(['success' => false, 'message' => 'Okul adı zorunludur']);
                    exit;
                }
                $school_code = $data['school_code'] ?? null;
                $school_type = $data['school_type'] ?? null;
                $city        = $data['city'] ?? null;
                $county      = $data['county'] ?? null;
                $country     = $data['country'] ?? 'Türkiye'; // Varsayılan değer
                $address     = $data['address'] ?? null;
                $phone       = $data['phone'] ?? null;
                $email       = $data['email'] ?? null;
                $description = $data['description'] ?? null;
                $status      = in_array(($data['status'] ?? 'active'), ['active','inactive']) ? $data['status'] : 'active';

                $stmt = $db->prepare("UPDATE schools SET school_code=?, name=?, school_type=?, city=?, county=?, country=?, address=?, phone=?, email=?, description=?, status=? WHERE id=?");
                $ok = $stmt->execute([$school_code, $name, $school_type, $city, $county, $country, $address, $phone, $email, $description, $status, $schoolId]);
                if ($ok) {
                    echo json_encode(['success' => true, 'message' => 'Okul başarıyla güncellendi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Okul güncellenemedi']);
                }
            } catch (PDOException $e) {
                error_log('Okul güncelleme hatası: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID']);
        }
        exit;
    }
}

if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'name' => $_POST['name'] ?? '',
        'surname' => $_POST['surname'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    // Şifre değişikliği var mı?
    if (!empty($_POST['password'])) {
        $userData['password'] = $_POST['password'];
    }
    
    // Profil fotoğrafı yüklendi mi?
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $userData['profile_image'] = 'uploads/profiles/' . $fileName;
        }
    }
    
    // Users tablosuna kaydet
    error_log('Update profile - User data: ' . print_r($userData, true));
    error_log('Update profile - User ID: ' . $_SESSION['user_id']);
    
    $result = $viewModel->updateUser($_SESSION['user_id'], $userData);
    
    error_log('Update profile - Result: ' . ($result ? 'true' : 'false'));
    
    // Settings tablosuna da kaydet (kullanıcı bazlı profil ayarları)
    if ($result) {
        try {
            $profileSettings = [
                'profile_name' => $userData['name'] ?? '',
                'profile_surname' => $userData['surname'] ?? '',
                'profile_email' => $userData['email'] ?? '',
                'profile_phone' => $userData['phone'] ?? ''
            ];
            
            if (isset($userData['profile_image'])) {
                $profileSettings['profile_image'] = $userData['profile_image'];
            }
            
            $stmt = $db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (?, ?, ?, 'profile')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");
            
            foreach ($profileSettings as $key => $value) {
                $stmt->execute([$_SESSION['user_id'], $key, $value]);
            }
        } catch (PDOException $e) {
            error_log('Profile settings save error: ' . $e->getMessage());
        }
        
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Profil bilgileri başarıyla güncellendi.');
    } else {
        error_log('Update profile failed - User ID: ' . $_SESSION['user_id']);
        $_SESSION['error'] = 'Profil bilgileri güncellenirken bir hata oluştu. Lütfen tekrar deneyin.';
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=error&message=Profil bilgileri güncellenirken bir hata oluştu.');
    }
}

if ($action === 'update_notifications' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationSettings = [
        'email_notifications' => isset($_POST['email_notifications']),
        'sms_notifications' => isset($_POST['sms_notifications']),
        'application_notifications' => isset($_POST['application_notifications']),
        'system_notifications' => isset($_POST['system_notifications'])
    ];
    
    $result = $viewModel->updateNotificationSettings($_SESSION['user_id'], $notificationSettings);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=settings&status=success&message=Bildirim ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Bildirim ayarları güncellenirken bir hata oluştu.';
    }
}

// Logout functionality
if ($action === 'logout') {
    session_destroy();
    redirect(BASE_URL . '/php/admin_login.php');
}

// Okul yönetimi işlemleri
if ($action === 'get_school') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM schools WHERE id = ?");
        $stmt->execute([$id]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        if ($school) {
            echo json_encode(['success' => true, 'school' => $school]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Okul bulunamadı']);
        }
    } catch (PDOException $e) {
        error_log("Okul getirme hatası: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    exit;
}

// Kullanıcı bilgisi getir (JSON) - sadece admin paneli için, güvenlik: role filtrelenebilir
if ($action === 'get_user') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $roleFilter = isset($_GET['role']) ? $_GET['role'] : null;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
        exit;
    }
    try {
        if ($roleFilter) {
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = ?");
            $stmt->execute([$id, $roleFilter]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        if ($user) {
            // İlçe M.E.M. yöneticisi profili (country_admin_profiles) varsa ekle
            try {
                $pstmt = $db->prepare("SELECT * FROM country_admin_profiles WHERE user_id = ? LIMIT 1");
                $pstmt->execute([$id]);
                $profile = $pstmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (PDOException $pe) {
                error_log('country_admin_profiles fetch error: ' . $pe->getMessage());
                $profile = null;
            }
            // Öğretmen ise profil bilgilerini getir
            $teacherProfile = null;
            if ($roleFilter === ROLE_TEACHER || ($user['role'] ?? null) === ROLE_TEACHER) {
                try {
                    $tpstmt = $db->prepare("SELECT tp.*, b.name AS branch_name FROM teacher_profiles tp LEFT JOIN branches b ON b.id = tp.application_branch_id WHERE tp.user_id = ? LIMIT 1");
                    $tpstmt->execute([$id]);
                    $teacherProfile = $tpstmt->fetch(PDO::FETCH_ASSOC) ?: null;
                    $latestAppliedBranch = null;

                    // applications tablosunda owner kolon adı şemaya göre teacher_id veya user_id olabilir.
                    $ownerColStmt = $db->query("
                        SELECT column_name
                        FROM information_schema.columns
                        WHERE table_schema = DATABASE()
                          AND table_name = 'applications'
                          AND column_name IN ('teacher_id', 'user_id')
                        ORDER BY FIELD(column_name, 'teacher_id', 'user_id')
                        LIMIT 1
                    ");
                    $ownerColumn = $ownerColStmt ? $ownerColStmt->fetchColumn() : false;
                    if ($ownerColumn) {
                        $appliedBranchStmt = $db->prepare("
                            SELECT b.name
                            FROM applications a
                            LEFT JOIN branches b ON b.id = a.branch_id
                            WHERE a.{$ownerColumn} = ?
                            ORDER BY a.id DESC
                            LIMIT 1
                        ");
                        $appliedBranchStmt->execute([$id]);
                        $latestAppliedBranch = $appliedBranchStmt->fetchColumn() ?: null;

                        $districtApprovedRole = defined('ROLE_COUNTRY_ADMIN') ? ROLE_COUNTRY_ADMIN : 'country_admin';
                        $districtApprovedApplicationStmt = $db->prepare("
                            SELECT s.name AS school_name, b.name AS branch_name
                            FROM applications a
                            LEFT JOIN schools s ON s.id = a.school_id
                            LEFT JOIN branches b ON b.id = a.branch_id
                            LEFT JOIN users approver ON approver.id = a.approved_by
                            WHERE a.{$ownerColumn} = ?
                              AND a.status = 'approved'
                              AND approver.role = ?
                            ORDER BY COALESCE(a.approved_at, a.updated_at, a.application_date) DESC, a.id DESC
                            LIMIT 1
                        ");
                        $districtApprovedApplicationStmt->execute([$id, $districtApprovedRole]);
                        $districtApprovedApplication = $districtApprovedApplicationStmt->fetch(PDO::FETCH_ASSOC) ?: null;

                        $user['is_district_approved'] = $districtApprovedApplication !== null;
                        $user['district_approved_school_name'] = $districtApprovedApplication['school_name'] ?? null;
                        $user['district_approved_branch_name'] = $districtApprovedApplication['branch_name'] ?? null;
                    }
                    
                    // teacher_profiles'den gelen verileri user objesine ekle
                    if ($teacherProfile) {
                        $user['university_name'] = $teacherProfile['institution_name'] ?? $teacherProfile['institution'] ?? null;
                        $user['department_name'] = $teacherProfile['department'] ?? null;
                        $user['graduation'] = $teacherProfile['graduation_level'] ?? null;
                        $teacherProfile['applied_branch_name'] = $latestAppliedBranch ?: ($teacherProfile['branch_name'] ?? null);
                    } else {
                        $teacherProfile = ['applied_branch_name' => $latestAppliedBranch];
                    }

                    if ((empty($teacherProfile['experience']) || trim((string)$teacherProfile['experience']) === '')) {
                        // Deneyim tablosu bazı kurulumlarda experiences adıyla bulunuyor.
                        $expTableStmt = $db->query("
                            SELECT table_name
                            FROM information_schema.tables
                            WHERE table_schema = DATABASE()
                              AND table_name IN ('teacher_experiences', 'experiences')
                            ORDER BY FIELD(table_name, 'teacher_experiences', 'experiences')
                            LIMIT 1
                        ");
                        $expTable = $expTableStmt ? $expTableStmt->fetchColumn() : false;
                        if ($expTable) {
                            $expColStmt = $db->prepare("
                                SELECT COUNT(*) 
                                FROM information_schema.columns
                                WHERE table_schema = DATABASE()
                                  AND table_name = ?
                                  AND column_name = 'experience'
                            ");
                            $expColStmt->execute([$expTable]);
                            $hasExperienceColumn = (int)$expColStmt->fetchColumn() > 0;
                            if ($hasExperienceColumn) {
                                $expStmt = $db->prepare("
                                    SELECT experience
                                    FROM {$expTable}
                                    WHERE user_id = ?
                                    ORDER BY id DESC
                                    LIMIT 1
                                ");
                                $expStmt->execute([$id]);
                                $latestExperience = $expStmt->fetchColumn();
                                if ($latestExperience !== false && $latestExperience !== null && trim((string)$latestExperience) !== '') {
                                    $teacherProfile['experience'] = $latestExperience;
                                }
                            }
                        }
                    }

                    $user['applied_branch_name'] = $teacherProfile['applied_branch_name'] ?? $latestAppliedBranch ?? null;
                } catch (PDOException $pe3) {
                    error_log('teacher_profiles fetch error: ' . $pe3->getMessage());
                    $teacherProfile = null;
                }
            }
            // Okul yöneticisi ise bağlı okul bilgisini getir
            $schoolInfo = null;
            if ($roleFilter === ROLE_SCHOOL_ADMIN || ($user['role'] ?? null) === ROLE_SCHOOL_ADMIN) {
                try {
                    $q = $db->prepare("SELECT sa.school_id, s.name AS school_name, s.school_code, s.address AS school_address FROM school_admins sa LEFT JOIN schools s ON s.id = sa.school_id WHERE sa.user_id = ? LIMIT 1");
                    $q->execute([$id]);
                    $schoolInfo = $q->fetch(PDO::FETCH_ASSOC) ?: null;
                } catch (PDOException $pe2) {
                    error_log('school_admins fetch error: ' . $pe2->getMessage());
                    $schoolInfo = null;
                }
                if ($schoolInfo) {
                    $user['school_id'] = $schoolInfo['school_id'];
                    $user['school_name'] = $schoolInfo['school_name'];
                    $user['school_code'] = $schoolInfo['school_code'];
                    $user['school_address'] = $schoolInfo['school_address'];
                }
            }
            echo json_encode(['success' => true, 'user' => $user, 'profile' => $profile, 'teacher_profile' => $teacherProfile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
        }
    } catch (PDOException $e) {
        error_log("Kullanıcı getirme hatası: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    exit;
}

if ($action === 'get_teacher_profile') {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($userId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
        exit;
    }
    try {
        $stmt = $db->prepare("SELECT tp.*, b.name AS branch_name FROM teacher_profiles tp LEFT JOIN branches b ON b.id = tp.application_branch_id WHERE tp.user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        if ($profile) {
            // Alan adlarını normalize et
            $profile['university_name'] = $profile['institution_name'] ?? $profile['institution'] ?? null;
            $profile['department_name'] = $profile['department'] ?? null;
            $profile['graduation'] = $profile['graduation_level'] ?? null;
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Profil bulunamadı']);
        }
    } catch (PDOException $e) {
        error_log("Öğretmen profili getirme hatası: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    exit;
}

if ($action === 'get_school_admins') {
    $admins = $viewModel->getSchoolAdmins($_SESSION['user_id']);
    echo json_encode($admins);
    exit;
}

if ($action === 'get_branch') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Geçersiz branş ID']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        $branch = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        if ($branch) {
            echo json_encode(['success' => true, 'branch' => $branch]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Branş bulunamadı']);
        }
    } catch (PDOException $e) {
        error_log("Branş getirme hatası: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    exit;
}

if ($action === 'update_school') {
    $id = isset($_POST['school_id']) ? (int)$_POST['school_id'] : 0;
    $success = $viewModel->updateSchool($id, $_POST, $_SESSION['user_id']);
    echo json_encode(['success' => $success]);
    exit;
}

// Excel yükleme işlemi
if ($action === 'upload_schools_excel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hataları yakalamak için çıktı tamponlamayı başlat
    ob_start();
    
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Dosya yüklenirken bir hata oluştu.';
        if (isset($_FILES['excel_file'])) {
            switch ($_FILES['excel_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error = 'Dosya boyutu çok büyük (PHP.ini\'de izin verilen maksimum boyutu aşıyor).';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'Dosya boyutu çok büyük (formda belirtilen maksimum boyutu aşıyor).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'Dosya kısmen yüklendi. Lütfen tekrar deneyin.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error = 'Dosya yüklenmedi. Lütfen bir dosya seçin.';
                    break;
            }
        }
        
        // Tamponlamayı temizle
        ob_end_clean();
        
        echo json_encode([
            'success' => false,
            'message' => $error
        ]);
        exit;
    }
    
    $uploadDir = dirname(__DIR__) . '/uploads/excel/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    
    // Dosya uzantısını kontrol et
    if (!in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
        // Tamponlamayı temizle
        ob_end_clean();
        
        echo json_encode([
            'success' => false,
            'message' => 'Sadece Excel ve CSV dosyaları (.xlsx, .xls, .csv) yüklenebilir.'
        ]);
        exit;
    }
    
    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $targetPath)) {
        try {
            // PHP'nin COM uzantısını kontrol et (sadece Excel dosyaları için)
            if ($fileExtension === 'xlsx' || $fileExtension === 'xls') {
                if (!extension_loaded('com_dotnet')) {
                    // COM uzantısı yüklü değilse hata ver
                    // Dosyayı temizle
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                    
                    // Tamponlamayı temizle
                    ob_end_clean();
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Excel dosyalarını işlemek için PHP COM uzantısı gereklidir. Lütfen sistem yöneticinizle iletişime geçin.',
                        'alternative' => 'Alternatif olarak, Excel dosyanızı CSV formatında kaydedip tekrar yüklemeyi deneyebilirsiniz.'
                    ]);
                    exit;
                }
            }
            
            // Dosyayı işle
            $result = $viewModel->uploadSchoolsFromExcel($targetPath, $_SESSION['user_id']);
            
            // Dosyayı temizle
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            
            // Tamponlamayı temizle
            ob_end_clean();
            
            // Sonucu döndür
            echo json_encode($result);
        } catch (com_exception $e) {
            // Dosyayı temizle
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            
            // Tamponlamayı temizle
            ob_end_clean();
            
            echo json_encode([
                'success' => false,
                'message' => 'Excel COM hatası: ' . $e->getMessage(),
                'suggestion' => 'Excel dosyasını açarken bir hata oluştu. Excel uygulamasının bilgisayarınızda yüklü olduğundan emin olun.',
                'alternative' => 'Alternatif olarak, Excel dosyanızı CSV formatında kaydedip tekrar yüklemeyi deneyebilirsiniz.'
            ]);
        } catch (Exception $e) {
            // Dosyayı temizle
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            
            // Tamponlamayı temizle
            ob_end_clean();
            
            echo json_encode([
                'success' => false,
                'message' => 'Excel işlenirken bir hata oluştu: ' . $e->getMessage()
            ]);
        }
    } else {
        // Tamponlamayı temizle
        ob_end_clean();
        
        echo json_encode([
            'success' => false,
            'message' => 'Dosya yüklenirken bir hata oluştu.'
        ]);
    }
    exit;
}

// Okul yöneticisi durumunu değiştir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_school_admin_status') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['admin_id']) || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Eksik parametre']);
        exit;
    }
    
    $result = $viewModel->toggleSchoolAdminStatus($data['admin_id'], $data['status'], $_SESSION['user_id']);
    echo json_encode(['success' => $result]);
    exit;
}

// Okul yöneticisi sil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_school_admin') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['admin_id'])) {
        echo json_encode(['success' => false, 'message' => 'Eksik parametre']);
        exit;
    }
    
    $result = $viewModel->deleteSchoolAdmin($data['admin_id'], $_SESSION['user_id']);
    echo json_encode(['success' => $result]);
    exit;
}

// (JSON gövdesi ile ele alınıyor) delete_country_admin işlemi yukarıdaki POST JSON bloğunda tanımlandı.

// Toplu okul ekleme
if ($action === 'bulk_add_schools' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['schools']) || !is_array($data['schools'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veri formatı'
        ]);
        exit;
    }
    
    $result = $viewModel->bulkAddSchools($data['schools'], $_SESSION['user_id']);
    echo json_encode($result);
    exit;
}

// Okulları Excel için export
if ($action === 'export_schools' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    try {
        $schools = $viewModel->getAllSchools();
        echo json_encode([
            'success' => true,
            'schools' => $schools,
            'count' => count($schools)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Branşları Excel için export
if ($action === 'export_branches' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    try {
        $branches = $viewModel->getAllBranches(1, 10000, 'name', 'asc');
        echo json_encode([
            'success' => true,
            'branches' => $branches,
            'count' => count($branches)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// İlçe M.E.M. Yöneticilerini Excel için export
if ($action === 'export_country_admins' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    try {
        $admins = $viewModel->filterUsers('country_admin');
        echo json_encode([
            'success' => true,
            'admins' => $admins,
            'count' => count($admins)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Okul Yöneticilerini Excel için export
if ($action === 'export_school_admins' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    try {
        $filters = [];
        // Eğer arama parametresi varsa, onu da dahil et
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = clean_input($_GET['search']);
        }
        $admins = $viewModel->getSchoolAdmins($filters);
        echo json_encode([
            'success' => true,
            'admins' => $admins,
            'count' => count($admins)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Öğretmenleri Excel için export
if ($action === 'export_teachers' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    try {
        // Tüm öğretmenleri getir (arama filtresi varsa dahil et)
        $search = isset($_GET['search']) && !empty($_GET['search']) ? clean_input($_GET['search']) : '';
        $teachers = $viewModel->filterUsers('teacher', $search);
        
        if ($teachers === false) {
            $teachers = [];
        }
        
        echo json_encode([
            'success' => true,
            'teachers' => $teachers,
            'count' => count($teachers)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// İlçeye bağlı okulları getir
if ($action === 'get_schools_by_county' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $county = isset($_GET['county']) ? trim($_GET['county']) : '';
    
    if (empty($county)) {
        echo json_encode([
            'success' => false,
            'message' => 'İlçe parametresi gereklidir'
        ]);
        exit;
    }
    
    try {
        $schools = $viewModel->getSchoolsByCounty($county);
        echo json_encode([
            'success' => true,
            'schools' => $schools,
            'count' => count($schools)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Okullar getirilirken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Dashboard grafik verisi (günlük başvuru istatistikleri)
if ($action === 'get_application_stats' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $days = isset($_GET['days']) ? (int) $_GET['days'] : 7;
    if ($days <= 0) {
        $days = 7;
    }
    if ($days > 365) {
        $days = 365;
    }

    try {
        $stmt = $db->prepare("
            SELECT
                DATE(application_date) AS date,
                COUNT(*) AS total_applications,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_applications
            FROM applications
            WHERE application_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY DATE(application_date)
            ORDER BY DATE(application_date) ASC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        echo json_encode($rows);
    } catch (Exception $e) {
        error_log('get_application_stats error: ' . $e->getMessage());
        echo json_encode([]);
    }
    exit;
}

// Başvuruları JSON formatında export et (frontend bu veriyi Excel'e dönüştürür)
if ($action === 'export_applications' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    try {
        $filters = [
            'status' => isset($_GET['status']) ? clean_input($_GET['status']) : null,
            'school_id' => isset($_GET['school_id']) ? clean_input($_GET['school_id']) : null,
            'branch_id' => isset($_GET['branch_id']) ? clean_input($_GET['branch_id']) : null,
            'search' => isset($_GET['search']) ? clean_input($_GET['search']) : null,
        ];
        $applications = $viewModel->getApplications($filters);
        echo json_encode([
            'success' => true,
            'applications' => is_array($applications) ? $applications : [],
            'count' => is_array($applications) ? count($applications) : 0
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Başvuru detayı
if ($action === 'view_application' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $applicationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($applicationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz başvuru ID']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            SELECT
                a.*,
                CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.surname, '')) AS teacher_name,
                u.email AS teacher_email,
                u.phone AS teacher_phone,
                s.name AS school_name,
                b.name AS branch_name
            FROM applications a
            LEFT JOIN users u ON u.id = a.teacher_id
            LEFT JOIN schools s ON s.id = a.school_id
            LEFT JOIN branches b ON b.id = a.branch_id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            echo json_encode(['success' => false, 'message' => 'Başvuru bulunamadı']);
            exit;
        }

        echo json_encode(['success' => true, 'application' => $application]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Başvuru detayı alınırken hata oluştu: ' . $e->getMessage()]);
    }
    exit;
}

// HTML içeriği başlamadan önce tüm AJAX işlemlerinin tamamlanmış olması gerekiyor
if (in_array($action, ['delete_school', 'delete_selected_schools', 'delete_selected_applications', 'bulk_add_schools', 'export_schools', 'export_branches', 'export_country_admins', 'export_school_admins', 'export_teachers', 'export_applications', 'get_branch', 'get_school', 'get_user', 'view_application', 'get_application_stats', 'get_school_types', 'add_school_type', 'update_school_type', 'delete_school_type', 'delete_school_type_branch_links', 'add_school_type_branch_links', 'get_schools_by_county'])) {
    exit;
}

// Okul yöneticisi düzenleme
if ($action === 'edit_school_admin' && isset($_GET['id'])) {
    $adminId = (int) $_GET['id'];
    if ($adminId <= 0) {
        redirect(BASE_URL . '/php/admin_panel.php?action=school_admins&status=error&message=Geçersiz yönetici ID');
    }
}

// Okul yöneticisi güncelleme
if ($action === 'update_school_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
    
    if ($adminId <= 0) {
        redirect(BASE_URL . '/php/admin_panel.php?action=school_admins&status=error&message=Geçersiz yönetici ID');
    }
    
    $userData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'school_id' => (int) $_POST['school_id'],
        'role' => ROLE_SCHOOL_ADMIN,
        'position' => $_POST['position'] ?? 'Okul Müdürü',
    ];
    
    // Şifre değiştirilecek mi kontrol et
    if (!empty($_POST['password'])) {
        $userData['password'] = $_POST['password'];
    }
    
    // Okul yöneticisini güncelle
    $result = $viewModel->updateSchoolAdmin($adminId, $userData, $_SESSION['user_id']);
    
    if ($result) {
        redirect(BASE_URL . '/php/admin_panel.php?action=school_admins&status=success&message=Okul yöneticisi başarıyla güncellendi');
    } else {
        redirect(BASE_URL . '/php/admin_panel.php?action=edit_school_admin&id=' . $adminId . '&status=error&message=Okul yöneticisi güncellenirken bir hata oluştu');
    }
}

// Layout için gerekli değişkenleri ayarla
$pageTitle = "Admin Paneli";
$userRoleName = "Sistem Yöneticisi";
$panelBaseUrl = BASE_URL . '/php/admin_panel.php';

$sidebarLinks = [
    ['url' => $panelBaseUrl . '?action=dashboard', 'icon' => 'fas fa-home', 'text' => 'Dashboard', 'action_key' => 'dashboard'],
    ['url' => $panelBaseUrl . '?action=applications', 'icon' => 'fas fa-file-alt', 'text' => 'Başvurular', 'action_key' => 'applications'],
    ['url' => $panelBaseUrl . '?action=schools', 'icon' => 'fas fa-school', 'text' => 'Okullar', 'action_key' => 'schools'],
    ['url' => $panelBaseUrl . '?action=school_types', 'icon' => 'fas fa-layer-group', 'text' => 'Okul Türleri', 'action_key' => 'school_types'],
    ['url' => $panelBaseUrl . '?action=branches', 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Branşlar', 'action_key' => 'branches'],
    ['url' => $panelBaseUrl . '?action=country_admins', 'icon' => 'fas fa-users-cog', 'text' => 'İlçe M.E.M. Yöneticileri', 'action_key' => 'country_admins'],
    ['url' => $panelBaseUrl . '?action=school_admins', 'icon' => 'fas fa-user-tie', 'text' => 'Okul Yöneticileri', 'action_key' => 'school_admins'],
    ['url' => $panelBaseUrl . '?action=teachers', 'icon' => 'fas fa-users', 'text' => 'Öğretmenler', 'action_key' => 'teachers', 'aliases' => ['users', 'add_user', 'edit_user']],
    ['url' => $panelBaseUrl . '?action=settings', 'icon' => 'fas fa-cog', 'text' => 'Ayarlar', 'action_key' => 'settings'],
];

$settingsLink = $panelBaseUrl . '?action=settings';

// Ana içerik dosyasını belirle
$allowed_actions = ['dashboard', 'applications', 'schools', 'school_types', 'branches', 'school_admins', 'users', 'teachers', 'settings', 'district_admins', 'country_admins', 'add_user', 'edit_user'];
$mainContentFile = $componentPath . 'dashboard.php'; // Varsayılan
if (in_array($action, $allowed_actions)) {
    // Özel eşleme: eski linkleri koru, yeni action dosya adı ile birebir
    $resolvedAction = ($action === 'district_admins') ? 'country_admins' : $action;
    $component_file = $componentPath . $resolvedAction . '.php';
    if (file_exists($component_file)) {
        $mainContentFile = $component_file;
    }
}

// Ana layout'u dahil et
require_once __DIR__ . '/layouts/admin_main_layout.php';