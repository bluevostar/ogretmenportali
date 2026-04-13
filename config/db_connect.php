<?php
/**
 * Veritabanı bağlantı ayarları
 */
if (!defined('DB_SERVER')) {
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '12345678');
    define('DB_NAME', 'ogretmenpro');
}

/**
 * MySQLi veritabanı bağlantısını oluştur
 */
function getDbConnection() {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Bağlantıyı kontrol et
    if (!$conn) {
        die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
    }
    
    // Türkçe karakter desteği
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}

/**
 * PDO veritabanı bağlantısını oluştur
 */
function getPdoConnection() {
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("PDO Veritabanı bağlantısı başarısız: " . $e->getMessage());
    }
}
?> 