# Ghid Pas cu Pas - Migrare la volta_db

## 📋 Prezentare

Acest ghid te va ajuta să migrezi toate datele din bazele multiple într-o singură bază de date `volta_db`.

## ✅ Verificare Pregătire

Înainte de a începe, verifică dacă sistemul este pregătit:

```bash
php artisan migrate:check-readiness
```

Această comandă va verifica:
- ✓ Conexiunea la MySQL
- ✓ Bazele de date existente și numărul de tabele
- ✓ Dacă `volta_db` există deja

## 🔒 Pasul 1: Backup Date (OBLIGATORIU!)

**IMPORTANT:** Înainte de orice, fă backup la toate bazele de date!

### Opțiunea 1: Script automat (Windows)

Rulează scriptul de backup:
```bash
backup-databases.bat
```

### Opțiunea 2: Backup manual

```bash
# Backup pentru fiecare bază de date
mysqldump -u root -p dashboard_db > backup_dashboard_db.sql
mysqldump -u root -p vanzari_1c_db > backup_vanzari_1c_db.sql
mysqldump -u root -p trafic_db > backup_trafic_db.sql
mysqldump -u root -p produse_db > backup_produse_db.sql
```

**Verifică că backup-urile au fost create cu succes!**

## ⚙️ Pasul 2: Actualizare Configurație .env

Deschide fișierul `.env` și actualizează:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=volta_db
DB_USERNAME=root
DB_PASSWORD=

# Păstrează aceste variabile pentru migrare (vor fi folosite de comandă)
DB_DATABASE_DASHBOARD=dashboard_db
DB_DATABASE_VANZARI=vanzari_1c_db
DB_DATABASE_TRAFIC=trafic_db
DB_DATABASE_PRODUSE=produse_db
```

**Notă:** După migrare, poți elimina variabilele `DB_DATABASE_*` dacă dorești.

## 🚀 Pasul 3: Rulare Migrare

Rulează comanda de migrare:

```bash
php artisan migrate:to-volta-db
```

Această comandă va:
1. ✅ Crea baza de date `volta_db` (dacă nu există)
2. ✅ Crea toate tabelele necesare
3. ✅ Migra toate datele din bazele existente
4. ✅ Păstra integritatea datelor (fără pierdere)

### Opțiuni:

- **Fără confirmare:** `php artisan migrate:to-volta-db --force`
- **Cu confirmare:** `php artisan migrate:to-volta-db` (va întreba pentru fiecare pas)

## ✅ Pasul 4: Verificare Date

După migrare, verifică că toate datele au fost migrate:

```bash
# Conectează-te la MySQL
mysql -u root -p

# Verifică baza de date
USE volta_db;
SHOW TABLES;

# Verifică numărul de înregistrări
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
```

## 🧪 Pasul 5: Testare Aplicație

Testează că aplicația funcționează corect:

1. **Login utilizatori** - verifică autentificarea
2. **Dashboard** - verifică că datele se afișează corect
3. **Rapoarte** - verifică rapoartele
4. **Import vânzări** - testează importul Excel
5. **Sincronizare Google Analytics** - testează sincronizarea

## 🔄 Rollback (Dacă este Necesar)

Dacă migrarea nu funcționează corect, poți restaura bazele originale:

```bash
# Restaurează fiecare bază de date
mysql -u root -p dashboard_db < backup_dashboard_db.sql
mysql -u root -p vanzari_1c_db < backup_vanzari_1c_db.sql
mysql -u root -p trafic_db < backup_trafic_db.sql
mysql -u root -p produse_db < backup_produse_db.sql
```

Apoi revino la configurația anterioară în `.env`.

## 📊 Tabele Migrate

Următoarele tabele vor fi migrate în `volta_db`:

### Din dashboard_db:
- `users` - utilizatori
- `password_reset_tokens` - token-uri resetare parolă
- `sessions` - sesiuni utilizatori
- `operatori` - operatori
- `oferte` - oferte

### Din vanzari_1c_db:
- `vanzari_1c` - vânzări
- `plan_vanzari` - plan vânzări
- `date_op` - date operatori

### Din trafic_db:
- `traffic_sources` - surse trafic

### Din produse_db:
- `produse` - produse

## ⚠️ Note Importante

1. **Nu șterge bazele vechi imediat** - păstrează-le cel puțin o săptămână
2. **Verifică integritatea datelor** - asigură-te că numărul de înregistrări corespunde
3. **Testează toate funcționalitățile** înainte de a considera migrarea completă
4. **Backup regulat** - continuă să faci backup-uri pentru `volta_db`

## 🆘 Suport

Dacă întâmpini probleme:

1. Verifică log-urile Laravel: `storage/logs/laravel.log`
2. Verifică log-urile MySQL pentru erori
3. Verifică că toate tabelele au fost create corect
4. Verifică că toate datele au fost migrate

## ✅ Checklist Final

- [ ] Backup făcut pentru toate bazele de date
- [ ] `.env` actualizat cu `DB_DATABASE=volta_db`
- [ ] Migrarea rulată cu succes
- [ ] Datele verificate în `volta_db`
- [ ] Aplicația testată și funcționează corect
- [ ] Backup-urile păstrate în siguranță

---

**Succes cu migrarea! 🎉**
