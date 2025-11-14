<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamTest;
use App\Models\ExamSkill;
use App\Models\ExamSection;
use App\Models\ExamQuestionGroup;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    /**
     * Display a listing of questions.
     */
    public function index(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        $questionGroups = $section->questionGroups()->with('questions')->get();
        
        return view('admin.exams.tests.skills.sections.questions.index', 
            compact('exam', 'test', 'skill', 'section', 'questionGroups'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        return view('admin.exams.tests.skills.sections.questions.create', 
            compact('exam', 'test', 'skill', 'section'));
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request, Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        // Implementation here
        
        return redirect()->route('admin.exams.tests.skills.sections.questions.index', 
            [$exam, $test, $skill, $section])
            ->with('success', 'Question đã được tạo thành công!');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section, ExamQuestion $question)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        return view('admin.exams.tests.skills.sections.questions.edit', 
            compact('exam', 'test', 'skill', 'section', 'question'));
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section, ExamQuestion $question)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        // Implementation here
        
        return redirect()->route('admin.exams.tests.skills.sections.questions.index', 
            [$exam, $test, $skill, $section])
            ->with('success', 'Question đã được cập nhật thành công!');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section, ExamQuestion $question)
    {
        $this->validateRelationships($exam, $test, $skill, $section);
        
        $question->delete();

        return redirect()->route('admin.exams.tests.skills.sections.questions.index', 
            [$exam, $test, $skill, $section])
            ->with('success', 'Question đã được xóa thành công!');
    }

    /**
     * Validate relationships
     */
    private function validateRelationships(Exam $exam, ExamTest $test, ExamSkill $skill, ExamSection $section)
    {
        if ($test->exam_id !== $exam->id) {
            abort(404, 'Test không thuộc Exam này');
        }
        
        if ($skill->exam_test_id !== $test->id) {
            abort(404, 'Skill không thuộc Test này');
        }
        
        if ($section->exam_skill_id !== $skill->id) {
            abort(404, 'Section không thuộc Skill này');
        }
    }
}
