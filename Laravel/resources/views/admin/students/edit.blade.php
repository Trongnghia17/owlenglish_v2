@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <a href="{{ route('admin.students.index') }}" class="btn btn-primary me-2 mb-3">← Quay lại danh sách</a>
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Chỉnh sửa tài khoản học sinh</h3>

            <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="{{ old('name', $student->name) }}" 
                        required
                    >
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            value="{{ old('email', $student->email) }}"
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="0912345678"
                            value="{{ old('phone', $student->phone) }}"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Để trống nếu không muốn đổi"
                        >
                        <div class="form-text">Mật khẩu tối thiểu 6 ký tự. Nếu để trống, hệ thống sẽ giữ nguyên mật khẩu cũ.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar (tùy chọn)</label>
                    <input type="file" id="avatar" name="avatar" class="form-control">

                    @if ($student->avatar)
                        <div class="mt-2">
                            <img src="{{ asset($student->avatar) }}" alt="Avatar" class="rounded" width="80">
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Hủy</a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
