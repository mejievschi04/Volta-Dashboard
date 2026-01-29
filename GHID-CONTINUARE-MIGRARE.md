# Ghid Continuare Migrare - Volta Dashboard

## 📋 Status Actual

- ✅ Aplicația clonată de pe GitHub
- ✅ PHP 8.2 instalat cu toate extensiile
- ✅ Composer instalat și dependențe instalate
- ✅ Aplicația mutată în `/var/www/volta-dashboard`
- ✅ Configurație Nginx creată
- ✅ MySQL/MariaDB instalat și configurat
- ✅ Baza de date creată
- ✅ Conectare baza de date testată și funcțională
- ⏳ Aplicație actualizată de pe GitHub (dacă e necesar)
- ⏳ Configurare volta_db
- ⏳ Migrări - de rulat
- ⏳ Assets - de build-uit
- ⏳ Optimizare Laravel - de făcut

---

## 🚀 Pașii Rămași (În Ordine)

### 1. Instalare MySQL

```bash
# Instalare MySQL
sudo apt update
sudo apt install -y mysql-server

# Pornire MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Verificare status
sudo systemctl status mysql
```

**Dacă apare eroare:**
- Verifică logs: `sudo journalctl -u mysql.service -n 50`
- Verifică dacă există procese MySQL: `ps aux | grep mysql`
- Oprește procese: `sudo pkill mysql`

---

### 2. Creare Baza de Date și Utilizator

```bash
# Conectare MySQL (fără parolă, folosește socket Unix)
sudo mysql -u root
```

**În MySQL/MariaDB, rulează:**

```sql
-- Creează baza de date
CREATE DATABASE IF NOT EXISTS volta_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Creează utilizatorul
-- Pentru MariaDB (fără WITH mysql_native_password):
CREATE USER IF NOT EXISTS 'volta_user'@'localhost' IDENTIFIED BY '2003ftpfuture';

-- SAU pentru MySQL (cu WITH mysql_native_password):
-- CREATE USER IF NOT EXISTS 'volta_user'@'localhost' IDENTIFIED WITH mysql_native_password BY '2003ftpfuture';

-- Acordă privilegii
GRANT ALL PRIVILEGES ON volta_dashboard.* TO 'volta_user'@'localhost';

-- Aplică modificările
FLUSH PRIVILEGES;

-- Verifică că totul este OK
SELECT user, host, plugin FROM mysql.user WHERE user='volta_user';
SHOW GRANTS FOR 'volta_user'@'localhost';

EXIT;
```

**⚠️ IMPORTANT:** 
- Înlocuiește `2003ftpfuture` cu o parolă sigură (sau păstrează-o dacă e deja setată)
- Notează parola - o vei folosi în `.env`
- **MariaDB:** Nu este necesar să specifici `WITH mysql_native_password` - este implicit
- **MySQL:** Poți specifica `WITH mysql_native_password` pentru compatibilitate

---

### 3. Configurare .env

```bash
# Navighează în directorul aplicației
cd /var/www/volta-dashboard

# Editează .env
sudo nano .env
```

**Asigură-te că ai următoarele setări (FĂRĂ `#` în față - liniile trebuie să fie active!):**

```env
APP_NAME="Volta Dashboard"
APP_ENV=production
APP_KEY=base64:... (ar trebui să fie deja generat)
APP_DEBUG=false
APP_URL=http://IP_VPS_TAU sau http://domeniul-tau.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_dashboard
DB_USERNAME=volta_user
DB_PASSWORD=parola_sigura_aici
```

**⚠️ IMPORTANT:** 
- **NU** pune `#` în fața liniilor DB_* - ele trebuie să fie active!
- Dacă vezi `# DB_HOST=127.0.0.1`, șterge `#` și spațiul: `DB_HOST=127.0.0.1`
- Verifică că nu există spații în jurul `=` (corect: `DB_HOST=127.0.0.1`, greșit: `DB_HOST = 127.0.0.1`)

**Salvează:** `Ctrl+X`, `Y`, `Enter`

**După salvare, verifică:**
```bash
cat .env | grep DB_
```

Ar trebui să vezi liniile FĂRĂ `#` în față:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_dashboard
DB_USERNAME=volta_user
DB_PASSWORD=parola_sigura_aici
```

---

### 4. Test Conectare Baza de Date

```bash
# Test conectare
php artisan tinker
```

**În tinker:**
```php
DB::connection()->getPdo();
exit
```

**✅ Dacă vezi un obiect PDO cu detalii despre conexiune, conectarea funcționează corect!**

Exemplu de output corect:
```
= PDO {#5360
    inTransaction: false,
    attributes: {
      ...
      DRIVER_NAME: "mysql",
      SERVER_VERSION: "10.11.15-MariaDB-...",
      CONNECTION_STATUS: "127.0.0.1 via TCP/IP",
      ...
    },
  }
