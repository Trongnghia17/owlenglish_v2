<!-- Create Exam Modal -->
<div class="modal fade" id="createExamModal" tabindex="-1" aria-labelledby="createExamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="createExamModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Thêm bộ đề thi mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.exams.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="modal-image" class="form-label fw-semibold">
                            Ảnh bìa
                        </label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="modal-image"
                            name="image" accept="image/*" onchange="ImagePreview.show(this, 'modalImagePreview')">
                        <small class="form-text text-muted d-block mt-1">
                            Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB
                        </small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- Image Preview -->
                        <div id="modalImagePreview" class="mt-3 position-relative"
                            style="display: none; max-width: 300px;">
                            <img src="" alt="Preview" class="img-thumbnail w-100 rounded shadow-sm">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                onclick="ImagePreview.remove('modal-image', 'modalImagePreview')" style="z-index: 10;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Tiêu đề <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name') }}" placeholder="Nhập tiêu đề ..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold">
                            Loại <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type"
                            required>
                            <option value="">-- Chọn loại --</option>
                            <option value="ielts" {{ old('type') == 'ielts' ? 'selected' : '' }}>IELTS</option>
                            <option value="toeic" {{ old('type') == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Collection -->
                    <div class="mb-3">
                        <label for="collection" class="form-label fw-semibold">
                            Bộ đề <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('exam_collection_id') is-invalid @enderror" id="collection"
                            name="exam_collection_id" required>
                            <option value="">-- Chọn bộ đề --</option>
                            @foreach ($collections as $collection)
                                <option value="{{ $collection->id }}" data-type="{{ $collection->type }}"
                                    {{ old('exam_collection_id') == $collection->id ? 'selected' : '' }}>
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

                            function filterCollections(type) {
                                collectionSelect.innerHTML = '';
                                collectionSelect.appendChild(options[0]);

                                options.forEach(option => {
                                    if (!option.dataset.type) return;

                                    if (option.dataset.type === type) {
                                        collectionSelect.appendChild(option);
                                    }
                                });

                                collectionSelect.value = '';
                            }

                            typeSelect.addEventListener('change', function() {
                                const type = this.value;
                                if (type) {
                                    filterCollections(type);
                                } else {
                                    // reset lại tất cả
                                    collectionSelect.innerHTML = '';
                                    options.forEach(opt => collectionSelect.appendChild(opt));
                                }
                            });
                            if (typeSelect.value) {
                                filterCollections(typeSelect.value);
                            }
                        });
                    </script>

                    <!-- Level -->
                    <div class="mb-3">
                        <label for="level" class="form-label fw-semibold">
                            Mức độ <span class="text-danger">*</span>
                        </label>

                        <select class="form-select @error('level') is-invalid @enderror" id="level" name="level"
                            required>
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



                    <!-- Description -->
                    <div class="mb-3">
                        <label for="modal-description" class="form-label fw-semibold">
                            Mô tả
                        </label>
                        <div id="modal-description-editor" class="border rounded"></div>
                        <textarea class="form-control d-none @error('description') is-invalid @enderror" id="modal-description"
                            name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>



                    <!-- Is Active -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="modal-is_active" name="is_active"
                                value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="modal-is_active">
                                Kích hoạt
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
