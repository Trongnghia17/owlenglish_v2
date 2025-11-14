<!-- Edit Test Modal -->
<div class="modal fade" id="editTestModal" tabindex="-1" aria-labelledby="editTestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editTestModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Sửa Nhóm Đề Thi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTestForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Current Image -->
                    <div id="edit-current-image-wrapper" class="mb-3" style="display: none;">
                        <label class="form-label fw-semibold">Ảnh bìa hiện tại</label>
                        <div class="position-relative" style="max-width: 300px;">
                            <img id="edit-current-image" src="" alt="Current Image" class="img-thumbnail w-100">
                            <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                    onclick="removeEditCurrentImage()"
                                    style="z-index: 10;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <input type="hidden" name="remove_image" id="edit-remove-image-flag" value="0">
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="edit-test-image" class="form-label fw-semibold">
                            <span id="edit-image-label">Thay đổi ảnh bìa</span>
                        </label>
                        <input type="file" class="form-control" id="edit-test-image"
                            name="image" accept="image/*" onchange="ImagePreview.show(this, 'editTestImagePreview')">
                        <small class="form-text text-muted d-block mt-1">
                            Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB
                        </small>

                        <!-- Image Preview -->
                        <div id="editTestImagePreview" class="mt-3" style="display: none;">
                            <label class="form-label">Xem trước:</label>
                            <div class="position-relative" style="max-width: 300px;">
                                <img src="" alt="Preview" class="img-thumbnail w-100 rounded shadow-sm">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                    onclick="ImagePreview.remove('edit-test-image', 'editTestImagePreview')" style="z-index: 10;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="edit-test-name" class="form-label fw-semibold">
                            Tên Test <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit-test-name"
                            name="name" placeholder="Ví dụ: Test 1, Mini Test, Full Test..." required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="edit-test-description" class="form-label fw-semibold">
                            Mô tả
                        </label>
                        <div id="edit-test-description-editor" class="border rounded"></div>
                        <textarea class="form-control d-none" id="edit-test-description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
