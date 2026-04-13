Write-Host "Composer Install - SSL Sorunu Gideriliyor..." -ForegroundColor Green
Write-Host ""

# Composer'ı SSL doğrulamasını atlayarak çalıştır
php composer.phar config -g secure-http false
php composer.phar config -g disable-tls true
php composer.phar install --no-interaction

Write-Host ""
Write-Host "Kurulum tamamlandi!" -ForegroundColor Green
Read-Host "Devam etmek icin Enter'a basin"
