# DB Standardization Checklist (PDO + Performance)

## A) PDO Standardizasyonu

- [x] Ana config baglantisi PDO olarak merkezilendi (`includes/config.php`).
- [ ] `functions/*.php` altindaki MySQLi bagimliliklarini kaldir.
- [ ] `config/db_connect.php` icindeki `getDbConnection()` (MySQLi) kullanimlarini sonlandir.
- [ ] Tum repository/metotlarda prepared statement zorunlu olsun.
- [ ] `PDO::ATTR_EMULATE_PREPARES = false` tum baglantilarda sabit.

## B) Kritik Sorgu Alanlari

- `users`: login ve rol filtreleri (`email`, `role`, `status`)
- `applications`: panel listeleri (`status`, `school_id`, `branch_id`, `user_id`)
- `teacher_profiles`: `user_id`, `branch_id`
- `school_admins`: `school_id`, `user_id`
- `notifications`: `user_id`, `is_read`, `created_at`

## C) Index Kontrol Onerileri

- [ ] `users(role, status)` composite index
- [ ] `applications(status, school_id, created_at)` composite index
- [ ] `applications(user_id, created_at)` composite index
- [ ] `notifications(user_id, is_read, created_at)` composite index
- [ ] `teacher_profiles(user_id)` unique kontrolu (tekrarsiz profil)

## D) Sorgu Performans Rutini

1. En yavas 10 sorguyu logla (`slow query log`).
2. Her sorgu icin `EXPLAIN` ciktisini dokumante et.
3. Table scan yapan sorgulara hedefli index ekle.
4. Gereksiz `SELECT *` kullanimini azalt.
5. Buyuk listelerde pagination zorunlu hale getir.
