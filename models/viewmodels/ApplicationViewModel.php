<?php
require_once dirname(__DIR__) . '/BaseViewModel.php';
require_once dirname(__DIR__) . '/ApplicationModel.php';
require_once dirname(__DIR__) . '/TeacherModel.php';
require_once dirname(__DIR__) . '/SchoolModel.php';

/**
 * Başvurularla ilgili işlemleri yöneten ViewModel sınıfı
 */
class ApplicationViewModel extends BaseViewModel {
    private $applicationModel;
    private $teacherModel;
    private $schoolModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->applicationModel = new ApplicationModel($db);
        $this->teacherModel = new TeacherModel($db);
        $this->schoolModel = new SchoolModel($db);
    }
    
    /**
     * View için verileri hazırla
     * @param array $params View için gerekli parametreler
     * @return array View'a iletilecek veriler
     */
    public function prepareViewData($params = []) {
        $action = $params['action'] ?? 'list';
        $this->addViewData('action', $action);
        
        switch ($action) {
            case 'list':
                $filters = $params['filters'] ?? [];
                $applications = $this->applicationModel->getAllApplications($filters);
                $stats = $this->applicationModel->getApplicationStats();
                $branchStats = $this->applicationModel->getBranchApplicationStats();
                
                $this->addViewData('applications', $applications);
                $this->addViewData('stats', $stats);
                $this->addViewData('branchStats', $branchStats);
                $this->addViewData('filters', $filters);
                break;
                
            case 'detail':
                $applicationId = $params['application_id'] ?? 0;
                if (!$applicationId) {
                    return $this->createError('Başvuru ID bulunamadı.');
                }
                
                $application = $this->applicationModel->getApplicationDetail($applicationId);
                if (!$application) {
                    return $this->createError('Başvuru bulunamadı.');
                }
                
                // Öğretmen deneyimleri ve eğitim bilgilerini de getir
                $teacherId = $application['teacher_id'];
                $experiences = $this->teacherModel->getTeacherExperiences($teacherId);
                $education = $this->teacherModel->getTeacherEducation($teacherId);
                
                $this->addViewData('application', $application);
                $this->addViewData('experiences', $experiences);
                $this->addViewData('education', $education);
                break;
                
            case 'teacher':
                $teacherId = $params['teacher_id'] ?? $this->getCurrentUserId();
                if (!$teacherId) {
                    return $this->createError('Öğretmen ID bulunamadı.');
                }
                
                $applications = $this->applicationModel->getTeacherApplications($teacherId);
                $this->addViewData('applications', $applications);
                break;
                
            case 'school':
                $schoolId = $params['school_id'] ?? 0;
                if (!$schoolId) {
                    return $this->createError('Okul ID bulunamadı.');
                }
                
                $status = $params['status'] ?? null;
                $branchId = $params['branch_id'] ?? null;
                
                $applications = $this->applicationModel->getSchoolApplications($schoolId, $status, $branchId);
                $school = $this->schoolModel->getSchoolDetails($schoolId);
                
                $this->addViewData('applications', $applications);
                $this->addViewData('school', $school);
                $this->addViewData('status', $status);
                $this->addViewData('branch_id', $branchId);
                break;
                
            case 'apply':
                $schools = $this->schoolModel->getActiveSchools();
                $branches = $this->teacherModel->getBranches();
                
                $this->addViewData('schools', $schools);
                $this->addViewData('branches', $branches);
                break;
        }
        
        return parent::prepareViewData();
    }
    
    /**
     * Kullanıcı girdilerini işle
     * @param array $input Kullanıcı girdileri
     * @return mixed İşlem sonucu
     */
    public function processInput($input = []) {
        $action = $input['action'] ?? '';
        $userId = $this->getCurrentUserId();
        
        if (!$userId) {
            return $this->createError('Oturum zaman aşımına uğradı veya giriş yapılmadı.');
        }
        
        switch ($action) {
            case 'create_application':
                return $this->createApplication($input, $userId);
                
            case 'update_status':
                return $this->updateApplicationStatus($input, $userId);
                
            case 'cancel_application':
                return $this->cancelApplication($input, $userId);
                
            default:
                return $this->createError('Geçersiz işlem.');
        }
    }
    
    /**
     * Yeni başvuru oluştur
     * @param array $input Form verileri
     * @param int $userId Kullanıcı ID
     * @return array İşlem sonucu
     */
    private function createApplication($input, $userId) {
        $required = ['school_id', 'branch_id'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // Role kontrolü
        if (!$this->checkUserRole($userId, 'teacher')) {
            return $this->createError('Sadece öğretmen rolündeki kullanıcılar başvuru yapabilir.');
        }
        
        // Zaten başvuru var mı kontrolü
        $existingApplication = $this->applicationModel->getOne([
            'teacher_id' => $userId,
            'school_id' => $input['school_id'],
            'branch_id' => $input['branch_id'],
            'status' => 'pending'
        ]);
        
        if ($existingApplication) {
            return $this->createError('Bu okula ve branşa zaten bekleyen bir başvurunuz bulunmaktadır.');
        }
        
        $data = [
            'teacher_id' => $userId,
            'school_id' => $input['school_id'],
            'branch_id' => $input['branch_id'],
            'notes' => $input['notes'] ?? ''
        ];
        
        $result = $this->applicationModel->createApplication($data);
        
        if ($result) {
            // Okul yöneticilerine bildirim gönder
            $this->notifySchoolAdmins($input['school_id'], 'new_application', 'Yeni bir başvuru yapıldı.');
            
            return $this->createSuccess('Başvurunuz başarıyla alındı.');
        } else {
            return $this->createError('Başvuru yapılırken bir hata oluştu.');
        }
    }
    
    /**
     * Başvuru durumunu güncelle
     * @param array $input Form verileri
     * @param int $userId Kullanıcı ID
     * @return array İşlem sonucu
     */
    private function updateApplicationStatus($input, $userId) {
        $applicationId = $input['application_id'] ?? 0;
        if (!$applicationId) {
            return $this->createError('Başvuru ID bulunamadı.');
        }
        
        $status = $input['status'] ?? '';
        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->createError('Geçersiz başvuru durumu.');
        }
        
        // Başvuru bilgilerini al
        $application = $this->applicationModel->getById($applicationId);
        if (!$application) {
            return $this->createError('Başvuru bulunamadı.');
        }
        
        // Yetki kontrolü
        if (!$this->checkUserRole($userId, 'admin') && !$this->isSchoolAdmin($userId, $application['school_id'])) {
            return $this->createError('Bu işlem için yetkiniz yok.');
        }
        
        $notes = $input['notes'] ?? null;
        $rejectionReason = $input['rejection_reason'] ?? null;
        
        $result = $this->applicationModel->updateApplicationStatus(
            $applicationId, 
            $status, 
            $userId, 
            $notes, 
            $rejectionReason
        );
        
        if ($result) {
            // Öğretmene bildirim gönder
            $this->createNotification(
                $application['teacher_id'], 
                'application_status', 
                'Başvurunuzun durumu güncellendi: ' . ($status === 'approved' ? 'Onaylandı' : 'Reddedildi'), 
                $applicationId
            );
            
            return $this->createSuccess('Başvuru durumu başarıyla güncellendi.');
        } else {
            return $this->createError('Başvuru durumu güncellenirken bir hata oluştu.');
        }
    }
    
    /**
     * Başvuruyu iptal et
     * @param array $input Form verileri
     * @param int $userId Kullanıcı ID
     * @return array İşlem sonucu
     */
    private function cancelApplication($input, $userId) {
        $applicationId = $input['application_id'] ?? 0;
        if (!$applicationId) {
            return $this->createError('Başvuru ID bulunamadı.');
        }
        
        // Başvuru bilgilerini al
        $application = $this->applicationModel->getById($applicationId);
        if (!$application) {
            return $this->createError('Başvuru bulunamadı.');
        }
        
        // Yetki kontrolü
        if ($application['teacher_id'] != $userId && !$this->checkUserRole($userId, 'admin')) {
            return $this->createError('Bu işlem için yetkiniz yok.');
        }
        
        // Sadece bekleyen başvurular iptal edilebilir
        if ($application['status'] !== 'pending') {
            return $this->createError('Sadece bekleyen başvurular iptal edilebilir.');
        }
        
        $result = $this->applicationModel->delete($applicationId);
        
        if ($result) {
            return $this->createSuccess('Başvurunuz başarıyla iptal edildi.');
        } else {
            return $this->createError('Başvuru iptal edilirken bir hata oluştu.');
        }
    }
    
    /**
     * Okul yöneticilerine bildirim gönder
     * @param int $schoolId Okul ID
     * @param string $type Bildirim tipi
     * @param string $message Bildirim mesajı
     * @param int $referenceId Referans ID (isteğe bağlı)
     * @return bool
     */
    private function notifySchoolAdmins($schoolId, $type, $message, $referenceId = null) {
        $admins = $this->schoolModel->getSchoolAdmins($schoolId);
        
        foreach ($admins as $admin) {
            $this->createNotification($admin['id'], $type, $message, $referenceId);
        }
        
        return true;
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
        
        return $this->applicationModel->create($data, 'notifications');
    }
    
    /**
     * Kullanıcının rolünü kontrol et
     * @param int $userId Kullanıcı ID
     * @param string $role Kontrol edilecek rol
     * @return bool
     */
    private function checkUserRole($userId, $role) {
        $query = "SELECT role FROM users WHERE id = ?";
        $result = $this->applicationModel->fetchOne($query, [$userId]);
        return $result && $result['role'] === $role;
    }
    
    /**
     * Kullanıcının okul yöneticisi olup olmadığını kontrol et
     * @param int $userId Kullanıcı ID
     * @param int $schoolId Okul ID
     * @return bool
     */
    private function isSchoolAdmin($userId, $schoolId) {
        $query = "
            SELECT 1 FROM school_admins 
            WHERE user_id = ? AND school_id = ?
        ";
        $result = $this->applicationModel->fetchOne($query, [$userId, $schoolId]);
        return $result ? true : false;
    }
    
    /**
     * Yetkilendirme kontrolü
     * @param array $params Kontrol parametreleri
     * @return bool Yetkili mi?
     */
    public function checkAuthorization($params = []) {
        return $this->checkSession();
    }

    // Merkezi başvuru yönetimi metodları
    public function getApplicationsBySchool($schoolId, $status = null, $search = '') {
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
                WHERE a.school_id = ?
            ";
            $params = [$schoolId];
            
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
            error_log("Okul başvuruları getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    public function getApplicationsByCountryAdmin($countryAdminId, $status = null, $search = '') {
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
            error_log("İlçe başvuruları getirilirken hata oluştu: " . $e->getMessage());
            return [];
        }
    }

    // Diğer başvuru yönetimi metodları buraya eklenecek
}