@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Chỉnh sửa Test</h1>
                
                </div>
                <div>
                    <a href="{{ route('admin.exams.tests.show', [$exam, $test]) }}" class="btn btn-secondary">
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
                    <form action="{{ route('admin.exams.tests.update', [$exam, $test]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Test <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $test->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description', $test->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Order -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Thứ tự</label>
                            <input type="number" 
                                   class="form-control @error('order') is-invalid @enderror" 
                                   id="order" 
                                   name="order" 
                                   value="{{ old('order', $test->order) }}"
                                   min="0">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Image -->
                        @if($test->image)
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            <div>
                                <img src="{{ Storage::url($test->image) }}" 
                                     alt="{{ $test->name }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px;">
                            </div>
                        </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                {{ $test->image ? 'Thay đổi hình ảnh' : 'Hình ảnh' }}
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
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <label class="form-label">Xem trước:</label>
                                <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Cập nhật
                            </button>
                            <a href="{{ route('admin.exams.tests.show', [$exam, $test]) }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thông tin</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-clock text-muted me-2"></i>
                            <strong>Tạo:</strong> {{ $test->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock-history text-muted me-2"></i>
                            <strong>Cập nhật:</strong> {{ $test->updated_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-grid text-muted me-2"></i>
                            <strong>Số Skill:</strong> {{ $test->skills->count() }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Exam: {{ $exam->name }}</h5>
                </div>
                <div class="card-body">
                    <span class="badge 
                        @if($exam->type == 'ielts') bg-primary
                        @elseif($exam->type == 'toeic') bg-success
                        @else bg-info
                        @endif">
                        {{ strtoupper($exam->type) }}
                    </span>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Khu vực nguy hiểm</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Xóa test sẽ xóa tất cả skill, section và câu hỏi liên quan.</p>
                    <form action="{{ route('admin.exams.tests.destroy', [$exam, $test]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Bạn có chắc muốn xóa? Hành động này không thể hoàn tác!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-2"></i>Xóa Test
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
</script>
@endpush
@endsection
