# Öğretmen İş Başvuru Sistemi

Bu proje, öğretmenlerin iş başvurusu yapabilecekleri, okul yöneticilerinin başvuruları değerlendirebilecekleri ve sistem yöneticilerinin tüm süreci yönetebilecekleri kapsamlı bir web uygulamasıdır.

## Proje Mimarisi

Bu projede MVVM (Model-View-ViewModel) mimarisi kullanılmaktadır:

- **Model**: Veritabanı işlemleri ve veri manipülasyonu
- **View**: Kullanıcı arayüzü bileşenleri (HTML, CSS)
- **ViewModel**: Model ve View arasındaki iletişimi sağlayan katman

Klasör Yapısı:
```
├── assets/               # Statik dosyalar (resimler, fontlar)
├── components/           # UI bileşenleri
│   ├── admin/           # Admin paneli bileşenleri
│   ├── school_admin/    # Okul yöneticisi bileşenleri
│   └── teacher/         # Öğretmen bileşenleri
├── config/               # Yapılandırma dosyaları
├── css/                  # CSS dosyaları
├── database/             # Veritabanı işlemleri ve SQL dosyaları
├── functions/            # Yardımcı fonksiyonlar
├── includes/             # Dahil edilen dosyalar (header, footer)
├── js/                   # JavaScript dosyaları
├── models/               # Veri modelleri
│   └── viewmodels/      # ViewModel sınıfları
├── templates/            # Şablonlar
├── uploads/              # Yüklenen dosyalar
└── views/                # Görünüm dosyaları
```

## Özellikler

- **Kullanıcı Rolleri**: Öğretmen, Okul Yöneticisi ve Sistem Yöneticisi
- **Öğretmenler İçin**:
  - Profil oluşturma ve CV yükleme
  - İş başvurusu yapma
  - Başvuru durumunu takip etme
- **Okul Yöneticileri İçin**:
  - Branşa göre başvuruları görüntüleme
  - Başvuruları onaylama veya reddetme
- **Sistem Yöneticileri İçin**:
  - Tüm başvuruları görüntüleme ve onaylama
  - Onaylanan başvuruların Excel çıktısını alma
  - Kullanıcı ve okul yönetimi

## Teknolojiler

- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Backend**: PHP
- **Veritabanı**: MySQL (phpMyAdmin)
- **Diğer**: Font Awesome, Responsive Design

## Kurulum

1. Projeyi sunucunuza klonlayın veya indirin
2. `database/create_tables.sql` dosyasını phpMyAdmin üzerinden veritabanına import edin
3. `includes/config.php` dosyasındaki veritabanı bilgilerini düzenleyin
4. CV yüklemeleri için `uploads/cv` klasörüne yazma izni (chmod 755 veya 777) verin

## Kullanım

### Öğretmenler İçin

1. Kayıt olun ve profilinizi oluşturun
2. CV'nizi yükleyin
3. Açık iş pozisyonlarını görüntüleyin ve başvurun
4. Başvuru durumunuzu takip edin

### Okul Yöneticileri İçin

1. Gelen başvuruları görüntüleyin
2. Başvuruları değerlendirin (onaylayın veya reddedin)
3. Onaylanan başvuruları takip edin

### Sistem Yöneticileri İçin

1. Tüm başvuruları görüntüleyin
2. Son onayları verin
3. Onaylanan başvuruların raporlarını alın
4. Kullanıcıları ve okulları yönetin

## Örnek Kullanıcılar

Sistem varsayılan olarak aşağıdaki test kullanıcılarıyla kurulur:

- **Sistem Yöneticisi**
  - E-posta: admin@ogretmenPortali.com
  - Şifre: admin123

- **Okul Yöneticisi**
  - E-posta: mudur@okul.com
  - Şifre: mudur123

- **Öğretmen**
  - E-posta: ayse@gmail.com
  - Şifre: ayse123

## SOLID Prensipleri

Bu projede aşağıdaki SOLID prensipleri uygulanmıştır:

- **Single Responsibility Principle (SRP)**: Her sınıf tek bir sorumluluğa sahiptir.
- **Open/Closed Principle (OCP)**: Sınıflar genişletmeye açık, değişime kapalıdır.
- **Liskov Substitution Principle (LSP)**: Alt sınıflar üst sınıfların yerine geçebilir.
- **Interface Segregation Principle (ISP)**: İstemcilere ihtiyaç duymadıkları arayüzleri uygulamaları zorlanmaz.
- **Dependency Inversion Principle (DIP)**: Yüksek seviyeli modüller düşük seviyeli modüllere bağlı değildir.

## Güvenlik

- Kullanıcı şifreleri MD5 ile hash'lenir
- Oturum yönetimi ve yetkilendirme kontrolleri
- Form doğrulamaları

## Katkıda Bulunma

1. Projeyi fork edin
2. Feature branch oluşturun (`git checkout -b feature/YeniOzellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik: Açıklama'`)
4. Branch'inize push edin (`git push origin feature/YeniOzellik`)
5. Pull Request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakın.

## İletişim

Sorularınız veya önerileriniz için: info@ogretmenPortali.com 