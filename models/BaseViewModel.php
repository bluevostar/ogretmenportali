<?php
require_once __DIR__ . '/IViewModel.php';

/**
 * Tüm ViewModel sınıfları için temel sınıf
 */
class BaseViewModel implements IViewModel {
    protected $db;
    protected $models = [];
    protected $data = [];
    
    /**
     * Constructor
     * @param PDO $db Veritabanı bağlantısı
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Model yükle
     * @param string $modelName Model sınıfı adı
     * @param string $alias Model için takma ad (opsiyonel)
     * @return BaseModel|null
     */
    protected function loadModel($modelName, $alias = null) {
        $modelFile = __DIR__ . '/' . $modelName . '.php';
        
        if (!file_exists($modelFile)) {
            error_log("Model dosyası bulunamadı: " . $modelFile);
            return null;
        }
        
        require_once $modelFile;
        
        if (!class_exists($modelName)) {
            error_log("Model sınıfı bulunamadı: " . $modelName);
            return null;
        }
        
        $model = new $modelName($this->db);
        $key = $alias ?? $modelName;
        $this->models[$key] = $model;
        
        return $model;
    }
    
    /**
     * Model al
     * @param string $key Model anahtarı
     * @return BaseModel|null
     */
    protected function getModel($key) {
        return $this->models[$key] ?? null;
    }
    
    /**
     * View için veri ekle
     * @param string $key Veri anahtarı
     * @param mixed $value Veri değeri
     */
    protected function addViewData($key, $value) {
        $this->data[$key] = $value;
    }
    
    /**
     * View için verileri hazırla (varsayılan implementasyon)
     * @param array $params View için gerekli parametreler
     * @return array View'a iletilecek veriler
     */
    public function prepareViewData($params = []) {
        return $this->data;
    }
    
    /**
     * Kullanıcı girdilerini işle (varsayılan implementasyon)
     * @param array $input Kullanıcı girdileri
     * @return mixed İşlem sonucu
     */
    public function processInput($input = []) {
        // Alt sınıflar tarafından uygulanacak
        return null;
    }
    
    /**
     * Yetkilendirme kontrolü (varsayılan implementasyon)
     * @param array $params Kontrol parametreleri
     * @return bool Yetkili mi?
     */
    public function checkAuthorization($params = []) {
        // Alt sınıflar tarafından uygulanacak
        return false;
    }
    
    /**
     * Oturum kontrolü
     * @param string $role Kontrol edilecek rol
     * @return bool Geçerli oturum var mı?
     */
    protected function checkSession($role = null) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        if ($role !== null && (!isset($_SESSION['role']) || $_SESSION['role'] !== $role)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Giriş yapan kullanıcının ID'sini al
     * @return int|null
     */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Hata mesajı oluştur
     * @param string $message Hata mesajı
     * @return array
     */
    protected function createError($message) {
        return [
            'status' => 'error',
            'message' => $message
        ];
    }
    
    /**
     * Başarı mesajı oluştur
     * @param string $message Başarı mesajı
     * @param mixed $data Ek veri
     * @return array
     */
    protected function createSuccess($message, $data = null) {
        $result = [
            'status' => 'success',
            'message' => $message
        ];
        
        if ($data !== null) {
            $result['data'] = $data;
        }
        
        return $result;
    }
    
    /**
     * Form verilerini kontrol et
     * @param array $form Formdan gelen veriler
     * @param array $required Gerekli alanlar
     * @return array|bool Hata varsa hata mesajı, yoksa true
     */
    protected function validateForm($form, $required = []) {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($form[$field]) || empty(trim($form[$field]))) {
                $errors[$field] = ucfirst($field) . ' alanı gereklidir.';
            }
        }
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Tek bir kayıt getir
     * @param string $query SQL sorgusu
     * @param array $params Parametreler
     * @return array|false
     */
    protected function fetchOne($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tüm kayıtları getir
     * @param string $query SQL sorgusu
     * @param array $params Parametreler
     * @return array|false
     */
    protected function fetchAll($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
            return [];
        }
    }
} 