<?php
// Ajax isteklerini tespit et
$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
                || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
                || (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false);

// Ajax isteği geldiyse
if ($isAjaxRequest) {
    ob_end_clean();
    ob_start();
    
    require_once '../includes/config.php';
    require_once '../models/viewmodels/SchoolAdminViewModel.php';
    
    // Kullanıcı giriş yapmamışsa veya okul yöneticisi değilse hata döndür
    if (!is_logged_in() || $_SESSION['role'] != ROLE_SCHOOL_ADMIN) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
        exit;
    }
    
    $viewModel = new SchoolAdminViewModel($db);
    $action = isset($_GET['action']) ? clean_input($_GET['action']) : '';
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Başvuru onaylama işlemi
    if ($action === 'approve_application') {
        if (!validate_csrf_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız.']);
            exit;
        }
        if (!isset($data['application_id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz istek: application_id eksik'
            ]);
            exit;
        }
        
        $success = $viewModel->approveApplication($data['application_id']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Başvuru başarıyla onaylandı.' : 'Başvuru onaylanırken bir hata oluştu.'
        ]);
        exit;
    }
    
    // Başvuru reddetme işlemi
    if ($action === 'reject_application') {
        if (!validate_csrf_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız.']);
            exit;
        }
        if (!isset($data['application_id']) || !isset($data['rejection_reason'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz istek: Gerekli alanlar eksik'
            ]);
            exit;
        }
        
        $success = $viewModel->rejectApplication($data['application_id'], $data['rejection_reason']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Başvuru başarıyla reddedildi.' : 'Başvuru reddedilirken bir hata oluştu.'
        ]);
        exit;
    }
    
    // Diğer AJAX istekleri aşağıdaki action handler'larına bırakılır.
}

// Normal sayfa yüklemesi
require_once '../includes/config.php';
require_once '../models/viewmodels/SchoolAdminViewModel.php';

// Kullanıcı giriş yapmamışsa veya okul yöneticisi değilse login sayfasına yönlendir
if (!is_logged_in() || $_SESSION['role'] != ROLE_SCHOOL_ADMIN) {
    redirect(BASE_URL . '/php/login.php');
}

// ViewModel'i başlat
$viewModel = new SchoolAdminViewModel($db);

// Sayfa işlemleri
$action = isset($_GET['action']) ? clean_input($_GET['action']) : 'dashboard';

// Excel export işlemi
if ($action === 'export' && isset($_POST['export_data'])) {
    $viewModel->exportToExcel();
}

// Başvuru işlemleri
if (isset($_POST['approve_application'])) {
    $applicationId = clean_input($_POST['application_id']);
    $viewModel->approveApplication($applicationId);
    redirect(BASE_URL . '/php/school-admin-panel.php?action=applications&status=success&message=Başvuru onaylandı');
}

if (isset($_POST['reject_application'])) {
    $applicationId = clean_input($_POST['application_id']);
    $rejectionReason = isset($_POST['rejection_reason']) ? clean_input($_POST['rejection_reason']) : null;
    $viewModel->rejectApplication($applicationId, $rejectionReason);
    redirect(BASE_URL . '/php/school-admin-panel.php?action=applications&status=success&message=Başvuru reddedildi');
}

