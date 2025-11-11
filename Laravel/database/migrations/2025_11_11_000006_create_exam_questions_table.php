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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_group_id')->constrained('exam_question_groups')->onDelete('cascade');
            $table->longText('content'); // Nội dung câu hỏi
            $table->longText('answer_content')->nullable(); // Nội dung đáp án (có thể là text, JSON cho nhiều đáp án)
            $table->boolean('is_correct')->default(false); // Đáp án đúng (cho multiple choice)
            $table->decimal('point', 5, 2)->default(1.00); // Điểm của câu hỏi
            $table->text('feedback')->nullable(); // Phản hồi/giải thích cho câu hỏi
            $table->text('hint')->nullable(); // Gợi ý
            $table->string('image')->nullable(); // Hình ảnh đi kèm câu hỏi
            $table->string('audio_file')->nullable(); // File audio cho câu hỏi (nếu có)
            $table->json('metadata')->nullable(); // Thông tin bổ sung
            $table->integer('order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['exam_question_group_id', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
