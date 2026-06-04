<?php

namespace App\Services;

use App\Imports\SkillImportRowsImport;
use App\Models\ExamFilter;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionGroup;
use App\Models\ExamSection;
use App\Models\ExamSkill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use HTMLPurifier;
use HTMLPurifier_Config;
use Maatwebsite\Excel\Facades\Excel;

class SkillExcelImportService
{
    private ?HTMLPurifier $htmlPurifier = null;

    public const HEADINGS = [
        'section_no',
        'section_title',
        'section_content',
        'section_feedback',
        'section_filter_slugs',
        'group_no',
        'group_content',
        'group_instructions',
        'group_question_type',
        'number_of_options',
        'question_no',
        'question_content',
        'question_type',
        'point',
        'answer_no',
        'answer_content',
        'answer_feedback',
        'is_correct',
        'direct_question_no',
        'sample_answer',
        'question_feedback',
        'hint',
    ];

    private const QUESTION_TYPES = [
        'multiple_choice',
        'yes_no_not_given',
        'true_false_not_given',
        'short_text',
        'note_completion',
        'table_selection',
        'matching',
        'plan_map_diagram_labelling',
        'form_completion',
        'table_completion',
        'flow_chart_completion',
        'summary_completion',
        'sentence_completion',
        'short_answer_questions',
        'fill_in_blank',
        'essay',
        'speaking',
    ];

    public function preview(ExamSkill $skill, UploadedFile $file): array
    {
        $sheets = Excel::toArray(new SkillImportRowsImport(), $file);
        $rows = $sheets[0] ?? [];

        return $this->buildPreview($skill, $rows);
    }

    public function buildPreview(ExamSkill $skill, array $rows): array
    {
        $errors = [];
        $payload = [
            'skill_id' => $skill->id,
            'skill_type' => $skill->skill_type,
            'sections' => [],
        ];

        $filterMap = $this->sectionFilterMap($skill);
        $rowCount = 0;

        foreach ($rows as $index => $row) {
            $row = $this->normalizeRow($row);

            if ($this->isBlankRow($row)) {
                continue;
            }

            $rowCount++;
            $rowNumber = $index + 2;
            $sectionNo = $this->positiveInt($row['section_no']);

            if (!$sectionNo) {
                $errors[] = $this->error($rowNumber, 'section_no is required and must be a positive number.');
                continue;
            }

            $section =& $this->section($payload['sections'], $sectionNo);
            $this->fillFirstValue($section, 'title', $row['section_title']);
            $this->fillFirstValue($section, 'content', $this->editorHtml($row['section_content']));
            $this->fillFirstValue($section, 'feedback', $this->editorHtml($row['section_feedback']));

            foreach ($this->parseList($row['section_filter_slugs']) as $filterSlug) {
                $normalizedSlug = $this->normalizeFilterSlug($filterSlug);

                if (!isset($filterMap[$normalizedSlug])) {
                    $errors[] = $this->error($rowNumber, "section_filter_slugs contains unknown slug '{$filterSlug}'.");
                    continue;
                }

                $section['filter_ids'][$filterMap[$normalizedSlug]] = $filterMap[$normalizedSlug];
            }

            if ($skill->isWriting() || $skill->isSpeaking()) {
                $this->parseDirectQuestionRow($row, $rowNumber, $section, $errors);
            } else {
                $this->parseGroupedQuestionRow($row, $rowNumber, $section, $errors);
            }
        }

        if ($rowCount === 0) {
            $errors[] = ['row' => null, 'message' => 'The import file does not contain any quiz rows.'];
        }

        $payload['sections'] = $this->sortAssocByNumericKey($payload['sections']);

        return [
            'errors' => $errors,
            'payload' => $payload,
            'summary' => $this->summary($payload, $rowCount),
        ];
    }

    public function replaceContent(ExamSkill $skill, array $payload): void
    {
        DB::transaction(function () use ($skill, $payload): void {
            $this->deleteExistingContent($skill);

            foreach ($payload['sections'] as $sectionData) {
                /** @var ExamSection $section */
                $section = $skill->sections()->create([
                    'title' => $sectionData['title'] ?: 'Section ' . $sectionData['section_no'],
                    'content' => $sectionData['content'] ?? '',
                    'feedback' => $sectionData['feedback'] ?? '',
                    'content_format' => 'text',
                    'metadata' => [],
                    'is_active' => true,
                ]);

                $filterIds = array_values($sectionData['filter_ids'] ?? []);
                if ($filterIds) {
                    $section->filters()->sync($filterIds);
                }

                if ($skill->isWriting() || $skill->isSpeaking()) {
                    $this->createDirectQuestions($section, $sectionData['direct_questions'] ?? []);
                } else {
                    $this->createQuestionGroups($section, $sectionData['groups'] ?? []);
                }
            }
        });
    }

