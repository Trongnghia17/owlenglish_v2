<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestionGroup;
use App\Models\ExamQuestion;
use App\Models\ExamSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExamQuestionController extends Controller
{
    // ==================== QUESTION GROUPS ====================

    /**
     * Display question groups of a section.
     * GET /api/sections/{sectionId}/question-groups
     */
    public function indexGroups(Request $request, $sectionId): JsonResponse
    {
        $section = ExamSection::find($sectionId);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $query = ExamQuestionGroup::where('exam_section_id', $sectionId);

        if ($request->boolean('with_questions')) {
            $query->with('questions');
        }

        $groups = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    /**
     * Store a new question group.
     * POST /api/sections/{sectionId}/question-groups
     */
    public function storeGroup(Request $request, $sectionId): JsonResponse
    {
        $section = ExamSection::find($sectionId);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'question_type' => 'required|in:multiple_choice,yes_no_not_given,true_false_not_given,short_text,fill_in_blank,matching,table_selection,essay,speaking',
            'answer_layout' => 'required|in:inline,side_by_side,drag_drop,standard',
            'instructions' => 'nullable|string',
            'options' => 'nullable|array',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['exam_section_id'] = $sectionId;

        $group = ExamQuestionGroup::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Question group created successfully',
            'data' => $group,
        ], 201);
    }

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

    /**
     * Update a question group.
     * PUT/PATCH /api/question-groups/{id}
     */
    public function updateGroup(Request $request, $id): JsonResponse
    {
        $group = ExamQuestionGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'question_type' => 'sometimes|required|in:multiple_choice,yes_no_not_given,true_false_not_given,short_text,fill_in_blank,matching,table_selection,essay,speaking',
            'answer_layout' => 'sometimes|required|in:inline,side_by_side,drag_drop,standard',
            'instructions' => 'nullable|string',
            'options' => 'nullable|array',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $group->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Question group updated successfully',
            'data' => $group,
        ]);
    }

    /**
     * Delete a question group.
     * DELETE /api/question-groups/{id}
     */
    public function destroyGroup($id): JsonResponse
    {
        $group = ExamQuestionGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question group deleted successfully',
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
     * Store a new question.
     * POST /api/question-groups/{groupId}/questions
     */
    public function storeQuestion(Request $request, $groupId): JsonResponse
    {
        $group = ExamQuestionGroup::find($groupId);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'answer_content' => 'nullable|string',
            'is_correct' => 'boolean',
            'point' => 'numeric|min:0',
            'feedback' => 'nullable|string',
            'hint' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'metadata' => 'nullable|array',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['exam_question_group_id'] = $groupId;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('exam-questions', 'public');
            $data['image'] = $path;
        }

        // Handle audio file upload
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('exam-questions-audio', 'public');
            $data['audio_file'] = $path;
        }

        $question = ExamQuestion::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => $question,
        ], 201);
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

    /**
     * Update a question.
     * PUT/PATCH /api/questions/{id}
     */
    public function updateQuestion(Request $request, $id): JsonResponse
    {
        $question = ExamQuestion::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|required|string',
            'answer_content' => 'nullable|string',
            'is_correct' => 'boolean',
            'point' => 'numeric|min:0',
            'feedback' => 'nullable|string',
            'hint' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'metadata' => 'nullable|array',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
            $path = $request->file('image')->store('exam-questions', 'public');
            $data['image'] = $path;
        }

        // Handle audio file upload
        if ($request->hasFile('audio_file')) {
            if ($question->audio_file && Storage::disk('public')->exists($question->audio_file)) {
                Storage::disk('public')->delete($question->audio_file);
            }
            $path = $request->file('audio_file')->store('exam-questions-audio', 'public');
            $data['audio_file'] = $path;
        }

        $question->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => $question,
        ]);
    }

    /**
     * Delete a question.
     * DELETE /api/questions/{id}
     */
    public function destroyQuestion($id): JsonResponse
    {
        $question = ExamQuestion::find($id);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        // Delete associated files
        if ($question->image && Storage::disk('public')->exists($question->image)) {
            Storage::disk('public')->delete($question->image);
        }
        if ($question->audio_file && Storage::disk('public')->exists($question->audio_file)) {
            Storage::disk('public')->delete($question->audio_file);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
        ]);
    }

    /**
     * Bulk create questions for a group.
     * POST /api/question-groups/{groupId}/questions/bulk
     */
    public function bulkStoreQuestions(Request $request, $groupId): JsonResponse
    {
        $group = ExamQuestionGroup::find($groupId);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Question group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'questions' => 'required|array|min:1',
            'questions.*.content' => 'required|string',
            'questions.*.answer_content' => 'nullable|string',
            'questions.*.is_correct' => 'boolean',
            'questions.*.point' => 'numeric|min:0',
            'questions.*.order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $createdQuestions = [];

        foreach ($request->questions as $questionData) {
            $questionData['exam_question_group_id'] = $groupId;
            $createdQuestions[] = ExamQuestion::create($questionData);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdQuestions) . ' questions created successfully',
            'data' => $createdQuestions,
        ], 201);
    }

    /**
     * Reorder questions in a group.
     * POST /api/question-groups/{groupId}/questions/reorder
     */
    public function reorderQuestions(Request $request, $groupId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:exam_questions,id',
            'questions.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->questions as $questionData) {
            ExamQuestion::where('id', $questionData['id'])
                ->where('exam_question_group_id', $groupId)
                ->update(['order' => $questionData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions reordered successfully',
        ]);
    }
}