```

**⚠️ Dacă apare eroare: `SQLSTATE[HY000] [1698] Access denied for user 'root'@'localhost'`**

Această eroare apare de obicei când MySQL/MariaDB folosește autentificare prin socket Unix pentru root. Soluții:

#### Soluția 1: Folosește utilizatorul `volta_user` în loc de `root` (RECOMANDAT)

În fișierul `.env`, asigură-te că folosești utilizatorul creat:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_dashboard
DB_USERNAME=volta_user
DB_PASSWORD=parola_sigura_aici
```

Apoi:
```bash
php artisan config:clear
php artisan tinker
```

#### Soluția 2: Activează autentificare cu parolă pentru root

**IMPORTANT:** Dacă root folosește plugin-ul `unix_socket` sau `auth_socket`, trebuie să-l schimbi explicit.

**Pasul 1: Verifică plugin-ul curent**
```bash
sudo mysql -u root -e "SELECT user, host, plugin FROM mysql.user WHERE user='root';"
```

**Pasul 2: Schimbă plugin-ul și setează parola (pentru MariaDB):**

```bash
# Conectează-te la MariaDB
sudo mysql -u root

# În MariaDB, rulează:
UPDATE mysql.user SET plugin='mysql_native_password', authentication_string=PASSWORD('2003ftpfuture') WHERE user='root' AND host='localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Sau folosește metoda alternativă:**
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY '2003ftpfuture';
UPDATE mysql.user SET plugin='mysql_native_password' WHERE user='root' AND host='localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Pasul 3: Verifică că s-a schimbat:**
```bash
sudo mysql -u root -e "SELECT user, host, plugin FROM mysql.user WHERE user='root';"
```
Ar trebui să vezi `mysql_native_password` în coloana `plugin`.

**Pasul 4: Actualizează `.env`:**
```env
DB_USERNAME=root
DB_PASSWORD=2003ftpfuture
```

**Pasul 5: Testează conectarea cu parola din linia de comandă:**
```bash
# Testează dacă parola funcționează
mysql -u root -p2003ftpfuture -e "SELECT 1;"
```

Dacă funcționează, problema este în configurația Laravel.

**Pasul 6: Verifică fișierul `.env`:**
```bash
# Verifică că parola este corectă în .env
cat .env | grep DB_
```

Asigură-te că ai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_dashboard
DB_USERNAME=root
DB_PASSWORD=2003ftpfuture
```

**⚠️ IMPORTANT:** Verifică că nu există spații în jurul valorilor în `.env`!

**Pasul 7: Clear toate cache-urile și testează:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan tinker
```

**Dacă încă nu funcționează:**

1. **Verifică dacă există un fișier `.env` duplicat sau `.env.backup`**
2. **Verifică permisiunile fișierului `.env`**: `ls -la .env`
3. **Folosește utilizatorul `volta_user`** (Soluția 1) - este mai sigură și mai simplă

#### Soluția 3: Verifică configurația MySQL

```bash
# Verifică că MySQL rulează
sudo systemctl status mysql

# Verifică că baza de date există
sudo mysql -u root -e "SHOW DATABASES;"

# Verifică că utilizatorul există și privilegiile
sudo mysql -u root -e "SELECT user, host, plugin FROM mysql.user WHERE user='volta_user';"
sudo mysql -u root -e "SHOW GRANTS FOR 'volta_user'@'localhost';"
```

---

### 5. Backup Date Existente (IMPORTANT!)

**Înainte de a șterge aplicația, fă backup la toate datele importante:**

```bash
# Navighează în directorul aplicației
cd /var/www/volta-dashboard

# 1. Backup configurație .env (dacă există)
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "✓ Backup .env creat"
fi

# 2. Backup baze de date existente
# Verifică ce baze de date există
sudo mysql -u root -p2003ftpfuture -e "SHOW DATABASES;" | grep -E "(dashboard|vanzari|trafic|produse|volta)"

# Creează director pentru backup-uri
mkdir -p /root/backups_volta_$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups_volta_$(date +%Y%m%d_%H%M%S)"
cd $BACKUP_DIR

# Backup pentru fiecare bază de date (dacă există)
mysqldump -u root -p2003ftpfuture dashboard_db > dashboard_db.sql 2>/dev/null && echo "✓ Backup dashboard_db" || echo "✗ dashboard_db nu există"
mysqldump -u root -p2003ftpfuture vanzari_1c_db > vanzari_1c_db.sql 2>/dev/null && echo "✓ Backup vanzari_1c_db" || echo "✗ vanzari_1c_db nu există"
mysqldump -u root -p2003ftpfuture trafic_db > trafic_db.sql 2>/dev/null && echo "✓ Backup trafic_db" || echo "✗ trafic_db nu există"
mysqldump -u root -p2003ftpfuture produse_db > produse_db.sql 2>/dev/null && echo "✓ Backup produse_db" || echo "✗ produse_db nu există"
mysqldump -u root -p2003ftpfuture volta_db > volta_db.sql 2>/dev/null && echo "✓ Backup volta_db" || echo "✗ volta_db nu există"

echo "Backup-urile sunt în: $BACKUP_DIR"
```

---

### 6. Ștergere Aplicație Existente și Pull Curat

**⚠️ ATENȚIE:** Acești pași vor șterge aplicația existentă. Asigură-te că ai făcut backup!

```bash
# Navighează în directorul aplicației
cd /var/www

