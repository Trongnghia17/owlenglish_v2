<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamTest;
use App\Models\ExamSkill;
use App\Models\ExamSection;
use Illuminate\Http\Request;

class ExamSectionController extends Controller
{
    /**
     * Display a listing of sections.
     */
    public function index(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateRelationships($exam, $test, $skill);
        
        $sections = $skill->sections()->with('questionGroups')->get();
        
        return view('admin.exams.tests.skills.sections.index', compact('exam', 'test', 'skill', 'sections'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateRelationships($exam, $test, $skill);
        
        return view('admin.exams.tests.skills.sections.create', compact('exam', 'test', 'skill'));
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request, Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateRelationships($exam, $test, $skill);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'audio_file' => 'nullable|string',
            'video_file' => 'nullable|string',
        ]);

        $section = $skill->sections()->create($validated);

        return redirect()->route('admin.exams.tests.skills.show', [$exam, $test, $skill])
            ->with('success', 'Section đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill);
        $this->validateSectionBelongsToSkill($skill, $section);
        
        return view('admin.exams.tests.skills.sections.edit', compact('exam', 'test', 'skill', 'section'));
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill);
        $this->validateSectionBelongsToSkill($skill, $section);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'audio_file' => 'nullable|string',
            'video_file' => 'nullable|string',
        ]);

        $section->update($validated);

        return redirect()->route('admin.exams.tests.skills.show', [$exam, $test, $skill])
            ->with('success', 'Section đã được cập nhật thành công!');
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill);
        $this->validateSectionBelongsToSkill($skill, $section);
        
        $section->delete();

        return redirect()->route('admin.exams.tests.skills.show', [$exam, $test, $skill])
            ->with('success', 'Section đã được xóa thành công!');
    }

    /**
     * Validate relationships
     */
    private function validateRelationships(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        if ($test->exam_id !== $exam->id) {
            abort(404, 'Test không thuộc Exam này');
        }
        
        if ($skill->exam_test_id !== $test->id) {
            abort(404, 'Skill không thuộc Test này');
        }
    }

    /**
     * Validate section belongs to skill
     */
    private function validateSectionBelongsToSkill(ExamSkill $skill, ExamSection $section)
    {
        if ($section->exam_skill_id !== $skill->id) {
            abort(404, 'Section không thuộc Skill này');
        }
    }
}
