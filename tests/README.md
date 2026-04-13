# Test Omurgasi

Bu klasor minimum smoke test omurgasi icin olusturuldu.

## Calistirma

```bash
php tests/smoke/run_smoke_tests.php
```

Alternatif:

```bash
npm run test:smoke
```

## Onkosullar

- Bu smoke test paketi DB baglantisi gerektirmez.
- Runner, DB baglantisini izole etmek icin varsayilan olarak `APP_ENV=test` ve `SKIP_DB=1` ayarlar.
- DB baglantili senaryolari entegrasyon/manual test katmaninda calistirin.

## Kapsam

- CSRF token uretimi/dogrulamasi (temel helper seviyesi)
- Geri uyumlu sifre dogrulama (md5 + password_hash)

## Sonraki Adim

- Login endpointleri icin entegrasyon testleri
- Rol bazli erisim testleri
- API action bazli smoke testleri (JSON contract)
