<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Numărul de dialoguri cu clienții per operator VoltaChat pentru o lună,
 * plus potrivirea cu operatorii din 1C (legătură salvată sau după nume).
 */
final class VoltaChatOperatorChats
{
    public function __construct(private VoltaChatAnalyticsService $chat) {}

    /**
     * @return array{status: string, operators: list<array{id: int, name: string, conversations: int}>}
     *   status: ok | future | not_configured | unreachable | unauthorized | invalid | rate_limited | disabled | http_N
     */
    public function forMonth(string $ym): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $ym.'-01')->startOfDay();
        $today = now()->startOfDay();
        if ($start->gt($today)) {
            return ['status' => 'future', 'operators' => []];
        }
        $end = $start->copy()->endOfMonth()->startOfDay();
        if ($end->gt($today)) {
            $end = $today;
        }

        $result = $this->chat->fetch($start, $end, ['type' => 'customer', 'grain' => 'month']);
        if (! $result['ok']) {
            return ['status' => (string) $result['error'], 'operators' => []];
        }

        $operators = [];
        foreach ((array) data_get($result['data'], 'datasets.operators.rows', []) as $row) {
            $id = (int) ($row['id'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }
            $operators[] = ['id' => $id, 'name' => $name, 'conversations' => max(0, (int) ($row['conversations'] ?? 0))];
        }
        usort($operators, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return ['status' => 'ok', 'operators' => $operators];
    }

    /**
     * Potrivire după nume: aceleași cuvinte în orice ordine, fără diacritice; altfel numele
     * din VoltaChat e conținut în numele din 1C (ex. doar prenumele), dacă e unic.
     *
     * @param  list<string>  $names1c
     * @param  list<array{id: int, name: string, conversations: int}>  $operators
     * @return array<string, int> nume 1C => id VoltaChat
     */
    public function guessMatches(array $names1c, array $operators): array
    {
        $vc = [];
        foreach ($operators as $op) {
            $vc[$op['id']] = $this->tokens($op['name']);
        }

        $matches = [];
        $taken = [];
        foreach ($names1c as $name) {
            $t = $this->tokens($name);
            foreach ($vc as $id => $vt) {
                if ($vt === $t && ! isset($taken[$id])) {
                    $matches[$name] = $id;
                    $taken[$id] = true;
                    break;
                }
            }
        }

        foreach ($names1c as $name) {
            if (isset($matches[$name])) {
                continue;
            }
            $t = $this->tokens($name);
            $candidates = array_keys(array_filter(
                $vc,
                fn ($vt, $id) => ! isset($taken[$id]) && $vt !== [] && array_diff($vt, $t) === [],
                ARRAY_FILTER_USE_BOTH
            ));
            if (count($candidates) !== 1) {
                continue;
            }
            $id = $candidates[0];
            $claimants = array_filter($names1c, fn ($n) => ! isset($matches[$n]) && array_diff($vc[$id], $this->tokens($n)) === []);
            if (count($claimants) === 1) {
                $matches[$name] = $id;
                $taken[$id] = true;
            }
        }

        return $matches;
    }

    /** @return list<string> */
    private function tokens(string $name): array
    {
        $plain = mb_strtolower(Str::ascii($name), 'UTF-8');
        $parts = preg_split('/[^a-z0-9]+/', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        sort($parts);

        return array_values(array_unique($parts));
    }
}
