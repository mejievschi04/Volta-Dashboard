# Creare Manuală Fișier Credențiale Google Analytics pe VPS

## Pasul 1: Obține Conținutul Fișierului JSON

### Opțiunea A: Din Google Cloud Console (Recomandat)

1. Accesează [Google Cloud Console](https://console.cloud.google.com/)
2. Selectează proiectul (sau creează unul nou)
3. Mergi la **IAM & Admin** > **Service Accounts**
4. Click pe service account-ul existent SAU creează unul nou:
   - Click **Create Service Account**
   - **Name**: `volta-ga4-service`
   - Click **Create and Continue**
   - Acordă rolul **Viewer**
   - Click **Done**
5. Click pe service account-ul creat
6. Mergi la tab-ul **Keys**
7. Click **Add Key** > **Create new key**
8. Selectează **JSON**
9. Click **Create** - fișierul JSON va fi descărcat
10. Deschide fișierul descărcat cu Notepad/Editor de text
11. **Copiază tot conținutul** (Ctrl+A, Ctrl+C)

### Opțiunea B: Dacă ai deja fișierul local

1. Deschide fișierul `service-account-credentials.json` cu Notepad
2. **Copiază tot conținutul** (Ctrl+A, Ctrl+C)

## Pasul 2: Creează Fișierul pe VPS

### Pe VPS, rulează:

```bash
cd /var/www/volta-dashboard
mkdir -p storage/app/google-analytics
nano storage/app/google-analytics/service-account-credentials.json
```

### În editorul nano:

1. **Lipește conținutul** copiat (Click dreapta sau Shift+Insert)
2. Verifică că JSON-ul este corect formatat
3. Salvează: `Ctrl+O` (Write Out), apoi `Enter`
4. Ieși: `Ctrl+X`

## Pasul 3: Setează Permisiunile

```bash
chmod 600 storage/app/google-analytics/service-account-credentials.json
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
```

## Pasul 4: Verifică Fișierul

```bash
# Verifică că există
ls -la storage/app/google-analytics/service-account-credentials.json

# Verifică conținutul (primele linii)
head -5 storage/app/google-analytics/service-account-credentials.json

# Verifică că JSON-ul este valid
cat storage/app/google-analytics/service-account-credentials.json | python3 -m json.tool > /dev/null && echo "✅ JSON valid" || echo "❌ JSON invalid"
```

Ar trebui să vezi ceva de genul:
```
-rw------- 1 www-data www-data 2345 Jan 28 12:00 service-account-credentials.json
```

## Pasul 5: Verifică Configurația .env

```bash
nano .env
```

Asigură-te că ai:
```env
GA_PROPERTY_ID=281678807
```

Dacă nu există, adaugă-l.

## Pasul 6: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Pasul 7: Testează

```bash
php artisan tinker
```

În tinker:
```php
$service = app(\App\Services\GoogleAnalyticsService::class);
$data = $service->fetchTrafficData('2026-01-01', '2026-01-31');
print_r($data);
exit
```

## Formatul JSON Așteptat

Fișierul trebuie să arate astfel:

```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC...\n-----END PRIVATE KEY-----\n",
  "client_email": "volta-ga4-service@your-project.iam.gserviceaccount.com",
  "client_id": "123456789",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/..."
}
```

## Important: Adaugă Service Account în Google Analytics

După ce ai creat fișierul, asigură-te că:

1. Accesează [Google Analytics](https://analytics.google.com/)
2. Mergi la **Admin** (⚙️)
3. Selectează property-ul (ID: 281678807)
4. În **Property**, click pe **Property access management**
5. Click **+** > **Add users**
6. Adaugă email-ul din `client_email` (din fișierul JSON)
7. Acordă rolul **Viewer**
8. Click **Add**

## Troubleshooting

### Eroare: "Permission denied"
```bash
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
chmod 600 storage/app/google-analytics/service-account-credentials.json
```

### Eroare: "JSON invalid"
- Verifică că ai copiat tot conținutul
- Verifică că nu ai caractere invalide
- Testează cu: `python3 -m json.tool storage/app/google-analytics/service-account-credentials.json`

### Eroare: "File not found"
```bash
ls -la storage/app/google-analytics/
# Dacă directorul nu există:
mkdir -p storage/app/google-analytics
```
