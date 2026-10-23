<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Cine era pe vechea temă implicită (dark sau nesetat) trece pe Neon Glass; Dark Red rămâne neschimbat. */
    public function up(): void
    {
        DB::table('users')
            ->where(fn ($q) => $q->whereNull('theme')->orWhere('theme', 'dark'))
            ->update(['theme' => 'neon-glass']);
    }

    public function down(): void
    {
        DB::table('users')->where('theme', 'neon-glass')->update(['theme' => 'dark']);
    }
};
