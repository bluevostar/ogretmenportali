<?php
include_once '../includes/config.php';

// Eğer kullanıcı zaten giriş yapmışsa ana sayfaya yönlendir
if (is_logged_in()) {
    redirect(BASE_URL);
}

// Hata ve başarı mesajları için değişkenler
$errors = [];
$success = false;

// Kayıt formu kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validate_csrf_request()) {
        $errors[] = "Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.";
    }

    // Temel bilgiler
    $name = clean_input($_POST['name']);
    $surname = clean_input($_POST['surname']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = clean_input($_POST['phone']);
    $tc_kimlik_no = clean_input($_POST['tc_kimlik_no']);
    $gender = isset($_POST['gender']) ? clean_input($_POST['gender']) : '';
    $birth_date = isset($_POST['birth_date']) ? clean_input($_POST['birth_date']) : '';
    $role = ROLE_TEACHER; // Sadece öğretmen rolüne izin veriyoruz
    
    // Öğretmen kayıtları için ekstra bilgiler
    $branch = isset($_POST['branch']) ? clean_input($_POST['branch']) : '';
    $education = isset($_POST['education']) ? clean_input($_POST['education']) : '';
    $experience = isset($_POST['experience']) ? clean_input($_POST['experience']) : '';
    
    // Validasyon
    if (empty($name)) {
        $errors[] = "Ad alanı zorunludur.";
    }
    
    if (empty($surname)) {
        $errors[] = "Soyad alanı zorunludur.";
    }
    
    if (empty($email)) {
        $errors[] = "E-posta alanı zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    } else {
        // E-posta adresi kullanılıyor mu kontrol et
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Bu e-posta adresi zaten kullanılıyor.";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Şifre alanı zorunludur.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Şifreler eşleşmiyor.";
    }
    
    // CV yükleme
    $cv_file = '';
    if ($role == ROLE_TEACHER && isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $file_name = $_FILES['cv_file']['name'];
        $file_size = $_FILES['cv_file']['size'];
        $file_tmp = $_FILES['cv_file']['tmp_name'];
        $file_type = $_FILES['cv_file']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $extensions = ["pdf", "doc", "docx"];
        
        if (!in_array($file_ext, $extensions)) {
            $errors[] = "CV için sadece PDF, DOC veya DOCX dosyası yükleyebilirsiniz.";
        }
        
        if ($file_size > 5242880) {
            $errors[] = "CV dosyası 5MB'den küçük olmalıdır.";
        }
        
        if (empty($errors)) {
            $cv_file = "uploads/cv/" . time() . "_" . $file_name;
            move_uploaded_file($file_tmp, "../" . $cv_file);
        }
    }
    
    // Hata yoksa kayıt işlemini gerçekleştir
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Transaction başlat
            $db->beginTransaction();
            
            // Kullanıcı kaydı (users tablosuna)
            $stmt = $db->prepare("INSERT INTO users (name, surname, email, password, role, phone, tc_kimlik_no, gender, birth_date, city, county) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $surname, 
                $email, 
                $hashed_password, 
                $role, 
                $phone, 
                $tc_kimlik_no, 
                $gender, 
                $birth_date,
                isset($_POST['city']) ? clean_input($_POST['city']) : '',
                isset($_POST['county']) ? clean_input($_POST['county']) : ''
            ]);
            
            // Transaction tamamla
            $db->commit();
            $success = true;
            
        } catch (PDOException $e) {
            // Hata durumunda geri al
            $db->rollBack();
            $errors[] = "Kayıt sırasında bir hata oluştu: " . $e->getMessage();
        }
    }
    
    if ($success) {
        $_SESSION['success'] = "Kayıt işlemi başarıyla tamamlandı. Şimdi giriş yapabilirsiniz.";
        redirect(BASE_URL . '/php/login.php');
    }
}

