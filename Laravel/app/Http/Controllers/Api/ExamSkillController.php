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
}