if ($action === 'export_teachers' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $teachers = $viewModel->getTeachers(
        isset($_GET['branch']) ? clean_input($_GET['branch']) : '',
        isset($_GET['status']) ? clean_input($_GET['status']) : '',
        isset($_GET['search']) ? clean_input($_GET['search']) : ''
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=teachers_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Ad Soyad', 'E-posta', 'Branş', 'Deneyim', 'Durum']);
    foreach ($teachers as $teacher) {
        fputcsv($out, [
            $teacher['id'] ?? '',
            trim(($teacher['name'] ?? '') . ' ' . ($teacher['surname'] ?? '')),
            $teacher['email'] ?? '',
            $teacher['branch_name'] ?? '',
            $teacher['experience'] ?? '',
            $teacher['status'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

if ($action === 'get_teacher_detail' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $teacherId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($teacherId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz öğretmen ID']);
        exit;
    }

    try {
        $adminSchoolStmt = $db->prepare("SELECT school_id FROM users WHERE id = ? AND role = ?");
        $adminSchoolStmt->execute([$_SESSION['user_id'], ROLE_SCHOOL_ADMIN]);
        $adminSchoolId = (int) ($adminSchoolStmt->fetchColumn() ?: 0);

        if ($adminSchoolId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Yetkili okul bilgisi bulunamadı']);
            exit;
        }

        $stmt = $db->prepare("
            SELECT
                u.id, u.name, u.surname, u.email, u.phone, u.status,
                tp.experience, tp.education, b.name AS branch_name
            FROM applications a
            INNER JOIN users u ON u.id = a.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN branches b ON b.id = tp.branch_id
            WHERE a.school_id = ? AND u.id = ?
            ORDER BY a.application_date DESC
            LIMIT 1
        ");
        $stmt->execute([$adminSchoolId, $teacherId]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$teacher) {
            echo json_encode(['success' => false, 'message' => 'Öğretmen bulunamadı veya erişim yetkisi yok']);
            exit;
        }

        $html = '
            <h3 class="text-lg font-semibold text-gray-900 mb-4">' . htmlspecialchars(trim(($teacher['name'] ?? '') . ' ' . ($teacher['surname'] ?? ''))) . '</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="font-medium text-gray-700">E-posta:</span> ' . htmlspecialchars($teacher['email'] ?? '-') . '</div>
                <div><span class="font-medium text-gray-700">Telefon:</span> ' . htmlspecialchars($teacher['phone'] ?? '-') . '</div>
                <div><span class="font-medium text-gray-700">Branş:</span> ' . htmlspecialchars($teacher['branch_name'] ?? '-') . '</div>
                <div><span class="font-medium text-gray-700">Deneyim:</span> ' . htmlspecialchars((string)($teacher['experience'] ?? '-')) . '</div>
                <div class="md:col-span-2"><span class="font-medium text-gray-700">Eğitim:</span> ' . htmlspecialchars($teacher['education'] ?? '-') . '</div>
            </div>
        ';

        echo json_encode(['success' => true, 'html' => $html]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Öğretmen detayı alınırken hata oluştu']);
    }
    exit;
}

if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=CSRF doğrulaması başarısız');
    }
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $address = clean_input($_POST['address'] ?? '');

    if ($name === '' || $email === '') {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Ad ve e-posta zorunludur');
    }

    $profileImagePath = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $filename = 'school_admin_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $profileImagePath = 'uploads/profiles/' . $filename;
        }
    }

    $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, address = :address";
    $params = [
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':id' => (int) $_SESSION['user_id']
    ];
    if ($profileImagePath !== null) {
        $sql .= ", profile_photo = :profile_photo";
        $params[':profile_photo'] = $profileImagePath;
    }
    $sql .= " WHERE id = :id AND role = :role";
    $params[':role'] = ROLE_SCHOOL_ADMIN;

    try {
        $stmt = $db->prepare($sql);
        $ok = $stmt->execute($params);
        if ($ok) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=success&message=Profil bilgileri güncellendi');
        }
    } catch (Throwable $e) {
        error_log('school-admin update_profile error: ' . $e->getMessage());
    }

    redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Profil güncellenemedi');
}

if ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=CSRF doğrulaması başarısız');
    }
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Tüm şifre alanları zorunludur');
    }
    if ($newPassword !== $confirmPassword) {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Yeni şifreler eşleşmiyor');
    }

    try {
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ? AND role = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION['user_id'], ROLE_SCHOOL_ADMIN]);
        $storedHash = (string)($stmt->fetchColumn() ?: '');
        if ($storedHash === '' || !verify_password_compat($currentPassword, $storedHash)) {
            redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Mevcut şifre hatalı');
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password = ? WHERE id = ? AND role = ?");
        $ok = $update->execute([$newHash, (int)$_SESSION['user_id'], ROLE_SCHOOL_ADMIN]);
        if ($ok) {
            redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=success&message=Şifre güncellendi');
        }
    } catch (Throwable $e) {
        error_log('school-admin change_password error: ' . $e->getMessage());
    }

    redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Şifre güncellenemedi');
}

if ($action === 'update_notifications' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=CSRF doğrulaması başarısız');
    }
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $applicationNotifications = isset($_POST['application_notifications']) ? 1 : 0;
    $systemNotifications = isset($_POST['system_notifications']) ? 1 : 0;

    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS user_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL UNIQUE,
                email_notifications TINYINT(1) NOT NULL DEFAULT 0,
                application_notifications TINYINT(1) NOT NULL DEFAULT 0,
                system_notifications TINYINT(1) NOT NULL DEFAULT 0,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $stmt = $db->prepare("
            INSERT INTO user_settings (user_id, email_notifications, application_notifications, system_notifications)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                email_notifications = VALUES(email_notifications),
                application_notifications = VALUES(application_notifications),
                system_notifications = VALUES(system_notifications)
        ");
        $ok = $stmt->execute([(int)$_SESSION['user_id'], $emailNotifications, $applicationNotifications, $systemNotifications]);
        if ($ok) {
            redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=success&message=Bildirim ayarları güncellendi');
        }
    } catch (Throwable $e) {
        error_log('school-admin update_notifications error: ' . $e->getMessage());
    }

    redirect(BASE_URL . '/php/school-admin-panel.php?action=settings&status=error&message=Bildirim ayarları güncellenemedi');
}

if ($action === 'view_teacher' && isset($_GET['id'])) {
    $teacherId = (int) $_GET['id'];
    redirect(BASE_URL . '/php/school-admin-panel.php?action=teachers&teacher_id=' . $teacherId);
}
// Logout functionality
if ($action === 'logout') {
    session_destroy();
    redirect(BASE_URL . '/php/login.php');
}

// Layout için gerekli değişkenleri ayarla
$pageTitle = "Okul Yönetici Paneli";
$userRoleName = "Okul Yöneticisi";
$panelBaseUrl = BASE_URL . '/php/school-admin-panel.php';

$sidebarLinks = [
    ['url' => $panelBaseUrl . '?action=dashboard', 'icon' => 'fas fa-home', 'text' => 'Dashboard', 'action_key' => 'dashboard'],
    ['url' => $panelBaseUrl . '?action=applications', 'icon' => 'fas fa-file-alt', 'text' => 'Başvurular', 'action_key' => 'applications'],
    ['url' => $panelBaseUrl . '?action=teachers', 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Öğretmenler', 'action_key' => 'teachers'],
    ['url' => $panelBaseUrl . '?action=settings', 'icon' => 'fas fa-cog', 'text' => 'Ayarlar', 'action_key' => 'settings'],
];

$settingsLink = $panelBaseUrl . '?action=settings';

// Ana içerik dosyasını belirle
$componentPath = dirname(__DIR__) . '/components/school-admin/';
$allowed_actions = ['dashboard', 'applications', 'teachers', 'settings', 'profile'];
$mainContentFile = $componentPath . 'dashboard.php'; // Varsayılan
if (in_array($action, $allowed_actions)) {
    $component_file = $componentPath . $action . '.php';
    if (file_exists($component_file)) {
        $mainContentFile = $component_file;
    }
}

// Ana layout'u dahil et
require_once __DIR__ . '/layouts/admin_main_layout.php';