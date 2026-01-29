# Fix pentru Sincronizare GA4 pe VPS

## Problema
Eroare 500 la sincronizarea datelor Google Analytics 4 pe serverul VPS.

## Pași de Diagnosticare pe VPS

### 1. Verifică Log-urile Laravel

```bash
cd /var/www/volta-dashboard
tail -n 100 storage/logs/laravel.log | grep -A 20 "GA Sync"
```

### 2. Verifică Existența Fișierului de Credențiale

```bash
# Verifică dacă directorul există
ls -la storage/app/google-analytics/

# Verifică dacă fișierul există
ls -la storage/app/google-analytics/service-account-credentials.json

# Verifică permisiunile
stat storage/app/google-analytics/service-account-credentials.json
```

**Dacă fișierul nu există:**
```bash
# Creează directorul
mkdir -p storage/app/google-analytics

# Copiază fișierul de credențiale (trebuie să-l ai local)
# Exemplu: scp service-account-credentials.json user@vps:/var/www/volta-dashboard/storage/app/google-analytics/

# Setează permisiunile corecte
chown -R www-data:www-data storage/app/google-analytics
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### 3. Verifică Configurația .env

```bash
cd /var/www/volta-dashboard
cat .env | grep GA_PROPERTY_ID
```

**Ar trebui să vezi:**
```
GA_PROPERTY_ID=281678807
```

**Dacă nu există, adaugă-l:**
```bash
nano .env
# Adaugă linia:
GA_PROPERTY_ID=281678807
```

### 4. Verifică Configurația PHP (OpenSSL)

```bash
# Verifică dacă OpenSSL este activat
php -m | grep openssl

# Verifică versiunea PHP
php -v
```

### 5. Testează Conectivitatea la Google API

```bash
cd /var/www/volta-dashboard
php artisan tinker
```

**În tinker:**
```php
$service = app(\App\Services\GoogleAnalyticsService::class);
$service->fetchTrafficData('2026-01-01', '2026-01-31');
exit
```

### 6. Verifică Permisiunile Storage

```bash
cd /var/www/volta-dashboard
chown -R www-data:www-data storage/
chmod -R 775 storage/
```

### 7. Clear Cache Laravel

```bash
cd /var/www/volta-dashboard
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 8. Testează API-ul Direct

```bash
# Obține token CSRF (din browser, inspect element -> Network -> Headers)
# Apoi testează:
curl -X POST "http://stats.volta.md/api/ga/sync?start_date=2026-01-01&end_date=2026-01-31" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_CSRF_TOKEN_HERE" \
  -b "laravel_session=YOUR_SESSION_COOKIE_HERE"
```

## Probleme Comune și Soluții

### Problema 1: "Fișierul de credențiale nu există"
**Soluție:** Încarcă fișierul `service-account-credentials.json` pe server în `storage/app/google-analytics/`

### Problema 2: "Property ID nu este configurat"
**Soluție:** Adaugă `GA_PROPERTY_ID=281678807` în fișierul `.env` pe server

### Problema 3: "Eroare la obținerea token-ului"
**Soluție:** 
- Verifică dacă OpenSSL este activat în PHP
- Verifică dacă fișierul de credențiale este valid JSON
- Verifică dacă service account-ul are permisiuni în Google Analytics

### Problema 4: "Eroare cURL" sau "SSL verify failed"
**Soluție:** 
- Adaugă în `.env`: `GA_SSL_VERIFY=false` (pentru testare)
- Sau instalează certificatele SSL corecte pe server

### Problema 5: "Eroare la cererea către GA4 API"
**Soluție:**
- Verifică dacă Property ID este corect
- Verifică dacă service account-ul are acces la property-ul GA4
- Verifică dacă API-ul Google Analytics Data API este activat în Google Cloud Console

## Verificare Finală

După ce ai rezolvat problemele, testează din nou sincronizarea din interfața web. Eroarea ar trebui să fie mai clară acum și să indice exact ce lipsește.

## Note Importante

1. **Fișierul de credențiale** trebuie să fie accesibil de către utilizatorul web server (de obicei `www-data`)
2. **Property ID** trebuie să fie setat corect în `.env`
3. **Service Account** trebuie să aibă rolul "Viewer" sau "Analyst" în Google Analytics
4. **API-ul Google Analytics Data API** trebuie să fie activat în Google Cloud Console