# Oprește temporar Nginx (opțional, pentru siguranță)
# sudo systemctl stop nginx

# Șterge directorul aplicației
sudo rm -rf volta-dashboard

# Clonează aplicația de pe GitHub
sudo git clone https://github.com/mejievschi04/Volta-Dashboard.git volta-dashboard

# Setează proprietarul corect
sudo chown -R www-data:www-data /var/www/volta-dashboard

# Navighează în directorul aplicației
cd /var/www/volta-dashboard

# Fix Git ownership issue (dacă apare eroare "dubious ownership")
sudo git config --global --add safe.directory /var/www/volta-dashboard

# Fix Git ownership issue (dacă apare eroare "dubious ownership")
sudo git config --global --add safe.directory /var/www/volta-dashboard

# Instalează dependențele Composer
# ⚠️ IMPORTANT: Nu rulează Composer ca root! Folosește utilizatorul normal sau www-data
# Opțiunea 1: Rulează ca utilizator normal (dacă ești logat ca user, nu root)
composer install --no-dev --optimize-autoloader

# Opțiunea 2: Dacă ești root, rulează ca www-data
sudo -u www-data composer install --no-dev --optimize-autoloader

# ⚠️ Dacă apare eroare: "Your lock file does not contain a compatible set of packages"
# sau "requires php >=8.4 -> your php version (8.2.30) does not satisfy that requirement"
# Actualizează composer.lock pentru PHP 8.2:
sudo -u www-data composer update --no-dev --optimize-autoloader --ignore-platform-reqs
# SAU dacă vrei să forțezi compatibilitatea cu PHP 8.2:
sudo -u www-data composer update --no-dev --optimize-autoloader --with-all-dependencies

# Copiază .env.example la .env (dacă există)
if [ -f .env.example ]; then
    cp .env.example .env
    echo "✓ Fișier .env creat din .env.example"
else
    touch .env
    echo "✓ Fișier .env creat"
fi

# Generează APP_KEY
php artisan key:generate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**⚠️ IMPORTANT:** 
- Înlocuiește `URL_REPO_TAU` cu URL-ul real al repository-ului tău de pe GitHub
- Dacă repository-ul este privat, asigură-te că ai configurat SSH keys sau token-uri

---

### 7. Configurare .env pentru volta_db

Actualizează `.env` pentru a folosi `volta_db`:

```bash
cd /var/www/volta-dashboard
sudo nano .env
```

**Actualizează configurația bazei de date:**

```env
APP_NAME="Volta Dashboard"
APP_ENV=production
APP_KEY=base64:... (ar trebui generat de key:generate)
APP_DEBUG=false
APP_URL=http://IP_VPS_TAU sau http://domeniul-tau.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_db
DB_USERNAME=root
DB_PASSWORD=2003ftpfuture

# Dacă există date vechi în baze multiple și vrei să le migrezi, păstrează aceste variabile:
DB_DATABASE_DASHBOARD=dashboard_db
DB_DATABASE_VANZARI=vanzari_1c_db
DB_DATABASE_TRAFIC=trafic_db
DB_DATABASE_PRODUSE=produse_db
```

**⚠️ IMPORTANT:** 
- **NU** pune `#` în fața liniilor DB_* - ele trebuie să fie active!
- Verifică că nu există spații în jurul `=` (corect: `DB_HOST=127.0.0.1`, greșit: `DB_HOST = 127.0.0.1`)

**Salvează:** `Ctrl+X`, `Y`, `Enter`

**Verifică configurația:**
```bash
cat .env | grep DB_
```

Ar trebui să vezi liniile FĂRĂ `#` în față:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_db
DB_USERNAME=root
DB_PASSWORD=2003ftpfuture
```

---

### 8. Creare Baza de Date volta_db

```bash
# Conectează-te la MySQL
sudo mysql -u root

# În MySQL/MariaDB:
CREATE DATABASE IF NOT EXISTS volta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

### 9. Eliminare Referințe la Conexiuni Multiple (IMPORTANT!)

**⚠️ PROBLEMĂ:** Dacă aplicația încă încearcă să folosească conexiuni multiple (`dashboard`, `vanzari`, `trafic`, `produse`), trebuie să elimini toate referințele din cod.

**Verifică și corectează:**

```bash
cd /var/www/volta-dashboard

# 1. Caută toate referințele la conexiuni multiple în cod
grep -r "connection('dashboard')" app/
grep -r "connection('vanzari')" app/
grep -r "connection('trafic')" app/
grep -r "connection('produse')" app/

# 2. Caută în modele
grep -r "protected \$connection" app/Models/
```

**Fișiere care trebuie actualizate:**

1. **`app/Http/Controllers/GoogleAnalyticsController.php`** - înlocuiește:
   ```php
   // GREȘIT:
   \DB::connection('trafic')->rollBack();
   
   // CORECT:
   \DB::rollBack();
   ```

