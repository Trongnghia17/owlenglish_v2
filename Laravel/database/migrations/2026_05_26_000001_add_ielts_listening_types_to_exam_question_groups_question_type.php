<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES_WITH_IELTS_LISTENING = "'multiple_choice','yes_no_not_given','true_false_not_given','short_text','note_completion','fill_in_blank','matching','plan_map_diagram_labelling','form_completion','table_completion','flow_chart_completion','summary_completion','sentence_completion','short_answer_questions','table_selection','essay','speaking'";
    private const TYPES_WITHOUT_IELTS_LISTENING = "'multiple_choice','yes_no_not_given','true_false_not_given','short_text','note_completion','fill_in_blank','matching','table_selection','essay','speaking'";
    private const NEW_TYPES = [
        'plan_map_diagram_labelling',
        'form_completion',
        'table_completion',
        'flow_chart_completion',
        'summary_completion',
        'sentence_completion',
        'short_answer_questions',
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITH_IELTS_LISTENING .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('exam_question_groups')
            ->whereIn('question_type', self::NEW_TYPES)
            ->update(['question_type' => 'short_text']);

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITHOUT_IELTS_LISTENING .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }
};
