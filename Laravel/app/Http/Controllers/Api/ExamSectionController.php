<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSection;
use App\Models\ExamSkill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamSectionController extends Controller
{
    /**
     * Display a specific section.
     * GET /api/sections/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = ExamSection::query();

        if ($request->boolean('with_questions')) {
            $query->with('questionGroups.questions');
        }

        $section = $query->find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $section,
        ]);
    }
}
