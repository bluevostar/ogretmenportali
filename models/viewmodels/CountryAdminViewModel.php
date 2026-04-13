<?php
require_once dirname(__DIR__) . '/BaseModel.php';
require_once dirname(__DIR__) . '/viewmodels/AdminViewModel.php';
require_once dirname(__DIR__) . '/viewmodels/ApplicationViewModel.php';
require_once dirname(__DIR__) . '/StatsModel.php';

class CountryAdminViewModel extends AdminViewModel {
    private $cache = [];
    private $applicationViewModel;
    private $statsModel;
    private $schoolModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->applicationViewModel = new ApplicationViewModel($db);
        $this->statsModel = new StatsModel($db);
        $this->schoolModel = new SchoolModel($db);
    }

    // İlçe yöneticisi istatistiklerini getir
    public function getCountryAdminStats($countryAdminId) {
        try {
            $stats = [];
            
            // Toplam okul sayısı
            $query = "SELECT COUNT(*) as total FROM schools WHERE country_admin_id = ?";
            $result = $this->fetchOne($query, [$countryAdminId]);
            $stats['total_schools'] = $result['total'];
            
            // Toplam branş sayısı
            $query = "SELECT COUNT(*) as total FROM branches";
            $result = $this->fetchOne($query);
            $stats['total_branches'] = $result['total'];
            
            // Toplam okul yöneticisi sayısı
            $query = "SELECT COUNT(*) as total FROM users WHERE role = ?";
            $result = $this->fetchOne($query, [ROLE_SCHOOL_ADMIN]);
            $stats['total_school_admins'] = $result['total'];
            
            // Toplam başvuru sayısı
            $query = "
                SELECT COUNT(*) as total 
                FROM applications a 
                JOIN schools s ON a.school_id = s.id 
                WHERE s.country_admin_id = ?
            ";
            $result = $this->fetchOne($query, [$countryAdminId]);
            $stats['total_applications'] = $result['total'];
            
            // Son başvurular
            $query = "
                SELECT a.id, a.status, a.created_at, 
                       u.name as teacher_name, 
                       s.name as school_name, 
                       b.name as branch_name 
                FROM applications a 
                JOIN users u ON a.teacher_id = u.id 
                JOIN schools s ON a.school_id = s.id 
                JOIN branches b ON a.branch_id = b.id 
                WHERE s.country_admin_id = ? 
                ORDER BY a.created_at DESC 
                LIMIT 10
            ";
            $stats['recent_applications'] = $this->fetchAll($query, [$countryAdminId]);
            
            return $stats;
        } catch (PDOException $e) {
            error_log("İlçe yöneticisi istatistikleri alınırken hata oluştu: " . $e->getMessage());
            return [
                'total_schools' => 0,
                'total_branches' => 0,
                'total_school_admins' => 0,
                'total_applications' => 0,
                'recent_applications' => []
            ];
        }
    }

    // Okulları getir
    public function getSchools($filters = []) {
        // AdminViewModel'den miras alındığı için signature'ı uyumlu hale getiriyoruz
        if (is_array($filters)) {
            $countryAdminId = $filters['country_admin_id'] ?? null;
            $search = $filters['search'] ?? '';
        } else {
            // Eski uyumluluk için
            $countryAdminId = $filters;
            $search = '';
        }
        try {
            $query = "SELECT * FROM schools WHERE country_admin_id = ?";
            $params = [$countryAdminId];
            
            if ($search) {
                $query .= " AND (name LIKE ? OR school_type LIKE ? OR city LIKE ? OR country LIKE ? OR address LIKE ? OR email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query .= " ORDER BY name ASC";
            
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("Okullar getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    // Okul detaylarını getir
    public function getSchoolById($schoolId) {
        try {
            $query = "SELECT * FROM schools WHERE id = ?";
            return $this->fetchOne($query, [$schoolId]);
        } catch (PDOException $e) {
            error_log("Okul detayları alınırken hata oluştu: " . $e->getMessage());
            return null;
        }
    }

    // Okul durumunu değiştir
    public function toggleSchoolStatus($schoolId, $status) {
        try {
            $data = ['active' => $status];
            $where = ['id' => $schoolId];
            return $this->update('schools', $data, $where);
        } catch (PDOException $e) {
            error_log("Okul durumu güncellenirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Okul sil
    public function deleteSchool($schoolId) {
        try {
            $where = ['id' => $schoolId];
            return $this->delete('schools', $where);
        } catch (PDOException $e) {
            error_log("Okul silinirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Okul yöneticilerini getir
    public function getSchoolAdmins($countryAdminId = null, $search = '') {
        try {
            error_log("Getting school admins for country admin ID: " . $countryAdminId); // Debug log
            
            if (!$countryAdminId) {
                error_log("CountryAdminId is required but was null");
                return [];
            }

            // Daha detaylı bir sorgu yazalım
            $query = "
                SELECT DISTINCT
                    u.id,
                    u.name,
                    u.email,
                    u.status,
                    u.created_at,
                    sa.position,
                    sa.school_id,
                    s.name as school_name,
                    s.school_code,
                    s.city
                FROM users u
                INNER JOIN school_admins sa ON u.id = sa.user_id
                INNER JOIN schools s ON sa.school_id = s.id
                WHERE u.role = 'school_admin'
                AND s.country_admin_id = ?
                AND u.status = 'active'
            ";
            
            $params = [$countryAdminId];
            
            if ($search) {
                $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR s.name LIKE ?)";
                $search = "%$search%";
                $params = array_merge($params, [$search, $search, $search]);
            }
            
            $query .= " ORDER BY u.name ASC";
            
            $result = $this->fetchAll($query, $params);
            error_log("Found " . count($result) . " school admins"); // Debug için sayıyı loglayalım
            
            return $result;
        } catch (PDOException $e) {
            error_log("Okul yöneticileri getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    // Başvuruları getir
    public function getApplications($filters = []) {
        // AdminViewModel'den miras alındığı için signature'ı uyumlu hale getiriyoruz
        // Filtrelerden gerekli bilgileri al
        if (is_array($filters) && isset($filters['country_admin_id'])) {
            $countryAdminId = $filters['country_admin_id'];
            $status = $filters['status'] ?? null;
            $search = $filters['search'] ?? '';
        } else {
            // Eski uyumluluk için
            $countryAdminId = $filters;
            $status = null;
            $search = '';
        }
        try {
            $query = "
                SELECT a.*, 
                       u.name as teacher_name, 
                       s.name as school_name, 
                       b.name as branch_name 
                FROM applications a 
                JOIN users u ON a.teacher_id = u.id 
                JOIN schools s ON a.school_id = s.id 
                JOIN branches b ON a.branch_id = b.id 
                WHERE s.country_admin_id = ?
            ";
            $params = [$countryAdminId];
            
            if ($status) {
                $query .= " AND a.status = ?";
                $params[] = $status;
            }
            
            if ($search) {
                $query .= " AND (u.name LIKE ? OR s.name LIKE ? OR b.name LIKE ?)";
                $search = "%$search%";
                $params = array_merge($params, [$search, $search, $search]);
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("Başvurular getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    // Başvuru detaylarını getir
    public function getApplicationDetails($applicationId) {
        try {
            $query = "
                SELECT a.*, 
                       u.name as teacher_name, 
                       s.name as school_name, 
                       b.name as branch_name 
                FROM applications a 
                JOIN users u ON a.teacher_id = u.id 
                JOIN schools s ON a.school_id = s.id 
                JOIN branches b ON a.branch_id = b.id 
                WHERE a.id = ?
            ";
            return $this->fetchOne($query, [$applicationId]);
        } catch (PDOException $e) {
            error_log("Başvuru detayları alınırken hata oluştu: " . $e->getMessage());
            return null;
        }
    }

    // Başvuru durumunu güncelle
    public function updateApplicationStatus($applicationId, $status) {
        try {
            $data = ['status' => $status];
            $where = ['id' => $applicationId];
            return $this->update('applications', $data, $where);
        } catch (PDOException $e) {
            error_log("Başvuru durumu güncellenirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Panel erişim bilgilerini getir
    public function getPanelAccess($userId) {
        try {
            $query = "
                SELECT pa.*, u.name as user_name, u.email as user_email 
                FROM panel_access pa 
                JOIN users u ON pa.user_id = u.id 
                WHERE pa.user_id = ?
            ";
            return $this->fetchOne($query, [$userId]);
        } catch (PDOException $e) {
            error_log("Panel erişim bilgileri alınırken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Panel erişim bilgilerini oluştur
    public function createPanelAccess($userId, $data) {
        try {
            $data['user_id'] = $userId;
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->insert('panel_access', $data);
        } catch (PDOException $e) {
            error_log("Panel erişim bilgileri oluşturulurken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Panel erişim bilgilerini güncelle
    public function updatePanelAccess($userId, $data) {
        try {
            $where = ['user_id' => $userId];
            return $this->update('panel_access', $data, $where);
        } catch (PDOException $e) {
            error_log("Panel erişim bilgileri güncellenirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    // Toplam öğretmen sayısını getir
    public function getTotalTeachers($countryAdminId = null) {
        // If countryAdminId is not specified, use parent method
        if ($countryAdminId === null) {
            return parent::getTotalTeachers();
        }
        
        try {
            $query = "
                SELECT COUNT(DISTINCT a.teacher_id) as count 
                FROM applications a 
                JOIN schools s ON a.school_id = s.id 
                WHERE s.country_admin_id = ?
            ";
            $result = $this->fetchOne($query, [$countryAdminId]);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Toplam öğretmen sayısı alınırken hata oluştu: " . $e->getMessage());
            return 0;
        }
    }

    // Tüm okulları getir
    public function getAllSchools() {
        try {
            $query = "SELECT * FROM schools ORDER BY name ASC";
            return $this->fetchAll($query);
        } catch (PDOException $e) {
            error_log("Tüm okullar getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tüm kullanıcıları getir
     * @return array Kullanıcı listesi
     */
    public function getAllUsers() {
        try {
            $query = "SELECT u.*, s.name as school_name, b.name as branch_name 
                     FROM users u 
                     LEFT JOIN schools s ON u.school_id = s.id 
                     LEFT JOIN teacher_profiles tp ON u.id = tp.user_id 
                     LEFT JOIN branches b ON tp.branch_id = b.id 
                     WHERE u.role = ?
                     ORDER BY u.created_at DESC";
            
            return $this->fetchAll($query, [ROLE_TEACHER]);
        } catch (PDOException $e) {
            error_log("Kullanıcılar getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Panele ait içerik sayfalarını dahil et
     */
    public function includeActionContent($action) {
        $db = $this->db;
        $viewModel = $this;
        $rootPath = dirname(__DIR__, 2);
        // Eğer action admin_panel ise admin panel bileşenlerini yükle
        if ($action === 'admin_panel') {
            $componentPath = $rootPath . '/components/admin/dashboard.php';
            if (file_exists($componentPath)) {
                include $componentPath;
                return;
            }
        }
        // Varsayılan olarak country_admin panel bileşenlerini yükle
        $componentPath = $rootPath . '/components/country_admin/' . $action . '.php';
        if (file_exists($componentPath)) {
            include $componentPath;
        } else {
            include $rootPath . '/components/country_admin/dashboard.php';
        }
    }

    /**
     * Başarı mesajını getir
     * @return string HTML formatında başarı mesajı
     */
    public function getSuccessMessage() {
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status'];
            $message = $_GET['message'];
            
            $alertClass = ($status === 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            
            return sprintf('
                <div class="mb-4 p-4 rounded %s" role="alert">
                    <button type="button" class="float-right alert-close" aria-label="Kapat">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p class="mb-0">%s</p>
                </div>
            ', $alertClass, htmlspecialchars($message));
        }
        return '';
    }

    /**
     * Dashboard istatistiklerini getir
     * @return array Dashboard istatistikleri
     */
    public function getDashboardStats() {
        try {
            $stats = [];
            $userId = $this->getCurrentUserId();
            
            // Toplam okul sayısı
            $query = "SELECT COUNT(*) as total FROM schools WHERE country_admin_id = ?";
            $result = $this->fetchOne($query, [$userId]);
            $stats['total_schools'] = $result['total'] ?? 0;
            
            // Başvuru istatistikleri
            $query = "
                SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                WHERE s.country_admin_id = ?
            ";
            $result = $this->fetchOne($query, [$userId]);
            if ($result) {
                $stats = array_merge($stats, $result);
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
                'total_schools' => 0,
                'total_applications' => 0,
                'pending_applications' => 0,
                'approved_applications' => 0,
                'rejected_applications' => 0
            ];
        }
    }

    /**
     * Tüm başvuruları getir
     * @param string $status_filter Durum filtresi (opsiyonel)
     * @return array Başvuru listesi
     */
    public function getAllApplications($status_filter = '') {
        try {
            $userId = $this->getCurrentUserId();
            $query = "
                SELECT a.*, 
                       u.name as teacher_name, u.email as teacher_email,
                       s.name as school_name, s.city as school_city,
                       b.name as branch
                FROM applications a
                JOIN users u ON a.teacher_id = u.id
                JOIN schools s ON a.school_id = s.id
                JOIN branches b ON a.branch_id = b.id
                WHERE s.country_admin_id = ?
            ";
            $params = [$userId];
            
            if ($status_filter) {
                $query .= " AND a.status = ?";
                $params[] = $status_filter;
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("Get all applications error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tüm branşları getir
     * @return array Branş listesi
     */
    public function getAllBranches() {
        try {
            $query = "SELECT * FROM branches ORDER BY name";
            return $this->fetchAll($query);
        } catch (PDOException $e) {
            error_log("Get all branches error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kullanıcıları filtrele
     * @param string|null $role Rol filtresi
     * @param string|null $search Arama terimi
     * @return array Filtrelenmiş kullanıcı listesi
     */
    public function filterUsers($role = null, $search = null) {
        try {
            if ($role === 'school_admin') {
                $query = "
                    SELECT sa.*, s.name as school_name 
                    FROM school_admins sa
                    JOIN schools s ON sa.school_id = s.id
                    WHERE sa.status = 'active'
                    ORDER BY sa.name ASC
                ";
                return $this->fetchAll($query);
            }
            
            if ($role === 'country_admin') {
                $query = "SELECT * FROM users WHERE role = ?";
                $params = [$role];
                if ($search) {
                    // Sadece ad/soyad ve tam ad üzerinde arama
                    $query .= " AND (name LIKE ? OR surname LIKE ? OR CONCAT(name, ' ', IFNULL(surname, '')) LIKE ?)";
                    $searchTerm = '%' . $search . '%';
                    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
                }
                $query .= " ORDER BY name ASC";
                return $this->fetchAll($query, $params);
            }
            
            // If no specific role is requested or for other roles, return empty array
            return [];
            
        } catch (PDOException $e) {
            error_log("Kullanıcılar filtrelenirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Aktif başvuruları say
     * @return int Aktif başvuru sayısı
     */
    public function getActiveApplications() {
        try {
            $userId = $this->getCurrentUserId();
            $query = "
                SELECT COUNT(*) as count
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                WHERE s.country_admin_id = ? AND a.status = 'pending'
            ";
            $result = $this->fetchOne($query, [$userId]);
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
            $userId = $this->getCurrentUserId();
            $query = "
                SELECT COUNT(*) as count
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                WHERE s.country_admin_id = ? AND a.status = 'pending'
            ";
            $result = $this->fetchOne($query, [$userId]);
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
            $userId = $this->getCurrentUserId();
            $query = "
                SELECT COUNT(*) as count
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                WHERE s.country_admin_id = ? AND a.status = 'rejected'
            ";
            $result = $this->fetchOne($query, [$userId]);
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
            $userId = $this->getCurrentUserId();
            $query = "
                SELECT COUNT(*) as count
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                WHERE s.country_admin_id = ? AND a.status = 'approved'
            ";
            $result = $this->fetchOne($query, [$userId]);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get approved applications error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total number of branches
     * @return int Total number of branches
     */
    public function getTotalBranchesCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM branches";
            $result = $this->fetchOne($query);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total branches count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tüm kayıtları getir
     */
    protected function fetchAll($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tek kayıt getir
     */
    protected function fetchOne($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kullanıcı detaylarını getir
     */
    public function getUserDetails($user_id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    /**
     * İlçe M.E.M. yöneticilerini getir
     * @param int|null $countryAdminId
     * @param string $search
     * @return array
     */
    public function getDistrictAdmins($countryAdminId = null, $search = '') {
        try {
            $query = "SELECT id, name, email, phone, city, county, status, created_at FROM users WHERE role = 'country_admin'";
            $params = [];
            if ($search) {
                $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ? OR county LIKE ?)";
                $searchParam = "%$search%";
                $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
            }
            $query .= " ORDER BY name ASC";
            return $this->fetchAll($query, $params);
        } catch (PDOException $e) {
            error_log("İlçe yöneticileri getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Branş sil
     * @param int $branchId
     * @param int $userId
     * @return bool
     */
    public function deleteBranch($branchId, $userId) {
        try {
            $query = "DELETE FROM branches WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$branchId]);
        } catch (PDOException $e) {
            error_log("Branş silinirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    public function bulkAddSchools($schoolsData, $userId) {
        return $this->schoolModel->bulkAddSchools($schoolsData, $userId);
    }
    
    public function updateSchoolStatus($schoolId, $status) {
        return $this->schoolModel->updateSchoolStatus($schoolId, $status);
    }

    public function getBranches() {
        return $this->schoolModel->getBranches();
    }
}