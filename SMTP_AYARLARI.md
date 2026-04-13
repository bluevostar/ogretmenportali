# SMTP E-posta Ayarları Rehberi

## 1. PHPMailer Kurulumu (İsteğe Bağlı)

**ÖNEMLİ:** PHPMailer kurulumu **isteğe bağlıdır**. SMTP ayarları yapılmazsa sistem PHP `mail()` fonksiyonunu kullanır. SMTP kullanmak istiyorsanız PHPMailer kurulumu yapın.

### Seçenek A: Composer ile Kurulum (Önerilen)

**Composer yüklü değilse:**

1. **Composer'ı indirin ve kurun:**
   - https://getcomposer.org/download/ adresine gidin
   - Windows için `Composer-Setup.exe` dosyasını indirin ve çalıştırın
   - Kurulum sırasında PHP.exe yolunu seçin (genelde `C:\AppServ\php\php.exe`)

2. **Kurulumdan sonra PowerShell veya CMD'yi yeniden başlatın**

3. **Proje klasöründe çalıştırın:**
   ```powershell
   cd c:\AppServ\www\ogretmenpro
   composer install
   ```

**SSL Sertifika Hatası Alıyorsanız:**

Eğer "certificate verify failed" hatası alıyorsanız, şu çözümleri deneyin:

**Çözüm 1: Composer'ı SSL doğrulamasını atlayarak indirin**
```powershell
cd c:\AppServ\www\ogretmenpro
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -d openssl.cafile= composer-setup.php
php composer.phar install
```

