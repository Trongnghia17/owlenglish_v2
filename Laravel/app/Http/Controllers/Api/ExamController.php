<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSkill;
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

    public function getExamDetails(int $examId)
    {
        $exam = Exam::with(['examTests' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('id', 'asc')
                ->with(['examSkills' => function ($skillQuery) {
                    $skillQuery->where('is_active', true)
                        ->orderBy('skill_type', 'asc');
                }]);
        }])
            ->where('is_active', true)
            ->find($examId);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Bộ đề không tồn tại hoặc không hoạt động.'
            ], 404);
        }

        $testsCount = $exam->examTests->count();

        $response = [
            'success' => true,
            'data' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'total_tests' => $testsCount,
                'exam_type' => $exam->type,
                'tests' => $exam->examTests->map(function ($test) {
                    return [
                        'id' => $test->id,
                        'name' => $test->name,
                        'description' => $test->description,
                        'total_skills' => $test->examSkills->count(),
                        'skills' => $test->examSkills->map(function ($skill) {
                            return [
                                'id' => $skill->id,
                                'name' => $skill->name,
                                'skill_type' => $skill->skill_type,
                                'time_limit' => $skill->time_limit,
                            ];
                        })
                    ];
                }),
            ],
        ];
        return response()->json($response);
    }

    public function getListeningContent(Request $request, $skillId, $sectionId = null)
    {
        // 1. Lấy thông tin Skill cơ bản
        $skill = ExamSkill::select(['id', 'name', 'time_limit', 'skill_type'])->find($skillId);

        if (!$skill) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy kỹ năng.'], 404);
        }

        // 2. Thiết lập truy vấn Sections
        $sectionsQuery = $skill->sections()
            ->with(['questionGroups.questions']);
        if ($sectionId) {
            $sectionsQuery->where('id', $sectionId);
        }

        // 4. Thực thi truy vấn
        $sections = $sectionsQuery->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'time_limit' => $skill->time_limit,
                'skill_type' => $skill->skill_type,
                'sections' => $sections, // Danh sách Sections đã được lọc
            ]
        ]);
    }
}
