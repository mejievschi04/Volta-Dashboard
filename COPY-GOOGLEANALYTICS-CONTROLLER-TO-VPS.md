# Copiere GoogleAnalyticsController pe VPS

## Metoda 1: Nano (Recomandat)

### Pe VPS, rulează:

```bash
cd /var/www/volta-dashboard

# Deschide fișierul în nano
nano app/Http/Controllers/GoogleAnalyticsController.php
```

### În nano:

1. **Șterge tot conținutul:**
   - `Ctrl+K` de mai multe ori pentru a șterge linia curentă
   - SAU `Ctrl+A` (selectează tot), apoi `Ctrl+K` (șterge)
   - SAU `Ctrl+6` (selectează tot), apoi `Ctrl+K`

2. **Lipește conținutul nou:**
   - Click dreapta în terminal SAU `Shift+Insert`
   - Lipește tot conținutul copiat

3. **Salvează:**
   - `Ctrl+O` (Write Out)
   - `Enter` (confirmă)
   - `Ctrl+X` (ieși)

### Verifică sintaxa:

```bash
php -l app/Http/Controllers/GoogleAnalyticsController.php
```

Dacă apare "No syntax errors", totul este OK!

## Metoda 2: Vim (Dacă preferi)

```bash
cd /var/www/volta-dashboard
vim app/Http/Controllers/GoogleAnalyticsController.php
```

### În vim:

1. **Șterge tot:**
   - Apasă `Esc` pentru a intra în modul normal
   - Scrie: `ggdG` (merge la început, șterge tot până la sfârșit)

2. **Intră în modul insert:**
   - Apasă `i` (insert mode)

3. **Lipește conținutul:**
   - Click dreapta SAU `Shift+Insert`

4. **Salvează și ieși:**
   - `Esc` (ieși din insert mode)
   - `:wq` (write and quit) + `Enter`

## Metoda 3: Șterge și Creează Nou

```bash
cd /var/www/volta-dashboard

# Șterge fișierul vechi
rm app/Http/Controllers/GoogleAnalyticsController.php

# Creează fișierul nou
nano app/Http/Controllers/GoogleAnalyticsController.php
```

Apoi lipește conținutul și salvează (`Ctrl+O`, `Enter`, `Ctrl+X`).

## După Copiere

```bash
# Verifică sintaxa
php -l app/Http/Controllers/GoogleAnalyticsController.php

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Testează
php artisan tinker
```

În tinker:
```php
$controller = app(\App\Http\Controllers\GoogleAnalyticsController::class);
echo "Controller loaded!\n";
exit
```
