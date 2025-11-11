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
        Schema::create('exam_question_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_section_id')->constrained('exam_sections')->onDelete('cascade');
            $table->longText('content')->nullable(); // Nội dung nhóm câu hỏi (văn bản, hình ảnh chung cho nhóm)
            $table->enum('question_type', [
                'multiple_choice',           // Trắc nghiệm
                'yes_no_not_given',         // Yes/No/Not Given (IELTS Reading)
                'true_false_not_given',     // True/False/Not Given (IELTS Reading)
                'short_text',               // Điền từ ngắn
                'fill_in_blank',            // Điền vào chỗ trống
                'matching',                 // Nối
                'table_selection',          // Chọn trong bảng
                'essay',                    // Viết luận (Writing)
                'speaking'                  // Nói (Speaking)
            ])->default('multiple_choice');
            
            $table->enum('answer_layout', [
                'inline',                   // Trả lời đầu vào bên trong nội dung
                'side_by_side',            // Chia nội dung và câu hỏi cạnh nhau
                'drag_drop',               // Cho phép kéo thả câu trả lời
                'standard'                 // Hiển thị tiêu chuẩn (câu hỏi rồi đáp án)
            ])->default('standard');
            
            $table->text('instructions')->nullable(); // Hướng dẫn làm bài cho nhóm câu hỏi
            $table->json('options')->nullable(); // Cấu hình đặc biệt (danh sách đáp án cho drag-drop, etc.)
            $table->integer('order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['exam_section_id', 'question_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_groups');
    }
};
