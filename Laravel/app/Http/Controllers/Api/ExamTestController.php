<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamTest;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExamTestController extends Controller
{
    /**
     * Display tests of a specific exam.
     * GET /api/exams/{examId}/tests
     */
    public function index(Request $request, $examId): JsonResponse
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $query = ExamTest::where('exam_id', $examId);

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Include skills if requested
        if ($request->boolean('with_skills')) {
            $query->with('skills');
        }

        $tests = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $tests,
        ]);
    }

    /**
     * Store a new test for an exam.
     * POST /api/exams/{examId}/tests
     */
    public function store(Request $request, $examId): JsonResponse
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
        $data['exam_id'] = $examId;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('exam-tests', 'public');
            $data['image'] = $path;
        }

        $test = ExamTest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Test created successfully',
            'data' => $test,
        ], 201);
    }

    /**
     * Display a specific test.
     * GET /api/tests/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = ExamTest::query();

        // Include full structure if requested
        if ($request->boolean('with_full_structure')) {
            $query->with('skills.sections.questionGroups.questions');
        } elseif ($request->boolean('with_skills')) {
            $query->with('skills');
        }

        $test = $query->find($id);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $test,
        ]);
    }

    /**
     * Update a test.
     * PUT/PATCH /api/tests/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $test = ExamTest::find($id);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            if ($test->image && Storage::disk('public')->exists($test->image)) {
                Storage::disk('public')->delete($test->image);
            }
            $path = $request->file('image')->store('exam-tests', 'public');
            $data['image'] = $path;
        }

        $test->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Test updated successfully',
            'data' => $test,
        ]);
    }

    /**
     * Delete a test.
     * DELETE /api/tests/{id}
     */
    public function destroy($id): JsonResponse
    {
        $test = ExamTest::find($id);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        $test->delete();

        return response()->json([
            'success' => true,
            'message' => 'Test deleted successfully',
        ]);
    }

    /**
     * Reorder tests.
     * POST /api/exams/{examId}/tests/reorder
     */
    public function reorder(Request $request, $examId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tests' => 'required|array',
            'tests.*.id' => 'required|exists:exam_tests,id',
            'tests.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->tests as $testData) {
            ExamTest::where('id', $testData['id'])
                ->where('exam_id', $examId)
                ->update(['order' => $testData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tests reordered successfully',
        ]);
    }

    /**
     * Duplicate a test.
     * POST /api/tests/{id}/duplicate
     */
    public function duplicate($id): JsonResponse
    {
        $test = ExamTest::with('skills.sections.questionGroups.questions')->find($id);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        // Clone the test
        $newTest = $test->replicate();
        $newTest->name = $test->name . ' (Copy)';
        $newTest->order = ExamTest::where('exam_id', $test->exam_id)->max('order') + 1;
        $newTest->save();

        // Clone skills and nested structures
        foreach ($test->skills as $skill) {
            $newSkill = $skill->replicate();
            $newSkill->exam_test_id = $newTest->id;
            $newSkill->save();

            foreach ($skill->sections as $section) {
                $newSection = $section->replicate();
                $newSection->exam_skill_id = $newSkill->id;
                $newSection->save();

                foreach ($section->questionGroups as $group) {
                    $newGroup = $group->replicate();
                    $newGroup->exam_section_id = $newSection->id;
                    $newGroup->save();

                    foreach ($group->questions as $question) {
                        $newQuestion = $question->replicate();
                        $newQuestion->exam_question_group_id = $newGroup->id;
                        $newQuestion->save();
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Test duplicated successfully',
            'data' => $newTest->load('skills'),
        ], 201);
    }
}
