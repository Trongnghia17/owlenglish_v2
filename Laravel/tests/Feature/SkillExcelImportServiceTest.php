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
                'question_content' => 'Write at least 250 words.',
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