    public function previewDetails(array $payload): array
    {
        return [
            'sections' => array_map(function (array $section): array {
                return [
                    'section_no' => $section['section_no'] ?? null,
                    'title' => $section['title'] ?? '',
                    'content_preview' => $this->textPreview($section['content'] ?? '', 260),
                    'content_html' => $this->htmlPreview($section['content'] ?? ''),
                    'feedback_preview' => $this->textPreview($section['feedback'] ?? '', 160),
                    'feedback_html' => $this->htmlPreview($section['feedback'] ?? ''),
                    'filter_count' => count($section['filter_ids'] ?? []),
                    'groups' => array_map(function (array $group): array {
                        return [
                            'group_no' => $group['group_no'] ?? null,
                            'question_type' => $group['question_type'] ?? '',
                            'instructions_preview' => $this->textPreview($group['instructions'] ?? '', 180),
                            'instructions_html' => $this->htmlPreview($group['instructions'] ?? ''),
                            'content_preview' => $this->textPreview($group['content'] ?? '', 180),
                            'content_html' => $this->htmlPreview($group['content'] ?? ''),
                            'questions' => array_map(function (array $question): array {
                                return [
                                    'question_no' => $question['question_no'] ?? null,
                                    'question_type' => $question['question_type'] ?? '',
                                    'content_preview' => $this->textPreview($question['content'] ?? '', 220),
                                    'content_html' => $this->htmlPreview($question['content'] ?? ''),
                                    'feedback_preview' => $this->textPreview($question['feedback'] ?? '', 180),
                                    'feedback_html' => $this->htmlPreview($question['feedback'] ?? ''),
                                    'point' => $question['point'] ?? 1,
                                    'answers' => array_map(function (array $answer): array {
                                        return [
                                            'answer_no' => $answer['answer_no'] ?? null,
                                            'content_preview' => $this->textPreview($answer['content'] ?? '', 160),
                                            'content_html' => $this->htmlPreview($answer['content'] ?? ''),
                                            'feedback_preview' => $this->textPreview($answer['feedback'] ?? '', 140),
                                            'feedback_html' => $this->htmlPreview($answer['feedback'] ?? ''),
                                            'is_correct' => (bool) ($answer['is_correct'] ?? false),
                                        ];
                                    }, $this->sortAssocByNumericKey($question['answers'] ?? [])),
                                ];
                            }, $this->sortAssocByNumericKey($group['questions'] ?? [])),
                        ];
                    }, $this->sortAssocByNumericKey($section['groups'] ?? [])),
                    'direct_questions' => array_map(function (array $question): array {
                        return [
                            'direct_question_no' => $question['direct_question_no'] ?? null,
                            'content_preview' => $this->textPreview($question['content'] ?? '', 220),
                            'content_html' => $this->htmlPreview($question['content'] ?? ''),
                            'answer_preview' => $this->textPreview($question['answer_content'] ?? '', 180),
                            'answer_html' => $this->htmlPreview($question['answer_content'] ?? ''),
                            'feedback_preview' => $this->textPreview($question['feedback'] ?? '', 180),
                            'feedback_html' => $this->htmlPreview($question['feedback'] ?? ''),
                            'hint' => $question['hint'] ?? '',
                            'point' => $question['point'] ?? 1,
                        ];
                    }, $this->sortAssocByNumericKey($section['direct_questions'] ?? [])),
                ];
            }, $payload['sections'] ?? []),
        ];
    }

    private function parseDirectQuestionRow(array $row, int $rowNumber, array &$section, array &$errors): void
    {
        $directQuestionNo = $this->positiveInt($row['direct_question_no']);

        if (!$directQuestionNo) {
            $errors[] = $this->error($rowNumber, 'direct_question_no is required for writing/speaking imports.');
            return;
        }

        $question =& $this->directQuestion($section['direct_questions'], $directQuestionNo);

        if ($row['question_content'] === '' && $question['content'] === '') {
            $errors[] = $this->error($rowNumber, 'question_content is required.');
        }

        $this->fillFirstValue($question, 'content', $this->editorHtml($row['question_content']));
        $this->fillFirstValue($question, 'answer_content', $this->editorHtml($row['sample_answer']));
        $this->fillFirstValue($question, 'feedback', $this->editorHtml($row['question_feedback']));
        $this->fillFirstValue($question, 'hint', $row['hint']);

        if ($row['point'] !== '') {
            if (!is_numeric($row['point']) || (float) $row['point'] < 0) {
                $errors[] = $this->error($rowNumber, 'point must be a number greater than or equal to 0.');
            } else {
                $question['point'] = (float) $row['point'];
            }
        }
    }

