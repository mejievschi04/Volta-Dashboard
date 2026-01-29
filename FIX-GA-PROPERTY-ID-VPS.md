# Fix: GA_PROPERTY_ID Lipsă din .env

## Problema Identificată

✅ Fișierul de credențiale există și funcționează  
✅ Testul în tinker funcționează (Success!)  
❌ **GA_PROPERTY_ID nu este setat în .env**

## Soluție

### Pe VPS, rulează:

```bash
cd /var/www/volta-dashboard

# Adaugă GA_PROPERTY_ID în .env
echo "GA_PROPERTY_ID=281678807" >> .env

# Verifică că a fost adăugat
cat .env | grep GA_PROPERTY_ID

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Verificare Finală

```bash
# Testează din nou în tinker
php artisan tinker
```

În tinker:
```php
config('google-analytics.property_id');
// Ar trebui să returneze: "281678807"

exit
```

Apoi încearcă din nou sincronizarea din interfață - ar trebui să funcționeze!

## Notă Despre Deprecare

Am văzut un warning despre `openssl_free_key()` - am corectat-o în cod. După ce actualizezi codul pe VPS, warning-ul va dispărea.