2. **Verifică toate controlerele** - înlocuiește:
   ```php
   // GREȘIT:
   DB::connection('dashboard')->...
   DB::connection('vanzari')->...
   DB::connection('trafic')->...
   DB::connection('produse')->...
   
   // CORECT:
   DB::...  // folosește conexiunea default (volta_db)
   ```

3. **Verifică modelele** - elimină:
   ```php
   // GREȘIT:
   protected $connection = 'dashboard';
   protected $connection = 'vanzari';
   protected $connection = 'trafic';
   protected $connection = 'produse';
   
   // CORECT:
   // Nu specifica $connection - folosește default (volta_db)
   ```

**Script automat pentru corectare (rulează pe server):**

```bash
cd /var/www/volta-dashboard

# 1. Corectează modelele - elimină protected $connection
sed -i "s/protected \$connection = 'dashboard';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/User.php
sed -i "s/protected \$connection = 'vanzari';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/Vanzari.php
sed -i "s/protected \$connection = 'vanzari';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/DateOp.php
sed -i "s/protected \$connection = 'vanzari';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/PlanVanzari.php
sed -i "s/protected \$connection = 'trafic';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/TrafficSource.php
sed -i "s/protected \$connection = 'produse';/\/\/ Removed: uses default connection (volta_db)/g" app/Models/Produs.php

# 2. Corectează controlerele - înlocuiește DB::connection('...') cu DB::
sed -i "s/DB::connection('vanzari')/DB::/g" app/Http/Controllers/Api/VanzariLunareController.php
sed -i "s/DB::connection('vanzari')/DB::/g" app/Http/Controllers/UploadVanzariController.php
sed -i "s/\\\\DB::connection('trafic')/\\\\DB::/g" app/Http/Controllers/GoogleAnalyticsController.php
sed -i "s/DB::connection('trafic')/DB::/g" app/Http/Controllers/GoogleAnalyticsController.php

# 3. Verifică modificările
echo "=== Verificare modele ==="
grep -n "protected \$connection" app/Models/*.php || echo "✓ Nu mai există referințe în modele"

echo "=== Verificare controlere ==="
grep -n "connection('vanzari')" app/Http/Controllers/**/*.php || echo "✓ Nu mai există referințe la vanzari"
grep -n "connection('trafic')" app/Http/Controllers/**/*.php || echo "✓ Nu mai există referințe la trafic"
grep -n "connection('dashboard')" app/Http/Controllers/**/*.php || echo "✓ Nu mai există referințe la dashboard"
grep -n "connection('produse')" app/Http/Controllers/**/*.php || echo "✓ Nu mai există referințe la produse"
```

**După actualizări:**
```bash
# Clear cache (ignoră erorile despre tabele inexistente - vor fi create la migrare)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache clear poate da eroare dacă tabelul cache nu există încă - este normal
php artisan cache:clear 2>/dev/null || echo "Tabelul cache nu există încă - va fi creat la migrare"
```

---

### 10. Eliminare Conexiuni Multiple din config/database.php

**⚠️ IMPORTANT:** Dacă `config/database.php` încă conține conexiuni multiple (`dashboard`, `vanzari`, `trafic`, `produse`), trebuie să le elimini!

**Verifică:**
```bash
grep -n "'dashboard'\|'vanzari'\|'trafic'\|'produse'" config/database.php
```

**Dacă găsește conexiuni multiple, elimină-le:**

```bash
cd /var/www/volta-dashboard

# Backup config/database.php
cp config/database.php config/database.php.backup

# Elimină conexiunile multiple (comentă sau șterge liniile)
# Deschide fișierul și elimină secțiunile pentru 'dashboard', 'vanzari', 'trafic', 'produse'
nano config/database.php
```

**Sau folosește sed pentru a elimina automat:**

```bash
# Creează un script temporar pentru a elimina conexiunile
cat > /tmp/remove_connections.sh << 'EOF'
#!/bin/bash
FILE="config/database.php"

# Găsește liniile de start și end pentru fiecare conexiune
# Șterge blocul 'dashboard' dacă există
sed -i "/'dashboard' => \[/,/],/d" "$FILE"

# Șterge blocul 'vanzari' dacă există
sed -i "/'vanzari' => \[/,/],/d" "$FILE"

# Șterge blocul 'trafic' dacă există
sed -i "/'trafic' => \[/,/],/d" "$FILE"

# Șterge blocul 'produse' dacă există
sed -i "/'produse' => \[/,/],/d" "$FILE"

echo "Conexiunile multiple au fost eliminate"
EOF

chmod +x /tmp/remove_connections.sh
cd /var/www/volta-dashboard
/tmp/remove_connections.sh

# Verifică că au fost eliminate
grep -n "'dashboard'\|'vanzari'\|'trafic'\|'produse'" config/database.php || echo "✓ Conexiunile multiple au fost eliminate"
```

