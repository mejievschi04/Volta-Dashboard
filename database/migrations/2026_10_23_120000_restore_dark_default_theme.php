<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Tema implicită redevine Dark: cine a fost mutat automat pe Neon Glass revine la Dark. */
    public function up(): void
    {
        DB::table('users')->where('theme', 'neon-glass')->update(['theme' => 'dark']);
    }

    public function down(): void
    {
    }
};
