# Routing Consistency

Header/Footer link tutarliligi ve kirik route temizligi kapsaminda yapilanlar:

## Yapilanlar

- `includes/header.php`
  - Desktop "Okullar" linki `../admin/schools.php` yerine
    `BASE_URL/php/schools.php` olarak duzeltildi.
- Eksik public sayfalar eklendi:
  - `php/about.php`
  - `php/schools.php`
  - `php/faq.php`
  - `php/privacy.php`
  - `php/terms.php`
  - `php/support.php`

## Neden

- Header/Footer'daki kirik linkler SEO, UX ve guven algisini olumsuz etkiliyordu.
- Public bilgi sayfalari olmadigi icin register/footer akislari yarim kaliyordu.

## Sonraki Adim

- Public sayfalarin icerigi hukuk/iletisim ekipleriyle netlestirilmeli.
- `schools.php` listesine filtreleme ve arama eklenmeli.
- Footer sosyal baglantilar gercek URL'lerle guncellenmeli.