**După eliminare:**
```bash
php artisan config:clear
rm -f bootstrap/cache/config.php

# ⚠️ IMPORTANT: Verifică SESSION_CONNECTION în .env
# Dacă există SESSION_CONNECTION=dashboard, elimină-l sau schimbă-l!
grep -n "SESSION_CONNECTION" .env

# Dacă găsește SESSION_CONNECTION=dashboard, elimină linia sau comentează-o:
sed -i "s/^SESSION_CONNECTION=dashboard/# SESSION_CONNECTION=mysql/g" .env
# SAU elimină complet:
sed -i "/^SESSION_CONNECTION=dashboard/d" .env

# IMPORTANT: Verifică și elimină protected $connection din modele
# Dacă modelul User sau alte modele au protected $connection = 'dashboard', elimină-l!
grep -n "protected \$connection" app/Models/*.php

# Dacă găsește, elimină liniile:
sed -i "s/protected \$connection = 'dashboard';/\/\/ Removed: uses default connection/g" app/Models/User.php
sed -i "s/protected \$connection = 'vanzari';/\/\/ Removed: uses default connection/g" app/Models/*.php
sed -i "s/protected \$connection = 'trafic';/\/\/ Removed: uses default connection/g" app/Models/*.php
sed -i "s/protected \$connection = 'produse';/\/\/ Removed: uses default connection/g" app/Models/*.php

# Clear cache din nou după modificări
php artisan config:clear
rm -f bootstrap/cache/config.php
```

---

### 11. Rulare Migrări

**⚠️ IMPORTANT:** Rulează migrările ÎNAINTE de a folosi aplicația! Migrările vor crea toate tabelele necesare, inclusiv `cache`, `sessions`, etc.

**ÎNAINTE de a rula migrările, asigură-te că ai eliminat TOATE referințele la conexiuni multiple:**

```bash
cd /var/www/volta-dashboard

# 1. Verifică .env - elimină SESSION_CONNECTION=dashboard
grep "SESSION_CONNECTION" .env
sed -i "/^SESSION_CONNECTION=dashboard/d" .env

# 2. Verifică modelele - elimină protected $connection
grep -r "protected \$connection" app/Models/
sed -i "s/protected \$connection = 'dashboard';/\/\/ Removed/g" app/Models/User.php
sed -i "s/protected \$connection = 'vanzari';/\/\/ Removed/g" app/Models/*.php
sed -i "s/protected \$connection = 'trafic';/\/\/ Removed/g" app/Models/*.php
sed -i "s/protected \$connection = 'produse';/\/\/ Removed/g" app/Models/*.php

# 3. Verifică config/database.php - elimină conexiunile multiple
grep -n "'dashboard'\|'vanzari'\|'trafic'\|'produse'" config/database.php

# 4. Șterge TOATE cache-urile
rm -rf bootstrap/cache/*
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Verifică că nu mai există referințe
echo "=== Verificare finală ==="
grep -r "connection.*dashboard" app/ config/ || echo "✓ Nu mai există referințe la dashboard"
```

**Opțiunea A: Dacă NU există date vechi (instalare nouă):**

```bash
# Rulare migrări normale
php artisan migrate --force
```

**⚠️ IMPORTANT: Verifică migrările!**

Dacă apare eroarea "Database connection [dashboard] not configured", problema este că migrarea încă folosește conexiunea 'dashboard'.

**Verifică migrarea users:**
```bash
cat database/migrations/0001_01_01_000000_create_users_table.php | grep -i "connection\|dashboard"
```

**Dacă găsește `$connection = 'dashboard'` sau `Schema::connection('dashboard')`, corectează:**

```bash
# Editează migrarea
nano database/migrations/0001_01_01_000000_create_users_table.php
```

**Elimină toate referințele la conexiunea 'dashboard':**
- Șterge: `$connection = 'dashboard';`
- Înlocuiește: `Schema::connection($connection)->` cu `Schema::`
- Înlocuiește: `Schema::connection('dashboard')->` cu `Schema::`

**Exemplu corect:**
```php
public function up(): void
{
    // Folosim conexiunea default (volta_db) - nu mai specificăm conexiunea
    if (!Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table) {
            // ...
        });
    }
    // ...
}
```

**După corectare:**
```bash
php artisan config:clear
php artisan migrate --force
```

**⚠️ Dacă apare eroarea pentru alte migrări (ex: "Database connection [trafic] not configured"):**

Corectează TOATE migrările deodată cu acest script:

