# Copiere Credențiale Google Analytics pe VPS

## Locația Fișierului Local

**Calea completă:**
```
C:\xampp\htdocs\Dashboard-Volta-Laravel\storage\app\google-analytics\service-account-credentials.json
```

**Calea relativă din proiect:**
```
storage/app/google-analytics/service-account-credentials.json
```

## Metode de Copiere pe VPS

### Metoda 1: Copy-Paste Manual (Recomandat)

#### Pasul 1: Deschide Fișierul Local

1. Navighează la: `C:\xampp\htdocs\Dashboard-Volta-Laravel\storage\app\google-analytics\`
2. Deschide `service-account-credentials.json` cu **Notepad** sau **VS Code**
3. Selectează tot conținutul: `Ctrl+A`
4. Copiază: `Ctrl+C`

#### Pasul 2: Creează Fișierul pe VPS

Pe VPS, rulează:
```bash
cd /var/www/volta-dashboard
mkdir -p storage/app/google-analytics
nano storage/app/google-analytics/service-account-credentials.json
```

#### Pasul 3: Lipește Conținutul

În editorul nano:
1. **Lipește conținutul** (Click dreapta sau Shift+Insert)
2. Verifică că JSON-ul este corect
3. Salvează: `Ctrl+O`, apoi `Enter`
4. Ieși: `Ctrl+X`

#### Pasul 4: Setează Permisiunile

```bash
chmod 600 storage/app/google-analytics/service-account-credentials.json
chown www-data:www-data storage/app/google-analytics/service-account-credentials.json
```

### Metoda 2: SCP (Secure Copy) - Dacă ai acces SSH

De pe mașina ta locală (Windows PowerShell sau CMD):
```powershell
scp C:\xampp\htdocs\Dashboard-Volta-Laravel\storage\app\google-analytics\service-account-credentials.json root@IP_VPS:/var/www/volta-dashboard/storage/app/google-analytics/
```

Apoi pe VPS:
```bash
chmod 600 /var/www/volta-dashboard/storage/app/google-analytics/service-account-credentials.json
chown www-data:www-data /var/www/volta-dashboard/storage/app/google-analytics/service-account-credentials.json
```

### Metoda 3: WinSCP / FileZilla (SFTP)

1. Conectează-te la VPS cu WinSCP sau FileZilla
2. Navighează la: `/var/www/volta-dashboard/storage/app/google-analytics/`
3. Creează directorul dacă nu există: `google-analytics`
4. Drag & drop fișierul `service-account-credentials.json` din Windows în directorul de pe VPS
5. Click dreapta pe fișier > Properties > Setează permisiunile la `600`
6. Setează owner la `www-data:www-data`

## Verificare

După copiere, verifică pe VPS:

```bash
cd /var/www/volta-dashboard

# Verifică că există
ls -la storage/app/google-analytics/service-account-credentials.json

# Verifică permisiunile (ar trebui să fie -rw-------)
stat storage/app/google-analytics/service-account-credentials.json

# Verifică că JSON-ul este valid
cat storage/app/google-analytics/service-account-credentials.json | python3 -m json.tool > /dev/null && echo "✅ JSON valid" || echo "❌ JSON invalid"

# Clear cache
php artisan config:clear
php artisan cache:clear
```

## Conținutul Fișierului

Fișierul conține:
- **Project ID**: micro-verbena-476807-j9
- **Client Email**: ga-data-fetcher@micro-verbena-476807-j9.iam.gserviceaccount.com
- **Private Key**: (cheie criptată)

⚠️ **IMPORTANT**: Acest fișier conține informații sensibile. Nu-l partaja public!

## Următorii Pași

După copierea fișierului:

1. ✅ Verifică că fișierul există pe VPS
2. ✅ Verifică permisiunile (600)
3. ✅ Verifică owner (www-data:www-data)
4. ✅ Verifică că `.env` conține `GA_PROPERTY_ID=281678807`
5. ✅ Asigură-te că service account-ul este adăugat în Google Analytics
6. ✅ Testează sincronizarea din interfață
