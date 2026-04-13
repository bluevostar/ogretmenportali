# Plan To-do Uygulama Raporu (2026-03-17)

Bu dokuman, atanan 5 to-do icin kod tabaninda yapilan inceleme sonucunu ve ilk duzeltme onceliklerini icerir.

## 1) inventory-auth-flow

### Auth / yetkilendirme envanteri (tek liste)

- `includes/config.php`
  - Session guvenligi, `is_logged_in()`, `check_role()`, CSRF helperlari.
  - Sifre dogrulama uyumlulugu: `verify_password_compat()` ile `password_verify` + `md5` fallback.
  - Basarili login sonrasi hash yenileme: `upgrade_password_hash_if_needed()`.
- `php/login.php`
  - Teacher / school_admin / country_admin girisi.
  - `validate_csrf_request()` var.
  - `users` tablosu + `verify_password_compat` kullanimi.
- `php/admin_login.php`
  - Admin / country_admin girisi.
  - `validate_csrf_request()` var.
  - `users` tablosu + `verify_password_compat` kullanimi.
- `php/register.php`
  - Yeni kayitta `password_hash`.
  - `validate_csrf_request()` var.
- `php/admin_panel.php`, `php/country_admin_panel.php`, `php/school-admin-panel.php`, `php/teacher_panel.php`
  - Session+role kontrolu ile panel korumasi.
- `php/api.php`
  - Login zorunlulugu var.
  - CSRF POST/PUT/DELETE icin **opsiyonel enforce** (`CSRF_ENFORCE_API` flag).
- Legacy auth noktasi:
  - `functions/auth.php` -> `md5` ile login/register.
  - `php/update_password.php` -> `md5` bazli sifre degisimi.
  - `functions/users.php`, `php/add_country_admin.php` -> `md5` gecisi.

### Legacy vs modern ayrimi

- **Modern akim (aktif panel akislari):**
  - `users` tablosu, `password_hash/password_verify`, compat fallback.
- **Legacy akim (riskli/kismi aktif):**
  - `teachers` veya eski yardimci fonksiyonlar, dogrudan `md5`.
- **Kritik not:**
  - Ayni projede iki hash modeli birlikte yasiyor; bu durum bakim yukunu ve guvenlik riskini artiriyor.

## 2) map-role-routes

### Role panel action-list vs UI link eslesmesi

- **Teacher panel (`php/teacher_panel.php`)**
  - Allowed action: `dashboard`, `profile`, `messages`, `settings`.
  - UI menu linkleri bu listeyle eslesiyor.
  - **Uyumsuz linkler var:** `components/teacher/dashboard.php` icinde `?action=apply`, `?action=applications`, `?action=application_detail` linkleri mevcut; panel allowed listesinde yok.
- **School admin panel (`php/school-admin-panel.php`)**
  - Allowed action: `dashboard`, `applications`, `teachers`, `settings`, `profile`.
  - **UI tarafinda karsiliksiz actionlar:**
    - `export_teachers`
    - `view_teacher`
    - `get_teacher_detail`
    - `update_profile`, `change_password`, `update_notifications` (settings form actionlari)
