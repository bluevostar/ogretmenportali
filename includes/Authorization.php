<?php
/**
 * Authorization Class
 * Kullanıcı rolleri ve izinlerini yönetir
 */
class Authorization {
    private $db;
    private $role;
    private $permissions;

    public function __construct($db) {
        $this->db = $db;
        $this->permissions = [];
    }

    /**
     * Kullanıcı rolü için izinleri yükle
     */
    public function loadPermissionsForRole($role) {
        $this->role = $role;
        
        // Rol bazlı izinleri tanımla
        switch ($role) {
            case 'admin':
                $this->permissions = [
                    'all' => true, // Admin her şeye erişebilir
                    'manage_users' => true,
                    'manage_schools' => true,
                    'manage_applications' => true,
                    'manage_country_admins' => true,
                    'manage_school_admins' => true,
                    'view_reports' => true,
                    'system_settings' => true,
                ];
                break;
                
            case 'country_admin':
                $this->permissions = [
                    'manage_country_admin_area' => true,
                    'view_applications' => true,
                    'view_schools' => true,
                    'manage_school_admins' => true,
                    'view_reports' => true,
                ];
                break;
                
            case 'school_admin':
                $this->permissions = [
                    'manage_school_admin_area' => true,
                    'view_applications' => true,
                    'view_teachers' => true,
                    'manage_applications' => true,
                ];
                break;
                
            case 'teacher':
                $this->permissions = [
                    'view_own_profile' => true,
                    'edit_own_profile' => true,
                    'submit_application' => true,
                    'view_own_applications' => true,
                ];
                break;
                
            default:
                $this->permissions = [];
        }
        
        // İzinleri session'a ekle
        $_SESSION['permissions'] = $this->permissions;
    }

    /**
     * Belirli bir izne sahip mi kontrol et
     */
    public function hasPermission($permission) {
        // Admin her şeye erişebilir
        if (isset($this->permissions['all']) && $this->permissions['all'] === true) {
            return true;
        }
        
        return isset($this->permissions[$permission]) && $this->permissions[$permission] === true;
    }

    /**
     * Birden fazla izne sahip mi kontrol et
     */
    public function hasAnyPermission($permissions) {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Tüm izinlere sahip mi kontrol et
     */
    public function hasAllPermissions($permissions) {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Kullanıcı rolünü kontrol et
     */
    public function hasRole($role) {
        return $this->role === $role;
    }

    /**
     * Birden fazla rolden birine sahip mi kontrol et
     */
    public function hasAnyRole($roles) {
        return in_array($this->role, $roles);
    }

    /**
     * Mevcut rolü getir
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * Tüm izinleri getir
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * İzin kontrolü yap ve yetki yoksa yönlendir
     */
    public function requirePermission($permission, $redirectUrl = null) {
        if (!$this->hasPermission($permission)) {
            if ($redirectUrl === null) {
                $redirectUrl = BASE_URL . '/php/unauthorized.php';
            }
            redirect($redirectUrl);
            exit;
        }
    }

    /**
     * Rol kontrolü yap ve yetki yoksa yönlendir
     */
    public function requireRole($role, $redirectUrl = null) {
        if (!$this->hasRole($role)) {
            if ($redirectUrl === null) {
                $redirectUrl = BASE_URL . '/php/unauthorized.php';
            }
            redirect($redirectUrl);
            exit;
        }
    }

    /**
     * Giriş yapmış kullanıcı kontrolü
     */
    public function requireLogin($redirectUrl = null) {
        if (!is_logged_in()) {
            if ($redirectUrl === null) {
                $redirectUrl = BASE_URL . '/php/login.php';
            }
            redirect($redirectUrl);
            exit;
        }
    }

    /**
     * Belirli bir kullanıcının kaynağına erişim izni var mı?
     */
    public function canAccessResource($resourceType, $resourceId, $userId) {
        // Admin her şeye erişebilir
        if ($this->hasPermission('all')) {
            return true;
        }

        // Kendi kaynağına erişebilir
        if ($userId == $_SESSION['user_id']) {
            return true;
        }

        // Diğer durumlar için özel kontroller
        switch ($resourceType) {
            case 'application':
                // School admin kendi okulunun başvurularını görebilir
                if ($this->hasRole('school_admin')) {
                    // Burada okul kontrolü yapılabilir
                    return true;
                }
                break;
                
            case 'profile':
                // Profil düzenleme genellikle sadece kendi profili
                return $userId == $_SESSION['user_id'];
                
            default:
                return false;
        }

        return false;
    }
}
