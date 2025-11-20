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
        Schema::table('exam_sections', function (Blueprint $table) {
            // Rename video_file column to ui_layer and change type
            $table->renameColumn('video_file', 'ui_layer');
        });
        
        // Change column type after rename
        Schema::table('exam_sections', function (Blueprint $table) {
            $table->enum('ui_layer', ['1', '2'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sections', function (Blueprint $table) {
            // Change back to string type first
            $table->string('ui_layer')->nullable()->change();
        });
        
        // Then rename back
        Schema::table('exam_sections', function (Blueprint $table) {
            $table->renameColumn('ui_layer', 'video_file');
        });
    }
};
