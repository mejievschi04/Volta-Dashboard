<?php

namespace App\Console\Commands;

use App\Support\LocalitatiMoldova;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillLivrariRaioane extends Command
{
    protected $signature = 'livrari:backfill-raioane
        {--dry-run : Arata ce s-ar modifica, fara sa scrie in baza}
        {--limit= : Proceseaza cel mult N livrari}
        {--only-missing : Proceseaza doar livrarile unde raionul lipseste}';

    protected $description = 'Completeaza raionul pentru livrarile vechi folosind localitatea si lookup fuzzy.';

    public function handle(): int
    {
        if (! Schema::hasTable('livrari') || ! Schema::hasColumn('livrari', 'raion')) {
            $this->error('Tabela livrari sau coloana raion nu exista.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') !== null ? max(0, (int) $this->option('limit')) : null;
        $onlyMissing = (bool) $this->option('only-missing');
        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $ambiguous = 0;

        $query = DB::table('livrari')
            ->select(['id', 'localitate', 'adresa_livrarii', 'raion'])
            ->orderBy('id');

        if ($onlyMissing) {
            $query->where(function ($q) {
                $q->whereNull('raion')
                    ->orWhereRaw("TRIM(COALESCE(raion, '')) IN ('', '-', '—')");
            });
        }

        $query->chunkById(250, function ($rows) use ($dryRun, $limit, &$processed, &$updated, &$skipped, &$ambiguous) {
            foreach ($rows as $row) {
                if ($limit !== null && $processed >= $limit) {
                    return false;
                }

                $processed++;
                $currentRaion = trim((string) ($row->raion ?? ''));
                $localitate = trim((string) ($row->localitate ?? ''));
                $address = trim((string) ($row->adresa_livrarii ?? ''));

                if ($localitate === '') {
                    $skipped++;
                    continue;
                }

                if ($currentRaion !== '' && LocalitatiMoldova::isKnownRaion($currentRaion)) {
                    $skipped++;
                    continue;
                }

                $match = LocalitatiMoldova::bestLocalitateMatch($localitate);
                $newRaion = LocalitatiMoldova::raionForLocalitateAndAddress($localitate, $address, $currentRaion);

                if (! LocalitatiMoldova::isKnownRaion($newRaion)) {
                    $ambiguous++;
                    $this->line(sprintf(
                        'SKIP #%d: "%s" / "%s" are %s',
                        $row->id,
                        $localitate,
                        $address,
                        $match === null ? '0 potriviri' : 'mai multe raioane: '.implode(', ', $match['raioane'])
                    ));
                    continue;
                }

                if ($dryRun) {
                    $this->line(sprintf('DRY #%d: "%s" | "%s" -> "%s"', $row->id, $localitate, $currentRaion ?: '-', $newRaion));
                } else {
                    DB::table('livrari')
                        ->where('id', $row->id)
                        ->update([
                            'localitate' => $match['localitate'] ?? $localitate,
                            'raion' => $newRaion,
                            'in_chisinau' => LocalitatiMoldova::normalizeSearch($newRaion) === 'chisinau',
                            'updated_at' => now(),
                        ]);
                }

                $updated++;
            }
        });

        $this->info(sprintf(
            '%s. Procesate: %d, actualizabile/actualizate: %d, sarite: %d, ambigue: %d.',
            $dryRun ? 'Dry-run terminat' : 'Backfill terminat',
            $processed,
            $updated,
            $skipped,
            $ambiguous
        ));

        return self::SUCCESS;
    }
}
