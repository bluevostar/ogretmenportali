# Security Baseline (P0)

Bu dokuman, mevcut P0 guvenlik aciklarinin dogrulamasini ve duzeltme sirasini kesinlestirir.

## 1) Dogrulanan Aciklar

- Hash tutarsizligi: `md5` ve `password_hash` birlikte kullaniliyor.
- CSRF korumasi form ve API katmaninda eksik.
- Session hardening eksik (cookie policy, strict mode, periodik ID rotate).
- Error policy prod ortaminda fazla detay veriyor (`display_errors`, exception mesaji).
- Duz metin DB credential kaynak kodda sabit.

## 2) Uygulanan Acil Duzeltmeler

- `includes/config.php`
  - Session cookie `httponly + samesite=lax + secure(https)` ayarlari eklendi.
  - `session.use_strict_mode` ve 15 dk session ID rotate eklendi.
  - Error policy ortama gore ayrildi (`APP_ENV=development` haric `display_errors=0`).
  - DB config `ENV` oncelikli hale getirildi.
  - CSRF helper fonksiyonlari eklendi (`csrf_input`, `validate_csrf_request`).
  - Geri uyumlu sifre dogrulama/migrasyon fonksiyonlari eklendi.
- `php/login.php`, `php/admin_login.php`, `php/register.php`
  - CSRF kontrolu eklendi.
  - Login formlarina CSRF hidden input eklendi.
  - MD5 -> `password_hash` lazy migration devreye alindi.
- `php/api.php`
  - Uretim hata mesaji sertlestirildi.
  - CSRF icin kademeli enforcement kontrolu eklendi (`CSRF_ENFORCE_API`).

## 3) Kapanmayan Riskler (Siradaki Sprint)

- Tum POST/PUT/DELETE formlarinda CSRF alanlari standardize edilmeli.
- API istemcilerinde `X-CSRF-Token` gonderimi frontend tarafinda zorunlu hale getirilmeli.
- Eski `functions/*.php` katmanindaki md5/mysqli tabanli endpointler devre disi birakilmali.
- Rate limit ve brute-force korumasi login endpointlerine eklenmeli.

## 4) Kesin Duzeltme Sirasi

1. **Auth migration tamamla**: tum md5 hashleri temizle, lazy migration izleme metrikleri ekle.
2. **CSRF tam enforce**: `CSRF_ENFORCE_API=1` + tum formlar tokenli.
3. **Session timeout politikasi**: idle timeout + zorunlu logout.
4. **Secret management**: DB bilgilerini `.env`/sunucu env'e tasiyip koddan temizle.
5. **Error hygiene**: tum endpointlerde guvenli hata sabloni.
