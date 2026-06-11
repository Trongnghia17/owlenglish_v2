<?php

namespace Tests\Unit;

use App\Exports\SkillImportTemplateExport;
use App\Models\ExamSkill;
use App\Services\SkillExcelImportService;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $sheets = $export->sheets();

        $this->assertCount(22, $export->headings());
        $this->assertCount(14, $sheets);
        $this->assertSame('Multiple Choice', $sheets[0]->title());
        $this->assertSame('True False Not Given', $sheets[1]->title());
        $this->assertSame('Yes No Not Given', $sheets[2]->title());
        $this->assertSame('Matching Information', $sheets[3]->title());
        $this->assertSame('Matching Headings', $sheets[4]->title());
        $this->assertSame('Matching Features', $sheets[5]->title());
        $this->assertSame('Matching Sentence Endings', $sheets[6]->title());
        $this->assertSame('Sentence Completion', $sheets[7]->title());
        $this->assertSame('Summary Completion', $sheets[8]->title());
        $this->assertSame('Note Completion', $sheets[9]->title());
        $this->assertSame('Table Completion', $sheets[10]->title());
        $this->assertSame('Flow-chart Completion', $sheets[11]->title());
        $this->assertSame('Diagram Label Completion', $sheets[12]->title());
        $this->assertSame('Short-answer Questions', $sheets[13]->title());
        $this->assertCount(119, $rows);
        $this->assertSame('Reading Passage 3 - To catch a king', $rows[0][1]);
        $this->assertInstanceOf(RichText::class, $rows[0][2]);
        $this->assertStringContainsString('To catch a king', $rows[0][2]->getPlainText());
        $this->assertRichTextHasBoldRun($rows[0][2]);
        $this->assertReadingTemplateSectionsHaveBoldEvidence($sheets);
        $this->assertSame('What is the reviewer\'s main purpose in the first paragraph?', $rows[0][11]);
        $this->assertSame('yes', $rows[1][17]);
        $this->assertSame('it fails to address whether Charles II\'s experiences had a lasting influence on him.', $rows[19][15]);
        $this->assertSame('yes', $rows[19][17]);
        $this->assertSame('Reading Passage 1 - Why we need to protect polar bears', $rows[20][1]);
        $this->assertSame('true_false_not_given', $rows[20][8]);
        $this->assertSame('true_false_not_given', $rows[20][12]);
        $this->assertSame('False', $rows[20][15]);
        $this->assertSame('Not Given', $rows[22][15]);
        $this->assertSame('Reading Passage 1 - The concept of intelligence', $rows[27][1]);
        $this->assertSame('yes_no_not_given', $rows[27][8]);
        $this->assertSame('yes_no_not_given', $rows[27][12]);
        $this->assertSame('Not Given', $rows[27][15]);
        $this->assertSame('No', $rows[28][15]);
        $this->assertSame('Yes', $rows[29][15]);
        $this->assertSame('Reading Passage 2 - Back to the future of skyscraper design', $rows[30][1]);
        $this->assertSame('matching_information', $rows[30][8]);
        $this->assertSame('matching_information', $rows[30][12]);
        $this->assertSame('why some people avoided hospitals in the 19th century', $rows[30][11]);
        $this->assertSame('Paragraph F', $rows[35][15]);
        $this->assertSame('yes', $rows[35][17]);
        $this->assertSame('Paragraph C', $rows[39][15]);
        $this->assertSame('yes', $rows[39][17]);
        $this->assertSame('Reading Passage 2 - The Desolenator: producing clean water', $rows[43][1]);
        $this->assertSame('matching_headings', $rows[43][8]);
        $this->assertSame('matching_headings', $rows[43][12]);
        $this->assertSame('Section A', $rows[43][11]);
        $this->assertSame('From initial inspiration to new product', $rows[45][15]);
        $this->assertSame('yes', $rows[45][17]);
        $this->assertSame('Cleaning water from a range of sources', $rows[53][15]);
        $this->assertSame('yes', $rows[53][17]);
        $this->assertSame('Getting the finance for production', $rows[58][15]);
        $this->assertSame('yes', $rows[58][17]);
        $this->assertSame('Reading Passage 3 - Should we try to bring extinct species back to life?', $rows[59][1]);
        $this->assertSame('matching_features', $rows[59][8]);
        $this->assertSame('matching_features', $rows[59][12]);
        $this->assertSame('Reintroducing an extinct species to its original habitat could improve the health of a particular species living there.', $rows[59][11]);
        $this->assertSame('Michael Archer', $rows[60][15]);
        $this->assertSame('yes', $rows[60][17]);
        $this->assertSame('Beth Shapiro', $rows[62][15]);
        $this->assertSame('yes', $rows[62][17]);
        $this->assertSame('Beth Shapiro', $rows[64][15]);
        $this->assertSame('yes', $rows[64][17]);
        $this->assertSame('Reading Passage 1 - Great Migrations', $rows[65][1]);
        $this->assertSame('matching_sentence_endings', $rows[65][8]);
        $this->assertSame('matching_sentence_endings', $rows[65][12]);
        $this->assertSame('According to Dingle, migratory routes are likely to', $rows[65][11]);
        $this->assertSame('follow a straight line.', $rows[71][15]);
        $this->assertSame('yes', $rows[71][17]);
        $this->assertSame('eat more than they need for immediate purposes.', $rows[72][15]);
        $this->assertSame('yes', $rows[72][17]);
        $this->assertSame('be discouraged by difficulties.', $rows[73][15]);
        $this->assertSame('yes', $rows[73][17]);
        $this->assertSame('ignore distractions.', $rows[74][15]);
        $this->assertSame('yes', $rows[74][17]);
        $this->assertSame('Reading Passage 1 - Cutty Sark: the fastest sailing ship of all time', $rows[75][1]);
        $this->assertStringContainsString('{{9}}', $rows[75][6]);
        $this->assertSame('sentence_completion', $rows[75][8]);
        $this->assertSame('sentence_completion', $rows[75][12]);
        $this->assertSame('wool', $rows[75][15]);
        $this->assertSame('navigator', $rows[76][15]);
        $this->assertSame('gale', $rows[77][15]);
        $this->assertSame('training', $rows[78][15]);
        $this->assertSame('fire', $rows[79][15]);
        $this->assertSame('Reading Passage 2 - Driverless cars', $rows[80][1]);
        $this->assertStringContainsString('{{19}}', $rows[80][6]);
        $this->assertSame('summary_completion', $rows[80][8]);
        $this->assertSame('summary_completion', $rows[80][12]);
        $this->assertSame('human error', $rows[80][15]);
        $this->assertSame('car-sharing', $rows[81][15]);
        $this->assertSame('ownership', $rows[82][15]);
        $this->assertSame('mileage', $rows[83][15]);
        $this->assertSame("Reading Passage 1 - The importance of children's play", $rows[84][1]);
        $this->assertStringContainsString('{{1}}', $rows[84][6]);
        $this->assertSame('note_completion', $rows[84][8]);
        $this->assertSame('note_completion', $rows[84][12]);
        $this->assertSame('creativity', $rows[84][15]);
        $this->assertSame('rules', $rows[85][15]);
        $this->assertSame('cities', $rows[86][15]);
        $this->assertSame('traffic', $rows[87][15]);
        $this->assertSame('crime', $rows[88][15]);
        $this->assertSame('competition', $rows[89][15]);
        $this->assertSame('evidence', $rows[90][15]);
        $this->assertSame('life', $rows[91][15]);
        $this->assertSame('Reading Passage 1 - Tourism New Zealand website', $rows[92][1]);
        $this->assertStringContainsString('<table>', $rows[92][6]);
        $this->assertStringContainsString('{{1}}', $rows[92][6]);
        $this->assertSame('table_completion', $rows[92][8]);
        $this->assertSame('table_completion', $rows[92][12]);
        $this->assertSame('update', $rows[92][15]);
        $this->assertSame('environment', $rows[93][15]);
        $this->assertSame('captain', $rows[94][15]);
        $this->assertSame('films', $rows[95][15]);
        $this->assertSame('season', $rows[96][15]);
        $this->assertSame('accommodation', $rows[97][15]);
        $this->assertSame('blog', $rows[98][15]);
        $this->assertSame('Reading Passage 1 - Careers with Kiwi Air', $rows[99][1]);
        $this->assertStringContainsString('{{21}}', $rows[99][6]);
        $this->assertSame('flow_chart_completion', $rows[99][8]);
        $this->assertSame('flow_chart_completion', $rows[99][12]);
        $this->assertSame('application', $rows[99][15]);
        $this->assertSame('Walk-In Day', $rows[100][15]);
        $this->assertSame('swimming test', $rows[101][15]);
        $this->assertSame('verbal references', $rows[102][15]);
        $this->assertSame('recruitment pool', $rows[103][15]);
        $this->assertSame('full interview', $rows[104][15]);
        $this->assertSame('emergency', $rows[105][15]);
        $this->assertSame('Reading Passage 1 - The Falkirk Wheel', $rows[106][1]);
        $this->assertStringContainsString('{{20}}', $rows[106][6]);
        $this->assertSame('diagram_label_completion', $rows[106][8]);
        $this->assertSame('diagram_label_completion', $rows[106][12]);
        $this->assertSame('gates', $rows[106][15]);
        $this->assertSame('clamp', $rows[107][15]);
        $this->assertSame('axle', $rows[108][15]);
        $this->assertSame('cogs', $rows[109][15]);
        $this->assertSame('aqueduct', $rows[110][15]);
        $this->assertSame('wall', $rows[111][15]);
        $this->assertSame('locks', $rows[112][15]);
        $this->assertSame('Reading Passage 1 - The Concept of Childhood in Western Countries', $rows[113][1]);
        $this->assertSame('short_answer_questions', $rows[113][8]);
        $this->assertSame('short_answer_questions', $rows[113][12]);
        $this->assertSame('history of childhood', $rows[113][15]);
        $this->assertSame('miniature adults', $rows[114][15]);
        $this->assertSame('industrialisation', $rows[115][15]);
        $this->assertSame('the Factory Act', $rows[116][15]);
        $this->assertSame('play and education', $rows[117][15]);
        $this->assertSame('classroom', $rows[118][15]);
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

    public function test_preview_reads_bold_section_content_from_excel(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (SkillExcelImportService::HEADINGS as $index => $heading) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $heading);
        }

        $richText = new RichText();
        $richText->createText('Intro ');
        $bold = $richText->createTextRun('bold evidence');
        $bold->getFont()?->setBold(true);
        $richText->createText(' outro.');

        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Section 1');
        $sheet->setCellValue('C2', $richText);
        $sheet->setCellValue('F2', 1);
        $sheet->setCellValue('I2', 'multiple_choice');
        $sheet->setCellValue('K2', 1);
        $sheet->setCellValue('L2', 'Question content');
        $sheet->setCellValue('M2', 'multiple_choice');
        $sheet->setCellValue('O2', 1);
        $sheet->setCellValue('P2', 'Answer content');
        $sheet->setCellValue('R2', 'yes');

        $path = sys_get_temp_dir() . '/skill-import-rich-text-' . uniqid('', true) . '.xlsx';

        try {
            (new Xlsx($spreadsheet))->save($path);

            $preview = (new SkillExcelImportService())->preview(
                new ExamSkill(['skill_type' => 'reading']),
                new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)
            );

            $this->assertSame([], $preview['errors']);
            $this->assertStringContainsString(
                '<strong>bold evidence</strong>',
                $preview['payload']['sections'][0]['content']
            );
        } finally {
            $spreadsheet->disconnectWorksheets();

            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_import_preview_keeps_true_false_not_given_question_type(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'true_false_not_given',
                'question_no' => 1,
                'question_content' => 'Polar bears suffer from health problems.',
                'answer_content' => 'False',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('true_false_not_given', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame(
            'true_false_not_given',
            $preview['payload']['sections'][0]['groups'][1]['questions'][1]['question_type']
        );
    }

    public function test_import_preview_keeps_yes_no_not_given_question_type(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'yes_no_not_given',
                'question_no' => 4,
                'question_content' => 'Scholars may discuss theories without fully understanding each other.',
                'answer_content' => 'Yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('yes_no_not_given', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame(
            'yes_no_not_given',
            $preview['payload']['sections'][0]['groups'][1]['questions'][4]['question_type']
        );
        $this->assertSame('Yes', $preview['payload']['sections'][0]['groups'][1]['questions'][4]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_matching_information_letters(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => 14,
                'question_content' => 'why some people avoided hospitals in the 19th century',
                'question_type' => 'matching_information',
                'answer_no' => 1,
                'answer_content' => 'Paragraph A',
                'is_correct' => 'no',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => 14,
                'question_type' => 'matching_information',
                'answer_no' => 6,
                'answer_content' => 'Paragraph F',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_information',
                'question_no' => 15,
                'question_content' => 'a suggestion that the popularity of tall buildings is linked to prestige',
                'question_type' => 'matching_information',
                'answer_content' => 'C',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('matching_information', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame(
            'matching_information',
            $preview['payload']['sections'][0]['groups'][1]['questions'][14]['question_type']
        );
        $this->assertSame('F', $preview['payload']['sections'][0]['groups'][1]['questions'][14]['answers'][6]['letter']);
        $this->assertSame('<p>Paragraph F</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][14]['answers'][6]['content']);
        $this->assertSame('C', $preview['payload']['sections'][0]['groups'][1]['questions'][15]['answers'][3]['letter']);
    }

    public function test_import_preview_keeps_matching_headings_roman_labels(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_headings',
                'question_no' => 14,
                'question_content' => 'Section A',
                'question_type' => 'matching_headings',
                'answer_no' => 3,
                'answer_content' => 'From initial inspiration to new product',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_headings',
                'question_no' => 15,
                'question_content' => 'Section B',
                'question_type' => 'matching_headings',
                'answer_content' => 'vi',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('matching_headings', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('iii', $preview['payload']['sections'][0]['groups'][1]['questions'][14]['answers'][3]['letter']);
        $this->assertSame(
            '<p>From initial inspiration to new product</p>',
            $preview['payload']['sections'][0]['groups'][1]['questions'][14]['answers'][3]['content']
        );
        $this->assertSame('vi', $preview['payload']['sections'][0]['groups'][1]['questions'][15]['answers'][6]['letter']);
        $this->assertSame('<p>vi</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][15]['answers'][6]['content']);
    }

    public function test_import_preview_keeps_matching_features_letters(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 23,
                'question_content' => 'Reintroducing an extinct species could improve another species.',
                'question_type' => 'matching_features',
                'answer_no' => 1,
                'answer_content' => 'Ben Novak',
                'is_correct' => 'no',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 23,
                'question_type' => 'matching_features',
                'answer_no' => 2,
                'answer_content' => 'Michael Archer',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_features',
                'question_no' => 24,
                'question_content' => 'It is important to concentrate on causes of extinction.',
                'question_type' => 'matching_features',
                'answer_content' => 'C',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('matching_features', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('B', $preview['payload']['sections'][0]['groups'][1]['questions'][23]['answers'][2]['letter']);
        $this->assertSame('<p>Michael Archer</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][23]['answers'][2]['content']);
        $this->assertSame('C', $preview['payload']['sections'][0]['groups'][1]['questions'][24]['answers'][3]['letter']);
        $this->assertSame('<p>C</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][24]['answers'][3]['content']);
    }

    public function test_import_preview_keeps_matching_sentence_endings_letters(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 19,
                'question_content' => 'According to Dingle, migratory routes are likely to',
                'question_type' => 'matching_sentence_endings',
                'answer_no' => 1,
                'answer_content' => 'be discouraged by difficulties.',
                'is_correct' => 'no',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 19,
                'question_type' => 'matching_sentence_endings',
                'answer_no' => 7,
                'answer_content' => 'follow a straight line.',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'matching_sentence_endings',
                'question_no' => 20,
                'question_content' => 'To prepare for migration, animals are likely to',
                'question_type' => 'matching_sentence_endings',
                'answer_content' => 'C',
                'is_correct' => 'yes',
            ]),
        ]);

        $this->assertSame([], $preview['errors']);
        $this->assertSame('matching_sentence_endings', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('G', $preview['payload']['sections'][0]['groups'][1]['questions'][19]['answers'][7]['letter']);
        $this->assertSame('<p>follow a straight line.</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][19]['answers'][7]['content']);
        $this->assertSame('C', $preview['payload']['sections'][0]['groups'][1]['questions'][20]['answers'][3]['letter']);
        $this->assertSame('<p>C</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][20]['answers'][3]['content']);
    }

    public function test_import_preview_keeps_sentence_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => '9 After 1880, Cutty Sark carried {{9}} as its main cargo.',
                'group_question_type' => 'sentence_completion',
                'question_no' => 9,
                'question_content' => 'After 1880, Cutty Sark carried as its main cargo.',
                'question_type' => 'sentence_completion',
                'answer_content' => 'wool',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('sentence_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('<p>9 After 1880, Cutty Sark carried {{9}} as its main cargo.</p>', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('sentence_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][9]['question_type']);
        $this->assertSame('<p>wool</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][9]['answers'][1]['content']);
        $this->assertSame('<p>navigator</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][10]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_summary_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => 'Most motor accidents are partly due to {{19}}.',
                'group_question_type' => 'summary_completion',
                'question_no' => 19,
                'question_content' => 'Most motor accidents are partly due to.',
                'question_type' => 'summary_completion',
                'answer_content' => 'human error',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('summary_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('<p>Most motor accidents are partly due to {{19}}.</p>', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('summary_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][19]['question_type']);
        $this->assertSame('<p>human error</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][19]['answers'][1]['content']);
        $this->assertSame('<p>car-sharing</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][20]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_note_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => "Children's play\n- building a magical kingdom may help develop {{1}}\n- board games involve {{2}} and turn-taking",
                'group_question_type' => 'note_completion',
                'question_no' => 1,
                'question_content' => 'building a magical kingdom may help develop',
                'question_type' => 'note_completion',
                'answer_content' => 'creativity',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('note_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertStringContainsString('{{1}}', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('note_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][1]['question_type']);
        $this->assertSame('<p>creativity</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][1]['answers'][1]['content']);
        $this->assertSame('<p>rules</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][2]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_table_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => '<table><tbody><tr><td>allowed businesses to {{1}} information regularly</td></tr><tr><td>impact on the {{2}}</td></tr></tbody></table>',
                'group_question_type' => 'table_completion',
                'question_no' => 1,
                'question_content' => 'allowed businesses to information regularly',
                'question_type' => 'table_completion',
                'answer_content' => 'update',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('table_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertStringContainsString('<table>', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertStringContainsString('{{1}}', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('table_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][1]['question_type']);
        $this->assertSame('<p>update</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][1]['answers'][1]['content']);
        $this->assertSame('<p>environment</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][2]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_flow_chart_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => '<p>Candidates go online to complete their {{21}}.</p><p>Suitable candidates attend a {{22}}.</p>',
                'group_question_type' => 'flow_chart_completion',
                'question_no' => 21,
                'question_content' => 'Candidates go online to complete their.',
                'question_type' => 'flow_chart_completion',
                'answer_content' => 'application',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('flow_chart_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertStringContainsString('{{21}}', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('flow_chart_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][21]['question_type']);
        $this->assertSame('<p>application</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][21]['answers'][1]['content']);
        $this->assertSame('<p>Walk-In Day</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][22]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_diagram_label_completion_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_content' => '<p>A pair of {{20}} are lifted.</p><p>A {{21}} is taken out.</p>',
                'group_question_type' => 'diagram_label_completion',
                'question_no' => 20,
                'question_content' => 'A pair of are lifted.',
                'question_type' => 'diagram_label_completion',
                'answer_content' => 'gates',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('diagram_label_completion', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertStringContainsString('{{20}}', $preview['payload']['sections'][0]['groups'][1]['content']);
        $this->assertSame('diagram_label_completion', $preview['payload']['sections'][0]['groups'][1]['questions'][20]['question_type']);
        $this->assertSame('<p>gates</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][20]['answers'][1]['content']);
        $this->assertSame('<p>clamp</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][21]['answers'][1]['content']);
    }

    public function test_import_preview_keeps_short_answer_question_answers(): void
    {
        $service = new SkillExcelImportService();
        $skill = new ExamSkill(['skill_type' => 'reading']);

        $preview = $service->buildPreview($skill, [
            $this->importRow([
                'section_no' => 1,
                'group_no' => 1,
                'group_question_type' => 'short_answer_questions',
                'question_no' => 8,
                'question_content' => 'What has become a heated topic?',
                'question_type' => 'short_answer_questions',
                'answer_content' => 'history of childhood',
                'is_correct' => 'yes',
            ]),
            $this->importRow([
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
        $this->assertSame('short_answer_questions', $preview['payload']['sections'][0]['groups'][1]['question_type']);
        $this->assertSame('short_answer_questions', $preview['payload']['sections'][0]['groups'][1]['questions'][8]['question_type']);
        $this->assertSame('<p>history of childhood</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][8]['answers'][1]['content']);
        $this->assertSame('<p>miniature adults</p>', $preview['payload']['sections'][0]['groups'][1]['questions'][9]['answers'][1]['content']);
    }

    private function importRow(array $values): array
    {
        return array_merge(array_fill_keys(SkillExcelImportService::HEADINGS, ''), $values);
    }

    private function assertReadingTemplateSectionsHaveBoldEvidence(array $sheets): void
    {
        foreach ($sheets as $sheet) {
            $sectionContent = null;

            foreach ($sheet->array() as $row) {
                if (($row[2] ?? '') !== '') {
                    $sectionContent = $row[2];
                    break;
                }
            }

            $this->assertInstanceOf(
                RichText::class,
                $sectionContent,
                "Sheet {$sheet->title()} should export section_content as rich text."
            );
            $this->assertRichTextHasBoldRun($sectionContent, "Sheet {$sheet->title()} should contain bold evidence.");
        }
    }

    private function assertRichTextHasBoldRun(RichText $richText, string $message = ''): void
    {
        foreach ($richText->getRichTextElements() as $element) {
            if ($element->getFont()?->getBold()) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail($message ?: 'Expected rich text to contain a bold run.');
    }
}
