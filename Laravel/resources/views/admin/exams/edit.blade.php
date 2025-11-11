@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Chỉnh sửa Exam</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.exams.index') }}">Exams</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exams.update', $exam) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Exam <span class="text-danger">*</span></label>
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
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description', $exam->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Image -->
                        @if($exam->image)
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
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
                                {{ $exam->image ? 'Thay đổi hình ảnh' : 'Hình ảnh' }}
                            </label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="previewImage(event)">
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
                                        onclick="removeImage()"
                                        style="top: 30px; right: 0; z-index: 10; margin: 0.5rem;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Is Active -->
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

        <!-- Helper Card -->
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-clock text-muted me-2"></i>
                            <strong>Tạo:</strong> {{ $exam->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock-history text-muted me-2"></i>
                            <strong>Cập nhật:</strong> {{ $exam->updated_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-file-earmark-text text-muted me-2"></i>
                            <strong>Số Test:</strong> {{ $exam->tests->count() }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Khu vực nguy hiểm</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Xóa exam sẽ xóa tất cả test, skill, section và câu hỏi liên quan.</p>
                    <form action="{{ route('admin.exams.destroy', $exam) }}" 
                          method="POST" 
                          onsubmit="return confirm('Bạn có chắc muốn xóa? Hành động này không thể hoàn tác!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-2"></i>Xóa Exam
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const reader = new FileReader();
    const imagePreview = document.getElementById('imagePreview');
    const preview = document.getElementById('preview');
    
    reader.onload = function() {
        preview.src = reader.result;
        imagePreview.style.display = 'block';
    }
    
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    } else {
        imagePreview.style.display = 'none';
    }
}

function removeImage() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const preview = document.getElementById('preview');
    
    // Reset file input
    imageInput.value = '';
    
    // Hide preview
    imagePreview.style.display = 'none';
    preview.src = '';
}

function removeCurrentImage() {
    const currentImageDiv = document.getElementById('currentImage').parentElement;
    const removeFlag = document.getElementById('removeImageFlag');
    
    if (confirm('Bạn có chắc muốn xóa hình ảnh hiện tại?')) {
        currentImageDiv.style.display = 'none';
        removeFlag.value = '1';
    }
}
</script>
@endpush
@endsection
