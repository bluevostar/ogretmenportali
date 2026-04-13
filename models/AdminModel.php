<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Admin panel işlemleri için veritabanı işlemleri
 */
class AdminModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'users');
    }
    
    /**
     * Kullanıcıları listele
     * @param array $filters Filtreleme kriterleri
     * @param int $limit Maksimum kayıt sayısı
     * @param bool $countOnly Sadece sayıyı döndür
     * @return array|int
     */
    public function getUsers($filters = [], $limit = null, $countOnly = false) {
        try {
            $params = [];
            $schoolTypeFilter = isset($filters['school_type']) ? trim((string)$filters['school_type']) : '';
            $useSchoolTypeFilter = $schoolTypeFilter !== '' && $this->tableExists('school_type_branch_map');
            $applicationsTableExists = $this->tableExists('applications');
            $applicationsOwnerColumn = null;
            if ($applicationsTableExists) {
                if ($this->columnExists('applications', 'teacher_id')) {
                    $applicationsOwnerColumn = 'teacher_id';
                } elseif ($this->columnExists('applications', 'user_id')) {
                    $applicationsOwnerColumn = 'user_id';
                }
            }
            
            if ($countOnly) {
                $query = "SELECT COUNT(*) as count
                          FROM users u
                          LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                          WHERE 1=1";
            } else {
                $latestApplicationJoin = '';
                if ($applicationsOwnerColumn !== null) {
                    $latestApplicationJoin = "
                    LEFT JOIN (
                        SELECT a.{$applicationsOwnerColumn} AS owner_id, a.branch_id
                        FROM applications a
                        INNER JOIN (
                            SELECT {$applicationsOwnerColumn} AS owner_id, MAX(id) AS latest_application_id
                            FROM applications
                            GROUP BY {$applicationsOwnerColumn}
                        ) latest_app ON latest_app.latest_application_id = a.id
                    ) la ON la.owner_id = u.id
                    LEFT JOIN branches ab ON ab.id = la.branch_id";
                }

                $query = "
                    SELECT 
                        u.*, 
                        (u.status = 'active') as active,
                        -- Teacher profile alanlari (varsa)
                        tp.graduation_level AS graduation,
                        tp.institution_name AS university_name,
                        tp.department AS department_name,
                        s.name AS assigned_school_name,
                        b.name AS branch_name,
                        COALESCE(ab.name, b.name) AS applied_branch_name
                    FROM users u
                    LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                    LEFT JOIN schools s ON s.id = tp.assigned_school_id
                    LEFT JOIN branches b ON b.id = tp.application_branch_id
                    {$latestApplicationJoin}
                    WHERE 1=1
                ";
            }
            
            if (isset($filters['role']) && $filters['role']) {
                $query .= " AND u.role = ?";
                $params[] = $filters['role'];
            }
            
            if (isset($filters['status']) && $filters['status']) {
                $query .= " AND u.status = ?";
                $params[] = $filters['status'];
            }

            if ($useSchoolTypeFilter) {
                $query .= " AND u.role = 'teacher'
                            AND EXISTS (
                                SELECT 1
                                FROM school_type_branch_map stbm
                                WHERE stbm.branch_id = tp.application_branch_id
                                  AND stbm.school_type = ?
                                  AND stbm.is_active = 1
                            )";
                $params[] = $schoolTypeFilter;
            }
            
            if (isset($filters['search']) && $filters['search']) {
                $query .= " AND (u.name LIKE ?
                                 OR u.surname LIKE ?
                                 OR CONCAT(u.name, ' ', IFNULL(u.surname, '')) LIKE ?
                                 OR u.email LIKE ?
                                 OR u.phone LIKE ?
                                 OR u.city LIKE ?
                                 OR u.county LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!$countOnly) {
                $query .= " ORDER BY u.name";
                
                if ($limit !== null) {
                    $query .= " LIMIT ?";
                    $params[] = (int)$limit;
                    
                    if (isset($filters['offset']) && $filters['offset'] !== null) {
                        $query .= " OFFSET ?";
                        $params[] = (int)$filters['offset'];
                    }
                }
            }
            
            if ($countOnly) {
                $result = $this->fetchOne($query, $params);
                return $result['count'] ?? 0;
            }
            
            $result = $this->fetchAll($query, $params);
            
            return $result !== false ? $result : [];
        } catch (PDOException $e) {
            error_log("getUsers hatası: " . $e->getMessage());
            error_log("SQL sorgusu: " . $query);
            return [];
        } catch (Exception $e) {
            error_log("getUsers genel hata: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sistemde tanımlı okul türlerini getir.
     * @return array
     */
    public function getSchoolTypes() {
        $query = "SELECT DISTINCT school_type
                  FROM schools
                  WHERE school_type IS NOT NULL
                    AND TRIM(school_type) <> ''
                  ORDER BY school_type ASC";
        return $this->fetchAll($query) ?: [];
    }

    /**
     * Bir tablonun veritabanında var olup olmadığını kontrol et.
     * @param string $tableName
     * @return bool
     */
    private function tableExists($tableName) {
        $query = "SELECT COUNT(*) AS c
                  FROM information_schema.tables
                  WHERE table_schema = DATABASE()
                    AND table_name = ?";
        $result = $this->fetchOne($query, [$tableName]);
        return isset($result['c']) && (int)$result['c'] > 0;
    }

    /**
     * Bir tabloda belirtilen sütunun varlığını kontrol et.
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    private function columnExists($tableName, $columnName) {
        $query = "SELECT COUNT(*) AS c
                  FROM information_schema.columns
                  WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND column_name = ?";
        $result = $this->fetchOne($query, [$tableName, $columnName]);
        return isset($result['c']) && (int)$result['c'] > 0;
    }
    
    /**
     * Kullanıcı sayılarını rol bazlı getir
     * @return array
     */
    public function getUserCountsByRole() {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN role = 'school_admin' THEN 1 ELSE 0 END) as school_admin_count,
                SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teacher_count
            FROM users
        ";
        
        return $this->fetchOne($query);
    }
    
    /**
     * Okul yöneticilerini ve okullarını getir
     * @param array $filters Filtreleme kriterleri (search, status vb.)
     * @return array
     */
    public function getSchoolAdminsWithSchools($filters = []) {
        $params = [];
        $query = "
            SELECT u.*, 
                   s.id as school_id, 
                   s.name as school_name,
                   s.school_code as school_code,
                   COALESCE(s.city, u.city) as school_city,
                   COALESCE(s.county, u.county) as school_county
            FROM users u
            LEFT JOIN school_admins sa ON u.id = sa.user_id
            LEFT JOIN schools s ON sa.school_id = s.id
            WHERE u.role = 'school_admin'
        ";
        
        if (isset($filters['search']) && $filters['search']) {
            $query .= " AND (u.name LIKE ? OR u.surname LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.city LIKE ? OR u.county LIKE ? OR s.name LIKE ? OR s.school_code LIKE ? OR s.city LIKE ? OR s.county LIKE ? OR s.address LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm; // u.name
            $params[] = $searchTerm; // u.surname
            $params[] = $searchTerm; // u.email
            $params[] = $searchTerm; // u.phone
            $params[] = $searchTerm; // u.city
            $params[] = $searchTerm; // u.county
            $params[] = $searchTerm; // s.name
            $params[] = $searchTerm; // s.school_code
            $params[] = $searchTerm; // s.city
            $params[] = $searchTerm; // s.county
            $params[] = $searchTerm; // s.address
            $params[] = $searchTerm; // s.phone
            $params[] = $searchTerm; // s.email
        }
        
        $query .= " ORDER BY u.name";
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Okulları listele
     * @param array $filters Filtreleme kriterleri
     * @return array
     */
    public function getSchools($filters = []) {
        $params = [];
        $query = "SELECT * FROM schools WHERE 1=1";
        
        if (isset($filters['status']) && $filters['status']) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['search']) && $filters['search']) {
            $query .= " AND (name LIKE ? OR school_type LIKE ? OR city LIKE ? OR county LIKE ? OR address LIKE ? OR email LIKE ? OR school_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY name";
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Branşları listele (arama destekli)
     * @param array $filters
     * @return array
     */
    public function getBranches($filters = []) {
        $params = [];
        $query = "SELECT * FROM branches WHERE 1=1";
        
        if (isset($filters['search']) && $filters['search']) {
            $query .= " AND (name LIKE ? OR code LIKE ? OR status LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY name";
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Başvuruları listele
     * @param array $filters Filtreleme kriterleri
     * @return array
     */
    public function getApplications($filters = []) {
        $params = [];
        $query = "
            SELECT a.*, 
                   u.name as teacher_name, 
                   s.name as school_name, 
                   b.name as branch_name,
                   admin.name as approved_by_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            LEFT JOIN users admin ON a.approved_by = admin.id
            WHERE 1=1
        ";
        
        if (isset($filters['status']) && $filters['status']) {
            $query .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['school_id']) && $filters['school_id']) {
            $query .= " AND a.school_id = ?";
            $params[] = $filters['school_id'];
        }
        
        if (isset($filters['branch_id']) && $filters['branch_id']) {
            $query .= " AND a.branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        if (isset($filters['search']) && $filters['search']) {
            $query .= " AND (u.name LIKE ? OR s.name LIKE ? OR b.name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY a.application_date DESC";
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Yeni kullanıcı ekle
     * @param array $data Kullanıcı verileri
     * @return int|bool Kullanıcı ID veya false
     */
    public function createUser($data) {
        // Zorunlu alanların kontrolü
        $requiredFields = ['name', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Şifreyi güvenli şekilde hashle
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Kullanıcı verileri
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Opsiyonel alanları ekle
        if (isset($data['surname']) && !empty($data['surname'])) {
            $userData['surname'] = $data['surname'];
        }
        if (isset($data['phone']) && !empty($data['phone'])) {
            $userData['phone'] = $data['phone'];
        }
        if (isset($data['address']) && !empty($data['address'])) {
            $userData['address'] = $data['address'];
        }
        if (isset($data['tc_kimlik_no']) && !empty($data['tc_kimlik_no'])) {
            $userData['tc_kimlik_no'] = $data['tc_kimlik_no'];
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $userData['city'] = $data['city'];
        }
        if (isset($data['county']) && !empty($data['county'])) {
            $userData['county'] = $data['county'];
        }
        
        // Kullanıcıyı oluştur
        $userId = $this->create($userData);
        
        // Eğer okul yöneticisi ise, okul ilişkisini de kur
        if ($userId && $data['role'] === 'school_admin' && isset($data['school_id'])) {
            $schoolAdminData = [
                'user_id' => $userId,
                'school_id' => $data['school_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->create($schoolAdminData, 'school_admins');
        }
        
        return $userId;
    }
    
    /**
     * Kullanıcı güncelle
     * @param int $userId Kullanıcı ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateUser($userId, $data) {
        try {
            $userData = [];
            
            // Zorunlu alanlar (varsa güncelle - boş string de kabul edilir)
            if (isset($data['name'])) {
                $userData['name'] = trim($data['name']);
            }
            if (isset($data['email'])) {
                $userData['email'] = trim($data['email']);
            }
            if (isset($data['status'])) {
                $userData['status'] = $data['status'];
            }
            if (isset($data['role'])) {
                $userData['role'] = $data['role'];
            }
            
            // Opsiyonel alanları ekle (boş string de kabul edilir - kullanıcı temizlemek isteyebilir)
            if (isset($data['surname'])) {
                $userData['surname'] = trim($data['surname']);
            }
            if (isset($data['phone'])) {
                $userData['phone'] = trim($data['phone']);
            }
            // address alanı kaldırıldı - users tablosunda yok
            if (isset($data['tc_kimlik_no'])) {
                $userData['tc_kimlik_no'] = trim($data['tc_kimlik_no']);
            }
            if (isset($data['city'])) {
                $userData['city'] = trim($data['city']);
            }
            if (isset($data['county'])) {
                $userData['county'] = trim($data['county']);
            }
            if (isset($data['profile_image']) && $data['profile_image'] !== '') {
                $userData['profile_image'] = $data['profile_image'];
            }
            
            // Şifre güncellemesi varsa
            if (isset($data['password']) && !empty(trim($data['password']))) {
                $userData['password'] = password_hash(trim($data['password']), PASSWORD_DEFAULT);
            }
            
            // En az bir alan güncellenmeli
            if (empty($userData)) {
                error_log('updateUser: Güncellenecek alan bulunamadı. Data: ' . print_r($data, true));
                return false;
            }
            
            // updated_at ekle
            $userData['updated_at'] = date('Y-m-d H:i:s');
            
            error_log('updateUser: Güncellenecek veriler - ' . print_r($userData, true));
            error_log('updateUser: User ID - ' . $userId);
            error_log('updateUser: Table - ' . $this->table);
            
            $result = $this->update($userId, $userData);
            
            // Güncelleme sonrası kontrol
            if ($result) {
                $updatedUser = $this->getById($userId);
                error_log('updateUser: Güncelleme başarılı. Güncellenmiş veri - ' . print_r($updatedUser, true));
            } else {
                error_log('updateUser: Güncelleme başarısız oldu');
            }
            
            // Eğer okul yöneticisi ise ve okul güncellemesi varsa
            if ($result && isset($data['role']) && $data['role'] === 'school_admin' && isset($data['school_id'])) {
                // Önce mevcut okul ilişkisini kontrol et
                $query = "SELECT id FROM school_admins WHERE user_id = ?";
                $existingRecord = $this->fetchOne($query, [$userId]);
                
                if ($existingRecord) {
                    // Varsa güncelle
                    $this->update($existingRecord['id'], ['school_id' => $data['school_id']], 'school_admins');
                } else {
                    // Yoksa yeni ilişki oluştur
                    $schoolAdminData = [
                        'user_id' => $userId,
                        'school_id' => $data['school_id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $this->create($schoolAdminData, 'school_admins');
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log('updateUser PDOException: ' . $e->getMessage());
            error_log('updateUser SQL State: ' . $e->getCode());
            error_log('updateUser Error Info: ' . print_r($e->errorInfo(), true));
            return false;
        } catch (Exception $e) {
            error_log('updateUser Exception: ' . $e->getMessage());
            error_log('updateUser Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Yeni okul ekle
     * @param array $data Okul verileri
     * @return int|bool Okul ID veya false
     */
    public function createSchool($data) {
        // Zorunlu alanların kontrolü
        $requiredFields = ['name', 'address', 'email'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Okul verileri
        $schoolData = [
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'description' => $data['description'] ?? null,
            'founded_year' => $data['founded_year'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($schoolData, 'schools');
    }
    
    /**
     * Okul güncelle
     * @param int $schoolId Okul ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateSchool($schoolId, $data) {
        $schoolData = [
            'name' => $data['name'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'description' => $data['description'] ?? null,
            'founded_year' => $data['founded_year'] ?? null,
            'status' => $data['status'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Boş alanları temizle
        $schoolData = array_filter($schoolData, function($value) {
            return $value !== null;
        });
        
        return $this->update($schoolId, $schoolData, 'schools');
    }
    
    /**
     * Yeni branş ekle
     * @param array $data Branş verileri
     * @return int|bool Branş ID veya false
     */
    public function createBranch($data) {
        // Zorunlu alanların kontrolü
        $requiredFields = ['name', 'code'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Branş verileri
        $branchData = [
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($branchData, 'branches');
    }
    
    /**
     * Branş güncelle
     * @param int $branchId Branş ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateBranch($branchId, $data) {
        $branchData = [
            'name' => $data['name'] ?? null,
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Boş alanları temizle
        $branchData = array_filter($branchData, function($value) {
            return $value !== null;
        });
        
        return $this->update($branchId, $branchData, 'branches');
    }
    
    /**
     * Başvuru onay durumunu güncelle
     * @param int $applicationId Başvuru ID
     * @param string $status Yeni durum
     * @param int $adminId Onaylayan admin ID
     * @param string $notes Notlar (isteğe bağlı)
     * @return bool
     */
    public function updateApplicationStatus($applicationId, $status, $adminId, $notes = null) {
        $data = [
            'status' => $status,
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        return $this->update($applicationId, $data, 'applications');
    }
    
    /**
     * Son işlemleri getir (son eklenen kullanıcılar, okullar, başvurular)
     * @param int $limit Maksimum kayıt sayısı
     * @return array
     */
    public function getRecentActivity($limit = 10) {
        $result = [
            'users' => [],
            'schools' => [],
            'applications' => []
        ];
        
        // Son eklenen kullanıcılar
        $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT ?";
        $result['users'] = $this->fetchAll($query, [$limit]);
        
        // Son eklenen okullar
        $query = "SELECT * FROM schools ORDER BY created_at DESC LIMIT ?";
        $result['schools'] = $this->fetchAll($query, [$limit]);
        
        // Son başvurular
        $query = "
            SELECT a.*, u.name as teacher_name, s.name as school_name, b.name as branch_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            ORDER BY a.application_date DESC
            LIMIT ?
        ";
        $result['applications'] = $this->fetchAll($query, [$limit]);
        
        return $result;
    }
    
    /**
     * Excel raporu için başvuruları getir
     * @param array $filters Filtreleme kriterleri
     * @return array
     */
    public function getApplicationsForReport($filters = []) {
        $params = [];
        $query = "
            SELECT 
                a.id,
                u.name as teacher_name,
                u.email as teacher_email,
                s.name as school_name,
                b.name as branch_name,
                a.status,
                a.application_date,
                a.approved_at,
                admin.name as approved_by_name,
                a.notes,
                a.rejection_reason
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            LEFT JOIN users admin ON a.approved_by = admin.id
            WHERE 1=1
        ";
        
        if (isset($filters['status']) && $filters['status']) {
            $query .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['school_id']) && $filters['school_id']) {
            $query .= " AND a.school_id = ?";
            $params[] = $filters['school_id'];
        }
        
        if (isset($filters['branch_id']) && $filters['branch_id']) {
            $query .= " AND a.branch_id = ?";
            $params[] = $filters['branch_id'];
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query .= " AND a.application_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $query .= " AND a.application_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $query .= " ORDER BY a.application_date DESC";
        
        return $this->fetchAll($query, $params);
    }

    public function updateSchoolStatus($schoolId, $status)
    {
        $stmt = $this->db->prepare("UPDATE schools SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $schoolId]);
    }

    public function getSchoolAdminsBySchoolId($schoolId)
    {
        // ... existing code ...
    }

    /**
     * Belirtilen tablodaki kayıt sayısını döndürür.
     * @param string $tableName Tablo adı
     * @return int Kayıt sayısı
     */
    public function count($tableName)
    {
        // BaseModel'deki fetchOne metodunu kullanarak sayımı yap
        $query = "SELECT COUNT(*) as count FROM " . $tableName;
        $result = $this->fetchOne($query);
        return $result['count'] ?? 0;
    }
}