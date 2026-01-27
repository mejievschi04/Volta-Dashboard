# Migrare Dashboard Volta în Laravel

## Structura Proiectului

Proiectul a fost migrat în Laravel cu următoarea structură:

### Baze de Date Configurate
- `dashboard_db` - pentru utilizatori (conexiune: `dashboard`)
- `produse_db` - pentru produse (conexiune: `produse`)
- `vanzari_1c_db` - pentru vânzări (conexiune: `vanzari`)
- `trafic_db` - pentru trafic (conexiune: `trafic`)

### Modele Create
- `User` - utilizatori (conexiune: dashboard)
- `Vanzari` - vânzări (conexiune: vanzari)
- `PlanVanzari` - plan vânzări (conexiune: vanzari)
- `TrafficSource` - surse trafic (conexiune: trafic)
- `Produs` - produse (conexiune: produse)

### Controlere Create
- `Auth\LoginController` - autentificare
- `DashboardController` - dashboard principal
- `OperatoriController` - operatori
- `ProduseController` - produse
- `RapoarteController` - rapoarte
- `Api\KpiController` - API pentru KPI
- `Api\VanzariController` - API pentru vânzări

### Rute Configurate
- `/login` - pagina de login
- `/dashboard` - dashboard principal
- `/operatori` - operatori
- `/produse` - produse
- `/rapoarte` - rapoarte
- `/istoric` - istoric
- `/trafic` - trafic
- `/setari` - setări

## Progres Migrare

### ✅ Completat
- Proiect Laravel creat și configurat
- Conexiuni multiple la baze de date configurate
- Modele Eloquent create (User, Vanzari, PlanVanzari, TrafficSource, Produs)
- Sistem de autentificare implementat (LoginController)
- Rute web configurate
- Layout principal și view-uri de bază create
- Asset-uri (CSS, JS) copiate în public/
- API Controller pentru KPI implementat
- Dashboard view de bază creat
- **Migrări pentru tabele create (traffic_sources, vanzari_1c, plan_vanzari)**
- **Serviciu Google Analytics implementat**
- **Controller pentru sincronizare Google Analytics**
- **Configurație Google Analytics**
- **Dashboard complet migrat cu toate funcționalitățile:**
  - API Controllers: VanzariLunare, VanzariZilnice, ComenziConversie, Sesiuni, VanzariDetalii
  - Grafic lunar (plan vs vânzări reale)
  - Grafic vânzări zilnice
  - Grafic comenzi vs conversie
  - Grafic sesiuni zilnice
  - Modal detalii vânzări (click pe grafic lunar)
  - Funcționalitate comparare cu lună anterioară
  - Toate KPI-urile cu actualizare dinamică
- **Analitică trafic completă migrată**

## Pași pentru Finalizare

### 1. Configurare .env
Adaugă în fișierul `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=

DB_DATABASE_DASHBOARD=dashboard_db
DB_DATABASE_PRODUSE=produse_db
DB_DATABASE_VANZARI=vanzari_1c_db
DB_DATABASE_TRAFIC=trafic_db

# Google Analytics
GA_PROPERTY_ID=281678807
```

### 1.1. Rulare Migrări
Pentru a crea tabelele în bazele de date, rulează:
```bash
php artisan migrate
```

**IMPORTANT:** Migrările sunt configurate pentru conexiuni multiple. Dacă tabelele există deja, poți să le ignori sau să le ștergi manual înainte de a rula migrările.

### 2. Actualizare Model User
Modelul User folosește `username` în loc de `email`. Verifică dacă tabelul `users` din `dashboard_db` are coloana `password_hash` sau `password`.

**IMPORTANT:** LoginController verifică atât `password_hash` cât și `password`. Dacă tabelul folosește `password_hash`, modelul User trebuie actualizat să folosească această coloană.

### 3. API Controllers Implementate ✅
Toate API controllers pentru dashboard sunt implementate:
- `Api\KpiController` - `/api/kpi` ✅
- `Api\VanzariLunareController` - `/api/vanzari-lunare` ✅
- `Api\VanzariZilniceController` - `/api/vanzari-zilnice` ✅
- `Api\ComenziConversieController` - `/api/comenzi-conversie` ✅
- `Api\SesiuniController` - `/api/sesiuni` ✅
- `Api\VanzariDetaliiController` - `/api/vanzari-detalii` ✅
- `Api\TraficController` - `/api/trafic` ✅

### 4. View-uri Create ✅
- `dashboard/index.blade.php` - Dashboard complet cu toate graficele ✅
- `dashboard/trafic.blade.php` - Analitică trafic ✅
- `auth/login.blade.php` - Pagină login ✅
- `layouts/app.blade.php` - Layout principal ✅

**Rămân de creat:**
- `operatori/index.blade.php`
- `operatori/show.blade.php`
- `produse/index.blade.php`
- `rapoarte/index.blade.php`
- `dashboard/setari.blade.php`

### 5. Configurare Google Analytics

#### 5.1. Copiere Credențiale
Copiază fișierul `service-account-credentials.json` din proiectul vechi în:
```
storage/app/google-analytics/service-account-credentials.json
```

#### 5.2. Sincronizare Date Google Analytics
Pentru a sincroniza datele din Google Analytics, folosește endpoint-ul:
```
POST /api/ga/sync
```

Parametri opționali:
- `start_date` și `end_date` - pentru o perioadă specifică (format: YYYY-MM-DD)
- `month` - pentru o lună specifică (format: YYYY-MM)
- Fără parametri - sincronizează luna curentă (sau noiembrie + luna curentă dacă suntem în decembrie/ianuarie)

Exemplu:
```bash
# Sincronizează luna curentă
curl -X POST http://localhost/api/ga/sync

# Sincronizează o lună specifică
curl -X POST "http://localhost/api/ga/sync?month=2024-11"

# Sincronizează o perioadă specifică
curl -X POST "http://localhost/api/ga/sync?start_date=2024-11-01&end_date=2024-11-30"
```

### 6. Funcționalități Migrate ✅
- ✅ Grafici și chart-uri (Chart.js) - toate graficele funcționale
- ✅ Google Analytics integration - sincronizare completă
- ✅ KPI cards cu actualizare dinamică
- ✅ Comparare cu lună anterioară
- ✅ Modal detalii vânzări

**Rămân de migrat:**
- Upload Excel (vanzări și trafic)

### 7. Testare
- Testează autentificarea
- Testează toate paginile
- Testează API endpoints
- Verifică conexiunile la bazele de date

## Note Importante

- Asset-urile (CSS, JS) au fost copiate în `public/css` și `public/js`
- Autentificarea folosește `username` în loc de `email`
- Toate rutele protejate folosesc middleware `auth`
- Layout-ul principal este în `resources/views/layouts/app.blade.php`
- **Migrările folosesc conexiuni multiple** - fiecare tabel este creat în baza de date corespunzătoare
- **Google Analytics Service** este în `app/Services/GoogleAnalyticsService.php`
- **Credențialele Google Analytics** trebuie să fie în `storage/app/google-analytics/service-account-credentials.json`

## Structura Servicii Google Analytics

- **Serviciu:** `App\Services\GoogleAnalyticsService`
  - `fetchTrafficData($startDate, $endDate)` - extrage date din GA4
  - `processTrafficData($gaData)` - procesează datele pentru baza de date
  
- **Controller:** `App\Http\Controllers\GoogleAnalyticsController`
  - `sync(Request $request)` - sincronizează datele în baza de date

- **Rută:** `POST /api/ga/sync` (protejată cu middleware auth)

