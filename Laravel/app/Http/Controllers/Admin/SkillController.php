<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSkill;
use App\Models\Exam;
use App\Models\ExamTest;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Display a listing of skills.
     */
    public function index(Request $request)
    {
        $query = ExamSkill::with(['examTest.exam']);

        // Filter by skill type
        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->whereHas('examTest', function($q) use ($request) {
                $q->where('exam_id', $request->exam_id);
            });
        }

        // Filter by test
        if ($request->filled('test_id')) {
            $query->where('exam_test_id', $request->test_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $skills = $query->latest()->paginate(15);

        // Get all exams and tests for filter dropdowns
        $exams = Exam::orderBy('name')->get();
        $tests = ExamTest::orderBy('name')->get();

        return view('admin.skills.index', compact('skills', 'exams', 'tests'));
    }

    /**
     * Show the form for creating a new skill.
     */
    public function create()
    {
        $exams = Exam::with('tests')->orderBy('name')->get();
        
        return view('admin.skills.create', compact('exams'));
    }

    /**
     * Store a newly created skill.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
        ]);

        // Verify that exam_test_id belongs to the selected exam_id
        $examTest = ExamTest::where('id', $validated['exam_test_id'])
            ->where('exam_id', $validated['exam_id'])
            ->firstOrFail();

        // Remove exam_id from validated data as it's not in the database table
        unset($validated['exam_id']);
        
        // Ensure is_active is always boolean (true or false)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $skill = ExamSkill::create($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được tạo thành công!');
    }

    /**
     * Display the specified skill.
     */
    public function show(ExamSkill $skill)
    {
        $skill->load(['examTest.exam', 'sections.questionGroups.questions']);
        
        return view('admin.skills.show', compact('skill'));
    }

    /**
     * Show the form for editing the specified skill.
     */
    public function edit(ExamSkill $skill)
    {
        $exams = Exam::with('tests')->orderBy('name')->get();
        $skill->load(['sections.questionGroups.questions', 'examTest.exam']);
        
        return view('admin.skills.edit', compact('skill', 'exams'));
    }

    /**
     * Update the specified skill.
     */
    public function update(Request $request, ExamSkill $skill)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'sections' => 'nullable|array',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.content' => 'nullable|string',
            'sections.*.feedback' => 'nullable|string',
            'sections.*.content_format' => 'nullable|in:text,audio,video',
            'sections.*.groups' => 'nullable|array',
            'sections.*.groups.*.content' => 'nullable|string',
            'sections.*.groups.*.question_type' => 'nullable|in:multiple_choice,yes_no_not_given,true_false_not_given,short_text,fill_in_blank,matching,table_selection,essay,speaking',
            'sections.*.groups.*.questions' => 'nullable|array',
            'sections.*.groups.*.questions.*.content' => 'nullable|string',
            'sections.*.groups.*.questions.*.answer_content' => 'nullable|string',
            'sections.*.groups.*.questions.*.point' => 'nullable|numeric|min:0',
        ]);

        // Verify that exam_test_id belongs to the selected exam_id
        $examTest = ExamTest::where('id', $validated['exam_test_id'])
            ->where('exam_id', $validated['exam_id'])
            ->firstOrFail();

        // Remove exam_id from validated data as it's not in the database table
        unset($validated['exam_id']);
        
        // Ensure is_active is always boolean (true or false)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Extract sections data before updating skill
        $sectionsData = $validated['sections'] ?? [];
        unset($validated['sections']);

        // Update skill basic info
        $skill->update($validated);

        // Process sections
        $this->processSections($skill, $sectionsData);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được cập nhật thành công!');
    }

    /**
     * Process sections, question groups, and questions
     */
    private function processSections(ExamSkill $skill, array $sectionsData)
    {
        $existingSectionIds = [];

        foreach ($sectionsData as $sectionData) {
            $sectionId = $sectionData['id'] ?? null;
            
            // Prepare section data
            $sectionInfo = [
                'exam_skill_id' => $skill->id,
                'title' => $sectionData['title'] ?? '',
                'content' => $sectionData['content'] ?? '',
                'feedback' => $sectionData['feedback'] ?? '',
                'content_format' => $sectionData['content_format'] ?? 'text',
                'metadata' => [
                    'answer_inputs_inside_content' => isset($sectionData['answer_inputs_inside_content']),
                ],
                'is_active' => true,
            ];

            // Update or create section
            if ($sectionId) {
                $section = $skill->sections()->findOrFail($sectionId);
                $section->update($sectionInfo);
                $existingSectionIds[] = $sectionId;
            } else {
                $section = $skill->sections()->create($sectionInfo);
                $existingSectionIds[] = $section->id;
            }

            // Process question groups
            $groupsData = $sectionData['groups'] ?? [];
            $this->processQuestionGroups($section, $groupsData);
        }

        // Delete sections that are not in the request
        $skill->sections()->whereNotIn('id', $existingSectionIds)->delete();
    }

    /**
     * Process question groups
     */
    private function processQuestionGroups($section, array $groupsData)
    {
        $existingGroupIds = [];

        foreach ($groupsData as $groupData) {
            $groupId = $groupData['id'] ?? null;
            
            $groupInfo = [
                'exam_section_id' => $section->id,
                'content' => $groupData['content'] ?? '',
                'question_type' => $groupData['question_type'] ?? 'multiple_choice',
                'options' => [
                    'answer_inputs_inside_content' => isset($groupData['answer_inputs_inside_content']),
                    'split_questions_side_by_side' => isset($groupData['split_questions_side_by_side']),
                    'allow_drag_drop' => isset($groupData['allow_drag_drop']),
                ],
                'is_active' => true,
            ];

            if ($groupId) {
                $group = $section->questionGroups()->findOrFail($groupId);
                $group->update($groupInfo);
                $existingGroupIds[] = $groupId;
            } else {
                $group = $section->questionGroups()->create($groupInfo);
                $existingGroupIds[] = $group->id;
            }

            // Process questions
            $questionsData = $groupData['questions'] ?? [];
            $this->processQuestions($group, $questionsData);
        }

        // Delete groups that are not in the request
        $section->questionGroups()->whereNotIn('id', $existingGroupIds)->delete();
    }

    /**
     * Process questions
     */
    private function processQuestions($group, array $questionsData)
    {
        $existingQuestionIds = [];

        foreach ($questionsData as $questionData) {
            $questionId = $questionData['id'] ?? null;
            
            $questionInfo = [
                'exam_question_group_id' => $group->id,
                'content' => $questionData['content'] ?? '',
                'answer_content' => $questionData['answer_content'] ?? '',
                'point' => $questionData['point'] ?? 1,
                'metadata' => [
                    'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                ],
                'is_active' => true,
            ];

            if ($questionId) {
                $question = $group->questions()->findOrFail($questionId);
                $question->update($questionInfo);
                $existingQuestionIds[] = $questionId;
            } else {
                $question = $group->questions()->create($questionInfo);
                $existingQuestionIds[] = $question->id;
            }
        }

        // Delete questions that are not in the request
        $group->questions()->whereNotIn('id', $existingQuestionIds)->delete();
    }

    /**
     * Remove the specified skill.
     */
    public function destroy(ExamSkill $skill)
    {
        $skill->delete();

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được xóa thành công!');
    }

    /**
     * Toggle skill active status.
     */
    public function toggleActive(ExamSkill $skill)
    {
        $skill->update([
            'is_active' => !$skill->is_active
        ]);

        return back()->with('success', 'Trạng thái skill đã được cập nhật!');
    }

    /**
     * Get tests by exam (AJAX)
     */
    public function getTestsByExam(Request $request)
    {
        $examId = $request->get('exam_id');
        
        $tests = ExamTest::where('exam_id', $examId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($tests);
    }
}
