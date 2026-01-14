<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamCollection;
use Illuminate\Http\Request;

class ExamCollectionController extends Controller
{
    /**
     * Danh sách bộ đề
     */
    public function index(Request $request)
    {
        $query = ExamCollection::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $collections = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->all());

        return view('admin.exam-collections.index', compact('collections'));
    }

    /**
     * Form tạo mới
     */
    public function create()
    {
        return view('admin.exam-collections.create');
    }

    /**
     * Lưu bộ đề mới
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:ielts,toeic',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        ExamCollection::create($data);

        return redirect()
            ->route('admin.exam-collections.index')
            ->with('success', 'Thêm bộ đề thành công');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit(ExamCollection $examCollection)
    {
        return view('admin.exam-collections.edit', compact('examCollection'));
    }

    /**
     * Cập nhật bộ đề
     */
    public function update(Request $request, ExamCollection $examCollection)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|in:ielts,toeic',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $examCollection->update($data);

        return redirect()
            ->route('admin.exam-collections.index')
            ->with('success', 'Cập nhật bộ đề thành công');
    }

    /**
     * Xóa bộ đề
     */
    public function destroy(ExamCollection $examCollection)
    {
        // Nếu muốn an toàn hơn, có thể check còn exam dùng không
        // if ($examCollection->exams()->exists()) { ... }

        $examCollection->delete();

        return redirect()
            ->route('admin.exam-collections.index')
            ->with('success', 'Đã xóa bộ đề');
    }

    /**
     * Bật / tắt trạng thái
     */
    public function toggleStatus(ExamCollection $examCollection)
    {
        $examCollection->update([
            'status' => ! $examCollection->status
        ]);

        return redirect()->back();
    }
}
