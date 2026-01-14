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
        Schema::create('user_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('test_type'); // 'exam', 'skill', 'section', 'test'
            $table->unsignedBigInteger('test_id');
            $table->string('title')->nullable();
            $table->text('content');
            $table->text('selected_text')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'test_type', 'test_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notes');
    }
};
