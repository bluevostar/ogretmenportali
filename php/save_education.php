<?php
require_once '../includes/config.php';

// Oturum kontrolü
if (!isset($_SESSION['teacher_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $query = "INSERT INTO teacher_education (teacher_id, degree, school, start_date, end_date, description) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['teacher_id'],
            $data['degree'],
            $data['school'],
            $data['start_date'],
            $data['end_date'] ?: null,
            $data['description'] ?? null
        ]);

        $educationId = $db->lastInsertId();
        
        // Yeni eklenen eğitim bilgisini getir
        $query = "SELECT * FROM teacher_education WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$educationId]);
        $education = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'education' => $education
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Eğitim bilgisi kaydedilirken bir hata oluştu.',
            'details' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>