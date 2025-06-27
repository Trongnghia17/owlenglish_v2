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
        Schema::create('general_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_content_id')->constrained()->onDelete('cascade');
            $table->text('title')->nullable();
            $table->longText('text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('type', ['multiple_choice', 'yes_no_ng', 'true_false_ng', 'short_text']);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_questions');
    }
}; 