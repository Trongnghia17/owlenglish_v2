<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamTest;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamTestController extends Controller
{
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
}
