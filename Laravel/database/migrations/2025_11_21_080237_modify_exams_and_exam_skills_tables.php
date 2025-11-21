<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật bảng exams: thay đổi enum type và migrate dữ liệu
        Schema::table('exams', function (Blueprint $table) {
            // Đầu tiên, thay đổi type của các bản ghi 'online' thành 'ielts'
            DB::statement("UPDATE exams SET type = 'ielts' WHERE type = 'online'");
            
            // Sau đó thay đổi enum để chỉ còn 'ielts' và 'toeic'
            DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('ielts', 'toeic') NOT NULL DEFAULT 'ielts'");
        });

        // Thêm trường is_online vào bảng exam_skills
        Schema::table('exam_skills', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa trường is_online khỏi exam_skills
        Schema::table('exam_skills', function (Blueprint $table) {
            $table->dropColumn('is_online');
        });

        // Khôi phục lại enum type với 3 giá trị
        Schema::table('exams', function (Blueprint $table) {
            DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('online', 'ielts', 'toeic') NOT NULL DEFAULT 'online'");
        });
    }
};
