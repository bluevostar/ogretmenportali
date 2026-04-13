<?php
session_start();
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/config/db_connect.php';

// Veritabanı bağlantısını al
$db = getPdoConnection();

// Kullanıcı girişi kontrolü - teacher için özel kontrol
if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ' . BASE_URL . '/php/login.php');
    exit;
}

// DEBUG: teacher_id session kontrolü
error_log('DEBUG update_profile.php - teacher_id: ' . $_SESSION['teacher_id']);

// POST verilerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $surname = clean_input($_POST['surname'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $tc_kimlik_no = clean_input($_POST['tc_kimlik_no'] ?? '');
    $birth_date = clean_input($_POST['birth_date'] ?? '');
    $gender = clean_input($_POST['gender'] ?? '');
    $city = clean_input($_POST['city'] ?? '');
    $county = clean_input($_POST['county'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    
    // Zorunlu alanları kontrol et
    if (empty($name) || empty($surname) || empty($email)) {
        $_SESSION['error'] = "Ad, Soyad ve Email alanları zorunludur.";
        header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
        exit;
    }
    
    // Email formatını kontrol et
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Geçerli bir email adresi giriniz.";
        header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
        exit;
    }
    
    // Email çakışması kontrolü (kendi email'i hariç)
    try {
        $emailCheckQuery = "SELECT teacher_id FROM teachers WHERE email = :email AND teacher_id != :current_teacher_id";
        $emailCheckStmt = $db->prepare($emailCheckQuery);
        $emailCheckStmt->execute([
            ':email' => $email,
            ':current_teacher_id' => $_SESSION['teacher_id']
        ]);
        
        if ($emailCheckStmt->rowCount() > 0) {
            $_SESSION['error'] = "Bu email adresi başka bir kullanıcı tarafından kullanılmaktadır.";
            header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Email check error: " . $e->getMessage());
    }
    
    // TC Kimlik No kontrolü
    if (!empty($tc_kimlik_no) && strlen($tc_kimlik_no) !== 11) {
        $_SESSION['error'] = "TC Kimlik Numarası 11 haneli olmalıdır.";
        header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
        exit;
    }
    
    // Telefon format kontrolü
    if (!empty($phone)) {
        $phoneRegex = '/^0\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}$/';
        if (!preg_match($phoneRegex, $phone)) {
            $_SESSION['error'] = "Telefon numarası 0(5xx) xxx xx xx formatında olmalıdır.";
            header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
            exit;
        }
    }
    
    try {
        // Teachers tablosunu güncelle
        $query = "UPDATE teachers SET 
                 name = :name, 
                 surname = :surname,
                 email = :email,
                 phone = :phone,
                 tc_kimlik_no = :tc_kimlik_no,
                 birth_date = :birth_date,
                 gender = :gender,
                 city = :city,
                 county = :county,
                 address = :address,
                 updated_at = NOW()
                 WHERE teacher_id = :teacher_id";
                 
        $stmt = $db->prepare($query);
        $params = [
            ':name' => $name,
            ':surname' => $surname,
            ':email' => $email,
            ':phone' => $phone,
            ':tc_kimlik_no' => $tc_kimlik_no ?: null,
            ':birth_date' => $birth_date ?: null,
            ':gender' => $gender ?: null,
            ':city' => $city,
            ':county' => $county,
            ':address' => $address,
            ':teacher_id' => $_SESSION['teacher_id']
        ];
        
        // DEBUG: SQL ve parametreler
        error_log('DEBUG update_profile.php - SQL: ' . $query);
        error_log('DEBUG update_profile.php - Params: ' . print_r($params, true));

        $result = $stmt->execute($params);
        
        if ($result) {
            $rowCount = $stmt->rowCount();
            error_log('DEBUG update_profile.php - Güncelleme başarılı! Etkilenen satır: ' . $rowCount);
            
            // Session içindeki kullanıcı bilgilerini güncelle
            $_SESSION['name'] = $name;
            $_SESSION['surname'] = $surname;
            $_SESSION['email'] = $email;
            
            // Başarı mesajı
            $_SESSION['success'] = "Profil bilgileriniz başarıyla güncellendi.";
            
        } else {
            error_log('DEBUG update_profile.php - Güncelleme başarısız!');
            $_SESSION['error'] = "Profil güncellenirken bir sorun oluştu.";
        }
        
        // Yönlendirme
        header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
        exit;
        
    } catch(PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        error_log("SQL Query: " . $query);
        error_log("Parameters: " . print_r($params, true));
        
        $_SESSION['error'] = "Profil güncellenirken bir hata oluştu: " . $e->getMessage();
        header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
        exit;
    }
} else {
    // POST olmayan istekleri reddet
    header('Location: ' . BASE_URL . '/php/teacher_panel.php?action=teacher_profile');
    exit;
}
?> 