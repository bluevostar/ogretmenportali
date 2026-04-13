<?php
    include_once 'includes/config.php';

    // Ana sayfa öğretmen odaklıdır:
    // Giriş yapan kullanıcı öğretmen değilse kendi paneline yönlendir.
    if (is_logged_in() && ($_SESSION['role'] ?? null) !== ROLE_TEACHER) {
        switch ($_SESSION['role'] ?? '') {
            case ROLE_ADMIN:
                redirect(BASE_URL . '/php/admin_panel.php');
                break;
            case ROLE_COUNTRY_ADMIN:
                redirect(BASE_URL . '/php/country_admin_panel.php');
                break;
            case ROLE_SCHOOL_ADMIN:
                redirect(BASE_URL . '/php/school-admin-panel.php');
                break;
            default:
                redirect(BASE_URL . '/php/login.php');
        }
    }

    include_once 'includes/header.php';
    
    // Ana sayfa ayarlarını veritabanından çek
    $homepageSettings = [];
    try {
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE user_id IS NULL AND setting_group = 'homepage'");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as $row) {
            $homepageSettings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log('Homepage settings fetch error: ' . $e->getMessage());
    }
    
    // Varsayılan değerler
    $heroTitle = $homepageSettings['homepage_hero_title'] ?? 'Öğretmen İş Başvuru Sistemi';
    $heroSubtitle = $homepageSettings['homepage_hero_subtitle'] ?? 'Türkiye\'nin güvenilir öğretmen iş başvuru platformu. Kariyerinizi profesyonel bir şekilde yönetin ve hayalinizdeki okula ulaşın.';
    $statsTeachers = $homepageSettings['homepage_stats_teachers'] ?? '5000+';
    $statsSchools = $homepageSettings['homepage_stats_schools'] ?? '1200+';
    $statsSuccessRate = $homepageSettings['homepage_stats_success_rate'] ?? '92%';
    $featuresTitle = $homepageSettings['homepage_features_title'] ?? 'Sistem Nasıl Çalışır?';
    $featuresSubtitle = $homepageSettings['homepage_features_subtitle'] ?? 'Öğretmen Portalı platformu ile kariyer yolculuğunuz dört adımda başlar. Profesyonel ve güvenilir bir süreç ile hayalinizdeki okula ulaşın.';
    $feature1Title = $homepageSettings['homepage_feature1_title'] ?? 'Kayıt Olun';
    $feature1Desc = $homepageSettings['homepage_feature1_description'] ?? 'Profilinizi oluşturun, kişisel bilgilerinizi girin ve CV belgenizi yükleyin.';
    $feature2Title = $homepageSettings['homepage_feature2_title'] ?? 'Başvuru Yapın';
    $feature2Desc = $homepageSettings['homepage_feature2_description'] ?? 'İlgilendiğiniz okullara kolayca başvuru gönderin ve başvuru durumunuzu takip edin.';
    $feature3Title = $homepageSettings['homepage_feature3_title'] ?? 'Değerlendirme';
    $feature3Desc = $homepageSettings['homepage_feature3_description'] ?? 'Okul yöneticileri başvurunuzu değerlendirir ve size bildirim gönderilir.';
    $feature4Title = $homepageSettings['homepage_feature4_title'] ?? 'Sonuç';
    $feature4Desc = $homepageSettings['homepage_feature4_description'] ?? 'Başvuru sonucunuzu takip edin ve yeni kariyer fırsatınızı yakalayın.';
    $statsTitle = $homepageSettings['homepage_stats_title'] ?? 'Platform İstatistikleri';
    $statsTeachersLabel = $homepageSettings['homepage_stats_teachers_label'] ?? 'Aktif Öğretmen Üye';
    $statsSchoolsLabel = $homepageSettings['homepage_stats_schools_label'] ?? 'Kayıtlı Okul';
    $statsSuccessLabel = $homepageSettings['homepage_stats_success_label'] ?? 'Başarılı Yerleştirme Oranı';
    $advantagesTitle = $homepageSettings['homepage_advantages_title'] ?? 'Neden Öğretmen Portalı?';
    $advantagesSubtitle = $homepageSettings['homepage_advantages_subtitle'] ?? 'Profesyonel bir platform ile kariyerinizi yönetin ve en iyi fırsatlara ulaşın.';
    $advantage1Title = $homepageSettings['homepage_advantage1_title'] ?? 'Güvenli Platform';
    $advantage1Desc = $homepageSettings['homepage_advantage1_description'] ?? 'Kişisel bilgileriniz güvenli bir şekilde korunur ve sadece yetkili kişiler erişebilir.';
    $advantage2Title = $homepageSettings['homepage_advantage2_title'] ?? 'Hızlı Süreç';
    $advantage2Desc = $homepageSettings['homepage_advantage2_description'] ?? 'Başvurularınız hızlı bir şekilde değerlendirilir ve sonuçlar size bildirilir.';
    $advantage3Title = $homepageSettings['homepage_advantage3_title'] ?? '7/24 Destek';
    $advantage3Desc = $homepageSettings['homepage_advantage3_description'] ?? 'Her zaman yanınızdayız. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.';
    $ctaTitle = $homepageSettings['homepage_cta_title'] ?? 'Kariyerinize Bugün Başlayın';
    $ctaSubtitle = $homepageSettings['homepage_cta_subtitle'] ?? 'Türkiye\'nin güvenilir öğretmen iş başvuru platformuna katılın ve hayalinizdeki okula ulaşın. Profesyonel bir şekilde kariyerinizi yönetin.';
    $ctaRegisterText = $homepageSettings['homepage_cta_register_text'] ?? 'Hemen Ücretsiz Kaydol';
    $ctaLoginText = $homepageSettings['homepage_cta_login_text'] ?? 'Giriş Yap';
