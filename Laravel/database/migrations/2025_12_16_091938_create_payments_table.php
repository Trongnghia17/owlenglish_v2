<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('payment_packages');

            $table->string('order_code')->unique();
            $table->integer('amount');
            $table->string('currency')->default('VND');

            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'canceled',
                'expired'
            ])->default('pending');

            $table->string('payment_method')->default('payos');
            $table->string('payos_payment_id')->nullable();
            $table->json('payos_data')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
