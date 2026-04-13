<?php
require_once dirname(__DIR__) . '/BaseViewModel.php';
require_once dirname(__DIR__) . '/AdminModel.php';
require_once dirname(__DIR__) . '/SchoolModel.php';

/**
 * Admin panel işlemleri için ViewModel sınıfı
 */
class AdminViewModel extends BaseViewModel {
    private $adminModel;
    private $schoolModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->adminModel = new AdminModel($db);
        $this->schoolModel = new SchoolModel($db);
    }
    
    /**
     * View için verileri hazırla
     * @param array $params View için gerekli parametreler
     * @return array View'a iletilecek veriler
     */
    public function prepareViewData($params = []) {
        $action = $params['action'] ?? 'dashboard';
        $this->addViewData('action', $action);
        
        // Kullanıcı yetkisi kontrolü
        if (!$this->checkAuthorization()) {
            return $this->createError('Bu sayfaya erişim izniniz bulunmamaktadır.');
        }
        
        switch ($action) {
            case 'dashboard':
                $this->prepareDashboardData();
                break;
                
            case 'users':
            case 'teachers':
                $this->prepareUsersData($params);
                break;
                
            case 'schools':
                $this->prepareSchoolsData($params);
                break;
                
            case 'branches':
                $this->prepareBranchesData($params);
                break;
                
            case 'applications':
                $this->prepareApplicationsData($params);
                break;
                
            case 'reports':
                $this->prepareReportsData($params);
                break;
                
            case 'settings':
                $this->prepareSettingsData($params);
                break;
        }
        
        return parent::prepareViewData();
    }
    
    /**
     * Dashboard verilerini hazırla
     */
    private function prepareDashboardData() {
        // Kullanıcı istatistikleri
        $userStats = $this->adminModel->getUserCountsByRole();
        $this->addViewData('userStats', $userStats);
        
        // Son aktiviteler
        $recentActivity = $this->adminModel->getRecentActivity(5);
        $this->addViewData('recentActivity', $recentActivity);
        
        // Başvuru istatistikleri
        $filters = [];
        $applications = $this->adminModel->getApplications($filters);
        if (!is_array($applications)) {
            $applications = [];
        }
        
        $applicationStats = [
            'total' => count($applications),
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
        
        foreach ($applications as $app) {
            if ($app['status'] === 'pending') {
                $applicationStats['pending']++;
            } elseif ($app['status'] === 'approved') {
                $applicationStats['approved']++;
            } elseif ($app['status'] === 'rejected') {
                $applicationStats['rejected']++;
            }
        }
        
        $this->addViewData('applicationStats', $applicationStats);
    }
    
    /**
     * Kullanıcı listesi verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareUsersData($params) {
        $filters = $params['filters'] ?? [];
        
        // Eğer action 'teachers' ise ve filters'da role yoksa, role = 'teacher' ekle
        $action = $params['action'] ?? '';
        if ($action === 'teachers' && !isset($filters['role'])) {
            $filters['role'] = 'teacher';
        }
        
        // GET parametrelerinden filtreleri al
        if (isset($_GET['role']) && !isset($filters['role'])) {
            $filters['role'] = clean_input($_GET['role']);
        }
        if (isset($_GET['search']) && !isset($filters['search'])) {
            $filters['search'] = clean_input($_GET['search']);
        }
        if (isset($_GET['school_type']) && !isset($filters['school_type'])) {
            $filters['school_type'] = clean_input($_GET['school_type']);
        }
        
        $users = $this->adminModel->getUsers($filters);
        if ($users === false) {
            $users = [];
        }
        $this->addViewData('users', $users);
        $this->addViewData('filters', $filters);
        $this->addViewData('schoolTypes', $this->adminModel->getSchoolTypes());
        
        // Okul listesini de gönder (okul yöneticisi eklerken kullanılacak)
        $schools = $this->adminModel->getSchools(['status' => 'active']);
        $this->addViewData('schools', $schools);
        
        // Eğer kullanıcı detayı isteniyorsa
        if (isset($params['user_id']) && $params['user_id']) {
            $user = $this->adminModel->getById($params['user_id']);
            $this->addViewData('userDetail', $user);
            
            // Eğer okul yöneticisi ise, okul bilgisini de getir
            if ($user && isset($user['role']) && $user['role'] === 'school_admin') {
                $query = "
                    SELECT s.* FROM schools s
                    JOIN school_admins sa ON s.id = sa.school_id
                    WHERE sa.user_id = ?
                ";
                $userSchool = $this->adminModel->fetchOne($query, [$params['user_id']]);
                $this->addViewData('userSchool', $userSchool);
            }
        }
    }
    
    /**
     * Okul listesi verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareSchoolsData($params) {
        $filters = $params['filters'] ?? [];
        $schools = $this->adminModel->getSchools($filters);
        $this->addViewData('schools', $schools);
        $this->addViewData('filters', $filters);
        
        // Eğer okul detayı isteniyorsa
        if (isset($params['school_id']) && $params['school_id']) {
            $school = $this->adminModel->getById($params['school_id'], 'schools');
            $this->addViewData('schoolDetail', $school);
            
            // Okula ait yöneticileri de getir
            $query = "
                SELECT u.* FROM users u
                JOIN school_admins sa ON u.id = sa.user_id
                WHERE sa.school_id = ? AND u.role = 'school_admin'
            ";
            $schoolAdmins = $this->adminModel->fetchAll($query, [$params['school_id']]);
            $this->addViewData('schoolAdmins', $schoolAdmins);
            
            // Okula yapılan başvuruları da getir
            $applications = $this->adminModel->getApplications(['school_id' => $params['school_id']]);
            $this->addViewData('schoolApplications', $applications);
        }
    }
    
    /**
     * Branş listesi verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareBranchesData($params) {
        $branches = $this->adminModel->getBranches();
        $this->addViewData('branches', $branches);
        
        // Eğer branş detayı isteniyorsa
        if (isset($params['branch_id']) && $params['branch_id']) {
            $branch = $this->adminModel->getById($params['branch_id'], 'branches');
            $this->addViewData('branchDetail', $branch);
            
            // Bu branşa yapılan başvuruları da getir
            $applications = $this->adminModel->getApplications(['branch_id' => $params['branch_id']]);
            $this->addViewData('branchApplications', $applications);
        }
    }

    /**
     * Başvuru listesi verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareApplicationsData($params) {
        $filters = $params['filters'] ?? [];
        $applications = $this->adminModel->getApplications($filters);
        $this->addViewData('applications', $applications);
        $this->addViewData('filters', $filters);
        
        // Filtre için okul ve branş listesini de gönder
        $schools = $this->adminModel->getSchools(['status' => 'active']);
        $branches = $this->adminModel->getBranches();
        $this->addViewData('schools', $schools);
        $this->addViewData('branches', $branches);
        
        // Eğer başvuru detayı isteniyorsa
        if (isset($params['application_id']) && $params['application_id']) {
            $query = "
                SELECT a.*, 
                       u.name as teacher_name, u.email as teacher_email,
                       s.name as school_name, 
                       b.name as branch_name,
                       tp.cv_file, tp.education, tp.experience, tp.skills, tp.about,
                       admin.name as approved_by_name
                FROM applications a
                JOIN users u ON a.teacher_id = u.id
                JOIN schools s ON a.school_id = s.id
                JOIN branches b ON a.branch_id = b.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                LEFT JOIN users admin ON a.approved_by = admin.id
                WHERE a.id = ?
            ";
            $application = $this->adminModel->fetchOne($query, [$params['application_id']]);
            $this->addViewData('applicationDetail', $application);
            
            // Öğretmenin deneyim ve eğitim bilgilerini de getir
            $teacherId = $application['teacher_id'];
            
            $query = "SELECT * FROM teacher_experiences WHERE user_id = ? ORDER BY start_date DESC";
            $experiences = $this->adminModel->fetchAll($query, [$teacherId]);
            $this->addViewData('experiences', $experiences);
            
            $query = "SELECT * FROM teacher_education WHERE user_id = ? ORDER BY start_date DESC";
            $education = $this->adminModel->fetchAll($query, [$teacherId]);
            $this->addViewData('education', $education);
        }
    }

    /**
     * Rapor verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareReportsData($params) {
        $filters = $params['filters'] ?? [];
        
        // Excel için rapor verileri
        if (isset($params['export']) && $params['export'] === 'excel') {
            $applications = $this->adminModel->getApplicationsForReport($filters);
            $this->addViewData('exportData', $applications);
            $this->addViewData('export', 'excel');
        } else {
            // Normal rapor görüntüleme için veriler
            $applications = $this->adminModel->getApplications($filters);
            $this->addViewData('applications', $applications);
            
            // Filtre seçenekleri için okullar ve branşlar
            $schools = $this->adminModel->getSchools(['status' => 'active']);
            $branches = $this->adminModel->getBranches();
            $this->addViewData('schools', $schools);
            $this->addViewData('branches', $branches);
            $this->addViewData('filters', $filters);
        }
    }
    
    /**
     * Ayarlar sayfası verilerini hazırla
     * @param array $params Parametre dizisi
     */
    private function prepareSettingsData($params) {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `settings` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) DEFAULT NULL COMMENT 'NULL = genel ayar, NOT NULL = kullanıcı bazlı ayar',
                  `setting_key` varchar(100) NOT NULL,
                  `setting_value` text DEFAULT NULL,
                  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_setting_key_user` (`user_id`, `setting_key`),
                  KEY `idx_setting_group` (`setting_group`),
                  KEY `idx_user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Throwable $t) {
            error_log('Settings table creation error: ' . $t->getMessage());
        }

        $settings = [];
        try {
            // Genel ayarları getir (user_id IS NULL)
            $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE user_id IS NULL");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            // Kullanıcı bazlı bildirim ayarlarını getir
            if (isset($_SESSION['user_id'])) {
                $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM settings WHERE user_id = ? AND setting_group = 'notification'");
                $stmt->execute([$_SESSION['user_id']]);
                $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($userRows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Throwable $t) {
            error_log('Settings fetch error: ' . $t->getMessage());
        }

        // Varsayılan değerler
        $defaults = [
            'site_title' => 'Öğretmen İş Başvuru Sistemi',
            'site_description' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'contact_address' => '',
            'site_logo' => '',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'from_email' => '',
            'from_name' => 'ÖğretmenPro',
            'session_timeout' => '30',
            'max_login_attempts' => '5',
            'password_min_length' => '8',
            'require_strong_password' => '0',
            'maintenance_mode' => '0',
            'error_reporting' => '0',
            'max_upload_size' => '5242880',
            'allowed_file_types' => 'pdf,doc,docx,jpg,png',
            'auto_backup' => '0',
            'backup_frequency' => 'daily',
            'email_notifications' => '1',
            'sms_notifications' => '0',
            'application_notifications' => '1',
            'system_notifications' => '1',
            'homepage_hero_title' => 'Öğretmen İş Başvuru Sistemi',
            'homepage_hero_subtitle' => 'Türkiye\'nin güvenilir öğretmen iş başvuru platformu. Kariyerinizi profesyonel bir şekilde yönetin ve hayalinizdeki okula ulaşın.',
            'homepage_stats_teachers' => '5000+',
            'homepage_stats_schools' => '1200+',
            'homepage_stats_success_rate' => '92%',
            'homepage_features_title' => 'Sistem Nasıl Çalışır?',
            'homepage_features_subtitle' => 'ÖğretmenPro platformu ile kariyer yolculuğunuz dört adımda başlar. Profesyonel ve güvenilir bir süreç ile hayalinizdeki okula ulaşın.',
            'homepage_feature1_title' => 'Kayıt Olun',
            'homepage_feature1_description' => 'Profilinizi oluşturun, kişisel bilgilerinizi girin ve CV belgenizi yükleyin.',
            'homepage_feature2_title' => 'Başvuru Yapın',
            'homepage_feature2_description' => 'İlgilendiğiniz okullara kolayca başvuru gönderin ve başvuru durumunuzu takip edin.',
            'homepage_feature3_title' => 'Değerlendirme',
            'homepage_feature3_description' => 'Okul yöneticileri başvurunuzu değerlendirir ve size bildirim gönderilir.',
            'homepage_feature4_title' => 'Sonuç',
            'homepage_feature4_description' => 'Başvuru sonucunuzu takip edin ve yeni kariyer fırsatınızı yakalayın.',
            'homepage_stats_title' => 'Platform İstatistikleri',
            'homepage_stats_teachers_label' => 'Aktif Öğretmen Üye',
            'homepage_stats_schools_label' => 'Kayıtlı Okul',
            'homepage_stats_success_label' => 'Başarılı Yerleştirme Oranı',
            'homepage_advantages_title' => 'Neden ÖğretmenPro?',
            'homepage_advantages_subtitle' => 'Profesyonel bir platform ile kariyerinizi yönetin ve en iyi fırsatlara ulaşın.',
            'homepage_advantage1_title' => 'Güvenli Platform',
            'homepage_advantage1_description' => 'Kişisel bilgileriniz güvenli bir şekilde korunur ve sadece yetkili kişiler erişebilir.',
            'homepage_advantage2_title' => 'Hızlı Süreç',
            'homepage_advantage2_description' => 'Başvurularınız hızlı bir şekilde değerlendirilir ve sonuçlar size bildirilir.',
            'homepage_advantage3_title' => '7/24 Destek',
            'homepage_advantage3_description' => 'Her zaman yanınızdayız. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.',
            'homepage_cta_title' => 'Kariyerinize Bugün Başlayın',
            'homepage_cta_subtitle' => 'Türkiye\'nin güvenilir öğretmen iş başvuru platformuna katılın ve hayalinizdeki okula ulaşın. Profesyonel bir şekilde kariyerinizi yönetin.',
            'homepage_cta_register_text' => 'Hemen Ücretsiz Kaydol',
            'homepage_cta_login_text' => 'Giriş Yap',
            'admin_panel_theme' => 'light',
            'admin_panel_language' => 'tr',
            'admin_panel_sidebar_collapsed' => '0',
            'admin_panel_items_per_page' => '10',
            'admin_panel_notifications_enabled' => '1',
            'admin_panel_email_notifications' => '1',
            'admin_panel_system_notifications' => '1',
            'admin_panel_application_notifications' => '1',
            'admin_panel_dashboard_widgets' => 'all',
            'admin_panel_auto_refresh' => '0',
            'admin_panel_refresh_interval' => '30'
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        $this->addViewData('settings', $settings);
    }
    
    /**
     * Kullanıcı girdilerini işle
     * @param array $input Kullanıcı girdileri
     * @return mixed İşlem sonucu
     */
    public function processInput($input = []) {
        $action = $input['action'] ?? '';
        $userId = $this->getCurrentUserId();
        
        // Kullanıcı yetkisi kontrolü
        if (!$this->checkAuthorization()) {
            return $this->createError('Bu işlemi gerçekleştirme yetkiniz bulunmamaktadır.');
        }
        
        switch ($action) {
            case 'create_user':
                return $this->createUser($input);
                
            case 'update_user':
                return $this->updateUserInternal($input);
                
            case 'create_school':
                return $this->createSchool($input);
                
            case 'update_school':
                return $this->updateSchool($input);
                
            case 'create_branch':
                return $this->createBranch($input);
                
            case 'update_branch':
                return $this->updateBranch($input);
                
            case 'update_application_status':
                return $this->updateApplicationStatus($input, $userId);
                
            case 'update_settings':
                return $this->updateSettings($input);
                
            default:
                return $this->createError('Geçersiz işlem.');
        }
    }

    /**
     * Yeni kullanıcı oluştur
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function createUser($input) {
        $required = ['name', 'email', 'password', 'role'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // E-posta kontrolü
        $existingUser = $this->adminModel->getOne(['email' => $input['email']]);
        if ($existingUser) {
            return $this->createError('Bu e-posta adresi ile kayıtlı bir kullanıcı zaten mevcut.');
        }
        
        $result = $this->adminModel->createUser($input);
        
        if ($result) {
            return $this->createSuccess('Kullanıcı başarıyla oluşturuldu.', ['user_id' => $result]);
            } else {
            return $this->createError('Kullanıcı oluşturulurken bir hata oluştu.');
        }
    }

    /**
     * Kullanıcı güncelle (internal - form processing için)
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function updateUserInternal($input) {
        $userId = $input['user_id'] ?? 0;
        if (!$userId) {
            return $this->createError('Kullanıcı ID bulunamadı.');
        }
        
        $required = ['name', 'email', 'role'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // E-posta kontrolü (kendisi hariç)
        $existingUser = $this->adminModel->getOne(['email' => $input['email']]);
        if ($existingUser && $existingUser['id'] != $userId) {
            return $this->createError('Bu e-posta adresi ile kayıtlı başka bir kullanıcı zaten mevcut.');
        }
        
        $result = $this->adminModel->updateUser($userId, $input);
        
        if ($result) {
            return $this->createSuccess('Kullanıcı bilgileri başarıyla güncellendi.');
        } else {
            return $this->createError('Kullanıcı bilgileri güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Yeni okul oluştur
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function createSchool($input) {
        $required = ['name', 'address', 'email'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        $result = $this->adminModel->createSchool($input);
        
        if ($result) {
            return $this->createSuccess('Okul başarıyla oluşturuldu.', ['school_id' => $result]);
            } else {
            return $this->createError('Okul oluşturulurken bir hata oluştu.');
        }
    }
    
    /**
     * Okul güncelle
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function updateSchool($input) {
        $schoolId = $input['school_id'] ?? 0;
        if (!$schoolId) {
            return $this->createError('Okul ID bulunamadı.');
        }
        
        $required = ['name', 'address', 'email'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        $result = $this->adminModel->updateSchool($schoolId, $input);
        
        if ($result) {
            return $this->createSuccess('Okul bilgileri başarıyla güncellendi.');
            } else {
            return $this->createError('Okul bilgileri güncellenirken bir hata oluştu.');
        }
    }
    
    /**
     * Yeni branş oluştur
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function createBranch($input) {
        $required = ['name', 'code'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // Kod benzersizlik kontrolü
        $existingBranch = $this->adminModel->getOne(['code' => $input['code']], 'branches');
        if ($existingBranch) {
            return $this->createError('Bu kod ile kayıtlı bir branş zaten mevcut.');
        }
        
        $result = $this->adminModel->createBranch($input);
        
        if ($result) {
            return $this->createSuccess('Branş başarıyla oluşturuldu.', ['branch_id' => $result]);
                } else {
            return $this->createError('Branş oluşturulurken bir hata oluştu.');
        }
    }

    /**
     * Branş güncelle
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function updateBranch($input) {
        $branchId = $input['branch_id'] ?? 0;
        if (!$branchId) {
            return $this->createError('Branş ID bulunamadı.');
        }
        
        $required = ['name', 'code'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // Kod benzersizlik kontrolü (kendisi hariç)
        $existingBranch = $this->adminModel->getOne(['code' => $input['code']], 'branches');
        if ($existingBranch && $existingBranch['id'] != $branchId) {
            return $this->createError('Bu kod ile kayıtlı başka bir branş zaten mevcut.');
        }
        
        $result = $this->adminModel->updateBranch($branchId, $input);
        
        if ($result) {
            return $this->createSuccess('Branş bilgileri başarıyla güncellendi.');
        } else {
            return $this->createError('Branş bilgileri güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Başvuru durumunu güncelle
     * @param array $input Form verileri
     * @param int $adminId Onaylayan admin ID
     * @return array İşlem sonucu
     */
    private function updateApplicationStatus($input, $adminId) {
        $applicationId = $input['application_id'] ?? 0;
        if (!$applicationId) {
            return $this->createError('Başvuru ID bulunamadı.');
        }
        
        $status = $input['status'] ?? '';
        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->createError('Geçersiz başvuru durumu.');
        }
        
        $notes = $input['notes'] ?? null;
        
        $result = $this->adminModel->updateApplicationStatus($applicationId, $status, $adminId, $notes);
        
        if ($result) {
            // Başvuruya ait öğretmene bildirim gönder
            $query = "SELECT teacher_id FROM applications WHERE id = ?";
            $application = $this->adminModel->fetchOne($query, [$applicationId]);
            
            if ($application) {
                $message = 'Başvurunuzun durumu güncellendi: ' . ($status === 'approved' ? 'Onaylandı' : 'Reddedildi');
                $this->createNotification($application['teacher_id'], 'application_status', $message, $applicationId);
            }
            
            return $this->createSuccess('Başvuru durumu başarıyla güncellendi.');
                } else {
            return $this->createError('Başvuru durumu güncellenirken bir hata oluştu.');
        }
    }
    
    /**
     * Sistem ayarlarını güncelle
     * @param array $input Form verileri
     * @return array İşlem sonucu
     */
    private function updateSettings($input) {
        // Sistem ayarları güncelleme işlemleri burada yapılacak
        // Örnek olarak, bir dosyaya yazılabilir veya veritabanına kaydedilebilir
        
        return $this->createSuccess('Sistem ayarları başarıyla güncellendi.');
    }
    
    /**
     * Bildirim oluştur
     * @param int $userId Kullanıcı ID
     * @param string $type Bildirim tipi
     * @param string $message Bildirim mesajı
     * @param int $referenceId Referans ID (isteğe bağlı)
     * @return bool
     */
    private function createNotification($userId, $type, $message, $referenceId = null) {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'reference_id' => $referenceId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->adminModel->create($data, 'notifications');
    }
    
    /**
     * Yetkilendirme kontrolü
     * @param array $params Kontrol parametreleri
     * @return bool Yetkili mi?
     */
    public function checkAuthorization($params = []) {
        return $this->checkSession('admin');
    }

    /**
     * Okul yöneticilerini getir
     * @return array Okul yöneticileri listesi
     */
    public function getSchoolAdmins($filters = []) {
        try {
            return $this->adminModel->getSchoolAdminsWithSchools($filters);
        } catch (PDOException $e) {
            error_log("Okul yöneticileri getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Toplam öğretmen sayısını getir
     * @return int Toplam öğretmen sayısı
     */
    public function getTotalTeachers() {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE role = 'teacher'";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get total teachers error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Toplam okul sayısını getir
     * @return int Toplam okul sayısı
     */
    public function getTotalSchools() {
        try {
            $query = "SELECT COUNT(*) as count FROM schools WHERE status = 'active'";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get total schools error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Toplam başvuru sayısını getir
     * @return int Toplam başvuru sayısı
     */
    public function getTotalApplications() {
        try {
            $query = "SELECT COUNT(*) as count FROM applications";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get total applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Panele ait içerik sayfalarını dahil et
     */
    public function includeActionContent($action) {
        $db = $this->db;
        $viewModel = $this;
        $rootPath = dirname(__DIR__, 2);
        $componentPath = $rootPath . '/components/admin/' . $action . '.php';
        if (file_exists($componentPath)) {
            include $componentPath;
        } else {
            include $rootPath . '/components/admin/dashboard.php';
        }
    }

    public function updateSchoolStatus($schoolId, $status) {
        return $this->adminModel->updateSchoolStatus($schoolId, $status);
    }

    /**
     * Başvuruları filtreleyerek getirir.
     * @param array $filters
     * @return array
     */
    public function getApplications($filters = [])
    {
        return $this->adminModel->getApplications($filters);
    }

    /**
     * Okulları filtreleyerek getirir.
     * @param array $filters
     * @return array
     */
    public function getSchools($filters = [])
    {
        return $this->adminModel->getSchools($filters);
    }

    /**
     * İlçeye bağlı okulları getirir.
     * @param string $county İlçe adı
     * @return array Okul listesi (kurum kodu, okul adı, okul türü)
     */
    public function getSchoolsByCounty($county)
    {
        try {
            $query = "
                SELECT 
                    school_code,
                    name,
                    school_type
                FROM schools
                WHERE county = ?
                ORDER BY name ASC
            ";
            $schools = $this->fetchAll($query, [$county]);
            return $schools ?: [];
        } catch (PDOException $e) {
            error_log("Get schools by county error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Branşları getirir (arama/filtre destekli)
     * @param array $filters
     * @return array
     */
    public function getBranches($filters = [])
    {
        return $this->adminModel->getBranches($filters);
    }

    /**
     * Tüm okulları getirir. getSchools() için bir alias.
     * @return array
     */
    public function getAllSchools()
    {
        return $this->getSchools();
    }

    /**
     * Tüm branşları getirir. getBranches() için bir alias.
     * @return array
     */
    public function getAllBranches($page = 1, $perPage = 10000, $sort = 'name', $direction = 'asc', $filters = [])
    {
        // Not implementing real pagination here; return filtered list
        return $this->getBranches($filters);
    }

    /**
     * Get total number of branches
     * @return int Total number of branches
     */
    public function getTotalBranchesCount()
    {
        return $this->adminModel->count('branches');
    }

    /**
     * Kullanıcıları filtrele
     * @param string|null $role
     * @param string|null $search
     * @return array
     */
    public function filterUsers($role = null, $search = null, $schoolType = null)
    {
        // İlçe M.E.M. Yöneticileri sayfası: yalnızca ad/soyad üzerinden arama yap
        if ($role === 'country_admin') {
            $query = "SELECT * FROM users WHERE role = ?";
            $params = [$role];
            if ($search) {
                $query .= " AND (name LIKE ? OR surname LIKE ? OR CONCAT(name, ' ', IFNULL(surname, '')) LIKE ?)";
                $term = '%' . $search . '%';
                $params[] = $term; $params[] = $term; $params[] = $term;
            }
            $query .= " ORDER BY name ASC";
            return $this->fetchAll($query, $params);
        }

        $filters = ['role' => $role, 'search' => $search];
        if ($schoolType) {
            $filters['school_type'] = $schoolType;
        }
        $result = $this->adminModel->getUsers($filters);
        
        return $result !== false ? $result : [];
    }

    /**
     * Okul türlerini getirir.
     * @return array
     */
    public function getSchoolTypes()
    {
        return $this->adminModel->getSchoolTypes();
    }

    /**
     * Kullanıcı detaylarını getirir.
     * @param int $userId
     * @return array|null
     */
    public function getUserDetails($userId)
    {
        // AdminModel'in getOne metodu 'users' tablosunu kullanacak şekilde ayarlı.
        return $this->adminModel->getOne(['id' => $userId]);
    }

    /**
     * Dashboard istatistiklerini getir
     * @return array Dashboard istatistikleri
     */
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Toplam öğretmen sayısı
            $stats['total_teachers'] = $this->getTotalTeachers();
            
            // Başvuru istatistikleri
            $query = "
                SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
                FROM applications
            ";
            $result = $this->fetchOne($query);
            if ($result) {
                $stats['total_applications'] = $result['total_applications'] ?? 0;
                $stats['pending_applications'] = $result['pending_applications'] ?? 0;
                $stats['approved_applications'] = $result['approved_applications'] ?? 0;
                $stats['rejected_applications'] = $result['rejected_applications'] ?? 0;
            } else {
                $stats['total_applications'] = 0;
                $stats['pending_applications'] = 0;
                $stats['approved_applications'] = 0;
                $stats['rejected_applications'] = 0;
            }
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'total_teachers' => 0,
                'total_applications' => 0,
                'pending_applications' => 0,
                'approved_applications' => 0,
                'rejected_applications' => 0
            ];
        }
    }

    /**
     * Aktif başvuruları say
     * @return int Aktif başvuru sayısı
     */
    public function getActiveApplications() {
        try {
            $query = "
                SELECT COUNT(*) as count
                FROM applications
                WHERE status = 'pending'
            ";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get active applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Bekleyen başvuruları say
     * @return int Bekleyen başvuru sayısı
     */
    public function getPendingApplications() {
        try {
            $query = "
                SELECT COUNT(*) as count
                FROM applications
                WHERE status = 'pending'
            ";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get pending applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Reddedilen başvuruları say
     * @return int Reddedilen başvuru sayısı
     */
    public function getRejectedApplications() {
        try {
            $query = "
                SELECT COUNT(*) as count
                FROM applications
                WHERE status = 'rejected'
            ";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get rejected applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Onaylanan başvuruları say
     * @return int Onaylanan başvuru sayısı
     */
    public function getApprovedApplications() {
        try {
            $query = "
                SELECT COUNT(*) as count
                FROM applications
                WHERE status = 'approved'
            ";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get approved applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Toplu okul ekleme
     * @param array $schoolsData Okul verileri dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return array İşlem sonucu
     */
    public function bulkAddSchools($schoolsData, $userId) {
        return $this->schoolModel->bulkAddSchools($schoolsData, $userId);
    }

    /**
     * Kullanıcı güncelle (public wrapper)
     * @param int $userId Kullanıcı ID
     * @param array $userData Güncellenecek veriler
     * @param int|null $adminId İşlemi yapan admin ID (opsiyonel)
     * @return bool İşlem başarılı mı
     */
    public function updateUser($userId, $userData, $adminId = null) {
        try {
            error_log('AdminViewModel::updateUser - User ID: ' . $userId);
            error_log('AdminViewModel::updateUser - User Data: ' . print_r($userData, true));
            
            $result = $this->adminModel->updateUser($userId, $userData);
            
            error_log('AdminViewModel::updateUser - Result: ' . ($result ? 'true' : 'false'));
            
            return $result;
        } catch (Exception $e) {
            error_log('AdminViewModel::updateUser error: ' . $e->getMessage());
            error_log('AdminViewModel::updateUser stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Throwable $t) {
            error_log('AdminViewModel::updateUser throwable error: ' . $t->getMessage());
            error_log('AdminViewModel::updateUser stack trace: ' . $t->getTraceAsString());
            return false;
        }
    }

    /**
     * Genel ayarları güncelle
     * @param array $settings Ayarlar dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateGeneralSettings($settings, $userId) {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `settings` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) DEFAULT NULL COMMENT 'NULL = genel ayar, NOT NULL = kullanıcı bazlı ayar',
                  `setting_key` varchar(100) NOT NULL,
                  `setting_value` text DEFAULT NULL,
                  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_setting_key_user` (`user_id`, `setting_key`),
                  KEY `idx_setting_group` (`setting_group`),
                  KEY `idx_user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (NULL, ?, ?, 'general')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateGeneralSettings error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * E-posta ayarlarını güncelle
     * @param array $settings Ayarlar dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateEmailSettings($settings, $userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (NULL, ?, ?, 'email')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateEmailSettings error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * Güvenlik ayarlarını güncelle
     * @param array $settings Ayarlar dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateSecuritySettings($settings, $userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (NULL, ?, ?, 'security')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateSecuritySettings error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * Sistem ayarlarını güncelle
     * @param array $settings Ayarlar dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateSystemSettings($settings, $userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (NULL, ?, ?, 'system')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateSystemSettings error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * Ana sayfa ayarlarını güncelle
     * @param array $settings Ayarlar dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return bool İşlem başarılı mı
     */
    public function updateHomepageSettings($settings, $userId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (NULL, ?, ?, 'homepage')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateHomepageSettings error: ' . $t->getMessage());
            return false;
        }
    }

    /**
     * Bildirim ayarlarını güncelle (kullanıcı bazlı)
     * @param int $userId Kullanıcı ID
     * @param array $settings Bildirim ayarları dizisi
     * @return bool İşlem başarılı mı
     */
    public function updateNotificationSettings($userId, $settings) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings (user_id, setting_key, setting_value, setting_group)
                VALUES (?, ?, ?, 'notification')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
            ");

            foreach ($settings as $key => $value) {
                $stmt->execute([$userId, $key, $value ? '1' : '0']);
            }

            return true;
        } catch (Throwable $t) {
            error_log('updateNotificationSettings error: ' . $t->getMessage());
            return false;
        }
    }
}
?>