// URL'den role parametresini kaldırıyoruz, sadece öğretmen kaydı yapılabilir
$roleTitle = 'Öğretmen Kaydı';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Öğretmen İş Başvuru Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl w-full flex rounded-2xl shadow-2xl overflow-hidden">
        <!-- Sol taraf - Resim bölümü -->
        <div class="w-0 md:w-1/2 hidden md:block relative overflow-hidden">
            <!-- Resim -->
            <img src="<?php echo BASE_URL; ?>/assets/images/register.jpg" alt="Eğitim Resmi" class="object-cover h-full w-full">
        </div>
        
        <!-- Sağ taraf - Kayıt formu -->
        <div class="w-full md:w-1/2 bg-white p-8 md:p-12">
            <div class="max-w-md mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-800">Kayıt Ol</h2>
                    <p class="text-gray-500 mt-1">ÖğretmenPro'ya üye olarak kariyer fırsatlarını keşfedin</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="list-disc pl-4">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="register-form" enctype="multipart/form-data" class="space-y-4">
                    <?php echo csrf_input(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Ad</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="name" name="name" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                            </div>
                        </div>
                        <div>
                            <label for="surname" class="block text-sm font-medium text-gray-700">Soyad</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="surname" name="surname" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="tc_kimlik_no" class="block text-sm font-medium text-gray-700">T.C. Kimlik No</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="tc_kimlik_no" name="tc_kimlik_no" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700">Doğum Tarihi</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date" id="birth_date" name="birth_date" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-venus-mars text-gray-400"></i>
                                </div>
                                <select id="gender" name="gender" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                                    <option value="">Seçiniz</option>
                                    <option value="male">Erkek</option>
                                    <option value="female">Kadın</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- İl ve İlçe Alanları -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">İl</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <select id="city" name="city" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm">
                                    <option value="">İl Seçiniz</option>
                                    <!-- JavaScript ile doldurulacak -->
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700">İlçe</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-map-pin text-gray-400"></i>
                                </div>
                                <select id="county" name="county" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" disabled>
                                    <option value="">İlçe Seçiniz</option>
                                    <!-- JavaScript ile doldurulacak -->
                                </select>
                            </div>
                        </div>
                    </div>
                                     
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="tel" id="phone" name="phone" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">En az 6 karakter olmalıdır.</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Şifre Tekrar</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" required>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" required>
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            <a href="../php/terms.php" class="text-indigo-600 hover:text-indigo-500" target="_blank">Kullanım Şartları</a>'nı ve
                            <a href="../php/privacy.php" class="text-indigo-600 hover:text-indigo-500" target="_blank">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Kayıt Ol
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Zaten hesabınız var mı?
                        <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                            Giriş Yap
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İl-İlçe verileri ve JavaScript -->
<script src="<?php echo BASE_URL; ?>/assets/js/city-county-data.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/city-county.js"></script>

<!-- Input mask için kütüphane -->
<script src="https://unpkg.com/imask@6.4.3/dist/imask.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Telefon input mask
        const phoneInput = document.getElementById('phone');
        const phoneMask = IMask(phoneInput, {
            mask: '0(000) 000 00 00',
            lazy: false,
            placeholderChar: 'x'
        });
        
        // Form doğrulama
        const registerForm = document.getElementById('register-form');
        
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor!');
            }
            
            // TC Kimlik No kontrolü
            const tcKimlikNo = document.getElementById('tc_kimlik_no').value.trim();
            if (tcKimlikNo.length !== 11) {
                e.preventDefault();
                alert('TC Kimlik Numarası 11 haneli olmalıdır!');
            }
            
            // Telefon format kontrolü
            const phoneValue = phoneInput.value.trim();
            const phoneRegex = /^0\(\d{3}\)\s\d{3}\s\d{2}\s\d{2}$/;
            if (!phoneRegex.test(phoneValue)) {
                e.preventDefault();
                alert('Telefon numarası 0(5xx) xxx xx xx formatında olmalıdır!');
            }
            
            // İl ve ilçe seçimi kontrolü
            const city = document.getElementById('city').value;
            const county = document.getElementById('county').value;
            
            if (city && !county) {
                e.preventDefault();
                alert('Lütfen bir ilçe seçiniz!');
            }
        });
    });
</script>
</body>
</html> 