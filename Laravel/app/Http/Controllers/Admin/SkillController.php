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
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
        
        return view('admin.skills.edit', compact('skill', 'exams'));
    }

    /**
     * Update the specified skill.
     */
    public function update(Request $request, ExamSkill $skill)
    {
        $validated = $request->validate([
            'exam_test_id' => 'required|exists:exam_tests,id',
            'skill_type' => 'required|in:reading,writing,listening,speaking',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $skill->update($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill đã được cập nhật thành công!');
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
