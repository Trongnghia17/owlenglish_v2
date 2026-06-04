<?php

namespace Tests\Unit;

use App\Exports\SkillImportTemplateExport;
use App\Models\ExamSkill;
use App\Services\SkillExcelImportService;
use PHPUnit\Framework\TestCase;

class SkillImportTemplateExportTest extends TestCase
{
    public function test_reading_template_contains_complete_multiple_choice_example(): void
    {
        $export = new SkillImportTemplateExport(new ExamSkill([
            'skill_type' => 'reading',
            'name' => 'Reading',
        ]));

        $rows = $export->array();

        $this->assertCount(22, $export->headings());
        $this->assertCount(20, $rows);
        $this->assertSame('Reading Passage 3 - To catch a king', $rows[0][1]);
        $this->assertStringContainsString('To catch a king', $rows[0][2]);
        $this->assertSame('What is the reviewer\'s main purpose in the first paragraph?', $rows[0][11]);
        $this->assertSame('yes', $rows[1][17]);
        $this->assertSame('it fails to address whether Charles II\'s experiences had a lasting influence on him.', $rows[19][15]);
        $this->assertSame('yes', $rows[19][17]);
    }

    public function test_preview_details_exposes_grouped_questions_and_correct_answers(): void
    {
        $service = new SkillExcelImportService();
        $details = $service->previewDetails([
            'sections' => [
                [
                    'section_no' => 1,
                    'title' => 'Section 1',
                    'content' => 'Long passage',
                    'feedback' => '',
                    'filter_ids' => [],
                    'groups' => [
                        [
                            'group_no' => 1,
                            'question_type' => 'multiple_choice',
                            'instructions' => 'Choose A, B, C, or D.',
                            'content' => '',
                            'questions' => [
                                [
                                    'question_no' => 36,
                                    'question_type' => 'multiple_choice',
                                    'content' => 'Question content',
                                    'feedback' => 'Question feedback',
                                    'point' => 1,
                                    'answers' => [
                                        1 => [
                                            'answer_no' => 1,
                                            'content' => 'Wrong answer',
                                            'feedback' => '',
                                            'is_correct' => false,
                                        ],
                                        2 => [
                                            'answer_no' => 2,
                                            'content' => '<strong>Correct answer</strong><script>alert("x")</script>',
                                            'feedback' => 'Correct feedback',
                                            'is_correct' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'direct_questions' => [],
                ],
            ],
        ]);

        $question = $details['sections'][0]['groups'][0]['questions'][0];

        $this->assertSame('Question content', $question['content_preview']);
        $this->assertStringContainsString('Correct answer', $question['answers'][1]['content_preview']);
        $this->assertStringContainsString('<strong>Correct answer</strong>', $question['answers'][1]['content_html']);
        $this->assertStringNotContainsString('script', $question['answers'][1]['content_html']);
        $this->assertTrue($question['answers'][1]['is_correct']);
    }
}
