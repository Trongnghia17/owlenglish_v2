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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExamModal">
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
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tên bộ đề thi..." value="{{ request('search') }}">
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
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Không hoạt động</option>
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
                                    <th>Hình ảnh</th>
                                    <th>Tên bộ đề thi</th>
                                    <th>Loại</th>
                                    <th>Trạng thái</th>
                                    <th>Số Test</th>
                                    <th width="180">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                <tr>
                                    <td>
                                        @if($exam->image)
                                            <img src="{{ Storage::url($exam->image) }}" 
                                                 alt="{{ $exam->name }}" 
                                                 class="rounded"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-image text-muted fs-3"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $exam->name }}</div>
                                        <small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($exam->type == 'ielts') bg-primary
                                            @elseif($exam->type == 'toeic') bg-success
                                            @else bg-info
                                            @endif">
                                            {{ strtoupper($exam->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.exams.toggle-active', $exam) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $exam->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $exam->is_active ? 'Hoạt động' : 'Tắt' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $exam->tests->count() }} Test</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.exams.show', $exam) }}" 
                                               class="btn btn-sm btn-info" title="Xem">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.exams.edit', $exam) }}" 
                                               class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.exams.destroy', $exam) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
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
                                    <td colspan="6" class="text-center py-5">
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

<!-- Create Exam Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1" aria-labelledby="createExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createExamModalLabel">Thêm bộ đề thi mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.exams.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên bộ đề thi <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Loại <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">-- Chọn loại --</option>
                            <option value="ielts" {{ old('type') == 'ielts' ? 'selected' : '' }}>IELTS</option>
                            <option value="toeic" {{ old('type') == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                            <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="modal-description" class="form-label">Mô tả</label>
                        <div id="modal-description-editor"></div>
                        <textarea class="form-control d-none @error('description') is-invalid @enderror" 
                                  id="modal-description" 
                                  name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="modal-image" class="form-label">Hình ảnh</label>
                        <input type="file" 
                               class="form-control @error('image') is-invalid @enderror" 
                               id="modal-image" 
                               name="image"
                               accept="image/*"
                               onchange="ImagePreview.show(this, 'modalImagePreview')">
                        <small class="form-text text-muted">
                            Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB.
                        </small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Image Preview -->
                        <div id="modalImagePreview" class="mt-3 position-relative" style="display: none; max-width: 300px;">
                            <img src="" alt="Preview" class="img-thumbnail w-100">
                            <button type="button" 
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                    onclick="ImagePreview.remove('modal-image', 'modalImagePreview')"
                                    style="z-index: 10;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Is Active -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="modal-is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="modal-is_active">
                                Kích hoạt
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>.ql-container, .ql-editor { min-height: 200px; font-size: 14px; }</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets/js/admin-editor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
