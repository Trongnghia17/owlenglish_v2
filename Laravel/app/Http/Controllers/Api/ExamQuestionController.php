<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestionGroup;
use App\Models\ExamQuestion;
use App\Models\ExamSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamQuestionController extends Controller
{
    // ==================== QUESTION GROUPS ====================

    /**
     * Display a specific question group.
     * GET /api/question-groups/{id}
     */
    public function showGroup(Request $request, $id): JsonResponse
    {
        $query = ExamQuestionGroup::query();

        if ($request->boolean('with_questions')) {
            $query->with('questions');
        }

        $group = $query->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $group,
        ]);
    }

    // ==================== QUESTIONS ====================

    /**
     * Display questions of a question group.
     * GET /api/question-groups/{groupId}/questions
     */
    public function indexQuestions($groupId): JsonResponse
    {
        $group = ExamQuestionGroup::find($groupId);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        $questions = ExamQuestion::where('exam_question_group_id', $groupId)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    /**
     * Display a specific question.
     * GET /api/questions/{id}
     */
    public function showQuestion($id): JsonResponse
    {
        $question = ExamQuestion::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $question,
        ]);
    }
}
