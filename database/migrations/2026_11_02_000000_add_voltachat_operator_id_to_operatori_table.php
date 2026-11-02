<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('operatori') || Schema::hasColumn('operatori', 'voltachat_operator_id')) {
            return;
        }

        Schema::table('operatori', function (Blueprint $table) {
            $table->unsignedBigInteger('voltachat_operator_id')->nullable()->after('activ');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('operatori', 'voltachat_operator_id')) {
            Schema::table('operatori', function (Blueprint $table) {
                $table->dropColumn('voltachat_operator_id');
            });
        }
    }
};
