<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamTestController extends Controller
{
    /**
     * Display a listing of tests for an exam.
     */
    public function index(Exam $exam)
    {
        $tests = $exam->tests()->with('skills')->get();
        
        return view('admin.exams.tests.index', compact('exam', 'tests'));
    }

    /**
     * Show the form for creating a new test.
     */
    public function create(Exam $exam)
    {
        return view('admin.exams.tests.create', compact('exam'));
    }

    /**
     * Store a newly created test.
     */
    public function store(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('exam-tests', 'public');
        }

        $test = $exam->tests()->create($validated);

        return redirect()->route('admin.exams.tests.show', [$exam, $test])
            ->with('success', 'Test đã được tạo thành công!');
    }

    /**
     * Display the specified test.
     */
    public function show(Exam $exam, ExamTest $test)
    {
        // Ensure the test belongs to the exam
        if ($test->exam_id !== $exam->id) {
            abort(404);
        }

        $test->load(['skills.sections.questionGroups.questions']);
        
        return view('admin.exams.tests.show', compact('exam', 'test'));
    }

    /**
     * Show the form for editing the specified test.
     */
    public function edit(Exam $exam, ExamTest $test)
    {
        // Ensure the test belongs to the exam
        if ($test->exam_id !== $exam->id) {
            abort(404);
        }

        return view('admin.exams.tests.edit', compact('exam', 'test'));
    }

    /**
     * Update the specified test.
     */
    public function update(Request $request, Exam $exam, ExamTest $test)
    {
        // Ensure the test belongs to the exam
        if ($test->exam_id !== $exam->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($test->image && Storage::disk('public')->exists($test->image)) {
                Storage::disk('public')->delete($test->image);
            }
            $validated['image'] = $request->file('image')->store('exam-tests', 'public');
        }

        $test->update($validated);

        return redirect()->route('admin.exams.tests.show', [$exam, $test])
            ->with('success', 'Test đã được cập nhật thành công!');
    }

    /**
     * Remove the specified test.
     */
    public function destroy(Exam $exam, ExamTest $test)
    {
        // Ensure the test belongs to the exam
        if ($test->exam_id !== $exam->id) {
            abort(404);
        }

        // Delete image
        if ($test->image && Storage::disk('public')->exists($test->image)) {
            Storage::disk('public')->delete($test->image);
        }

        $test->delete();

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Test đã được xóa thành công!');
    }

    /**
     * Duplicate a test with all its nested data.
     */
    public function duplicate(Exam $exam, ExamTest $test)
    {
        // Ensure the test belongs to the exam
        if ($test->exam_id !== $exam->id) {
            abort(404);
        }

        $newTest = $test->duplicate();

        return redirect()->route('admin.exams.tests.show', [$exam, $newTest])
            ->with('success', 'Test đã được sao chép thành công!');
    }
}
