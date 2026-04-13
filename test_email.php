<?php
/**
 * E-posta gönderim test sayfası
 * Bu sayfayı tarayıcıdan açarak e-posta gönderimini test edebilirsiniz
 */
require_once dirname(__FILE__) . '/includes/config.php';

// Sadece admin için
if (!is_logged_in() || $_SESSION['role'] !== ROLE_ADMIN) {
    die('Bu sayfaya sadece admin erişebilir.');
}

require_once dirname(__FILE__) . '/includes/email_helper.php';

$test_email = isset($_GET['email']) ? $_GET['email'] : '';
$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    $test_code = '123456';
    
    global $phpmailerAvailable;
    $settings = get_email_settings($db);
    
    $result .= "<h3>Test Sonuçları:</h3>";
    $result .= "<p><strong>PHPMailer Durumu:</strong> " . ($phpmailerAvailable ? '✅ Yüklü' : '❌ Yüklü değil') . "</p>";
    $result .= "<p><strong>SMTP Host:</strong> " . htmlspecialchars($settings['smtp_host'] ?? 'Boş') . "</p>";
    $result .= "<p><strong>SMTP Port:</strong> " . htmlspecialchars($settings['smtp_port'] ?? '587') . "</p>";
    $result .= "<p><strong>SMTP Kullanıcı:</strong> " . htmlspecialchars($settings['smtp_username'] ?? 'Boş') . "</p>";
    $result .= "<p><strong>Gönderen E-posta:</strong> " . htmlspecialchars($settings['from_email'] ?? 'Boş') . "</p>";
    $result .= "<p><strong>Gönderen Adı:</strong> " . htmlspecialchars($settings['from_name'] ?? 'Boş') . "</p>";
    
    if (send_verification_email($db, $test_email, 'Test Kullanıcı', $test_code)) {
        $result .= "<p style='color: green;'><strong>✅ E-posta gönderim denemesi başarılı!</strong></p>";
        $result .= "<p>E-postanızı kontrol edin. Eğer gelmediyse log dosyasını kontrol edin: <code>logs/error.log</code></p>";
    } else {
        $result .= "<p style='color: red;'><strong>❌ E-posta gönderilemedi!</strong></p>";
        $result .= "<p>Lütfen <code>logs/error.log</code> dosyasını kontrol edin.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Test Sayfası</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        form { background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input[type="email"] { width: 300px; padding: 8px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; background: #fff; border-left: 4px solid #007bff; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>E-posta Gönderim Test Sayfası</h1>
    
    <form method="POST">
        <label>
            Test E-posta Adresi:
            <input type="email" name="test_email" value="<?php echo htmlspecialchars($test_email); ?>" required>
        </label>
        <br>
        <button type="submit">Test E-postası Gönder</button>
    </form>
    
    <?php if ($result): ?>
        <div class="result">
            <?php echo $result; ?>
        </div>
    <?php endif; ?>
    
    <hr>
    <h3>Log Dosyasını Kontrol Edin</h3>
    <p>E-posta gönderim hatalarını görmek için şu dosyayı kontrol edin:</p>
    <p><code><?php echo dirname(__FILE__) . '/logs/error.log'; ?></code></p>
    
    <hr>
    <h3>SMTP Ayarlarını Kontrol Edin</h3>
    <p>Admin panelden SMTP ayarlarını kontrol edin:</p>
    <p><a href="<?php echo BASE_URL; ?>/php/admin_panel.php?action=settings">Ayarlar Sayfası</a></p>
</body>
</html>
