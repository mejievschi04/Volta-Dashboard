# Verificare Configurație Nginx pe VPS

## Comenzi pentru a găsi configurația nginx:

### 1. Listează toate fișierele de configurație:
```bash
# Vezi ce fișiere există în sites-enabled
ls -la /etc/nginx/sites-enabled/

# Vezi ce fișiere există în sites-available
ls -la /etc/nginx/sites-available/

# Vezi toate configurațiile nginx
ls -la /etc/nginx/conf.d/
```

### 2. Caută configurația care conține calea proiectului:
```bash
# Caută după /var/www/volta-dashboard
grep -r "/var/www/volta-dashboard" /etc/nginx/ 2>/dev/null

# Caută după volta-dashboard
grep -r "volta-dashboard" /etc/nginx/ 2>/dev/null

# Vezi configurația principală nginx
cat /etc/nginx/nginx.conf
```

### 3. Dacă nu găsești configurația, verifică dacă folosește Apache:
```bash
# Verifică dacă Apache rulează
systemctl status apache2

# Verifică configurațiile Apache
ls -la /etc/apache2/sites-enabled/
grep -r "volta" /etc/apache2/sites-enabled/ 2>/dev/null
```

### 4. Verifică ce web server rulează:
```bash
# Verifică nginx
systemctl status nginx

# Verifică apache
systemctl status apache2

# Verifică ce porturi sunt deschise
netstat -tlnp | grep :80
netstat -tlnp | grep :443
```

### 5. După ce găsești configurația, actualizează proiectul:
```bash
cd /var/www/volta-dashboard
git pull origin main
php artisan cache:clear
php artisan view:clear
php artisan config:clear
chown -R www-data:www-data storage bootstrap/cache
```
