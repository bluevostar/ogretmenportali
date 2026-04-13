# PHPMailer Manuel Kurulum Rehberi

SSL sertifika sorunu nedeniyle Composer kullanamıyorsanız, PHPMailer'ı manuel olarak kurabilirsiniz.

## Adım 1: PHPMailer'ı İndirin

1. Tarayıcınızdan şu adrese gidin:
   ```
   https://github.com/PHPMailer/PHPMailer/releases/latest
   ```

2. **Source code (zip)** dosyasını indirin (örnek: `PHPMailer-6.9.1.zip`)

## Adım 2: Klasör Yapısını Oluşturun

Proje klasörünüzde şu klasör yapısını oluşturun:
```
ogretmenPortali/
  vendor/
    PHPMailer/
      PHPMailer/
        (PHPMailer dosyaları buraya)
```

## Adım 3: Dosyaları Çıkarın

1. İndirdiğiniz ZIP dosyasını açın
2. İçindeki `PHPMailer` klasörünü bulun
3. Bu klasörü `c:\AppServ\www\ogretmenPortali\vendor\PHPMailer\` konumuna kopyalayın

Sonuç:
```
c:\AppServ\www\ogretmenPortali\vendor\PHPMailer\PHPMailer\
  ├── src/
  │   ├── PHPMailer.php
  │   ├── SMTP.php
  │   ├── Exception.php
  │   └── ...
  └── ...
```

## Adım 4: Autoload Dosyası Oluşturun

`vendor/autoload.php` dosyasını oluşturun:

```php
<?php
// vendor/autoload.php
spl_autoload_register(function ($class) {
    // PHPMailer namespace'i için
    if (strpos($class, 'PHPMailer\\PHPMailer\\') === 0) {
        $class = str_replace('PHPMailer\\PHPMailer\\', '', $class);
        $file = __DIR__ . '/PHPMailer/PHPMailer/src/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
```

## Alternatif: Basit Autoload (Daha Kolay)

Eğer yukarıdaki autoload çalışmazsa, `includes/email_helper.php` dosyasını güncelleyin:

```php
// PHPMailer'ı manuel yükle
$phpmailerPath = dirname(__DIR__) . '/vendor/PHPMailer/PHPMailer/src';
if (file_exists($phpmailerPath . '/PHPMailer.php')) {
    require_once $phpmailerPath . '/PHPMailer.php';
    require_once $phpmailerPath . '/SMTP.php';
    require_once $phpmailerPath . '/Exception.php';
    $phpmailerAvailable = true;
} else {
    $phpmailerAvailable = false;
}
```

## Test

Kurulumdan sonra yeni bir öğretmen kaydı yapın ve e-postanın gidip gitmediğini kontrol edin.