?>

<style>
    :root {
        --turkuaz-50: #E0F7FA;
        --turkuaz-100: #B2EBF2;
        --turkuaz-200: #80DEEA;
        --turkuaz-300: #4DD0E1;
        --turkuaz-400: #26C6DA;
        --turkuaz-500: #00BCD4;
        --turkuaz-600: #00ACC1;
        --turkuaz-700: #0097A7;
        --turkuaz-800: #00838F;
        --turkuaz-900: #006064;
    }
</style>

<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-teal-600 via-teal-500 to-cyan-600 overflow-hidden">
    <div class="container mx-auto px-6 py-24 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <div class="mb-8">
                <div class="inline-block bg-white/20 backdrop-blur-sm rounded-full p-4 mb-6">
                    <i class="fas fa-graduation-cap text-white text-5xl"></i>
                </div>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                <?php echo htmlspecialchars($heroTitle); ?>
            </h1>
            <p class="text-xl md:text-2xl text-white/90 mb-10 max-w-2xl mx-auto leading-relaxed">
                <?php echo htmlspecialchars($heroSubtitle); ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="php/register.php" class="px-10 py-4 bg-white text-teal-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-300 text-center shadow-lg text-lg">
                    <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                </a>
                <a href="#nasil-calisir" class="px-10 py-4 bg-white/10 backdrop-blur-sm border-2 border-white text-white rounded-lg font-semibold hover:bg-white/20 transition-all duration-300 text-center text-lg">
                    <i class="fas fa-info-circle mr-2"></i>Nasıl Çalışır?
                </a>
            </div>
            
            <div class="mt-12 flex items-center justify-center gap-8 text-white/80">
                <div class="text-center">
                    <div class="text-3xl font-bold text-white"><?php echo htmlspecialchars($statsTeachers); ?></div>
                    <div class="text-sm">Aktif Öğretmen</div>
                </div>
                <div class="h-12 w-px bg-white/30"></div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white"><?php echo htmlspecialchars($statsSchools); ?></div>
                    <div class="text-sm">Kayıtlı Okul</div>
                </div>
                <div class="h-12 w-px bg-white/30"></div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-white"><?php echo htmlspecialchars($statsSuccessRate); ?></div>
                    <div class="text-sm">Başarı Oranı</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dalga Şekli -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-auto">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,58.7C960,64,1056,64,1152,58.7C1248,53,1344,43,1392,37.3L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div>
