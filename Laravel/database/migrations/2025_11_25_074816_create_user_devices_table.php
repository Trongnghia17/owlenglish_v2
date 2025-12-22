<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Device info
            $table->string('device_name')->nullable();             // iPad Air, Desktop 2, v.v.
            $table->string('device_type')->nullable();             // mobile / tablet / desktop
            $table->string('platform')->nullable();                // iOS / Android / Windows
            $table->string('browser')->nullable();                 // Chrome / Safari / Edge

            // Identification
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_hash', 255)->nullable();        // hash theo IP + UA

            // Activity
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // Location
            $table->string('location_city')->nullable();
            $table->string('location_country')->nullable();

            // Status
            $table->enum('status', ['active', 'logged_out'])->default('active');

            // token hoặc session để logout từ xa
            $table->string('session_id')->nullable();

            $table->timestamps();

            // Indexing
            $table->index(['user_id', 'status']);
            $table->index(['device_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
