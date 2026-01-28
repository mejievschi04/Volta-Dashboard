@echo off
echo ========================================
echo Backup Baze de Date - Dashboard Volta
echo ========================================
echo.

set BACKUP_DIR=backups
set DATE_STR=%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set DATE_STR=%DATE_STR: =0%

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Creare backup pentru: %DATE_STR%
echo.

echo [1/4] Backup dashboard_db...
mysqldump -u root dashboard_db > "%BACKUP_DIR%\dashboard_db_%DATE_STR%.sql"
if %errorlevel% equ 0 (
    echo ✓ Backup dashboard_db completat
) else (
    echo ✗ Eroare la backup dashboard_db
)

echo [2/4] Backup vanzari_1c_db...
mysqldump -u root vanzari_1c_db > "%BACKUP_DIR%\vanzari_1c_db_%DATE_STR%.sql"
if %errorlevel% equ 0 (
    echo ✓ Backup vanzari_1c_db completat
) else (
    echo ✗ Eroare la backup vanzari_1c_db
)

echo [3/4] Backup trafic_db...
mysqldump -u root trafic_db > "%BACKUP_DIR%\trafic_db_%DATE_STR%.sql"
if %errorlevel% equ 0 (
    echo ✓ Backup trafic_db completat
) else (
    echo ✗ Eroare la backup trafic_db
)

echo [4/4] Backup produse_db...
mysqldump -u root produse_db > "%BACKUP_DIR%\produse_db_%DATE_STR%.sql"
if %errorlevel% equ 0 (
    echo ✓ Backup produse_db completat
) else (
    echo ✗ Eroare la backup produse_db
)

echo.
echo ========================================
echo Backup completat!
echo Fisiere salvate in: %BACKUP_DIR%
echo ========================================
pause
