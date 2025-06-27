<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->unique()->nullable(); // Thêm trường phone
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable(); // Thêm trường xác thực phone
            $table->string('password');
            $table->tinyInteger('role')->default(7)->comment('0: admin, 1: teacher_teaching, 2: teacher_grading, 3: teacher_content, 4: student_care, 5: assistant_content, 6: student_center, 7: student_visitor');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('identifier')->primary(); // Đổi từ email thành identifier để hỗ trợ cả email và phone
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
