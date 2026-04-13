<?php
require_once '../includes/config.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/php/login.php');
}

// Kullanıcı bilgilerini al
$query = "SELECT u.*, tp.branch, tp.education, tp.experience 
          FROM users u
          LEFT JOIN teacher_profiles tp ON u.id = tp.user_id 
          WHERE u.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'name' => clean_input($_POST['name']),
        'email' => clean_input($_POST['email']),
        'phone' => clean_input($_POST['phone']),
        'address' => clean_input($_POST['address']),
        'branch' => clean_input($_POST['branch']),
        'education' => clean_input($_POST['education']),
        'experience' => clean_input($_POST['experience'])
    ];

    try {
        $db->beginTransaction();

        // Users tablosunu güncelle
        $query = "UPDATE users SET 
                  name = ?, 
                  email = ?, 
                  phone = ?,
                  address = ?,
                  updated_at = NOW() 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $userData['name'],
            $userData['email'],
            $userData['phone'],
            $userData['address'],
            $_SESSION['user_id']
        ]);

        // Teacher_profiles tablosunu güncelle
        $query = "INSERT INTO teacher_profiles (user_id, branch, education, experience) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                 branch = VALUES(branch),
                 education = VALUES(education),
                 experience = VALUES(experience)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'],
            $userData['branch'],
            $userData['education'],
            $userData['experience']
        ]);

        $db->commit();
        $_SESSION['success'] = 'Profil bilgileriniz başarıyla güncellendi.';
        redirect(BASE_URL . '/php/teacher_profile.php');
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = 'Profil güncellenirken bir hata oluştu.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Düzenle - <?php echo htmlspecialchars($user['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Profil Bilgilerini Düzenle</h2>
                    <p class="text-gray-600 mt-1">Kişisel ve profesyonel bilgilerinizi güncelleyin</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <!-- Kişisel Bilgiler -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kişisel Bilgiler</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Profesyonel Bilgiler -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Profesyonel Bilgiler</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="branch" class="block text-sm font-medium text-gray-700">Branş</label>
                                <input type="text" id="branch" name="branch" value="<?php echo htmlspecialchars($user['branch'] ?? ''); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="education" class="block text-sm font-medium text-gray-700">Eğitim</label>
                                <input type="text" id="education" name="education" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="experience" class="block text-sm font-medium text-gray-700">Deneyim (Yıl)</label>
                                <input type="number" id="experience" name="experience" value="<?php echo htmlspecialchars($user['experience'] ?? '0'); ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_profile.php" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            İptal
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Değişiklikleri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script>
        // Form doğrulama
        document.querySelector('form').addEventListener('submit', function(e) {
            const required = ['name', 'email'];
            let isValid = true;

            required.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Lütfen gerekli alanları doldurun.');
            }
        });
    </script>
</body>
</html>