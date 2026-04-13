<?php
require_once dirname(__DIR__) . '/includes/config.php';

if (is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === ROLE_TEACHER) {
    redirect(BASE_URL . '/php/teacher_panel.php');
}

$error = '';
$email = isset($_GET['email']) ? clean_input($_GET['email']) : (isset($_POST['email']) ? clean_input($_POST['email']) : (isset($_SESSION['register_email_for_verify']) ? $_SESSION['register_email_for_verify'] : ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean_input($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    if (empty($email) || empty($code)) {
        $error = 'E-posta ve doğrulama kodu zorunludur.';
    } else {
        try {
            $stmt = $db->prepare("
                SELECT v.user_id, v.email 
                FROM email_verification_codes v 
                INNER JOIN users u ON u.id = v.user_id AND u.email = v.email AND u.role = ?
                WHERE v.email = ? AND v.code = ? AND v.expires_at > NOW() 
                LIMIT 1
            ");
            $stmt->execute([ROLE_TEACHER, $email, $code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $userId = (int) $row['user_id'];
                $db->beginTransaction();
                $db->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$userId]);
                $db->prepare("DELETE FROM email_verification_codes WHERE user_id = ? AND email = ?")->execute([$userId, $email]);
                $db->commit();
                
                $user = $db->prepare("SELECT id, name, surname, email, role FROM users WHERE id = ?");
                $user->execute([$userId]);
                $user = $user->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['teacher_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['surname'] = $user['surname'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    if (isset($_SESSION['register_email_for_verify'])) unset($_SESSION['register_email_for_verify']);
                    $_SESSION['success'] = 'Hesabınız aktifleştirildi. Hoş geldiniz!';
                    redirect(BASE_URL . '/php/teacher_panel.php');
                }
            }
            $error = 'Geçersiz veya süresi dolmuş doğrulama kodu. Lütfen kodu kontrol edin veya yeni kod isteyin.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('verify_email error: ' . $e->getMessage());
            $error = 'Doğrulama sırasında bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

$pageTitle = 'E-posta Doğrulama';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Öğretmen Portalı</title>
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
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-indigo-100 mb-4">
                <i class="fas fa-envelope-open-text text-indigo-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">E-posta Doğrulama</h2>
            <p class="text-gray-500 mt-1">E-postanıza gelen 6 haneli kodu girin</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success']) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                <div class="mt-1">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full px-4 py-3 border border-gray-300 rounded-md"
                           placeholder="ornek@email.com" required>
                </div>
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Doğrulama Kodu</label>
                <div class="mt-1">
                    <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code"
                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full px-4 py-3 border border-gray-300 rounded-md text-center text-xl tracking-widest"
                           placeholder="000000" required>
                </div>
                <p class="mt-1 text-sm text-gray-500">E-postanıza gelen 6 haneli kodu girin (24 saat geçerlidir).</p>
            </div>
            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Doğrula ve Giriş Yap
                </button>
            </div>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            <a href="<?php echo BASE_URL; ?>/php/login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Giriş sayfasına dön</a>
        </p>
    </div>
</div>
</body>
</html>
