<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_section_filter', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_section_id')
                ->constrained('exam_sections')
                ->cascadeOnDelete();

            $table->foreignId('exam_filter_id')
                ->constrained('exam_filters')
                ->cascadeOnDelete();

            $table->timestamps();

            // Tránh trùng filter cho cùng section
            $table->unique(
                ['exam_section_id', 'exam_filter_id'],
                'section_filter_unique'
            );

            $table->index(['exam_filter_id', 'exam_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_section_filter');
    }
};