- **Admin panel (`php/admin_panel.php`)**
  - Sidebar ve allowed actionlar genel olarak uyumlu.
  - `teachers` icin `users/add_user/edit_user` alias mantigi var.
  - Ancak bazi admin component action cagirilari backend handler ile eslesmiyor (asagida #3).
- **Country admin panel (`php/country_admin_panel.php`)**
  - Allowed action: `dashboard`, `applications`, `schools`, `branches`, `school_admins`, `users`, `teachers`, `settings`.
  - **UI tarafinda karsiliksiz actionlar:**
    - `get_application`, `approve_application`, `reject_application`, `export_applications`
    - `delete_selected_school_admins`, `delete_school_admin`
    - `get_user_details`
    - `bulk_add_schools`, `delete_selected_schools`, `delete_branches`
    - `add_school_admin` (link var, route handler yok)

## 3) verify-missing-actions

### UI cagiriyor, backend eksik (dogrulanmis liste)

- **Admin panel**
  - `get_application_stats` (`components/admin/dashboard.php`)
  - `delete_selected_applications` (`components/admin/applications.php`)
  - `export_applications` (`components/admin/applications.php`)
  - `view_application` (`components/admin/applications.php`)
- **School admin panel**
  - `export_teachers`, `view_teacher`, `get_teacher_detail` (`components/school-admin/teachers.php`)
  - `update_profile`, `change_password`, `update_notifications` (`components/school-admin/settings.php`)
- **Country admin panel**
  - `get_application`, `approve_application`, `reject_application`, `export_applications` (`components/country_admin/applications.php`)
  - `delete_selected_school_admins`, `delete_school_admin` (`components/country_admin/school_admins.php`)
  - `get_user_details` (`components/country_admin/users.php`)
  - `bulk_add_schools`, `delete_selected_schools` (`components/country_admin/schools.php`)
  - `delete_branches` (`components/country_admin/branches.php`)

### Etki

- UI butonlarinin "calisiyor gorunup islem yapmamasi" riski.
- AJAX tarafinda sessiz basarisizlik/404/unsupported response riski.

## 4) csrf-coverage-check

### CSRF korumasi olan mutasyon girisleri

- `php/login.php`
- `php/admin_login.php`
- `php/register.php`
- `php/api.php` (POST/PUT/DELETE icin kontrol var; enforce flag'e bagli)

### CSRF korumasi eksik veya tutarsiz mutasyon girisleri

- Panel endpointleri:
  - `php/admin_panel.php` (cok sayida POST/JSON action)
  - `php/country_admin_panel.php` (POST/JSON actionlar)
  - `php/school-admin-panel.php` (POST/JSON actionlar)
  - `php/teacher_panel.php` (profil foto, ayar, JSON islemler)
- Diger mutasyon scriptleri:
  - `php/save_application.php`, `php/save_education.php`, `php/save_skill.php`, `php/save_experience.php`
  - `php/update_profile.php`, `php/update_password.php`
  - `php/upload_cv.php`, `php/upload_profile_image.php`, `php/update_profile_photo.php`
  - `php/verify_email.php`, `php/edit-profile.php`, `php/contact.php`

### Kisa sonuc

- CSRF korumasi login/register tarafinda var, ancak panel ve AJAX mutasyon endpointlerinde genis kapsamli ve zorunlu degil.

## 5) doc-gap-summary

### README / OpenAPI ile gercek davranis farklari

- `README.md` API dokumani olarak `docs/api/openapi.yaml` dosyasini isaretliyor; ancak OpenAPI sadece `php/api.php` icin cok genel bir sablon sunuyor.
- Gercek sistemde mutasyonlarin buyuk bolumu `admin_panel.php`, `country_admin_panel.php`, `school-admin-panel.php`, `teacher_panel.php` action endpointlerinde donuyor; OpenAPI bunu kapsamiyor.
- OpenAPI `419 CSRF` cevabini tanimliyor; pratikte `api.php` icinde CSRF enforce ortam flag'ine bagli, panel endpointlerinin cogu ise CSRF check yapmiyor.
- README "md5 -> password_hash migration" notu iceriyor; kod tabaninda hala birden fazla aktif/legacy `md5` yazma-dogrulama noktasi mevcut.
- Dokumanda role bazli action endpoint listesi yok; UI-backend uyumsuzluklari (karsiliksiz actionlar) dokumanda gorunmuyor.

## Onerilen ilk uygulama sirasi

1. Eksik action endpointleri: UI'de aktif butonlarin cagridigi ama backend'de olmayan actionlari kapat ya da implement et.
2. Panel CSRF zorunlulugu: `admin_panel/country_admin_panel/school-admin-panel/teacher_panel` mutasyonlarina ortak CSRF middleware ekle.
3. Teacher route temizlik: `apply/applications/application_detail` gibi karsiliksiz action linklerini tek route yapisina cek.
4. Legacy auth temizligi: `md5` kullanan kalan yazma/dogrulama noktalarini modern akisa tasi.
5. Dokumantasyon hizalama: gercek action endpoint listesi + CSRF gereksinimleri + rol bazli erisim matrisini README/OpenAPI'ye ekle.

