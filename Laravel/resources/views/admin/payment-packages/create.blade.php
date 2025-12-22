@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <a href="{{ route('admin.payment-packages.index') }}"
       class="btn btn-primary me-2 mb-3">← Quay lại danh sách</a>

    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Thêm gói nạp tiền</h3>

            <form action="{{ route('admin.payment-packages.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Tên gói <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Thời hạn (tháng) <span class="text-danger">*</span></label>
                        <input type="number" name="duration"
                               class="form-control"
                               value="{{ old('duration') }}"
                               min="1" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Giá gốc (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="price"
                               class="form-control"
                               value="{{ old('price') }}"
                               min="0" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Giảm giá (%)</label>
                        <input type="number" name="discount_percent"
                               class="form-control"
                               value="{{ old('discount_percent', 0) }}"
                               min="0" max="100">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Thứ tự hiển thị</label>
                        <input type="number" name="display_order"
                               class="form-control"
                               value="{{ old('display_order', 0) }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Gói nổi bật</label>
                        <select name="is_featured" class="form-select">
                            <option value="0">Không</option>
                            <option value="1">Có</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="1">Hoạt động</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <a href="{{ route('admin.payment-packages.index') }}"
                       class="btn btn-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
