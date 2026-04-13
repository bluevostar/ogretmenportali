<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/config.php';

// Check if school ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Okul ID belirtilmedi.']);
    exit;
}

$schoolId = (int)$_GET['id'];

try {
    // Fetch all columns for the school
    $stmt = $db->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->execute([$schoolId]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($school) {
        echo json_encode(['success' => true, 'school' => $school]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Okul bulunamadı.']);
    }
} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Database error in get_school_details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası oluştu.']);
} 