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
        <form action="{{ route('admin.skills.update', $skill) }}" method="POST" id="skillForm"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-12">
                    <div id="quiz-info" class="card mb-4">
                        <div class="card-body">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Quiz Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $skill->name) }}"
                                    placeholder="Enter quiz title" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Quiz Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="Enter quiz description ">{{ old('description', $skill->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Chấp nhận: JPG, PNG, GIF. Tối đa: 2MB</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Current Image -->
                                @if ($skill->image)
                                    <div class="mt-2">
                                        <label class="form-label">Hình ảnh hiện tại:</label>
                                        <div id="currentImage">
                                            <img src="{{ asset('storage/' . $skill->image) }}" alt="Current image"
                                                class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                            <div class="mt-1">
                                                <small class="text-muted">{{ basename($skill->image) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Image Preview -->
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <label class="form-label">Xem trước hình mới:</label>
                                    <div>
                                        <img src="" alt="Preview" class="img-thumbnail"
                                            style="max-width: 200px; max-height: 200px;">
                                    </div>
                                </div>
                            </div>

                            @if ($skill->isListening())
                                <!-- Shared Audio File -->
                                <div class="mb-3">
                                    <label for="audio_file" class="form-label">Audio File dùng chung</label>
                                    <input type="file" class="form-control @error('audio_file') is-invalid @enderror"
                                        id="audio_file" name="audio_file" accept="audio/*">
                                    <small class="form-text text-muted">
                                        Một file nghe áp dụng cho toàn bộ sections của quiz Listening này.
                                    </small>
                                    @error('audio_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if ($skill->audio_file)
                                        @php
                                            $skillAudioPath = preg_replace('#^/?storage/#', '', $skill->audio_file);
                                            $skillAudioUrl = url(
                                                '/api/public/media/' .
                                                    collect(explode('/', ltrim($skillAudioPath, '/')))
                                                        ->map(fn($segment) => rawurlencode($segment))
                                                        ->implode('/'),
                                            );
                                        @endphp
                                        <div class="mt-2">
                                            <label class="form-label">Audio hiện tại:</label>
                                            <div>
                                                <small class="text-muted">{{ basename($skill->audio_file) }}</small>
                                                <audio controls class="w-100 mt-1">
                                                    <source src="{{ $skillAudioUrl }}">
                                                </audio>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Time Limit -->
                            <div class="mb-3">
                                <label for="time_limit" class="form-label">Time Limit (phút) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                    id="time_limit" name="time_limit"
                                    value="{{ old('time_limit', $skill->time_limit) }}" min="1"
                                    placeholder="Nhập thời gian (phút)" required>
                                <small class="form-text text-muted">Thời gian làm bài tính theo phút</small>
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
                                    <option value="reading"
                                        {{ old('skill_type', $skill->skill_type) == 'reading' ? 'selected' : '' }}>
                                        Reading (Đọc)
                                    </option>
                                    <option value="writing"
                                        {{ old('skill_type', $skill->skill_type) == 'writing' ? 'selected' : '' }}>
                                        Writing (Viết)
                                    </option>
                                    <option value="listening"
                                        {{ old('skill_type', $skill->skill_type) == 'listening' ? 'selected' : '' }}>
                                        Listening (Nghe)
                                    </option>
                                    <option value="speaking"
                                        {{ old('skill_type', $skill->skill_type) == 'speaking' ? 'selected' : '' }}>
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

                            <!-- Is Online -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_online" name="is_online"
                                        value="1" {{ old('is_online', $skill->is_online) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_online">
                                        Online Mode
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
                                    @foreach ($exams as $exam)
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
                                <label for="exam_test_id" class="form-label">Quiz Group <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('exam_test_id') is-invalid @enderror"
                                    id="exam_test_id" name="exam_test_id" required>
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
                <div class="col-lg-3 col-12 quiz-navigation-col">
                    <div class="card quiz-navigation-card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Navigation</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="sectionNavigation" class="list-group list-group-flush">

                                @foreach ($skill->sections as $index => $section)
                                    <div class="section-nav-item">
                                        <a href="#section-{{ $index }}"
                                            class="list-group-item list-group-item-action">
                                            <i class="bi bi-file-text me-2"></i>Section {{ $index + 1 }}
                                        </a>

                                        @if ($skill->isSpeaking() || $skill->isWriting())
                                            {{-- Speaking/Writing: Direct questions without groups --}}
                                            @if ($section->questions->count() > 0)
                                                <div class="ps-3">
                                                    @foreach ($section->questions as $qIndex => $question)
                                                        <a href="#direct-question-{{ $index }}-{{ $qIndex }}"
                                                            class="list-group-item list-group-item-action small text-muted py-1">
                                                            <i class="bi bi-question-circle me-2"></i>Question
                                                            {{ $qIndex + 1 }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            {{-- Listening/Reading: Questions grouped --}}
                                            @if ($section->questionGroups->count() > 0)
                                                <div class="ps-3">
                                                    @foreach ($section->questionGroups as $gIndex => $group)
                                                        <div>
                                                            <a href="#group-{{ $index }}-{{ $gIndex }}"
                                                                class="list-group-item list-group-item-action small">
                                                                <i class="bi bi-diagram-3 me-2"></i>Group
                                                                {{ $gIndex + 1 }}
                                                            </a>
                                                            @if ($group->questions->count() > 0)
                                                                <div class="ps-3">
                                                                    @foreach ($group->questions as $qIndex => $question)
                                                                        <a href="#question-{{ $index }}-{{ $gIndex }}-{{ $qIndex }}"
                                                                            class="list-group-item list-group-item-action small text-muted py-1">
                                                                            <i
                                                                                class="bi bi-question-circle me-2"></i>Question
                                                                            {{ $qIndex + 1 }}
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
                        <div class="card-header bg-light sections-builder-sticky-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Sections & Questions Builder
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-action="add-section">
                                    <i class="bi bi-plus-circle me-1"></i>Add Section
                                </button>
                            </div>
                        </div>
                        <div class="card-body sections-builder-scroll-body">
                            <div id="sectionsContainer" class="skill-type-{{ $skill->skill_type }}">

                                @foreach ($skill->sections as $sectionIndex => $section)
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
                                        <div class="card-body section-content collapse show exam-filter-sidebar"
                                            id="section-body-{{ $sectionIndex }}">
                                            <input type="hidden" name="sections[{{ $sectionIndex }}][id]"
                                                value="{{ $section->id }}">

                                            @php
                                                $selectedFilterIds = $section->filters->pluck('id')->toArray();
                                            @endphp

                                            @php
                                                $skillSlug = Str::slug($skill->skill_type);
                                                $skillFilter = $skillFilters[$skillSlug] ?? null;
                                            @endphp

                                            @if ($skillFilter)
                                                @foreach ($skillFilter->children as $group)
                                                    <div class="filter-group mb-3 d-none"
                                                        data-group-type="{{ Str::slug($group->name) }}">
                                                        <strong>{{ $group->name }}</strong>

                                                        @foreach ($group->children as $value)
                                                            <div class="form-check">
                                                                <input class="form-check-input exam-filter-input"
                                                                    type="checkbox" data-skill="{{ $skillSlug }}"
                                                                    data-group="old_section_{{ $sectionIndex }}{{ $group->id }}"
                                                                    value="{{ $value->id }}"
                                                                    name="sections[{{ $sectionIndex }}][exam_filters][]"
                                                                    {{ in_array($value->id, $selectedFilterIds) ? 'checked' : '' }}>

                                                                <label class="form-check-label">
                                                                    {{ $value->name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            @endif
                                            <!-- Section Title -->
                                            <div class="mb-3">
                                                <label class="form-label">Section Title</label>
                                                <input type="text" class="form-control"
                                                    name="sections[{{ $sectionIndex }}][title]"
                                                    value="{{ old('sections.' . $sectionIndex . '.title', $section->title) }}"
                                                    placeholder="Enter section title">
                                            </div>

                                            @if ($skill->isListening())
                                                <!-- Section Audio File -->
                                                <div class="mb-3">
                                                    <label class="form-label">Audio File riêng cho section</label>
                                                    <input type="file"
                                                        class="form-control @error('sections.' . $sectionIndex . '.audio_file') is-invalid @enderror"
                                                        name="sections[{{ $sectionIndex }}][audio_file]"
                                                        accept="audio/*">
                                                    <small class="form-text text-muted">
                                                        Nếu để trống, section này sẽ dùng audio chung của skill.
                                                    </small>
                                                    @error('sections.' . $sectionIndex . '.audio_file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror

                                                    @if ($section->audio_file)
                                                        @php
                                                            $sectionAudioPath = preg_replace('#^/?storage/#', '', $section->audio_file);
                                                            $sectionAudioUrl = url(
                                                                '/api/public/media/' .
                                                                    collect(explode('/', ltrim($sectionAudioPath, '/')))
                                                                        ->map(fn($segment) => rawurlencode($segment))
                                                                        ->implode('/'),
                                                            );
                                                        @endphp
                                                        <div class="mt-2">
                                                            <label class="form-label">Audio section hiện tại:</label>
                                                            <div>
                                                                <small class="text-muted">{{ basename($section->audio_file) }}</small>
                                                                <audio controls class="w-100 mt-1">
                                                                    <source src="{{ $sectionAudioUrl }}">
                                                                </audio>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

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

                                            @if ($skill->isSpeaking() || $skill->isWriting())
                                                <!-- Direct Questions (for Speaking/Writing) -->
                                                <div class="direct-questions-container mt-4">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6>Questions</h6>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            data-action="add-direct-question">
                                                            <i class="bi bi-plus me-1"></i>Add Question
                                                        </button>
                                                    </div>

                                                    <div class="direct-questions-list">
                                                        @foreach ($section->questions as $qIndex => $question)
                                                            <div id="direct-question-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                class="direct-question-item bg-light p-3 rounded mb-3"
                                                                data-question-index="{{ $qIndex }}">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-start mb-2">
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

                                                                <div class="collapse show">
                                                                    <input type="hidden"
                                                                        name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][id]"
                                                                        value="{{ $question->id }}">

                                                                    <!-- Question Content -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Question
                                                                            Content</label>
                                                                        <input type="hidden"
                                                                            id="direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][content]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.content', $question->content) }}">
                                                                        <div
                                                                            id="direct-question-content-{{ $sectionIndex }}-{{ $qIndex }}-editor">
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
                                                                        <label class="form-label">Sample Answer /
                                                                            Marking Criteria</label>
                                                                        <input type="hidden"
                                                                            id="direct-answer-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][answer_content]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.answer_content', $question->answer_content) }}">
                                                                        <div
                                                                            id="direct-answer-{{ $sectionIndex }}-{{ $qIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Feedback -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Feedback</label>
                                                                        <input type="hidden"
                                                                            id="direct-feedback-{{ $sectionIndex }}-{{ $qIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][direct_questions][{{ $qIndex }}][feedback]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.direct_questions.' . $qIndex . '.feedback', $question->feedback) }}">
                                                                        <div
                                                                            id="direct-feedback-{{ $sectionIndex }}-{{ $qIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Hint -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Hint
                                                                            (Optional)
                                                                        </label>
                                                                        <input type="text" class="form-control"
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
                                                    <div
                                                        class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6>Question Groups</h6>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary"
                                                            data-action="add-group">
                                                            <i class="bi bi-plus me-1"></i>Add Question Group
                                                        </button>
                                                    </div>

                                                    <div class="groups-list">
                                                        @foreach ($section->questionGroups as $groupIndex => $group)
                                                            <div id="group-{{ $sectionIndex }}-{{ $groupIndex }}"
                                                                class="question-group-item border rounded p-3 mb-3"
                                                                data-group-index="{{ $groupIndex }}">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-3">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-link text-dark p-0"
                                                                            data-bs-toggle="collapse"
                                                                            data-bs-target="#group-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}">
                                                                            <i class="bi bi-chevron-down"></i>
                                                                        </button>
                                                                        <strong>Question Group
                                                                            {{ $groupIndex + 1 }}</strong>
                                                                    </div>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-link text-danger"
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
                                                                        <label class="form-label">Question Group
                                                                            Content</label>
                                                                        <input type="hidden"
                                                                            id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][content]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.content', $group->content) }}">
                                                                        <div
                                                                            id="group-content-{{ $sectionIndex }}-{{ $groupIndex }}-editor">
                                                                        </div>
                                                                    </div>

                                                                    <!-- Question Group Instructions -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Question Group
                                                                            Instructions</label>
                                                                        <input type="hidden"
                                                                            id="group-instructions-{{ $sectionIndex }}-{{ $groupIndex }}"
                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][instructions]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.instructions', $group->instructions) }}">
                                                                        <div
                                                                            id="group-instructions-{{ $sectionIndex }}-{{ $groupIndex }}-editor">
                                                                        </div>
                                                                        <small class="form-text text-muted">
                                                                            Example: Questions 7 - 10, Complete the
                                                                            notes below, Write ONE WORD ONLY...
                                                                        </small>
                                                                    </div>

                                                                    <!-- Question Type -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Question Type
                                                                            cha</label>
                                                                        <select
                                                                            class="form-select group-question-type-select"
                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][question_type]">
                                                                            @php
                                                                                $selectedGroupQuestionType = old(
                                                                                    'sections.' .
                                                                                        $sectionIndex .
                                                                                        '.groups.' .
                                                                                        $groupIndex .
                                                                                        '.question_type',
                                                                                    $group->question_type,
                                                                                );
                                                                                $groupQuestionTypeOptions = $questionTypeOptions;

                                                                                if (
                                                                                    $selectedGroupQuestionType &&
                                                                                    !array_key_exists(
                                                                                        $selectedGroupQuestionType,
                                                                                        $groupQuestionTypeOptions,
                                                                                    )
                                                                                ) {
                                                                                    $groupQuestionTypeOptions =
                                                                                        [
                                                                                            $selectedGroupQuestionType =>
                                                                                                $questionTypeLabels[
                                                                                                    $selectedGroupQuestionType
                                                                                                ] ??
                                                                                                $selectedGroupQuestionType,
                                                                                        ] + $groupQuestionTypeOptions;
                                                                                }
                                                                            @endphp
                                                                            @foreach ($groupQuestionTypeOptions as $value => $label)
                                                                                <option value="{{ $value }}"
                                                                                    {{ $selectedGroupQuestionType == $value ? 'selected' : '' }}>
                                                                                    {{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <!-- Number of Options (for Table Selection) -->
                                                                    <div class="mb-3 table-selection-options"
                                                                        style="display: {{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.question_type', $group->question_type) == 'table_selection' ? 'block' : 'none' }};">
                                                                        <label class="form-label">Number of
                                                                            Options</label>
                                                                        <input type="number" class="form-control"
                                                                            name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][number_of_options]"
                                                                            value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.number_of_options', $group->options['number_of_options'] ?? 4) }}"
                                                                            min="2" max="10"
                                                                            placeholder="Enter number of options (e.g., 4)">
                                                                        <small class="form-text text-muted">Number of
                                                                            dropdown options for table selection
                                                                            questions</small>
                                                                    </div>

                                                                    <!-- Questions -->
                                                                    <div class="questions-container mt-3">
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                                            <h6 class="mb-0">Questions</h6>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-success"
                                                                                data-action="add-question">
                                                                                <i class="bi bi-plus me-1"></i>Add
                                                                                Question
                                                                            </button>
                                                                        </div>

                                                                        <div class="questions-list">
                                                                            @foreach ($group->questions as $qIndex => $question)
                                                                                <div id="question-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}"
                                                                                    class="question-item bg-light p-3 rounded mb-2"
                                                                                    data-question-index="{{ $qIndex }}">
                                                                                    <div
                                                                                        class="d-flex justify-content-between align-items-start mb-2">
                                                                                        <div
                                                                                            class="d-flex align-items-center gap-2">
                                                                                            <button type="button"
                                                                                                class="btn btn-sm btn-link text-dark p-0"
                                                                                                data-bs-toggle="collapse"
                                                                                                data-bs-target="#question-content-collapse-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}">
                                                                                                <i
                                                                                                    class="bi bi-chevron-down"></i>
                                                                                            </button>
                                                                                            <strong>Question
                                                                                                {{ $qIndex + 1 }}</strong>
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
                                                                                            <label
                                                                                                class="form-label small">Question
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
                                                                                                <input type="number"
                                                                                                    step="0.01"
                                                                                                    class="form-control form-control-sm"
                                                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][point]"
                                                                                                    value="{{ old('sections.' . $sectionIndex . '.groups.' . $groupIndex . '.questions.' . $qIndex . '.point', $question->point ?? 1) }}">
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <label
                                                                                                    class="form-label small">Question
                                                                                                    Type</label>
                                                                                                <select
                                                                                                    class="form-select form-select-sm question-type-select"
                                                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][question_type]">
                                                                                                    <option
                                                                                                        value="">
                                                                                                        Chọn</option>
                                                                                                    @php
                                                                                                        $selectedQuestionType = old(
                                                                                                            'sections.' .
                                                                                                                $sectionIndex .
                                                                                                                '.groups.' .
                                                                                                                $groupIndex .
                                                                                                                '.questions.' .
                                                                                                                $qIndex .
                                                                                                                '.question_type',
                                                                                                            $question
                                                                                                                ->metadata[
                                                                                                                'question_type'
                                                                                                            ] ?? '',
                                                                                                        );
                                                                                                        $questionOptions = $questionTypeOptions;

                                                                                                        if (
                                                                                                            $selectedQuestionType &&
                                                                                                            !array_key_exists(
                                                                                                                $selectedQuestionType,
                                                                                                                $questionOptions,
                                                                                                            )
                                                                                                        ) {
                                                                                                            $questionOptions =
                                                                                                                [
                                                                                                                    $selectedQuestionType =>
                                                                                                                        $questionTypeLabels[
                                                                                                                            $selectedQuestionType
                                                                                                                        ] ??
                                                                                                                        $selectedQuestionType,
                                                                                                                ] +
                                                                                                                $questionOptions;
                                                                                                        }
                                                                                                    @endphp
                                                                                                    @foreach ($questionOptions as $value => $label)
                                                                                                        <option
                                                                                                            value="{{ $value }}"
                                                                                                            {{ $selectedQuestionType == $value ? 'selected' : '' }}>
                                                                                                            {{ $label }}
                                                                                                        </option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>

                                                                                        <!-- Answers List Section -->
                                                                                        <div
                                                                                            class="mb-3 answers-list-section">
                                                                                            <div class="answers-list"
                                                                                                data-question-id="{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}">
                                                                                                @php
                                                                                                    $answers =
                                                                                                        $question
                                                                                                            ->metadata[
                                                                                                            'answers'
                                                                                                        ] ?? [];
                                                                                                    // If old answer_content exists, convert to new format
                                                                                                    if (
                                                                                                        empty(
                                                                                                            $answers
                                                                                                        ) &&
                                                                                                        !empty(
                                                                                                            $question->answer_content
                                                                                                        )
                                                                                                    ) {
                                                                                                        $answers = [
                                                                                                            [
                                                                                                                'content' =>
                                                                                                                    $question->answer_content,
                                                                                                                'is_correct' => true,
                                                                                                            ],
                                                                                                        ];
                                                                                                    }
                                                                                                @endphp
                                                                                                @foreach ($answers as $ansIndex => $answer)
                                                                                                    <div class="answer-item mb-3 p-3"
                                                                                                        style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; position: relative;">
                                                                                                        <!-- Answer Content: dropdown for Yes/No/Not Given and True/False/Not Given; otherwise rich text -->
                                                                                                        @php
                                                                                                            $qType = old(
                                                                                                                'sections.' .
                                                                                                                    $sectionIndex .
                                                                                                                    '.groups.' .
                                                                                                                    $groupIndex .
                                                                                                                    '.questions.' .
                                                                                                                    $qIndex .
                                                                                                                    '.question_type',
                                                                                                                $question
                                                                                                                    ->metadata[
                                                                                                                    'question_type'
                                                                                                                ] ?? '',
                                                                                                            );
                                                                                                            $isYN =
                                                                                                                $qType ===
                                                                                                                'yes_no_not_given';
                                                                                                            $isTF =
                                                                                                                $qType ===
                                                                                                                'true_false_not_given';
                                                                                                        @endphp
                                                                                                        <div
                                                                                                            class="mb-3">
                                                                                                            <label
                                                                                                                class="form-label small fw-semibold">Answer
                                                                                                                Content</label>
                                                                                                            @if ($isYN || $isTF)
                                                                                                                @php
                                                                                                                    $options = $isYN
                                                                                                                        ? [
                                                                                                                            'Yes',
                                                                                                                            'No',
                                                                                                                            'Not Given',
                                                                                                                        ]
                                                                                                                        : [
                                                                                                                            'True',
                                                                                                                            'False',
                                                                                                                            'Not Given',
                                                                                                                        ];
                                                                                                                    $defaultVal = $isYN
                                                                                                                        ? 'Yes'
                                                                                                                        : 'True';
                                                                                                                    $selectedVal = old(
                                                                                                                        'sections.' .
                                                                                                                            $sectionIndex .
                                                                                                                            '.groups.' .
                                                                                                                            $groupIndex .
                                                                                                                            '.questions.' .
                                                                                                                            $qIndex .
                                                                                                                            '.answers.' .
                                                                                                                            $ansIndex .
                                                                                                                            '.content',
                                                                                                                        $answer[
                                                                                                                            'content'
                                                                                                                        ] ??
                                                                                                                            $defaultVal,
                                                                                                                    );
                                                                                                                @endphp
                                                                                                                <select
                                                                                                                    class="form-select form-select-sm"
                                                                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][content]">
                                                                                                                    @foreach ($options as $opt)
                                                                                                                        <option
                                                                                                                            value="{{ $opt }}"
                                                                                                                            {{ $selectedVal === $opt ? 'selected' : '' }}>
                                                                                                                            {{ $opt }}
                                                                                                                        </option>
                                                                                                                    @endforeach
                                                                                                                </select>
                                                                                                            @else
                                                                                                                <input
                                                                                                                    type="hidden"
                                                                                                                    id="answer-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}"
                                                                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][content]"
                                                                                                                    value="{{ $answer['content'] ?? '' }}">
                                                                                                                <div id="answer-content-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}-editor"
                                                                                                                    style="background: white;">
                                                                                                                </div>
                                                                                                            @endif
                                                                                                        </div>

                                                                                                        <!-- Feedback with Rich Text Editor -->
                                                                                                        <div
                                                                                                            class="mb-3">
                                                                                                            <label
                                                                                                                class="form-label small fw-semibold">Feedback</label>
                                                                                                            <input
                                                                                                                type="hidden"
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
                                                                                                            <div
                                                                                                                class="form-check">
                                                                                                                <input
                                                                                                                    class="form-check-input"
                                                                                                                    type="checkbox"
                                                                                                                    id="answer-correct-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}"
                                                                                                                    name="sections[{{ $sectionIndex }}][groups][{{ $groupIndex }}][questions][{{ $qIndex }}][answers][{{ $ansIndex }}][is_correct]"
                                                                                                                    value="1"
                                                                                                                    {{ $answer['is_correct'] ?? false ? 'checked' : '' }}>
                                                                                                                <label
                                                                                                                    class="form-check-label"
                                                                                                                    for="answer-correct-{{ $sectionIndex }}-{{ $groupIndex }}-{{ $qIndex }}-{{ $ansIndex }}">
                                                                                                                    Is
                                                                                                                    correct
                                                                                                                </label>
                                                                                                            </div>
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn btn-sm text-danger"
                                                                                                                data-action="delete-answer"
                                                                                                                style="text-decoration: none;">
                                                                                                                Delete
                                                                                                                Answer
                                                                                                            </button>
                                                                                                        </div>

                                                                                                        <!-- Move Up/Down Arrows -->
                                                                                                        <div class="position-absolute"
                                                                                                            style="top: 10px; left: -25px; display: flex; flex-direction: column; gap: 5px;">
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn btn-sm btn-light border"
                                                                                                                data-action="move-answer-up"
                                                                                                                style="padding: 2px 8px;">
                                                                                                                <i
                                                                                                                    class="bi bi-chevron-up"></i>
                                                                                                            </button>
                                                                                                            <button
                                                                                                                type="button"
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
                                                                                                    <input
                                                                                                        type="number"
                                                                                                        class="form-control form-control-sm answer-count-input"
                                                                                                        min="1"
                                                                                                        max="10"
                                                                                                        value="1"
                                                                                                        placeholder="Enter number of answer to create"
                                                                                                        style="max-width: 300px;">
                                                                                                    <button
                                                                                                        type="button"
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
    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" form="skillForm" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Cập nhật
                        </button>
                    </div>
    </div>
