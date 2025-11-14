<!-- Create Test Modal -->
<div class="modal fade" id="createTestModal" tabindex="-1" aria-labelledby="createTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createTestModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Thêm Test Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.exams.tests.store', $exam) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="modal-test-image" class="form-label fw-semibold">
                            Hình ảnh
                        </label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="modal-test-image"
                            name="image" accept="image/*" onchange="ImagePreview.show(this, 'modalTestImagePreview')">
                        <small class="form-text text-muted d-block mt-1">
                            Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB
                        </small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- Image Preview -->
                        <div id="modalTestImagePreview" class="mt-3 position-relative"
                            style="display: none; max-width: 300px;">
                            <img src="" alt="Preview" class="img-thumbnail w-100 rounded shadow-sm">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                onclick="ImagePreview.remove('modal-test-image', 'modalTestImagePreview')" style="z-index: 10;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="test-name" class="form-label fw-semibold">
                            Tên Test <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="test-name"
                            name="name" value="{{ old('name') }}" placeholder="Ví dụ: Test 1, Mini Test, Full Test..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="modal-test-description" class="form-label fw-semibold">
                            Mô tả
                        </label>
                        <div id="modal-test-description-editor" class="border rounded"></div>
                        <textarea class="form-control d-none @error('description') is-invalid @enderror"
                            id="modal-test-description" name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
