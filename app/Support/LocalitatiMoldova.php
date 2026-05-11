<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LocalitatiMoldova
{
    private const EXCLUDED_RAIOANE = [
        'UATSN',
    ];

    public static function all(): Collection
    {
        $path = resource_path('data/localitati_moldova.json');

        if (! File::exists($path)) {
            return collect();
        }

        $items = json_decode(File::get($path), true);

        if (! is_array($items)) {
            return collect();
        }

        return collect($items)
            ->filter(fn ($item) => isset($item['localitate'], $item['raion']))
            ->reject(fn ($item) => self::isExcludedRaion((string) $item['raion']))
            ->values();
    }

    public static function raioane(): Collection
    {
        return self::all()
            ->pluck('raion')
            ->unique()
            ->sortBy(fn ($raion) => mb_strtolower($raion))
            ->values();
    }

    public static function isKnownRaion(string $raion): bool
    {
        $normalized = self::normalizeSearch($raion);

        if ($normalized === '') {
            return false;
        }

        return self::raioane()->contains(function (string $knownRaion) use ($normalized) {
            return self::normalizeSearch($knownRaion) === $normalized;
        });
    }

    public static function localitateMatches(string $query, int $limit = 8): Collection
    {
        $queryNormalized = self::normalizeSearch($query);

        if ($queryNormalized === '') {
            return collect();
        }

        return self::all()
            ->groupBy(fn ($item) => self::normalizeSearch((string) $item['localitate']))
            ->map(function (Collection $items) use ($queryNormalized) {
                $first = $items->first();
                $score = self::matchScore($queryNormalized, self::normalizeSearch((string) $first['localitate']));

                if ($score === null) {
                    return null;
                }

                return [
                    'localitate' => $first['localitate'],
                    'raioane' => $items->pluck('raion')->unique()->values()->all(),
                    'score' => $score,
                ];
            })
            ->filter()
            ->sortBy([
                ['score', 'asc'],
                ['localitate', 'asc'],
            ])
            ->values()
            ->take($limit);
    }

    public static function bestLocalitateMatch(string $query, ?string $raion = null): ?array
    {
        $raionNormalized = self::normalizeSearch((string) $raion);

        return self::localitateMatches($query, 12)
            ->first(function (array $match) use ($raionNormalized) {
                if ($raionNormalized === '') {
                    return true;
                }

                foreach ($match['raioane'] as $matchRaion) {
                    if (self::normalizeSearch((string) $matchRaion) === $raionNormalized) {
                        return true;
                    }
                }

                return false;
            });
    }

    public static function isExcludedRaion(string $raion): bool
    {
        $normalized = self::normalizeRaion($raion);

        foreach (self::EXCLUDED_RAIOANE as $excludedRaion) {
            if ($normalized === self::normalizeRaion($excludedRaion)) {
                return true;
            }
        }

        return false;
    }

    public static function raionForLocalitate(string $localitate, string $fallbackRaion = ''): string
    {
        return self::raionForLocalitateAndAddress($localitate, '', $fallbackRaion);
    }

    public static function raionForLocalitateAndAddress(string $localitate, string $address = '', string $fallbackRaion = ''): string
    {
        if ($fallbackRaion !== '' && self::isKnownRaion($fallbackRaion)) {
            return $fallbackRaion;
        }

        $raionFromLocalitateName = self::canonicalRaionByNormalized(self::normalizeSearch($localitate));
        if ($raionFromLocalitateName !== null) {
            return $raionFromLocalitateName;
        }

        $matches = self::localitateMatches($localitate, 12);
        $addressRaion = self::raionFromText($address, $matches->flatMap(fn (array $match) => $match['raioane'])->unique()->all());

        if ($addressRaion !== null) {
            return $addressRaion;
        }

        $match = $matches->first();

        if ($match !== null && count($match['raioane']) === 1) {
            return $match['raioane'][0];
        }

        return $fallbackRaion;
    }

    public static function administrativeUnitLabel(string $raion): string
    {
        $trimmed = trim($raion);
        if ($trimmed === '') {
            return '';
        }

        $normalized = self::normalizeSearch($trimmed);

        if ($normalized === 'chisinau') {
            return 'Municipiul Chișinău';
        }

        if ($normalized === 'balti') {
            return 'Municipiul Bălți';
        }

        return $trimmed;
    }

    public static function raionFromText(string $text, ?array $candidateRaioane = null): ?string
    {
        $normalizedText = self::normalizeSearch($text);

        if ($normalizedText === '') {
            return null;
        }

        $raioane = $candidateRaioane !== null && $candidateRaioane !== []
            ? collect($candidateRaioane)
            : self::raioane();

        $matches = $raioane
            ->unique()
            ->filter(function (string $raion) use ($normalizedText) {
                $normalizedRaion = self::normalizeSearch($raion);
                return $normalizedRaion !== '' && str_contains($normalizedText, $normalizedRaion);
            })
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public static function normalizeSearch(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        $normalized = strtr($normalized, [
            'ă' => 'a',
            'â' => 'a',
            'î' => 'i',
            'ș' => 's',
            'ş' => 's',
            'ț' => 't',
            'ţ' => 't',
        ]);

        return preg_replace('/[^a-z0-9]+/u', '', $normalized) ?? '';
    }

    private static function normalizeRaion(string $value): string
    {
        return self::normalizeSearch($value);
    }

    private static function canonicalRaionByNormalized(string $normalized): ?string
    {
        if ($normalized === '') {
            return null;
        }

        return self::raioane()->first(function (string $raion) use ($normalized) {
            return self::normalizeSearch($raion) === $normalized;
        });
    }

    private static function matchScore(string $query, string $candidate): ?int
    {
        if ($candidate === '') {
            return null;
        }

        if ($candidate === $query) {
            return 0;
        }

        if (str_starts_with($candidate, $query)) {
            return 10 + abs(strlen($candidate) - strlen($query));
        }

        $position = strpos($candidate, $query);
        if ($position !== false) {
            return 25 + $position + abs(strlen($candidate) - strlen($query));
        }

        if (strlen($query) < 3) {
            return null;
        }

        $distance = levenshtein($query, $candidate);
        $threshold = strlen($query) <= 5 ? 1 : (strlen($query) <= 9 ? 2 : 3);

        if ($distance > $threshold) {
            return null;
        }

        return 50 + ($distance * 8) + abs(strlen($candidate) - strlen($query));
    }
}