```bash
cd /var/www/volta-dashboard

# Corectează toate migrările deodată
for file in database/migrations/*.php; do
    # Elimină linia cu $connection = '...';
    sed -i "/\$connection = ['\"]dashboard['\"];/d" "$file"
    sed -i "/\$connection = ['\"]vanzari['\"];/d" "$file"
    sed -i "/\$connection = ['\"]trafic['\"];/d" "$file"
    sed -i "/\$connection = ['\"]produse['\"];/d" "$file"
    
    # IMPORTANT: Înlocuiește Schema::connection($connection)-> cu Schema:: ÎNAINTE de a elimina variabila
    # Astfel evităm eroarea "Undefined variable $connection"
    sed -i "s/Schema::connection(\$connection)->/Schema::/g" "$file"
    sed -i "s/Schema::connection(\$connection)/Schema::/g" "$file"
    
    # Înlocuiește Schema::connection('dashboard')-> cu Schema::
    sed -i "s/Schema::connection('dashboard')->/Schema::/g" "$file"
    sed -i "s/Schema::connection(\"dashboard\")->/Schema::/g" "$file"
    sed -i "s/Schema::connection('dashboard')\./Schema::/g" "$file"
    sed -i "s/Schema::connection(\"dashboard\")\./Schema::/g" "$file"
    
    # Înlocuiește Schema::connection('vanzari')-> cu Schema::
    sed -i "s/Schema::connection('vanzari')->/Schema::/g" "$file"
    sed -i "s/Schema::connection(\"vanzari\")->/Schema::/g" "$file"
    sed -i "s/Schema::connection('vanzari')\./Schema::/g" "$file"
    sed -i "s/Schema::connection(\"vanzari\")\./Schema::/g" "$file"
    
    # Înlocuiește Schema::connection('trafic')-> cu Schema::
    sed -i "s/Schema::connection('trafic')->/Schema::/g" "$file"
    sed -i "s/Schema::connection(\"trafic\")->/Schema::/g" "$file"
    sed -i "s/Schema::connection('trafic')\./Schema::/g" "$file"
    sed -i "s/Schema::connection(\"trafic\")\./Schema::/g" "$file"
    
    # Înlocuiește Schema::connection('produse')-> cu Schema::
    sed -i "s/Schema::connection('produse')->/Schema::/g" "$file"
    sed -i "s/Schema::connection(\"produse\")->/Schema::/g" "$file"
    sed -i "s/Schema::connection('produse')\./Schema::/g" "$file"
    sed -i "s/Schema::connection(\"produse\")\./Schema::/g" "$file"
    
done

# Verifică dacă există migrări care încă folosesc $connection fără definire
echo "=== Verificare variabile \$connection nefolosite ==="
for file in database/migrations/*.php; do
    if grep -q "\$connection" "$file" && ! grep -q "\$connection = " "$file"; then
        echo "⚠️ $file folosește \$connection dar nu o definește - corectează manual!"
        # Încearcă să corecteze automat - înlocuiește Schema::connection($connection) cu Schema::
        sed -i "s/Schema::connection(\$connection)->/Schema::/g" "$file"
        sed -i "s/Schema::connection(\$connection)/Schema::/g" "$file"
    fi
done

# Verifică că au fost corectate
echo "=== Verificare migrări ==="
grep -r "connection.*dashboard\|connection.*vanzari\|connection.*trafic\|connection.*produse" database/migrations/ || echo "✓ Nu mai există referințe la conexiuni multiple"

# Verifică dacă există referințe la $connection care nu mai sunt definite
echo "=== Verificare variabile \$connection ==="
for file in database/migrations/*.php; do
    if grep -q "\$connection" "$file" && ! grep -q "\$connection = " "$file"; then
        echo "⚠️ $file folosește \$connection dar nu o definește - corectează automat..."
        # Încearcă să corecteze automat - înlocuiește Schema::connection($connection) cu Schema::
        sed -i "s/Schema::connection(\$connection)->/Schema::/g" "$file"
        sed -i "s/Schema::connection(\$connection)/Schema::/g" "$file"
        # Dacă încă există $connection fără utilizare, elimină linia
        sed -i "/^\s*\$connection\s*;/d" "$file"
    fi
done
grep -rn "\$connection" database/migrations/ | grep -v "//" || echo "✓ Nu mai există referințe la \$connection"

# Clear cache
php artisan config:clear
rm -f bootstrap/cache/config.php

# Rulează migrările
php artisan migrate --force
```

**Opțiunea B: Dacă EXISTĂ date vechi în baze multiple (migrare date):****

```bash
# Verifică pregătirea sistemului
php artisan migrate:check-readiness

# Rulează migrarea la volta_db (va migra datele din bazele vechi)
php artisan migrate:to-volta-db --force
```

Această comandă va:
1. ✅ Crea baza de date `volta_db` (dacă nu există)
2. ✅ Crea toate tabelele necesare
3. ✅ Migra toate datele din bazele existente în `volta_db`
4. ✅ Păstra integritatea datelor (fără pierdere)

**Dacă apare eroare:**
- Verifică logs: `tail -f storage/logs/laravel.log`
- Verifică permisiuni: `ls -la storage/ bootstrap/cache/`
- Verifică că baza de date există: `sudo mysql -u root -e "SHOW DATABASES;"`

---

### 11. Verificare Date după Migrare

Dacă ai migrat date din baze multiple, verifică că toate datele au fost migrate:

```bash
# Conectează-te la MySQL
sudo mysql -u root

# În MySQL/MariaDB:
USE volta_db;
SHOW TABLES;

# Verifică numărul de înregistrări pentru fiecare tabel
SELECT 'users' as tabel, COUNT(*) as count FROM users
UNION ALL
SELECT 'vanzari_1c', COUNT(*) FROM vanzari_1c
UNION ALL
SELECT 'traffic_sources', COUNT(*) FROM traffic_sources
UNION ALL
SELECT 'produse', COUNT(*) FROM produse
UNION ALL
SELECT 'operatori', COUNT(*) FROM operatori
UNION ALL
SELECT 'oferte', COUNT(*) FROM oferte
UNION ALL
SELECT 'plan_vanzari', COUNT(*) FROM plan_vanzari
UNION ALL
SELECT 'date_op', COUNT(*) FROM date_op;

EXIT;
```

