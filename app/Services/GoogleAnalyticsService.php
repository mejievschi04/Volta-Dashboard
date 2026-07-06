<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticsService
{
    private $propertyId;
    private $credentials;
    private $credentialsPath;

    public function __construct()
    {
        $this->propertyId = config('google-analytics.property_id');
        $this->credentialsPath = config('google-analytics.credentials_path');
        
        // Nu încărcăm credențialele în constructor pentru a evita erori fatale
        // Vom încărca credențialele doar când sunt necesare
    }
    
    /**
     * Verifică și încarcă credențialele dacă nu sunt deja încărcate
     */
    private function ensureCredentialsLoaded()
    {
        if ($this->credentials === null) {
            $this->loadCredentials();
        }
    }

    /**
     * Încarcă credențialele din fișierul JSON
     */
    private function loadCredentials()
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error("GA Credentials file not found", ['path' => $this->credentialsPath]);
            throw new Exception("Fișierul de credențiale nu există: {$this->credentialsPath}");
        }

        $json = file_get_contents($this->credentialsPath);
        $this->credentials = json_decode($json, true);

        if (!$this->credentials) {
            $jsonError = json_last_error_msg();
            Log::error("GA Credentials JSON decode failed", [
                'path' => $this->credentialsPath,
                'error' => $jsonError
            ]);
            throw new Exception("Nu s-au putut citi credențialele din fișierul JSON: {$jsonError}");
        }

        if (!isset($this->credentials['private_key']) || !isset($this->credentials['client_email'])) {
            Log::error("GA Credentials missing required fields", [
                'has_private_key' => isset($this->credentials['private_key']),
                'has_client_email' => isset($this->credentials['client_email'])
            ]);
            throw new Exception("Credențialele nu conțin câmpurile necesare (private_key, client_email)");
        }
    }

    /**
     * Obține access token folosind Service Account
     */
    private function getAccessToken()
    {
        $this->ensureCredentialsLoaded();
        
        $url = 'https://oauth2.googleapis.com/token';

        $jwt = $this->createJWT();

        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ];

        // Încercăm mai întâi cu HTTP Client-ul Laravel (mai robust)
        try {
            $sslVerify = filter_var(env('GA_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN);
            
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withOptions([
                    'verify' => $sslVerify,
                ])
                ->asForm()
                ->post($url, $data);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['access_token'])) {
                    return $result['access_token'];
                }
                throw new Exception("Token-ul nu a fost returnat în răspuns: " . $response->body());
            }

            Log::error("GA Token request failed (HTTP Client)", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            throw new Exception("Eroare la obținerea token-ului: HTTP {$response->status()} - {$response->body()}");
            
        } catch (\Exception $e) {
            // Fallback la cURL dacă HTTP Client eșuează
            Log::warning("GA Token HTTP Client failed, trying cURL", ['error' => $e->getMessage()]);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            
            // Configurare SSL - pentru XAMPP pe Windows, poate fi necesar să dezactivăm verificarea
            $sslVerify = filter_var(env('GA_SSL_VERIFY', false), FILTER_VALIDATE_BOOLEAN);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Laravel-GoogleAnalytics-Service/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            if ($curlError || $curlErrno) {
                Log::error("GA Token request curl error", [
                    'error' => $curlError,
                    'errno' => $curlErrno,
                    'url' => $url
                ]);
                throw new Exception("Eroare cURL la obținerea token-ului: {$curlError} (cod: {$curlErrno})");
            }

            if ($httpCode !== 200) {
                Log::error("GA Token request failed (cURL)", [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'url' => $url
                ]);
                throw new Exception("Eroare la obținerea token-ului: HTTP {$httpCode} - {$response}");
            }

            $result = json_decode($response, true);
            if (!isset($result['access_token'])) {
                throw new Exception("Token-ul nu a fost returnat în răspuns: {$response}");
            }

            return $result['access_token'];
        }
    }

    /**
     * Creează JWT pentru autentificare
     */
    private function createJWT()
    {
        $this->ensureCredentialsLoaded();
        
        $now = time();
        $exp = $now + 3600; // Expiră în 1 oră

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $exp,
            'iat' => $now
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $data = "{$headerEncoded}.{$payloadEncoded}";

        // Semnează cu cheia privată
        $privateKey = $this->credentials['private_key'];

        // Formatează cheia privată dacă nu este deja formatată
        if (strpos($privateKey, '-----BEGIN') === false) {
            $privateKey = "-----BEGIN PRIVATE KEY-----\n" .
                         chunk_split($privateKey, 64, "\n") .
                         "-----END PRIVATE KEY-----\n";
        }

        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            $opensslError = openssl_error_string();
            Log::error("GA OpenSSL key error", ['error' => $opensslError]);
            throw new Exception("Eroare la citirea cheii private: " . $opensslError);
        }

        openssl_sign($data, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        // openssl_free_key() este deprecated în PHP 8.0+, nu mai este necesar

        $signatureEncoded = $this->base64UrlEncode($signature);

        return "{$data}.{$signatureEncoded}";
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Extrage date despre trafic pentru o perioadă dată
     */
    public function fetchTrafficData($startDate, $endDate)
    {
        if ($this->propertyId === 'YOUR_PROPERTY_ID_HERE' || empty($this->propertyId)) {
            throw new Exception("Property ID nu este configurat! Verifică config/google-analytics.php");
        }

        $accessToken = $this->getAccessToken();

        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
            ],
            'dimensions' => [
                ['name' => 'date'],
                ['name' => 'sessionSourceMedium']
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'newUsers'],
                ['name' => 'activeUsers']
            ],
            'orderBys' => [
                [
                    'dimension' => [
                        'dimensionName' => 'date'
                    ]
                ]
            ]
        ];

        // Încercăm mai întâi cu HTTP Client-ul Laravel
        try {
            $sslVerify = filter_var(env('GA_SSL_VERIFY', false), FILTER_VALIDATE_BOOLEAN);
            
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withOptions([
                    'verify' => $sslVerify,
                ])
                ->withToken($accessToken)
                ->post($url, $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("GA API JSON decode failed", [
                        'error' => json_last_error_msg(),
                        'response' => substr($response->body(), 0, 500)
                    ]);
                    throw new Exception("Eroare la decodarea răspunsului JSON: " . json_last_error_msg());
                }
                return $data;
            }

            Log::error("GA API request failed (HTTP Client)", [
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $url
            ]);
            throw new Exception("Eroare la cererea către GA4 API: HTTP {$response->status()} - {$response->body()}");
            
        } catch (\Exception $e) {
            // Fallback la cURL dacă HTTP Client eșuează
            Log::warning("GA API HTTP Client failed, trying cURL", ['error' => $e->getMessage()]);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
            
            // Configurare SSL - pentru XAMPP pe Windows, dezactivăm verificarea
            $sslVerify = filter_var(env('GA_SSL_VERIFY', false), FILTER_VALIDATE_BOOLEAN);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
            
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error("GA API curl error", ['error' => $curlError]);
                throw new Exception("Eroare cURL la extragerea datelor din GA4: {$curlError}");
            }

            if ($httpCode !== 200) {
                Log::error("GA API request failed", [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'url' => $url
                ]);
                throw new Exception("Eroare la cererea către GA4 API: HTTP {$httpCode} - {$response}");
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("GA API JSON decode failed", [
                    'error' => json_last_error_msg(),
                    'response' => substr($response, 0, 500)
                ]);
                throw new Exception("Eroare la decodarea răspunsului JSON: " . json_last_error_msg());
            }

            return $data;
        }
    }

    /**
     * Procesează datele din GA4 și le transformă în format pentru baza de date
     */
    public function processTrafficData($gaData)
    {
        $processed = [];

        if (!isset($gaData['rows'])) {
            return $processed;
        }

        foreach ($gaData['rows'] as $row) {
            $date = $row['dimensionValues'][0]['value']; // format: YYYYMMDD
            $sourceMedium = $row['dimensionValues'][1]['value']; // ex: google/organic, (direct)/(none), etc.
            $sessions = (int)$row['metricValues'][0]['value'];
            $newUsers = isset($row['metricValues'][1]) ? (int)$row['metricValues'][1]['value'] : 0;
            $activeUsers = isset($row['metricValues'][2]) ? (int)$row['metricValues'][2]['value'] : 0;
            // Calculăm utilizatori vechi: activeUsers - newUsers
            $returningUsers = max(0, $activeUsers - $newUsers);

            // Convertim data din YYYYMMDD în YYYY-MM-DD
            $formattedDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);

            // Categorizăm sursa de trafic
            $source = $this->categorizeSource($sourceMedium);

            if (!isset($processed[$formattedDate])) {
                $processed[$formattedDate] = [];
            }

            if (!isset($processed[$formattedDate][$source])) {
                $processed[$formattedDate][$source] = [
                    'visits' => 0,
                    'new_users' => 0,
                    'returning_users' => 0
                ];
            }

            $processed[$formattedDate][$source]['visits'] += $sessions;
            $processed[$formattedDate][$source]['new_users'] += $newUsers;
            $processed[$formattedDate][$source]['returning_users'] += $returningUsers;
        }

        return $processed;
    }

    /**
     * Categorizează sursa de trafic în categorii: google, google_cpc, direct, yandex, other
     */
    private function categorizeSource($sourceMedium)
    {
        $sourceMedium = strtolower($sourceMedium);

        // Google organic
        if (strpos($sourceMedium, 'google/organic') !== false ||
            (strpos($sourceMedium, 'google') !== false && strpos($sourceMedium, 'cpc') === false)) {
            return 'google';
        }

        // Google CPC (plătit)
        if (strpos($sourceMedium, 'google') !== false &&
            (strpos($sourceMedium, 'cpc') !== false || strpos($sourceMedium, 'paid') !== false)) {
            return 'google_cpc';
        }

        // Direct
        if (strpos($sourceMedium, 'direct') !== false || strpos($sourceMedium, '(none)') !== false) {
            return 'direct';
        }

        // Yandex
        if (strpos($sourceMedium, 'yandex') !== false) {
            return 'yandex';
        }

        // Altele
        return 'other';
    }

    /**
     * Extrage date despre utilizatori
     */
    public function fetchUsersData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [['name' => 'date']],
            'metrics' => [
                ['name' => 'activeUsers'],
                ['name' => 'newUsers'],
                ['name' => 'sessions'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate'],
                ['name' => 'engagementRate']
            ]
        ];

        return $this->makeApiRequest($url, $accessToken, $requestBody);
    }

    /**
     * Extrage date despre dispozitive
     */
    public function fetchDevicesData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [
                ['name' => 'deviceCategory'],
                ['name' => 'operatingSystem'],
                ['name' => 'browser']
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'activeUsers'],
                ['name' => 'screenPageViews']
            ],
            'limit' => 100
        ];

        return $this->makeApiRequest($url, $accessToken, $requestBody);
    }

    /**
     * Extrage date geografice
     */
    public function fetchGeoData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [
                ['name' => 'country'],
                ['name' => 'city']
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'activeUsers']
            ],
            'limit' => 100
        ];

        return $this->makeApiRequest($url, $accessToken, $requestBody);
    }

    /**
     * Extrage date despre conținut
     */
    public function fetchContentData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [
                ['name' => 'pagePath'],
                ['name' => 'pageTitle']
            ],
            'metrics' => [
                ['name' => 'screenPageViews'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate']
            ],
            'orderBys' => [
                ['metric' => ['metricName' => 'screenPageViews'], 'desc' => true]
            ],
            'limit' => 50
        ];

        return $this->makeApiRequest($url, $accessToken, $requestBody);
    }

    /**
     * Metrici lunare pentru raport eCommerce (GA4).
     *
     * - sessions → vizitatori site (accesări e-shop)
     * - bounceRate → % sesiuni neangajate (fără interacțiune semnificativă)
     * - sessionConversionRate → % sesiuni cu eveniment de conversie (ex. achiziție)
     *
     * @return array<string, array{sessions: int, bounce_rate: float|null, conversion_rate: float|null}>
     */
    public function fetchMonthlyRaportMetrics(string $startDate, string $endDate): array
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [['name' => 'yearMonth']],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'bounceRate'],
                ['name' => 'sessionConversionRate'],
            ],
            'orderBys' => [
                ['dimension' => ['dimensionName' => 'yearMonth']],
            ],
        ];

        $response = $this->makeApiRequest($url, $accessToken, $requestBody);
        $byMonth = [];

        foreach ($response['rows'] ?? [] as $row) {
            $ymRaw = $row['dimensionValues'][0]['value'] ?? '';
            if (! preg_match('/^(\d{4})(\d{2})$/', $ymRaw, $m)) {
                continue;
            }
            $ym = $m[1] . '-' . $m[2];
            $sessions = (int) round((float) ($row['metricValues'][0]['value'] ?? 0));
            $bounceRaw = (float) ($row['metricValues'][1]['value'] ?? 0);
            $conversionRaw = (float) ($row['metricValues'][2]['value'] ?? 0);

            $byMonth[$ym] = [
                'sessions' => $sessions,
                'bounce_rate' => round($bounceRaw <= 1 ? $bounceRaw * 100 : $bounceRaw, 2),
                'conversion_rate' => round($conversionRaw <= 1 ? $conversionRaw * 100 : $conversionRaw, 2),
            ];
        }

        return $byMonth;
    }

    /** @deprecated Folosește fetchMonthlyRaportMetrics */
    public function fetchMonthlyEngagement(string $startDate, string $endDate): array
    {
        return $this->fetchMonthlyRaportMetrics($startDate, $endDate);
    }

    /**
     * Extrage date despre e-commerce
     * Notă: Metricile e-commerce necesită configurare în GA4
     */
    public function fetchEcommerceData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        // Încercăm mai întâi cu metricile e-commerce
        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [['name' => 'date']],
            'metrics' => [
                ['name' => 'totalRevenue'],
                ['name' => 'purchasers'],
                ['name' => 'transactions'],
                ['name' => 'itemsPurchased'],
                ['name' => 'averagePurchaseRevenue']
            ]
        ];

        try {
            return $this->makeApiRequest($url, $accessToken, $requestBody);
        } catch (\Exception $e) {
            // Dacă e-commerce nu este configurat, returnăm un răspuns gol
            Log::warning("E-commerce metrics not available", [
                'error' => $e->getMessage(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            // Returnăm un răspuns gol cu structura corectă
            return [
                'rows' => [],
                'rowCount' => 0,
                'metadata' => [
                    'currencyCode' => 'RON'
                ]
            ];
        }
    }

    /**
     * Extrage date despre campanii
     */
    public function fetchCampaignsData($startDate, $endDate)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://analyticsdata.googleapis.com/v1beta/properties/{$this->propertyId}:runReport";

        $requestBody = [
            'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
            'dimensions' => [
                ['name' => 'sessionCampaignName'],
                ['name' => 'sessionSourceMedium']
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'activeUsers']
            ],
            'limit' => 100
        ];

        return $this->makeApiRequest($url, $accessToken, $requestBody);
    }

    /**
     * Face o cerere către GA4 API
     */
    private function makeApiRequest($url, $accessToken, $requestBody)
    {
        try {
            $sslVerify = filter_var(env('GA_SSL_VERIFY', false), FILTER_VALIDATE_BOOLEAN);
            
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withOptions(['verify' => $sslVerify])
                ->withToken($accessToken)
                ->post($url, $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Eroare la decodarea răspunsului JSON: " . json_last_error_msg());
                }
                return $data;
            }

            throw new Exception("Eroare la cererea către GA4 API: HTTP {$response->status()} - {$response->body()}");
            
        } catch (\Exception $e) {
            Log::error("GA API request failed", [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            throw $e;
        }
    }
}

