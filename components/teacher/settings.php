<?php
// Kullanıcı bilgilerini getir
$user = $teacherViewModel->getUserDetails($_SESSION['user_id']);
$settings = (is_array($user) && isset($user['settings'])) ? $user['settings'] : [];
?>

<div id="teacher-settings-page" class="mb-8">
    <style>
        /* Ayarlar sekmelerindeki form tipografisini diğer sayfalarla hizala */
        #teacher-settings-page .tab-content label {
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 700;
        }

        #teacher-settings-page .tab-content input:not([type="checkbox"]):not([type="radio"]),
        #teacher-settings-page .tab-content textarea,
        #teacher-settings-page .tab-content select {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        #teacher-settings-page .tab-content h1,
        #teacher-settings-page .tab-content h2,
        #teacher-settings-page .tab-content h3,
        #teacher-settings-page .tab-content h4,
        #teacher-settings-page .tab-content h5,
        #teacher-settings-page .tab-content h6 {
            font-weight: 700;
        }
    </style>
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title">Hesap Ayarları</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sol Menü -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="space-y-1">
                    
                    <button onclick="showTab('security')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button" data-tab="security">
                        <i class="fas fa-shield-alt mr-2"></i> Şifre Değiştirme
                    </button>
                    <button onclick="showTab('notification')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button" data-tab="notification">
                        <i class="fas fa-bell mr-2"></i> Bildirim Ayarları
                    </button>
                </div>
            </div>
        </div>

        <!-- Sağ İçerik -->
        <div class="lg:col-span-2">

            <!-- Şifre Değiştirme -->
            <div id="security-tab" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-lg p-6 tab-pane" id="password-settings">
                    <h3 class="text-lg font-semibold mb-4">Şifre Değiştirme</h3>
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'error' && isset($_GET['message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['message']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <form id="password-form" action="<?php echo BASE_URL; ?>/php/update_password.php" method="POST" class="space-y-4">
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mevcut Şifre</label>
                                <input type="password" id="current_password" name="current_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Yeni Şifre</label>
                                <input type="password" id="new_password" name="new_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="text-xs text-gray-500 mt-1">En az 8 karakter, büyük harf, küçük harf ve rakam içermelidir.</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Yeni Şifre (Tekrar)</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Şifreyi Değiştir</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bildirim Ayarları -->
            <div id="notification-tab" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Bildirim Ayarları</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/php/update_notification_settings.php" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bildirim Tercihleri</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="email_notifications" id="email_notifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['email_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="email_notifications" class="ml-2 block text-sm text-gray-700">E-posta Bildirimleri</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="application_updates" id="application_updates" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['application_updates'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="application_updates" class="ml-2 block text-sm text-gray-700">Başvuru Güncellemeleri</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="new_job_notifications" id="new_job_notifications" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['new_job_notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="new_job_notifications" class="ml-2 block text-sm text-gray-700">Yeni İş İlanları</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i> Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(tabName) {
        // Tüm tab içeriklerini gizle
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Tüm tab butonlarının active sınıfını kaldır
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Seçilen tab'ı göster
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        // Seçilen tab butonuna active sınıfı ekle
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    }
    
    // Şifre formunun doğrulanması
    document.getElementById('password-form').addEventListener('submit', function(e) {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Tüm alanların doldurulduğunu kontrol et
        if (!currentPassword || !newPassword || !confirmPassword) {
            e.preventDefault();
            alert('Lütfen tüm alanları doldurun.');
            return;
        }
        
        // Şifre uzunluğunu kontrol et
        if (newPassword.length < 8) {
            e.preventDefault();
            alert('Yeni şifreniz en az 8 karakter uzunluğunda olmalıdır.');
            return;
        }
        
        // Şifre karmaşıklığını kontrol et
        const hasUpperCase = /[A-Z]/.test(newPassword);
        const hasLowerCase = /[a-z]/.test(newPassword);
        const hasNumbers = /\d/.test(newPassword);
        
        if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
            e.preventDefault();
            alert('Şifreniz en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.');
            return;
        }
        
        // Şifrelerin eşleştiğini kontrol et
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Yeni şifreler eşleşmiyor.');
            return;
        }
    });
    
    // Sayfa yüklendiğinde ilk tab'ı göster
    document.addEventListener('DOMContentLoaded', function() {
        showTab('security');
    });
</script> 