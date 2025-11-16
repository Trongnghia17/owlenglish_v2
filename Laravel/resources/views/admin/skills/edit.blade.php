@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }

        .quill-container {
            height: auto;
        }

        .ql-editor {
            min-height: 100px;
        }

        .skill-specific-field {
            display: none;
        }

        .skill-type-listening .listening-field,
        .skill-type-reading .reading-field,
        .skill-type-writing .writing-field,
        .skill-type-speaking .speaking-field {
            display: block;
        }

        .skill-info-content>div {
            display: none;
        }

        .skill-info-content.skill-type-listening .listening-info,
        .skill-info-content.skill-type-reading .reading-info,
        .skill-info-content.skill-type-writing .writing-info,
        .skill-info-content.skill-type-speaking .speaking-info {
            display: block !important;
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
                        <div class="card-body">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $skill->name) }}" placeholder="Enter quiz title"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Quiz Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3"
                                    placeholder="Enter quiz description ">{{ old('description', $skill->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Time Limit -->
                            <div class="mb-3">
                                <label for="time_limit" class="form-label">Time Limit <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                    id="time_limit" name="time_limit" value="{{ old('time_limit', $skill->time_limit) }}"
                                    min="1" placeholder="Enter time limit" required>
                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Skill Type -->
                            <div class="mb-3">
                                <label for="skill_type" class="form-label">Quiz Preset <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('skill_type') is-invalid @enderror" id="skill_type"
                                    name="skill_type" required disabled>
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
                                <input type="hidden" name="skill_type" value="{{ $skill->skill_type }}">
                                @error('skill_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Is Active -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $skill->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Visible outside syllabus
                                    </label>
                                </div>
                            </div>

                            <!-- Exam Selection -->
                            <div class="mb-3">
                                <label for="exam_id" class="form-label">Quiz Collection <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('exam_id') is-invalid @enderror" id="exam_id"
                                    name="exam_id" required>
                                    <option value="">Quiz Collection</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ old('exam_id', $skill->examTest->exam_id) == $exam->id ? 'selected' : '' }}>
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
                                    name="exam_test_id" required>
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
                <div class="col-lg-3 col-12" style="position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto;">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Navigation</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="sectionNavigation" class="list-group list-group-flush">

                                @foreach($skill->sections as $index => $section)
                                    <div class="section-nav-item">
                                        <a href="#section-{{ $index }}" class="list-group-item list-group-item-action">
                                            <i class="bi bi-file-text me-2"></i>Section {{ $index + 1 }}
                                        </a>

                                        @if($skill->isSpeaking() || $skill->isWriting())
                                            {{-- Speaking/Writing: Direct questions without groups --}}
                                            @if($section->questions->count() > 0)
                                                <div class="ps-3">
                                                    @foreach($section->questions as $qIndex => $question)
                                                        <a href="#direct-question-{{ $index }}-{{ $qIndex }}"
                                                            class="list-group-item list-group-item-action small text-muted py-1">
                                                            <i class="bi bi-question-circle me-2"></i>Question {{ $qIndex + 1 }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            {{-- Listening/Reading: Questions grouped --}}
                                            @if($section->questionGroups->count() > 0)
                                                <div class="ps-3">
                                                    @foreach($section->questionGroups as $gIndex => $group)
                                                        <div>
                                                            <a href="#group-{{ $index }}-{{ $gIndex }}"
                                                                class="list-group-item list-group-item-action small">
                                                                <i class="bi bi-diagram-3 me-2"></i>Group {{ $gIndex + 1 }}
                                                            </a>
                                                            @if($group->questions->count() > 0)
                                                                <div class="ps-3">
                                                                    @foreach($group->questions as $qIndex => $question)
                                                                        <a href="#question-{{ $index }}-{{ $gIndex }}-{{ $qIndex }}"
                                                                            class="list-group-item list-group-item-action small text-muted py-1">
                                                                            <i class="bi bi-question-circle me-2"></i>Question {{ $qIndex + 1 }}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
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
                                <button type="button" class="btn btn-sm btn-outline-primary" data-action="add-section">
                                    <i class="bi bi-plus-circle me-1"></i>Add Section
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="sectionsContainer" class="skill-type-{{ $skill->skill_type }}">
                                @foreach($skill->sections as $sectionIndex => $section)
                                    <div id="section-{{ $sectionIndex }}" class="section-item card mb-3"
                                        data-section-index="{{ $sectionIndex }}">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link text-dark p-0"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#section-body-{{ $sectionIndex }}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <strong>Section {{ $sectionIndex + 1 }}</strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger"
                                    data-action="delete-section">
                                    Delete Section
                                </button>
                            </div>
                        </div>
                        <div class="card-body section-content collapse show"
                            id="section-body-{{ $sectionIndex }}">
                            <input type="hidden" name="sections[{{ $sectionIndex }}][id]"
                                value="{{ $section->id }}">                                            <!-- Section Title -->
                                            <div class="mb-3">
                                                <label class="form-label">Section Title</label>
                                                <input type="text" class="form-control"
                                                    name="sections[{{ $sectionIndex }}][title]"
                                                    value="{{ old('sections.' . $sectionIndex . '.title', $section->title) }}"
                                                    placeholder="Enter section title">
                                            </div>

                                            <!-- Section Content -->
                                            <div class="mb-3">
                                                <label class="form-label">Section Content</label>
                                                <input type="hidden" id="section-content-{{ $sectionIndex }}"
                                                    name="sections[{{ $sectionIndex }}][content]"
                                                    value="{{ old('sections.' . $sectionIndex . '.content', $section->content) }}">
                                                <div id="section-content-{{ $sectionIndex }}-editor"></div>
                                            </div>

                                            <!-- Section Feedback -->
                                            <div class="mb-3">
                                                <label class="form-label">Section Feedback</label>
                                                <input type="hidden" id="section-feedback-{{ $sectionIndex }}"
                                                    name="sections[{{ $sectionIndex }}][feedback]"
                                                    value="{{ old('sections.' . $sectionIndex . '.feedback', $section->feedback) }}">
                                                <div id="section-feedback-{{ $sectionIndex }}-editor"></div>
                                            </div>

                                            <!-- Audio File Upload (for Listening) -->
                                            <div class="mb-3 skill-specific-field listening-field">
                                                <label class="form-label">Audio File <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control"
                                                    name="sections[{{ $sectionIndex }}][audio_file]" accept="audio/*">
                                                @if($section->metadata['audio_file'] ?? false)
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current:
                                                            {{ $section->metadata['audio_file'] }}</small>
                                                        <audio controls class="w-100 mt-1">
                                                            <source src="{{ asset('storage/' . $section->metadata['audio_file']) }}"
                                                                type="audio/mpeg">
                                                        </audio>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Video File Upload (for Listening with video) -->
                                            <div class="mb-3 skill-specific-field listening-field">
                                                <label class="form-label">Video File (Optional)</label>
                                                <input type="file" class="form-control"
                                                    name="sections[{{ $sectionIndex }}][video_file]" accept="video/*">
                                                @if($section->metadata['video_file'] ?? false)
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current:
                                                            {{ $section->metadata['video_file'] }}</small>
                                                        <video controls class="w-100 mt-1" style="max-height: 300px;">
                                                            <source src="{{ asset('storage/' . $section->metadata['video_file']) }}"
                                                                type="video/mp4">
                                                        </video>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Answer Inputs Inside Content -->
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="sections[{{ $sectionIndex }}][answer_inputs_inside_content]"
                                                        value="1" {{ old('sections.' . $sectionIndex . '.answer_inputs_inside_content', $section->metadata['answer_inputs_inside_content'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        Answer inputs inside content
                                                    </label>
                                                </div>
                                            </div>

                                            @if($skill->isSpeaking() || $skill->isWriting())
                                                <!-- Direct Questions (for Speaking/Writing) -->
                                                <div class="direct-questions-container mt-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6>Questions</h6>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            data-action="add-direct-question">
                                                            <i class="bi bi-plus me-1"></i>Add Question
                                                        </button>
                                                    </div>

                                                    <div class="direct-questions-list">
                                                        @foreach($section->questions as $qIndex => $question)
                                                            <div id="direct-question-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                class="direct-question-item bg-light p-3 rounded mb-3"
                                                                data-question-index="{{ $qIndex }}">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-link text-dark p-0"
                                                                            data-bs-toggle="collapse"
                                                                            data-bs-target="#direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}">
                                                                            <i class="bi bi-chevron-down"></i>
                                                                        </button>
                                                                        <strong>Question {{ $qIndex + 1 }}</strong>
                                                                    </div>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-link text-danger"
                                                                        data-action="delete-direct-question">
                                                                        Delete Question
                                                                    </button>
                                                                </div>

                                                                <div class="collapse show"
                                                                    id="direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}">
                                                                    <input type="hidden"
                                                                        name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][id]"
                                                                        value="{{ $question->id }}">

                                                                    <!-- Question Content -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Question Content</label>
                                                                        <input type="hidden"
                                                                            id="direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][content]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.content', $question->content) }}">
                                                                        <div id="direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Points -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Points</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][point]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.point', $question->point ?? 1) }}">
                                                                    </div>

                                                                    <!-- Answer Content (for Writing/Speaking) -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Sample Answer / Marking Criteria</label>
                                                                        <input type="hidden"
                                                                            id="direct-answer-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][answer_content]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.answer_content', $question->answer_content) }}">
                                                                        <div id="direct-answer-{{ $sectionIndex }}-{{ $qIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Feedback -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Feedback</label>
                                                                        <input type="hidden"
                                                                            id="direct-feedback-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][feedback]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.feedback', $question->feedback) }}">
                                                                        <div id="direct-feedback-{{ $sectionIndex }}-{{ $qIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Hint -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Hint (Optional)</label>
                                                                        <input type="text"
                                                                            class="form-control"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][hint]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.hint', $question->hint) }}"
                                                                            placeholder="Enter hint for this question">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Question Groups (for Listening/Reading) -->
                                                <div class="question-groups-container mt-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6>Question Groups</h6>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                            data-action="add-group">
                                                            <i class="bi bi-plus me-1"></i>Add Question Group
                                                        </button>
                                                    </div>

                                                    <div class="groups-list">
                                                    @foreach($section->questionGroups as $groupIndex => $group)
                                                        <div id="group-{{ $sectionIndex }}-{{ $groupIndex }}"
                                                            class="question-group-item border rounded p-3 mb-3"
                                                            data-group-index="{{ $groupIndex }}">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <button type="button" class="btn btn-sm btn-link text-dark p-0"
                                                                        data-bs-toggle="collapse"
                                                                        data-bs-target="#group-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}">
                                                                        <i class="bi bi-chevron-down"></i>
                                                                    </button>
                                                                    <strong>Question Group {{ $groupIndex + 1 }}</strong>
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-link text-danger"
                                                                    data-action="delete-group">
                                                                    Delete Question Group
                                                                </button>
                                                            </div>

                                                            <div class="group-content collapse show"
                                                                id="group-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}">
                                                                <input type="hidden"
                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][id]"
                                                                    value="{{ $group->id }}">

                                                                <!-- Question Group Content -->
                                                                <div class="mb-3">
                                                                    <label class="form-label">Question Group Content</label>
                                                                    <input type="hidden"
                                                                        id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}"
                                                                        name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][content]"
                                                                        value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.content', $group->content) }}">
                                                                    <div
                                                                        id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}-editor">
                                                                    </div>
                                                                </div>

                                                                <!-- Question Type -->
                                                                <div class="mb-3">
                                                                    <label class="form-label">Question Type</label>
                                                                    <select class="form-select"
                                                                        name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][question_type]">
                                                                        <option value="multiple_choice" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                                                        <option value="yes_no_not_given" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'yes_no_not_given' ? 'selected' : '' }}>Yes/No/Not Given</option>
                                                                        <option value="true_false_not_given" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'true_false_not_given' ? 'selected' : '' }}>True/False/Not Given</option>
                                                                        <option value="short_text" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'short_text' ? 'selected' : '' }}>Short Text</option>
                                                                        <option value="table_selection" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'table_selection' ? 'selected' : '' }}>Table Selection</option>

                                                                    </select>
                                                                </div>

                                                                <!-- Group Options -->
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][answer_inputs_inside_content]"
                                                                                value="1" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.answer_inputs_inside_content', $group->options['answer_inputs_inside_content'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label">Answer inputs inside
                                                                                content</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][split_questions_side_by_side]"
                                                                                value="1" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.split_questions_side_by_side', $group->options['split_questions_side_by_side'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label">Split content and
                                                                                questions side by side</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][allow_drag_drop]"
                                                                                value="1" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.allow_drag_drop', $group->options['allow_drag_drop'] ?? false) ? 'checked' : '' }}>
                                                                            <label class="form-check-label">Allow drag and drop
                                                                                answers</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Questions -->
                                                                <div class="questions-container mt-3">
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center mb-2">
                                                                        <h6 class="mb-0">Questions</h6>
                                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                                            data-action="add-question">
                                                                            <i class="bi bi-plus me-1"></i>Add Question
                                                                        </button>
                                                                    </div>

                                                                    <div class="questions-list">
                                                                        @foreach($group->questions as $qIndex => $question)
                                                                            <div id="question-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}"
                                                                                class="question-item bg-light p-3 rounded mb-2"
                                                                                data-question-index="{{ $qIndex }}">
                                                                                <div
                                                                                    class="d-flex justify-content-between align-items-start mb-2">
                                                                                    <div class="d-flex align-items-center gap-2">
                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-link text-dark p-0"
                                                                                            data-bs-toggle="collapse"
                                                                                            data-bs-target="#question-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}">
                                                                                            <i class="bi bi-chevron-down"></i>
                                                                                        </button>
                                                                                        <strong>Question {{ $qIndex + 1 }}</strong>
                                                                                    </div>
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-link text-danger"
                                                                                        data-action="delete-question">
                                                                                        Delete Question
                                                                                    </button>
                                                                                </div>

                                                                                <div class="question-content collapse show"
                                                                                    id="question-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}">
                                                                                    <input type="hidden"
                                                                                        name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][id]"
                                                                                        value="{{ $question->id }}">

                                                                                    <!-- Question Content -->
                                                                                    <div class="mb-2">
                                                                                        <label class="form-label small">Question
                                                                                            Content</label>
                                                                                        <input type="hidden"
                                                                                            id="question-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}"
                                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][content]"
                                                                                            value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.content', $question->content) }}">
                                                                                        <div
                                                                                            id="question-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-editor">
                                                                                        </div>
                                                                                    </div>

                                                                                    <!-- Points -->
                                                                                    <div class="row mb-2">
                                                                                        <div class="col-md-6">
                                                                                            <label
                                                                                                class="form-label small">Points</label>
                                                                                            <input type="number" step="0.01"
                                                                                                class="form-control form-control-sm"
                                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][point]"
                                                                                                value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.point', $question->point ?? 1) }}">
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <label class="form-label small">Question
                                                                                                Type</label>
                                                                                            <select
                                                                                                class="form-select form-select-sm question-type-select"
                                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][question_type]">
                                                                                                <option value="">Chọn</option>
                                                                                                <option value="multiple_choice" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice
                                                                                                </option>
                                                                                                <option value="yes_no_not_given" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '') == 'yes_no_not_given' ? 'selected' : '' }}>Yes/No/Not Given
                                                                                                </option>
                                                                                                <option value="true_false_not_given" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '') == 'true_false_not_given' ? 'selected' : '' }}>True/False/Not
                                                                                                    Given</option>
                                                                                                <option value="short_text" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '') == 'short_text' ? 'selected' : '' }}>Short Text</option>
                                                                                                <option value="table_selection" {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '') == 'table_selection' ? 'selected' : '' }}>Table Selection
                                                                                                </option>

                                                                                            </select>
                                                                                        </div>
                                                                                    </div>




                                                                                    <!-- Answers List Section -->
                                                                                    <div class="mb-3 answers-list-section">
                                                                                        <div class="answers-list"
                                                                                            data-question-id="{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}">
                                                                                            @php
                                                                                                $answers = $question->metadata['answers'] ?? [];
                                                                                                // If old answer_content exists, convert to new format
                                                                                                if (empty($answers) && !empty($question->answer_content)) {
                                                                                                    $answers = [
                                                                                                        ['content' => $question->answer_content, 'is_correct' => true]
                                                                                                    ];
                                                                                                }
                                                                                            @endphp
                                                                                            @foreach($answers as $ansIndex => $answer)
                                                                                                <div class="answer-item mb-3 p-3"
                                                                                                    style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; position: relative;">
                                                                                                    <!-- Answer Content: dropdown for Yes/No/Not Given and True/False/Not Given; otherwise rich text -->
                                                                                                    @php
                                                                                                        $qType = old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.question_type', $question->metadata['question_type'] ?? '');
                                                                                                        $isYN = $qType === 'yes_no_not_given';
                                                                                                        $isTF = $qType === 'true_false_not_given';
                                                                                                    @endphp
                                                                                                    <div class="mb-3">
                                                                                                        <label class="form-label small fw-semibold">Answer Content</label>
                                                                                                        @if($isYN || $isTF)
                                                                                                            @php
                                                                                                                $options = $isYN ? ['Yes','No','Not Given'] : ['True','False','Not Given'];
                                                                                                                $defaultVal = $isYN ? 'Yes' : 'True';
                                                                                                                $selectedVal = old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.answers.' . $ansIndex . '.content', $answer['content'] ?? $defaultVal);
                                                                                                            @endphp
                                                                                                            <select class="form-select form-select-sm"
                                                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][content]">
                                                                                                                @foreach($options as $opt)
                                                                                                                    <option value="{{ $opt }}" {{ $selectedVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        @else
                                                                                                            <input type="hidden"
                                                                                                                id="answer-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}"
                                                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][content]"
                                                                                                                value="{{ $answer['content'] ?? '' }}">
                                                                                                            <div id="answer-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}-editor"
                                                                                                                style="background: white;"></div>
                                                                                                        @endif
                                                                                                    </div>

                                                                                                    <!-- Feedback with Rich Text Editor -->
                                                                                                    <div class="mb-3">
                                                                                                        <label
                                                                                                            class="form-label small fw-semibold">Feedback</label>
                                                                                                        <input type="hidden"
                                                                                                            id="answer-feedback-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}"
                                                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][feedback]"
                                                                                                            value="{{ $answer['feedback'] ?? '' }}">
                                                                                                        <div id="answer-feedback-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}-editor"
                                                                                                            style="background: white;">
                                                                                                        </div>
                                                                                                    </div>

                                                                                                    <!-- Is Correct & Delete Answer -->
                                                                                                    <div
                                                                                                        class="d-flex justify-content-between align-items-center">
                                                                                                        <div class="form-check">
                                                                                                            <input class="form-check-input"
                                                                                                                type="checkbox"
                                                                                                                id="answer-correct-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}"
                                                                                                                name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][is_correct]"
                                                                                                                value="1" {{ ($answer['is_correct'] ?? false) ? 'checked' : '' }}>
                                                                                                            <label class="form-check-label"
                                                                                                                for="answer-correct-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}">
                                                                                                                Is correct
                                                                                                            </label>
                                                                                                        </div>
                                                                                                        <button type="button"
                                                                                                            class="btn btn-sm text-danger"
                                                                                                            data-action="delete-answer"
                                                                                                            style="text-decoration: none;">
                                                                                                            Delete Answer
                                                                                                        </button>
                                                                                                    </div>

                                                                                                    <!-- Move Up/Down Arrows -->
                                                                                                    <div class="position-absolute"
                                                                                                        style="top: 10px; left: -25px; display: flex; flex-direction: column; gap: 5px;">
                                                                                                        <button type="button"
                                                                                                            class="btn btn-sm btn-light border"
                                                                                                            data-action="move-answer-up"
                                                                                                            style="padding: 2px 8px;">
                                                                                                            <i class="bi bi-chevron-up"></i>
                                                                                                        </button>
                                                                                                        <button type="button"
                                                                                                            class="btn btn-sm btn-light border"
                                                                                                            data-action="move-answer-down"
                                                                                                            style="padding: 2px 8px;">
                                                                                                            <i
                                                                                                                class="bi bi-chevron-down"></i>
                                                                                                        </button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        </div>

                                                                                        <!-- Answer to create Section -->
                                                                                        <div class="mt-3 p-3"
                                                                                            style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                                                                            <label
                                                                                                class="form-label small fw-semibold">Answer
                                                                                                to create</label>
                                                                                            <div
                                                                                                class="d-flex gap-2 align-items-center">
                                                                                                <input type="number"
                                                                                                    class="form-control form-control-sm answer-count-input"
                                                                                                    min="1" max="10" value="1"
                                                                                                    placeholder="Enter number of answer to create"
                                                                                                    style="max-width: 300px;">
                                                                                                <button type="button"
                                                                                                    class="btn btn-sm btn-primary"
                                                                                                    data-action="add-multiple-answers"
                                                                                                    data-section="{{ $sectionIndex }}"
                                                                                                    data-group="{{ $groupIndex }}"
                                                                                                    data-question="{{ $qIndex }}">
                                                                                                    <i
                                                                                                        class="bi bi-plus-lg me-1"></i>Add
                                                                                                    Answer
                                                                                                </button>
                                                                                            </div>
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
                                            @endif
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
            (function () {
                'use strict';

                let sectionIndex = {{ count($skill->sections) }};
                const exams = @json($exams);
                const currentTestId = {{ $skill->exam_test_id }};
                const skillType = '{{ $skill->skill_type }}'; // Get current skill type

                // Event delegation for all button clicks
                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-action]');
                    if (!btn) return;

                    const action = btn.dataset.action;
                    const actions = {
                        'add-section': () => addSection(),
                        'delete-section': () => deleteItem(btn, '.section-item', 'section'),
                        'add-group': () => addQuestionGroup(btn),
                        'delete-group': () => deleteItem(btn, '.question-group-item', 'group'),
                        'add-question': () => addQuestion(btn),
                        'delete-question': () => deleteItem(btn, '.question-item', 'question'),
                        'add-direct-question': () => addDirectQuestion(btn),
                        'delete-direct-question': () => deleteItem(btn, '.direct-question-item', 'direct question'),
                        'delete-answer': () => deleteItem(btn, '.answer-item', 'answer'),
                        'move-answer-up': () => moveAnswer(btn, -1),
                        'move-answer-down': () => moveAnswer(btn, 1),
                        'add-multiple-answers': () => addMultipleAnswers(btn)
                    };

                    if (actions[action]) {
                        e.preventDefault();
                        actions[action]();
                    }
                });

                // Handle Bootstrap collapse icon rotation
                document.addEventListener('shown.bs.collapse', e => {
                    const btn = document.querySelector(`[data-bs-target="#${e.target.id}"] i`);
                    if (btn) btn.className = 'bi bi-chevron-down';
                });

                document.addEventListener('hidden.bs.collapse', e => {
                    const btn = document.querySelector(`[data-bs-target="#${e.target.id}"] i`);
                    if (btn) btn.className = 'bi bi-chevron-right';
                });

                // Question type change handler
                document.addEventListener('change', function (e) {
                    if (e.target.matches('.question-type-select')) {
                        const questionItem = e.target.closest('.question-item');
                        const optionsSection = questionItem?.querySelector('.question-options-section');
                        if (optionsSection) {
                            optionsSection.style.display = e.target.value === 'multiple_choice' ? 'block' : 'none';
                        }
                    }
                });

                // Initialize on DOM ready
                document.addEventListener('DOMContentLoaded', function () {
                    initExamSelect();
                    initSkillTypeSelect();
                    initializeAllEditors();
                });

                // Initialize exam select
                function initExamSelect() {
                    const examSelect = document.getElementById('exam_id');
                    const testSelect = document.getElementById('exam_test_id');

                    examSelect.addEventListener('change', function () {
                        testSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
                        testSelect.disabled = true;

                        if (!this.value) {
                            testSelect.innerHTML = '<option value="">-- Vui lòng chọn Quiz Collection trước --</option>';
                            return;
                        }

                        const selectedExam = exams.find(exam => exam.id == this.value);
                        if (selectedExam?.tests) {
                            testSelect.innerHTML = '<option value="">Chọn Quiz Group</option>';
                            selectedExam.tests.forEach(test => {
                                const opt = new Option(test.name, test.id, false, test.id == currentTestId);
                                testSelect.add(opt);
                            });
                            testSelect.disabled = false;
                        } else {
                            testSelect.innerHTML = '<option value="">-- Không có test nào --</option>';
                        }
                    });

                    examSelect.dispatchEvent(new Event('change'));
                }

                // Initialize skill type classes (select is disabled in edit mode)
                function initSkillTypeSelect() {
                    const skillTypeSelect = document.getElementById('skill_type');
                    const type = skillTypeSelect.value;
                    const container = document.getElementById('sectionsContainer');

                    // Just set container class once, no need for event listener since select is disabled
                    ['listening', 'reading', 'writing', 'speaking'].forEach(skill => {
                        container?.classList.toggle(`skill-type-${skill}`, type === skill);
                    });
                }

                // Generic delete item function
                function deleteItem(btn, selector, type) {
                    if (!confirm(`Are you sure you want to delete this ${type}?`)) return;

                    const item = btn.closest(selector);
                    const parent = item.parentElement;
                    item.remove();

                    // Update numbering based on type
                    if (selector === '.section-item') {
                        updateSectionNumbers();
                    } else if (selector === '.question-group-item') {
                        updateGroupNumbers(parent.closest('.section-item'));
                    } else if (selector === '.question-item') {
                        updateQuestionNumbers(parent.closest('.question-group-item'));
                    } else if (selector === '.direct-question-item') {
                        updateDirectQuestionNumbers(parent.closest('.section-item'));
                    }

                    updateNavigation();
                }

                // Move answer up or down
                function moveAnswer(btn, direction) {
                    const item = btn.closest('.answer-item');
                    const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;

                    if (sibling?.classList.contains('answer-item')) {
                        direction < 0 ?
                            item.parentNode.insertBefore(item, sibling) :
                            item.parentNode.insertBefore(sibling, item);
                    }
                }

                // Add answer (single or multiple)
                function addMultipleAnswers(btn) {
                    const { section, group, question } = btn.dataset;
                    const input = btn.closest('.answers-list-section')?.querySelector('.answer-count-input');
                    const count = input ? parseInt(input.value) || 1 : 1;

                    for (let i = 0; i < count; i++) {
                        addAnswer(btn, section, group, question);
                    }

                    if (input) input.value = 1;
                }

                // Add single answer
                function addAnswer(btn, sectionIdx, groupIdx, questionIdx) {
                    const answersList = btn.closest('.answers-list-section').querySelector('.answers-list');
                    const answerIndex = answersList.querySelectorAll('.answer-item').length;

                    // Detect question type to decide between dropdown or rich text for answer content
                    const questionItem = btn.closest('.question-item');
                    const qType = questionItem?.querySelector('.question-type-select')?.value || '';
                    const isYN = qType === 'yes_no_not_given';
                    const isTF = qType === 'true_false_not_given';

                    const dropdownHtml = () => {
                        const opts = isYN ? ['Yes','No','Not Given'] : ['True','False','Not Given'];
                        const optionsHTML = opts.map(opt => `<option value="${opt}" ${opt === (isYN ? 'Yes' : 'True') ? 'selected' : ''}>${opt}</option>`).join('');
                        return `
                            <select class="form-select form-select-sm" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][content]">
                                ${optionsHTML}
                            </select>
                        `;
                    };

                    const answerContentHtml = (isYN || isTF)
                        ? dropdownHtml()
                        : `
                            <input type="hidden" id="answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}"
                                   name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][content]">
                            <div id="answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}-editor" style="background: white;"></div>
                        `;

                    answersList.insertAdjacentHTML('beforeend', `
                    <div class="answer-item mb-3 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; position: relative;">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Answer Content</label>
                            ${answerContentHtml}
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Feedback</label>
                            <input type="hidden" id="answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}"
                                   name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][feedback]">
                            <div id="answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}-editor" style="background: white;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIdx}][answers][${answerIndex}][is_correct]" value="1">
                                <label class="form-check-label">Is correct</label>
                            </div>
                            <button type="button" class="btn btn-sm text-danger" data-action="delete-answer">Delete Answer</button>
                        </div>
                        <div class="position-absolute" style="top: 10px; left: -25px; display: flex; flex-direction: column; gap: 5px;">
                            <button type="button" class="btn btn-sm btn-light border" data-action="move-answer-up"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-sm btn-light border" data-action="move-answer-down"><i class="bi bi-chevron-down"></i></button>
                        </div>
                    </div>
                `);

                    // Initialize editors only when using rich text mode
                    if (!(isYN || isTF)) {
                        initQuillEditor(`answer-content-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}`);
                    }
                    initQuillEditor(`answer-feedback-new-${sectionIdx}-${groupIdx}-${questionIdx}-${answerIndex}`);
                }

                // Add section
                function addSection() {
                    const container = document.getElementById('sectionsContainer');
                    const isSpeakingOrWriting = (skillType === 'speaking' || skillType === 'writing');

                    // Determine which questions container to show
                    const questionsContainerHTML = isSpeakingOrWriting ? `
                        <div class="direct-questions-container mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Questions</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" data-action="add-direct-question">
                                    <i class="bi bi-plus me-1"></i>Add Question
                                </button>
                            </div>
                            <div class="direct-questions-list"></div>
                        </div>
                    ` : `
                        <div class="question-groups-container mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Question Groups</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="add-group">
                                    <i class="bi bi-plus me-1"></i>Add Question Group
                                </button>
                            </div>
                            <div class="groups-list"></div>
                        </div>
                    `;

                    container.insertAdjacentHTML('beforeend', `
                    <div class="section-item card mb-3" data-section-index="${sectionIndex}">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#section-new-${sectionIndex}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <strong>Section ${sectionIndex + 1}</strong>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-section">Delete Section</button>
                            </div>
                        </div>
                        <div class="card-body section-content collapse show" id="section-new-${sectionIndex}">
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
                            <div class="mb-3 skill-specific-field listening-field">
                                <label class="form-label">Audio File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="sections[${sectionIndex}][audio_file]" accept="audio/*">
                            </div>
                            <div class="mb-3 skill-specific-field listening-field">
                                <label class="form-label">Video File (Optional)</label>
                                <input type="file" class="form-control" name="sections[${sectionIndex}][video_file]" accept="video/*">
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIndex}][answer_inputs_inside_content]" value="1">
                                    <label class="form-check-label">Answer inputs inside content</label>
                                </div>
                            </div>
                            ${questionsContainerHTML}
                        </div>
                    </div>
                `);

                    initQuillEditor(`section-content-new-${sectionIndex}`);
                    initQuillEditor(`section-feedback-new-${sectionIndex}`);
                    sectionIndex++;
                    updateSectionNumbers();
                    updateNavigation();
                }

                function updateSectionNumbers() {
                    document.querySelectorAll('.section-item').forEach((section, newIndex) => {
                        const oldIndex = section.dataset.sectionIndex;

                        // Update section index
                        section.dataset.sectionIndex = newIndex;
                        section.id = `section-${newIndex}`;

                        // Update all name attributes within this section
                        section.querySelectorAll('[name]').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name && name.startsWith(`sections[${oldIndex}]`)) {
                                input.setAttribute('name', name.replace(`sections[${oldIndex}]`, `sections[${newIndex}]`));
                            }
                        });

                        // Update all id attributes for Quill editors
                        section.querySelectorAll('[id]').forEach(element => {
                            const id = element.getAttribute('id');
                            if (id && id.includes(`-${oldIndex}-`)) {
                                element.setAttribute('id', id.replace(`-${oldIndex}-`, `-${newIndex}-`));
                            }
                        });

                        // Update header text
                        const headerText = section.querySelector('.card-header strong');
                        if (headerText) {
                            headerText.textContent = `Section ${newIndex + 1}`;
                        }
                    });
                }

                // Add question group
                function addQuestionGroup(btn) {
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const groupsList = sectionItem.querySelector('.groups-list');
                    const groupIndex = groupsList.querySelectorAll('.question-group-item').length;

                    groupsList.insertAdjacentHTML('beforeend', `
                    <div class="question-group-item border rounded p-3 mb-3" data-group-index="${groupIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#group-new-${sectionIdx}-${groupIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question Group ${groupIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-group">Delete Question Group</button>
                        </div>
                        <div class="group-content collapse show" id="group-new-${sectionIdx}-${groupIndex}">
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
                                    <button type="button" class="btn btn-sm btn-outline-success" data-action="add-question">
                                        <i class="bi bi-plus me-1"></i>Add Question
                                    </button>
                                </div>
                                <div class="questions-list"></div>
                            </div>
                        </div>
                    </div>
                `);

                    initQuillEditor(`group-content-new-${sectionIdx}-${groupIndex}`);
                    updateGroupNumbers(sectionItem);
                    updateNavigation();
                }

                function updateGroupNumbers(sectionItem) {
                    sectionItem.querySelectorAll('.question-group-item').forEach((group, index) => {
                        group.querySelector('strong').textContent = `Question Group ${index + 1}`;
                    });
                }

                // Add question
                function addQuestion(btn) {
                    const groupItem = btn.closest('.question-group-item');
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const groupIdx = groupItem.dataset.groupIndex;
                    const questionsList = groupItem.querySelector('.questions-list');
                    const questionIndex = questionsList.querySelectorAll('.question-item').length;

                    questionsList.insertAdjacentHTML('beforeend', `
                    <div class="question-item bg-light p-3 rounded mb-2" data-question-index="${questionIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#question-new-${sectionIdx}-${groupIdx}-${questionIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question ${questionIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-question">Delete Question</button>
                        </div>
                        <div class="question-content collapse show" id="question-new-${sectionIdx}-${groupIdx}-${questionIndex}">
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
                                    <select class="form-select form-select-sm question-type-select" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][question_type]">
                                        <option value="">Chọn</option>
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="yes_no_not_given">Yes/No/Not Given</option>
                                        <option value="true_false_not_given">True/False/Not Given</option>
                                        <option value="short_text">Short Text</option>
                                        <option value="table_selection">Table Selection</option>
                                    </select>
                                </div>
                            </div>



                            <div class="mb-3 answers-list-section">
                                <div class="answers-list" data-question-id="${sectionIdx}-${groupIdx}-${questionIndex}">
                                    <!-- Answers will be added here -->
                                </div>

                                <!-- Answer to create Section -->
                                <div class="mt-3 p-3" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                    <label class="form-label small fw-semibold">Answer to create</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="number" class="form-control form-control-sm answer-count-input" min="1" max="10" value="1" placeholder="Enter number of answer to create" style="max-width: 300px;">
                                        <button type="button" class="btn btn-sm btn-primary" data-action="add-multiple-answers" data-section="${sectionIdx}" data-group="${groupIdx}" data-question="${questionIndex}">
                                            <i class="bi bi-plus-lg me-1"></i>Add Answer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 question-options-section" style="display: none;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][allow_multiple_selection]" value="1">
                                    <label class="form-check-label small">Allow multiple selection</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="sections[${sectionIdx}][groups][${groupIdx}][questions][${questionIndex}][count_each_correct]" value="1">
                                    <label class="form-check-label small">Count each correct answer as separate question</label>
                                </div>
                            </div>

                        </div>
                    </div>
                `);

                    initQuillEditor(`question-content-new-${sectionIdx}-${groupIdx}-${questionIndex}`);
                    updateQuestionNumbers(groupItem);
                    updateNavigation();
                }

                function updateQuestionNumbers(groupItem) {
                    groupItem.querySelectorAll('.question-item').forEach((question, index) => {
                        question.querySelector('strong').textContent = `Question ${index + 1}`;
                    });
                }

                // Add direct question (for speaking/writing)
                function addDirectQuestion(btn) {
                    const sectionItem = btn.closest('.section-item');
                    const sectionIdx = sectionItem.dataset.sectionIndex;
                    const questionsList = sectionItem.querySelector('.direct-questions-list');
                    const questionIndex = questionsList.querySelectorAll('.direct-question-item').length;

                    questionsList.insertAdjacentHTML('beforeend', `
                    <div class="direct-question-item bg-light p-3 rounded mb-3" data-question-index="${questionIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-link text-dark p-0" data-bs-toggle="collapse" data-bs-target="#direct-question-new-${sectionIdx}-${questionIndex}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <strong>Question ${questionIndex + 1}</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" data-action="delete-direct-question">Delete Question</button>
                        </div>
                        <div class="collapse show" id="direct-question-new-${sectionIdx}-${questionIndex}">
                            <div class="mb-3">
                                <label class="form-label">Question Content</label>
                                <input type="hidden" id="direct-question-content-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][content]">
                                <div id="direct-question-content-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Points</label>
                                <input type="number" step="0.01" class="form-control" name="sections[${sectionIdx}][direct_questions][${questionIndex}][point]" value="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sample Answer / Marking Criteria</label>
                                <input type="hidden" id="direct-answer-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][answer_content]">
                                <div id="direct-answer-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Feedback</label>
                                <input type="hidden" id="direct-feedback-new-${sectionIdx}-${questionIndex}" name="sections[${sectionIdx}][direct_questions][${questionIndex}][feedback]">
                                <div id="direct-feedback-new-${sectionIdx}-${questionIndex}-editor"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hint (Optional)</label>
                                <input type="text" class="form-control" name="sections[${sectionIdx}][direct_questions][${questionIndex}][hint]" placeholder="Enter hint for this question">
                            </div>
                        </div>
                    </div>
                `);

                    initQuillEditor(`direct-question-content-new-${sectionIdx}-${questionIndex}`);
                    initQuillEditor(`direct-answer-new-${sectionIdx}-${questionIndex}`);
                    initQuillEditor(`direct-feedback-new-${sectionIdx}-${questionIndex}`);
                    updateDirectQuestionNumbers(sectionItem);
                    updateNavigation();
                }

                function updateDirectQuestionNumbers(sectionItem) {
                    sectionItem.querySelectorAll('.direct-question-item').forEach((question, index) => {
                        question.querySelector('strong').textContent = `Question ${index + 1}`;
                    });
                }

                // Update navigation
                function updateNavigation() {
                    const navContainer = document.getElementById('sectionNavigation');
                    const sections = document.querySelectorAll('.section-item');

                    navContainer.innerHTML = Array.from(sections).map((section, sIndex) => {
                        section.id = `section-${sIndex}`;

                        // Check if section has direct questions (Speaking/Writing)
                        const directQuestions = section.querySelectorAll('.direct-question-item');
                        if (directQuestions.length > 0) {
                            const directQuestionsHtml = `<div class="ps-3">${Array.from(directQuestions).map((question, qIndex) => {
                                question.id = `direct-question-${sIndex}-${qIndex}`;
                                return `<a href="#direct-question-${sIndex}-${qIndex}" class="list-group-item list-group-item-action small text-muted py-1">
                                    <i class="bi bi-question-circle me-2"></i>Question ${qIndex + 1}
                                </a>`;
                            }).join('')}</div>`;

                            return `<div class="section-nav-item">
                                <a href="#section-${sIndex}" class="list-group-item list-group-item-action">
                                    <i class="bi bi-file-text me-2"></i>Section ${sIndex + 1}
                                </a>${directQuestionsHtml}
                            </div>`;
                        }

                        // Otherwise check for question groups (Listening/Reading)
                        const groups = section.querySelectorAll('.question-group-item');
                        const groupsHtml = groups.length > 0 ? `<div class="ps-3">${Array.from(groups).map((group, gIndex) => {
                            group.id = `group-${sIndex}-${gIndex}`;
                            const questions = group.querySelectorAll('.question-item');

                            const questionsHtml = questions.length > 0 ? `<div class="ps-3">${Array.from(questions).map((question, qIndex) => {
                                question.id = `question-${sIndex}-${gIndex}-${qIndex}`;
                                return `<a href="#question-${sIndex}-${gIndex}-${qIndex}" class="list-group-item list-group-item-action small text-muted py-1">
                                <i class="bi bi-question-circle me-2"></i>Question ${qIndex + 1}
                            </a>`;
                            }).join('')}</div>` : '';

                            return `<div>
                            <a href="#group-${sIndex}-${gIndex}" class="list-group-item list-group-item-action small">
                                <i class="bi bi-diagram-3 me-2"></i>Group ${gIndex + 1}
                            </a>${questionsHtml}
                        </div>`;
                        }).join('')}</div>` : '';

                        return `<div class="section-nav-item">
                        <a href="#section-${sIndex}" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-text me-2"></i>Section ${sIndex + 1}
                        </a>${groupsHtml}
                    </div>`;
                    }).join('');
                }

                // Initialize Quill editors
                function initializeAllEditors() {
                    const patterns = [
                        'section-content-',
                        'section-feedback-',
                        'group-content-',
                        'question-content-',
                        'answer-content-',
                        'answer-feedback-',
                        'direct-question-content-',
                        'direct-answer-',
                        'direct-feedback-'
                    ];
                    patterns.forEach(pattern => {
                        document.querySelectorAll(`[id^="${pattern}"][id$="-editor"]`).forEach(editorDiv => {
                            initQuillEditor(editorDiv.id.replace('-editor', ''));
                        });
                    });
                }

                function initQuillEditor(elementId) {
                    const hiddenInput = document.getElementById(elementId);
                    const editorDiv = document.getElementById(elementId + '-editor');

                    if (!editorDiv || editorDiv.classList.contains('ql-container')) return;

                    console.log('Initializing Quill for:', elementId, 'Value:', hiddenInput?.value?.substring(0, 100));

                    const quill = new Quill(`#${elementId}-editor`, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Nhập nội dung...'
                    });

                    if (hiddenInput?.value) {
                        // Decode HTML entities if the value contains escaped HTML
                        let decodedHtml = hiddenInput.value;
                        if (decodedHtml.includes('&lt;') || decodedHtml.includes('&gt;')) {
                            const tempDiv = document.createElement('textarea');
                            tempDiv.innerHTML = decodedHtml;
                            decodedHtml = tempDiv.value;
                            console.log('Decoded HTML entities for:', elementId);
                        }

                        // Set content with a small delay to prevent browser lag
                        setTimeout(() => {
                            quill.root.innerHTML = decodedHtml;
                            console.log('Set content for:', elementId, 'Length:', decodedHtml.length);
                        }, 10);
                    } else {
                        console.log('No value for:', elementId);
                    }

                    quill.on('text-change', () => {
                        if (hiddenInput) hiddenInput.value = quill.root.innerHTML;
                    });

                    return quill;
                }

            })();
        </script>
    @endpush
@endsection
