@echo off
echo ========================================
echo Creare Baza de Date Curata Volta Dashboard
echo ========================================
echo.

cd /d C:\xampp\mysql\bin

if not exist "mysql.exe" (
    echo EROARE: MySQL nu este gasit in C:\xampp\mysql\bin
    echo Verifica calea catre XAMPP!
    pause
    exit /b 1
)

echo [1/5] Creare baza de date volta_dashboard_clean...
mysql -u root -e "DROP DATABASE IF EXISTS volta_dashboard_clean;"
mysql -u root -e "CREATE DATABASE volta_dashboard_clean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo EROARE: Nu s-a putut crea baza de date!
    pause
    exit /b 1
)
echo ✓ Baza de date creata cu succes!
echo.

echo [2/5] Export tabele necesare din volta_db...
mysqldump -u root volta_db ^
  --tables users password_reset_tokens sessions operatori oferte ^
  vanzari_1c plan_vanzari date_op traffic_sources produse ^
  cache cache_locks jobs job_batches failed_jobs migrations ^
  > volta_dashboard_clean_temp.sql 2>nul

if not exist "volta_dashboard_clean_temp.sql" (
    echo EROARE: Nu s-a putut exporta datele!
    pause
    exit /b 1
)
echo ✓ Tabele exportate cu succes!
echo.

echo [3/5] Import date in baza de date curata...
mysql -u root volta_dashboard_clean < volta_dashboard_clean_temp.sql
if errorlevel 1 (
    echo ATENTIE: Au aparut erori la import, dar continuam...
)
echo ✓ Date importate!
echo.

echo [4/5] Verificare date...
mysql -u root volta_dashboard_clean -e "SELECT 'users' as tabel, COUNT(*) as count FROM users UNION ALL SELECT 'vanzari_1c', COUNT(*) FROM vanzari_1c UNION ALL SELECT 'traffic_sources', COUNT(*) FROM traffic_sources UNION ALL SELECT 'operatori', COUNT(*) FROM operatori;"
echo.

echo [5/5] Export baza de date curata pentru server...
mysqldump -u root volta_dashboard_clean > volta_dashboard_clean_export.sql
if errorlevel 1 (
    echo EROARE: Nu s-a putut exporta baza de date!
    pause
    exit /b 1
)
echo ✓ Export creat cu succes!
echo.

echo ========================================
echo FINALIZAT!
echo ========================================
echo.
echo Fisiere create:
echo - volta_dashboard_clean_temp.sql (temporar, poate fi sters)
echo - volta_dashboard_clean_export.sql (pentru server)
echo.
echo Urmatorii pasi:
echo 1. Transfera volta_dashboard_clean_export.sql pe server
echo 2. Pe server: sudo mysql -u root volta_db ^< /tmp/volta_dashboard_clean_export.sql
echo.
pause
