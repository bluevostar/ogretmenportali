<?php
require_once dirname(__DIR__) . '/BaseViewModel.php';
require_once dirname(__DIR__) . '/SchoolModel.php';

class SchoolAdminViewModel extends BaseViewModel {
    private $schoolModel;

    public function __construct($db) {
        parent::__construct($db);
        $this->schoolModel = new SchoolModel($db);
    }
    
    // Dashboard istatistiklerini getir
    public function getDashboardStats() {
        $stats = [
            'total_applications' => 0,
            'pending_applications' => 0,
            'approved_applications' => 0,
            'rejected_applications' => 0
        ];
        
        // Okul yöneticisinin ID'sini al
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return $stats;
        
        $result = $this->schoolModel->getApplicationStats($schoolId);
        
        if ($result) {
            $stats['total_applications'] = $result['total'];
            $stats['pending_applications'] = $result['pending'];
            $stats['approved_applications'] = $result['approved'];
            $stats['rejected_applications'] = $result['rejected'];
        }
        
        return $stats;
    }
    
    // Son başvuruları getir
    public function getRecentApplications($limit = 5) {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return [];
        
        return $this->schoolModel->getRecentApplications($schoolId, $limit);
    }
    
    // Branşlara göre başvuruları getir
    public function getApplicationsByBranch() {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return [];
        
        return $this->schoolModel->getApplicationsByBranch($schoolId);
    }
    
    // Başvuru detayını getir
    public function getApplication($id) {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return null;
        
        return $this->schoolModel->getApplicationForSchool($id, $schoolId);
    }
    
    // Başvuruyu onayla
    public function approveApplication($id) {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return false;
        
        $data = [
            'status' => 'approved',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $where = [
            'id' => $id,
            'school_id' => $schoolId,
            'status' => 'pending'
        ];
        
        $success = $this->update('applications', $data, $where);
        
        if ($success) {
            // Öğretmene bildirim gönder
            $this->sendNotification($id, 'application_approved');
        }
        
        return $success;
    }
    
    // Başvuruyu reddet
    public function rejectApplication($id, $reason) {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return false;
        
        $data = [
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $where = [
            'id' => $id,
            'school_id' => $schoolId,
            'status' => 'pending'
        ];
        
        $success = $this->update('applications', $data, $where);
        
        if ($success) {
            // Öğretmene bildirim gönder
            $this->sendNotification($id, 'application_rejected', $reason);
        }
        
        return $success;
    }
    
    // Başvuruları filtrele ve getir
    public function getApplications($status = '', $branch = '', $search = '') {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return [];
        
        return $this->schoolModel->getFilteredApplications($schoolId, $status, $branch, $search);
    }
    
    // Branşları getir
    public function getBranches() {
        return $this->fetchAll("SELECT * FROM branches ORDER BY name");
    }
    
    // Öğretmenleri filtrele ve getir
    public function getTeachers($branch = '', $status = '', $search = '') {
        $schoolId = $this->getSchoolIdForAdmin($_SESSION['user_id']);
        
        if (!$schoolId) return [];
        
        $query = "
            SELECT u.*, t.experience, t.education, b.name as branch_name
            FROM users u
            LEFT JOIN teacher_profiles t ON u.id = t.user_id
            LEFT JOIN branches b ON t.branch_id = b.id
            WHERE u.role = 'teacher'
        ";
        
        $params = [];
        
        if ($branch) {
            $query .= " AND t.branch_id = ?";
            $params[] = $branch;
        }
        
        if ($status) {
            $query .= " AND u.status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $search = "%$search%";
            $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
        }
        
        $query .= " ORDER BY u.created_at DESC";
        
        return $this->fetchAll($query, $params);
    }
    
    // Okul yöneticisinin ID'sini getir
    private function getSchoolIdForAdmin($userId) {
        return $this->schoolModel->getSchoolIdForAdmin($userId);
    }
    
    // Bildirim gönder
    private function sendNotification($applicationId, $type, $message = '') {
        $application = $this->getApplication($applicationId);
        if (!$application) return false;
        
        $notificationData = [
            'user_id' => $application['teacher_id'],
            'type' => $type,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert('notifications', $notificationData);
    }
    
    // Ayarları getir
    public function getSettings() {
        $userId = $this->getCurrentUserId();
        
        if (!$userId) {
            return [
                'name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'profile_image' => '',
                'email_notifications' => false,
                'application_notifications' => false,
                'system_notifications' => false
            ];
        }
        
        // Kullanıcı bilgilerini getir
        $query = "SELECT * FROM users WHERE id = ?";
        $user = $this->fetchOne($query, [$userId]);
        
        if (!$user) {
            return [
                'name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'profile_image' => '',
                'email_notifications' => false,
                'application_notifications' => false,
                'system_notifications' => false
            ];
        }
        
        // Bildirim ayarlarını getir
        $notificationQuery = "SELECT * FROM user_settings WHERE user_id = ?";
        $notificationSettings = $this->fetchOne($notificationQuery, [$userId]);
        
        // Ayarlar için gerekli bilgileri hazırla
        $settings = [
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'profile_image' => $user['profile_photo'] ?? '',
            'email_notifications' => $notificationSettings['email_notifications'] ?? false,
            'application_notifications' => $notificationSettings['application_notifications'] ?? false,
            'system_notifications' => $notificationSettings['system_notifications'] ?? false
        ];
        
        return $settings;
    }
    
    protected function insert($table, $data) {
        try {
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($data);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
    }
    
    protected function update($table, $data, $where) {
        try {
            $set = [];
            foreach (array_keys($data) as $key) {
                $set[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $set);
            
            $whereClause = [];
            $params = $data;
            foreach ($where as $key => $value) {
                $whereClause[] = "{$key} = :where_{$key}";
                $params["where_{$key}"] = $value;
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $query = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }
}