@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <div class="mb-5">
            <h3 class="mb-0">Thêm bộ đề</h3>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.exam-collections.store') }}"
                      method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tên bộ đề</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Loại kỳ thi</label>
                        <select name="type"
                                class="form-control"
                                required>
                            <option value="">-- Chọn --</option>
                            <option value="ielts">IELTS</option>
                            <option value="toeic">TOEIC</option>
                        </select>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox"
                               name="status"
                               value="1"
                               class="form-check-input"
                               checked>
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
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
