# Öğretmen İş Başvuru Sistemi - Sistem Desenleri

## MVVM (Model-View-ViewModel) Deseni
Uygulama MVVM mimarisi ile tasarlanmıştır. Bu desen, kullanıcı arayüzü ile iş mantığı ve veri erişim kodlarını ayrı tutmayı ve bunlar arasında net bir ayrım yapmayı sağlar.

### Uygulama Örneği:
- **Model**: `SchoolModel.php` - Veritabanı işlemleri
- **ViewModel**: `SchoolViewModel.php` - İş mantığı ve veri dönüşümleri
- **View**: `components/admin/schools.php` - Kullanıcı arayüzü

## Singleton Deseni
Veritabanı bağlantısı gibi tekil olması gereken nesneler için Singleton deseni kullanılmıştır.

### Uygulama Örneği:
```php
class Database {
    private static $instance = null;
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PDO(...);
        }
        return self::$instance;
    }
}
```

## Observer Deseni
Bildirim sistemi Observer deseni ile geliştirilmiştir. Kullanıcılar belirli olayları (başvuru durumu değişikliği gibi) takip eder ve ilgili olaylar gerçekleştiğinde bildirim alırlar.

### Uygulama Örneği:
`ApplicationViewModel` sınıfındaki `createNotification` ve `notifySchoolAdmins` metotları

## Repository Deseni
Veritabanı işlemleri Repository deseni kullanılarak geliştirilmiş ve `BaseModel` sınıfından türetilmiştir. Bu sayede CRUD işlemleri merkezi olarak yönetilmektedir.

### Uygulama Örneği:
`BaseModel` sınıfı ve bu sınıftan türetilen `ApplicationModel`, `SchoolModel` gibi model sınıfları

## Strategy Deseni
Form doğrulaması gibi farklı stratejilerin uygulanması gereken durumlarda Strategy deseni kullanılmıştır.

### Uygulama Örneği:
`BaseViewModel` sınıfındaki `validateForm` metodu

## Factory Metot Deseni
Farklı türdeki nesnelerin oluşturulması için Factory metot deseni kullanılmıştır.

### Uygulama Örneği:
```php
class ViewModelFactory {
    public static function create($type, $db) {
        switch ($type) {
            case 'admin':
                return new AdminViewModel($db);
            case 'application':
                return new ApplicationViewModel($db);
            case 'school':
                return new SchoolViewModel($db);
            default:
                throw new Exception("Unknown ViewModel type");
        }
    }
}
```

## Facade Deseni
Karmaşık alt sistemleri basitleştirmek için Facade deseni kullanılmıştır.

### Uygulama Örneği:
`ApplicationViewModel` sınıfı, başvuru işlemleriyle ilgili karmaşık alt sistemleri basit bir arayüz arkasında gizler.

## Dependency Injection
Nesneler arası bağımlılıkları azaltmak için Dependency Injection kullanılmıştır.

### Uygulama Örneği:
```php
class SchoolViewModel extends BaseViewModel {
    private $schoolModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->schoolModel = new SchoolModel($db);
    }
}
```

## Chain of Responsibility
Form doğrulama ve kullanıcı yetkilendirme işlemleri için Chain of Responsibility deseni kullanılmıştır.

### Uygulama Örneği:
`BaseViewModel` sınıfındaki `checkAuthorization` ve türetilmiş sınıflardaki `checkAuthorization` metotları 