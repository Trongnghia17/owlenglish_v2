@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
   <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0 fw-bold">Bộ đề thi</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-secondary">
                            Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Lưu
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Form -->
    <div class="row">
        <div class=" col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exams.update', $exam) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @if($exam->image)
                        <div class="mb-3">
                            <label class="form-label">Ảnh bìa hiện tại</label>
                            <div class="position-relative" style="max-width: 300px;">
                                <img src="{{ Storage::url($exam->image) }}" 
                                     alt="{{ $exam->name }}" 
                                     class="img-thumbnail w-100"
                                     id="currentImage">
                                <button type="button" 
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                        onclick="removeCurrentImage()"
                                        style="z-index: 10;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                            </div>
                        </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                {{ $exam->image ? 'Thay đổi ảnh bìa' : 'Ảnh bìa' }}
                            </label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="ImagePreview.show(this)">
                            <small class="form-text text-muted">
                                Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB.
                            </small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 position-relative" style="display: none; max-width: 300px;">
                                <label class="form-label">Xem trước:</label>
                                <img id="preview" src="" alt="Preview" class="img-thumbnail w-100">
                                <button type="button" 
                                        class="btn btn-danger btn-sm position-absolute" 
                                        onclick="ImagePreview.remove()"
                                        style="top: 30px; right: 0; z-index: 10; margin: 0.5rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên bộ đề thi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $exam->name) }}"
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
                                <option value="ielts" {{ old('type', $exam->type) == 'ielts' ? 'selected' : '' }}>IELTS</option>
                                <option value="toeic" {{ old('type', $exam->type) == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                                <option value="online" {{ old('type', $exam->type) == 'online' ? 'selected' : '' }}>Online</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <div id="description-editor"></div>
                            <textarea class="form-control d-none @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description">{{ old('description', $exam->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Image -->
                        

                        Is Active
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $exam->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt
                                </label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Cập nhật
                            </button>
                            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

       
    </div>
    <!-- Tests List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nhóm đề thi</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTestModal">
                        <i class="bi bi-plus-circle me-1"></i>Thêm nhóm đề thi
                    </button>
                </div>
                <div class="card-body">
                    @forelse($exam->tests as $test)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                        {{ $test->name }}
                                    </h6>
                                    @if($test->description)
                                        <small class="text-muted">{{ $test->description }}</small>
                                    @endif
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('admin.exams.tests.show', [$exam, $test]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.tests.edit', [$exam, $test]) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @forelse($test->skills as $skill)
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($skill->skill_type == 'reading')
                                                <i class="bi bi-book text-primary fs-4 me-2"></i>
                                            @elseif($skill->skill_type == 'writing')
                                                <i class="bi bi-pencil-square text-success fs-4 me-2"></i>
                                            @elseif($skill->skill_type == 'listening')
                                                <i class="bi bi-headphones text-info fs-4 me-2"></i>
                                            @else
                                                <i class="bi bi-mic text-warning fs-4 me-2"></i>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $skill->name }}</h6>
                                                <small class="text-muted">{{ $skill->time_limit }} phút</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary">{{ $skill->sections->count() }} Section</span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">Chưa có skill nào</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Chưa có test nào</p>
                        <a href="{{ route('admin.exams.tests.create', $exam) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Thêm Test Đầu Tiên
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Create Test Modal -->
@include('admin.exams.tests._create_modal')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>.ql-container, .ql-editor { min-height: 300px; font-size: 14px; }</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets/js/admin-editor.js') }}"></script>
<script>
function removeCurrentImage() {
    const currentImageDiv = document.getElementById('currentImage').parentElement;
    const removeFlag = document.getElementById('removeImageFlag');
    
    if (confirm('Bạn có chắc muốn xóa hình ảnh hiện tại?')) {
        currentImageDiv.style.display = 'none';
        removeFlag.value = '1';
    }
}

// Initialize modal test description editor
document.addEventListener('DOMContentLoaded', function() {
    QuillEditor.init('#modal-test-description', 'Nhập mô tả về test này...');
});
</script>
@endpush
@endsection
