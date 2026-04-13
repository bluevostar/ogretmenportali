<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Başvurularla ilgili veritabanı işlemleri
 */
class ApplicationModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'applications');
    }
    
    /**
     * Tüm başvuruları getir
     * @param array $filters Filtreleme kriterleri
     * @param int $limit Maksimum kayıt sayısı
     * @param int $offset Başlangıç kaydı
     * @return array
     */
    public function getAllApplications($filters = [], $limit = null, $offset = null) {
        $params = [];
        $query = "
            SELECT a.*, 
                   u.name as teacher_name, 
                   s.name as school_name, 
                   b.name as branch_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
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
        
        if (isset($filters['teacher_id']) && $filters['teacher_id']) {
            $query .= " AND a.teacher_id = ?";
            $params[] = $filters['teacher_id'];
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
        
        if ($limit !== null) {
            $query .= " LIMIT ?";
            $params[] = (int)$limit;
            
            if ($offset !== null) {
                $query .= " OFFSET ?";
                $params[] = (int)$offset;
            }
        }
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Başvuru detayını getir
     * @param int $applicationId Başvuru ID
     * @return array|bool
     */
    public function getApplicationDetail($applicationId) {
        $query = "
            SELECT a.*, 
                   u.name as teacher_name, 
                   u.email as teacher_email,
                   s.name as school_name, 
                   b.name as branch_name,
                   tp.cv_file,
                   tp.education,
                   tp.experience,
                   tp.skills,
                   tp.about,
                   admin.name as approved_by_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            LEFT JOIN users admin ON a.approved_by = admin.id
            WHERE a.id = ?
        ";
        
        return $this->fetchOne($query, [$applicationId]);
    }
    
    /**
     * Öğretmenin başvurularını getir
     * @param int $teacherId Öğretmen ID
     * @param string $status Başvuru durumu (isteğe bağlı)
     * @return array
     */
    public function getTeacherApplications($teacherId, $status = null) {
        $params = [$teacherId];
        $query = "
            SELECT a.*, 
                   s.name as school_name, 
                   s.address as school_address,
                   b.name as branch_name
            FROM applications a
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            WHERE a.teacher_id = ?
        ";
        
        if ($status) {
            $query .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY a.application_date DESC";
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Okulun başvurularını getir
     * @param int $schoolId Okul ID
     * @param string $status Başvuru durumu (isteğe bağlı)
     * @param int $branchId Branş ID (isteğe bağlı)
     * @return array
     */
    public function getSchoolApplications($schoolId, $status = null, $branchId = null) {
        $params = [$schoolId];
        $query = "
            SELECT a.*, 
                   u.name as teacher_name, 
                   u.email as teacher_email,
                   b.name as branch_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
        ";
        
        if ($status) {
            $query .= " AND a.status = ?";
            $params[] = $status;
        }
        
        if ($branchId) {
            $query .= " AND a.branch_id = ?";
            $params[] = $branchId;
        }
        
        $query .= " ORDER BY a.application_date DESC";
        
        return $this->fetchAll($query, $params);
    }
    
    /**
     * Başvuru durumunu güncelle
     * @param int $applicationId Başvuru ID
     * @param string $status Yeni durum
     * @param int $approvedBy Onaylayan kullanıcı ID
     * @param string $notes Notlar (isteğe bağlı)
     * @param string $rejectionReason Reddetme nedeni (isteğe bağlı)
     * @return bool
     */
    public function updateApplicationStatus($applicationId, $status, $approvedBy, $notes = null, $rejectionReason = null) {
        $data = [
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        if ($status === 'rejected' && $rejectionReason) {
            $data['rejection_reason'] = $rejectionReason;
        }
        
        return $this->update($applicationId, $data);
    }
    
    /**
     * Yeni başvuru oluştur
     * @param array $data Başvuru verileri
     * @return int|bool Başvuru ID veya false
     */
    public function createApplication($data) {
        // Zorunlu alanların kontrolü
        $requiredFields = ['teacher_id', 'school_id', 'branch_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        
        // Temel başvuru verileri
        $applicationData = [
            'teacher_id' => $data['teacher_id'],
            'school_id' => $data['school_id'],
            'branch_id' => $data['branch_id'],
            'status' => 'pending',
            'application_date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // İsteğe bağlı alanlar
        if (isset($data['notes'])) {
            $applicationData['notes'] = $data['notes'];
        }
        
        return $this->create($applicationData);
    }
    
    /**
     * Başvuru istatistiklerini getir
     * @return array
     */
    public function getApplicationStats() {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM applications
        ";
        
        return $this->fetchOne($query);
    }
    
    /**
     * Branş bazlı başvuru istatistiklerini getir
     * @return array
     */
    public function getBranchApplicationStats() {
        $query = "
            SELECT 
                b.name as branch_name,
                COUNT(a.id) as total,
                SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM branches b
            LEFT JOIN applications a ON b.id = a.branch_id
            GROUP BY b.id
            ORDER BY total DESC
        ";
        
        return $this->fetchAll($query);
    }
    
    /**
     * Son eklenen başvuruları getir
     * @param int $limit Maksimum kayıt sayısı
     * @return array
     */
    public function getRecentApplications($limit = 10) {
        $query = "
            SELECT a.*, 
                   u.name as teacher_name, 
                   s.name as school_name, 
                   b.name as branch_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN schools s ON a.school_id = s.id
            JOIN branches b ON a.branch_id = b.id
            ORDER BY a.application_date DESC
            LIMIT ?
        ";
        
        return $this->fetchAll($query, [$limit]);
    }
} 