<?php
/**
 * E-posta gönderim yardımcısı (doğrulama kodu vb.)
 * SMTP ayarları varsa PHPMailer ile SMTP kullanır, yoksa PHP mail() kullanır.
 */
if (!defined('ROOT_DIR')) {
    return;
}

// PHPMailer yükleme (composer veya manuel)
$phpmailerAvailable = false;

// Önce composer autoload'ı dene
$phpmailerPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
    $phpmailerAvailable = class_exists('PHPMailer\PHPMailer\PHPMailer');
}

// Composer yoksa manuel kurulumu dene
if (!$phpmailerAvailable) {
    $manualPath = dirname(__DIR__) . '/vendor/PHPMailer/PHPMailer/src';
    if (file_exists($manualPath . '/PHPMailer.php')) {
        require_once $manualPath . '/PHPMailer.php';
        require_once $manualPath . '/SMTP.php';
        require_once $manualPath . '/Exception.php';
        $phpmailerAvailable = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
}

function get_email_settings($db) {
    static $settings = null;
    if ($settings !== null) return $settings;
    
    try {
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE user_id IS NULL AND setting_group = 'email'");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $settings = [
            'smtp_host' => $rows['smtp_host'] ?? '',
            'smtp_port' => (int)($rows['smtp_port'] ?? 587),
            'smtp_username' => $rows['smtp_username'] ?? '',
            'smtp_password' => $rows['smtp_password'] ?? '',
            'smtp_encryption' => !empty($rows['smtp_encryption']) && $rows['smtp_encryption'] !== '0' ? 'tls' : '',
            'from_email' => $rows['from_email'] ?? ($rows['sender_email'] ?? 'noreply@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost')),
            'from_name' => $rows['from_name'] ?? ($rows['sender_name'] ?? 'Öğretmen Portalı')
        ];
    } catch (Throwable $e) {
        error_log('get_email_settings error: ' . $e->getMessage());
        $settings = [
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => '',
            'from_email' => 'noreply@localhost',
            'from_name' => 'Öğretmen Portalı'
        ];
    }
    return $settings;
}

function send_verification_email($db, $to_email, $user_name, $code) {
    global $phpmailerAvailable;
    
    $settings = get_email_settings($db);
    $subject = 'E-posta Doğrulama Kodunuz - Öğretmen Portalı';
    $message = "Merhaba " . ($user_name ?: 'Değerli Üye') . ",\n\n";
    $message .= "Öğretmen Portalı hesabınızı aktifleştirmek için doğrulama kodunuz: " . $code . "\n\n";
    $message .= "Bu kod 24 saat geçerlidir. Doğrulama sayfasına gidip kodu girin ve hesabınızı aktifleştirin.\n\n";
    $message .= "Doğrulama sayfası: " . BASE_URL . "/php/verify_email.php?email=" . urlencode($to_email) . "\n\n";
    $message .= "İyi günler,\nÖğretmen Portalı";
    
    // Debug: Ayarları logla
    error_log("Email send attempt - SMTP Host: " . ($settings['smtp_host'] ?? 'empty') . ", PHPMailer Available: " . ($phpmailerAvailable ? 'yes' : 'no'));
    
    // SMTP ayarları varsa PHPMailer kullan
    if (!empty($settings['smtp_host']) && $phpmailerAvailable) {
        try {
            error_log("Attempting to send email via PHPMailer SMTP to: $to_email");
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->SMTPAuth = !empty($settings['smtp_username']);
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            $mail->SMTPSecure = $settings['smtp_encryption'];
            $mail->Port = $settings['smtp_port'];
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 2; // Debug modunu aç (test için)
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug ($level): $str");
            };
            
            $mail->setFrom($settings['from_email'], $settings['from_name']);
            $mail->addAddress($to_email);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->isHTML(false);
            
            $result = $mail->send();
            error_log("SMTP email sent to $to_email: " . ($result ? 'success' : 'failed'));
            if (!$result) {
                error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            }
            return $result;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("PHPMailer exception: " . $e->getMessage());
            error_log("PHPMailer ErrorInfo: " . ($mail->ErrorInfo ?? 'N/A'));
            // SMTP başarısız olursa PHP mail() ile dene
        } catch (Exception $e) {
            error_log("General exception in send_verification_email: " . $e->getMessage());
        }
    } else {
        if (empty($settings['smtp_host'])) {
            error_log("SMTP host is empty, using PHP mail()");
        }
        if (!$phpmailerAvailable) {
            error_log("PHPMailer not available, using PHP mail()");
        }
    }
    
    // SMTP yoksa veya başarısız olduysa PHP mail() kullan
    error_log("Falling back to PHP mail() for: $to_email");
    $headers = "From: " . $settings['from_name'] . " <" . $settings['from_email'] . ">\r\n";
    $headers .= "Reply-To: " . $settings['from_email'] . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $result = @mail($to_email, $subject, $message, $headers);
    error_log("PHP mail() sent to $to_email: " . ($result ? 'success' : 'failed'));
    if (!$result) {
        $lastError = error_get_last();
        error_log("PHP mail() error: " . ($lastError['message'] ?? 'Unknown error'));
    }
    return $result;
}
