<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     * GET /api/exams
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exam::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Include tests if requested
        if ($request->boolean('with_tests')) {
            $query->with('activeTests');
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $exams,
        ]);
    }

    /**
     * Display the specified exam.
     * GET /api/exams/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Exam::query();

        // Include relationships if requested
        if ($request->boolean('with_tests')) {
            $query->with('tests.skills.sections.questionGroups.questions');
        }

        $exam = $query->find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $exam,
        ]);
    }
}
