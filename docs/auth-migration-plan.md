# Auth Migration Plan (MD5 -> password_hash)

Bu plan, md5 hashlerden `password_hash/password_verify` standardina geri uyumlu gecisi tarif eder.

## Hedef

- Yeni kayitlarda yalnizca `password_hash(PASSWORD_DEFAULT)` kullanmak.
- Eski md5 hashli kullanicilari sifre degistirmeden kademeli olarak yeni hashe tasimak.
- Login akisini kesintisiz korumak.

## Geçis Stratejisi

### Faz 1 - Backward Compatible Login (Uygulandi)

- Dogrulama sirasinda once `password_verify`, degilse `md5` fallback.
- Basarili md5 login sonrasi ayni request icinde yeni hash ile otomatik update (lazy migration).
- Kod: `verify_password_compat`, `upgrade_password_hash_if_needed`.

### Faz 2 - Operasyonel Temizlik

- MD5 kalan kullanicilari raporla:
  - `SELECT id, email FROM users WHERE password REGEXP '^[a-f0-9]{32}$';`
- Rapor metrigini haftalik izle, kalan md5 sayisini dusur.

### Faz 3 - Zorunlu Kesim

- MD5 fallback kodunu kaldir.
- MD5 kullanan yardimci fonksiyonlari (`functions/*.php`, eski endpointler) kapat.
- Login basarisiz mesajlarini sabit tut, yan kanal bilgi sizmasini engelle.

## Geri Uyumluluk Notu

- Lazy migration sayesinde kullanicinin sifre reset yapmasi zorunlu degildir.
- Hesap kilitleme/rate limit eklendiginde fallback akisi etkilenmez.

## Teknik Kontrol Listesi

- [x] `login.php` md5 fallback + rehash
- [x] `admin_login.php` md5 fallback + rehash
- [x] `register.php` `password_hash` kullaniyor
- [ ] `update_password.php` md5 kullanimini kaldir
- [ ] `functions/auth.php` ve `functions/users.php` md5 kullanimini kaldir
- [ ] admin bulk import sifrelemesini `password_hash` yap