</div>

<!-- Özellikler -->
<section class="py-20 bg-white" id="nasil-calisir">
    <div class="container mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($featuresTitle); ?></h2>
            <div class="w-24 h-1 bg-teal-500 mx-auto mb-6"></div>
            <p class="text-lg text-gray-600 leading-relaxed">
                <?php echo htmlspecialchars($featuresSubtitle); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-lg p-8 shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-gray-100 group">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 text-2xl font-bold mb-6 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300">
                    1
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($feature1Title); ?></h3>
                <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($feature1Desc); ?></p>
            </div>
            
            <div class="bg-white rounded-lg p-8 shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-gray-100 group">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 text-2xl font-bold mb-6 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300">
                    2
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($feature2Title); ?></h3>
                <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($feature2Desc); ?></p>
            </div>
            
            <div class="bg-white rounded-lg p-8 shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-gray-100 group">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 text-2xl font-bold mb-6 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300">
                    3
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($feature3Title); ?></h3>
                <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($feature3Desc); ?></p>
            </div>
            
            <div class="bg-white rounded-lg p-8 shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border border-gray-100 group">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 text-2xl font-bold mb-6 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300">
                    4
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($feature4Title); ?></h3>
                <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($feature4Desc); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- İstatistikler Bölümü -->
<section class="py-20 bg-gradient-to-br from-teal-50 to-cyan-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($statsTitle); ?></h2>
            <div class="w-24 h-1 bg-teal-500 mx-auto"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="bg-white rounded-lg p-10 text-center shadow-md border border-gray-100">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-teal-600 text-3xl"></i>
                </div>
                <h3 class="text-4xl font-bold text-teal-600 mb-2"><?php echo htmlspecialchars($statsTeachers); ?></h3>
                <p class="text-gray-600 font-medium"><?php echo htmlspecialchars($statsTeachersLabel); ?></p>
            </div>
            
            <div class="bg-white rounded-lg p-10 text-center shadow-md border border-gray-100">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-school text-teal-600 text-3xl"></i>
                </div>
                <h3 class="text-4xl font-bold text-teal-600 mb-2"><?php echo htmlspecialchars($statsSchools); ?></h3>
                <p class="text-gray-600 font-medium"><?php echo htmlspecialchars($statsSchoolsLabel); ?></p>
            </div>
            
            <div class="bg-white rounded-lg p-10 text-center shadow-md border border-gray-100">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chart-line text-teal-600 text-3xl"></i>
                </div>
                <h3 class="text-4xl font-bold text-teal-600 mb-2"><?php echo htmlspecialchars($statsSuccessRate); ?></h3>
                <p class="text-gray-600 font-medium"><?php echo htmlspecialchars($statsSuccessLabel); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Avantajlar Bölümü -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($advantagesTitle); ?></h2>
            <div class="w-24 h-1 bg-teal-500 mx-auto mb-6"></div>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                <?php echo htmlspecialchars($advantagesSubtitle); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-teal-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($advantage1Title); ?></h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($advantage1Desc); ?></p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-teal-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($advantage2Title); ?></h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($advantage2Desc); ?></p>
            </div>
            
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-teal-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-teal-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($advantage3Title); ?></h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($advantage3Desc); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="relative py-20 bg-gradient-to-r from-teal-600 to-cyan-600 overflow-hidden">
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6"><?php echo htmlspecialchars($ctaTitle); ?></h2>
            <p class="text-xl text-white/90 mb-10 leading-relaxed">
                <?php echo htmlspecialchars($ctaSubtitle); ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="php/register.php" class="px-10 py-4 bg-white text-teal-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-300 inline-block shadow-lg text-lg">
                    <i class="fas fa-user-plus mr-2"></i><?php echo htmlspecialchars($ctaRegisterText); ?>
                </a>
                <a href="php/login.php" class="px-10 py-4 bg-white/10 backdrop-blur-sm border-2 border-white text-white rounded-lg font-semibold hover:bg-white/20 transition-all duration-300 inline-block text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i><?php echo htmlspecialchars($ctaLoginText); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?> 