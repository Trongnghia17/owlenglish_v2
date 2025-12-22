<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');                 // Tên gói
            $table->integer('duration');            // Số tháng (1, 3, 12)
            $table->integer('price');               // Giá gốc
            $table->integer('discount_percent')->default(0); // % giảm
            $table->integer('final_price');         // Giá sau giảm

            $table->boolean('is_featured')->default(false); // Gói nổi bật
            $table->integer('display_order')->default(0);   // Thứ tự hiển thị
            $table->tinyInteger('status')->default(1);      // 1: hiện | 0: ẩn

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_packages');
    }
};
