@echo off
echo Composer Install - SSL Sorunu Gideriliyor...
echo.

REM Composer'ı SSL doğrulamasını atlayarak çalıştır
php composer.phar config -g secure-http false
php composer.phar config -g disable-tls true
php composer.phar install --no-interaction

echo.
echo Kurulum tamamlandi!
pause
