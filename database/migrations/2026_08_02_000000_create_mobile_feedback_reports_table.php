<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_feedback_reports', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('reporter_name', 128)->nullable();
            $table->string('reporter_email', 255)->nullable();
            $table->string('screenshot_filename', 255)->nullable();
            $table->string('screenshot_mime', 64)->nullable();
            $table->longText('screenshot_base64')->nullable();
            $table->boolean('has_screenshot')->default(false)->index();
            $table->string('status', 32)->default('new')->index();
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

            $table->index(['platform', 'occurred_at']);
            $table->index(['status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_feedback_reports');
    }
};
