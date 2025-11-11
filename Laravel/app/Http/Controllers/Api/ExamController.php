<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
     * Store a newly created exam.
     * POST /api/exams
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:online,ielts,toeic',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            $path = $request->file('image')->store('exams', 'public');
            $data['image'] = $path;
        }

        $exam = Exam::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Exam created successfully',
            'data' => $exam,
        ], 201);
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

    /**
     * Update the specified exam.
     * PUT/PATCH /api/exams/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $exam = Exam::find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:online,ielts,toeic',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            // Delete old image if exists
            if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                Storage::disk('public')->delete($exam->image);
            }
            $path = $request->file('image')->store('exams', 'public');
            $data['image'] = $path;
        }

        $exam->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Exam updated successfully',
            'data' => $exam,
        ]);
    }

    /**
     * Remove the specified exam (soft delete).
     * DELETE /api/exams/{id}
     */
    public function destroy($id): JsonResponse
    {
        $exam = Exam::find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully',
        ]);
    }

    /**
     * Restore a soft deleted exam.
     * POST /api/exams/{id}/restore
     */
    public function restore($id): JsonResponse
    {
        $exam = Exam::withTrashed()->find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $exam->restore();

        return response()->json([
            'success' => true,
            'message' => 'Exam restored successfully',
            'data' => $exam,
        ]);
    }

    /**
     * Toggle active status.
     * POST /api/exams/{id}/toggle-active
     */
    public function toggleActive($id): JsonResponse
    {
        $exam = Exam::find($id);

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found',
            ], 404);
        }

        $exam->update(['is_active' => !$exam->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Exam status updated successfully',
            'data' => $exam,
        ]);
    }
}
