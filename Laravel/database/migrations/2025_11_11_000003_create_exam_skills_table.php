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
        Schema::create('exam_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_test_id')->constrained('exam_tests')->onDelete('cascade');
            $table->enum('skill_type', ['reading', 'writing', 'speaking', 'listening']); // 4 kỹ năng
            $table->string('name'); // Reading, Writing, Speaking, Listening
            $table->text('description')->nullable();
            $table->integer('time_limit')->nullable(); // Thời gian làm bài (phút)
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['exam_test_id', 'skill_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_skills');
    }
};
