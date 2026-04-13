# Öğretmen İş Başvuru Sistemi - Teknik Bağlam

## Geliştirme Ortamı
- **Backend**: PHP 8.1+
- **Frontend**: HTML5, CSS3, JavaScript
- **Stil Kütüphanesi**: Tailwind CSS
- **Veritabanı**: MySQL 8.0
- **Sunucu**: Apache
- **Sürüm Kontrolü**: Git

## Mimari Yapı (MVVM)
Projede Model-View-ViewModel (MVVM) mimari deseni kullanılmaktadır:

1. **Model Katmanı**:
   - Veritabanı işlemleri
   - Veri modelleri
   - Doğrulama kuralları
   - Örnek dosyalar: ApplicationModel.php, SchoolModel.php, AdminModel.php, TeacherModel.php

2. **View Katmanı**:
   - Kullanıcı arayüzü bileşenleri
   - Şablonlar
   - HTML içerikleri
   - Örnek dosyalar: admin_layout.php, dashboard.php, applications.php

3. **ViewModel Katmanı**:
   - İş mantığı
   - Model ve View arasındaki bağlantı
   - Veri dönüşümleri
   - Örnek dosyalar: ApplicationViewModel.php, SchoolViewModel.php, AdminViewModel.php, TeacherViewModel.php

## Veritabanı Şeması
- **users**: Sistem kullanıcıları (öğretmenler, okul yöneticileri, admin)
- **schools**: Okul bilgileri
- **branches**: Branş bilgileri
- **applications**: Başvuru bilgileri
- **teacher_profiles**: Öğretmen profil bilgileri
- **teacher_experiences**: Öğretmen deneyim bilgileri
- **teacher_education**: Öğretmen eğitim bilgileri
- **notifications**: Bildirimler
- **school_admins**: Okul-yönetici ilişkileri
- **school_positions**: Okullardaki açık pozisyonlar

## API Yapısı
RESTful API prensipleri takip edilmekte olup, ViewModel sınıfları üzerinden veri alışverişi yapılmaktadır. AJAX çağrıları için JSON formatında veri döndürülmektedir.

## Güvenlik Yapısı
- MD5 ile şifre hashleme
- Session bazlı kimlik doğrulama
- CSRF koruması
- Kullanıcı yetkilendirme kontrolleri
- SQL Injection koruması (PDO parametreleri)

## Performans Optimizasyonu
- Veritabanı sorgu optimizasyonu
- Minimum CSS/JS kullanımı (Tailwind utility-first yaklaşımı)
- Gereksiz sorgu ve yeniden render işlemlerinden kaçınma

## Responsive Tasarım
Tailwind CSS kullanılarak tüm cihazlarda (mobil, tablet, masaüstü) optimum kullanıcı deneyimi sağlanmaktadır. 