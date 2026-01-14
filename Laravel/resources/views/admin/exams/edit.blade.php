@extends('layouts.app')

@section('content')
    <div class="container-fluid p-4">
        <form action="{{ route('admin.exams.update', $exam) }}" method="POST" enctype="multipart/form-data" id="examForm">
            @csrf
            @method('PUT')

            <!-- Header with Tabs -->
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

            <!-- Main Content Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <!-- Image Upload -->
                            @if ($exam->image)
                                <div class="mb-3">
                                    <label class="form-label">Ảnh bìa hiện tại</label>
                                    <div class="position-relative" style="max-width: 300px;">
                                        <img src="{{ Storage::url($exam->image) }}" alt="{{ $exam->name }}"
                                            class="img-thumbnail w-100" id="currentImage">
                                        <button type="button"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                            onclick="removeCurrentImage()" style="z-index: 10;">
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
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*" onchange="ImagePreview.show(this)">
                                <small class="form-text text-muted">
                                    Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB.
                                </small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Image Preview (for new uploads) -->
                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <label class="form-label">Xem trước:</label>
                                    <div class="position-relative" style="max-width: 300px;">
                                        <img id="preview" src="" alt="Preview" class="img-thumbnail w-100">
                                        <button type="button"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                            onclick="ImagePreview.remove()" style="z-index: 10;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold">Tiêu đề</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $exam->name) }}"
                                    placeholder="Nhập tiêu đề ..." required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold">Mô tả</label>
                                <div id="description-editor"></div>
                                <textarea class="form-control d-none @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $exam->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div class="mb-4">
                                <label for="type" class="form-label fw-semibold">Chương trình học</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type"
                                    name="type" required>
                                    <option value="">-- Chọn chương trình --</option>
                                    <option value="ielts" {{ old('type', $exam->type) == 'ielts' ? 'selected' : '' }}>
                                        IELTS</option>
                                    <option value="toeic" {{ old('type', $exam->type) == 'toeic' ? 'selected' : '' }}>
                                        TOEIC</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Collection -->
                            <div class="mb-4">
                                <label for="collection" class="form-label fw-semibold">
                                    Bộ đề <span class="text-danger">*</span>
                                </label>

                                <select class="form-select @error('exam_collection_id') is-invalid @enderror"
                                    id="collection" name="exam_collection_id" required>
                                    <option value="">-- Chọn bộ đề --</option>

                                    @foreach ($collections as $collection)
                                        <option value="{{ $collection->id }}" data-type="{{ $collection->type }}"
                                            {{ old('exam_collection_id', $exam->exam_collection_id) == $collection->id ? 'selected' : '' }}>
                                            {{ $collection->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('collection')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const typeSelect = document.getElementById('type');
                                    const collectionSelect = document.getElementById('collection');
                                    const options = Array.from(collectionSelect.options);

                                    function filterCollections() {
                                        const type = typeSelect.value;

                                        options.forEach(option => {
                                            if (!option.value) return;

                                            const optionType = option.dataset.type;
                                            option.hidden = type && optionType !== type;
                                        });

                                        // nếu collection đang chọn không hợp lệ → reset
                                        if (collectionSelect.selectedOptions.length) {
                                            const selected = collectionSelect.selectedOptions[0];
                                            if (type && selected.dataset.type !== type) {
                                                collectionSelect.value = '';
                                            }
                                        }
                                    }

                                    // khi đổi type
                                    typeSelect.addEventListener('change', filterCollections);

                                    // khi đổi collection → sync lại type
                                    collectionSelect.addEventListener('change', function() {
                                        const selected = collectionSelect.selectedOptions[0];
                                        if (selected?.dataset.type) {
                                            typeSelect.value = selected.dataset.type;
                                            filterCollections();
                                        }
                                    });

                                    // init lúc load edit
                                    filterCollections();
                                });
                            </script>

                            <!-- Level -->
                            <div class="mb-3">
                                <label for="level" class="form-label fw-semibold">
                                    Mức độ <span class="text-danger">*</span>
                                </label>

                                <select class="form-select @error('level') is-invalid @enderror" id="level"
                                    name="level" required>
                                    <option value="easy"
                                        {{ old('level', $exam->level ?? 'medium') == 'easy' ? 'selected' : '' }}>
                                        Dễ
                                    </option>
                                    <option value="medium"
                                        {{ old('level', $exam->level ?? 'medium') == 'medium' ? 'selected' : '' }}>
                                        Vừa
                                    </option>
                                    <option value="hard"
                                        {{ old('level', $exam->level ?? 'medium') == 'hard' ? 'selected' : '' }}>
                                        Khó
                                    </option>
                                </select>

                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>



                            <!-- Is Active -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $exam->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Hiển thị trên website
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Lưu
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tests List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Nhóm đề thi</h5>

                            @forelse($exam->tests as $test)
                                <div class="card mb-3 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <h6 class="mb-0 fw-bold">{{ $test->name }}</h6>
                                                    <button type="button"
                                                        class="btn btn-link btn-sm p-0 text-decoration-none"
                                                        onclick="openEditTestModal({{ $test->id }}, '{{ $test->name }}', '{{ addslashes($test->description ?? '') }}', '{{ $test->image ? Storage::url($test->image) : '' }}')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-link btn-sm p-0 text-decoration-none text-danger"
                                                        onclick="deleteTest({{ $test->id }}, '{{ $test->name }}')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                @if ($test->description)
                                                    <p class="text-muted small mb-0">
                                                        {{ Str::limit($test->description, 100) }}</p>
                                                @else
                                                    <p class="text-muted small mb-0">Không tồn tại đề kiểm tra</p>
                                                @endif
                                            </div>

                                        </div>

                                        <!-- Skills List -->
                                        @if ($test->skills->count() > 0)
                                            <div class="border-top pt-3">
                                                @foreach ($test->skills as $skill)
                                                    <div
                                                        class="d-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded">
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if ($skill->skill_type == 'reading')
                                                                <i class="bi bi-book text-primary"></i>
                                                            @elseif($skill->skill_type == 'writing')
                                                                <i class="bi bi-pencil-square text-success"></i>
                                                            @elseif($skill->skill_type == 'listening')
                                                                <i class="bi bi-headphones text-info"></i>
                                                            @else
                                                                <i class="bi bi-mic text-warning"></i>
                                                            @endif
                                                            <span class="fw-semibold">{{ $skill->name }}</span>
                                                            <span class="text-muted small">- {{ $skill->time_limit }}
                                                                phút</span>
                                                        </div>
                                                        <a href="{{ route('admin.skills.edit', $skill) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            Chỉnh sửa <i class="bi bi-pencil ms-1"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            <!-- Add Test Button -->
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#createTestModal">
                                <i class="bi bi-plus-circle me-2"></i>Tạo nhóm đề
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Include Create Test Modal -->
    @include('admin.exams.tests._create_modal')

    <!-- Include Edit Test Modal -->
    @include('admin.exams.tests._edit_modal')

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
        <style>
            .ql-container,
            .ql-editor {
                min-height: 200px;
                font-size: 14px;
            }

            .object-fit-cover {
                object-fit: cover;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
        <script src="{{ asset('assets/js/admin-editor.js') }}"></script>
        <script>
            let editTestQuill = null;

            function removeCurrentImage() {
                const currentImageDiv = document.getElementById('currentImage').parentElement;
                const removeFlag = document.getElementById('removeImageFlag');

                if (confirm('Bạn có chắc muốn xóa ảnh bìa hiện tại?')) {
                    currentImageDiv.style.display = 'none';
                    removeFlag.value = '1';
                }
            }

            function removeEditCurrentImage() {
                const wrapper = document.getElementById('edit-current-image-wrapper');
                const removeFlag = document.getElementById('edit-remove-image-flag');

                if (confirm('Bạn có chắc muốn xóa ảnh bìa hiện tại?')) {
                    wrapper.style.display = 'none';
                    removeFlag.value = '1';
                    document.getElementById('edit-image-label').textContent = 'Ảnh bìa';
                }
            }

            function openEditTestModal(testId, testName, testDescription, testImage) {
                // Set form action
                const form = document.getElementById('editTestForm');
                form.action = `/admin/exams/{{ $exam->id }}/tests/${testId}`;

                // Set name
                document.getElementById('edit-test-name').value = testName;

                // Set description in Quill editor
                if (editTestQuill) {
                    editTestQuill.root.innerHTML = testDescription || '';
                }

                // Handle current image
                const imageWrapper = document.getElementById('edit-current-image-wrapper');
                const currentImage = document.getElementById('edit-current-image');
                const removeFlag = document.getElementById('edit-remove-image-flag');
                const imageLabel = document.getElementById('edit-image-label');

                removeFlag.value = '0';

                if (testImage) {
                    currentImage.src = testImage;
                    imageWrapper.style.display = 'block';
                    imageLabel.textContent = 'Thay đổi ảnh bìa';
                } else {
                    imageWrapper.style.display = 'none';
                    imageLabel.textContent = 'Ảnh bìa';
                }

                // Clear new image preview
                ImagePreview.remove('edit-test-image', 'editTestImagePreview');

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editTestModal'));
                modal.show();
            }

            function deleteTest(testId, testName) {
                if (confirm(`Bạn có chắc muốn xóa nhóm đề "${testName}"?\nHành động này không thể hoàn tác!`)) {
                    // Create a form to submit DELETE request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/exams/{{ $exam->id }}/tests/${testId}`;

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            // Initialize modal test description editor
            document.addEventListener('DOMContentLoaded', function() {
                QuillEditor.init('#modal-test-description', 'Nhập mô tả về test này...');

                // Initialize edit modal Quill editor with image upload handler
                editTestQuill = new Quill('#edit-test-description-editor', {
                    theme: 'snow',
                    placeholder: 'Nhập mô tả về test này...',
                    modules: {
                        toolbar: {
                            container: [
                                [{
                                    'header': [1, 2, 3, false]
                                }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                [{
                                    'color': []
                                }, {
                                    'background': []
                                }],
                                ['link', 'image'],
                                ['clean']
                            ],
                            handlers: {
                                image: imageHandler
                            }
                        }
                    }
                });

                // Image upload handler for edit modal
                function imageHandler() {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.click();

                    input.onchange = async () => {
                        const file = input.files[0];
                        if (!file) return;

                        // Show loading
                        const range = editTestQuill.getSelection(true);
                        editTestQuill.insertText(range.index, 'Đang tải ảnh...');

                        // Upload to server
                        const formData = new FormData();
                        formData.append('image', file);

                        try {
                            const response = await fetch('/admin/upload-image', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                }
                            });

                            const data = await response.json();

                            // Remove loading text
                            editTestQuill.deleteText(range.index, 16);

                            if (data.success) {
                                // Insert image
                                editTestQuill.insertEmbed(range.index, 'image', data.url);
                                editTestQuill.setSelection(range.index + 1);
                            } else {
                                alert('Upload failed: ' + (data.message || 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Upload error:', error);
                            editTestQuill.deleteText(range.index, 16);
                            alert('Lỗi khi upload ảnh!');
                        }
                    };
                }

                // Sync Quill content with textarea on form submit
                document.getElementById('editTestForm').addEventListener('submit', function() {
                    document.getElementById('edit-test-description').value = editTestQuill.root.innerHTML;
                });
            });
        </script>
    @endpush
@endsection