    private function parseGroupedQuestionRow(array $row, int $rowNumber, array &$section, array &$errors): void
    {
        $groupNo = $this->positiveInt($row['group_no']);
        $questionNo = $this->positiveInt($row['question_no']);

        if (!$groupNo) {
            $errors[] = $this->error($rowNumber, 'group_no is required for reading/listening imports.');
            return;
        }

        if (!$questionNo) {
            $errors[] = $this->error($rowNumber, 'question_no is required for reading/listening imports.');
            return;
        }

        if ($row['answer_content'] === '') {
            $errors[] = $this->error($rowNumber, 'answer_content is required for reading/listening imports.');
        }

        $groupType = $this->normalizeQuestionType($row['group_question_type'] ?: $row['question_type'] ?: 'multiple_choice');
        $questionType = $this->normalizeQuestionType($row['question_type'] ?: $groupType);

        if (!$this->isAllowedQuestionType($groupType)) {
            $errors[] = $this->error($rowNumber, "group_question_type '{$row['group_question_type']}' is not supported.");
        }

        if (!$this->isAllowedQuestionType($questionType)) {
            $errors[] = $this->error($rowNumber, "question_type '{$row['question_type']}' is not supported.");
        }

        $group =& $this->group($section['groups'], $groupNo);
        $this->fillFirstValue($group, 'content', $this->editorHtml($row['group_content']));
        $this->fillFirstValue($group, 'instructions', $this->editorHtml($row['group_instructions']));
        $this->fillFirstValue($group, 'question_type', $groupType);

        if ($row['number_of_options'] !== '') {
            $numberOfOptions = $this->positiveInt($row['number_of_options']);
            if (!$numberOfOptions || $numberOfOptions < 2 || $numberOfOptions > 10) {
                $errors[] = $this->error($rowNumber, 'number_of_options must be between 2 and 10.');
            } else {
                $group['number_of_options'] = $numberOfOptions;
            }
        }

        $question =& $this->question($group['questions'], $questionNo);

        if ($row['question_content'] === '' && $question['content'] === '') {
            $errors[] = $this->error($rowNumber, 'question_content is required.');
        }

        $this->fillFirstValue($question, 'content', $this->editorHtml($row['question_content']));
        $this->fillFirstValue($question, 'feedback', $this->editorHtml($row['question_feedback']));
        $this->fillFirstValue($question, 'question_type', $questionType);

        if ($row['point'] !== '') {
            if (!is_numeric($row['point']) || (float) $row['point'] < 0) {
                $errors[] = $this->error($rowNumber, 'point must be a number greater than or equal to 0.');
            } else {
                $question['point'] = (float) $row['point'];
            }
        }

        $answerNo = $this->positiveInt($row['answer_no']) ?: count($question['answers']) + 1;
        $isCorrect = $this->parseBoolean($row['is_correct']);

        if ($isCorrect === null) {
            $errors[] = $this->error($rowNumber, 'is_correct must be yes/no, true/false, or 1/0.');
            $isCorrect = false;
        }

        $question['answers'][$answerNo] = [
            'answer_no' => $answerNo,
            'content' => $this->editorHtml($row['answer_content']),
            'feedback' => $this->editorHtml($row['answer_feedback']),
            'is_correct' => $isCorrect,
        ];
    }

