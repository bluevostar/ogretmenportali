<?php
require_once dirname(__DIR__) . '/BaseViewModel.php';
require_once dirname(__DIR__) . '/TeacherModel.php';

class TeacherViewModel extends BaseViewModel {
    private $teacherModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->teacherModel = new TeacherModel($db);
    }

    /**
     * View için verileri hazırla
     * @param array $params View için gerekli parametreler
     * @return array View'a iletilecek veriler
     */
    public function prepareViewData($params = []) {
        $action = $params['action'] ?? 'dashboard';
        $teacherId = $this->getCurrentUserId();
        
        if (!$teacherId) {
            return $this->createError('Oturum zaman aşımına uğradı veya giriş yapılmadı.');
        }
        
        // Temel öğretmen bilgilerini al
        $teacherProfile = $this->teacherModel->getTeacherDetailedProfile($teacherId);
        $this->addViewData('profile', $teacherProfile);
        
        // Aksiyon tipi
        $this->addViewData('action', $action);
        
        // Aksiyona göre ek verileri yükle
        switch ($action) {
            case 'dashboard':
                $applications = $this->teacherModel->getTeacherApplications($teacherId);
                $this->addViewData('applications', $applications);
                break;
                
            case 'profile':
                $experiences = $this->teacherModel->getTeacherExperiences($teacherId);
                $education = $this->teacherModel->getTeacherEducation($teacherId);
                $skills = $this->teacherModel->getTeacherSkills($teacherId);
                
                $this->addViewData('experiences', $experiences);
                $this->addViewData('education', $education);
                $this->addViewData('skills', $skills);
                break;
                
            case 'apply':
                $branches = $this->teacherModel->getBranches();
                $this->addViewData('branches', $branches);
                break;
                
            case 'applications':
                $applications = $this->teacherModel->getTeacherApplications($teacherId);
                $this->addViewData('applications', $applications);
                break;
        }
        
        return parent::prepareViewData();
    }
    
    /**
     * Kullanıcı girdilerini işle
     * @param array $input Kullanıcı girdileri
     * @return mixed İşlem sonucu
     */
    public function processInput($input = []) {
        // JSON input kontrolü
        if (empty($input) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $json = file_get_contents('php://input');
            if ($json) {
                $input = json_decode($json, true);
            }
        }
        
        $action = $input['action'] ?? '';
        $teacherId = $this->getCurrentUserId();
        
            if (!$teacherId) {
            return $this->createError('Oturum zaman aşımına uğradı veya giriş yapılmadı.');
        }
        
        switch ($action) {
            case 'update_profile':
                return $this->updateProfile($input, $teacherId);
                
            case 'add_experience':
                return $this->addExperience($input, $teacherId);
                
            case 'add_education':
                return $this->addEducation($input, $teacherId);
                
            case 'update_skills':
                return $this->updateSkills($input, $teacherId);
                
            case 'apply_job':
                return $this->applyJob($input, $teacherId);
                
            case 'update_cover_color':
                return $this->updateCoverColor($input, $teacherId);
                
            default:
                return $this->createError('Geçersiz işlem.');
        }
    }

    /**
     * Profil bilgilerini güncelle
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function updateProfile($input, $teacherId) {
        $required = ['name', 'surname', 'email', 'phone']; // address çıkarıldı
        $validation = $this->validateForm($input, $required);
        if ($validation !== true) {
            // Eksik alanları kullanıcıya göster
            $errorFields = array_keys($validation);
            $errorMessages = implode('<br>', array_values($validation));
            return $this->createError('Lütfen zorunlu alanları doldurun:<br>' . $errorMessages);
        }
        $data = [
            'name' => $input['name'],
            'surname' => $input['surname'],
            'gender' => $input['gender'] ?? 'male',
            'job_title' => $input['job_title'] ?? '',
            'email' => $input['email'],
            'phone' => $input['phone'],
            'address' => $input['address'] ?? '',
            'about' => $input['about'] ?? ''
        ];
        $result = $this->teacherModel->updateTeacherProfile($teacherId, $data);
        if ($result) {
            return $this->createSuccess('Profil bilgileriniz başarıyla güncellendi.');
        } else {
            return $this->createError('Profil güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Deneyim ekle
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function addExperience($input, $teacherId) {
        $required = ['company', 'position', 'start_date'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        $data = [
            'user_id' => $teacherId,
            'company' => $input['company'],
            'position' => $input['position'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'] ?? null,
            'description' => $input['description'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->teacherModel->create($data, 'experiences');
        
        if ($result) {
            return $this->createSuccess('Deneyim başarıyla eklendi.');
        } else {
            return $this->createError('Deneyim eklenirken bir hata oluştu.');
        }
    }

    /**
     * Eğitim bilgisi ekle
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function addEducation($input, $teacherId) {
        $required = ['institution', 'degree', 'start_date'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        $data = [
            'user_id' => $teacherId,
            'institution' => $input['institution'],
            'degree' => $input['degree'],
            'field_of_study' => $input['field_of_study'] ?? '',
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'] ?? null,
            'description' => $input['description'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->teacherModel->create($data, 'education');
        
        if ($result) {
            return $this->createSuccess('Eğitim bilgisi başarıyla eklendi.');
        } else {
            return $this->createError('Eğitim bilgisi eklenirken bir hata oluştu.');
        }
    }

    /**
     * Yetenekleri güncelle
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function updateSkills($input, $teacherId) {
        // Mevcut becerileri sil
        $deleteQuery = "DELETE FROM teacher_skills WHERE user_id = ?";
        $stmt = $this->db->prepare($deleteQuery);
            $stmt->execute([$teacherId]);
        
        // Yeni becerileri ekle
        $skills = $input['skills'] ?? [];
        
        if (empty($skills)) {
            return $this->createSuccess('Beceriler güncellendi.');
        }
        
        $insertQuery = "INSERT INTO teacher_skills (user_id, name, level, category) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        
        foreach ($skills as $skill) {
            $stmt->execute([
                $teacherId,
                $skill['name'],
                $skill['level'] ?? 3,
                $skill['category'] ?? 'other'
            ]);
        }
        
        return $this->createSuccess('Beceriler başarıyla güncellendi.');
    }
    
    /**
     * İş başvurusu yap
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function applyJob($input, $teacherId) {
        $required = ['school_id', 'branch_id', 'message'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        // CV kontrolü
        $cvPath = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvPath = $this->uploadCV($_FILES['cv'], $teacherId);
            
            if (!$cvPath) {
                return $this->createError('CV yükleme sırasında bir hata oluştu.');
            }
        }
        
        $data = [
            'user_id' => $teacherId,
            'school_id' => $input['school_id'],
            'branch_id' => $input['branch_id'],
            'message' => $input['message'],
            'cv_path' => $cvPath,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->teacherModel->createApplication($data);
        
        if ($result) {
            return $this->createSuccess('Başvurunuz başarıyla alındı.');
        } else {
            return $this->createError('Başvuru yapılırken bir hata oluştu.');
        }
    }

    /**
     * CV yükle
     * @param array $file Yüklenen dosya
     * @param int $teacherId Öğretmen ID
     * @return string|bool Dosya yolu veya false
     */
    private function uploadCV($file, $teacherId) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        $uploadDir = dirname(dirname(__DIR__)) . '/uploads/cv/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'cv_' . $teacherId . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return 'uploads/cv/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Kapak fotoğrafı rengini güncelle
     * @param array $input Form verileri
     * @param int $teacherId Öğretmen ID
     * @return array İşlem sonucu
     */
    private function updateCoverColor($input, $teacherId) {
        $required = ['color'];
        $validation = $this->validateForm($input, $required);
        
        if ($validation !== true) {
            return $this->createError('Form eksik veya hatalı dolduruldu.');
        }
        
        $coverColor = $input['color'];
        $result = $this->teacherModel->updateCoverColor($teacherId, $coverColor);
        
        if ($result) {
            return $this->createSuccess('Kapak fotoğrafı rengi başarıyla güncellendi.');
        } else {
            return $this->createError('Kapak fotoğrafı rengi güncellenirken bir hata oluştu.');
        }
    }
    
    /**
     * Yetkilendirme kontrolü
     * @param array $params Kontrol parametreleri
     * @return bool Yetkili mi?
     */
    public function checkAuthorization($params = []) {
        return $this->checkSession('teacher');
    }
    
    /**
     * Sidebar link sınıfı
     * @param string $linkAction Link aksiyonu
     * @param string $currentAction Mevcut aksiyon
     * @return string CSS sınıfı
     */
    public function getSidebarLinkClass($linkAction, $currentAction) {
        $baseClass = "flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200";
        
        if ($linkAction === $currentAction) {
            return $baseClass . " bg-indigo-50 text-indigo-700";
        }
        
        return $baseClass . " text-gray-700 hover:bg-gray-100";
    }
    
    /**
     * Kullanıcı detaylarını getir
     * @param int $userId Kullanıcı ID
     * @return array Kullanıcı bilgileri
     */
    public function getUserDetails($userId) {
        return $this->teacherModel->getTeacherDetailedProfile($userId);
    }
    
    /**
     * Aksiyon içeriğini dahil et
     * @param string $action Aksiyon adı
     */
    public function includeActionContent($action) {
        $file = dirname(dirname(__DIR__)) . '/components/teacher/' . $action . '.php';
        
        // Component dosyalarında kullanılabilmesi için $teacherViewModel değişkenini tanımla
        $teacherViewModel = $this;
        
        if (file_exists($file)) {
            include $file;
        } else {
            include dirname(dirname(__DIR__)) . '/components/teacher/dashboard.php';
        }
    }

    public function getCurrentUserId() {
        return $_SESSION['teacher_id'] ?? null;
    }
} 