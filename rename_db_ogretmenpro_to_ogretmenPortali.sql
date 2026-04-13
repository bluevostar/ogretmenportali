-- MySQL / MariaDB veritabanı adı taşıma scripti
-- Eski ad: ogretmenpro
-- Yeni ad: ogretmenPortali
--
-- Notlar:
-- 1) MySQL'de doğrudan "RENAME DATABASE" yoktur.
-- 2) Bu script tabloları yeni veritabanına taşır.
-- 3) İşleme başlamadan önce mutlaka yedek alın.

CREATE DATABASE IF NOT EXISTS `ogretmenPortali`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Önce eski veritabanındaki tablo adlarını listeleyin:
-- SELECT TABLE_NAME
-- FROM information_schema.TABLES
-- WHERE TABLE_SCHEMA = 'ogretmenpro';

-- Aşağıdaki örnek satırları her tablo için çoğaltıp çalıştırın:
-- RENAME TABLE `ogretmenpro`.`users` TO `ogretmenPortali`.`users`;
-- RENAME TABLE `ogretmenpro`.`teacher_profiles` TO `ogretmenPortali`.`teacher_profiles`;
-- RENAME TABLE `ogretmenpro`.`applications` TO `ogretmenPortali`.`applications`;
-- RENAME TABLE `ogretmenpro`.`schools` TO `ogretmenPortali`.`schools`;
-- RENAME TABLE `ogretmenpro`.`admin_settings` TO `ogretmenPortali`.`admin_settings`;

-- Taşıma sonrası kontrol:
-- SELECT COUNT(*) AS table_count
-- FROM information_schema.TABLES
-- WHERE TABLE_SCHEMA = 'ogretmenPortali';

-- Her şey doğrulandıktan sonra (opsiyonel) eski veritabanını kaldırın:
-- DROP DATABASE `ogretmenpro`;
