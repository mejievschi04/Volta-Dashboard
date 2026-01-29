# Setup Credențiale Google Analytics pe VPS

## Problema
Fișierul de credențiale Google Analytics nu există pe server, ceea ce cauzează eroarea 500 la sincronizare.

## Soluție

### Pasul 1: Creează Directorul pe VPS

```bash
cd /var/www/volta-dashboard
mkdir -p storage/app/google-analytics
chown -R www-data:www-data storage/app/google-analytics
chmod -R 775 storage/app/google-analytics
```

### Pasul 2: Obține Fișierul de Credențiale

Ai două opțiuni:

#### Opțiunea A: Dacă ai fișierul local
1. Copiază fișierul `service-account-credentials.json` de pe mașina ta locală
2. Transferă-l pe VPS folosind SCP sau SFTP:

```bash
# De pe mașina ta locală (Windows PowerShell sau CMD)
scp C:\calea\către\service-account-credentials.json root@IP_VPS:/var/www/volta-dashboard/storage/app/google-analytics/

# SAU folosind WinSCP sau FileZilla (SFTP)
# Conectează-te la VPS și copiază fișierul în:
# /var/www/volta-dashboard/storage/app/google-analytics/
```

#### Opțiunea B: Dacă nu ai fișierul, creează-l din Google Cloud Console

1. Accesează [Google Cloud Console](https://console.cloud.google.com/)
2. Selectează proiectul tău (sau creează unul nou)
3. Mergi la **IAM & Admin** > **Service Accounts**
4. Click pe **Create Service Account**
5. Completează:
   - **Name**: `volta-ga4-service`
   - **Description**: `Service account pentru sincronizare GA4`
6. Click **Create and Continue**
7. Acordă rolul: **Viewer** (sau **Analytics Viewer** dacă este disponibil)
8. Click **Done**
9. Click pe service account-ul creat
10. Mergi la tab-ul **Keys**
11. Click **Add Key** > **Create new key**
12. Selectează **JSON**
13. Click **Create** - fișierul JSON va fi descărcat automat
14. Redenumește fișierul în `service-account-credentials.json`

### Pasul 3: Adaugă Service Account în Google Analytics

1. Accesează [Google Analytics](https://analytics.google.com/)
2. Mergi la **Admin** (⚙️)
3. Selectează property-ul tău (ID: 281678807)
4. În secțiunea **Property**, click pe **Property access management**
5. Click **+** > **Add users**
6. Adaugă email-ul service account-ului (găsești-l în fișierul JSON la `client_email`)
7. Acordă rolul **Viewer**
8. Click **Add**

### Pasul 4: Transferă Fișierul pe VPS

```bash
# De pe mașina ta locală
scp service-account-credentials.json root@IP_VPS:/var/www/volta-dashboard/storage/app/google-analytics/
```

### Pasul 5: Setează Permisiunile Corecte pe VPS

```bash
cd /var/www/volta-dashboard
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
chmod 600 storage/app/google-analytics/service-account-credentials.json

# Verifică
ls -la storage/app/google-analytics/service-account-credentials.json
```

Ar trebui să vezi ceva de genul:
```
-rw------- 1 www-data www-data 2345 Jan 28 12:00 service-account-credentials.json
```

### Pasul 6: Verifică Structura Fișierului JSON

Fișierul JSON trebuie să conțină următoarele câmpuri:

```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "volta-ga4-service@your-project.iam.gserviceaccount.com",
  "client_id": "...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "..."
}
```

### Pasul 7: Verifică Configurația .env

```bash
cd /var/www/volta-dashboard
nano .env
```

Asigură-te că ai:
```env
GA_PROPERTY_ID=281678807
```

### Pasul 8: Clear Cache și Testează

```bash
cd /var/www/volta-dashboard
php artisan config:clear
php artisan cache:clear

# Testează în tinker
php artisan tinker
```

În tinker:
```php
$service = app(\App\Services\GoogleAnalyticsService::class);
$data = $service->fetchTrafficData('2026-01-01', '2026-01-31');
print_r($data);
exit
```

Dacă funcționează, ar trebui să vezi date din Google Analytics.

### Pasul 9: Testează Sincronizarea din Interfață

Accesează pagina de trafic în dashboard și încearcă să sincronizezi datele. Eroarea 500 ar trebui să dispară.

## Verificare Finală

```bash
# Verifică că fișierul există
ls -la /var/www/volta-dashboard/storage/app/google-analytics/service-account-credentials.json

# Verifică permisiunile
stat /var/www/volta-dashboard/storage/app/google-analytics/service-account-credentials.json

# Verifică log-urile după o sincronizare
tail -n 50 /var/www/volta-dashboard/storage/logs/laravel.log | grep "GA"
```

## Note de Securitate

⚠️ **IMPORTANT:**
- Fișierul de credențiale conține informații sensibile
- Nu-l comite în Git (ar trebui să fie deja în `.gitignore`)
- Folosește permisiuni restrictive (600) - doar owner poate citi/scrie
- Nu partaja acest fișier public

## Troubleshooting

### Eroare: "Permission denied"
```bash
chown -R www-data:www-data storage/app/google-analytics
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### Eroare: "Invalid credentials"
- Verifică că fișierul JSON este valid: `cat storage/app/google-analytics/service-account-credentials.json | python3 -m json.tool`
- Verifică că service account-ul are acces în Google Analytics

### Eroare: "Property ID not found"
- Verifică că `GA_PROPERTY_ID=281678807` este în `.env`
- Rulează `php artisan config:clear`
