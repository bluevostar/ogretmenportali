<?php
require_once '../config/db_connect.php';

/**
 * Yeni başvuru ekle
 */
function addApplication($user_id, $school_id, $position, $message = '', $cv_path = null) {
    $conn = getDbConnection();
    
    // Verileri temizle
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $school_id = mysqli_real_escape_string($conn, $school_id);
    $position = mysqli_real_escape_string($conn, $position);
    $message = mysqli_real_escape_string($conn, $message);
    $cv_path = $cv_path ? mysqli_real_escape_string($conn, $cv_path) : null;
    
    // Kullanıcının bilgilerini al
    $user_sql = "SELECT branch FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);
    
    if (!$user_result || mysqli_num_rows($user_result) == 0) {
        return [
            'success' => false,
            'message' => 'Geçersiz kullanıcı.'
        ];
    }
    
    $user_data = mysqli_fetch_assoc($user_result);
    $branch = $user_data['branch'];
    
    if (!$branch) {
        return [
            'success' => false,
            'message' => 'Başvuru yapabilmek için branşınızı belirtmelisiniz.'
        ];
    }
    
    // CV alanı SQL sorgusu için
    $cv_field = $cv_path ? "'$cv_path'" : "NULL";
    
    // Aynı okula daha önce başvuru yapılıp yapılmadığını kontrol et
    $check_sql = "SELECT * FROM applications WHERE user_id = '$user_id' AND school_id = '$school_id' AND status != 'rejected'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        return [
            'success' => false,
            'message' => 'Bu okula daha önce başvuru yapmışsınız ve başvurunuz hala değerlendiriliyor veya onaylanmış durumda.'
        ];
    }
    
    $sql = "INSERT INTO applications (user_id, school_id, position, branch, message, cv_path, status, created_at) 
            VALUES ('$user_id', '$school_id', '$position', '$branch', '$message', $cv_field, 'pending', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Başvurunuz başarıyla kaydedildi.',
            'application_id' => mysqli_insert_id($conn)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Başvuru sırasında bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Başvuru detaylarını getir
 */
function getApplicationDetails($application_id) {
    $conn = getDbConnection();
    
    $application_id = mysqli_real_escape_string($conn, $application_id);
    
    $sql = "SELECT a.*, s.name as school_name, s.city as school_city, 
                   u.name as user_name, u.surname as user_surname, u.email as user_email 
            FROM applications a
            JOIN schools s ON a.school_id = s.id
            JOIN users u ON a.user_id = u.id
            WHERE a.id = '$application_id'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Kullanıcının tüm başvurularını getir
 */
function getUserApplications($user_id) {
    $conn = getDbConnection();
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $sql = "SELECT a.*, s.name as school_name, s.city as school_city 
            FROM applications a
            JOIN schools s ON a.school_id = s.id
            WHERE a.user_id = '$user_id'
            ORDER BY a.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $applications = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Okul için başvuruları getir
 */
function getSchoolApplications($school_id, $status = null, $branch = null) {
    $conn = getDbConnection();
    
    $school_id = mysqli_real_escape_string($conn, $school_id);
    
    $where_conditions = ["a.school_id = '$school_id'"];
    
    if ($status) {
        $status = mysqli_real_escape_string($conn, $status);
        $where_conditions[] = "a.status = '$status'";
    }
    
    if ($branch) {
        $branch = mysqli_real_escape_string($conn, $branch);
        $where_conditions[] = "a.branch = '$branch'";
    }
    
    $where_sql = implode(" AND ", $where_conditions);
    
    $sql = "SELECT a.*, u.name as user_name, u.surname as user_surname, u.email as user_email, u.phone as user_phone
            FROM applications a
            JOIN users u ON a.user_id = u.id
            WHERE $where_sql
            ORDER BY a.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $applications = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Tüm başvuruları getir (admin için)
 */
function getAllApplications($status = null, $branch = null, $limit = null) {
    $conn = getDbConnection();
    
    $where_conditions = [];
    
    if ($status) {
        $status = mysqli_real_escape_string($conn, $status);
        $where_conditions[] = "a.status = '$status'";
    }
    
    if ($branch) {
        $branch = mysqli_real_escape_string($conn, $branch);
        $where_conditions[] = "a.branch = '$branch'";
    }
    
    $where_sql = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $limit_sql = "";
    if ($limit) {
        $limit = (int)$limit;
        $limit_sql = "LIMIT $limit";
    }
    
    $sql = "SELECT a.*, u.name as user_name, u.surname as user_surname, u.email as user_email,
                   s.name as school_name, s.city as school_city
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN schools s ON a.school_id = s.id
            $where_sql
            ORDER BY a.created_at DESC
            $limit_sql";
    
    $result = mysqli_query($conn, $sql);
    
    $applications = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Başvuru durumunu güncelle (okul yöneticisi veya admin için)
 */
function updateApplicationStatus($application_id, $new_status, $feedback = '') {
    $conn = getDbConnection();
    
    $application_id = mysqli_real_escape_string($conn, $application_id);
    $new_status = mysqli_real_escape_string($conn, $new_status);
    $feedback = mysqli_real_escape_string($conn, $feedback);
    
    // Geçerli durumlar: pending, approved, rejected, completed
    $valid_statuses = ['pending', 'approved', 'rejected', 'completed'];
    
    if (!in_array($new_status, $valid_statuses)) {
        return [
            'success' => false,
            'message' => 'Geçersiz durum belirtildi.'
        ];
    }
    
    $status_date_field = "";
    $additional_fields = "";
    
    if ($new_status == 'approved') {
        $status_date_field = ", approved_at = NOW()";
    } elseif ($new_status == 'rejected') {
        $status_date_field = ", rejected_at = NOW()";
    } elseif ($new_status == 'completed') {
        $status_date_field = ", completed_at = NOW()";
    }
    
    if ($feedback) {
        $additional_fields = ", feedback = '$feedback'";
    }
    
    $sql = "UPDATE applications 
            SET status = '$new_status' $status_date_field $additional_fields, updated_at = NOW()
            WHERE id = '$application_id'";
    
    if (mysqli_query($conn, $sql)) {
        // Eğer başvuru onaylandıysa, kullanıcıya bildirim gönder
        if ($new_status == 'approved' || $new_status == 'rejected') {
            $app_details = getApplicationDetails($application_id);
            if ($app_details) {
                $user_id = $app_details['user_id'];
                $school_name = $app_details['school_name'];
                $status_text = $new_status == 'approved' ? 'onaylandı' : 'reddedildi';
                $message = "$school_name okuluna yaptığınız başvuru $status_text.";
                if ($feedback) {
                    $message .= " Geri bildirim: $feedback";
                }
                
                // Bildirim ekleme fonksiyonu (notifications.php'de tanımlanacak)
                if (function_exists('addNotification')) {
                    addNotification($user_id, 'application_update', $message, $application_id);
                }
            }
        }
        
        return [
            'success' => true,
            'message' => 'Başvuru durumu güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Başvuru durumu güncellenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Başvuruyu sil
 */
function deleteApplication($application_id) {
    $conn = getDbConnection();
    
    $application_id = mysqli_real_escape_string($conn, $application_id);
    
    // Başvuru dosyasını kontrol et
    $file_sql = "SELECT cv_path FROM applications WHERE id = '$application_id'";
    $file_result = mysqli_query($conn, $file_sql);
    
    if ($file_result && mysqli_num_rows($file_result) == 1) {
        $file_row = mysqli_fetch_assoc($file_result);
        $cv_path = $file_row['cv_path'];
        
        // Eğer CV yüklenmişse ve dosya varsa, sil
        if ($cv_path && file_exists($cv_path)) {
            unlink($cv_path);
        }
    }
    
    $sql = "DELETE FROM applications WHERE id = '$application_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Başvuru başarıyla silindi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Başvuru silinirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Başvuru istatistiklerini getir (admin için)
 */
function getApplicationStatistics() {
    $conn = getDbConnection();
    
    // Durum bazında başvuru sayıları
    $status_sql = "SELECT status, COUNT(*) as count 
                  FROM applications 
                  GROUP BY status";
    
    $status_result = mysqli_query($conn, $status_sql);
    
    $status_stats = [
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'completed' => 0
    ];
    
    if ($status_result && mysqli_num_rows($status_result) > 0) {
        while ($row = mysqli_fetch_assoc($status_result)) {
            $status_stats[$row['status']] = (int)$row['count'];
        }
    }
    
    // Branş bazında başvuru sayıları
    $branch_sql = "SELECT branch, COUNT(*) as count 
                  FROM applications 
                  GROUP BY branch 
                  ORDER BY count DESC";
    
    $branch_result = mysqli_query($conn, $branch_sql);
    
    $branch_stats = [];
    
    if ($branch_result && mysqli_num_rows($branch_result) > 0) {
        while ($row = mysqli_fetch_assoc($branch_result)) {
            $branch_stats[$row['branch']] = (int)$row['count'];
        }
    }
    
    // Şehir bazında başvuru sayıları
    $city_sql = "SELECT s.city, COUNT(*) as count 
                FROM applications a
                JOIN schools s ON a.school_id = s.id
                GROUP BY s.city 
                ORDER BY count DESC";
    
    $city_result = mysqli_query($conn, $city_sql);
    
    $city_stats = [];
    
    if ($city_result && mysqli_num_rows($city_result) > 0) {
        while ($row = mysqli_fetch_assoc($city_result)) {
            $city_stats[$row['city']] = (int)$row['count'];
        }
    }
    
    // Aylara göre başvuru sayıları
    $monthly_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                   FROM applications 
                   GROUP BY month 
                   ORDER BY month DESC";
    
    $monthly_result = mysqli_query($conn, $monthly_sql);
    
    $monthly_stats = [];
    
    if ($monthly_result && mysqli_num_rows($monthly_result) > 0) {
        while ($row = mysqli_fetch_assoc($monthly_result)) {
            $monthly_stats[$row['month']] = (int)$row['count'];
        }
    }
    
    return [
        'status' => $status_stats,
        'branch' => $branch_stats,
        'city' => $city_stats,
        'monthly' => $monthly_stats,
        'total' => array_sum($status_stats)
    ];
}

/**
 * Onaylanan başvuruları Excel'e aktarmak için verileri getir
 */
function getApprovedApplicationsForExport($start_date = null, $end_date = null, $city = null, $branch = null) {
    $conn = getDbConnection();
    
    $where_conditions = ["a.status = 'approved'"];
    
    if ($start_date) {
        $start_date = mysqli_real_escape_string($conn, $start_date);
        $where_conditions[] = "a.approved_at >= '$start_date 00:00:00'";
    }
    
    if ($end_date) {
        $end_date = mysqli_real_escape_string($conn, $end_date);
        $where_conditions[] = "a.approved_at <= '$end_date 23:59:59'";
    }
    
    if ($city) {
        $city = mysqli_real_escape_string($conn, $city);
        $where_conditions[] = "s.city = '$city'";
    }
    
    if ($branch) {
        $branch = mysqli_real_escape_string($conn, $branch);
        $where_conditions[] = "a.branch = '$branch'";
    }
    
    $where_sql = implode(" AND ", $where_conditions);
    
    $sql = "SELECT a.id, a.position, a.branch, a.status, a.created_at, a.approved_at,
                   u.name as user_name, u.surname as user_surname, u.email as user_email, u.phone as user_phone,
                   s.name as school_name, s.city as school_city, s.address as school_address
            FROM applications a
            JOIN users u ON a.user_id = u.id
            JOIN schools s ON a.school_id = s.id
            WHERE $where_sql
            ORDER BY a.approved_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $applications = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}
?> 