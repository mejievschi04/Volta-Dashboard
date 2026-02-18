# Verificare afișare date 1C (înainte de deploy)

## Problema raportată
- **Istoric**: datele se afișează corect.
- **Celelalte** (Dashboard KPI, Comparare, grafic lunar): datele nu se afișau.

## Verificări făcute

### 1. Backend – același mod de citire 1C ca la Istoric
- **IstoricController**: `OnecKpiSync::whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$luna])->orderByDesc('created_at')->first()` → folosește `vanzari_fara_tva`, `profit`, `nr_comenzi`, etc.
- **KpiController**: aceeași interogare și aceleași câmpuri pentru luna cerută (`?month=YYYY-MM`). Răspunsul conține: `vanzari_luna`, `profit`, `comenzi`, `plan_luna`, `sesiuni`, etc.
- **VanzariLunareController**: citește din `OnecKpiSync` per lună, construiește `data[]` cu `vanzari` (vanzari_fara_tva) pentru graficul lunar și dropdown.

**Concluzie**: Logica de citire 1C este aliniată între Istoric, KPI și VanzariLunare.

### 2. Bug remediat în KpiController
- **Problema**: `use Illuminate\Support\Facades\Auth;` apărea de două ori (linii 10–11), ceea ce poate provoca erori la rulare.
- **Remediu**: a rămas o singură linie `use Illuminate\Support\Facades\Auth;`.

Dacă KpiController dădea eroare (ex. 500), Dashboard și Comparare nu primeau date și cardurile rămâneau goale.

### 3. Frontend – ce așteaptă și ce primește
- **Dashboard** (`updateKPIandChart`): folosește `kpiData.vanzari_luna`, `kpiData.profit`, `kpiData.comenzi`, `kpiData.plan_luna`, etc. Toate aceste chei sunt trimise de API.
- **Comparare**: folosește `data1[key]` / `data2[key]` pentru `plan_luna`, `vanzari_luna`, `profit`, etc. – aceleași chei ca în răspunsul KPI.
- **ID-uri carduri**: `plan-luna`, `vanzari-luna`, `progres-plan`, `comenzi`, `profit`, etc. există în HTML și sunt actualizate în script.

**Concluzie**: Structura răspunsului API și ID-urile din pagină sunt consistente.

### 4. Flux Dashboard (de ce puteau rămâne cardurile goale)
- Cardurile se completează doar după ce se apelează `updateKPIandChart(luna)`.
- `updateKPIandChart` e apelat la:
  - schimbarea lunii în dropdown (`change` pe `selectLuna`), sau
  - fallback-urile din `loadVanzariTotale()` (când API eșuează sau lista de luni e goală) – acolo se apelează explicit `updateKPIandChart(lunaCurenta)`.

Dacă `api/vanzari-lunare` dădea eroare (ex. tabel 1C inexistent) și nu exista fallback, dropdown-ul rămânea gol și nu se declanșa niciun `change`, deci cardurile nu se actualizau. Fallback-urile adăugate anterior rezolvă acest caz.

### 5. VanzariLunareController și tabelul gol/lipsă
- Apelurile la `OnecKpiSync` sunt în `try/catch`: dacă tabelul lipsește sau e gol, nu se mai aruncă excepție până la 500.
- Se folosesc mereu ultimele 24 de luni ca interval, cu `vanzari` 0 unde nu există sync 1C, astfel că dropdown-ul are mereu opțiuni și graficul lunar se poate desena.

## Ce s-a corectat în acest pas
- **KpiController**: eliminat `use Illuminate\Support\Facades\Auth` duplicat.

## Checklist înainte de deploy pe server
1. [ ] Rulat `php artisan migrate` pe server (tabele `onec_kpi_syncs`, `onec_kpi_operatori`).
2. [ ] Făcut cel puțin un Sync 1C (sidebar sau Setări → Date 1C) pentru luna curentă / lunile dorite.
3. [ ] După deploy: deschis Dashboard, verificat că dropdown-ul are luni și că la schimbarea lunii cardurile KPI se actualizează (inclusiv cu 0 dacă nu există sync pentru acea lună).
4. [ ] Verificat Rapoarte → Comparare pentru două luni cu sync 1C.
5. [ ] Verificat că graficul lunar (Dashboard) arată bare pentru lunile care au date în 1C.

## Dacă tot nu se afișează date
- Deschide F12 → Network: la schimbarea lunii trebuie request la `.../api/kpi?month=YYYY-MM`. Verifică dacă răspunsul e 200 și conține `vanzari_luna`, `profit`, etc.
- Dacă răspunsul e 500: verifică `storage/logs/laravel.log` pe server pentru eroare din KpiController sau migrări.