**Testează conectarea din Laravel:**
```bash
php artisan tinker
```

```php
DB::connection()->getPdo();
DB::table('users')->count(); // sau alt tabel
exit
```

---

### 10. Build Assets (dacă există)

```bash
# Instalare dependențe Node.js
npm install

# Build assets pentru producție
npm run build
# SAU
npm run production
```

**Dacă apare eroare:**
- Verifică Node.js: `node --version`
- Verifică npm: `npm --version`
- Instalează Node.js dacă lipsește: `curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - && sudo apt install -y nodejs`

---

### 11. Setare Permisiuni

```bash
# Setare proprietar
sudo chown -R www-data:www-data /var/www/volta-dashboard

# Setare permisiuni
sudo chmod -R 755 /var/www/volta-dashboard
sudo chmod -R 775 /var/www/volta-dashboard/storage
sudo chmod -R 775 /var/www/volta-dashboard/bootstrap/cache
```

---

### 12. Optimizare Laravel

```bash
# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache pentru producție
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

### 13. Verificare Configurație Nginx

```bash
# Verificare configurație
sudo nginx -t

# Verificare că fișierul există
cat /etc/nginx/sites-available/volta-dashboard

# Dacă fișierul este gol, creează-l:
sudo nano /etc/nginx/sites-available/volta-dashboard
```

**Conținut configurație:**

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/volta-dashboard/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Salvează și activează:**

```bash
# Activare
sudo ln -s /etc/nginx/sites-available/volta-dashboard /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test și reload
sudo nginx -t
sudo systemctl reload nginx
```

**⚠️ IMPORTANT:** Verifică versiunea PHP-FPM:
```bash
ls -la /var/run/php/
```

Dacă ai `php8.1-fpm.sock`, schimbă în configurație: `php8.1-fpm.sock`
Dacă ai `php8.4-fpm.sock`, schimbă în configurație: `php8.4-fpm.sock`

---

### 14. Verificare Servicii

```bash
# Verificare status toate serviciile
sudo systemctl status nginx
sudo systemctl status php8.2-fpm  # sau php8.1-fpm, php8.4-fpm
sudo systemctl status mysql

# Dacă nu rulează, pornește-le:
sudo systemctl start nginx
sudo systemctl start php8.2-fpm
sudo systemctl start mysql

# Activează-le la boot:
sudo systemctl enable nginx
sudo systemctl enable php8.2-fpm
sudo systemctl enable mysql
```

---

### 15. Test Aplicație

```bash
# Test local
curl http://localhost
curl -I http://localhost

# Test cu IP-ul VPS-ului
curl http://IP_VPS_TAU

# Verificare logs dacă apare eroare
sudo tail -f /var/log/nginx/error.log
tail -f /var/www/volta-dashboard/storage/logs/laravel.log
```

---

## 🔧 Comenzi Utile pentru Troubleshooting

### Verificare Logs

```bash
# Logs Laravel
tail -f /var/www/volta-dashboard/storage/logs/laravel.log

# Logs Nginx
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# Logs PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log

# Logs MySQL
sudo tail -f /var/log/mysql/error.log
```

### Verificare Permisiuni

```bash
# Verificare permisiuni aplicație
ls -la /var/www/volta-dashboard/
ls -la /var/www/volta-dashboard/storage/
ls -la /var/www/volta-dashboard/bootstrap/cache/

# Setare permisiuni (dacă e necesar)
sudo chown -R www-data:www-data /var/www/volta-dashboard
sudo chmod -R 755 /var/www/volta-dashboard
sudo chmod -R 775 /var/www/volta-dashboard/storage
sudo chmod -R 775 /var/www/volta-dashboard/bootstrap/cache
```

### Clear Cache Laravel

```bash
cd /var/www/volta-dashboard

# Clear toate cache-urile
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Verificare Baza de Date

```bash
# Conectare MySQL
sudo mysql -u root

# În MySQL:
SHOW DATABASES;
USE volta_dashboard;
SHOW TABLES;
EXIT;
```

---

## 🔒 Configurare SSL (Opțional - După Ce Totul Funcționează)

Dacă ai un domeniu configurat:

```bash
# Instalare certificat SSL
sudo certbot --nginx -d domeniul-tau.com -d www.domeniul-tau.com

# Certbot va configura automat HTTPS
```

---

## ✅ Checklist Final

