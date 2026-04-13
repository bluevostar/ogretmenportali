# Controller Refactor Plan (Admin/Country/Teacher)

Mevcut panel dosyalarinda action routing, business logic ve response handling ayni dosyada toplaniyor.
Hedef, katmanlari ayrik hale getirip bakimi kolaylastirmaktir.

## Mevcut Sorun

- `php/admin_panel.php`: cok sayida action if bloklari ve JSON/form response karisimi.
- `php/country_admin_panel.php`: benzer action bloklari tekrari.
- `php/teacher_panel.php`: upload, debug, UI render ayni dosyada.

## Hedef Katmanlar

1. `Controller` (request validation + response)
2. `Service` (is kurallari)
3. `Repository` (PDO sorgulari)
4. `ViewModel/View` (sadece sunum)

## Oncelikli Parcalama (Sprint bazli)

### Sprint 1

- `PanelActionRouter` olustur:
  - `POST_JSON`, `POST_FORM`, `GET_PAGE` ayir.
- `AdminUserController`:
  - add/edit/toggle user islemleri.
- `SchoolController`:
  - school CRUD + status toggle.

### Sprint 2

- `SettingsController`:
  - general/email/security/system settings.
- `ApplicationController`:
  - approve/reject/export.
- `SchoolAdminController`:
  - school admin CRUD.

### Sprint 3

- `TeacherProfileController`:
  - profil guncelleme + media upload.
- Debug endpointlerini kaldir veya sadece `APP_ENV=development` iken ac.

## Dosya Organizasyon Onerisi

- `php/controllers/admin/*.php`
- `php/controllers/country_admin/*.php`
- `php/controllers/teacher/*.php`
- `services/*.php`
- `repositories/*.php`

## Teknik Kurallar

- Her controller metodu tek bir response turu donmeli (JSON veya redirect).
- Request body parse islemi merkezi helper uzerinden yapilmali.
- CSRF ve role guard controller girisinde standart middleware benzeri katmanda calismali.
