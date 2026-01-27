# Metrici și Dimensiuni Disponibile în GA4 API

## Metrici Disponibile (Metrics)

### Utilizatori și Sesiuni
- `activeUsers` - Utilizatori activi
- `newUsers` - Utilizatori noi
- `sessions` - Număr de sesiuni
- `sessionsPerUser` - Sesiuni per utilizator

### Engagement
- `screenPageViews` - Număr de pagini vizualizate
- `averageSessionDuration` - Durata medie a sesiunii (în secunde)
- `bounceRate` - Rata de respingere (%)
- `engagementRate` - Rata de angajament (%)
- `eventCount` - Număr total de evenimente
- `userEngagementDuration` - Durata totală de angajament (în secunde)

### Conversii și E-commerce
- `conversions` - Număr de conversii
- `totalRevenue` - Venitul total
- `purchaseRevenue` - Venitul din achiziții
- `itemRevenue` - Venitul pe articol
- `purchasers` - Număr de cumpărători
- `itemsPurchased` - Număr de articole cumpărate
- `transactions` - Număr de tranzacții
- `totalPurchasers` - Total cumpărători

### Altele
- `adUnitExposure` - Expunerea unităților publicitare
- `cohortActiveUsers` - Utilizatori activi în cohortă

## Dimensiuni Disponibile (Dimensions)

### Timp
- `date` - Data (YYYYMMDD)
- `year` - Anul
- `month` - Luna
- `week` - Săptămâna
- `day` - Ziua
- `dayOfWeek` - Ziua săptămânii
- `hour` - Ora

### Trafic
- `sessionSource` - Sursa sesiunii
- `sessionMedium` - Mediul sesiunii
- `sessionSourceMedium` - Sursa și mediul combinat
- `sessionCampaign` - Campania
- `sessionCampaignId` - ID-ul campaniei
- `sessionGoogleAdsAccountName` - Numele contului Google Ads
- `sessionGoogleAdsCampaignId` - ID-ul campaniei Google Ads
- `sessionGoogleAdsCampaignName` - Numele campaniei Google Ads
- `sessionGoogleAdsAdGroupId` - ID-ul grupului de anunțuri
- `sessionGoogleAdsAdGroupName` - Numele grupului de anunțuri
- `sessionGoogleAdsCreativeId` - ID-ul creativului
- `sessionGoogleAdsCustomerId` - ID-ul clientului Google Ads

### Geografie
- `country` - Țara
- `region` - Regiunea
- `city` - Orașul
- `continent` - Continentul
- `subContinent` - Subcontinentul

### Dispozitive și Tehnologie
- `deviceCategory` - Categoria dispozitivului (desktop/mobile/tablet)
- `mobileDeviceInfo` - Informații despre dispozitivul mobil
- `mobileDeviceMarketingName` - Numele de marketing al dispozitivului
- `operatingSystem` - Sistemul de operare
- `operatingSystemVersion` - Versiunea sistemului de operare
- `browser` - Browser-ul
- `browserVersion` - Versiunea browser-ului
- `screenResolution` - Rezoluția ecranului

### Conținut
- `pagePath` - Calea paginii
- `pageTitle` - Titlul paginii
- `pageLocation` - Locația paginii (URL complet)
- `landingPage` - Pagina de destinație
- `exitPage` - Pagina de ieșire
- `hostName` - Numele gazdei

### Utilizatori
- `userId` - ID-ul utilizatorului
- `userAgeBracket` - Grupul de vârstă
- `userGender` - Genul
- `interestAffinityCategory` - Categoria de interes
- `firstSessionDate` - Data primei sesiuni

### Evenimente
- `eventName` - Numele evenimentului
- `customEvent:EVENT_NAME` - Evenimente personalizate

## Exemple de Combinații Utile

### 1. Trafic pe Dispozitive
```php
'dimensions' => [
    ['name' => 'date'],
    ['name' => 'deviceCategory']
],
'metrics' => [
    ['name' => 'sessions'],
    ['name' => 'activeUsers']
]
```

### 2. Top Pagini
```php
'dimensions' => [
    ['name' => 'pagePath'],
    ['name' => 'pageTitle']
],
'metrics' => [
    ['name' => 'screenPageViews'],
    ['name' => 'averageSessionDuration'],
    ['name' => 'bounceRate']
]
```

### 3. Trafic Geografic
```php
'dimensions' => [
    ['name' => 'country'],
    ['name' => 'city']
],
'metrics' => [
    ['name' => 'sessions'],
    ['name' => 'activeUsers']
]
```

### 4. E-commerce Performance
```php
'dimensions' => [
    ['name' => 'date']
],
'metrics' => [
    ['name' => 'totalRevenue'],
    ['name' => 'purchasers'],
    ['name' => 'transactions'],
    ['name' => 'itemsPurchased']
]
```

### 5. Campanii Google Ads
```php
'dimensions' => [
    ['name' => 'sessionGoogleAdsCampaignName'],
    ['name' => 'sessionGoogleAdsAdGroupName']
],
'metrics' => [
    ['name' => 'sessions'],
    ['name' => 'conversions'],
    ['name' => 'totalRevenue']
]
```

## Recomandări pentru Dashboard

### Prioritate Înaltă:
1. **Bounce Rate** - Rata de respingere pentru a vedea calitatea traficului
2. **Average Session Duration** - Durata medie a sesiunii
3. **Screen Page Views** - Numărul de pagini vizualizate
4. **Device Category** - Distribuția pe dispozitive (desktop/mobile/tablet)
5. **Top Pages** - Paginile cele mai vizitate

### Prioritate Medie:
6. **Country/City** - Distribuția geografică
7. **Browser/OS** - Tehnologia utilizată
8. **Landing Pages** - Paginile de destinație
9. **Engagement Rate** - Rata de angajament

### Prioritate Scăzută (dacă ai E-commerce):
10. **Conversions** - Numărul de conversii
11. **Total Revenue** - Venitul total
12. **Purchasers** - Numărul de cumpărători

## Note Importante

- Nu toate metricile pot fi combinate cu toate dimensiunile
- Unele metrici necesită configurare specială în GA4 (ex: conversii)
- Limitele API: maxim 10 dimensiuni și 10 metrici per cerere
- Rate limiting: 10 cereri pe secundă per IP

