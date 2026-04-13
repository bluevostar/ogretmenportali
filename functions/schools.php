<?php
require_once '../config/db_connect.php';

/**
 * Tüm okulları getir
 */
function getAllSchools($city = null, $limit = null, $offset = 0) {
    $conn = getDbConnection();
    
    $where_condition = '';
    if ($city) {
        $city = mysqli_real_escape_string($conn, $city);
        $where_condition = "WHERE city = '$city'";
    }
    
    $limit_sql = '';
    if ($limit) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $limit_sql = "LIMIT $offset, $limit";
    }
    
    $sql = "SELECT s.*, 
                  (SELECT COUNT(*) FROM applications a WHERE a.school_id = s.id) as application_count
           FROM schools s
           $where_condition
           ORDER BY s.name ASC
           $limit_sql";
    
    $result = mysqli_query($conn, $sql);
    
    $schools = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $schools[] = $row;
        }
    }
    
    return $schools;
}

/**
 * Okul detaylarını getir
 */
function getSchoolDetails($school_id) {
    $conn = getDbConnection();
    
    $school_id = mysqli_real_escape_string($conn, $school_id);
    
    $sql = "SELECT s.*, 
                  (SELECT COUNT(*) FROM applications a WHERE a.school_id = s.id) as application_count,
                  (SELECT COUNT(*) FROM applications a WHERE a.school_id = s.id AND a.status = 'approved') as approved_count
           FROM schools s
           WHERE s.id = '$school_id'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

/**
 * Yeni okul ekle
 */
function addSchool($name, $address, $city, $description = '', $manager_id = null) {
    $conn = getDbConnection();
    
    // Verileri temizle
    $name = mysqli_real_escape_string($conn, $name);
    $address = mysqli_real_escape_string($conn, $address);
    $city = mysqli_real_escape_string($conn, $city);
    $description = mysqli_real_escape_string($conn, $description);
    $manager_id = $manager_id ? mysqli_real_escape_string($conn, $manager_id) : 'NULL';
    
    $manager_sql = $manager_id != 'NULL' ? "'$manager_id'" : $manager_id;
    
    $sql = "INSERT INTO schools (name, address, city, description, manager_id) 
            VALUES ('$name', '$address', '$city', '$description', $manager_sql)";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Okul başarıyla eklendi.',
            'school_id' => mysqli_insert_id($conn)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Okul eklenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Okul bilgilerini güncelle
 */
function updateSchool($school_id, $name, $address, $city, $description = '', $manager_id = null) {
    $conn = getDbConnection();
    
    // Verileri temizle
    $school_id = mysqli_real_escape_string($conn, $school_id);
    $name = mysqli_real_escape_string($conn, $name);
    $address = mysqli_real_escape_string($conn, $address);
    $city = mysqli_real_escape_string($conn, $city);
    $description = mysqli_real_escape_string($conn, $description);
    
    $manager_sql = "";
    if ($manager_id !== null) {
        $manager_id = mysqli_real_escape_string($conn, $manager_id);
        $manager_sql = ", manager_id = '$manager_id'";
    }
    
    $sql = "UPDATE schools 
            SET name = '$name', address = '$address', city = '$city', 
                description = '$description' $manager_sql
            WHERE id = '$school_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Okul bilgileri güncellendi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Okul bilgileri güncellenirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Okul sil
 */
function deleteSchool($school_id) {
    $conn = getDbConnection();
    
    $school_id = mysqli_real_escape_string($conn, $school_id);
    
    // Önce bu okula yapılan başvuruları kontrol et
    $check_sql = "SELECT COUNT(*) as count FROM applications WHERE school_id = '$school_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if ($check_result) {
        $row = mysqli_fetch_assoc($check_result);
        if ($row['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Bu okula ait başvurular bulunduğu için silinemez.'
            ];
        }
    }
    
    $sql = "DELETE FROM schools WHERE id = '$school_id'";
    
    if (mysqli_query($conn, $sql)) {
        return [
            'success' => true,
            'message' => 'Okul başarıyla silindi.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Okul silinirken bir hata oluştu: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Belirli bir yöneticiye ait okulları getir
 */
function getSchoolsByManager($manager_id) {
    $conn = getDbConnection();
    
    $manager_id = mysqli_real_escape_string($conn, $manager_id);
    
    $sql = "SELECT * FROM schools WHERE manager_id = '$manager_id' ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    
    $schools = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $schools[] = $row;
        }
    }
    
    return $schools;
}

/**
 * Uygun filtrelere göre okul araması
 */
function searchSchools($search_term = '', $city = '') {
    $conn = getDbConnection();
    
    $where_clauses = [];
    
    if (!empty($search_term)) {
        $search_term = mysqli_real_escape_string($conn, $search_term);
        $where_clauses[] = "(name LIKE '%$search_term%' OR description LIKE '%$search_term%')";
    }
    
    if (!empty($city)) {
        $city = mysqli_real_escape_string($conn, $city);
        $where_clauses[] = "city = '$city'";
    }
    
    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";
    
    $sql = "SELECT * FROM schools $where_sql ORDER BY name ASC";
    $result = mysqli_query($conn, $sql);
    
    $schools = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $schools[] = $row;
        }
    }
    
    return $schools;
}

/**
 * Tüm şehirleri getir (okul verilerinden)
 */
function getAllCities() {
    $conn = getDbConnection();
    
    $sql = "SELECT DISTINCT city FROM schools ORDER BY city ASC";
    $result = mysqli_query($conn, $sql);
    
    $cities = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cities[] = $row['city'];
        }
    }
    
    return $cities;
}
?> 