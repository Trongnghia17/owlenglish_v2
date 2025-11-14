@extends('layouts.app')

@section('content')
    <div class="container-fluid p-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-1 h2 fw-bold">Quiz Builder</h1>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" form="skillForm" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Tạo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Quiz Builder</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.skills.store') }}" method="POST" id="skillForm">
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" placeholder="Enter quiz title" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Quiz Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3"
                                    placeholder="Enter quiz description ">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Time Limit -->
                            <div class="mb-3">
                                <label for="time_limit" class="form-label">Time Limit <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                    id="time_limit" name="time_limit" value="{{ old('time_limit') }}" min="1"
                                    placeholder="Enter time limit" required>

                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                             <!-- Skill Type -->
                            <div class="mb-3">
                                <label for="skill_type" class="form-label">Quiz Preset <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('skill_type') is-invalid @enderror" id="skill_type"
                                    name="skill_type" required>
                                    <option value="">Quiz Preset</option>
                                    <option value="reading" {{ old('skill_type') == 'reading' ? 'selected' : '' }}>
                                        Reading (Đọc)
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
                             <!-- Is Active -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                       Visible outside syllabus
                                    </label>
                                </div>
                            </div>
                            <!-- Exam Selection -->
                            <div class="mb-3">
                                <label for="exam_id" class="form-label">Quiz Collection <span class="text-danger">*</span></label>
                                <select class="form-select @error('exam_id') is-invalid @enderror" id="exam_id"
                                    name="exam_id" required>
                                    <option value="">Quiz Collection</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
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
                                <label for="exam_test_id" class="form-label">Quiz Group <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('exam_test_id') is-invalid @enderror" id="exam_test_id"
                                    name="exam_test_id" required disabled>
                                    <option value="">-- Please select Quiz Collection first --</option>
                                </select>
                                @error('exam_test_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                    

                            <!-- Submit -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Tạo 
                                </button>
                                <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">Hủy</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

           
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const examSelect = document.getElementById('exam_id');
                const testSelect = document.getElementById('exam_test_id');

                // Load tests when exam changes
                examSelect.addEventListener('change', function () {
                    const examId = this.value;

                    // Reset test select
                    testSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                    testSelect.disabled = true;

                    if (!examId) {
                        testSelect.innerHTML = '<option value="">-- Vui lòng chọn Quiz Collection trước --</option>';
                        return;
                    }

                    // Get tests for selected exam
                    const exams = @json($exams);
                    const selectedExam = exams.find(exam => exam.id == examId);

                    if (selectedExam && selectedExam.tests) {
                        testSelect.innerHTML = '<option value="">  Chọn Quiz Group </option>';
                        selectedExam.tests.forEach(test => {
                            const option = document.createElement('option');
                            option.value = test.id;
                            option.textContent = test.name;
                            testSelect.appendChild(option);
                        });
                        testSelect.disabled = false;
                    } else {
                        testSelect.innerHTML = '<option value="">-- Không có test nào --</option>';
                    }
                });

                // Restore selected values if validation fails
                @if(old('exam_id'))
                    examSelect.value = "{{ old('exam_id') }}";
                    examSelect.dispatchEvent(new Event('change'));

                    setTimeout(function () {
                        @if(old('exam_test_id'))
                            testSelect.value = "{{ old('exam_test_id') }}";
                        @endif
                                }, 100);
                @endif
                });
        </script>
    @endpush
@endsection