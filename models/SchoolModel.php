<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Okullarla ilgili veritabanı işlemleri
 */
class SchoolModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'schools');
    }
    
    /**
     * Aktif okulların listesini getir
     * @return array
     */
    public function getActiveSchools() {
        $query = "SELECT * FROM schools WHERE status = 'active' ORDER BY name";
        return $this->fetchAll($query);
    }
    
    /**
     * Belirli bir okul için açık pozisyonları getir
     * @param int $schoolId Okul ID
     * @return array
     */
    public function getOpenPositions($schoolId) {
        $query = "
            SELECT p.*, b.name as branch_name 
            FROM school_positions p
            JOIN branches b ON p.branch_id = b.id
            WHERE p.school_id = ? AND p.status = 'open'
            ORDER BY p.created_at DESC
        ";
        return $this->fetchAll($query, [$schoolId]);
    }
    
    /**
     * Okul detaylarını getir
     * @param int $schoolId Okul ID
     * @return array|bool
     */
    public function getSchoolDetails($schoolId) {
        $query = "SELECT * FROM schools WHERE id = ?";
        return $this->fetchOne($query, [$schoolId]);
    }
    
    /**
     * Okul yöneticilerini getir
     * @param int $schoolId Okul ID
     * @return array
     */
    public function getSchoolAdmins($schoolId) {
        $query = "
            SELECT u.* 
            FROM users u
            JOIN school_admins sa ON u.id = sa.user_id
            WHERE sa.school_id = ? AND u.role = 'school_admin' AND u.status = 'active'
            ORDER BY u.name
        ";
        return $this->fetchAll($query, [$schoolId]);
    }
    
    /**
     * Okul için başvuru istatistiklerini getir
     * @param int $schoolId Okul ID
     * @return array
     */
    public function getApplicationStats($schoolId) {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM applications
            WHERE school_id = ?
        ";
        return $this->fetchOne($query, [$schoolId]);
    }
    
    /**
     * Okul için branş bazlı başvuru istatistiklerini getir
     * @param int $schoolId Okul ID
     * @return array
     */
    public function getBranchApplicationStats($schoolId) {
        $query = "
            SELECT 
                b.name as branch_name,
                COUNT(*) as total,
                SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM applications a
            JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
            GROUP BY a.branch_id
            ORDER BY total DESC
        ";
        return $this->fetchAll($query, [$schoolId]);
    }
    
    /**
     * Okul için başvuruları getir
     * @param int $schoolId Okul ID
     * @param string $status Başvuru durumu (isteğe bağlı)
     * @param int $limit Maksimum kayıt sayısı (isteğe bağlı)
     * @param int $offset Başlangıç kaydı (isteğe bağlı)
     * @return array
     */
    public function getSchoolApplications($schoolId, $status = null, $limit = null, $offset = null) {
        $params = [$schoolId];
        $query = "
            SELECT a.*, u.name as teacher_name, u.email as teacher_email, b.name as branch_name
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
        ";
        
        if ($status) {
            $query .= " AND a.status = ?";
            $params[] = $status;
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
     * Okul yöneticisine bağlı okul ID bilgisini getir
     * @param int $userId Kullanıcı ID
     * @return int|null
     */
    public function getSchoolIdForAdmin($userId) {
        $query = "SELECT school_id FROM users WHERE id = ? AND role = 'school_admin'";
        $result = $this->fetchOne($query, [$userId]);
        return $result ? (int)$result['school_id'] : null;
    }

    /**
     * Okula ait son başvuruları getir
     * @param int $schoolId Okul ID
     * @param int $limit Kayıt limiti
     * @return array
     */
    public function getRecentApplications($schoolId, $limit = 5) {
        $query = "
            SELECT a.*, u.name as teacher_name, u.email, u.profile_image, b.name as branch
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
            ORDER BY a.application_date DESC
            LIMIT ?
        ";

        return $this->fetchAll($query, [$schoolId, (int)$limit]);
    }

    /**
     * Okula ait branş bazlı başvuru sayılarını getir
     * @param int $schoolId Okul ID
     * @return array
     */
    public function getApplicationsByBranch($schoolId) {
        $query = "
            SELECT b.name, COUNT(*) as count
            FROM applications a
            JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
            GROUP BY b.id, b.name
            ORDER BY count DESC
        ";

        return $this->fetchAll($query, [$schoolId]);
    }

    /**
     * Okula ait tek başvuru detayını getir
     * @param int $applicationId Başvuru ID
     * @param int $schoolId Okul ID
     * @return array|bool
     */
    public function getApplicationForSchool($applicationId, $schoolId) {
        $query = "
            SELECT a.*, u.name as teacher_name, u.email, u.phone, u.profile_image, 
                   b.name as branch, t.experience, t.education, t.cv_file
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN branches b ON a.branch_id = b.id
            LEFT JOIN teacher_profiles t ON u.id = t.user_id
            WHERE a.id = ? AND a.school_id = ?
        ";

        return $this->fetchOne($query, [$applicationId, $schoolId]);
    }

    /**
     * Okula ait başvuruları filtreleyerek getir
     * @param int $schoolId Okul ID
     * @param string $status Durum filtresi
     * @param string $branch Branş filtresi
     * @param string $search Metin arama filtresi
     * @return array
     */
    public function getFilteredApplications($schoolId, $status = '', $branch = '', $search = '') {
        $query = "
            SELECT a.*, u.name as teacher_name, u.email, u.profile_image, b.name as branch
            FROM applications a
            JOIN users u ON a.teacher_id = u.id
            LEFT JOIN branches b ON a.branch_id = b.id
            WHERE a.school_id = ?
        ";

        $params = [$schoolId];

        if ($status) {
            $query .= " AND a.status = ?";
            $params[] = $status;
        }

        if ($branch) {
            $query .= " AND a.branch_id = ?";
            $params[] = $branch;
        }

        if ($search) {
            $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $likeSearch = "%{$search}%";
            $params[] = $likeSearch;
            $params[] = $likeSearch;
        }

        $query .= " ORDER BY a.application_date DESC";

        return $this->fetchAll($query, $params);
    }

    public function updateSchoolStatus($schoolId, $status)
    {
        $stmt = $this->db->prepare("UPDATE schools SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $schoolId]);
    }

    /**
     * Toplu okul ekleme
     * @param array $schoolsData Okul verileri dizisi
     * @param int $userId İşlemi yapan kullanıcı ID
     * @return array İşlem sonucu
     */
    public function bulkAddSchools($schoolsData, $userId) {
        try {
            $this->db->beginTransaction();
            
            $successCount = 0;
            $failedCount = 0;
            $errors = [];
            
            foreach ($schoolsData as $index => $school) {
                try {
                    // Aynı kurum koduna sahip okul var mı kontrol et
                    if (!empty($school['school_code'])) {
                        $checkQuery = "SELECT id FROM schools WHERE school_code = ?";
                        $stmt = $this->db->prepare($checkQuery);
                        $stmt->execute([$school['school_code']]);
                        if ($stmt->fetch()) {
                            $failedCount++;
                            $errors[] = "Satır " . ($index + 2) . ": Kurum kodu '{$school['school_code']}' zaten mevcut.";
                            continue;
                        }
                    }
                    
                    // Okul ekle
                    $query = "INSERT INTO schools (school_code, name, school_type, city, county, address, phone, email, description, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        $school['school_code'] ?? null,
                        $school['name'] ?? '',
                        $school['school_type'] ?? 'Belirtilmemiş',
                        $school['city'] ?? null,
                        $school['county'] ?? null,
                        $school['address'] ?? null,
                        $school['phone'] ?? null,
                        $school['email'] ?? null,
                        $school['description'] ?? null
                    ]);
                    
                    $successCount++;
                } catch (PDOException $e) {
                    $failedCount++;
                    $schoolName = $school['name'] ?? 'Bilinmeyen';
                    $errors[] = "Satır " . ($index + 2) . " ({$schoolName}): " . $e->getMessage();
                    error_log("Bulk add school error: " . $e->getMessage());
                }
            }
            
            $this->db->commit();
            
            $message = "$successCount okul başarıyla eklendi.";
            if ($failedCount > 0) {
                $message .= " $failedCount okul eklenemedi.";
            }
            
            return [
                'success' => $successCount > 0,
                'message' => $message,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => array_slice($errors, 0, 10) // İlk 10 hatayı göster
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Bulk add schools transaction error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'İşlem sırasında bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }
} 