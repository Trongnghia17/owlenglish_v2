<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestResult;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestResultController extends Controller
{
    /**
     * Submit test result
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'skill_id' => 'nullable|exists:exam_skills,id',
            'section_id' => 'nullable|exists:exam_sections,id',
            'test_id' => 'nullable|exists:exam_tests,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:exam_questions,id',
            'answers.*.answer' => 'nullable',
            'all_question_ids' => 'nullable|array',
            'all_question_ids.*' => 'exists:exam_questions,id',
            'time_spent' => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:1',
            'answered_questions' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate score
            $correctCount = 0;
            $answersData = [];
            
            // Get all questions (cả câu đã trả lời và chưa trả lời)
            $allQuestionIds = $validated['all_question_ids'] ?? array_column($validated['answers'], 'question_id');
            $questions = ExamQuestion::whereIn('id', $allQuestionIds)
                ->with(['questionGroup.examSection', 'examSection'])
                ->get()
                ->keyBy('id');

            // Tạo map câu trả lời
            $answersMap = [];
            foreach ($validated['answers'] as $answerData) {
                $answersMap[$answerData['question_id']] = $answerData['answer'] ?? null;
            }

            // Group by section (mỗi section là 1 part)
            $questionsBySection = [];
            foreach ($allQuestionIds as $questionId) {
                $question = $questions->get($questionId);
                if ($question) {
                    // Lấy section_id từ questionGroup hoặc trực tiếp từ question
                    $sectionId = $question->questionGroup?->exam_section_id ?? $question->exam_section_id;
                    
                    if (!isset($questionsBySection[$sectionId])) {
                        $questionsBySection[$sectionId] = [
                            'section' => $question->questionGroup?->examSection ?? $question->examSection,
                            'questions' => []
                        ];
                    }
                    $questionsBySection[$sectionId]['questions'][] = [
                        'question' => $question,
                        'answer' => $answersMap[$questionId] ?? null
                    ];
                }
            }

            // Process answers with section-specific numbering
            $partIndex = 1;
            foreach ($questionsBySection as $sectionId => $sectionData) {
                $section = $sectionData['section'];
                $sectionQuestions = $sectionData['questions'];
                
                // Tạo tên Part rõ ràng
                $partName = 'Part ' . $partIndex;
                if ($section && !empty($section->title)) {
                    $partName = $section->title;
                }
                
                foreach ($sectionQuestions as $index => $data) {
                    $question = $data['question'];
                    $userAnswer = $data['answer'];
                    
                    // Chỉ check đúng/sai nếu có trả lời
                    $isCorrect = false;
                    if ($userAnswer !== null && trim($userAnswer) !== '') {
                        $isCorrect = $this->checkAnswer($question, $userAnswer);
                        if ($isCorrect) {
                            $correctCount++;
                        }
                    }

                    // Get correct answer from metadata
                    $correctAnswer = $this->getCorrectAnswerText($question);

                    $answersData[] = [
                        'question_id' => $question->id,
                        'question_number' => $index + 1, // Số thứ tự trong section
                        'user_answer' => $userAnswer,
                        'correct_answer' => $correctAnswer,
                        'is_correct' => $isCorrect,
                        'part' => $partName,
                        'section_id' => $sectionId,
                    ];
                }
                
                $partIndex++;
            }

            // Calculate band score (IELTS style: 0-9)
            $score = $this->calculateBandScore(
                $correctCount,
                $validated['total_questions']
            );

            // Create test result
            $result = TestResult::create([
                'user_id' => auth()->id(),
                'exam_skill_id' => $validated['skill_id'] ?? null,
                'exam_section_id' => $validated['section_id'] ?? null,
                'exam_test_id' => $validated['test_id'] ?? null,
                'total_questions' => $validated['total_questions'],
                'answered_questions' => $validated['answered_questions'],
                'correct_answers' => $correctCount,
                'score' => $score,
                'time_spent' => $validated['time_spent'],
                'status' => 'submitted',
                'answers' => $answersData,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test submitted successfully',
                'data' => [
                    'id' => $result->id,
                    'score' => $result->score,
                    'correct_answers' => $correctCount,
                    'total_questions' => $validated['total_questions'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit test: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save test draft (auto-save)
     */
    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'skill_id' => 'nullable|exists:exam_skills,id',
            'section_id' => 'nullable|exists:exam_sections,id',
            'test_id' => 'nullable|exists:exam_tests,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:exam_questions,id',
            'answers.*.answer' => 'required',
            'time_spent' => 'required|integer|min:0',
        ]);

        try {
            // Find existing draft or create new
            $draft = TestResult::where('user_id', auth()->id())
                ->where('status', 'draft')
                ->where(function ($query) use ($validated) {
                    if (isset($validated['skill_id'])) {
                        $query->where('exam_skill_id', $validated['skill_id']);
                    }
                    if (isset($validated['section_id'])) {
                        $query->where('exam_section_id', $validated['section_id']);
                    }
                })
                ->first();

            $answersData = collect($validated['answers'])->map(function ($answer) {
                return [
                    'question_id' => $answer['question_id'],
                    'user_answer' => $answer['answer'],
                ];
            })->toArray();

            if ($draft) {
                $draft->update([
                    'answers' => $answersData,
                    'time_spent' => $validated['time_spent'],
                    'answered_questions' => count($validated['answers']),
                ]);
            } else {
                $draft = TestResult::create([
                    'user_id' => auth()->id(),
                    'exam_skill_id' => $validated['skill_id'] ?? null,
                    'exam_section_id' => $validated['section_id'] ?? null,
                    'exam_test_id' => $validated['test_id'] ?? null,
                    'answers' => $answersData,
                    'time_spent' => $validated['time_spent'],
                    'answered_questions' => count($validated['answers']),
                    'status' => 'draft',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'data' => ['id' => $draft->id],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get test result by ID
     */
    public function show($id)
    {
        $result = TestResult::with(['skill', 'section', 'test', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get user's exam history
     */
    public function history(Request $request)
    {
        $skillType = $request->query('skill_type'); // listening, reading, writing, speaking
        
        $query = TestResult::with(['skill', 'section', 'test'])
            ->where('user_id', auth()->id())
            ->where('status', 'submitted')
            ->orderBy('created_at', 'desc');

        // Filter by skill type if provided
        if ($skillType) {
            $query->whereHas('skill', function ($q) use ($skillType) {
                $q->where('skill_type', $skillType);
            });
        }

        $results = $query->get()->map(function ($result) {
            return [
                'id' => $result->id,
                'title' => $result->test->name ?? $result->skill->name ?? $result->section->title ?? 'Test',
                'section' => ucfirst($result->skill->skill_type ?? 'Reading'),
                'duration' => $this->formatDuration($result->time_spent),
                'date' => $result->created_at->format('Y-m-d'),
                'correct' => $result->correct_answers,
                'wrong' => $result->total_questions - $result->correct_answers - ($result->total_questions - $result->answered_questions),
                'skipped' => $result->total_questions - $result->answered_questions,
                'score' => $result->score,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Format duration in seconds to HH:MM:SS
     */
    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Check if answer is correct
     */
    private function checkAnswer(ExamQuestion $question, $userAnswer): bool
    {
        // Get correct answer from metadata
        $metadata = is_string($question->metadata) 
            ? json_decode($question->metadata, true) 
            : $question->metadata;
        
        if (!$metadata || !isset($metadata['answers'])) {
            return false;
        }

        // Find correct answer(s)
        $correctAnswers = collect($metadata['answers'])
            ->filter(fn($ans) => ($ans['is_correct'] ?? 0) == 1 || ($ans['is_correct'] ?? false) === true)
            ->map(function($ans) {
                // Strip HTML tags from answer
                $content = $ans['content'] ?? '';
                $content = strip_tags($content);
                return trim($content);
            })
            ->filter()
            ->toArray();

        if (empty($correctAnswers)) {
            return false;
        }

        // Normalize user answer
        $userAnswer = trim(strip_tags($userAnswer));

        // Check if user answer matches any correct answer (case-insensitive)
        foreach ($correctAnswers as $correctAnswer) {
            if (strcasecmp($correctAnswer, $userAnswer) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get correct answer text from question metadata
     */
    private function getCorrectAnswerText(ExamQuestion $question): string
    {
        $metadata = is_string($question->metadata) 
            ? json_decode($question->metadata, true) 
            : $question->metadata;
        
        if (!$metadata || !isset($metadata['answers'])) {
            return '';
        }

        // Find first correct answer
        $correctAnswer = collect($metadata['answers'])
            ->first(fn($ans) => ($ans['is_correct'] ?? 0) == 1 || ($ans['is_correct'] ?? false) === true);

        if (!$correctAnswer) {
            return '';
        }

        $content = $correctAnswer['content'] ?? '';
        return trim(strip_tags($content));
    }

    /**
     * Calculate IELTS band score based on correct answers
     */
    private function calculateBandScore(int $correct, int $total): float
    {
        if ($total === 0) return 0;

        $percentage = ($correct / $total) * 100;

        // IELTS Reading band score conversion (approximate)
        if ($percentage >= 90) return 9.0;
        if ($percentage >= 85) return 8.5;
        if ($percentage >= 80) return 8.0;
        if ($percentage >= 75) return 7.5;
        if ($percentage >= 70) return 7.0;
        if ($percentage >= 65) return 6.5;
        if ($percentage >= 60) return 6.0;
        if ($percentage >= 55) return 5.5;
        if ($percentage >= 50) return 5.0;
        if ($percentage >= 45) return 4.5;
        if ($percentage >= 40) return 4.0;
        if ($percentage >= 35) return 3.5;
        if ($percentage >= 30) return 3.0;
        
        return 2.5;
    }
}
