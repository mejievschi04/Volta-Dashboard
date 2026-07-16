<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_crashes', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->nullable()->index();
            $table->string('error_type', 255)->index();
            $table->string('error_message', 1024)->nullable();
            $table->longText('stack_trace')->nullable();
            $table->boolean('is_fatal')->default(true)->index();
            $table->string('screen', 255)->nullable()->index();
            $table->string('session_id', 128)->nullable()->index();
            $table->string('mobile_user_id', 64)->nullable()->index();
            $table->string('device_id', 128)->nullable()->index();
            $table->string('platform', 32)->nullable()->index();
            $table->string('app_version', 32)->nullable()->index();
            $table->string('os_version', 64)->nullable();
            $table->string('device_model', 128)->nullable();
            $table->string('build_number', 32)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['platform', 'app_version', 'occurred_at']);
            $table->index(['fingerprint', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_crashes');
    }
};
