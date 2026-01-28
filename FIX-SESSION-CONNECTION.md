# Fix: Database connection [dashboard] not configured

## Problema

Eroarea apare pentru că configurația sesiunilor încă încearcă să folosească conexiunea `dashboard` care nu mai există.

## Soluție

### Opțiunea 1: Elimină din .env (Recomandat)

În fișierul `.env`, elimină sau comentează linia:
```env
SESSION_CONNECTION=dashboard
```

Sau schimbă-o în:
```env
SESSION_CONNECTION=
```

### Opțiunea 2: Folosește conexiunea default

Dacă vrei să folosești explicit conexiunea mysql (volta_db), setează:
```env
SESSION_CONNECTION=mysql
```

## După actualizare

Rulează:
```bash
php artisan config:clear
php artisan cache:clear
```

## Verificare

După fix, aplicația ar trebui să funcționeze fără erori.
