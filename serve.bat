@echo off
REM Doar pentru development local Windows (XAMPP). Pe VPS folosiți web server (nginx/apache).
set "PHP_PATH=C:\xampp\php\php.exe"
if not exist "%PHP_PATH%" (
    echo Eroare: Nu exista %PHP_PATH%
    echo Ruleaza: php artisan serve
    pause
    exit /b 1
)
"%PHP_PATH%" artisan serve
pause
