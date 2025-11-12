@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Thêm bộ đề thi mới</h1>
                </div>
                <div>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
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
                <div class="card-body">
                    <form action="{{ route('admin.exams.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên bộ đề thi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Loại <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" 
                                    name="type" 
                                    required>
                                <option value="">-- Chọn loại --</option>
                                <option value="ielts" {{ old('type') == 'ielts' ? 'selected' : '' }}>IELTS</option>
                                <option value="toeic" {{ old('type') == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                                <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <div id="description-editor"></div>
                            <textarea class="form-control d-none @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh</label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="ImagePreview.show(this)">
                            <small class="form-text text-muted">
                                Định dạng: JPG, PNG, GIF, WEBP. Tối đa 10MB.
                            </small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 position-relative" style="display: none; max-width: 300px;">
                                <img id="preview" src="" alt="Preview" class="img-thumbnail w-100">
                                <button type="button" 
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                        onclick="ImagePreview.remove()"
                                        style="z-index: 10;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Is Active -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt
                                </label>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Lưu
                            </button>
                            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Helper Card -->
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            <strong>Tên bộ đề thi:</strong> Tên hiển thị của bộ đề thi
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            <strong>Loại:</strong> IELTS, TOEIC hoặc Online
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            <strong>Hình ảnh:</strong> Định dạng JPG, PNG, GIF, WEBP (tối đa 10MB)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>.ql-container, .ql-editor { min-height: 300px; font-size: 14px; }</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="{{ asset('assets/js/admin-editor.js') }}"></script>
@endpush
@endsection
