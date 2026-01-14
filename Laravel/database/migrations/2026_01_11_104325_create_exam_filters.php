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
        Schema::create('exam_filters', function (Blueprint $table) {
            $table->id();

            $table->string('name');              // Tên hiển thị
            $table->string('slug')->nullable();  // dùng cho FE / URL
            $table->enum('type', [
                'skill',    // Listening, Reading
                'group',    // Theo dạng, Theo phần, Other
                'value'     // Multiple choice, Section 1...
            ]);

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('exam_filters')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_filters');
    }
};
