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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_skill_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('exam_section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('exam_test_id')->nullable()->constrained()->onDelete('cascade');
            
            // Test info
            $table->integer('total_questions')->default(0);
            $table->integer('answered_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score', 5, 2)->default(0); // Band score (0-9)
            $table->integer('time_spent')->default(0); // seconds
            
            // Status
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            
            // Answers (JSON)
            $table->json('answers')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
