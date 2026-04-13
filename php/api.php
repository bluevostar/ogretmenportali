<?php
/**
 * Merkezi API Endpoint
 * Tüm AJAX istekleri bu dosya üzerinden yönetilir.
 */

// Yanıt formatını ve önbelleklemeyi ayarla
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Gerekli dosyaları dahil et
require_once dirname(__DIR__) . '/includes/config.php';

/**
 * JSON formatında yanıt gönderip işlemi sonlandıran yardımcı fonksiyon.
 * @param array $data Gönderilecek veri
 */
function send_json_response($data) {
    // Çıktı tamponlamasını temizle
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode($data);
    exit;
}

try {
    $isDevelopment = in_array(strtolower((string) env('APP_ENV', 'production')), ['dev', 'development', 'local'], true);

    // --- Yetkilendirme Kontrolü ---
    if (!is_logged_in()) {
        http_response_code(401); // Unauthorized
        send_json_response(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapın.']);
    }

    // CSRF - API için kademeli geçiş: prod'da şimdilik logla, flag açılınca zorunluya geçir
    $method = $_SERVER['REQUEST_METHOD'];
    $csrfEnforceApi = env('CSRF_ENFORCE_API', '0') === '1';
    if (in_array($method, ['POST', 'PUT', 'DELETE'], true) && !validate_csrf_request()) {
        if ($csrfEnforceApi) {
            http_response_code(419);
            send_json_response(['success' => false, 'message' => 'Güvenlik doğrulaması başarısız.']);
        }
        error_log("API CSRF warning: action=" . ($_GET['action'] ?? '') . ", user_id=" . ($_SESSION['user_id'] ?? 'guest'));
    }

    // --- Rol Bazlı ViewModel Yükleme ---
    $userRole = $_SESSION['role'];
    $viewModel = null;

    switch ($userRole) {
        case ROLE_ADMIN:
            require_once dirname(__DIR__) . '/models/viewmodels/AdminViewModel.php';
            $viewModel = new AdminViewModel($db);
            break;
        case ROLE_COUNTRY_ADMIN:
            require_once dirname(__DIR__) . '/models/viewmodels/CountryAdminViewModel.php';
            $viewModel = new CountryAdminViewModel($db);
            break;
        case ROLE_SCHOOL_ADMIN:
            require_once dirname(__DIR__) . '/models/viewmodels/SchoolAdminViewModel.php';
            $viewModel = new SchoolAdminViewModel($db);
            break;
        default:
            http_response_code(403); // Forbidden
            send_json_response(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
    }

    // --- İstekleri Yönlendirme (Routing) ---
    $action = isset($_GET['action']) ? clean_input($_GET['action']) : '';
    $data = [];

    if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];
    }

    // Gelen isteğe göre ilgili işlemi yap
    switch ($action) {
        case 'delete_school':
            if ($method !== 'POST' || !isset($data['school_id'])) {
                throw new InvalidArgumentException('Geçersiz istek: school_id eksik.');
            }
            $result = $viewModel->deleteSchool($data['school_id'], $_SESSION['user_id']);
            send_json_response(['success' => $result, 'message' => $result ? 'Okul başarıyla silindi.' : 'Okul silinirken bir hata oluştu.']);
            break;

        case 'delete_selected_schools':
            if ($method !== 'POST' || !isset($data['school_ids']) || !is_array($data['school_ids'])) {
                throw new InvalidArgumentException('Geçersiz veri.');
            }
            $result = $viewModel->deleteSelectedSchools($data['school_ids'], $_SESSION['user_id']);
            send_json_response(['success' => $result, 'message' => $result ? 'Seçili okullar başarıyla silindi.' : 'Okullar silinirken bir hata oluştu.']);
            break;

        case 'toggle_school_status':
             if ($method !== 'POST' || !isset($data['school_id']) || !isset($data['status'])) {
                throw new InvalidArgumentException('Eksik parametre.');
            }
            $result = $viewModel->updateSchoolStatus((int)$data['school_id'], $data['status']);
            send_json_response(['success' => $result, 'message' => $result ? 'Okul durumu güncellendi.' : 'Okul durumu güncellenemedi.']);
            break;

        // Buraya diğer AJAX aksiyonları eklenebilir (örn: get_user_details, delete_branch vb.)

        default:
            http_response_code(400); // Bad Request
            send_json_response(['success' => false, 'message' => 'Desteklenmeyen veya geçersiz işlem.']);
            break;
    }

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    $safeMessage = isset($isDevelopment) && $isDevelopment
        ? ('Sunucu hatası: ' . $e->getMessage())
        : 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.';
    send_json_response(['success' => false, 'message' => $safeMessage]);
}