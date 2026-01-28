# Migrare la Baza de Date Unică volta_db

## Prezentare Generală

Acest document descrie procesul de migrare a tuturor datelor din bazele de date multiple într-o singură bază de date numită `volta_db`.

## Structura Anterioară

Înainte de migrare, aplicația folosea următoarele baze de date:
- `dashboard_db` - utilizatori, operatori, oferte
- `vanzari_1c_db` - vânzări, plan vânzări, date_op
- `trafic_db` - surse trafic
- `produse_db` - produse

## Structura Nouă

După migrare, toate datele vor fi consolidate într-o singură bază de date:
- `volta_db` - toate tabelele

## Pași de Migrare

### 1. Backup Date Existente

**IMPORTANT:** Înainte de a începe migrarea, asigură-te că ai făcut backup la toate bazele de date existente!

```bash
# Backup pentru fiecare bază de date
mysqldump -u root -p dashboard_db > backup_dashboard_db.sql
mysqldump -u root -p vanzari_1c_db > backup_vanzari_1c_db.sql
mysqldump -u root -p trafic_db > backup_trafic_db.sql
mysqldump -u root -p produse_db > backup_produse_db.sql
```

### 2. Actualizare Configurație .env

Actualizează fișierul `.env` pentru a folosi `volta_db`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Rulare Comandă de Migrare

Rulează comanda Artisan pentru a migra toate datele:

```bash
php artisan migrate:to-volta-db
```

Această comandă va:
1. Crea baza de date `volta_db`
2. Crea toate tabelele necesare
3. Migra toate datele din bazele existente în `volta_db`
4. Păstra integritatea datelor (fără pierdere de date)

### 4. Verificare Date

După migrare, verifică că toate datele au fost migrate corect:

```bash
# Conectează-te la MySQL
mysql -u root -p

# Verifică baza de date
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
SELECT 'oferte', COUNT(*) FROM oferte;
```

### 5. Testare Aplicație

După migrare, testează aplicația pentru a verifica că totul funcționează corect:
- Login utilizatori
- Dashboard cu date
- Rapoarte
- Import vânzări
- Sincronizare Google Analytics

## Tabele Migrate

Următoarele tabele au fost migrate în `volta_db`:

### Din dashboard_db:
- `users`
- `password_reset_tokens`
- `sessions`
- `operatori`
- `oferte`

### Din vanzari_1c_db:
- `vanzari_1c`
- `plan_vanzari`
- `date_op`

### Din trafic_db:
- `traffic_sources`

### Din produse_db:
- `produse`

## Modificări Cod

Următoarele fișiere au fost actualizate pentru a folosi conexiunea default:

### Modele:
- `app/Models/User.php` - eliminat `protected $connection = 'dashboard'`
- `app/Models/Vanzari.php` - eliminat `protected $connection = 'vanzari'`
- `app/Models/PlanVanzari.php` - eliminat `protected $connection = 'vanzari'`
- `app/Models/DateOp.php` - eliminat `protected $connection = 'vanzari'`
- `app/Models/TrafficSource.php` - eliminat `protected $connection = 'trafic'`
- `app/Models/Produs.php` - eliminat `protected $connection = 'produse'`

### Controlere:
- `app/Http/Controllers/Api/VanzariLunareController.php` - actualizat `DB::connection('vanzari')` la `DB::`
- `app/Http/Controllers/GoogleAnalyticsController.php` - actualizat `DB::connection('trafic')` la `DB::`
- `app/Http/Controllers/UploadVanzariController.php` - actualizat conexiunile

### Migrări:
- Toate migrările au fost actualizate să folosească conexiunea default în loc de conexiuni specifice

### Configurație:
- `config/database.php` - eliminat conexiunile multiple, păstrând doar conexiunea default `mysql`

## Rollback (Dacă este Necesar)

Dacă migrarea nu funcționează corect, poți restaura bazele de date originale:

```bash
# Restaurează fiecare bază de date
mysql -u root -p dashboard_db < backup_dashboard_db.sql
mysql -u root -p vanzari_1c_db < backup_vanzari_1c_db.sql
mysql -u root -p trafic_db < backup_trafic_db.sql
mysql -u root -p produse_db < backup_produse_db.sql
```

Apoi revino la configurația anterioară în `.env` și `config/database.php`.

## Note Importante

1. **Nu șterge bazele de date vechi** imediat după migrare. Păstrează-le cel puțin o săptămână pentru a te asigura că totul funcționează corect.

2. **Verifică integritatea datelor** - asigură-te că numărul de înregistrări din `volta_db` corespunde cu suma înregistrărilor din bazele vechi.

3. **Testează toate funcționalitățile** înainte de a considera migrarea completă.

4. **Backup regulat** - continuă să faci backup-uri regulate pentru `volta_db`.

## Suport

Dacă întâmpini probleme în timpul migrării, verifică:
- Log-urile Laravel: `storage/logs/laravel.log`
- Log-urile MySQL pentru erori
- Verifică că toate tabelele au fost create corect
- Verifică că toate datele au fost migrate