- [ ] MySQL/MariaDB instalat și pornit
- [ ] Aplicație actualizată de pe GitHub (dacă e necesar)
- [ ] Backup făcut pentru bazele de date existente (dacă există date)
- [ ] Baza de date `volta_db` creată
- [ ] Fișier `.env` configurat corect cu `DB_DATABASE=volta_db`
- [ ] Conectare baza de date testată
- [ ] Migrări rulate cu succes (`migrate` sau `migrate:to-volta-db`)
- [ ] Date verificate în `volta_db` (dacă s-a făcut migrare)
- [ ] Assets build-uite (dacă există)
- [ ] Permisiuni setate corect
- [ ] Laravel optimizat
- [ ] Nginx configurat corect
- [ ] PHP-FPM configurat corect
- [ ] Toate serviciile rulează
- [ ] Aplicația accesibilă pe HTTP
- [ ] (Opțional) SSL configurat

---

## 📝 Informații Importante

**Locații:**
- Aplicație: `/var/www/volta-dashboard`
- Configurație Nginx: `/etc/nginx/sites-available/volta-dashboard`
- Logs Laravel: `/var/www/volta-dashboard/storage/logs/laravel.log`
- Logs Nginx: `/var/log/nginx/error.log`

**Versiuni:**
- PHP: 8.2 (verifică cu `php -v`)
- MySQL: 8.0 (verifică cu `mysql --version`)
- Nginx: (verifică cu `nginx -v`)

**Parole și Credențiale:**
- MySQL root: `2003ftpfuture` (sau parola setată)
- MySQL volta_user: (parola setată în pasul 2, dacă folosești)
- Baza de date: `volta_db` (noua versiune unificată)

---

## 🆘 Dacă Apar Probleme

### Eroare: Git "dubious ownership"

**Eroare:** `fatal: detected dubious ownership in repository at '/var/www/volta-dashboard'`

**Soluție:**
```bash
sudo git config --global --add safe.directory /var/www/volta-dashboard
```

---

### Eroare: Composer "lock file does not contain a compatible set of packages"

**Eroare:** `Your lock file does not contain a compatible set of packages. Please run composer update.`
**Eroare:** `requires php >=8.4 -> your php version (8.2.30) does not satisfy that requirement`

**Cauză:** `composer.lock` a fost generat cu PHP 8.4, dar serverul are PHP 8.2.

**Soluții:**

**Opțiunea 1: Actualizează composer.lock pentru PHP 8.2 (RECOMANDAT):**
```bash
cd /var/www/volta-dashboard
sudo -u www-data composer update --no-dev --optimize-autoloader --ignore-platform-reqs
```

**Opțiunea 2: Șterge composer.lock și reinstalează:**
```bash
cd /var/www/volta-dashboard
rm composer.lock
sudo -u www-data composer install --no-dev --optimize-autoloader
```

**Opțiunea 3: Actualizează cu toate dependențele:**
```bash
cd /var/www/volta-dashboard
sudo -u www-data composer update --no-dev --optimize-autoloader --with-all-dependencies
```

**Notă:** După actualizare, verifică că aplicația funcționează corect.

---

### Eroare: Composer "Do not run Composer as root"

**Eroare:** `Do not run Composer as root/super user!`

**Soluție:** Nu rulează Composer ca root. Folosește:
```bash
# Ca utilizator normal
composer install --no-dev --optimize-autoloader

# SAU ca www-data
sudo -u www-data composer install --no-dev --optimize-autoloader
```

---

### Eroare: `SQLSTATE[HY000] [1698] Access denied for user 'root'@'localhost'`

**Cauză:** MySQL/MariaDB folosește autentificare prin socket Unix pentru root, nu prin parolă.

**Soluții:**

1. **Folosește utilizatorul `volta_user`** (recomandat):
   - Verifică că utilizatorul există: `sudo mysql -u root -e "SELECT user, host FROM mysql.user WHERE user='volta_user';"`
   - Asigură-te că în `.env` ai `DB_USERNAME=volta_user` și `DB_PASSWORD=parola_corecta`
   - Rulează: `php artisan config:clear`

2. **Activează autentificare cu parolă pentru root**:
   
   **Pentru MariaDB** (sintaxa diferită):
   ```bash
   sudo mysql -u root
   ```
   ```sql
   -- MariaDB (fără WITH mysql_native_password):
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'parola_root';
   FLUSH PRIVILEGES;
   EXIT;
   ```
   
   **Sau folosește metoda alternativă:**
   ```sql
   SET PASSWORD FOR 'root'@'localhost' = PASSWORD('parola_root');
   FLUSH PRIVILEGES;
   ```
   
   **Pentru MySQL** (dacă nu e MariaDB):
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'parola_root';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Verifică plugin-ul de autentificare**:
   ```bash
   sudo mysql -u root -e "SELECT user, host, plugin FROM mysql.user;"
   ```
   Dacă vezi `auth_socket` pentru root, trebuie să schimbi la autentificare cu parolă (vezi pasul 2).

### Alte Probleme

1. **Verifică logs-urile** - vezi secțiunea "Comenzi Utile pentru Troubleshooting"
2. **Verifică status servicii** - `sudo systemctl status nginx php8.2-fpm mysql`
3. **Verifică permisiuni** - vezi secțiunea "Verificare Permisiuni"
4. **Clear cache Laravel** - vezi secțiunea "Clear Cache Laravel"
5. **Verifică configurația** - `sudo nginx -t` pentru Nginx

---

**Succes cu continuarea migrării! 🚀**
