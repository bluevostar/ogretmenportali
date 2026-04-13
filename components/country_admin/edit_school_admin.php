<?php
// Hata ayıklama
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Edit school admin page loaded -->";
error_log("Edit school admin page loaded. GET params: " . print_r($_GET, true));

// Okul yöneticisi ID'sini kontrol et
if (!isset($_GET['id'])) {
    echo "<!-- No ID parameter found -->";
    error_log("Edit school admin - No ID parameter found");
    redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins');
}

$adminId = (int) $_GET['id'];
echo "<!-- Admin ID: " . $adminId . " -->";
error_log("Edit school admin - Admin ID: " . $adminId);

// Okul yöneticisi bilgilerini getir
$admin = $viewModel->getSchoolAdminDetails($adminId);

if (!$admin) {
    $_SESSION['error'] = 'Okul yöneticisi bulunamadı.';
    redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins');
}

// Mevcut okulları getir
$schools = $viewModel->getAllSchools();

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $userData = [
        'name' => clean_input($_POST['name']),
        'email' => clean_input($_POST['email']),
        'phone' => clean_input($_POST['phone']),
        'address' => clean_input($_POST['address']),
        'school_id' => (int) $_POST['school_id'],
        'role' => ROLE_SCHOOL_ADMIN,
        'position' => clean_input($_POST['position'] ?? 'Okul Müdürü'),
    ];
    
    // Şifre değiştirilecek mi kontrol et
    if (!empty($_POST['password'])) {
        $userData['password'] = $_POST['password'];
    }
    
    // Okul yöneticisini güncelle
    $result = $viewModel->updateSchoolAdmin($adminId, $userData, $_SESSION['user_id']);
    
    if ($result) {
        $_SESSION['success'] = 'Okul yöneticisi bilgileri başarıyla güncellendi.';
        redirect(BASE_URL . '/php/country_admin_panel.php?action=school_admins');
    } else {
        $_SESSION['error'] = 'Okul yöneticisi güncellenirken bir hata oluştu.';
    }
}
?>

<div class="content-area">
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title mb-4 md:mb-0">Okul Yöneticisi Düzenle</h1>
        <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=school_admins" class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>
            Geri Dön
        </a>
    </div>
    
    <!-- Hata ve Başarı Mesajları -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Form Kartı -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <form method="POST" action="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=update_school_admin" class="p-6">
            <input type="hidden" name="admin_id" value="<?php echo $adminId; ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kişisel Bilgiler Bölümü -->
                <div class="md:col-span-2">
                    <h4 class="text-gray-600 font-medium mb-3 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-indigo-500"></i>
                        Kişisel Bilgiler
                    </h4>
                    <div class="w-full h-0.5 bg-gray-100 mb-4"></div>
                </div>
                
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="name" id="name" value="<?php echo isset($admin['name']) ? htmlspecialchars($admin['name']) : ''; ?>" required
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" value="<?php echo isset($admin['email']) ? htmlspecialchars($admin['email']) : ''; ?>" required
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-700">Şifre (Değiştirmek için doldurun)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Değiştirmek için yeni şifre girin"
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="position" class="block text-sm font-medium text-gray-700">Pozisyon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-briefcase text-gray-400"></i>
                        </div>
                        <input type="text" name="position" id="position" value="<?php echo isset($admin['position']) ? htmlspecialchars($admin['position']) : 'Okul Müdürü'; ?>" 
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <!-- İletişim Bilgileri Bölümü -->
                <div class="md:col-span-2 mt-4">
                    <h4 class="text-gray-600 font-medium mb-3 flex items-center">
                        <i class="fas fa-address-card mr-2 text-indigo-500"></i>
                        İletişim Bilgileri
                    </h4>
                    <div class="w-full h-0.5 bg-gray-100 mb-4"></div>
                </div>
                
                <div class="space-y-2">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone-alt text-gray-400"></i>
                        </div>
                        <input type="text" name="phone" id="phone" value="<?php echo isset($admin['phone']) ? htmlspecialchars($admin['phone']) : ''; ?>"
                               class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="school_id" class="block text-sm font-medium text-gray-700">Okul</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-school text-gray-400"></i>
                        </div>
                        <select name="school_id" id="school_id" required 
                                class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                            <option value="">Okul Seçin</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?php echo $school['id']; ?>" <?php echo (isset($admin['school_id']) && $admin['school_id'] == $school['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($school['name'] . ' (' . $school['school_code'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-2 space-y-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                            <i class="fas fa-home text-gray-400"></i>
                        </div>
                        <textarea name="address" id="address" rows="3" 
                                 class="pl-10 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all"><?php echo isset($admin['address']) ? htmlspecialchars($admin['address']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Form Butonları -->
            <div class="flex justify-end mt-8 space-x-3">
                <a href="<?php echo BASE_URL; ?>/php/country_admin_panel.php?action=school_admins" 
                   class="px-5 py-2.5 bg-white text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    <i class="fas fa-times mr-2"></i>İptal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Edit school admin page loaded");
    
    // Telefon input mask
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    }
    
    // Şifre validasyonu
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            if (value && value.length < 6) {
                this.setCustomValidity('Şifre en az 6 karakter olmalıdır.');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script> 