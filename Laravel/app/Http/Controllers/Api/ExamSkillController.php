<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSkill;
use App\Models\ExamTest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExamSkillController extends Controller
{
    /**
     * Display skills of a specific test.
     * GET /api/tests/{testId}/skills
     */
    public function index(Request $request, $testId): JsonResponse
    {
        $test = ExamTest::find($testId);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        $query = ExamSkill::where('exam_test_id', $testId);

        // Filter by skill type
        if ($request->has('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        // Include sections if requested
        if ($request->boolean('with_sections')) {
            $query->with('sections.questionGroups.questions');
        }

        $skills = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Store a new skill for a test.
     * POST /api/tests/{testId}/skills
     */
    public function store(Request $request, $testId): JsonResponse
    {
        $test = ExamTest::find($testId);

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Test not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'skill_type' => 'required|in:reading,writing,speaking,listening',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
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
        $data['exam_test_id'] = $testId;

        $skill = ExamSkill::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Skill created successfully',
            'data' => $skill,
        ], 201);
    }

    /**
     * Display a specific skill.
     * GET /api/skills/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = ExamSkill::query();

        if ($request->boolean('with_sections')) {
            $query->with('sections.questionGroups.questions');
        }

        $skill = $query->find($id);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $skill,
        ]);
    }

    /**
     * Update a skill.
     * PUT/PATCH /api/skills/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $skill = ExamSkill::find($id);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'skill_type' => 'sometimes|required|in:reading,writing,speaking,listening',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $skill->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Skill updated successfully',
            'data' => $skill,
        ]);
    }

    /**
     * Delete a skill.
     * DELETE /api/skills/{id}
     */
    public function destroy($id): JsonResponse
    {
        $skill = ExamSkill::find($id);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found',
            ], 404);
        }

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skill deleted successfully',
        ]);
    }

    /**
     * Reorder skills.
     * POST /api/tests/{testId}/skills/reorder
     */
    public function reorder(Request $request, $testId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'skills' => 'required|array',
            'skills.*.id' => 'required|exists:exam_skills,id',
            'skills.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->skills as $skillData) {
            ExamSkill::where('id', $skillData['id'])
                ->where('exam_test_id', $testId)
                ->update(['order' => $skillData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Skills reordered successfully',
        ]);
    }
}
