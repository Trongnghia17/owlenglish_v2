<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES_WITH_NOTE_COMPLETION = "'multiple_choice','yes_no_not_given','true_false_not_given','short_text','note_completion','fill_in_blank','matching','table_selection','essay','speaking'";
    private const TYPES_WITHOUT_NOTE_COMPLETION = "'multiple_choice','yes_no_not_given','true_false_not_given','short_text','fill_in_blank','matching','table_selection','essay','speaking'";

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITH_NOTE_COMPLETION .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('exam_question_groups')
            ->where('question_type', 'note_completion')
            ->update(['question_type' => 'short_text']);

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITHOUT_NOTE_COMPLETION .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }
};
