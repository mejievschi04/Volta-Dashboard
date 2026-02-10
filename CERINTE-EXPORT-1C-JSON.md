# Cerințe export 1C → VOLTA Dashboard (format JSON)

Acest document descrie **ce date avem nevoie din 1C** și **cum să fie livrate în JSON**, pentru a înlocui importul curent din Excel și pentru a alimenta KPI-urile din dashboard.

## 1) Reguli generale

- **Format**: JSON (UTF-8), livrat fie ca fișier (`.json`), fie printr-un endpoint HTTP (aceeași structură).
- **Numere**: valori numerice JSON (fără ghilimele), cu **punct** ca separator zecimal (`1234.56`), fără separatori de mii.
- **Monedă**: **lei moldovenești (MDL)**.
- **Date**:
  - pentru agregări zilnice: `YYYY-MM-DD`
  - pentru agregări lunare: `YYYY-MM`
- **Perioadă**: exportul trebuie să accepte o perioadă \([start, end]\) și să returneze agregările pentru acea perioadă.
- **Definiții** (important să fie consecvente):
  - **vânzări fără TVA** = sumă netă
  - **vânzări cu TVA** = sumă brută
  - **profit** = profit aferent vânzărilor (conform calculelor din 1C)
  - **nrComenzi** = numărul de comenzi (documente/ordine) în perioada selectată

## 2) Ce KPI-uri avem nevoie (total, pentru cardurile KPI)

Pentru o perioadă selectată, vrem **totalurile**:

- `vanzariCuTVA` (MDL)
- `vanzariFaraTVA` (MDL)
- `profit` (MDL)
- `nrComenzi` (integer)

## 3) Ce KPI-uri avem nevoie per operator

Pentru aceeași perioadă selectată, vrem aceleași valori **per operator**:

- `operatorId1c` (ID-ul operatorului în 1C) sau un `operatorCode` stabil
- `operatorNume`
- `vanzariCuTVA`, `vanzariFaraTVA`, `profit`
- `nrComenzi`


Observație: dacă în 1C “operatorul” e definit prin utilizator/agent/manager, ne trebuie identificatorul folosit în 1C pentru raportare (important să fie stabil în timp).

## 4) Top produse lunar (produse cu preț > 1000 MDL)

Pentru **fiecare lună** dintr-o perioadă, vrem lista de **cele mai vândute produse** care au **preț unitar cu TVA > 1000 MDL**.

Interpretare recomandată:
- filtrare: `pretUnitarCuTVA > 1000`
- “cele mai vândute”: sortare după `cantitate` descrescător (la egalitate după `vanzariCuTVA` descrescător)

Pentru fiecare produs din top:

- `productId1c` (ID-ul produsului în 1C) sau `sku`/`cod` stabil
- `denumire`
- `pretUnitarCuTVA` (MDL) (cel folosit pentru filtrare > 1000)
- (opțional) `pretUnitarFaraTVA` (MDL)
- `cantitate` (sumă cantități vândute în luna respectivă)
- `vanzariCuTVA` (MDL) (sumă brută pe produs în luna respectivă)
- `vanzariFaraTVA` (MDL) (sumă netă pe produs în luna respectivă)
- (opțional) `profit` (MDL) pe produs

## 5) Structura JSON (payload recomandat)

### 5.1 Meta

- `meta.generatedAt`: timestamp ISO (`YYYY-MM-DDTHH:mm:ssZ`)
- `meta.company`: (opțional) firmă / entitate
- `meta.currency`: `"MDL"`
- `meta.period.start`, `meta.period.end`: perioada exportului

### 5.2 KPI total

- `kpiTotal`: totalurile pentru întreaga perioadă

### 5.3 KPI per operator

- `kpiPeOperator`: array cu totaluri per operator pentru perioada selectată

### 5.4 Top produse lunar (> 1000 MDL)

- `topProduseLunarPeste1000`: array cu elemente pe lună

## 6) Exemplu complet (JSON)

{
  "meta": {
    "generatedAt": "2026-02-02T10:30:00Z",
    "company": "VOLTA",
    "currency": "MDL",
    "period": {
      "start": "2026-01-01",
      "end": "2026-01-31"
    }
  },
  "kpiTotal": {
    "vanzariCuTVA": 1250000.55,
    "vanzariFaraTVA": 1050420.63,
    "profit": 210340.25,
    "nrComenzi": 842
  },
  "kpiPeOperator": [
    {
      "operatorId1c": "OP-00017",
      "operatorNume": "Neteda Octavian",
      "vanzariCuTVA": 350000.0,
      "vanzariFaraTVA": 294117.65,
      "profit": 60350.25,
      "nrComenzi": 210
    },
    {
      "operatorId1c": "OP-00023",
      "operatorNume": "Iurcu Marina",
      "vanzariCuTVA": 280000.25,
      "vanzariFaraTVA": 235294.33,
      "profit": 49320.0,
      "nrComenzi": 165
    }
  ],
  "topProduseLunarPeste1000": [
    {
      "luna": "2026-01",
      "reguli": {
        "pragPretUnitarCuTVA": 1000,
        "sortare": "cantitate_desc_then_vanzariCuTVA_desc"
      },
      "produse": [
        {
          "productId1c": "PRD-100045",
          "sku": "SKU-100045",
          "denumire": "Invertor 10kW Model X",
          "pretUnitarCuTVA": 1250.0,
          "pretUnitarFaraTVA": 1050.42,
          "cantitate": 42,
          "vanzariCuTVA": 52500.0,
          "vanzariFaraTVA": 44117.64,
          "profit": 8200.0
        },
        {
          "productId1c": "PRD-100912",
          "sku": "SKU-100912",
          "denumire": "Baterie 5kWh Model B",
          "pretUnitarCuTVA": 1899.99,
          "pretUnitarFaraTVA": 1596.63,
          "cantitate": 18,
          "vanzariCuTVA": 34199.82,
          "vanzariFaraTVA": 28739.34,
          "profit": 6100.0
        }
      ]
    }
  ]
}


