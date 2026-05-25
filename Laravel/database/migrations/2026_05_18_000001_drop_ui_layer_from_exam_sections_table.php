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
        if (!Schema::hasColumn('exam_sections', 'ui_layer')) {
            return;
        }

        Schema::table('exam_sections', function (Blueprint $table) {
            $table->dropColumn('ui_layer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('exam_sections', 'ui_layer')) {
            return;
        }

        Schema::table('exam_sections', function (Blueprint $table) {
            $table->enum('ui_layer', ['1', '2'])->nullable()->after('audio_file');
        });
    }
};
