<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('raport_lunar_call_center_inputs')
            || Schema::hasColumn('raport_lunar_call_center_inputs', 'plan_individual')) {
            return;
        }

        Schema::table('raport_lunar_call_center_inputs', function (Blueprint $table) {
            $table->decimal('plan_individual', 15, 2)->nullable()->after('apeluri');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('raport_lunar_call_center_inputs', 'plan_individual')) {
            Schema::table('raport_lunar_call_center_inputs', function (Blueprint $table) {
                $table->dropColumn('plan_individual');
            });
        }
    }
};
