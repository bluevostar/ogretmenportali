<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * İstatistik işlemleri için merkezi model
 */
class StatsModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Başvuru istatistiklerini getir
     */
    public function getApplicationStats($filters = []) {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
                FROM applications a
            ";
            
            $params = [];
            if (!empty($filters)) {
                $conditions = [];
                foreach ($filters as $field => $value) {
                    $conditions[] = "$field = ?";
                    $params[] = $value;
                }
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $result = $this->fetchOne($query, $params);
            return $result ?: [
                'total_applications' => 0,
                'pending_applications' => 0,
                'approved_applications' => 0,
                'rejected_applications' => 0
            ];
        } catch (PDOException $e) {
            error_log("Başvuru istatistikleri alınırken hata: " . $e->getMessage());
            return [
                'total_applications' => 0,
                'pending_applications' => 0,
                'approved_applications' => 0,
                'rejected_applications' => 0
            ];
        }
    }

    /**
     * Okul istatistiklerini getir 
     */
    public function getSchoolStats($filters = []) {
        try {
            $stats = [];
            
            // Toplam okul sayısı
            $query = "SELECT COUNT(*) as total FROM schools";
            $params = [];
            
            if (!empty($filters)) {
                $conditions = [];
                foreach ($filters as $field => $value) {
                    $conditions[] = "$field = ?";
                    $params[] = $value;
                }
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $result = $this->fetchOne($query, $params);
            $stats['total_schools'] = $result['total'] ?? 0;
            
            // Aktif/Pasif okul sayısı
            $query = "
                SELECT 
                    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_schools,
                    SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive_schools
                FROM schools
            ";
            if (!empty($filters)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $result = $this->fetchOne($query, $params);
            $stats['active_schools'] = $result['active_schools'] ?? 0;
            $stats['inactive_schools'] = $result['inactive_schools'] ?? 0;
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Okul istatistikleri alınırken hata: " . $e->getMessage());
            return [
                'total_schools' => 0,
                'active_schools' => 0,
                'inactive_schools' => 0
            ];
        }
    }

    /**
     * Branş istatistiklerini getir
     */
    public function getBranchStats() {
        try {
            // Toplam branş sayısı
            $query = "SELECT COUNT(*) as total FROM branches";
            $result = $this->fetchOne($query);
            $stats['total_branches'] = $result['total'] ?? 0;
            
            // Branşlara göre başvuru sayıları
            $query = "
                SELECT 
                    b.id,
                    b.name,
                    COUNT(a.id) as application_count
                FROM branches b
                LEFT JOIN applications a ON b.id = a.branch_id
                GROUP BY b.id, b.name
                ORDER BY application_count DESC
            ";
            
            $stats['branch_applications'] = $this->fetchAll($query) ?: [];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Branş istatistikleri alınırken hata: " . $e->getMessage());
            return [
                'total_branches' => 0,
                'branch_applications' => []
            ];
        }
    }
}
