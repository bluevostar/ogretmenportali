<?php
// Kullanıcı bilgilerini getir
$user = $viewModel->getUserDetails($_SESSION['user_id']);
?>

<div id="country-admin-settings-page" class="mb-8">
    <style>
        /* Ayarlar sekmelerindeki form tipografisini diğer sayfalarla hizala */
        #country-admin-settings-page .tab-content label {
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 700;
        }

        #country-admin-settings-page .tab-content input:not([type="checkbox"]):not([type="radio"]),
        #country-admin-settings-page .tab-content textarea,
        #country-admin-settings-page .tab-content select {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        #country-admin-settings-page .tab-content h1,
        #country-admin-settings-page .tab-content h2,
        #country-admin-settings-page .tab-content h3,
        #country-admin-settings-page .tab-content h4,
        #country-admin-settings-page .tab-content h5,
        #country-admin-settings-page .tab-content h6 {
            font-weight: 700;
        }

        /* Güvenlik sekmesindeki checkbox satırı etiketleri normal ağırlıkta kalır */
        #country-admin-settings-page .tab-content .flex.items-center label {
            font-weight: 400;
        }
    </style>
    <div class="flex justify-between items-center mb-6">
        <h1 class="header-title">Sistem Ayarları</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sol Menü -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="space-y-1">
                    <button onclick="showTab('general')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button active" data-tab="general">
                        <i class="fas fa-cog mr-2"></i> Genel Ayarlar
                    </button>
                    <button onclick="showTab('email')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button" data-tab="email">
                        <i class="fas fa-envelope mr-2"></i> E-posta Ayarları
                    </button>
                    <button onclick="showTab('security')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button" data-tab="security">
                        <i class="fas fa-shield-alt mr-2"></i> Güvenlik Ayarları
                    </button>
                    <button onclick="showTab('system')" class="w-full text-left px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 tab-button" data-tab="system">
                        <i class="fas fa-server mr-2"></i> Sistem Ayarları
                    </button>
                </div>
            </div>
        </div>

        <!-- Sağ İçerik -->
        <div class="lg:col-span-2">
            <!-- Genel Ayarlar -->
            <div id="general-tab" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Genel Site Ayarları</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Başlığı</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Öğretmen İş Başvuru Sistemi'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Açıklaması</label>
                            <textarea name="site_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Logo</label>
                            <input type="file" name="site_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if (!empty($settings['site_logo'])): ?>
                                <img src="<?php echo BASE_URL; ?>/<?php echo $settings['site_logo']; ?>" alt="Site Logo" class="mt-2 h-12">
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İletişim Bilgileri</label>
                            <div class="space-y-2">
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" placeholder="E-posta" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="tel" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" placeholder="Telefon" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <textarea name="contact_address" rows="2" placeholder="Adres" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sosyal Medya</label>
                            <div class="space-y-2">
                                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" placeholder="Facebook URL" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>" placeholder="Twitter URL" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" placeholder="Instagram URL" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="url" name="linkedin_url" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>" placeholder="LinkedIn URL" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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

            <!-- E-posta Ayarları -->
            <div id="email-tab" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">E-posta Ayarları</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_email_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Sunucu</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                            <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Kullanıcı Adı</label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Şifre</label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gönderen E-posta</label>
                            <input type="email" name="sender_email" value="<?php echo htmlspecialchars($settings['sender_email'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gönderen Adı</label>
                            <input type="text" name="sender_name" value="<?php echo htmlspecialchars($settings['sender_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="smtp_encryption" id="smtp_encryption" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['smtp_encryption'] ?? true) ? 'checked' : ''; ?>>
                            <label for="smtp_encryption" class="ml-2 block text-sm text-gray-700">SSL/TLS Kullan</label>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i> Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Güvenlik Ayarları -->
            <div id="security-tab" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Güvenlik Ayarları</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_security_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Şifre Politikası</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="password_min_length" id="password_min_length" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['password_min_length'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="password_min_length" class="ml-2 block text-sm text-gray-700">Minimum Şifre Uzunluğu (8 karakter)</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="password_uppercase" id="password_uppercase" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['password_uppercase'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="password_uppercase" class="ml-2 block text-sm text-gray-700">Büyük Harf Zorunluluğu</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="password_special" id="password_special" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['password_special'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="password_special" class="ml-2 block text-sm text-gray-700">Özel Karakter Zorunluluğu</label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Oturum Güvenliği</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="session_timeout" id="session_timeout" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['session_timeout'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="session_timeout" class="ml-2 block text-sm text-gray-700">Oturum Zaman Aşımı (30 dakika)</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="login_attempts" id="login_attempts" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['login_attempts'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="login_attempts" class="ml-2 block text-sm text-gray-700">Başarısız Giriş Denemesi Limiti (5)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İki Faktörlü Doğrulama</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="two_factor_auth" id="two_factor_auth" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['two_factor_auth'] ?? false) ? 'checked' : ''; ?>>
                                    <label for="two_factor_auth" class="ml-2 block text-sm text-gray-700">İki Faktörlü Doğrulama Aktif</label>
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

            <!-- Sistem Ayarları -->
            <div id="system-tab" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Sistem Ayarları</h3>
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_system_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bakım Modu</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="maintenance_mode" id="maintenance_mode" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['maintenance_mode'] ?? false) ? 'checked' : ''; ?>>
                                <label for="maintenance_mode" class="ml-2 block text-sm text-gray-700">Bakım Modunu Aktifleştir</label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hata Raporlama</label>
                            <div class="flex items-center">
                                <input type="checkbox" name="error_reporting" id="error_reporting" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['error_reporting'] ?? true) ? 'checked' : ''; ?>>
                                <label for="error_reporting" class="ml-2 block text-sm text-gray-700">Hata Raporlamayı Aktifleştir</label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dosya Yükleme Limitleri</label>
                            <div class="space-y-2">
                                <input type="number" name="max_upload_size" value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '5'); ?>" placeholder="Maksimum Dosya Boyutu (MB)" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <input type="text" name="allowed_file_types" value="<?php echo htmlspecialchars($settings['allowed_file_types'] ?? 'jpg,jpeg,png,pdf,doc,docx'); ?>" placeholder="İzin Verilen Dosya Türleri" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Yedekleme Ayarları</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="auto_backup" id="auto_backup" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['auto_backup'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="auto_backup" class="ml-2 block text-sm text-gray-700">Otomatik Yedekleme</label>
                                </div>
                                <select name="backup_frequency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="daily" <?php echo ($settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : ''; ?>>Günlük</option>
                                    <option value="weekly" <?php echo ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Haftalık</option>
                                    <option value="monthly" <?php echo ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Aylık</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="clearCache()" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-trash mr-2"></i> Önbelleği Temizle
                            </button>
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
    
    function clearCache() {
        if (confirm('Önbelleği temizlemek istediğinizden emin misiniz?')) {
            csrfFetch('<?php echo BASE_URL; ?>/php/admin_panel.php?action=clear_cache', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Önbellek başarıyla temizlendi.');
                } else {
                    alert('Önbellek temizlenirken bir hata oluştu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu.');
            });
        }
    }
    
    // Sayfa yüklendiğinde ilk tab'ı göster
    document.addEventListener('DOMContentLoaded', function() {
        showTab('general');
    });
</script> 