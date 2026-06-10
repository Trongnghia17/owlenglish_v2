<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPES_WITH_DIAGRAM_LABEL_COMPLETION = "'multiple_choice','yes_no_not_given','true_false_not_given','matching_information','matching_headings','matching_features','matching_sentence_endings','short_text','note_completion','fill_in_blank','matching','plan_map_diagram_labelling','form_completion','table_completion','flow_chart_completion','diagram_label_completion','summary_completion','sentence_completion','short_answer_questions','table_selection','essay','speaking'";
    private const TYPES_WITHOUT_DIAGRAM_LABEL_COMPLETION = "'multiple_choice','yes_no_not_given','true_false_not_given','matching_information','matching_headings','matching_features','matching_sentence_endings','short_text','note_completion','fill_in_blank','matching','plan_map_diagram_labelling','form_completion','table_completion','flow_chart_completion','summary_completion','sentence_completion','short_answer_questions','table_selection','essay','speaking'";

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITH_DIAGRAM_LABEL_COMPLETION .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('exam_question_groups')
            ->where('question_type', 'diagram_label_completion')
            ->update(['question_type' => 'plan_map_diagram_labelling']);

        DB::statement(
            'ALTER TABLE exam_question_groups MODIFY question_type ENUM(' .
            self::TYPES_WITHOUT_DIAGRAM_LABEL_COMPLETION .
            ") NOT NULL DEFAULT 'multiple_choice'"
        );
    }
};
