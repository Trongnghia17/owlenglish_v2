<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_filters', function (Blueprint $table) {
            $table->enum('exam_type', ['ielts', 'toeic'])
                ->after('id')
                ->default('ielts')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('exam_filters', function (Blueprint $table) {
            $table->dropColumn('exam_type');
        });
    }
};