    private function createQuestionGroups(ExamSection $section, array $groups): void
    {
        foreach ($this->sortAssocByNumericKey($groups) as $groupData) {
            $options = [];
            if (!empty($groupData['number_of_options'])) {
                $options['number_of_options'] = (int) $groupData['number_of_options'];
            }

            /** @var ExamQuestionGroup $group */
            $group = $section->questionGroups()->create([
                'content' => $groupData['content'] ?? '',
                'question_type' => $groupData['question_type'] ?? 'multiple_choice',
                'instructions' => $groupData['instructions'] ?? null,
                'options' => $options,
                'is_active' => true,
            ]);

            foreach ($this->sortAssocByNumericKey($groupData['questions'] ?? []) as $questionData) {
                $answers = array_values($this->sortAssocByNumericKey($questionData['answers'] ?? []));
                $firstCorrectAnswer = Arr::first($answers, fn(array $answer): bool => (bool) ($answer['is_correct'] ?? false));

                $group->questions()->create([
                    'exam_section_id' => null,
                    'content' => $questionData['content'] ?? '',
                    'answer_content' => $firstCorrectAnswer['content'] ?? ($answers[0]['content'] ?? null),
                    'point' => $questionData['point'] ?? 1,
                    'feedback' => $questionData['feedback'] ?? null,
                    'metadata' => [
                        'question_type' => $questionData['question_type'] ?? $group->question_type,
                        'answer_label' => null,
                        'answers' => array_map(fn(array $answer): array => [
                            'content' => $answer['content'] ?? '',
                            'feedback' => $answer['feedback'] ?? '',
                            'is_correct' => (bool) ($answer['is_correct'] ?? false),
                        ], $answers),
                    ],
                    'is_active' => true,
                ]);
            }
        }
    }

    private function createDirectQuestions(ExamSection $section, array $questions): void
    {
        foreach ($this->sortAssocByNumericKey($questions) as $questionData) {
            $section->questions()->create([
                'exam_question_group_id' => null,
                'content' => $questionData['content'] ?? '',
                'answer_content' => $questionData['answer_content'] ?? null,
                'point' => $questionData['point'] ?? 1,
                'feedback' => $questionData['feedback'] ?? null,
                'hint' => $questionData['hint'] ?? null,
                'metadata' => [],
                'is_active' => true,
            ]);
        }
    }

    private function deleteExistingContent(ExamSkill $skill): void
    {
        $sections = $skill->sections()
            ->with(['questionGroups.questions', 'questions', 'filters'])
            ->get();

        foreach ($sections as $section) {
            if ($section->audio_file && Storage::disk('public')->exists($section->audio_file)) {
                Storage::disk('public')->delete($section->audio_file);
            }

            $section->filters()->sync([]);

            foreach ($section->questionGroups as $group) {
                $group->questions->each(fn(ExamQuestion $question) => $question->delete());
                $group->delete();
            }

            $section->questions()
                ->whereNull('exam_question_group_id')
                ->get()
                ->each(fn(ExamQuestion $question) => $question->delete());

            $section->delete();
        }
    }

    private function sectionFilterMap(ExamSkill $skill): array
    {
        $examType = $skill->examTest?->exam?->type;

        $query = ExamFilter::query()
            ->where('type', 'value')
            ->whereNotNull('slug')
            ->whereHas('parent.parent', function ($query) use ($skill): void {
                $query->whereRaw('LOWER(name) = ?', [strtolower($skill->skill_type)]);
            });

        if ($examType) {
            $query->whereHas('parent.parent', function ($query) use ($examType): void {
                $query->where('exam_type', $examType);
            });
        }

        return $query->pluck('id', 'slug')
            ->mapWithKeys(fn(int $id, string $slug): array => [$this->normalizeFilterSlug($slug) => $id])
            ->toArray();
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach (self::HEADINGS as $heading) {
            $value = $row[$heading] ?? '';
            $normalized[$heading] = is_string($value) ? trim($value) : $value;
            $normalized[$heading] = $normalized[$heading] === null ? '' : $normalized[$heading];
        }

        return $normalized;
    }

    private function isBlankRow(array $row): bool
    {
        foreach (self::HEADINGS as $heading) {
            if (($row[$heading] ?? '') !== '') {
                return false;
            }
        }

        return true;
    }

    private function &section(array &$sections, int $sectionNo): array
    {
        if (!isset($sections[$sectionNo])) {
            $sections[$sectionNo] = [
                'section_no' => $sectionNo,
                'title' => '',
                'content' => '',
                'feedback' => '',
                'filter_ids' => [],
                'groups' => [],
                'direct_questions' => [],
            ];
        }

        return $sections[$sectionNo];
    }

    private function &group(array &$groups, int $groupNo): array
    {
        if (!isset($groups[$groupNo])) {
            $groups[$groupNo] = [
                'group_no' => $groupNo,
                'content' => '',
                'instructions' => '',
                'question_type' => 'multiple_choice',
                'number_of_options' => null,
                'questions' => [],
            ];
        }

        return $groups[$groupNo];
    }

    private function &question(array &$questions, int $questionNo): array
    {
        if (!isset($questions[$questionNo])) {
            $questions[$questionNo] = [
                'question_no' => $questionNo,
                'content' => '',
                'question_type' => 'multiple_choice',
                'point' => 1,
                'feedback' => null,
                'answers' => [],
            ];
        }

        return $questions[$questionNo];
    }

