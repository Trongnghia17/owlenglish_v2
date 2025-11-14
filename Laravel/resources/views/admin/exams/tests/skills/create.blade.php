@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Thêm Skill Mới</h1>
         
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
                <div class="card-header">
                    <h5 class="mb-0">Thông tin Skill</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.exams.tests.skills.store', [$exam, $test]) }}" method="POST">
                        @csrf
                        
                        <!-- Skill Type -->
                        <div class="mb-3">
                            <label for="skill_type" class="form-label">Loại Skill <span class="text-danger">*</span></label>
                            <select class="form-select @error('skill_type') is-invalid @enderror" 
                                    id="skill_type" 
                                    name="skill_type" 
                                    required>
                                <option value="">-- Chọn loại skill --</option>
                                <option value="reading" {{ old('skill_type') == 'reading' ? 'selected' : '' }}>
                                    <i class="bi bi-book"></i> Reading (Đọc)
                                </option>
                                <option value="writing" {{ old('skill_type') == 'writing' ? 'selected' : '' }}>
                                    Writing (Viết)
                                </option>
                                <option value="listening" {{ old('skill_type') == 'listening' ? 'selected' : '' }}>
                                    Listening (Nghe)
                                </option>
                                <option value="speaking" {{ old('skill_type') == 'speaking' ? 'selected' : '' }}>
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
                                   value="{{ old('name') }}"
                                   placeholder="Ví dụ: Reading Part 1, Listening Section A..."
                                   required>
                            @error('name')
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
                                   value="{{ old('time_limit') }}"
                                   min="1"
                                   placeholder="60"
                                   required>
                            <small class="form-text text-muted">Thời gian làm bài cho skill này (tính bằng phút)</small>
                            @error('time_limit')
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
                                   value="{{ old('order') }}"
                                   min="0"
                                   placeholder="Để trống để tự động">
                            <small class="form-text text-muted">Thứ tự hiển thị của skill (để trống để tự động)</small>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Tạo Skill
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
                    <h5 class="mb-0">Test: {{ $test->name }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Exam: <strong>{{ $exam->name }}</strong>
                        <span class="badge 
                            @if($exam->type == 'ielts') bg-primary
                            @elseif($exam->type == 'toeic') bg-success
                            @else bg-info
                            @endif ms-2">
                            {{ strtoupper($exam->type) }}
                        </span>
                    </p>
                    <p class="text-muted small mb-0">
                        Số skill hiện tại: <strong>{{ $test->skills->count() }}</strong>
                    </p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            <strong>Reading:</strong> Kỹ năng đọc hiểu
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-success me-2"></i>
                            <strong>Writing:</strong> Kỹ năng viết
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <strong>Listening:</strong> Kỹ năng nghe
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-warning me-2"></i>
                            <strong>Speaking:</strong> Kỹ năng nói
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
