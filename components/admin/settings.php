<?php
// Kullanıcı bilgilerini getir
$user = $viewModel->getUserDetails($_SESSION['user_id']);
?>

<div id="admin-settings-page" class="space-y-6">
    <style>
        /* Ayarlar sekmelerindeki form tipografisini diğer sayfalarla hizala */
        #admin-settings-page .tab-content label {
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 700;
        }

        #admin-settings-page .tab-content input:not([type="checkbox"]):not([type="radio"]),
        #admin-settings-page .tab-content textarea,
        #admin-settings-page .tab-content select {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        #admin-settings-page .tab-content h1,
        #admin-settings-page .tab-content h2,
        #admin-settings-page .tab-content h3,
        #admin-settings-page .tab-content h4,
        #admin-settings-page .tab-content h5,
        #admin-settings-page .tab-content h6 {
            font-weight: 700;
        }

        /* Güvenlik / Sistem sekmelerindeki checkbox satırı etiketleri normal ağırlıkta kalır */
        #admin-settings-page .tab-content .flex.items-center label {
            font-weight: 400;
        }
    </style>
    <!-- Trezo benzeri başlık alanı -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="header-title">Ayarlar</h1>
            <p class="text-sm text-slate-500">Profil, sistem ve e-posta ayarlarınızı buradan yönetin.</p>
        </div>
    </div>

    <!-- Üst sekme menüsü -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
        <div class="px-4 sm:px-6 pt-4">
            <nav class="flex flex-wrap border-b border-slate-200/70 gap-2">
                <button onclick="showTab('profile')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="profile">
                    <i class="fas fa-user text-[13px]"></i>
                    <span>Profilim</span>
                </button>
                <button onclick="showTab('general')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="general">
                    <i class="fas fa-cog text-[13px]"></i>
                    <span>Genel Ayarlar</span>
                </button>
                <button onclick="showTab('email')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="email">
                    <i class="fas fa-envelope text-[13px]"></i>
                    <span>E-posta Ayarları</span>
                </button>
                <button onclick="showTab('security')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="security">
                    <i class="fas fa-shield-alt text-[13px]"></i>
                    <span>Güvenlik</span>
                </button>
                <button onclick="showTab('system')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="system">
                    <i class="fas fa-server text-[13px]"></i>
                    <span>Sistem</span>
                </button>
                <button onclick="showTab('homepage')" class="tab-button inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-t-xl border-b-2 border-transparent focus:outline-none" data-tab="homepage">
                    <i class="fas fa-home text-[13px]"></i>
                    <span>Ana Sayfa</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Sekme içerikleri -->
    <div class="space-y-6 mt-4">
        <!-- Profilim -->
            <div id="profile-tab" class="tab-content">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_profile" method="post" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Ad</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Soyad</label>
                                <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">E-posta</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Telefon</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Yeni Şifre (opsiyonel)</label>
                                <input type="password" name="password" placeholder="Yeni şifre" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Profili Güncelle
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

        <!-- Genel Ayarlar -->
            <div id="general-tab" class="tab-content hidden">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Site Başlığı</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? 'Öğretmen İş Başvuru Sistemi'); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Site Açıklaması</label>
                            <textarea name="site_description" rows="3" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Site Logo</label>
                            <input type="file" name="site_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if (!empty($settings['site_logo'])): ?>
                                <img src="<?php echo BASE_URL; ?>/<?php echo $settings['site_logo']; ?>" alt="Site Logo" class="mt-2 h-12">
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Kaydet
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

        <!-- E-posta Ayarları -->
            <div id="email-tab" class="tab-content hidden">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_email_settings" method="post" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Sunucu</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Port</label>
                            <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Kullanıcı Adı</label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Şifre</label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Gönderen E-posta</label>
                            <input type="email" name="sender_email" value="<?php echo htmlspecialchars($settings['sender_email'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Gönderen Adı</label>
                            <input type="text" name="sender_name" value="<?php echo htmlspecialchars($settings['sender_name'] ?? ''); ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="smtp_encryption" id="smtp_encryption" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" <?php echo ($settings['smtp_encryption'] ?? true) ? 'checked' : ''; ?>>
                            <label for="smtp_encryption" class="ml-2 block text-sm text-slate-700">SSL/TLS Kullan</label>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Kaydet
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

        <!-- Güvenlik Ayarları -->
            <div id="security-tab" class="tab-content hidden">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    
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
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Kaydet
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

        <!-- Ana Sayfa Ayarları -->
            <div id="homepage-tab" class="tab-content hidden">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    
                    <form action="<?php echo BASE_URL; ?>/php/admin_panel.php?action=update_homepage_settings" method="post" class="space-y-6">
                        <!-- Hero Section -->
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <h4 class="text-base font-bold text-gray-800 mb-2">Hero Bölümü</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Ana Başlık</label>
                                    <input type="text" name="homepage_hero_title" value="<?php echo htmlspecialchars($settings['homepage_hero_title'] ?? 'Öğretmen İş Başvuru Sistemi'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Alt Başlık / Açıklama</label>
                                    <textarea name="homepage_hero_subtitle" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_hero_subtitle'] ?? 'Türkiye\'nin güvenilir öğretmen iş başvuru platformu. Kariyerinizi profesyonel bir şekilde yönetin ve hayalinizdeki okula ulaşın.'); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- İstatistikler -->
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <h4 class="text-base font-bold text-gray-800 mb-2">İstatistikler Bölümü</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Bölüm Başlığı</label>
                                    <input type="text" name="homepage_stats_title" value="<?php echo htmlspecialchars($settings['homepage_stats_title'] ?? 'Platform İstatistikleri'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Öğretmen Sayısı</label>
                                    <?php 
                                    $totalTeachers = $viewModel->getTotalTeachers();
                                    $successRate = 0;
                                    $totalApplications = $viewModel->getTotalApplications();
                                    if ($totalApplications > 0) {
                                        $approvedApplications = $viewModel->getApprovedApplications();
                                        $successRate = round(($approvedApplications / $totalApplications) * 100, 1);
                                    }
                                    ?>
                                    <input type="text" name="homepage_stats_teachers" value="<?php echo $totalTeachers; ?>" readonly class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Öğretmen Etiketi</label>
                                    <input type="text" name="homepage_stats_teachers_label" value="<?php echo htmlspecialchars($settings['homepage_stats_teachers_label'] ?? 'Aktif Öğretmen Üye'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Okul Sayısı</label>
                                    <?php $totalSchools = $viewModel->getTotalSchools(); ?>
                                    <input type="text" name="homepage_stats_schools" value="<?php echo $totalSchools; ?>" readonly class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Okul Etiketi</label>
                                    <input type="text" name="homepage_stats_schools_label" value="<?php echo htmlspecialchars($settings['homepage_stats_schools_label'] ?? 'Kayıtlı Okul'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Başarı Oranı</label>
                                    <input type="text" name="homepage_stats_success_rate" value="<?php echo $successRate . '%'; ?>" readonly class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Başarı Oranı Etiketi</label>
                                    <input type="text" name="homepage_stats_success_label" value="<?php echo htmlspecialchars($settings['homepage_stats_success_label'] ?? 'Başarılı Yerleştirme Oranı'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Özellikler (Nasıl Çalışır) -->
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <h4 class="text-base font-bold text-gray-800 mb-2">Özellikler Bölümü (Nasıl Çalışır?)</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Bölüm Başlığı</label>
                                    <input type="text" name="homepage_features_title" value="<?php echo htmlspecialchars($settings['homepage_features_title'] ?? 'Sistem Nasıl Çalışır?'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Bölüm Açıklaması</label>
                                    <textarea name="homepage_features_subtitle" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_features_subtitle'] ?? 'Öğretmen Portalı platformu ile kariyer yolculuğunuz dört adımda başlar. Profesyonel ve güvenilir bir süreç ile hayalinizdeki okula ulaşın.'); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 1 - Başlık</label>
                                        <input type="text" name="homepage_feature1_title" value="<?php echo htmlspecialchars($settings['homepage_feature1_title'] ?? 'Kayıt Olun'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 1 - Açıklama</label>
                                        <textarea name="homepage_feature1_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_feature1_description'] ?? 'Profilinizi oluşturun, kişisel bilgilerinizi girin ve CV belgenizi yükleyin.'); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 2 - Başlık</label>
                                        <input type="text" name="homepage_feature2_title" value="<?php echo htmlspecialchars($settings['homepage_feature2_title'] ?? 'Başvuru Yapın'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 2 - Açıklama</label>
                                        <textarea name="homepage_feature2_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_feature2_description'] ?? 'İlgilendiğiniz okullara kolayca başvuru gönderin ve başvuru durumunuzu takip edin.'); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 3 - Başlık</label>
                                        <input type="text" name="homepage_feature3_title" value="<?php echo htmlspecialchars($settings['homepage_feature3_title'] ?? 'Değerlendirme'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 3 - Açıklama</label>
                                        <textarea name="homepage_feature3_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_feature3_description'] ?? 'Okul yöneticileri başvurunuzu değerlendirir ve size bildirim gönderilir.'); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 4 - Başlık</label>
                                        <input type="text" name="homepage_feature4_title" value="<?php echo htmlspecialchars($settings['homepage_feature4_title'] ?? 'Sonuç'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Özellik 4 - Açıklama</label>
                                        <textarea name="homepage_feature4_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_feature4_description'] ?? 'Başvuru sonucunuzu takip edin ve yeni kariyer fırsatınızı yakalayın.'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avantajlar -->
                        <div class="border-b border-gray-200 pb-3 mb-3">
                            <h4 class="text-base font-bold text-gray-800 mb-2">Avantajlar Bölümü</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Bölüm Başlığı</label>
                                    <input type="text" name="homepage_advantages_title" value="<?php echo htmlspecialchars($settings['homepage_advantages_title'] ?? 'Neden Öğretmen Portalı?'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Bölüm Açıklaması</label>
                                    <textarea name="homepage_advantages_subtitle" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_advantages_subtitle'] ?? 'Profesyonel bir platform ile kariyerinizi yönetin ve en iyi fırsatlara ulaşın.'); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 1 - Başlık</label>
                                        <input type="text" name="homepage_advantage1_title" value="<?php echo htmlspecialchars($settings['homepage_advantage1_title'] ?? 'Güvenli Platform'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 1 - Açıklama</label>
                                        <textarea name="homepage_advantage1_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_advantage1_description'] ?? 'Kişisel bilgileriniz güvenli bir şekilde korunur ve sadece yetkili kişiler erişebilir.'); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 2 - Başlık</label>
                                        <input type="text" name="homepage_advantage2_title" value="<?php echo htmlspecialchars($settings['homepage_advantage2_title'] ?? 'Hızlı Süreç'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 2 - Açıklama</label>
                                        <textarea name="homepage_advantage2_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_advantage2_description'] ?? 'Başvurularınız hızlı bir şekilde değerlendirilir ve sonuçlar size bildirilir.'); ?></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 3 - Başlık</label>
                                        <input type="text" name="homepage_advantage3_title" value="<?php echo htmlspecialchars($settings['homepage_advantage3_title'] ?? '7/24 Destek'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Avantaj 3 - Açıklama</label>
                                        <textarea name="homepage_advantage3_description" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_advantage3_description'] ?? 'Her zaman yanınızdayız. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Call to Action -->
                        <div class="pb-3">
                            <h4 class="text-base font-bold text-gray-800 mb-2">Call to Action Bölümü</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Başlık</label>
                                    <input type="text" name="homepage_cta_title" value="<?php echo htmlspecialchars($settings['homepage_cta_title'] ?? 'Kariyerinize Bugün Başlayın'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Açıklama</label>
                                    <textarea name="homepage_cta_subtitle" rows="2" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($settings['homepage_cta_subtitle'] ?? 'Türkiye\'nin güvenilir öğretmen iş başvuru platformuna katılın ve hayalinizdeki okula ulaşın. Profesyonel bir şekilde kariyerinizi yönetin.'); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Kayıt Ol Butonu Metni</label>
                                        <input type="text" name="homepage_cta_register_text" value="<?php echo htmlspecialchars($settings['homepage_cta_register_text'] ?? 'Hemen Ücretsiz Kaydol'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Giriş Yap Butonu Metni</label>
                                        <input type="text" name="homepage_cta_login_text" value="<?php echo htmlspecialchars($settings['homepage_cta_login_text'] ?? 'Giriş Yap'); ?>" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Kaydet
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>

        <!-- Sistem Ayarları -->
            <div id="system-tab" class="tab-content hidden">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm">
                    <div class="p-6">
                    
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
                            <button type="submit" class="inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent shadow-sm text-sm font-semibold rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300">
                                <i class="fas fa-check"></i> Kaydet
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
            button.classList.remove('is-active', 'bg-primary-50', 'text-primary-700', 'ring-1', 'ring-primary-100');
            button.classList.add('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
        });
        
        // Seçilen tab'ı göster
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        // Seçilen tab butonuna active sınıfı ekle
        const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeBtn) {
            activeBtn.classList.add('is-active', 'bg-primary-50', 'text-primary-700', 'ring-1', 'ring-primary-100');
            activeBtn.classList.remove('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
        }
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
        showTab('profile');
    });
</script> 