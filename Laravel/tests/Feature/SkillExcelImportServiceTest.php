<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamQuestionGroup;
use App\Models\ExamSection;
use App\Models\ExamSkill;
use App\Models\ExamTest;
use App\Services\SkillExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SkillExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension is required for database import tests.');
        }

        parent::setUp();
    }

    public function test_preview_groups_reading_rows_into_sections_groups_questions_and_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Section 1',
                'group_no' => 1,
                'group_instructions' => 'Questions 1 - 2',
                'group_question_type' => 'multiple_choice',
                'question_no' => 1,
                'question_content' => 'Question 1',
                'question_type' => 'multiple_choice',
                'answer_no' => 1,
                'answer_content' => 'Answer A',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'question_no' => 1,
                'answer_no' => 2,
                'answer_content' => 'Answer B',
                'is_correct' => 'no',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'question_no' => 2,
                'question_content' => 'Question 2',
                'answer_no' => 1,
                'answer_content' => 'Answer C',
                'is_correct' => 'true',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame([
            'rows' => 3,
            'sections' => 1,
            'groups' => 1,
            'questions' => 2,
            'direct_questions' => 0,
            'answers' => 3,
        ], $preview['summary']);
    }

    public function test_replace_content_removes_old_builder_content_and_creates_imported_content(): void
    {
        $skill = $this->createSkill('reading');
        $oldSection = $skill->sections()->create([
            'title' => 'Old Section',
            'content' => '',
            'feedback' => '',
            'content_format' => 'text',
            'metadata' => [],
            'is_active' => true,
        ]);
        $oldGroup = $oldSection->questionGroups()->create([
            'content' => '',
            'question_type' => 'multiple_choice',
            'instructions' => null,
            'options' => [],
            'is_active' => true,
        ]);
        $oldGroup->questions()->create([
            'content' => 'Old Question',
            'metadata' => [],
            'is_active' => true,
        ]);

        $service = app(SkillExcelImportService::class);
        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Imported Section',
                'group_no' => 1,
                'group_question_type' => 'multiple_choice',
                'question_no' => 1,
                'question_content' => 'Imported Question',
                'answer_no' => 1,
                'answer_content' => 'Correct Answer',
                'is_correct' => '1',
            ]),
        ]);

        $service->replaceContent($skill, $preview['payload']);

        $this->assertNull(ExamSection::find($oldSection->id));
        $this->assertNull(ExamQuestionGroup::find($oldGroup->id));

        $section = $skill->sections()->first();
        $this->assertSame('Imported Section', $section->title);

        $question = $section->questionGroups()->first()->questions()->first();
        $this->assertSame('Imported Question', trim(strip_tags($question->content)));
        $this->assertSame('Correct Answer', trim(strip_tags($question->answer_content)));
        $this->assertTrue($question->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_direct_questions_for_writing(): void
    {
        $skill = $this->createSkill('writing');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Task 1',
                'direct_question_no' => 1,
                'question_content' => 'Write at least 150 words.',
                'point' => 9,
                'sample_answer' => 'Band 9 sample',
                'question_feedback' => 'Criteria',
                'hint' => 'Plan first',
            ]),
            $this->row([
                'section_no' => 1,
                'direct_question_no' => 2,
                'question_content' => 'Write at least 251 words.',
                'point' => 9,
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $section = $skill->sections()->first();
        $this->assertSame(0, $section->questionGroups()->count());
        $this->assertSame(2, $section->questions()->whereNull('exam_question_group_id')->count());
        $this->assertSame(
            'Band 9 sample',
            trim(strip_tags($section->questions()->whereNull('exam_question_group_id')->first()->answer_content))
        );
    }

    public function test_preview_accepts_true_false_not_given_as_one_answer_row_per_question(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_instructions' => 'Questions 1 - 2',
                'group_question_type' => 'true_false_not_given',
                'question_no' => 1,
                'question_content' => 'Polar bears suffer from health problems.',
                'answer_content' => 'False',
                'question_feedback' => 'The passage says polar bears experience no such consequences.',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'true_false_not_given',
                'question_no' => 2,
                'question_content' => 'The researchers were the first to compare the bears genetically.',
                'answer_content' => 'Not Given',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame(2, $preview['summary']['questions']);
        $this->assertSame(2, $preview['summary']['answers']);

        $this->assertSame('true_false_not_given', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame(
            'true_false_not_given',
            $preview['payload']['sections'][0]['groups'][1]['questions'][1]['question_type']
        );

        $answers = $preview['payload']['sections'][0]['groups'][1]['questions'][1]['answers'];

        $this->assertSame('False', $answers[1]['content']);
        $this->assertTrue($answers[1]['is_correct']);
    }

    public function test_replace_content_imports_matching_information_with_letter_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 2',
                'group_no' => 1,
                'group_instructions' => 'Which section contains the following information?',
                'group_question_type' => 'matching_information',
                'question_no' => 14,
                'question_content' => 'why some people avoided hospitals in the 19th century',
                'question_type' => 'matching_information',
                'answer_no' => 1,
                'answer_content' => 'Paragraph A',
                'is_correct' => 'no',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => 14,
                'question_type' => 'matching_information',
                'answer_no' => 6,
                'answer_content' => 'Paragraph F',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => 15,
                'question_content' => 'a suggestion that the popularity of tall buildings is linked to prestige',
                'question_type' => 'matching_information',
                'answer_no' => 3,
                'answer_content' => 'Paragraph C',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('matching_information', $group->question_type);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('F', $questions[0]->answer_content);
        $this->assertSame('C', $questions[1]->answer_content);
        $this->assertSame('F', $questions[0]->metadata['answers'][1]['letter']);
        $this->assertSame('<p>Paragraph F</p>', $questions[0]->metadata['answers'][1]['content']);
    }

    public function test_replace_content_imports_matching_headings_with_roman_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 2',
                'group_no' => 1,
                'group_instructions' => 'Choose the correct heading for each section.',
                'group_question_type' => 'matching_headings',
                'question_no' => 14,
                'question_content' => 'Section A',
                'question_type' => 'matching_headings',
                'answer_no' => 1,
                'answer_content' => 'Getting the finance for production',
                'is_correct' => 'no',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_headings',
                'question_no' => 14,
                'question_type' => 'matching_headings',
                'answer_no' => 3,
                'answer_content' => 'From initial inspiration to new product',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_headings',
                'question_no' => 15,
                'question_content' => 'Section B',
                'question_type' => 'matching_headings',
                'answer_no' => 6,
                'answer_content' => 'Cleaning water from a range of sources',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('matching_headings', $group->question_type);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('iii', $questions[0]->answer_content);
        $this->assertSame('vi', $questions[1]->answer_content);
        $this->assertSame('iii', $questions[0]->metadata['answers'][1]['letter']);
        $this->assertSame('<p>From initial inspiration to new product</p>', $questions[0]->metadata['answers'][1]['content']);
    }

    public function test_replace_content_imports_matching_features_with_reusable_letter_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 3',
                'group_no' => 1,
                'group_instructions' => 'Match each statement with the correct person.',
                'group_question_type' => 'matching_features',
                'question_no' => 23,
                'question_content' => 'Reintroducing an extinct species could improve another species.',
                'question_type' => 'matching_features',
                'answer_no' => 1,
                'answer_content' => 'Ben Novak',
                'is_correct' => 'no',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 23,
                'question_type' => 'matching_features',
                'answer_no' => 2,
                'answer_content' => 'Michael Archer',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 24,
                'question_content' => 'It is important to concentrate on causes of extinction.',
                'question_type' => 'matching_features',
                'answer_no' => 3,
                'answer_content' => 'Beth Shapiro',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 26,
                'question_content' => 'Current efforts at preserving biodiversity are insufficient.',
                'question_type' => 'matching_features',
                'answer_no' => 3,
                'answer_content' => 'Beth Shapiro',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('matching_features', $group->question_type);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('B', $questions[0]->answer_content);
        $this->assertSame('C', $questions[1]->answer_content);
        $this->assertSame('C', $questions[2]->answer_content);
        $this->assertSame('B', $questions[0]->metadata['answers'][1]['letter']);
        $this->assertSame('<p>Michael Archer</p>', $questions[0]->metadata['answers'][1]['content']);
    }

    public function test_replace_content_imports_matching_sentence_endings_with_letter_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_instructions' => 'Complete each sentence with the correct ending.',
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 19,
                'question_content' => 'According to Dingle, migratory routes are likely to',
                'question_type' => 'matching_sentence_endings',
                'answer_no' => 1,
                'answer_content' => 'be discouraged by difficulties.',
                'is_correct' => 'no',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 19,
                'question_type' => 'matching_sentence_endings',
                'answer_no' => 7,
                'answer_content' => 'follow a straight line.',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 20,
                'question_content' => 'To prepare for migration, animals are likely to',
                'question_type' => 'matching_sentence_endings',
                'answer_no' => 3,
                'answer_content' => 'eat more than they need for immediate purposes.',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('matching_sentence_endings', $group->question_type);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('G', $questions[0]->answer_content);
        $this->assertSame('C', $questions[1]->answer_content);
        $this->assertSame('G', $questions[0]->metadata['answers'][1]['letter']);
        $this->assertSame('<p>follow a straight line.</p>', $questions[0]->metadata['answers'][1]['content']);
    }

    public function test_replace_content_imports_sentence_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_content' => '9 After 1880, Cutty Sark carried {{9}} as its main cargo.',
                'group_instructions' => 'Choose ONE WORD ONLY from the passage.',
                'group_question_type' => 'sentence_completion',
                'question_no' => 9,
                'question_content' => 'After 1880, Cutty Sark carried as its main cargo.',
                'question_type' => 'sentence_completion',
                'answer_content' => 'wool',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'sentence_completion',
                'question_no' => 10,
                'question_content' => 'As a captain and, Woodget was very skilled.',
                'question_type' => 'sentence_completion',
                'answer_content' => 'navigator',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('sentence_completion', $group->question_type);
        $this->assertStringContainsString('{{9}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>wool</p>', $questions[0]->answer_content);
        $this->assertSame('<p>navigator</p>', $questions[1]->answer_content);
        $this->assertSame('<p>wool</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_summary_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 2',
                'group_no' => 1,
                'group_content' => 'Most motor accidents are partly due to {{19}}.',
                'group_instructions' => 'Choose NO MORE THAN TWO WORDS from the passage.',
                'group_question_type' => 'summary_completion',
                'question_no' => 19,
                'question_content' => 'Most motor accidents are partly due to.',
                'question_type' => 'summary_completion',
                'answer_content' => 'human error',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'summary_completion',
                'question_no' => 20,
                'question_content' => 'Schemes for will be more workable.',
                'question_type' => 'summary_completion',
                'answer_content' => 'car-sharing',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('summary_completion', $group->question_type);
        $this->assertStringContainsString('{{19}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>human error</p>', $questions[0]->answer_content);
        $this->assertSame('<p>car-sharing</p>', $questions[1]->answer_content);
        $this->assertSame('<p>human error</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_note_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_content' => "Children's play\n- building a magical kingdom may help develop {{1}}\n- board games involve {{2}} and turn-taking",
                'group_instructions' => 'Choose ONE WORD ONLY from the passage.',
                'group_question_type' => 'note_completion',
                'question_no' => 1,
                'question_content' => 'building a magical kingdom may help develop',
                'question_type' => 'note_completion',
                'answer_content' => 'creativity',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'note_completion',
                'question_no' => 2,
                'question_content' => 'board games involve and turn-taking',
                'question_type' => 'note_completion',
                'answer_content' => 'rules',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('note_completion', $group->question_type);
        $this->assertStringContainsString('{{1}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>creativity</p>', $questions[0]->answer_content);
        $this->assertSame('<p>rules</p>', $questions[1]->answer_content);
        $this->assertSame('<p>creativity</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_table_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_content' => '<table><tbody><tr><td>allowed businesses to {{1}} information regularly</td></tr><tr><td>impact on the {{2}}</td></tr></tbody></table>',
                'group_instructions' => 'Choose ONE WORD ONLY from the passage.',
                'group_question_type' => 'table_completion',
                'question_no' => 1,
                'question_content' => 'allowed businesses to information regularly',
                'question_type' => 'table_completion',
                'answer_content' => 'update',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'table_completion',
                'question_no' => 2,
                'question_content' => 'impact on the',
                'question_type' => 'table_completion',
                'answer_content' => 'environment',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('table_completion', $group->question_type);
        $this->assertStringContainsString('<table>', $group->content);
        $this->assertStringContainsString('{{1}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>update</p>', $questions[0]->answer_content);
        $this->assertSame('<p>environment</p>', $questions[1]->answer_content);
        $this->assertSame('<p>update</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_flow_chart_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_content' => '<p>Candidates go online to complete their {{21}}.</p><p>Suitable candidates attend a {{22}}.</p>',
                'group_instructions' => 'Choose NO MORE THAN TWO WORDS from the text.',
                'group_question_type' => 'flow_chart_completion',
                'question_no' => 21,
                'question_content' => 'Candidates go online to complete their.',
                'question_type' => 'flow_chart_completion',
                'answer_content' => 'application',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'flow_chart_completion',
                'question_no' => 22,
                'question_content' => 'Suitable candidates attend a.',
                'question_type' => 'flow_chart_completion',
                'answer_content' => 'Walk-In Day',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('flow_chart_completion', $group->question_type);
        $this->assertStringContainsString('{{21}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>application</p>', $questions[0]->answer_content);
        $this->assertSame('<p>Walk-In Day</p>', $questions[1]->answer_content);
        $this->assertSame('<p>application</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_diagram_label_completion_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_content' => '<p>A pair of {{20}} are lifted.</p><p>A {{21}} is taken out.</p>',
                'group_instructions' => 'Choose ONE WORD from the passage.',
                'group_question_type' => 'diagram_label_completion',
                'question_no' => 20,
                'question_content' => 'A pair of are lifted.',
                'question_type' => 'diagram_label_completion',
                'answer_content' => 'gates',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'diagram_label_completion',
                'question_no' => 21,
                'question_content' => 'A is taken out.',
                'question_type' => 'diagram_label_completion',
                'answer_content' => 'clamp',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('diagram_label_completion', $group->question_type);
        $this->assertStringContainsString('{{20}}', $group->content);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>gates</p>', $questions[0]->answer_content);
        $this->assertSame('<p>clamp</p>', $questions[1]->answer_content);
        $this->assertSame('<p>gates</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_replace_content_imports_short_answer_question_text_answers(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_title' => 'Passage 1',
                'group_no' => 1,
                'group_instructions' => 'Choose NO MORE THAN THREE WORDS from the passage.',
                'group_question_type' => 'short_answer_questions',
                'question_no' => 8,
                'question_content' => 'What has become a heated topic?',
                'question_type' => 'short_answer_questions',
                'answer_content' => 'history of childhood',
                'is_correct' => 'yes',
            ]),
            $this->row([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'short_answer_questions',
                'question_no' => 9,
                'question_content' => 'What were children regarded as?',
                'question_type' => 'short_answer_questions',
                'answer_content' => 'miniature adults',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);

        $service->replaceContent($skill, $preview['payload']);

        $group = $skill->sections()->first()->questionGroups()->first();
        $this->assertSame('short_answer_questions', $group->question_type);

        $questions = $group->questions()->orderBy('id')->get();
        $this->assertSame('<p>history of childhood</p>', $questions[0]->answer_content);
        $this->assertSame('<p>miniature adults</p>', $questions[1]->answer_content);
        $this->assertSame('<p>history of childhood</p>', $questions[0]->metadata['answers'][0]['content']);
        $this->assertTrue($questions[0]->metadata['answers'][0]['is_correct']);
    }

    public function test_preview_reports_invalid_question_type_missing_question_and_unknown_filter_slug(): void
    {
        $skill = $this->createSkill('reading');
        $service = app(SkillExcelImportService::class);

        $preview = $service->buildPreview($skill, [
            $this->row([
                'section_no' => 1,
                'section_filter_slugs' => 'missing-filter',
                'group_no' => 1,
                'group_question_type' => 'not-a-type',
                'question_no' => 1,
                'answer_content' => 'Answer',
                'is_correct' => 'maybe',
            ]),
        ]);

        $messages = collect($preview['errors'])->pluck('message')->implode("\n");

        $this->assertStringContainsString('unknown slug', $messages);
        $this->assertStringContainsString('question_content is required', $messages);
        $this->assertStringContainsString('group_question_type', $messages);
        $this->assertStringContainsString('is_correct', $messages);
    }

    public function test_preview_route_rejects_non_xlsx_files(): void
    {
        $skill = $this->createSkill('reading');

        $this->withoutMiddleware()
            ->post(route('admin.skills.import.preview', $skill), [
                'import_file' => UploadedFile::fake()->create('quiz.txt', 1, 'text/plain'),
            ])
            ->assertSessionHasErrors('import_file');
    }

    private function createSkill(string $skillType): ExamSkill
    {
        $exam = Exam::create([
            'name' => 'IELTS',
            'type' => 'ielts',
            'is_active' => true,
        ]);

        $test = ExamTest::create([
            'exam_id' => $exam->id,
            'name' => 'Test 1',
            'is_active' => true,
        ]);

        return ExamSkill::create([
            'exam_test_id' => $test->id,
            'skill_type' => $skillType,
            'name' => ucfirst($skillType),
            'description' => '',
            'time_limit' => 60,
            'is_active' => true,
            'is_online' => false,
        ]);
    }

    private function row(array $overrides): array
    {
        return array_merge(array_fill_keys(SkillExcelImportService::HEADINGS, ''), $overrides);
    }
}
