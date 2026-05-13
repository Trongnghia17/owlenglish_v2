<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_skills', function (Blueprint $table) {
            $table->string('audio_file')->nullable()->after('image');
        });

        DB::table('exam_skills')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($skills) {
                foreach ($skills as $skill) {
                    $sections = DB::table('exam_sections')
                        ->where('exam_skill_id', $skill->id)
                        ->orderBy('id')
                        ->get(['audio_file', 'metadata']);

                    $audioFile = null;

                    foreach ($sections as $section) {
                        if (!empty($section->audio_file)) {
                            $audioFile = $section->audio_file;
                            break;
                        }

                        if (empty($section->metadata)) {
                            continue;
                        }

                        $metadata = json_decode($section->metadata, true);
                        if (is_array($metadata) && !empty($metadata['audio_file'])) {
                            $audioFile = $metadata['audio_file'];
                            break;
                        }
                    }

                    if ($audioFile) {
                        DB::table('exam_skills')
                            ->where('id', $skill->id)
                            ->update(['audio_file' => $audioFile]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_skills', function (Blueprint $table) {
            $table->dropColumn('audio_file');
        });
    }
};
