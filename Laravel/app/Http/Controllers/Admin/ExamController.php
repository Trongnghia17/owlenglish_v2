<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        $query = Exam::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $exams = $query->withCount('tests')
            ->latest()
            ->paginate(10)
            ->appends($request->except('page'));

        return view('admin.exams.index', compact('exams'));
    }

    /**
     * Store a newly created exam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ielts,toeic',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('exams', 'public');
        }

        $exam = Exam::create($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Tạo bộ đề thi thành công!');
    }


    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        return view('admin.exams.edit', compact('exam'));
    }

    /**
     * Update the specified exam.
     */
    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ielts,toeic',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'is_active' => 'boolean',
            'remove_image' => 'nullable|in:0,1',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Handle image removal
        if ($request->input('remove_image') == '1') {
            if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                Storage::disk('public')->delete($exam->image);
            }
            $validated['image'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                Storage::disk('public')->delete($exam->image);
            }
            $validated['image'] = $request->file('image')->store('exams', 'public');
        }

        $exam->update($validated);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Cập nhật bộ đề thi thành công!');
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(Exam $exam)
    {
        // Delete image
        if ($exam->image && Storage::disk('public')->exists($exam->image)) {
            Storage::disk('public')->delete($exam->image);
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Xóa bộ đề thi thành công!');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Exam $exam)
    {
        $exam->update(['is_active' => !$exam->is_active]);

        return redirect()->back()
            ->with('success', 'Cập nhật trạng thái thành công!');
    }
}
