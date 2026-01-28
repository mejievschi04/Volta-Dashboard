@echo off
echo ========================================
echo Actualizare .env pentru volta_db
echo ========================================
echo.

if not exist ".env" (
    echo Eroare: Fișierul .env nu există!
    echo Creează un fișier .env bazat pe .env.example
    pause
    exit /b 1
)

echo Verificare configurație actuală...
findstr /C:"DB_DATABASE=" .env
echo.

echo Actualizare DB_DATABASE=volta_db...
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=volta_db' | Set-Content .env"

echo.
echo Verificare configurație actualizată...
findstr /C:"DB_DATABASE=" .env
echo.

echo ========================================
echo Actualizare completă!
echo ========================================
echo.
echo Următorii pași:
echo 1. Rulează: php artisan config:clear
echo 2. Rulează: php artisan migrate:verify
echo.
pause
