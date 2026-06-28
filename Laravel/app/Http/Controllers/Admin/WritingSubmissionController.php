<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class WritingSubmissionController extends Controller
{
    private const CRITERIA = [
        'task_response' => 'TA/TR',
        'coherence_cohesion' => 'CC',
        'lexical_resource' => 'LR',
        'grammatical_range' => 'GRA',
    ];

    private const CRITERIA_TITLES = [
        'task_response' => 'Task Achievement / Task Response',
        'coherence_cohesion' => 'Coherence and Cohesion',
        'lexical_resource' => 'Lexical Resource',
        'grammatical_range' => 'Grammatical Range and Accuracy',
    ];

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = trim((string) $request->input('search', ''));

        $query = TestResult::query()
            ->with(['user', 'skill', 'section.examSkill', 'test', 'grader'])
            ->where('status', 'submitted')
            ->where(function ($query) {
                $query->whereHas('skill', fn ($skill) => $skill->where('skill_type', 'writing'))
                    ->orWhereHas('section.examSkill', fn ($skill) => $skill->where('skill_type', 'writing'));
            });

        if ($status === 'pending') {
            $query->whereNull('writing_feedback');
        } elseif ($status === 'graded') {
            $query->whereNotNull('writing_feedback');
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $query->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                    ->orWhereHas('skill', fn ($skill) => $skill->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('section', fn ($section) => $section->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('test', fn ($test) => $test->where('name', 'like', "%{$search}%"));
            });
        }

        $submissions = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.writing-submissions.index', compact('submissions', 'status', 'search'));
    }

    public function edit(TestResult $writingSubmission)
    {
        $this->ensureWritingSubmission($writingSubmission);

        $writingSubmission->load(['user', 'skill', 'section.examSkill', 'test', 'grader']);

        return view('admin.writing-submissions.edit', [
            'submission' => $writingSubmission,
            'criteria' => self::CRITERIA,
            'criteriaTitles' => self::CRITERIA_TITLES,
            'feedback' => $this->normalizeFeedback($writingSubmission->writing_feedback),
            'tasks' => $this->buildTaskSubmissions(
                $writingSubmission->answers ?? [],
                $writingSubmission->writing_feedback
            ),
        ]);
    }

    public function update(Request $request, TestResult $writingSubmission)
    {
        $this->ensureWritingSubmission($writingSubmission);

        $validated = $request->validate([
            'teacher_note' => 'nullable|string|max:5000',
            'tasks' => 'required|array|min:1',
            'tasks.*.key' => 'required|string|max:120',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.task_number' => 'nullable|integer|min:1|max:2',
            'tasks.*.section_id' => 'nullable|integer',
            'tasks.*.question_id' => 'nullable|integer',
            'tasks.*.prompt' => 'nullable|string',
            'tasks.*.answer' => 'nullable|string',
            'tasks.*.scores.task_response' => 'required|numeric|min:0|max:9',
            'tasks.*.scores.coherence_cohesion' => 'required|numeric|min:0|max:9',
            'tasks.*.scores.lexical_resource' => 'required|numeric|min:0|max:9',
            'tasks.*.scores.grammatical_range' => 'required|numeric|min:0|max:9',
            'tasks.*.teacher_note' => 'nullable|string|max:5000',
            'tasks.*.criteria' => 'nullable|array',
            'tasks.*.criteria.*.strengths' => 'nullable|string|max:5000',
            'tasks.*.criteria.*.weaknesses' => 'nullable|string|max:5000',
            'tasks.*.details' => 'nullable|array',
            'tasks.*.details.*.original' => 'nullable|string|max:2000',
            'tasks.*.details.*.explanation' => 'nullable|string|max:5000',
            'tasks.*.details.*.correction' => 'nullable|string|max:5000',
        ]);

        $this->validateHalfBandScores($validated['tasks']);

        $tasksFeedback = collect($validated['tasks'])
            ->map(fn ($task, $index) => $this->buildTaskFeedback($task, $index))
            ->values()
            ->all();

        $rawOverallScore = $this->calculateWritingOverallRaw($tasksFeedback);
        $overallScore = $rawOverallScore === null ? null : $this->roundIeltsBand($rawOverallScore);

        $writingSubmission->update([
            'score' => $overallScore ?? 0,
            'writing_feedback' => [
                'version' => 2,
                'scores' => [
                    'overall' => $overallScore,
                ],
                'overall_score' => $overallScore,
                'raw_overall_score' => $rawOverallScore === null ? null : round($rawOverallScore, 4),
                'teacher_note' => trim((string) ($validated['teacher_note'] ?? '')),
                'tasks' => $tasksFeedback,
                'graded_at' => Carbon::now()->toISOString(),
            ],
            'graded_by' => auth()->id(),
            'graded_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('admin.writing-submissions.edit', $writingSubmission)
            ->with('success', 'Đã lưu kết quả chấm Writing theo từng task.');
    }

    private function ensureWritingSubmission(TestResult $submission): void
    {
        $submission->loadMissing(['skill', 'section.examSkill']);

        $skillType = $submission->skill?->skill_type ?? $submission->section?->examSkill?->skill_type;

        abort_unless($skillType === 'writing', 404);
    }

    private function normalizeFeedback(?array $feedback): array
    {
        $feedback = $feedback ?? [];

        return [
            'overall_score' => $feedback['overall_score'] ?? ($feedback['scores']['overall'] ?? null),
            'raw_overall_score' => $feedback['raw_overall_score'] ?? null,
            'teacher_note' => (string) ($feedback['teacher_note'] ?? ''),
        ];
    }

    private function buildTaskSubmissions(array $answers, ?array $feedback): array
    {
        $savedTasks = collect($feedback['tasks'] ?? [])->keyBy('key');
        $entries = $this->extractTaskEntries($answers);

        if ($entries->isEmpty() && $savedTasks->isNotEmpty()) {
            $entries = $savedTasks->values()->map(fn ($task, $index) => [
                'key' => $task['key'] ?? 'task:' . ($index + 1),
                'title' => $task['title'] ?? 'Writing Task ' . ($index + 1),
                'task_number' => (int) ($task['task_number'] ?? ($index + 1)),
                'section_id' => $task['section_id'] ?? null,
                'question_id' => $task['question_id'] ?? null,
                'prompt' => $task['prompt'] ?? '',
                'answer' => $task['answer'] ?? '',
                'word_count' => $this->countWords($task['answer'] ?? ''),
            ]);
        }

        if ($entries->count() === 1 && $savedTasks->isEmpty() && !empty($feedback) && isset($feedback['criteria'])) {
            $savedTasks = collect([
                $entries->first()['key'] => $this->convertLegacyFeedbackToTask($feedback),
            ]);
        }

        return $entries
            ->values()
            ->map(function ($entry, $index) use ($savedTasks) {
                $saved = $savedTasks->get($entry['key']) ?? [];
                $scores = $this->normalizeCriterionScores($saved['scores'] ?? []);

                return [
                    ...$entry,
                    'prompt' => $saved['prompt'] ?? ($entry['prompt'] ?? ''),
                    'task_number' => $entry['task_number'] ?: $index + 1,
                    'scores' => $scores,
                    'raw_task_score' => $saved['raw_task_score'] ?? $this->calculateTaskRawScore($scores),
                    'rounded_task_score' => $saved['rounded_task_score'] ?? null,
                    'teacher_note' => (string) ($saved['teacher_note'] ?? ''),
                    'criteria' => $this->normalizeCriteriaFeedback($saved['criteria'] ?? []),
                    'details' => $this->normalizeDetails($saved['details'] ?? []),
                ];
            })
            ->all();
    }

    private function extractTaskEntries(array $answers): \Illuminate\Support\Collection
    {
        $entries = collect();
        $seen = [];
        $questionPrompts = $this->getQuestionPrompts($answers);

        foreach ($answers as $answer) {
            $text = trim(strip_tags((string) ($answer['user_answer'] ?? '')));

            if ($text === '') {
                continue;
            }

            $sectionId = $answer['section_id'] ?? null;
            $questionId = $answer['question_id'] ?? null;
            $key = $sectionId ? "section:{$sectionId}" : "question:{$questionId}";

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $taskNumber = $this->inferTaskNumber($answer['part'] ?? '', $entries->count() + 1);

            $entries->push([
                'key' => $key,
                'title' => $this->formatTaskTitle($answer['part'] ?? '', $taskNumber),
                'task_number' => $taskNumber,
                'section_id' => $sectionId,
                'question_id' => $questionId,
                'prompt' => $questionPrompts[$questionId] ?? '',
                'answer' => $text,
                'word_count' => $this->countWords($text),
            ]);
        }

        return $entries;
    }

    private function buildTaskFeedback(array $task, int $index): array
    {
        $scores = $this->normalizeCriterionScores($task['scores'] ?? []);
        $rawTaskScore = $this->calculateTaskRawScore($scores);
        $roundedTaskScore = $rawTaskScore === null ? null : $this->roundIeltsBand($rawTaskScore);

        return [
            'key' => $task['key'],
            'title' => $task['title'],
            'task_number' => (int) ($task['task_number'] ?? ($index + 1)),
            'section_id' => $task['section_id'] ?? null,
            'question_id' => $task['question_id'] ?? null,
            'prompt' => trim((string) ($task['prompt'] ?? '')),
            'answer' => trim((string) ($task['answer'] ?? '')),
            'scores' => [
                ...$scores,
                'raw_task_score' => $rawTaskScore === null ? null : round($rawTaskScore, 4),
                'task_score' => $rawTaskScore === null ? null : round($rawTaskScore, 2),
                'rounded_task_score' => $roundedTaskScore,
            ],
            'raw_task_score' => $rawTaskScore === null ? null : round($rawTaskScore, 4),
            'rounded_task_score' => $roundedTaskScore,
            'teacher_note' => trim((string) ($task['teacher_note'] ?? '')),
            'criteria' => $this->buildCriteriaFeedback($task['criteria'] ?? []),
            'details' => $this->buildDetailsFeedback($task['details'] ?? []),
        ];
    }

    private function normalizeCriterionScores(array $scores): array
    {
        $normalized = [];

        foreach (array_keys(self::CRITERIA) as $key) {
            if (!isset($scores[$key]) || $scores[$key] === '') {
                $normalized[$key] = null;
                continue;
            }

            $normalized[$key] = (float) $scores[$key];
        }

        return $normalized;
    }

    private function buildCriteriaFeedback(array $criteria): array
    {
        $normalized = [];

        foreach (self::CRITERIA_TITLES as $key => $title) {
            $saved = collect($criteria)->firstWhere('key', $key);
            $input = $criteria[$key] ?? $saved ?? [];

            $normalized[] = [
                'key' => $key,
                'title' => $title,
                'strengths' => trim((string) ($input['strengths'] ?? '')),
                'weaknesses' => trim((string) ($input['weaknesses'] ?? '')),
            ];
        }

        return $normalized;
    }

    private function normalizeCriteriaFeedback(array $criteria): array
    {
        return collect($this->buildCriteriaFeedback($criteria))
            ->mapWithKeys(fn ($item) => [$item['key'] => $item])
            ->all();
    }

    private function buildDetailsFeedback(array $details): array
    {
        return collect($details)
            ->map(fn ($detail) => [
                'original' => trim((string) ($detail['original'] ?? '')),
                'explanation' => trim((string) ($detail['explanation'] ?? '')),
                'correction' => trim((string) ($detail['correction'] ?? '')),
            ])
            ->filter(fn ($detail) => $detail['original'] !== '' || $detail['explanation'] !== '' || $detail['correction'] !== '')
            ->values()
            ->all();
    }

    private function normalizeDetails(array $details): array
    {
        $normalized = $this->buildDetailsFeedback($details);

        return empty($normalized)
            ? [
                ['original' => '', 'explanation' => '', 'correction' => ''],
                ['original' => '', 'explanation' => '', 'correction' => ''],
            ]
            : $normalized;
    }

    private function validateHalfBandScores(array $tasks): void
    {
        $errors = [];

        foreach ($tasks as $taskIndex => $task) {
            foreach (array_keys(self::CRITERIA) as $scoreKey) {
                $score = (float) ($task['scores'][$scoreKey] ?? -1);
                $scaled = $score * 2;

                if (abs($scaled - round($scaled)) > 0.00001) {
                    $errors["tasks.{$taskIndex}.scores.{$scoreKey}"] =
                        'Điểm IELTS Writing chỉ được nhập số nguyên hoặc .5 (ví dụ 6.0, 6.5, 7.0).';
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function calculateTaskRawScore(array $scores): ?float
    {
        $criterionScores = collect(array_keys(self::CRITERIA))
            ->map(fn ($key) => $scores[$key] ?? null)
            ->filter(fn ($score) => is_numeric($score));

        if ($criterionScores->count() !== count(self::CRITERIA)) {
            return null;
        }

        return (float) $criterionScores->average();
    }

    private function calculateWritingOverallRaw(array $tasks): ?float
    {
        $taskScores = collect($tasks)
            ->filter(fn ($task) => is_numeric($task['raw_task_score'] ?? null))
            ->values();

        if ($taskScores->isEmpty()) {
            return null;
        }

        if ($taskScores->count() === 1) {
            return (float) $taskScores->first()['raw_task_score'];
        }

        $taskOne = $taskScores->firstWhere('task_number', 1) ?? $taskScores->get(0);
        $taskTwo = $taskScores->firstWhere('task_number', 2) ?? $taskScores->get(1);

        return ((float) $taskOne['raw_task_score'] + ((float) $taskTwo['raw_task_score'] * 2)) / 3;
    }

    private function roundIeltsBand(float $score): float
    {
        $score = max(0, min(9, $score));
        $base = floor($score);
        $fraction = $score - $base;

        if ($fraction < 0.25) {
            return (float) $base;
        }

        if ($fraction < 0.75) {
            return min(9.0, $base + 0.5);
        }

        return min(9.0, $base + 1.0);
    }

    private function convertLegacyFeedbackToTask(array $feedback): array
    {
        return [
            'scores' => $this->normalizeCriterionScores($feedback['scores'] ?? []),
            'teacher_note' => (string) ($feedback['teacher_note'] ?? ''),
            'criteria' => $feedback['criteria'] ?? [],
            'details' => $feedback['details'] ?? [],
        ];
    }

    private function getQuestionPrompts(array $answers): array
    {
        $questionIds = collect($answers)
            ->pluck('question_id')
            ->filter()
            ->unique()
            ->values();

        if ($questionIds->isEmpty()) {
            return [];
        }

        return ExamQuestion::query()
            ->whereIn('id', $questionIds)
            ->get(['id', 'content', 'feedback', 'hint'])
            ->mapWithKeys(function (ExamQuestion $question) {
                $prompt = $this->cleanPromptContent(
                    $question->content ?: $question->feedback ?: $question->hint ?: ''
                );

                return [$question->id => $prompt];
            })
            ->all();
    }

    private function cleanPromptContent(string $content): string
    {
        return trim(preg_replace("/(\r\n|\r|\n){3,}/", "\n\n", $content) ?? '');
    }

    private function inferTaskNumber(string $label, int $fallback): int
    {
        if (preg_match('/task\s*2|part\s*2|essay|250\s+words/i', $label)) {
            return 2;
        }

        if (preg_match('/task\s*1|part\s*1|chart|map|process|150\s+words/i', $label)) {
            return 1;
        }

        return $fallback >= 2 ? 2 : 1;
    }

    private function formatTaskTitle(string $label, int $taskNumber): string
    {
        $label = trim($label);

        if ($label !== '' && preg_match('/task\s*[12]/i', $label)) {
            return $label;
        }

        return 'Writing Task ' . $taskNumber;
    }

    private function countWords(string $text): int
    {
        $words = preg_split('/\s+/', trim($text));

        return $words === false ? 0 : count(array_filter($words));
    }
}
