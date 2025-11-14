@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Sửa Skill</h1>
                    <p class="text-muted mb-0">{{ $skill->name }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">
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
                <div class="card-header">
                    <h5 class="mb-0">Thông tin Skill</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.skills.update', $skill) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Exam Selection -->
                        <div class="mb-3">
                            <label for="exam_id" class="form-label">Chọn Exam <span class="text-danger">*</span></label>
                            <select class="form-select @error('exam_id') is-invalid @enderror" 
                                    id="exam_id" 
                                    name="exam_id" 
                                    required>
                                <option value="">-- Chọn Exam --</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" 
                                        {{ old('exam_id', $skill->examTest->exam_id) == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->name }} ({{ strtoupper($exam->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Test Selection -->
                        <div class="mb-3">
                            <label for="exam_test_id" class="form-label">Chọn Test <span class="text-danger">*</span></label>
                            <select class="form-select @error('exam_test_id') is-invalid @enderror" 
                                    id="exam_test_id" 
                                    name="exam_test_id" 
                                    required>
                                <option value="">-- Vui lòng chọn Exam trước --</option>
                            </select>
                            @error('exam_test_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Skill Type -->
                        <div class="mb-3">
                            <label for="skill_type" class="form-label">Loại Skill <span class="text-danger">*</span></label>
                            <select class="form-select @error('skill_type') is-invalid @enderror" 
                                    id="skill_type" 
                                    name="skill_type" 
                                    required>
                                <option value="">-- Chọn loại skill --</option>
                                <option value="reading" {{ old('skill_type', $skill->skill_type) == 'reading' ? 'selected' : '' }}>
                                    Reading (Đọc)
                                </option>
                                <option value="writing" {{ old('skill_type', $skill->skill_type) == 'writing' ? 'selected' : '' }}>
                                    Writing (Viết)
                                </option>
                                <option value="listening" {{ old('skill_type', $skill->skill_type) == 'listening' ? 'selected' : '' }}>
                                    Listening (Nghe)
                                </option>
                                <option value="speaking" {{ old('skill_type', $skill->skill_type) == 'speaking' ? 'selected' : '' }}>
                                    Speaking (Nói)
                                </option>
                            </select>
                            @error('skill_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Skill <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $skill->name) }}"
                                   placeholder="Ví dụ: Reading Part 1, Listening Section A..."
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
                                      rows="3"
                                      placeholder="Mô tả về skill này...">{{ old('description', $skill->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time Limit -->
                        <div class="mb-3">
                            <label for="time_limit" class="form-label">Thời gian (phút) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('time_limit') is-invalid @enderror" 
                                   id="time_limit" 
                                   name="time_limit" 
                                   value="{{ old('time_limit', $skill->time_limit) }}"
                                   min="1"
                                   placeholder="60"
                                   required>
                            <small class="form-text text-muted">Thời gian làm bài cho skill này (tính bằng phút)</small>
                            @error('time_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       {{ old('is_active', $skill->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt
                                </label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Cập nhật Skill
                            </button>
                            <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-list-check text-primary me-2"></i>
                            <strong>Sections:</strong> {{ $skill->sections->count() }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar text-success me-2"></i>
                            <strong>Tạo lúc:</strong> {{ $skill->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock-history text-info me-2"></i>
                            <strong>Cập nhật:</strong> {{ $skill->updated_at->format('d/m/Y H:i') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const examSelect = document.getElementById('exam_id');
    const testSelect = document.getElementById('exam_test_id');
    const currentTestId = {{ $skill->exam_test_id }};
    
    // Load tests when exam changes
    examSelect.addEventListener('change', function() {
        const examId = this.value;
        
        // Reset test select
        testSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        testSelect.disabled = true;
        
        if (!examId) {
            testSelect.innerHTML = '<option value="">-- Vui lòng chọn Exam trước --</option>';
            return;
        }
        
        // Get tests for selected exam
        const exams = @json($exams);
        const selectedExam = exams.find(exam => exam.id == examId);
        
        if (selectedExam && selectedExam.tests) {
            testSelect.innerHTML = '<option value="">-- Chọn Test --</option>';
            selectedExam.tests.forEach(test => {
                const option = document.createElement('option');
                option.value = test.id;
                option.textContent = test.name;
                if (test.id == currentTestId) {
                    option.selected = true;
                }
                testSelect.appendChild(option);
            });
            testSelect.disabled = false;
        } else {
            testSelect.innerHTML = '<option value="">-- Không có test nào --</option>';
        }
    });
    
    // Trigger change event on page load
    examSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection
