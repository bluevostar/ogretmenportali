<?php
// Debug için hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Normal sayfa yüklemesi
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/models/viewmodels/AdminViewModel.php';
require_once dirname(__DIR__) . '/models/viewmodels/CountryAdminViewModel.php';

// Kullanıcı giriş yapmamışsa veya admin değilse admin giriş sayfasına yönlendir
if (!is_logged_in() || $_SESSION['role'] != ROLE_COUNTRY_ADMIN) {
    redirect(BASE_URL . '/php/admin_login.php');
}

// ViewModel'i başlat - global $db değişkenini kullan
$viewModel = new CountryAdminViewModel($db);

// Sayfa işlemleri - action değişkenini al
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Debug için action değerini konsola yazdır
echo "<!-- Current action: " . $action . " -->";

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
    redirect(BASE_URL . '/php/country_admin_panel.php?action=applications&status=success&message=Başvuru onaylandı');
}

// Başvuru reddetme işlemi
if (isset($_POST['reject_application'])) {
    $applicationId = $_POST['application_id'];
    $rejectionReason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;
    $viewModel->rejectApplication($applicationId, $rejectionReason);
    redirect(BASE_URL . '/php/country_admin_panel.php?action=applications&status=success&message=Başvuru reddedildi');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=users&status=success&message=Kullanıcı başarıyla eklendi.');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=users&status=success&message=Kullanıcı başarıyla güncellendi.');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=Genel ayarlar başarıyla güncellendi.');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=E-posta ayarları başarıyla güncellendi.');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=Güvenlik ayarları başarıyla güncellendi.');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=Sistem ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Sistem ayarları güncellenirken bir hata oluştu.';
    }
}

if ($action === 'clear_cache' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $viewModel->clearCache();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
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
    
    $result = $viewModel->updateUser($_SESSION['user_id'], $userData);
    
    if ($result) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=Profil bilgileri başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Profil bilgileri güncellenirken bir hata oluştu.';
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=settings&status=success&message=Bildirim ayarları başarıyla güncellendi.');
    } else {
        $_SESSION['error'] = 'Bildirim ayarları güncellenirken bir hata oluştu.';
    }
}

// Logout functionality
if ($action === 'logout') {
    session_destroy();
    redirect(BASE_URL . '/php/admin_login.php');
}

// Ayarları getir
if ($action === 'settings') {
    $settings = $viewModel->getSettings($_SESSION['user_id']);
    $notificationSettings = $viewModel->getNotificationSettings($_SESSION['user_id']);
}

// Okul yönetimi işlemleri
if ($action === 'get_school') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID']);
        exit;
    }
    
    $school = $viewModel->getSchoolById($id, $_SESSION['user_id']);
    echo json_encode(['success' => true, 'school' => $school]);
    exit;
}

if ($action === 'get_school_admins') {
    $admins = $viewModel->getSchoolAdmins($_SESSION['user_id']);
    echo json_encode($admins);
    exit;
}

if ($action === 'add_school') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        echo json_encode(['success' => false, 'message' => 'Okul adı zorunludur']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO schools
                (school_code, name, school_type, city, county, country, address, phone, email, description, status, country_admin_id)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([
            trim($_POST['school_code'] ?? '') ?: null,
            $name,
            trim($_POST['school_type'] ?? '') ?: null,
            trim($_POST['city'] ?? '') ?: null,
            trim($_POST['county'] ?? '') ?: null,
            trim($_POST['country'] ?? '') ?: 'Türkiye',
            trim($_POST['address'] ?? '') ?: null,
            trim($_POST['phone'] ?? '') ?: null,
            trim($_POST['email'] ?? '') ?: null,
            trim($_POST['description'] ?? '') ?: null,
            in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
            (int)$_SESSION['user_id']
        ]);
        echo json_encode(['success' => (bool)$ok]);
    } catch (Throwable $e) {
        error_log('country_admin add_school error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Okul eklenirken hata oluştu']);
    }
    exit;
}

if ($action === 'update_school') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $id = isset($_POST['school_id']) ? (int)$_POST['school_id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz okul ID']);
        exit;
    }

    try {
        $stmt = $db->prepare("
            UPDATE schools SET
                school_code = ?,
                name = ?,
                school_type = ?,
                city = ?,
                county = ?,
                country = ?,
                address = ?,
                phone = ?,
                email = ?,
                description = ?,
                status = ?
            WHERE id = ? AND country_admin_id = ?
        ");
        $ok = $stmt->execute([
            trim($_POST['school_code'] ?? '') ?: null,
            trim($_POST['name'] ?? ''),
            trim($_POST['school_type'] ?? '') ?: null,
            trim($_POST['city'] ?? '') ?: null,
            trim($_POST['county'] ?? '') ?: null,
            trim($_POST['country'] ?? '') ?: 'Türkiye',
            trim($_POST['address'] ?? '') ?: null,
            trim($_POST['phone'] ?? '') ?: null,
            trim($_POST['email'] ?? '') ?: null,
            trim($_POST['description'] ?? '') ?: null,
            in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
            $id,
            (int)$_SESSION['user_id']
        ]);
        echo json_encode(['success' => (bool)$ok]);
    } catch (Throwable $e) {
        error_log('country_admin update_school error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Okul güncellenirken hata oluştu']);
    }
    exit;
}

if ($action === 'get_application' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz başvuru ID']);
        exit;
    }
    $application = $viewModel->getApplicationDetails($id);
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Başvuru bulunamadı']);
        exit;
    }
    echo json_encode(array_merge(['success' => true], $application));
    exit;
}

if ($action === 'approve_application' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($data['application_id']) ? (int)$data['application_id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz başvuru ID']);
        exit;
    }
    $ok = $viewModel->updateApplicationStatus($id, 'approved');
    echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Başvuru onaylandı' : 'Başvuru onaylanamadı']);
    exit;
}

if ($action === 'reject_application' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($data['application_id']) ? (int)$data['application_id'] : 0;
    $reason = trim((string)($data['rejection_reason'] ?? ''));
    if ($id <= 0 || $reason === '') {
        echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
        exit;
    }
    // Bu ViewModel metodu sadece status update yapıyor; nedeni ayrıca yazıyoruz.
    $ok = $viewModel->updateApplicationStatus($id, 'rejected');
    if ($ok) {
        try {
            $stmt = $db->prepare("UPDATE applications SET rejection_reason = ? WHERE id = ?");
            $stmt->execute([$reason, $id]);
        } catch (Throwable $e) {
            error_log('country_admin reject reason save error: ' . $e->getMessage());
        }
    }
    echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Başvuru reddedildi' : 'Başvuru reddedilemedi']);
    exit;
}

if ($action === 'export_applications' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $statusFilter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
    $applications = $viewModel->getAllApplications($statusFilter);
    echo json_encode([
        'success' => true,
        'applications' => is_array($applications) ? $applications : [],
        'count' => is_array($applications) ? count($applications) : 0
    ]);
    exit;
}

if ($action === 'get_user_details' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı ID']);
        exit;
    }
    $user = $viewModel->getUserDetails($id);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
        exit;
    }
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
}

if ($action === 'delete_school_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $adminId = isset($data['admin_id']) ? (int)$data['admin_id'] : (isset($data['id']) ? (int)$data['id'] : 0);
    if ($adminId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici ID']);
        exit;
    }
    try {
        $db->beginTransaction();
        $db->prepare("DELETE FROM school_admins WHERE user_id = ?")->execute([$adminId]);
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = ?");
        $ok = $stmt->execute([$adminId, ROLE_SCHOOL_ADMIN]);
        $db->commit();
        echo json_encode(['success' => (bool)$ok]);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('country_admin delete_school_admin error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Yönetici silinemedi']);
    }
    exit;
}

if ($action === 'delete_selected_school_admins' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $adminIds = isset($data['admin_ids']) && is_array($data['admin_ids']) ? array_map('intval', $data['admin_ids']) : [];
    $adminIds = array_values(array_filter($adminIds, function ($v) { return $v > 0; }));
    if (empty($adminIds)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz yönetici listesi']);
        exit;
    }
    try {
        $placeholders = implode(',', array_fill(0, count($adminIds), '?'));
        $db->beginTransaction();
        $stmt1 = $db->prepare("DELETE FROM school_admins WHERE user_id IN ($placeholders)");
        $stmt1->execute($adminIds);
        $stmt2 = $db->prepare("DELETE FROM users WHERE role = ? AND id IN ($placeholders)");
        $ok = $stmt2->execute(array_merge([ROLE_SCHOOL_ADMIN], $adminIds));
        $db->commit();
        echo json_encode(['success' => (bool)$ok]);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('country_admin delete_selected_school_admins error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Toplu silme başarısız']);
    }
    exit;
}

if ($action === 'bulk_add_schools' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $schools = isset($data['schools']) && is_array($data['schools']) ? $data['schools'] : [];
    if (empty($schools)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
        exit;
    }
    $result = $viewModel->bulkAddSchools($schools, (int)$_SESSION['user_id']);
    echo json_encode($result);
    exit;
}

if ($action === 'delete_selected_schools' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $schoolIds = isset($data['school_ids']) && is_array($data['school_ids']) ? array_map('intval', $data['school_ids']) : [];
    $schoolIds = array_values(array_filter($schoolIds, function ($v) { return $v > 0; }));
    if (empty($schoolIds)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz okul listesi']);
        exit;
    }
    try {
        $placeholders = implode(',', array_fill(0, count($schoolIds), '?'));
        $params = array_merge($schoolIds, [(int)$_SESSION['user_id']]);
        $stmt = $db->prepare("DELETE FROM schools WHERE id IN ($placeholders) AND country_admin_id = ?");
        $ok = $stmt->execute($params);
        echo json_encode(['success' => (bool)$ok, 'deleted_count' => $stmt->rowCount()]);
    } catch (Throwable $e) {
        error_log('country_admin delete_selected_schools error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Okullar silinemedi']);
    }
    exit;
}

if ($action === 'delete_branches' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        echo json_encode(['success' => false, 'message' => 'CSRF doğrulaması başarısız']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $branchIds = isset($data['branch_ids']) && is_array($data['branch_ids']) ? array_map('intval', $data['branch_ids']) : [];
    $branchIds = array_values(array_filter($branchIds, function ($v) { return $v > 0; }));
    if (empty($branchIds)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz branş listesi']);
        exit;
    }
    try {
        $placeholders = implode(',', array_fill(0, count($branchIds), '?'));
        $stmt = $db->prepare("DELETE FROM branches WHERE id IN ($placeholders)");
        $ok = $stmt->execute($branchIds);
        echo json_encode(['success' => (bool)$ok, 'deleted_count' => $stmt->rowCount()]);
    } catch (Throwable $e) {
        error_log('country_admin delete_branches error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Branşlar silinemedi']);
    }
    exit;
}

if ($action === 'add_school_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=add_school_admin&status=error&message=CSRF doğrulaması başarısız');
    }

    $name = clean_input($_POST['name'] ?? '');
    $surname = clean_input($_POST['surname'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $schoolId = (int)($_POST['school_id'] ?? 0);
    $phone = clean_input($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $schoolId <= 0) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=add_school_admin&status=error&message=Zorunlu alanları doldurun');
    }

    try {
        $checkSchool = $db->prepare("SELECT id FROM schools WHERE id = ? AND country_admin_id = ? LIMIT 1");
        $checkSchool->execute([$schoolId, (int)$_SESSION['user_id']]);
        if (!$checkSchool->fetch()) {
            redirect(BASE_URL . '/php/country_admin_panel.php?action=add_school_admin&status=error&message=Seçilen okula yetkiniz yok');
        }

        $exists = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $exists->execute([$email]);
        if ($exists->fetch()) {
            redirect(BASE_URL . '/php/country_admin_panel.php?action=add_school_admin&status=error&message=Bu e-posta zaten kullanılıyor');
        }

        $db->beginTransaction();
        $userInsert = $db->prepare("
            INSERT INTO users (name, surname, email, password, role, phone, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        $userInsert->execute([
            $name,
            $surname,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            ROLE_SCHOOL_ADMIN,
            $phone
        ]);
        $newUserId = (int)$db->lastInsertId();

        $saInsert = $db->prepare("
            INSERT INTO school_admins (user_id, school_id, position, status)
            VALUES (?, ?, ?, 'active')
        ");
        $saInsert->execute([$newUserId, $schoolId, 'Okul Müdürü']);

        $db->commit();
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=success&message=Okul yöneticisi eklendi');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('country_admin add_school_admin error: ' . $e->getMessage());
        redirect(BASE_URL . '/php/country_admin_panel.php?action=add_school_admin&status=error&message=Okul yöneticisi eklenemedi');
    }
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

// Kullanıcı listesini getir
$users = $viewModel->getAllUsers();

// HTML içeriği başlamadan önce tüm AJAX işlemlerinin tamamlanmış olması gerekiyor
if (in_array($action, ['delete_school', 'delete_selected_schools', 'bulk_add_schools', 'get_application', 'approve_application', 'reject_application', 'export_applications', 'get_user_details', 'delete_school_admin', 'delete_selected_school_admins', 'delete_branches'])) {
    exit;
}

// Okul yöneticisi düzenleme
if ($action === 'edit_school_admin' && isset($_GET['id'])) {
    $adminId = (int) $_GET['id'];
    if ($adminId <= 0) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=error&message=Geçersiz yönetici ID');
    }
}

// Okul yöneticisi güncelleme
if ($action === 'update_school_admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminId = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
    
    if ($adminId <= 0) {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=error&message=Geçersiz yönetici ID');
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
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins&status=success&message=Okul yöneticisi başarıyla güncellendi');
    } else {
        redirect(BASE_URL . '/php/country_admin_panel.php?action=edit_school_admin&id=' . $adminId . '&status=error&message=Okul yöneticisi güncellenirken bir hata oluştu');
    }
}

// Layout için gerekli değişkenleri ayarla
$pageTitle = "İlçe MEM Yönetici Paneli";
$userRoleName = "İlçe Yöneticisi";
$panelBaseUrl = BASE_URL . '/php/country_admin_panel.php';

$sidebarLinks = [
    ['url' => $panelBaseUrl . '?action=dashboard', 'icon' => 'fas fa-home', 'text' => 'Dashboard', 'action_key' => 'dashboard'],
    ['url' => $panelBaseUrl . '?action=applications', 'icon' => 'fas fa-file-alt', 'text' => 'Başvurular', 'action_key' => 'applications'],
    ['url' => $panelBaseUrl . '?action=schools', 'icon' => 'fas fa-school', 'text' => 'Okullar', 'action_key' => 'schools'],
    ['url' => $panelBaseUrl . '?action=branches', 'icon' => 'fas fa-chalkboard-teacher', 'text' => 'Branşlar', 'action_key' => 'branches'],
    ['url' => $panelBaseUrl . '?action=school_admins', 'icon' => 'fas fa-user-tie', 'text' => 'Okul Yöneticileri', 'action_key' => 'school_admins'],
    ['url' => $panelBaseUrl . '?action=users', 'icon' => 'fas fa-users', 'text' => 'Öğretmenler', 'action_key' => 'users', 'aliases' => ['teachers']],
];

$settingsLink = $panelBaseUrl . '?action=settings';

// Ana içerik dosyasını belirle
$componentPath = dirname(__DIR__) . '/components/country_admin/';
$allowed_actions = ['dashboard', 'applications', 'schools', 'branches', 'school_admins', 'users', 'teachers', 'settings', 'add_school_admin', 'edit_school_admin'];
$mainContentFile = $componentPath . 'dashboard.php'; // Varsayılan
if (in_array($action, $allowed_actions)) {
    $component_file = $componentPath . $action . '.php';
    if (file_exists($component_file)) {
        $mainContentFile = $component_file;
    }
}

// Ana layout'u dahil et
require_once __DIR__ . '/layouts/admin_main_layout.php';