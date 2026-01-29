# Fix: Composer PHP Version Mismatch pe VPS

## Problema
`composer.lock` a fost generat cu PHP 8.4, dar serverul are PHP 8.2.30.

## Soluție Rapidă

### Pe VPS, rulează:

```bash
cd /var/www/volta-dashboard

# Opțiunea 1: Actualizează composer.lock pentru PHP 8.2 (RECOMANDAT)
composer update --no-dev --optimize-autoloader --ignore-platform-reqs

# SAU dacă ești root:
sudo -u www-data composer update --no-dev --optimize-autoloader --ignore-platform-reqs
```

### Dacă Opțiunea 1 Nu Funcționează:

```bash
# Opțiunea 2: Șterge composer.lock și reinstalează
rm composer.lock
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# SAU ca www-data:
sudo -u www-data rm composer.lock
sudo -u www-data composer install --no-dev --optimize-autoloader --ignore-platform-reqs
```

### Verificare După Fix:

```bash
# Verifică că nu mai apare eroarea
php artisan --version

# Testează tinker
php artisan tinker
# În tinker:
exit
```

## Explicație

- `composer.json` specifică `"php": "^8.2"` (corect)
- `composer.lock` a fost generat cu PHP 8.4 (problema)
- `--ignore-platform-reqs` ignoră verificarea platformei și permite instalarea pentru PHP 8.2
- `--no-dev` instalează doar dependențele de producție
- `--optimize-autoloader` optimizează autoloader-ul pentru performanță

## Notă Importantă

După actualizare, `composer.lock` va fi regenerat pentru PHP 8.2. Dacă lucrezi și local cu PHP 8.4, va trebui să regenerezi lock-ul când schimbi între medii.
