<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamTest;
use App\Models\ExamSkill;
use Illuminate\Http\Request;

class ExamSkillController extends Controller
{
    /**
     * Display a listing of skills for a test.
     */
    public function index(Exam $exam, ExamTest $test)
    {
        $this->validateTestBelongsToExam($exam, $test);
        
        $skills = $test->skills()->with('sections')->orderBy('order')->get();
        
        return view('admin.exams.tests.skills.index', compact('exam', 'test', 'skills'));
    }

    /**
     * Show the form for creating a new skill.
     */
    public function create(Exam $exam, ExamTest $test)
    {
        $this->validateTestBelongsToExam($exam, $test);
        
        return view('admin.exams.tests.skills.create', compact('exam', 'test'));
    }

    /**
     * Store a newly created skill.
     */
    public function store(Request $request, Exam $exam, ExamTest $test)
    {
        $this->validateTestBelongsToExam($exam, $test);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'time_limit' => 'required|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ]);

        // Auto-increment order if not provided
        if (!isset($validated['order'])) {
            $validated['order'] = $test->skills()->max('order') + 1;
        }

        $skill = $test->skills()->create($validated);

        return redirect()->route('admin.exams.tests.skills.show', [$exam, $test, $skill])
            ->with('success', 'Skill đã được tạo thành công!');
    }

    /**
     * Display the specified skill.
     */
    public function show(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateTestBelongsToExam($exam, $test);
        $this->validateSkillBelongsToTest($test, $skill);
        
        $skill->load(['sections.questionGroups.questions']);
        
        return view('admin.exams.tests.skills.show', compact('exam', 'test', 'skill'));
    }

    /**
     * Show the form for editing the specified skill.
     */
    public function edit(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateTestBelongsToExam($exam, $test);
        $this->validateSkillBelongsToTest($test, $skill);
        
        return view('admin.exams.tests.skills.edit', compact('exam', 'test', 'skill'));
    }

    /**
     * Update the specified skill.
     */
    public function update(Request $request, Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateTestBelongsToExam($exam, $test);
        $this->validateSkillBelongsToTest($test, $skill);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'time_limit' => 'required|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ]);

        $skill->update($validated);

        return redirect()->route('admin.exams.tests.skills.show', [$exam, $test, $skill])
            ->with('success', 'Skill đã được cập nhật thành công!');
    }

    /**
     * Remove the specified skill.
     */
    public function destroy(Exam $exam, ExamTest $test, ExamSkill $skill)
    {
        $this->validateTestBelongsToExam($exam, $test);
        $this->validateSkillBelongsToTest($test, $skill);
        
        $skill->delete();

        return redirect()->route('admin.exams.tests.show', [$exam, $test])
            ->with('success', 'Skill đã được xóa thành công!');
    }

    /**
     * Reorder skills.
     */
    public function reorder(Request $request, Exam $exam, ExamTest $test)
    {
        $this->validateTestBelongsToExam($exam, $test);
        
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:exam_skills,id',
        ]);

        foreach ($validated['order'] as $index => $skillId) {
            ExamSkill::where('id', $skillId)
                ->where('exam_test_id', $test->id)
                ->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thứ tự skill đã được cập nhật!',
        ]);
    }

    /**
     * Validate that test belongs to exam.
     */
    private function validateTestBelongsToExam(Exam $exam, ExamTest $test)
    {
        if ($test->exam_id !== $exam->id) {
            abort(404, 'Test không thuộc Exam này');
        }
    }

    /**
     * Validate that skill belongs to test.
     */
    private function validateSkillBelongsToTest(ExamTest $test, ExamSkill $skill)
    {
        if ($skill->exam_test_id !== $test->id) {
            abort(404, 'Skill không thuộc Test này');
        }
    }
}
