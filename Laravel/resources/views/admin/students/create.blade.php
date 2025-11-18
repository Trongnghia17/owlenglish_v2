@extends('layouts.app')

@section('content')
<div class="container mt-4">
	<a href="{{ route('admin.students.index') }}" class="btn btn-link mb-3">← Quay lại danh sách</a>

	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif

	<div class="card">
		<div class="card-body">
			<h3 class="card-title">Thêm tài khoản học sinh</h3>

			@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
				@csrf

				<div class="mb-3">
					<label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
					<input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
				</div>

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="email" class="form-label">Email</label>
						<input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
					</div>

					<div class="col-md-6 mb-3">
						<label for="phone" class="form-label">Số điện thoại</label>
						<input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0912345678">
					</div>
				</div>

				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="birthday" class="form-label">Ngày sinh</label>
						<input type="date" id="birthday" name="birthday" class="form-control" value="{{ old('birthday') }}">
					</div>

					<div class="col-md-6 mb-3">
						<label for="password" class="form-label">Mật khẩu</label>
						<input type="password" id="password" name="password" class="form-control" placeholder="Để trống sẽ dùng mật khẩu mặc định">
						<div class="form-text">Mật khẩu tối thiểu 6 ký tự. Nếu để trống, hệ thống sẽ tạo mật khẩu mặc định.</div>
					</div>
				</div>

				<div class="mb-3">
					<label for="avatar" class="form-label">Avatar (tùy chọn)</label>
					<input type="file" id="avatar" name="avatar" class="form-control">
				</div>

				<div class="d-flex gap-2">
					<button type="submit" class="btn btn-primary">Lưu</button>
					<a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Hủy</a>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection