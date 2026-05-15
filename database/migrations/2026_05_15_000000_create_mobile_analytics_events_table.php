<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 80)->index();
            $table->string('session_id', 128)->nullable()->index();
            $table->string('mobile_user_id', 64)->nullable()->index();
            $table->string('device_id', 128)->nullable()->index();
            $table->string('platform', 32)->nullable()->index();
            $table->string('app_version', 32)->nullable();
            $table->string('page', 255)->nullable()->index();
            $table->string('previous_page', 255)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedTinyInteger('checkout_step')->nullable()->index();
            $table->decimal('cart_total', 12, 2)->nullable();
            $table->unsignedInteger('items_count')->nullable();
            $table->string('banner_id', 128)->nullable()->index();
            $table->string('banner_title', 255)->nullable();
            $table->string('order_id', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['event_name', 'occurred_at']);
            $table->index(['page', 'event_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_analytics_events');
    }
};
