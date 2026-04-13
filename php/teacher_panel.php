<?php
require_once '../includes/config.php';
require_once '../config/db_connect.php';
require_once '../models/viewmodels/TeacherViewModel.php';

// PDO bağlantıyı al
$db = getPdoConnection();

// ViewModel oluştur
$viewModel = new TeacherViewModel($db);

// Session kontrolü
if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

// Aksiyon parametresini al
$action = $_GET['action'] ?? 'dashboard';

// Profil fotoğrafı yükleme fonksiyonu
function handleProfilePhotoUpload() {
    global $db;
    
    try {

        $teacherId = $_SESSION['teacher_id'];
        $removePhoto = isset($_POST['remove_photo']) && $_POST['remove_photo'] === 'on';
        
        // Mevcut profil bilgilerini al
        $stmt = $db->prepare("SELECT profile_photo, gender FROM users WHERE id = ?");
        $stmt->execute([$teacherId]);
        $currentProfile = $stmt->fetch();
        
        if ($removePhoto) {
            // Mevcut fotoğrafı sil
            if (!empty($currentProfile['profile_photo'])) {
                $oldPhotoPath = '../' . $currentProfile['profile_photo'];
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            
            // Veritabanından fotoğrafı kaldır
            $stmt = $db->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
            $stmt->execute([$teacherId]);
            
            return [
                'status' => 'success',
                'message' => 'Profil fotoğrafı kaldırıldı',
                'photo_url' => null,
                'gender' => $currentProfile['gender'] ?? 'male'
            ];
        }
        
        // Dosya yükleme kontrolü
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return [
                'status' => 'error',
                'message' => 'Dosya yükleme hatası'
            ];
        }
        
        $file = $_FILES['photo'];
        
        // Dosya boyutu kontrolü (2MB - server ayarına göre)
        if ($file['size'] > 2 * 1024 * 1024) {
            return [
                'status' => 'error',
                'message' => 'Dosya boyutu 2MB\'dan büyük olamaz'
            ];
        }
        
        // Dosya türü kontrolü
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $file['type'];
        if (!in_array($fileType, $allowedTypes)) {
            return [
                'status' => 'error',
                'message' => 'Sadece JPG, PNG veya GIF formatında dosyalar yükleyebilirsiniz'
            ];
        }
        
        // Dosya uzantısını belirle
        $extensions = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif'
        ];
        $extension = $extensions[$fileType];
        
        // Benzersiz dosya adı oluştur
        $fileName = 'profile_' . $teacherId . '_' . time() . $extension;
        $uploadDir = '../uploads/profile/';
        $uploadPath = $uploadDir . $fileName;
        
        // Dizin kontrolü
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dosyayı yükle
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Eski fotoğrafı sil
            if (!empty($currentProfile['profile_photo'])) {
                $oldPhotoPath = '../' . $currentProfile['profile_photo'];
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            
            // Veritabanını güncelle
            $dbPath = 'uploads/profile/' . $fileName;
            $stmt = $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->execute([$dbPath, $teacherId]);
            
            return [
                'status' => 'success',
                'message' => 'Profil fotoğrafı başarıyla güncellendi',
                'photo_url' => BASE_URL . '/' . $dbPath,
                'gender' => $currentProfile['gender'] ?? 'male'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Dosya yükleme başarısız'
            ];
        }
        
    } catch (Exception $e) {
        // Debug log - kaldırılabilir
        // error_log("Photo upload error: " . $e->getMessage());
        // error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'status' => 'error',
            'message' => 'Bir hata oluştu: ' . $e->getMessage()
        ];
    }
}

