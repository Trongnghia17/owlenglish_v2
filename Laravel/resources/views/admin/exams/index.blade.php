@extends('layouts.app')

@section('content')
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-1 h2 fw-bold">Quản lý bộ đề thi</h1>

                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createExamModal">
                            <i class="bi bi-plus-circle me-2"></i>Thêm bộ đề thi mới
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.exams.index') }}" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" name="search" class="form-control" placeholder="Tiêu đề..."
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Loại</label>
                                <select name="type" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="ielts" {{ request('type') == 'ielts' ? 'selected' : '' }}>IELTS</option>
                                    <option value="toeic" {{ request('type') == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                                    <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="is_active" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hiển thị</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Lọc
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- bộ đề this Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-lg">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tiêu đề</th>
                                        <th class="text-center">Ảnh bìa</th>
                                        <th class="text-center">Loại</th>
                                        <th class="text-center">Số Test</th>
                                        <th class="text-center">Ngày tạo</th>
                                        <th class="text-center">Ngày cập nhật</th>
                                        <th class="text-center">Hiển thị</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exams as $exam)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $exam->name }}</div>

                                            </td>
                                            <td class="text-center">
                                                @if($exam->image)
                                                    <img src="{{ Storage::url($exam->image) }}" alt="{{ $exam->name }}"
                                                        class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                        style="width: 60px; height: 60px;">
                                                        <i class="bi bi-image text-muted fs-3"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="text-center">
                                                <span class="badge
                                                                                    @if($exam->type == 'ielts') bg-primary
                                                                                    @elseif($exam->type == 'toeic') bg-success
                                                                                    @else bg-info
                                                                                    @endif">
                                                    {{ strtoupper($exam->type) }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $exam->tests->count() }} Test</span>
                                            </td>

                                            <td class="text-center">
                                                {{ $exam->created_at->format('d/m/Y') }}
                                            </td>

                                            <td class="text-center">
                                                {{ $exam->updated_at->format('d/m/Y') }}
                                            </td>

                                            <td class="text-center">
                                                <form action="{{ route('admin.exams.toggle-active', $exam) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $exam->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                        {{ $exam->is_active ? 'Hiển thị' : 'Ẩn' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.exams.edit', $exam) }}"
                                                        class="btn btn-sm btn-warning" title="Sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                                <p class="text-muted">Không có bộ đề thi nào</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($exams->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $exams->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Create Modal --}}
    @include('admin.exams._create_modal')

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
        <style>
            .ql-container,
            .ql-editor {
                min-height: 200px;
                font-size: 14px;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
        <script src="{{ asset('assets/js/admin-editor.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize Quill editor for modal
                const modalEditor = QuillEditor.init('#modal-description', 'Nhập mô tả...');

                // Re-open modal if there are validation errors
                @if($errors->any())
                    var createExamModal = new bootstrap.Modal(document.getElementById('createExamModal'));
                    createExamModal.show();
                @endif
                                        });
        </script>
    @endpush
@endsection
