<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSkill;
use App\Models\ExamTest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamSkillController extends Controller
{
    /**
     * Display a listing of skills.
     * GET /api/skills
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExamSkill::with(['examTest.exam']);

        // Filter by exam type through relationship
        if ($request->has('exam_type')) {
            $query->whereHas('examTest.exam', function($q) use ($request) {
                $q->where('type', $request->exam_type);
            });
        }

        // Filter by skill type
        if ($request->has('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Only active skills by default
        if (!$request->has('is_active')) {
            $query->where('is_active', true);
        }

        $skills = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Display a specific skill.
     * GET /api/skills/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = ExamSkill::query();

        if ($request->boolean('with_sections')) {
            $query->with([
                'sections.questionGroups.questions', // For Reading/Listening
                'sections.questions' // For Writing/Speaking
            ]);
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
}
