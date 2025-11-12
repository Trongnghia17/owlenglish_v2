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
        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_skill_id')->constrained('exam_skills')->onDelete('cascade');
            $table->string('title'); // Part 1, Part 2, Part 3, etc.
            $table->longText('content')->nullable(); // Nội dung chung của section (đoạn văn cho Reading, mô tả cho Listening)
            $table->text('feedback')->nullable(); // Phản hồi/hướng dẫn cho section này
            $table->enum('content_format', ['text', 'audio', 'video', 'image'])->default('text'); // Định dạng nội dung
            $table->string('audio_file')->nullable(); // File audio cho Listening
            $table->string('video_file')->nullable(); // File video nếu có
            $table->json('metadata')->nullable(); // Thông tin bổ sung (cấu hình đặc biệt)
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['exam_skill_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sections');
    }
};