// Debug endpoint (GET request)
if (isset($_GET['debug_upload']) && $_GET['debug_upload'] === '1') {
    $uploadDir = '../uploads/profile/';
    $debugInfo = [
        'upload_dir' => $uploadDir,
        'upload_dir_exists' => is_dir($uploadDir),
        'upload_dir_writable' => is_writable($uploadDir),
        'base_url' => BASE_URL,
        'session_teacher_id' => $_SESSION['teacher_id'] ?? 'NOT_SET',
        'php_settings' => [
            'file_uploads' => ini_get('file_uploads'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($debugInfo);
    exit;
}

// POST işlemlerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Profil fotoğrafı yükleme işlemi
    if (isset($_POST['action']) && $_POST['action'] === 'upload_profile_photo') {
        $result = handleProfilePhotoUpload();
        
        // Her durumda JSON döndür (AJAX veya normal form)
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    $result = $viewModel->processInput($_POST);
    
    // AJAX isteği ise JSON döndür
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    // Normal form gönderimi ise, yönlendir
    if ($result['status'] === 'success') {
        header("Location: teacher_panel.php?action={$action}&status=success&message=" . urlencode($result['message']));
    } else {
        header("Location: teacher_panel.php?action={$action}&status=error&message=" . urlencode($result['message']));
    }
    exit;
}

// View verilerini hazırla
try {
    $viewData = $viewModel->prepareViewData(['action' => $action]);
    
    // Debug için
    if (isset($_GET['debug'])) {
        echo "<pre>";
        echo "Action: " . $action . "\n";
        echo "ViewData keys: " . implode(', ', array_keys($viewData)) . "\n";
        if (isset($viewData['profile'])) {
            echo "Profile data available\n";
        }
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>";
    echo "<strong class='font-bold'>Hata!</strong>";
    echo "<span class='block sm:inline'> Veri yüklenirken hata oluştu: " . htmlspecialchars($e->getMessage()) . "</span>";
    echo "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Paneli - ÖğretmenPro</title>
    <script>
        (function () {
            try {
                if (window.localStorage && localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (error) {
                // localStorage erisimi engellenirse ilk render varsayilan halde devam eder.
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        },
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
        }
        
        .sidebar {
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            white-space: nowrap;
        }
        
        .sidebar.collapsed {
            width: 5rem !important;
        }
        
        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed span:not(.sidebar-mini),
        .sidebar.collapsed .dropdown-content,
        .sidebar.collapsed p,
        .sidebar.collapsed h4,
        .sidebar.collapsed .w-24 {
            display: none;
        }
        
        .sidebar.collapsed i {
            font-size: 1.25rem;
            margin-right: 0;
        }
        
        .sidebar.collapsed #desktopSidebarToggle i {
            padding-left: 14px;
            font-size: 24px;
        }       
        
        .sidebar.collapsed .flex-col.items-center {
            margin-bottom: 1rem;
        }
        
        .sidebar.collapsed .sidebar-link {
            justify-content: center;
            padding: 0.75rem;
        }
        
        .sidebar.collapsed .sidebar-link i {
            margin-right: 0;
            width: auto;
        }
        
        #desktopSidebarToggle {
            transform: translateX(14px);
        }
        
        html.sidebar-collapsed #sidebar {
            width: 5rem !important;
        }
        
        html.sidebar-collapsed #sidebar .sidebar-title,
        html.sidebar-collapsed #sidebar span:not(.sidebar-mini),
        html.sidebar-collapsed #sidebar .dropdown-content,
        html.sidebar-collapsed #sidebar p,
        html.sidebar-collapsed #sidebar h4,
        html.sidebar-collapsed #sidebar .w-24 {
            display: none;
        }
        
        html.sidebar-collapsed #sidebar i {
            font-size: 1.25rem;
            margin-right: 0;
        }
        
        html.sidebar-collapsed #sidebar #desktopSidebarToggle i {
            padding-left: 14px;
            font-size: 24px;
        }
        
        html.sidebar-collapsed #sidebar .flex-col.items-center {
            margin-bottom: 1rem;
        }
        
        html.sidebar-collapsed #sidebar .sidebar-link {
            justify-content: center;
            padding: 0.75rem;
        }
        
        html.sidebar-collapsed #sidebar .sidebar-link i {
            margin-right: 0;
            width: auto;
        }
        
        .sidebar-link {
            transition: all 0.2s;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            color: #475569;
            font-weight: 500;
            white-space: nowrap;
            font-size: 0.9rem;
        }
        
        .sidebar-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }
        
        .sidebar-link:hover {
            background-color: #f1f5f9;
            color: #0ea5e9;
        }
        
        .sidebar-link.active {
            background-color: #0ea5e9;
            color: white;
        }
        
        .sidebar-link.active:hover {
            background-color: #0284c7;
            color: white;
        }
        
        .sidebar-menu-scroll {
            overflow-y: auto;
            max-height: calc(100vh - 20rem);
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        
        .content-area {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
        }
        
        table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #e2e8f0;
            color: #1f2937 !important;
            font-weight: 700 !important;
        }
        
        /* Modal yazi puntosunu tablo satirlarina esitle */
        [id$="Modal"].fixed,
        #modal.fixed {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        [id$="Modal"].fixed :is(p, label, span, li, a, button, input, select, textarea, td, th, div),
        #modal.fixed :is(p, label, span, li, a, button, input, select, textarea, td, th, div) {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
        }
        [id$="Modal"].fixed :is(p, li),
        #modal.fixed :is(p, li) {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
        
        .header-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            min-width: 180px;
            z-index: 50;
            background-color: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 0.375rem;
            padding: 0.5rem 0;
            margin-top: 0.25rem;
            border: 1px solid #e5e7eb;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            z-index: 10;
            margin-top: 0.5rem;
            min-width: 12rem;
            border-radius: 0.375rem;
            background-color: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            padding: 0.25rem 0;
            transition: opacity 0.3s;
            display: none;
        }
        .dropdown-menu a {
            padding: 12px 16px;
            display: block;
            white-space: nowrap;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .dropdown:hover .dropdown-content {
            opacity: 1;
            visibility: visible;
        }
        
        /* Kamera butonu stilleri */
        .camera-btn {
            position: absolute;
            bottom: -8px;
            right: -8px;
            z-index: 10;
            min-width: 32px;
            min-height: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .camera-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Profil container stilleri */
        .profile-photo-container {
            position: relative;
            overflow: visible !important;
        }

        /* Tum modal'lar icin ortak sabit/ortali davranis */
        [id$="Modal"].fixed {
            position: fixed !important;
            inset: 0 !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem !important;
            overflow-y: auto !important;
        }
        [id$="Modal"].fixed:not(.hidden) {
            display: flex !important;
        }
        [id$="Modal"].fixed > div {
            width: 100% !important;
            max-width: min(100%, 42rem) !important;
            max-height: calc(100vh - 2rem) !important;
            overflow: hidden !important;
            margin: auto !important;
            top: auto !important;
        }
        [id$="Modal"].fixed .max-h-\[calc\(100vh-200px\)\] {
            max-height: calc(100vh - 13rem) !important;
            overflow-y: auto !important;
        }
    </style>
</head>
<body>
    <div class="flex h-screen bg-secondary-100">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-64 md:block h-screen flex flex-col overflow-hidden transition-all duration-300 ease-in-out">
            <div class="flex items-center justify-between h-14 border-b border-secondary-200 px-4 shrink-0">
                <span class="text-xl font-bold text-secondary-900">
                    <svg width="32" height="32" viewBox="0 0 140 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="inline-block mr-2">
                        <defs>
                            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#7C3AED;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <circle cx="20" cy="20" r="16" fill="url(#grad1)"/>
                        <rect x="13" y="15" width="14" height="10" rx="1" fill="none" stroke="white" stroke-width="1.5"/>
                        <line x1="20" y1="15" x2="20" y2="25" stroke="white" stroke-width="1"/>
                        <path d="M15 12l5-2 5 2-5 2-5-2z" fill="white"/>
                        <path d="M25 12v3l-5 2-5-2v-3" fill="none" stroke="white" stroke-width="1"/>
                    </svg>
                    <span class="sidebar-title">ÖğretmenPro</span>
                </span>
                <button id="desktopSidebarToggle" class="text-secondary-500 hover:text-secondary-700 hidden md:block flex items-center justify-center">
                    <i class="fas fa-bars fa-xl"></i>
                </button>
            </div>
            <div class="mt-4 px-4 pb-6 flex-1 min-h-0 flex flex-col">
                <div class="flex flex-col items-center mb-6">
                    <div class="profile-photo-container w-24 h-24 mb-3">
                        <?php
                        function getCoverColorGradient($color) {
                            $gradients = [
                                'blue' => 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                                'cyan' => 'linear-gradient(135deg, #06b6d4, #0891b2)',
                                'emerald' => 'linear-gradient(135deg, #10b981, #059669)',
                                'lime' => 'linear-gradient(135deg, #84cc16, #65a30d)',
                                'orange' => 'linear-gradient(135deg, #f97316, #ea580c)',
                                'red' => 'linear-gradient(135deg, #ef4444, #dc2626)',
                                'violet' => 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
                                'pink' => 'linear-gradient(135deg, #ec4899, #db2777)',
                                'slate' => 'linear-gradient(135deg, #64748b, #475569)',
                                'gray' => 'linear-gradient(135deg, #6b7280, #4b5563)',
                                'amber' => 'linear-gradient(135deg, #fbbf24, #f59e0b)',
                                'purple' => 'linear-gradient(135deg, #a855f7, #9333ea)'
                            ];
                            return $gradients[$color] ?? '';
                        }
                        
                        $profile = $viewData['profile'] ?? [];
                        $coverColorStyle = !empty($profile['cover_color']) ? 'style="background: ' . getCoverColorGradient($profile['cover_color']) . '"' : '';
                        ?>
                        
                        <div class="w-full h-full rounded-full bg-primary-100 flex items-center justify-center overflow-hidden" <?php echo $coverColorStyle; ?>>
                            <?php if (!empty($profile['cover_photo'])): ?>
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($profile['cover_photo']) ?>" alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php 
                                $gender = $profile['gender'] ?? 'male';
                                $avatar = ($gender === 'female') ? 'avatar-female.jpg' : 'avatar-male.jpg';
                                ?>
                                <img src="<?= BASE_URL ?>/assets/images/<?= $avatar ?>" alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Profil fotoğrafı değiştirme butonu -->
                        <button type="button" class="camera-btn bg-primary-500 hover:bg-primary-600 text-white w-8 h-8 rounded-full transition-all flex items-center justify-center border-2 border-white" onclick="openPhotoModal()">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>
                    <h4 class="text-secondary-900 font-semibold"><?php echo $_SESSION['name'] . ' ' . $_SESSION['surname']; ?></h4>
                    <p class="text-secondary-500 text-sm">Öğretmen</p>
                </div>
                <ul class="sidebar-menu-scroll flex-1 min-h-0 pr-1">
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=dashboard" class="sidebar-link <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Pano</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=profile" class="sidebar-link <?php echo $action === 'profile' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=messages" class="sidebar-link <?php echo $action === 'messages' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i>
                            <span>Mesajlar</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=settings" class="sidebar-link <?php echo $action === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Ayarlar</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/logout.php?from=teacher" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış Yap</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Mobile Sidebar Toggle -->
        <div class="md:hidden fixed bottom-4 right-4 z-50">
            <button id="sidebarToggle" class="bg-primary-500 text-white p-3 rounded-full shadow-lg">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Mobile Sidebar -->
        <div id="mobileSidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:hidden transition duration-200 ease-in-out z-30 w-64 bg-white shadow-lg">
            <div class="flex items-center justify-between h-16 border-b border-secondary-200 px-4">
                <span class="text-xl font-bold text-secondary-900">
                    <i class="fas fa-graduation-cap text-primary-500 mr-2"></i>
                    ÖğretmenPro
                </span>
                <button id="closeSidebar" class="text-secondary-500 hover:text-secondary-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4 px-4 pb-6">
                <div class="flex flex-col items-center mb-6">
                    <div class="profile-photo-container w-24 h-24 mb-3">
                        <div class="w-full h-full rounded-full bg-primary-100 flex items-center justify-center overflow-hidden" <?php echo $coverColorStyle; ?>>
                            <?php if (!empty($profile['profile_photo'])): ?>
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($profile['profile_photo']) ?>" alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php 
                                $gender = $profile['gender'] ?? 'male';
                                $avatar = ($gender === 'female') ? 'avatar-female.jpg' : 'avatar-male.jpg';
                                ?>
                                <img src="<?= BASE_URL ?>/assets/images/<?= $avatar ?>" alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Profil fotoğrafı değiştirme butonu -->
                        <button type="button" class="camera-btn bg-primary-500 hover:bg-primary-600 text-white w-8 h-8 rounded-full transition-all flex items-center justify-center border-2 border-white" onclick="openPhotoModal()">
                            <i class="fas fa-camera text-sm"></i>
                        </button>
                    </div>
                    <h4 class="text-secondary-900 font-semibold"><?php echo $_SESSION['name'] . ' ' . $_SESSION['surname']; ?></h4>
                    <p class="text-secondary-500 text-sm">Öğretmen</p>
                </div>
                <ul class="sidebar-menu-scroll">
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=dashboard" class="sidebar-link <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Pano</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=profile" class="sidebar-link <?php echo $action === 'profile' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=messages" class="sidebar-link <?php echo $action === 'messages' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i>
                            <span>Mesajlar</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=settings" class="sidebar-link <?php echo $action === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Ayarlar</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/php/logout.php?from=teacher" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış Yap</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white h-14 border-b border-secondary-200">
                <div class="flex items-center justify-between h-14 px-6">
                    <div class="flex items-center">
                        <button id="menuBtn" class="text-secondary-500 hover:text-secondary-700 md:hidden">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="flex items-center">
                        <div class="relative dropdown">
                            <button class="flex items-center focus:outline-none">
                                <?php
                                $profilePhoto = !empty($profile['profile_photo'])
                                    ? BASE_URL . '/' . htmlspecialchars($profile['profile_photo'])
                                    : BASE_URL . '/assets/images/avatar-male.jpg';
                                ?>
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-2 overflow-hidden">
                                    <img src="<?= $profilePhoto ?>" alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                                </div>
                                <span class="text-sm font-medium text-gray-700 mr-1"><?php echo $_SESSION['name'] . ' ' . $_SESSION['surname']; ?></span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            <div class="dropdown-content mt-1 py-2 bg-white rounded-md shadow-lg">
                                <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Profil
                                </a>
                                <a href="<?php echo BASE_URL; ?>/php/teacher_panel.php?action=settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> Ayarlar
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <a href="<?php echo BASE_URL; ?>/php/logout.php?from=teacher" onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-secondary-50 p-6">
                <?php
                // Başarı/hata mesajlarını göster
                if (isset($_GET['status']) && isset($_GET['message'])) {
                    $status = $_GET['status'];
                    $message = htmlspecialchars($_GET['message']);
                    $alertClass = $status === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                    echo "<div class='{$alertClass} px-4 py-3 rounded relative mb-4' role='alert'>";
                    echo "<span class='block sm:inline'>{$message}</span>";
                    echo "</div>";
                }
                ?>
                
                <?php 
                // İçerik dosyasını dahil et
                $allowed_actions = ['dashboard', 'profile', 'messages', 'settings'];
                if (in_array($action, $allowed_actions)) {
                    $component_file = dirname(__DIR__) . '/components/teacher/' . $action . '.php';
                    if (file_exists($component_file)) {
                        // Component dosyalarında kullanılabilmesi için değişkenleri tanımla
                        $teacherViewModel = $viewModel;
                        $viewData = $viewData;
                        include $component_file;
                    } else {
                        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>Component file not found: {$component_file}</div>";
                    }
                } else {
                    // Fallback to dashboard
                    $teacherViewModel = $viewModel;
                    $viewData = $viewData;
                    include dirname(__DIR__) . '/components/teacher/dashboard.php';
                }
                ?>
            </main>
        </div>
    </div>
    

    
    <!-- Kapak Rengi Seçme Modal -->
    <div id="colorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Kapak Rengini Seç</h3>
                    <button type="button" onclick="closeColorModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Renk Seçin</label>
                    <div class="grid grid-cols-6 gap-3">
                        <!-- Mavi tonları -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);" data-color="blue" onclick="selectColor('blue')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #06b6d4, #0891b2);" data-color="cyan" onclick="selectColor('cyan')"></button>
                        
                        <!-- Yeşil tonları -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #10b981, #059669);" data-color="emerald" onclick="selectColor('emerald')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #84cc16, #65a30d);" data-color="lime" onclick="selectColor('lime')"></button>
                        
                        <!-- Turuncu/Kırmızı tonları -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #f97316, #ea580c);" data-color="orange" onclick="selectColor('orange')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #ef4444, #dc2626);" data-color="red" onclick="selectColor('red')"></button>
                        
                        <!-- Mor tonları -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);" data-color="violet" onclick="selectColor('violet')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #ec4899, #db2777);" data-color="pink" onclick="selectColor('pink')"></button>
                        
                        <!-- Diğer tonlar -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #64748b, #475569);" data-color="slate" onclick="selectColor('slate')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #6b7280, #4b5563);" data-color="gray" onclick="selectColor('gray')"></button>
                        
                        <!-- Özel renkler -->
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);" data-color="amber" onclick="selectColor('amber')"></button>
                        <button type="button" class="color-option w-10 h-10 rounded-full border-2 border-gray-300 hover:border-gray-500 transition-all" style="background: linear-gradient(135deg, #a855f7, #9333ea);" data-color="purple" onclick="selectColor('purple')"></button>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Profil arka plan renginizi seçin.</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeColorModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        İptal
                    </button>
                    <button type="button" id="applyColorBtn" onclick="applySelectedColor()" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600" disabled>
                        <i class="fas fa-check mr-2"></i>Uygula
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profil Fotoğrafı Değiştirme Modal -->
    <div id="photoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Profil Fotoğrafı Değiştir</h3>
                    <button type="button" onclick="closePhotoModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="photoUploadForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Fotoğraf Seçin</label>
                        
                        <!-- Fotoğraf önizlemesi -->
                        <div class="flex justify-center mb-4">
                            <div class="relative">
                                <img id="photoPreview" src="" alt="Önizleme" class="w-32 h-32 rounded-full object-cover border-4 border-gray-300 hidden">
                                <div id="photoPlaceholder" class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-300">
                                    <i class="fas fa-camera text-gray-400 text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dosya seçme alanı -->
                        <div class="relative">
                            <input type="file" id="photoFile" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(event)">
                            <label for="photoFile" class="cursor-pointer inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-upload mr-2"></i>
                                Fotoğraf Seç
                            </label>
                        </div>
                        
                        <p class="mt-2 text-sm text-gray-500">
                            JPG, PNG veya GIF formatında, maksimum 2MB boyutunda olmalıdır.
                        </p>
                    </div>
                    
                    <!-- Mevcut fotoğrafı kaldır seçeneği -->
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="removePhoto" name="remove_photo" class="form-checkbox h-4 w-4 text-primary-600">
                            <span class="ml-2 text-sm text-gray-700">Mevcut fotoğrafı kaldır</span>
                        </label>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePhotoModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            İptal
                        </button>
                        <button type="button" id="uploadPhotoBtn" onclick="uploadPhoto()" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600" disabled>
                            <i class="fas fa-save mr-2"></i>Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    
    <script>
        // BASE_URL tanımını JavaScript'e aktar
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Kapak rengi modal fonksiyonları
        let selectedColor = '';
        
        function openColorModal() {
            document.getElementById('colorModal').classList.remove('hidden');
        }
        
        function closeColorModal() {
            document.getElementById('colorModal').classList.add('hidden');
            // Seçili rengi temizle
            document.querySelectorAll('.color-option').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-blue-300');
            });
            selectedColor = '';
            document.getElementById('applyColorBtn').disabled = true;
        }
        
        function selectColor(color) {
            selectedColor = color;
            
            // Tüm renk butonlarından seçimi kaldır
            document.querySelectorAll('.color-option').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-blue-300');
            });
            
            // Seçili butona vurgu ekle
            event.target.classList.add('ring-4', 'ring-blue-300');
            
            // Uygula butonunu aktif et
            document.getElementById('applyColorBtn').disabled = false;
        }
        
        function applySelectedColor() {
            if (!selectedColor) return;
            
            // Renk gradientlerini tanımla
            const colorGradients = {
                'blue': 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                'cyan': 'linear-gradient(135deg, #06b6d4, #0891b2)',
                'emerald': 'linear-gradient(135deg, #10b981, #059669)',
                'lime': 'linear-gradient(135deg, #84cc16, #65a30d)',
                'orange': 'linear-gradient(135deg, #f97316, #ea580c)',
                'red': 'linear-gradient(135deg, #ef4444, #dc2626)',
                'violet': 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
                'pink': 'linear-gradient(135deg, #ec4899, #db2777)',
                'slate': 'linear-gradient(135deg, #64748b, #475569)',
                'gray': 'linear-gradient(135deg, #6b7280, #4b5563)',
                'amber': 'linear-gradient(135deg, #fbbf24, #f59e0b)',
                'purple': 'linear-gradient(135deg, #a855f7, #9333ea)'
            };
            
            // Profil fotoğrafı containerlarını bul ve arka planını değiştir
            document.querySelectorAll('.profile-photo-container .bg-primary-100').forEach(container => {
                container.style.background = colorGradients[selectedColor];
                container.classList.remove('bg-primary-100');
            });
            
            // Server'a renk değişikliğini kaydet
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'update_cover_color',
                    color: selectedColor
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    closeColorModal();
                } else {
                    alert('Renk kaydedilirken bir hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Renk kaydedilirken bir hata oluştu.');
            });
        }
        
        // Profil fotoğrafı modal fonksiyonları
        function openPhotoModal() {
            document.getElementById('photoModal').classList.remove('hidden');
        }
        
        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
            // Formu temizle
            document.getElementById('photoUploadForm').reset();
            document.getElementById('photoPreview').classList.add('hidden');
            document.getElementById('photoPlaceholder').classList.remove('hidden');
            document.getElementById('uploadPhotoBtn').disabled = true;
        }
        
        function previewPhoto(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            const uploadBtn = document.getElementById('uploadPhotoBtn');
            
            if (file) {
                // Dosya boyutu kontrolü (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Dosya boyutu 2MB\'dan büyük olamaz.');
                    event.target.value = '';
                    return;
                }
                
                // Dosya türü kontrolü
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Sadece JPG, PNG veya GIF formatında dosyalar yükleyebilirsiniz.');
                    event.target.value = '';
                    return;
                }
                
                // Önizleme göster
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    uploadBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
                uploadBtn.disabled = true;
            }
        }
        
        function uploadPhoto() {
            const form = document.getElementById('photoUploadForm');
            const formData = new FormData(form);
            formData.append('action', 'upload_profile_photo');
            
            // Debug log - kaldırılabilir
            // console.log('BASE_URL:', BASE_URL);
            // console.log('Form data action:', formData.get('action'));
            // console.log('Photo file:', formData.get('photo'));
            // console.log('Remove photo:', formData.get('remove_photo'));
            
            // Yükleme butonunu devre dışı bırak
            const uploadBtn = document.getElementById('uploadPhotoBtn');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Yükleniyor...';
            uploadBtn.disabled = true;
            
            // Fetch isteği
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Debug log - kaldırılabilir
                // console.log('Response status:', response.status);
                // console.log('Response Content-Type:', response.headers.get('Content-Type'));
                
                // Response text'ini önce al
                return response.text().then(text => {
                    // Debug log - kaldırılabilir
                    // console.log('Raw response text:', text);
                    
                    // JSON parse et
                    try {
                        const data = JSON.parse(text);
                        return data;
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 200) + '...');
                    }
                });
            })
            .then(data => {
                // Debug log - kaldırılabilir
                // console.log('Parsed response data:', data);
                
                if (data.status === 'success') {
                    // Profil fotoğraflarını güncelle
                    const profileImages = document.querySelectorAll('.profile-photo-container img');
                    profileImages.forEach(img => {
                        if (data.photo_url) {
                            img.src = data.photo_url + '?t=' + new Date().getTime(); // Cache busting
                        } else {
                            // Varsayılan avatar'a dön
                            const gender = data.gender || 'male';
                            const avatar = (gender === 'female') ? 'avatar-female.jpg' : 'avatar-male.jpg';
                            img.src = BASE_URL + '/assets/images/' + avatar;
                        }
                    });
                    
                    // Header'daki profil fotoğrafını da güncelle
                    const headerImage = document.querySelector('.dropdown button img');
                    if (headerImage) {
                        if (data.photo_url) {
                            headerImage.src = data.photo_url + '?t=' + new Date().getTime();
                        } else {
                            const gender = data.gender || 'male';
                            const avatar = (gender === 'female') ? 'avatar-female.jpg' : 'avatar-male.jpg';
                            headerImage.src = BASE_URL + '/assets/images/' + avatar;
                        }
                    }
                    
                    closePhotoModal();
                    
                    // Başarı mesajı göster
                    showMessage('Profil fotoğrafı başarıyla güncellendi!', 'success');
                } else {
                    alert('Fotoğraf yüklenirken bir hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                // Debug log - kaldırılabilir
                // console.error('Upload error:', error);
                alert('Fotoğraf yüklenirken bir hata oluştu: ' + error.message);
            })
            .finally(() => {
                // Butonu eski haline getir
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
            });
        }
        
        // Mesaj gösterme fonksiyonu
        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            const alertHtml = `
                <div class="${alertClass} px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">${message}</span>
                    <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Main content alanının başına ekle
            const mainContent = document.querySelector('main');
            mainContent.insertAdjacentHTML('afterbegin', alertHtml);
            
            // 5 saniye sonra otomatik kapat
            setTimeout(() => {
                const alert = mainContent.querySelector('.alert-close');
                if (alert) {
                    alert.click();
                }
            }, 5000);
        }
        
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
        
        desktopSidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            document.documentElement.classList.toggle('sidebar-collapsed', isCollapsed);
            
            // Tercih olarak kaydet
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        });
        
        // Sayfa yüklendiğinde önceki tercihi uygula
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof(Storage) !== "undefined") {
                const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
                if (collapsed) {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                }
            }
        });
        
        // Mobile sidebar toggle
        document.getElementById('menuBtn')?.addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.remove('-translate-x-full');
        });
        
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.remove('-translate-x-full');
        });
        
        document.getElementById('closeSidebar')?.addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.add('-translate-x-full');
        });

        // Modal acik/kapali durumuna gore sayfa scroll kilidi
        function syncGlobalModalScrollLock() {
            const anyOpenModal = Array.from(document.querySelectorAll('[id$="Modal"].fixed'))
                .some(modal =>
                    !modal.classList.contains('hidden') &&
                    window.getComputedStyle(modal).display !== 'none'
                );
            document.documentElement.style.overflow = anyOpenModal ? 'hidden' : '';
            document.body.style.overflow = anyOpenModal ? 'hidden' : '';
        }
        const modalObserver = new MutationObserver(syncGlobalModalScrollLock);
        document.querySelectorAll('[id$="Modal"].fixed').forEach((modal) => {
            modalObserver.observe(modal, { attributes: true, attributeFilter: ['class', 'style'] });
        });
        syncGlobalModalScrollLock();
        
        // Close alerts
        document.querySelectorAll('.alert-close').forEach(function(button) {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html> 