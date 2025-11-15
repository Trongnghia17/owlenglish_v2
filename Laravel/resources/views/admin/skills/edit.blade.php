@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .quill-container {
            height: auto;
        }
        .ql-editor {
            min-height: 100px;
        }
    </style>
@endpush

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
                        <i class="bi bi-save me-2"></i>Cập nhật
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.skills.update', $skill) }}" method="POST" id="skillForm">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-12">
                <div id="quiz-info" class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quiz Information</h5>
                    </div>
                    <div class="card-body">

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $skill->name) }}"
                                   placeholder="Enter quiz title"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Quiz Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Enter quiz description ">{{ old('description', $skill->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time Limit -->
                        <div class="mb-3">
                            <label for="time_limit" class="form-label">Time Limit <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('time_limit') is-invalid @enderror"
                                   id="time_limit"
                                   name="time_limit"
                                   value="{{ old('time_limit', $skill->time_limit) }}"
                                   min="1"
                                   placeholder="Enter time limit"
                                   required>
                            @error('time_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Skill Type -->
                        <div class="mb-3">
                            <label for="skill_type" class="form-label">Quiz Preset <span class="text-danger">*</span></label>
                            <select class="form-select @error('skill_type') is-invalid @enderror"
                                    id="skill_type"
                                    name="skill_type"
                                    required>
                                <option value="">Quiz Preset</option>
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

                        <!-- Is Active -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                    {{ old('is_active', $skill->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Visible outside syllabus
                                </label>
                            </div>
                        </div>

                        <!-- Exam Selection -->
                        <div class="mb-3">
                            <label for="exam_id" class="form-label">Quiz Collection <span class="text-danger">*</span></label>
                            <select class="form-select @error('exam_id') is-invalid @enderror"
                                    id="exam_id"
                                    name="exam_id"
                                    required>
                                <option value="">Quiz Collection</option>
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
                            <label for="exam_test_id" class="form-label">Quiz Group <span class="text-danger">*</span></label>
                            <select class="form-select @error('exam_test_id') is-invalid @enderror"
                                    id="exam_test_id"
                                    name="exam_test_id"
                                    required>
                                <option value="">-- Please select Quiz Collection first --</option>
                            </select>
                            @error('exam_test_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 col-12">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Navigation</h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="sectionNavigation" class="list-group list-group-flush">

                            @foreach($skill->sections as $index => $section)
                            <div class="section-nav-item">
                                <a href="#section-{{ $index }}" class="list-group-item list-group-item-action" onclick="scrollToSection(event, {{ $index }})">
                                    <i class="bi bi-file-text me-2"></i>Section {{ $index + 1 }}
                                </a>
                                @if($section->questionGroups->count() > 0)
                                <div class="ps-3">
                                    @foreach($section->questionGroups as $gIndex => $group)
                                    <div>
                                        <a href="#group-{{ $index }}-{{ $gIndex }}" class="list-group-item list-group-item-action small" onclick="scrollToGroup(event, {{ $index }}, {{ $gIndex }})">
                                            <i class="bi bi-diagram-3 me-2"></i>Group {{ $gIndex + 1 }}
                                        </a>
                                        @if($group->questions->count() > 0)
                                        <div class="ps-3">
                                            @foreach($group->questions as $qIndex => $question)
                                            <a href="#question-{{ $index }}-{{ $gIndex }}-{{ $qIndex }}" class="list-group-item list-group-item-action small text-muted py-1" onclick="scrollToQuestion(event, {{ $index }}, {{ $gIndex }}, {{ $qIndex }})">
                                                <i class="bi bi-question-circle me-2"></i>Question {{ $qIndex + 1 }}
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-12">
                <!-- Quiz Info Card -->


                <!-- Sections Builder Card -->
                <div id="sections-builder" class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Sections & Questions Builder</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSection()">
                                <i class="bi bi-plus-circle me-1"></i>Add Section
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="sectionsContainer">
                            @foreach($skill->sections as $sectionIndex => $section)
                            <div id="section-{{ $sectionIndex }}" class="section-item card mb-3" data-section-index="{{ $sectionIndex }}">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleSection(this)">
                                                    <i class="bi bi-chevron-down"></i>
                                                </button>
                                                <strong>Section {{ $sectionIndex + 1 }}</strong>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteSection(this)">
                                                Delete Section
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body section-content">
                                        <input type="hidden" name="sections[{{ $sectionIndex }}][id]" value="{{ $section->id }}">

                                        <!-- Section Title -->
                                        <div class="mb-3">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" class="form-control" name="sections[{{ $sectionIndex }}][title]"
                                                   value="{{ old('sections.'.$sectionIndex.'.title', $section->title) }}"
                                                   placeholder="Enter section title">
                                        </div>

                                        <!-- Section Content -->
                                        <div class="mb-3">
                                            <label class="form-label">Section Content</label>
                                            <input type="hidden" id="section-content-{{ $sectionIndex }}" name="sections[{{ $sectionIndex }}][content]" value="{{ old('sections.'.$sectionIndex.'.content', $section->content) }}">
                                            <div id="section-content-{{ $sectionIndex }}-editor"></div>
                                        </div>

                                        <!-- Section Feedback -->
                                        <div class="mb-3">
                                            <label class="form-label">Section Feedback</label>
                                            <input type="hidden" id="section-feedback-{{ $sectionIndex }}" name="sections[{{ $sectionIndex }}][feedback]" value="{{ old('sections.'.$sectionIndex.'.feedback', $section->feedback) }}">
                                            <div id="section-feedback-{{ $sectionIndex }}-editor"></div>
                                        </div>

                                        <!-- Content Format -->
                                        <div class="mb-3">
                                            <label class="form-label">Content Format</label>
                                            <select class="form-select" name="sections[{{ $sectionIndex }}][content_format]">
                                                <option value="text" {{ old('sections.'.$sectionIndex.'.content_format', $section->content_format) == 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="audio" {{ old('sections.'.$sectionIndex.'.content_format', $section->content_format) == 'audio' ? 'selected' : '' }}>Audio</option>
                                                <option value="video" {{ old('sections.'.$sectionIndex.'.content_format', $section->content_format) == 'video' ? 'selected' : '' }}>Video</option>
                                            </select>
                                        </div>

                                        <!-- Answer Inputs Inside Content -->
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="sections[{{ $sectionIndex }}][answer_inputs_inside_content]"
                                                       value="1"
                                                       {{ old('sections.'.$sectionIndex.'.answer_inputs_inside_content', $section->metadata['answer_inputs_inside_content'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    Answer inputs inside content
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Question Groups -->
                                        <div class="question-groups-container mt-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6>Question Groups</h6>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addQuestionGroup(this)">
                                                    <i class="bi bi-plus me-1"></i>Add Question Group
                                                </button>
                                            </div>

                                            <div class="groups-list">
                                                @foreach($section->questionGroups as $groupIndex => $group)
                                                <div id="group-{{ $sectionIndex }}-{{ $groupIndex }}" class="question-group-item border rounded p-3 mb-3" data-group-index="{{ $groupIndex }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleGroup(this)">
                                                                <i class="bi bi-chevron-down"></i>
                                                            </button>
                                                            <strong>Question Group {{ $groupIndex + 1 }}</strong>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteQuestionGroup(this)">
                                                            Delete Question Group
                                                        </button>
                                                    </div>

                                                    <div class="group-content">
                                                        <input type="hidden" name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][id]" value="{{ $group->id }}">

                                                        <!-- Question Group Content -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Question Group Content</label>
                                                            <input type="hidden" id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}" name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][content]" value="{{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.content', $group->content) }}">
                                                            <div id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}-editor"></div>
                                                        </div>

                                                        <!-- Question Type -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Question Type</label>
                                                            <select class="form-select" name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][question_type]">
                                                                <option value="multiple_choice" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.question_type', $group->question_type) == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                                                <option value="yes_no_not_given" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.question_type', $group->question_type) == 'yes_no_not_given' ? 'selected' : '' }}>Yes/No/Not Given</option>
                                                                <option value="true_false_not_given" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.question_type', $group->question_type) == 'true_false_not_given' ? 'selected' : '' }}>True/False/Not Given</option>
                                                                <option value="short_text" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.question_type', $group->question_type) == 'short_text' ? 'selected' : '' }}>Short Text</option>
                                                                <option value="table_selection" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.question_type', $group->question_type) == 'table_selection' ? 'selected' : '' }}>Table Selection</option>

                                                            </select>
                                                        </div>

                                                        <!-- Group Options -->
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][answer_inputs_inside_content]"
                                                                           value="1"
                                                                           {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.answer_inputs_inside_content', $group->options['answer_inputs_inside_content'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label">Answer inputs inside content</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][split_questions_side_by_side]"
                                                                           value="1"
                                                                           {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.split_questions_side_by_side', $group->options['split_questions_side_by_side'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label">Split content and questions side by side</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][allow_drag_drop]"
                                                                           value="1"
                                                                           {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.allow_drag_drop', $group->options['allow_drag_drop'] ?? false) ? 'checked' : '' }}>
                                                                    <label class="form-check-label">Allow drag and drop answers</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Questions -->
                                                        <div class="questions-container mt-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0">Questions</h6>
                                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addQuestion(this)">
                                                                    <i class="bi bi-plus me-1"></i>Add Question
                                                                </button>
                                                            </div>

                                                            <div class="questions-list">
                                                                @foreach($group->questions as $qIndex => $question)
                                                                <div id="question-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}" class="question-item bg-light p-3 rounded mb-2" data-question-index="{{ $qIndex }}">
                                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleQuestion(this)">
                                                                                <i class="bi bi-chevron-down"></i>
                                                                            </button>
                                                                            <strong>Question {{ $qIndex + 1 }}</strong>
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteQuestion(this)">
                                                                            Delete Question
                                                                        </button>
                                                                    </div>

                                                                    <div class="question-content">
                                                                        <input type="hidden" name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][id]" value="{{ $question->id }}">

                                                                        <!-- Question Content -->
                                                                        <div class="mb-2">
                                                                            <label class="form-label small">Question Content</label>
                                                                            <input type="hidden" id="question-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}" name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][content]" value="{{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.content', $question->content) }}">
                                                                            <div id="question-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-editor"></div>
                                                                        </div>

                                                                        <!-- Points -->
                                                                        <div class="row mb-2">
                                                                            <div class="col-md-6">
                                                                                <label class="form-label small">Points</label>
                                                                                <input type="number" step="0.01" class="form-control form-control-sm"
                                                                                       name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][point]"
                                                                                       value="{{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.point', $question->point ?? 1) }}">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label class="form-label small">Question Type</label>
                                                                                <select class="form-select form-select-sm"
                                                                                        name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][question_type]">
                                                                                    <option value="">Chọn</option>
                                                                                    <option value="multiple_choice" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.question_type', $question->metadata['question_type'] ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                                                                    <option value="yes_no_not_given" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.question_type', $question->metadata['question_type'] ?? '') == 'yes_no_not_given' ? 'selected' : '' }}>Yes/No/Not Given</option>
                                                                                    <option value="true_false_not_given" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.question_type', $question->metadata['question_type'] ?? '') == 'true_false_not_given' ? 'selected' : '' }}>True/False/Not Given</option>
                                                                                    <option value="short_text" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.question_type', $question->metadata['question_type'] ?? '') == 'short_text' ? 'selected' : '' }}>Short Text</option>
                                                                                    <option value="table_selection" {{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.question_type', $question->metadata['question_type'] ?? '') == 'table_selection' ? 'selected' : '' }}>Table Selection</option>

                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Answer Content (for text/essay) -->
                                                                        <div class="mb-2">
                                                                            <label class="form-label small">Answer/Correct Response</label>
                                                                            <input type="text" class="form-control form-control-sm"
                                                                                   name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answer_content]"
                                                                                   value="{{ old('sections.'.$sectionIndex.'.groups.'.$groupIndex.'.questions.'.$qIndex.'.answer_content', $question->answer_content) }}"
                                                                                   placeholder="Enter correct answer">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets/js/admin-editor.js') }}"></script>
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

// Section Management
let sectionIndex = {{ count($skill->sections) }};

function addSection() {
    const container = document.getElementById('sectionsContainer');
    const sectionHtml = `
        <div class="section-item card mb-3" data-section-index="${sectionIndex}">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleSection(this)">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <strong>Section ${sectionIndex + 1}</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteSection(this)">
                        Delete Section
                    </button>
                </div>
            </div>
            <div class="card-body section-content">
                <div class="mb-3">
                    <label class="form-label">Section Title</label>
                    <input type="text" class="form-control" name="sections[${sectionIndex}][title]" placeholder="Enter section title">
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Content</label>
                    <input type="hidden" id="section-content-new-${sectionIndex}" name="sections[${sectionIndex}][content]">
                    <div id="section-content-new-${sectionIndex}-editor"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Feedback</label>
                    <input type="hidden" id="section-feedback-new-${sectionIndex}" name="sections[${sectionIndex}][feedback]">
                    <div id="section-feedback-new-${sectionIndex}-editor"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Content Format</label>
                    <select class="form-select" name="sections[${sectionIndex}][content_format]">
                        <option value="text">Text</option>
                        <option value="audio">Audio</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="sections[${sectionIndex}][answer_inputs_inside_content]" value="1">
                        <label class="form-check-label">Answer inputs inside content</label>
                    </div>
                </div>
                <div class="question-groups-container mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>Question Groups</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addQuestionGroup(this)">
                            <i class="bi bi-plus me-1"></i>Add Question Group
                        </button>
                    </div>
                    <div class="groups-list"></div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', sectionHtml);

    // Initialize Quill editors for new section
    initQuillEditor(`section-content-new-${sectionIndex}`);
    initQuillEditor(`section-feedback-new-${sectionIndex}`);

    sectionIndex++;
    updateSectionNumbers();
    updateNavigation();
}

function deleteSection(btn) {
    if (confirm('Are you sure you want to delete this section?')) {
        btn.closest('.section-item').remove();
        updateSectionNumbers();
        updateNavigation();
    }
}

function toggleSection(btn) {
    const content = btn.closest('.card-header').nextElementSibling;
    const icon = btn.querySelector('i');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
    } else {
        content.style.display = 'none';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
    }
}

function updateSectionNumbers() {
    document.querySelectorAll('.section-item').forEach((section, index) => {
        section.querySelector('.card-header strong').textContent = `Section ${index + 1}`;
    });
}

// Question Group Management
function addQuestionGroup(btn) {
    const sectionItem = btn.closest('.section-item');
    const sectionIdx = sectionItem.dataset.sectionIndex;
    const groupsList = sectionItem.querySelector('.groups-list');
    const groupIndex = groupsList.querySelectorAll('.question-group-item').length;

    const groupHtml = `
        <div class="question-group-item border rounded p-3 mb-3" data-group-index="${groupIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleGroup(this)">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <strong>Question Group ${groupIndex + 1}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteQuestionGroup(this)">
                    Delete Question Group
                </button>
            </div>
            <div class="group-content">
                <div class="mb-3">
                    <label class="form-label">Question Group Content</label>
                    <input type="hidden" id="group-content-new-${sectionIdx}-${groupIndex}" name="sections[${sectionIdx}][groups][${groupIndex}][content]">
                    <div id="group-content-new-${sectionIdx}-${groupIndex}-editor"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Question Type</label>
                    <select class="form-select" name="sections[${sectionIdx}][groups][${groupIndex}][question_type]">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="yes_no_not_given">Yes/No/Not Given</option>
                        <option value="true_false_not_given">True/False/Not Given</option>
                        <option value="short_text">Short Text</option>
                        <option value="table_selection">Table Selection</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][answer_inputs_inside_content]" value="1">
                            <label class="form-check-label">Answer inputs inside content</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][split_questions_side_by_side]" value="1">
                            <label class="form-check-label">Split content and questions side by side</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIndex}][allow_drag_drop]" value="1">
                            <label class="form-check-label">Allow drag and drop answers</label>
                        </div>
                    </div>
                </div>
                <div class="questions-container mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Questions</h6>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addQuestion(this)">
                            <i class="bi bi-plus me-1"></i>Add Question
                        </button>
                    </div>
                    <div class="questions-list"></div>
                </div>
            </div>
        </div>
    `;
    groupsList.insertAdjacentHTML('beforeend', groupHtml);

    // Initialize Quill editor for new group
    initQuillEditor(`group-content-new-${sectionIdx}-${groupIndex}`);

    updateGroupNumbers(sectionItem);
    updateNavigation();
}

function deleteQuestionGroup(btn) {
    if (confirm('Are you sure you want to delete this question group?')) {
        const sectionItem = btn.closest('.section-item');
        btn.closest('.question-group-item').remove();
        updateGroupNumbers(sectionItem);
        updateNavigation();
    }
}

function toggleGroup(btn) {
    const content = btn.closest('.question-group-item').querySelector('.group-content');
    const icon = btn.querySelector('i');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
    } else {
        content.style.display = 'none';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
    }
}

function updateGroupNumbers(sectionItem) {
    sectionItem.querySelectorAll('.question-group-item').forEach((group, index) => {
        group.querySelector('strong').textContent = `Question Group ${index + 1}`;
    });
}

// Question Management
function addQuestion(btn) {
    const groupItem = btn.closest('.question-group-item');
    const sectionItem = btn.closest('.section-item');
    const sectionIdx = sectionItem.dataset.sectionIndex;
    const groupIdx = groupItem.dataset.groupIndex;
    const questionsList = groupItem.querySelector('.questions-list');
    const questionIndex = questionsList.querySelectorAll('.question-item').length;

    const questionHtml = `
        <div class="question-item bg-light p-3 rounded mb-2" data-question-index="${questionIndex}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-dark p-0" onclick="toggleQuestion(this)">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <strong>Question ${questionIndex + 1}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteQuestion(this)">
                    Delete Question
                </button>
            </div>
            <div class="question-content">
                <div class="mb-2">
                    <label class="form-label small">Question Content</label>
                    <input type="hidden" id="question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][content]">
                    <div id="question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}-editor"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label small">Points</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][point]" value="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Question Type</label>
                        <select class="form-select form-select-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][question_type]">
                            <option value="">Chọn</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="yes_no_not_given">Yes/No/Not Given</option>
                            <option value="true_false_not_given">True/False/Not Given</option>
                            <option value="short_text">Short Text</option>
                            <option value="table_selection">Table Selection</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Answer/Correct Response</label>
                    <input type="text" class="form-control form-control-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][answer_content]" placeholder="Enter correct answer">
                </div>
            </div>
        </div>
    `;
    questionsList.insertAdjacentHTML('beforeend', questionHtml);

    // Initialize Quill editor for new question
    initQuillEditor(`question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}`);

    updateQuestionNumbers(groupItem);
    updateNavigation();
}

function deleteQuestion(btn) {
    if (confirm('Are you sure you want to delete this question?')) {
        const groupItem = btn.closest('.question-group-item');
        btn.closest('.question-item').remove();
        updateQuestionNumbers(groupItem);
        updateNavigation();
    }
}

function toggleQuestion(btn) {
    const content = btn.closest('.question-item').querySelector('.question-content');
    const icon = btn.querySelector('i');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.replace('bi-chevron-right', 'bi-chevron-down');
    } else {
        content.style.display = 'none';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-right');
    }
}

function updateQuestionNumbers(groupItem) {
    groupItem.querySelectorAll('.question-item').forEach((question, index) => {
        question.querySelector('strong').textContent = `Question ${index + 1}`;
    });
}

// Navigation Functions
function scrollToElement(e, elementId) {
    e.preventDefault();
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function scrollToSection(e, index) {
    e.preventDefault();
    const section = document.getElementById(`section-${index}`);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function scrollToGroup(e, sectionIndex, groupIndex) {
    e.preventDefault();
    const group = document.getElementById(`group-${sectionIndex}-${groupIndex}`);
    if (group) {
        group.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function scrollToQuestion(e, sectionIndex, groupIndex, questionIndex) {
    e.preventDefault();
    const question = document.getElementById(`question-${sectionIndex}-${groupIndex}-${questionIndex}`);
    if (question) {
        question.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Update navigation when sections are added/removed
function updateNavigation() {
    const navContainer = document.getElementById('sectionNavigation');
    const sections = document.querySelectorAll('.section-item');

    let navHtml = ``;

    sections.forEach((section, sIndex) => {
        const sectionId = `section-${sIndex}`;
        section.id = sectionId;

        navHtml += `<div class="section-nav-item">
            <a href="#${sectionId}" class="list-group-item list-group-item-action" onclick="scrollToSection(event, ${sIndex})">
                <i class="bi bi-file-text me-2"></i>Section ${sIndex + 1}
            </a>`;

        const groups = section.querySelectorAll('.question-group-item');
        if (groups.length > 0) {
            navHtml += `<div class="ps-3">`;
            groups.forEach((group, gIndex) => {
                const groupId = `group-${sIndex}-${gIndex}`;
                group.id = groupId;
                navHtml += `<div>
                    <a href="#${groupId}" class="list-group-item list-group-item-action small" onclick="scrollToGroup(event, ${sIndex}, ${gIndex})">
                        <i class="bi bi-diagram-3 me-2"></i>Group ${gIndex + 1}
                    </a>`;

                const questions = group.querySelectorAll('.question-item');
                if (questions.length > 0) {
                    navHtml += `<div class="ps-3">`;
                    questions.forEach((question, qIndex) => {
                        const questionId = `question-${sIndex}-${gIndex}-${qIndex}`;
                        question.id = questionId;
                        navHtml += `<a href="#${questionId}" class="list-group-item list-group-item-action small text-muted py-1" onclick="scrollToQuestion(event, ${sIndex}, ${gIndex}, ${qIndex})">
                            <i class="bi bi-question-circle me-2"></i>Question ${qIndex + 1}
                        </a>`;
                    });
                    navHtml += `</div>`;
                }

                navHtml += `</div>`;
            });
            navHtml += `</div>`;
        }

        navHtml += `</div>`;
    });

    navContainer.innerHTML = navHtml;
}

// Initialize Quill Editors
document.addEventListener('DOMContentLoaded', function() {
    initializeAllEditors();
});

function initializeAllEditors() {
    // Initialize existing section content editors
    document.querySelectorAll('[id^="section-content-"][id$="-editor"]').forEach(editorDiv => {
        const id = editorDiv.id.replace('-editor', '');
        initQuillEditor(id);
    });

    // Initialize existing section feedback editors
    document.querySelectorAll('[id^="section-feedback-"][id$="-editor"]').forEach(editorDiv => {
        const id = editorDiv.id.replace('-editor', '');
        initQuillEditor(id);
    });

    // Initialize existing group content editors
    document.querySelectorAll('[id^="group-content-"][id$="-editor"]').forEach(editorDiv => {
        const id = editorDiv.id.replace('-editor', '');
        initQuillEditor(id);
    });

    // Initialize existing question content editors
    document.querySelectorAll('[id^="question-content-"][id$="-editor"]').forEach(editorDiv => {
        const id = editorDiv.id.replace('-editor', '');
        initQuillEditor(id);
    });
}

function initQuillEditor(elementId) {
    const hiddenInput = document.getElementById(elementId);
    const editorDiv = document.getElementById(elementId + '-editor');

    if (!editorDiv || editorDiv.classList.contains('ql-container')) {
        return; // Already initialized
    }

    const quill = new Quill('#' + elementId + '-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Nhập nội dung...'
    });

    // Set initial content
    if (hiddenInput && hiddenInput.value) {
        quill.root.innerHTML = hiddenInput.value;
    }

    // Update hidden input on change
    quill.on('text-change', function() {
        if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;
        }
    });

    return quill;
}
</script>
@endpush
@endsection

