# Tests + Docs Bootstrap

Bu adimda minimum test ve dokumantasyon omurgasi olusturuldu:

- Kök proje dokumani: `README.md`
- API sablonu: `docs/api/openapi.yaml`
- Smoke test runner: `tests/smoke/run_smoke_tests.php`
- Test kullanim notu: `tests/README.md`

## Minimum Kabul Kriterleri

- [x] Proje kurulumu ve calistirma adimlari yazili
- [x] Guvenlik/migration notlari dokumante edildi
- [x] API icin baslangic OpenAPI dosyasi var
- [x] En az bir otomatik smoke test scripti var

## Sonraki Adim (Kisa Vade)

- CI pipeline'a smoke test ekle
- Login / role / csrf entegrasyon testleri ekle
- Action bazli API endpointlerini path bazli REST tasarimina tasimaya hazirlik yap
