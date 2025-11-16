<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Thay đổi để cho phép questions thuộc trực tiếp về section
     * (cho speaking và writing) hoặc thuộc về question_group (cho listening và reading)
     */
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Drop foreign key và index cũ
            $table->dropForeign(['exam_question_group_id']);
            $table->dropIndex('idx_questions_group_active');
            
            // Thay đổi exam_question_group_id thành nullable
            $table->foreignId('exam_question_group_id')->nullable()->change();
            
            // Thêm exam_section_id (nullable - chỉ dùng cho speaking và writing)
            $table->foreignId('exam_section_id')->nullable()->after('exam_question_group_id')
                ->constrained('exam_sections')->onDelete('cascade');
            
            // Tạo lại foreign key cho exam_question_group_id với nullable
            $table->foreign('exam_question_group_id')
                ->references('id')->on('exam_question_groups')
                ->onDelete('cascade');
            
            // Thêm index mới
            $table->index(['exam_question_group_id', 'is_active'], 'idx_questions_group_active');
            $table->index(['exam_section_id', 'is_active'], 'idx_questions_section_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Drop foreign keys và indexes
            $table->dropForeign(['exam_section_id']);
            $table->dropForeign(['exam_question_group_id']);
            $table->dropIndex('idx_questions_group_active');
            $table->dropIndex('idx_questions_section_active');
            
            // Remove exam_section_id column
            $table->dropColumn('exam_section_id');
            
            // Restore exam_question_group_id to NOT NULL
            $table->foreignId('exam_question_group_id')->change();
            
            // Recreate foreign key
            $table->foreign('exam_question_group_id')
                ->references('id')->on('exam_question_groups')
                ->onDelete('cascade');
            
            // Recreate original index
            $table->index(['exam_question_group_id', 'is_active'], 'idx_questions_group_active');
        });
    }
};
