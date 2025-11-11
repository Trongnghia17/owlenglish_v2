<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSection;
use App\Models\ExamSkill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExamSectionController extends Controller
{
    /**
     * Display sections of a specific skill.
     * GET /api/skills/{skillId}/sections
     */
    public function index(Request $request, $skillId): JsonResponse
    {
        $skill = ExamSkill::find($skillId);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found',
            ], 404);
        }

        $query = ExamSection::where('exam_skill_id', $skillId);

        if ($request->boolean('with_questions')) {
            $query->with('questionGroups.questions');
        }

        $sections = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    /**
     * Store a new section.
     * POST /api/skills/{skillId}/sections
     */
    public function store(Request $request, $skillId): JsonResponse
    {
        $skill = ExamSkill::find($skillId);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'feedback' => 'nullable|string',
            'content_format' => 'required|in:text,audio,video,image',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
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
        $data['exam_skill_id'] = $skillId;

        // Handle audio file upload
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('exam-audio', 'public');
            $data['audio_file'] = $path;
        }

        // Handle video file upload
        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('exam-video', 'public');
            $data['video_file'] = $path;
        }

        $section = ExamSection::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section,
        ], 201);
    }

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

    /**
     * Update a section.
     * PUT/PATCH /api/sections/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $section = ExamSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'feedback' => 'nullable|string',
            'content_format' => 'sometimes|required|in:text,audio,video,image',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
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

        // Handle audio file upload
        if ($request->hasFile('audio_file')) {
            if ($section->audio_file && Storage::disk('public')->exists($section->audio_file)) {
                Storage::disk('public')->delete($section->audio_file);
            }
            $path = $request->file('audio_file')->store('exam-audio', 'public');
            $data['audio_file'] = $path;
        }

        // Handle video file upload
        if ($request->hasFile('video_file')) {
            if ($section->video_file && Storage::disk('public')->exists($section->video_file)) {
                Storage::disk('public')->delete($section->video_file);
            }
            $path = $request->file('video_file')->store('exam-video', 'public');
            $data['video_file'] = $path;
        }

        $section->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => $section,
        ]);
    }

    /**
     * Delete a section.
     * DELETE /api/sections/{id}
     */
    public function destroy($id): JsonResponse
    {
        $section = ExamSection::find($id);

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        // Delete associated files
        if ($section->audio_file && Storage::disk('public')->exists($section->audio_file)) {
            Storage::disk('public')->delete($section->audio_file);
        }
        if ($section->video_file && Storage::disk('public')->exists($section->video_file)) {
            Storage::disk('public')->delete($section->video_file);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully',
        ]);
    }

    /**
     * Reorder sections.
     * POST /api/skills/{skillId}/sections/reorder
     */
    public function reorder(Request $request, $skillId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:exam_sections,id',
            'sections.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->sections as $sectionData) {
            ExamSection::where('id', $sectionData['id'])
                ->where('exam_skill_id', $skillId)
                ->update(['order' => $sectionData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully',
        ]);
    }
}
