<?php
require_once dirname(__DIR__) . '/includes/config.php';

// Eğer kullanıcı zaten giriş yapmışsa ve rolü admin veya country_admin ise ilgili panele yönlendir
if (is_logged_in() && in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_COUNTRY_ADMIN])) {
    redirect(BASE_URL . '/php/' . ($_SESSION['role'] === ROLE_ADMIN ? 'admin_panel.php' : 'country_admin_panel.php'));
}

// Eğer kullanıcı zaten giriş yapmışsa admin paneline yönlendir
if (is_logged_in() && $_SESSION['role'] === ROLE_ADMIN) {
    redirect(BASE_URL . '/php/admin_panel.php');
}

// Giriş formu kontrolü
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validate_csrf_request()) {
        $_SESSION['error'] = "Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.";
    } else {
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    
    // Boş alan kontrolü
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Lütfen tüm alanları doldurunuz.";
    } else {
        try {
            // users tablosunda admin veya country_admin rolündeki kullanıcıyı ara
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND (role = 'admin' OR role = 'country_admin')");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                if (verify_password_compat($password, $user['password'])) {
                    // MD5 veya eski maliyetli hash'ten yeni hash'e otomatik geçiş
                    upgrade_password_hash_if_needed($db, $user['id'], $password, $user['password']);

                    // Kullanıcının durumunu kontrol et
                    if ($user['status'] !== 'active') {
                        $_SESSION['error'] = "Hesabınız aktif değil. Lütfen sistem yöneticisi ile iletişime geçin.";
                        redirect(BASE_URL . '/php/admin_login.php');
                    }

                    // Session bilgilerini ayarla
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['surname'] = $user['surname'] ?? '';
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role']; // Rolü veritabanından dinamik olarak al

                    if ($user['role'] === 'admin') {
                        redirect(BASE_URL . '/php/admin_panel.php');
                    } else { // country_admin
                        redirect(BASE_URL . '/php/country_admin_panel.php');
                    }
                } else {
                    $_SESSION['error'] = "Geçersiz şifre.";
                }
            } else {
                $_SESSION['error'] = "Bu e-posta adresi ile kayıtlı bir yönetici bulunamadı.";
            }
        } catch(PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $_SESSION['error'] = "Giriş yapılırken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Öğretmen İş Başvuru Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --btn-uniform-height: 42px;
            --btn-uniform-gap: 0.75rem;
        }
        button,
        input[type="button"],
        input[type="submit"],
        input[type="reset"] {
            min-height: var(--btn-uniform-height);
            height: var(--btn-uniform-height);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: middle;
        }
        :where(.flex, .inline-flex):has(> button, > input[type="button"], > input[type="submit"], > input[type="reset"]) {
            align-items: center;
            gap: var(--btn-uniform-gap);
        }
        :where(.flex, .inline-flex):has(> button, > input[type="button"], > input[type="submit"], > input[type="reset"])
            > :where(button, input[type="button"], input[type="submit"], input[type="reset"]) + :where(button, input[type="button"], input[type="submit"], input[type="reset"]) {
            margin-left: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl w-full flex rounded-2xl shadow-2xl overflow-hidden">
        <!-- Sol taraf - Resim bölümü -->
        <div class="w-0 md:w-1/2 hidden md:block relative overflow-hidden">
            <img src="<?php echo BASE_URL; ?>/assets/images/login-image.jpg" alt="Admin Giriş Resmi" class="object-cover h-full w-full">
        </div>
        <!-- Sağ taraf - Giriş formu -->
        <div class="w-full md:w-1/2 bg-white p-8 md:p-12">
            <div class="max-w-md mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-800">Admin Girişi</h2>
                    <p class="text-gray-500 mt-2">Yönetici paneli için giriş yapın</p>
                </div>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                    <?php echo csrf_input(); ?>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" placeholder="ornekmail@domain.com" required>
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md text-sm" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Beni Hatırla</label>
                        </div>
                        <div class="text-sm">
                            <a href="forgot_password.php" class="font-medium text-indigo-600 hover:text-indigo-500">Şifremi Unuttum</a>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                            Giriş Yap
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
