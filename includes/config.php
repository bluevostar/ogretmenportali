<?php
// Proje kök dizini tanımla
define('ROOT_DIR', dirname(__DIR__));

/**
 * Ortam değişkeni okuyucu (ENV + SERVER fallback).
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    return $default;
}

// Logs dizinini kontrol et ve oluştur
$logsDir = ROOT_DIR . '/logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}

// Tarih ayarları
date_default_timezone_set('Europe/Istanbul');

// Site URL'sini otomatik tespit et
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
define('BASE_URL', $protocol . $host . '/ogretmenpro');

// Ortam ve hata politikası
$appEnv = strtolower((string) env('APP_ENV', 'production'));
$isDevelopment = in_array($appEnv, ['dev', 'development', 'local'], true);
$isTestEnv = $appEnv === 'test';
$skipDbConnection = $isTestEnv || env('SKIP_DB', '0') === '1';
error_reporting(E_ALL);
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT_DIR . '/logs/error.log');

// Session güvenlik ayarları
if (session_status() === PHP_SESSION_NONE) {
    $isSecureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('ogretmenpro_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Periyodik session yenileme (session fixation riskini düşürür)
if (!isset($_SESSION['__session_regenerated_at'])) {
    session_regenerate_id(true);
    $_SESSION['__session_regenerated_at'] = time();
} elseif (time() - (int)$_SESSION['__session_regenerated_at'] > 900) {
    session_regenerate_id(true);
    $_SESSION['__session_regenerated_at'] = time();
}

// Veritabanı bağlantı bilgileri (ENV öncelikli)
define('DB_SERVER', (string) env('DB_SERVER', 'localhost'));
define('DB_USERNAME', (string) env('DB_USERNAME', 'root'));
define('DB_PASSWORD', (string) env('DB_PASSWORD', '12345678'));
define('DB_NAME', (string) env('DB_NAME', 'ogretmenpro'));

if ($skipDbConnection) {
    // Test/izole smoke koşullarında DB zorunluluğunu kaldır.
    $db = null;
} else {
    try {
        // PDO bağlantısı oluştur
        $db = new PDO(
            "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USERNAME,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyiniz.");
    }
}

// Kullanıcı rolleri
define('ROLE_ADMIN', 'admin');
define('ROLE_COUNTRY_ADMIN', 'country_admin');
define('ROLE_SCHOOL_ADMIN', 'school_admin');
define('ROLE_TEACHER', 'teacher');

// Başvuru durumları
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Dosya yükleme limitleri
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx']);

// Token süresi (1 gün)
define('TOKEN_EXPIRY', 86400);

// Yardımcı fonksiyonlar
function clean_input($data) {
    $data = trim((string)$data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit();
    }
    echo '<script>window.location.href="' . $url . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function check_role($allowed_roles) {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/php/login.php');
    }

    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        redirect(BASE_URL . '/php/unauthorized.php');
    }

    return true;
}

function generate_token() {
    return bin2hex(random_bytes(32));
}

function get_csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function validate_csrf_request() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }
    $requestToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    return is_string($requestToken) && is_string($sessionToken) && $requestToken !== '' && hash_equals($sessionToken, $requestToken);
}

function is_md5_hash($hash) {
    return is_string($hash) && preg_match('/^[a-f0-9]{32}$/i', $hash) === 1;
}

function verify_password_compat($plainPassword, $storedHash) {
    if (!is_string($storedHash) || $storedHash === '') {
        return false;
    }
    if (password_verify($plainPassword, $storedHash)) {
        return true;
    }
    if (is_md5_hash($storedHash)) {
        return hash_equals(strtolower($storedHash), md5($plainPassword));
    }
    return false;
}

function should_upgrade_password_hash($storedHash) {
    if (is_md5_hash($storedHash)) {
        return true;
    }
    return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
}

function upgrade_password_hash_if_needed(PDO $db, $userId, $plainPassword, $storedHash) {
    if (!should_upgrade_password_hash($storedHash)) {
        return false;
    }
    $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    return $stmt->execute([':password' => $newHash, ':id' => (int)$userId]);
}
?>