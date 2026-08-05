@echo off
cd /d "%~dp0"

:loop
php artisan queue:work --sleep=3 --tries=3
timeout /t 5 /nobreak >nul
goto loop