    private function &directQuestion(array &$questions, int $questionNo): array
    {
        if (!isset($questions[$questionNo])) {
            $questions[$questionNo] = [
                'direct_question_no' => $questionNo,
                'content' => '',
                'answer_content' => '',
                'feedback' => '',
                'hint' => '',
                'point' => 1,
            ];
        }

        return $questions[$questionNo];
    }

    private function fillFirstValue(array &$data, string $key, mixed $value): void
    {
        if (($data[$key] ?? '') === '' && $value !== '') {
            $data[$key] = $value;
        }
    }

    private function positiveInt(mixed $value): ?int
    {
        if ($value === '' || $value === null || !is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private function parseBoolean(mixed $value): ?bool
    {
        if ($value === '' || $value === null) {
            return false;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'y', 'correct', 'dung', 'đúng' => true,
            '0', 'false', 'no', 'n', 'incorrect', 'sai' => false,
            default => null,
        };
    }

    private function parseList(mixed $value): array
    {
        if ($value === '' || $value === null) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/[,;|\r\n]+/', (string) $value) ?: []),
            fn(string $item): bool => $item !== ''
        ));
    }

    private function normalizeQuestionType(mixed $value): string
    {
        return str_replace('-', '_', Str::slug((string) $value, '_'));
    }

    private function normalizeFilterSlug(string $slug): string
    {
        return Str::slug($slug);
    }

    private function isAllowedQuestionType(string $questionType): bool
    {
        return in_array($questionType, self::QUESTION_TYPES, true);
    }

    private function textPreview(mixed $value, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?: '');

        return Str::limit($text, $limit);
    }

    private function htmlPreview(mixed $value): string
    {
        $html = trim((string) $value);

        if ($html === '') {
            return '';
        }

        if ($html === strip_tags($html)) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        return $this->purifier()->purify($html);
    }

    private function editorHtml(mixed $value): string
    {
        $content = trim((string) $value);

        if ($content === '') {
            return '';
        }

        if ($content !== strip_tags($content)) {
            return $this->purifier()->purify($content);
        }

        $paragraphs = preg_split("/\R{2,}/", str_replace(["\r\n", "\r"], "\n", $content)) ?: [];
        $paragraphs = array_map(function (string $paragraph): string {
            $lines = array_map(
                fn(string $line): string => htmlspecialchars(trim($line), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                explode("\n", trim($paragraph))
            );

            return '<p>' . implode('<br>', array_filter($lines, fn(string $line): bool => $line !== '')) . '</p>';
        }, $paragraphs);

        return implode('', array_filter($paragraphs, fn(string $paragraph): bool => $paragraph !== '<p></p>'));
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->htmlPurifier) {
            return $this->htmlPurifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', implode(',', [
            'p[style]',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'span[style]',
            'ul',
            'ol',
            'li',
            'a[href|target|rel]',
            'img[src|alt|width|height|style]',
            'blockquote',
            'h1[style]',
            'h2[style]',
            'h3[style]',
            'h4[style]',
            'h5[style]',
            'h6[style]',
            'table',
            'thead',
            'tbody',
            'tr',
            'th[colspan|rowspan|style]',
            'td[colspan|rowspan|style]',
        ]));
        $config->set('CSS.AllowedProperties', [
            'text-align',
            'color',
            'background-color',
            'width',
            'height',
        ]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        return $this->htmlPurifier = new HTMLPurifier($config);
    }

    private function sortAssocByNumericKey(array $items): array
    {
        ksort($items, SORT_NUMERIC);

        return array_values($items);
    }

    private function summary(array $payload, int $rowCount): array
    {
        $summary = [
            'rows' => $rowCount,
            'sections' => count($payload['sections']),
            'groups' => 0,
            'questions' => 0,
            'direct_questions' => 0,
            'answers' => 0,
        ];

        foreach ($payload['sections'] as $section) {
            $summary['direct_questions'] += count($section['direct_questions'] ?? []);

            foreach ($section['groups'] ?? [] as $group) {
                $summary['groups']++;
                $summary['questions'] += count($group['questions'] ?? []);

                foreach ($group['questions'] ?? [] as $question) {
                    $summary['answers'] += count($question['answers'] ?? []);
                }
            }
        }

        return $summary;
    }

    private function error(?int $row, string $message): array
    {
        return [
            'row' => $row,
            'message' => $message,
        ];
    }
}
