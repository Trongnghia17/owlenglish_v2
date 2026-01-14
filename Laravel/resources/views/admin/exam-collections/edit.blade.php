@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <div class="mb-5">
            <h3 class="mb-0">Chỉnh sửa bộ đề</h3>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.exam-collections.update', $examCollection) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Tên bộ đề</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $examCollection->name) }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại kỳ thi</label>
                        <select name="type"
                                class="form-control"
                                required>
                            <option value="ielts"
                                {{ $examCollection->type === 'ielts' ? 'selected' : '' }}>
                                IELTS
                            </option>
                            <option value="toeic"
                                {{ $examCollection->type === 'toeic' ? 'selected' : '' }}>
                                TOEIC
                            </option>
                        </select>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox"
                               name="status"
                               value="1"
                               class="form-check-input"
                               {{ $examCollection->status ? 'checked' : '' }}>
                        <label class="form-check-label">
                            Hoạt động
                        </label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.exam-collections.index') }}"
                           class="btn btn-light me-2">
                            Hủy
                        </a>
                        <button class="btn btn-primary">
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
