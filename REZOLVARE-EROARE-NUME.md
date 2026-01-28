# Rezolvare Eroare: Field 'nume' doesn't have a default value

## Problema

Eroarea apare când se încearcă inserarea unui utilizator nou:
```
SQLSTATE[HY000]: General error: 1364 Field 'nume' doesn't have a default value
```

## Cauza

Tabelul `users` din `volta_db` are coloana `nume` care era NOT NULL fără default value. De asemenea, MySQL strict mode cere valori pentru toate coloanele NOT NULL.

## Soluții Aplicate

### 1. Migrare pentru coloana `nume`
- ✅ Coloana `nume` a fost făcută nullable
- ✅ Datele din `nume` au fost copiate în `name` (dacă `name` era NULL)

### 2. Migrare pentru alte coloane
- ✅ `prenume` - făcută nullable
- ✅ `telefon` - făcută nullable  
- ✅ `parola` - făcută nullable

### 3. Configurație Database
- ✅ `strict` mode setat la `false` în `config/database.php` pentru compatibilitate

## Verificare

După aplicarea fix-urilor, verifică:
```bash
php artisan check:users-table
```

## Notă

Coloanele `username` și `password_hash` rămân NOT NULL (corect), dar trebuie setate explicit în cod când se creează utilizatori noi. `UserController` setează corect aceste valori.

## Dacă problema persistă

1. Verifică că migrările au rulat: `php artisan migrate:status`
2. Verifică structura tabelului: `php artisan check:users-table`
3. Verifică că `config/database.php` are `'strict' => false`
4. Rulează: `php artisan config:clear`
