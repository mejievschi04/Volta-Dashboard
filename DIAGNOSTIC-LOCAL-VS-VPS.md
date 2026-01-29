# Diagnostic: Local Funcționează, VPS Nu

## Verificări Rapide pe VPS

### 1. Verifică Versiunea PHP

```bash
php -v
```

**Local:** Probabil PHP 8.4  
**VPS:** PHP 8.2.30 (confirmat)

**Soluție:** Vezi `FIX-COMPOSER-PHP-VERSION-VPS.md`

### 2. Verifică Extensiile PHP

```bash
php -m | grep -E "openssl|curl|json|pdo_mysql|mbstring|xml|fileinfo|gd"
```

**Verifică că toate sunt instalate pe VPS!**

### 3. Verifică Configurația .env

```bash
cd /var/www/volta-dashboard
cat .env | grep -E "APP_|DB_|GA_"
```

**Compară cu local:**
- `APP_ENV=production` (pe VPS) vs `APP_ENV=local` (local)
- `APP_DEBUG=false` (pe VPS) vs `APP_DEBUG=true` (local)
- `DB_DATABASE` - trebuie să fie același
- `GA_PROPERTY_ID` - trebuie să fie setat

### 4. Verifică Permisiunile Storage

```bash
ls -la storage/
ls -la storage/logs/
ls -la storage/app/
ls -la storage/framework/
```

**Trebuie să fie:**
- Owner: `www-data:www-data`
- Permisiuni: `775` pentru directoare, `664` pentru fișiere

**Corectare:**
```bash
chown -R www-data:www-data storage/
chmod -R 775 storage/
```

### 5. Verifică Cache-ul Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 6. Verifică Autoloader-ul Composer

```bash
composer dump-autoload
```

### 7. Verifică Log-urile pentru Erori

```bash
tail -n 100 storage/logs/laravel.log
```

**Caută:**
- Erori de sintaxă
- Erori de conexiune la baza de date
- Erori de permisiuni
- Erori de autoloading

### 8. Verifică Baza de Date

```bash
php artisan tinker
```

În tinker:
```php
// Test conexiune
DB::connection()->getPdo();

// Test tabele
Schema::hasTable('traffic_sources');
Schema::hasTable('users');
Schema::hasTable('vanzari_1c');

exit
```

### 9. Verifică Fișierele de Credențiale

```bash
ls -la storage/app/google-analytics/service-account-credentials.json
cat .env | grep GA_PROPERTY_ID
```

### 10. Verifică Sintaxa PHP

```bash
php -l app/Http/Controllers/GoogleAnalyticsController.php
php -l app/Services/GoogleAnalyticsService.php
```

## Probleme Comune: Local vs VPS

### Problema 1: Cache-ul Laravel

**Local:** Cache-ul este șters automat  
**VPS:** Cache-ul poate fi vechi

**Soluție:**
```bash
php artisan optimize:clear
```

### Problema 2: Permisiuni Storage

**Local:** Permisiunile sunt OK automat  
**VPS:** Trebuie setate manual

**Soluție:**
```bash
chown -R www-data:www-data storage/ bootstrap/cache/
chmod -R 775 storage/ bootstrap/cache/
```

### Problema 3: APP_DEBUG

**Local:** `APP_DEBUG=true` - vezi erorile  
**VPS:** `APP_DEBUG=false` - erorile sunt ascunse

**Soluție temporară pentru debugging:**
```bash
nano .env
# Schimbă: APP_DEBUG=true
php artisan config:clear
```

**⚠️ IMPORTANT:** Schimbă înapoi la `false` după debugging!

### Problema 4: Composer Lock File

**Local:** `composer.lock` generat cu PHP 8.4  
**VPS:** PHP 8.2.30

**Soluție:**
```bash
composer update --no-dev --optimize-autoloader --ignore-platform-reqs
```

### Problema 5: Variabile de Mediu

**Local:** `.env` poate avea valori diferite  
**VPS:** `.env` poate lipsi sau avea valori greșite

**Verificare:**
```bash
# Compară cu local
cat .env
```

### Problema 6: Extensii PHP Lipsă

**Local:** Toate extensiile instalate  
**VPS:** Unele extensii pot lipsi

**Verificare:**
```bash
php -m
```

**Instalare (dacă lipsesc):**
```bash
sudo apt install php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd
```

### Problema 7: Web Server User

**Local:** Rulează ca utilizatorul tău  
**VPS:** Rulează ca `www-data`

**Verificare:**
```bash
ps aux | grep php-fpm
ps aux | grep nginx
```

## Script de Diagnosticare Complet

```bash
cd /var/www/volta-dashboard

echo "=== PHP Version ==="
php -v

echo ""
echo "=== PHP Extensions ==="
php -m | grep -E "openssl|curl|json|pdo_mysql|mbstring|xml|fileinfo|gd"

echo ""
echo "=== .env Config ==="
cat .env | grep -E "APP_|DB_|GA_"

echo ""
echo "=== Storage Permissions ==="
ls -la storage/ | head -5

echo ""
echo "=== Composer ==="
composer --version

echo ""
echo "=== Last Errors ==="
tail -n 20 storage/logs/laravel.log
```

## Pași de Rezolvare (în Ordine)

1. ✅ Fix Composer PHP version (vezi `FIX-COMPOSER-PHP-VERSION-VPS.md`)
2. ✅ Clear cache Laravel
3. ✅ Verifică permisiuni storage
4. ✅ Verifică .env (GA_PROPERTY_ID, DB_DATABASE, etc.)
5. ✅ Verifică log-urile pentru erori specifice
6. ✅ Activează temporar APP_DEBUG pentru mai multe detalii
7. ✅ Verifică extensiile PHP
8. ✅ Testează conexiunea la baza de date

## Contact pentru Suport

Dacă problema persistă, trimite output-ul din:
```bash
tail -n 200 storage/logs/laravel.log
```

Și din scriptul de diagnosticare de mai sus.
