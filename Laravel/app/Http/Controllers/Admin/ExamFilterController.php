<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamFilter;
use Illuminate\Http\Request;

class ExamFilterController extends Controller
{
    /**
     * Danh sách bộ lọc
     */
    public function index(Request $request)
    {
        $query = ExamFilter::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $filters = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->all());

        $parents = ExamFilter::whereNull('parent_id')->get();

        return view(
            'admin.exam-filters.index',
            compact('filters', 'parents')
        );
    }

    /**
     * Form tạo mới
     */
    public function create()
    {
        $parents = ExamFilter::orderBy('type')->orderBy('name')->get();

        return view('admin.exam-filters.create', compact('parents'));
    }

    /**
     * Lưu bộ lọc
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:skill,group,format,other,value',
            'parent_id' => 'nullable|exists:exam_filters,id',
            'status'    => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        ExamFilter::create($data);

        return redirect()
            ->route('admin.exam-filters.index')
            ->with('success', 'Thêm bộ lọc thành công');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit(ExamFilter $examFilter)
    {
        $parents = ExamFilter::where('id', '!=', $examFilter->id)
        ->orderBy('type')
        ->orderBy('name')
        ->get();

        return view(
            'admin.exam-filters.edit',
            compact('examFilter', 'parents')
        );
    }

    /**
     * Cập nhật bộ lọc
     */
    public function update(Request $request, ExamFilter $examFilter)
    {
        // return $request->all();
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:skill,group,format,other,value',
            'parent_id' => 'nullable|exists:exam_filters,id',
            'status'    => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $examFilter->update($data);

        return redirect()
            ->route('admin.exam-filters.index')
            ->with('success', 'Cập nhật bộ lọc thành công');
    }

    /**
     * Xóa bộ lọc
     */
    public function destroy(ExamFilter $examFilter)
    {
        // Nếu có con → không cho xóa (optional)
        if ($examFilter->children()->exists()) {
            return redirect()->back()
                ->with('error', 'Không thể xóa bộ lọc đang có bộ lọc con');
        }

        $examFilter->delete();

        return redirect()
            ->route('admin.exam-filters.index')
            ->with('success', 'Đã xóa bộ lọc');
    }

    /**
     * Bật / tắt trạng thái
     */
    public function toggleStatus(ExamFilter $examFilter)
    {
        $examFilter->update([
            'status' => ! $examFilter->status
        ]);

        return redirect()->back();
    }
}
