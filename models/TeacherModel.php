<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Öğretmenlerle ilgili veritabanı işlemleri
 */
class TeacherModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'users');
    }
    
    /**
     * Öğretmen profil bilgilerini getir
     * @param int $teacherId Öğretmen ID
     * @return array|bool
     */
    public function getTeacherProfile($teacherId) {
        $query = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
        return $this->fetchOne($query, [$teacherId]);
    }
    
    /**
     * Öğretmen detaylı profilini getir (users ve teacher_profiles tablosundan)
     * @param int $teacherId Öğretmen ID
     * @return array
     */
    public function getTeacherDetailedProfile($teacherId) {
        try {
            // Öğretmen verisini users tablosundan al
            $query = "SELECT * FROM users WHERE id = ? AND role = 'teacher'";
            $userData = $this->fetchOne($query, [$teacherId]);
            
            if (!$userData) {
                return [];
            }
            
            return $userData;
        } catch (Exception $e) {
            error_log("TeacherModel::getTeacherDetailedProfile error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Öğretmen profil bilgilerini güncelle
     * @param int $teacherId Öğretmen ID
     * @param array $data Güncellenecek veriler
     * @return bool
     */
    public function updateTeacherProfile($teacherId, $data) {
        return $this->update($teacherId, $data);
    }
    
    /**
     * Öğretmenin deneyimlerini getir
     * @param int $teacherId Öğretmen ID
     * @return array
     */
    public function getTeacherExperiences($teacherId) {
        $query = "SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC";
        $result = $this->fetchAll($query, [$teacherId]);
        return is_array($result) ? $result : [];
    }
    
    /**
     * Öğretmenin eğitim bilgilerini getir
     * @param int $teacherId Öğretmen ID
     * @return array
     */
    public function getTeacherEducation($teacherId) {
        $query = "SELECT * FROM education WHERE user_id = ? ORDER BY start_date DESC";
        $result = $this->fetchAll($query, [$teacherId]);
        return is_array($result) ? $result : [];
    }
    
    /**
     * Öğretmenin yeteneklerini getir
     * @param int $teacherId Öğretmen ID
     * @return array
     */
    public function getTeacherSkills($teacherId) {
        $query = "SELECT * FROM teacher_skills WHERE user_id = ? ORDER BY level DESC";
        $result = $this->fetchAll($query, [$teacherId]);
        return is_array($result) ? $result : [];
    }
    
    /**
     * Öğretmenin başvurularını getir
     * @param int $teacherId Öğretmen ID
     * @return array
     */
    public function getTeacherApplications($teacherId) {
        $query = "
            SELECT a.*, s.name as school_name, s.logo as school_logo
            FROM applications a
            JOIN schools s ON a.school_id = s.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
        ";
        $result = $this->fetchAll($query, [$teacherId]);
        return is_array($result) ? $result : [];
    }
    
    /**
     * Yeni başvuru oluştur
     * @param array $applicationData Başvuru verileri
     * @return int|bool Başvuru ID veya false
     */
    public function createApplication($applicationData) {
        $table = 'applications';
        
        // Tablo parametresi ile insert metodu çalışmıyor, ayrı bir metot kullanmamız gerekiyor
        try {
            $fields = array_keys($applicationData);
            $values = array_values($applicationData);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $query = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Başvuru eklenirken hata oluştu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Branş listesini getir
     * @return array
     */
    public function getBranches() {
        $query = "SELECT * FROM branches ORDER BY name";
        $result = $this->fetchAll($query);
        return is_array($result) ? $result : [];
    }
    
    /**
     * Branş adını getir
     * @param int $branchId Branş ID
     * @return string|null
     */
    public function getBranchName($branchId) {
        $query = "SELECT name FROM branches WHERE id = ?";
        $result = $this->fetchOne($query, [$branchId]);
        return $result ? $result['name'] : null;
    }
    
    /**
     * Kapak fotoğrafı rengini güncelle
     * @param int $teacherId Öğretmen ID
     * @param string $color Renk
     * @return bool
     */
    public function updateCoverColor($teacherId, $color) {
        // Önce cover_color alanının var olup olmadığını kontrol et
        try {
            $query = "ALTER TABLE users ADD COLUMN cover_color VARCHAR(50) DEFAULT NULL";
            $this->db->exec($query);
        } catch (PDOException $e) {
            // Kolon zaten varsa hata almayı görmezden gel
            if ($e->getCode() !== '42S21') { // Column already exists
                error_log("Cover color column add error: " . $e->getMessage());
            }
        }
        
        $data = ['cover_color' => $color];
        return $this->update($teacherId, $data);
    }
} 