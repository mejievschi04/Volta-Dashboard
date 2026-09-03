<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_analytics_events', function (Blueprint $table) {
            $table->index(['occurred_at', 'event_name'], 'mae_period_event_idx');
            $table->index(['mobile_user_id', 'occurred_at'], 'mae_user_period_idx');
            $table->index(['session_id', 'occurred_at'], 'mae_session_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_analytics_events', function (Blueprint $table) {
            $table->dropIndex('mae_period_event_idx');
            $table->dropIndex('mae_user_period_idx');
            $table->dropIndex('mae_session_period_idx');
        });
    }
};