**Çözüm 2: Composer'ı manuel indirin**
1. https://getcomposer.org/download/ adresine gidin
2. "Manual Download" bölümünden `composer.phar` dosyasını indirin
3. Dosyayı proje klasörüne (`c:\AppServ\www\ogretmenpro\`) kopyalayın
4. Şu komutu çalıştırın:
   ```powershell
   cd c:\AppServ\www\ogretmenpro
   php composer.phar install
   ```

**Çözüm 3: OpenSSL sertifikalarını güncelleyin**
1. https://curl.se/ca/cacert.pem adresinden `cacert.pem` dosyasını indirin
2. Dosyayı `C:\AppServ\php\extras\ssl\` klasörüne kopyalayın
3. `php.ini` dosyasını açın (`C:\AppServ\php\php.ini`)
4. Şu satırı bulun ve düzenleyin:
   ```ini
   openssl.cafile=C:\AppServ\php\extras\ssl\cacert.pem
   ```
5. PHP'yi yeniden başlatın

**Composer zaten yüklüyse ama PATH'te değilse:**

```powershell
# Composer'ın tam yolunu kullanın (örnek)
C:\Users\KullaniciAdi\AppData\Roaming\Composer\vendor\bin\composer.bat install
```

### Seçenek B: Composer Olmadan Kullanım

**PHPMailer kurmadan da SMTP kullanabilirsiniz!** Sistem otomatik olarak PHP'nin yerleşik SMTP özelliklerini kullanır. Ancak PHPMailer daha güvenilir ve özellik bakımından zengindir.

**Composer kurmak istemiyorsanız:**
- SMTP ayarlarını admin panelden yapın
- Sistem otomatik olarak PHP `mail()` fonksiyonunu kullanacak
- Yerel sunucuda (localhost) mail gönderimi çalışmayabilir, gerçek sunucuda çalışır

### Seçenek C: Manuel PHPMailer Kurulumu

1. PHPMailer'ı indirin: https://github.com/PHPMailer/PHPMailer/releases
2. `vendor/PHPMailer/PHPMailer/` klasörüne çıkarın
3. `includes/email_helper.php` dosyasındaki autoload satırını güncelleyin

## 2. Admin Panelinden SMTP Ayarları

1. **Admin paneline giriş yapın**
   - URL: `http://localhost/ogretmenpro/php/admin_panel.php`
   - Admin hesabıyla giriş yapın

2. **Ayarlar sekmesine gidin**
   - Sol menüden **"Ayarlar"** tıklayın
   - Üstteki sekmelerden **"E-posta Ayarları"** sekmesini seçin

3. **SMTP bilgilerini girin:**

   | Alan | Açıklama | Örnek Değer |
   |------|----------|-------------|
   | **SMTP Sunucu** | SMTP sunucu adresi | `smtp.gmail.com` veya `smtp.mailtrap.io` |
   | **SMTP Port** | SMTP port numarası | `587` (TLS) veya `465` (SSL) |
   | **SMTP Kullanıcı Adı** | E-posta adresiniz | `ornek@gmail.com` |
   | **SMTP Şifre** | E-posta şifreniz veya uygulama şifresi | `şifreniz` |
   | **Gönderen E-posta** | Gönderen adresi (genelde SMTP kullanıcı adıyla aynı) | `ornek@gmail.com` |
   | **Gönderen Adı** | Gönderen görünen adı | `ÖğretmenPro` |
   | **SSL/TLS Kullan** | Şifreleme türü | ✅ İşaretli (TLS için) |

4. **Kaydet** butonuna tıklayın

## 3. Popüler E-posta Sağlayıcıları için Ayarlar

### Gmail (Google)

```
SMTP Sunucu: smtp.gmail.com
SMTP Port: 587
SMTP Kullanıcı Adı: gmail-adresiniz@gmail.com
SMTP Şifre: Gmail Uygulama Şifresi (2 adımlı doğrulama açık olmalı)
Gönderen E-posta: gmail-adresiniz@gmail.com
Gönderen Adı: ÖğretmenPro
SSL/TLS: ✅ İşaretli
```

**Not:** Gmail için "Uygulama Şifresi" oluşturmanız gerekir:
1. Google Hesabınız → Güvenlik
2. 2 Adımlı Doğrulama'yı açın
3. "Uygulama şifreleri" → Yeni oluştur
4. Oluşturulan şifreyi SMTP Şifre alanına girin

### Outlook / Hotmail

```
SMTP Sunucu: smtp-mail.outlook.com
SMTP Port: 587
SMTP Kullanıcı Adı: outlook-adresiniz@outlook.com
SMTP Şifre: Outlook şifreniz
Gönderen E-posta: outlook-adresiniz@outlook.com
Gönderen Adı: ÖğretmenPro
SSL/TLS: ✅ İşaretli
```

### Yandex Mail

```
SMTP Sunucu: smtp.yandex.com
SMTP Port: 465
SMTP Kullanıcı Adı: yandex-adresiniz@yandex.com
SMTP Şifre: Yandex şifreniz
Gönderen E-posta: yandex-adresiniz@yandex.com
Gönderen Adı: ÖğretmenPro
SSL/TLS: ✅ İşaretli (Port 465 için SSL)
```

### Mailtrap (Test Ortamı)

```
SMTP Sunucu: smtp.mailtrap.io
SMTP Port: 2525
SMTP Kullanıcı Adı: Mailtrap kullanıcı adınız
SMTP Şifre: Mailtrap şifreniz
Gönderen E-posta: test@ogretmenpro.local
Gönderen Adı: ÖğretmenPro
SSL/TLS: ❌ İşaretsiz (Port 2525 için)
```

## 4. Test Etme

SMTP ayarlarını kaydettikten sonra:

1. Yeni bir öğretmen kaydı yapın (`register.php`)
2. E-postanıza doğrulama kodu gelip gelmediğini kontrol edin
3. Eğer mail gelmediyse:
   - Admin panel → Ayarlar → E-posta Ayarları'nda bilgileri kontrol edin
   - Sunucu log dosyalarını kontrol edin: `logs/error.log`
   - PHP hata loglarını kontrol edin

## 5. Sorun Giderme

### Mail gitmiyor

- **SMTP bilgilerini kontrol edin:** Sunucu, port, kullanıcı adı, şifre doğru mu?
- **Port numarasını kontrol edin:** 587 (TLS) veya 465 (SSL)
- **Güvenlik duvarı:** SMTP portları açık mı?
- **Gmail kullanıyorsanız:** "Daha az güvenli uygulamalara izin ver" açık olmalı veya Uygulama Şifresi kullanın

### PHPMailer hatası

- `composer install` çalıştırıldı mı?
- `vendor/` klasörü var mı?
- PHP'de `openssl` ve `mbstring` eklentileri aktif mi?
- **Composer yüklü değilse:** PHPMailer olmadan da çalışır, sadece PHP `mail()` kullanılır

### SMTP ayarları yoksa

SMTP ayarları boşsa sistem otomatik olarak PHP `mail()` fonksiyonunu kullanır. Bu genelde yerel sunucularda çalışmaz, gerçek sunucuda çalışabilir.

## 6. Güvenlik Notları

- SMTP şifresini asla kod içine yazmayın, sadece admin panelden girin
- Gmail gibi servisler için "Uygulama Şifresi" kullanın, normal şifre değil
- Test ortamında Mailtrap gibi servisler kullanın
- Üretim ortamında güvenilir SMTP servisleri kullanın

## 7. Tipografi ve Responsive Font Kullanımı

Bu projede metinlerin tüm cihazlarda (mobil, laptop, büyük ekran) tutarlı ve okunabilir olması için global bir tipografi yaklaşımı kullanılmaktadır.

- `html` etiketinde çözünürlüğe göre ölçeklenen bir taban font boyutu tanımlıdır:
  - `html { font-size: clamp(14px, 0.9vw + 0.6rem, 18px); }`
  - Küçük ekranlarda (mobil) taban font yaklaşık **14px**, laptop ve büyük ekranlarda yaklaşık **18px** seviyesine çıkar.
- Ziyaretçi tarafında (ana site) gövde metni için `Inter`, başlıklar için `Poppins` fontu kullanılır.
- Yönetim panellerinde `Poppins` ailesi ile benzer okunabilirlik hedeflenir.

Yeni sayfa veya bileşen geliştirirken Tailwind CSS tarafında şu prensipler izlenmelidir:

- Gövde metni ve açıklamalar için:
  - Varsayılan: `text-base`
  - Daha rahat okuma gereken alanlarda: `text-base md:text-lg`
- Başlıklar için:
  - Orta seviye başlık: `text-xl md:text-2xl`
  - Ana sayfa / bölüm başlıkları: `text-2xl md:text-3xl`
- Uyarı, etiket vb. ikincil metinler dışında **`text-xs` ve `text-sm`** kullanımı mümkün olduğunca kaçınılmalıdır.

Farklı çözünürlüklerde manuel test yaparken:

- Mobil (≈375px genişlik): Metinler sıkışık görünmemeli, satırlar birbirine çok yaklaşmamalıdır.
- Küçük laptop (≈1366x768): Gövde metni rahat okunmalı, başlıklar belirgin olmalıdır.
- Büyük ekran (≥1920px): Metinler çok küçük kalmamalı, boşluklar (padding/margin) ile birlikte denge korunmalıdır.

Yeni bileşen eklerken bu font ölçekleme yaklaşımına ve Tailwind sınıf önerilerine uyulması, proje genelinde tutarlı bir tipografi sağlar.
