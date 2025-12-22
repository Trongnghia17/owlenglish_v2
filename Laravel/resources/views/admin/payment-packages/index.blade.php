@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <!-- Title -->
        <div class="mb-5">
            <h3 class="mb-0">Quản lý gói nạp tiền</h3>
        </div>

        <!-- Card -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <!-- Add button -->
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('admin.payment-packages.create') }}"
                           class="btn btn-primary">
                            + Thêm gói nạp
                        </a>
                    </div>

                    <!-- Search -->
                    <div class="col-md-4">
                        <form method="GET"
                              action="{{ route('admin.payment-packages.index') }}"
                              class="d-flex">
                            <input type="search"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control"
                                   placeholder="Tìm theo tên gói">
                            <button class="btn btn-primary ms-2">Tìm</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Tên gói</th>
                                <th>Thời hạn</th>
                                <th>Giá gốc</th>
                                <th>Giảm</th>
                                <th>Giá sau giảm</th>
                                <th>Hiển thị nổi bật</th>
                                <th>Trạng thái</th>
                                <th>Chức năng</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($packages as $package)
                                <tr>
                                    <td>
                                        {{ ($packages->currentPage() - 1) * $packages->perPage() + $loop->iteration }}
                                    </td>

                                    <td>{{ $package->name }}</td>

                                    <td>{{ $package->duration }} tháng</td>

                                    <td>{{ number_format($package->price) }}VNĐ</td>

                                    <td>{{ $package->discount_percent }}%</td>

                                    <td class="fw-bold text-primary">
                                        {{ number_format($package->final_price) }}VNĐ
                                    </td>

                                    <!-- Featured -->
                                    <td>
                                        @if ($package->is_featured)
                                            <span class="badge badge-success-soft text-success">
                                                ⭐ Nổi bật
                                            </span>
                                        @else
                                            <span class="badge badge-secondary-soft text-muted">
                                                Thường
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @if ($package->status)
                                            <span class="badge badge-success-soft text-success">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="badge badge-danger-soft text-danger">
                                                Tạm ẩn
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <!-- Toggle status -->
                                        <form action="{{ route('admin.payment-packages.toggleStatus', $package) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-ghost btn-sm"
                                                    data-bs-toggle="tooltip"
                                                    title="Bật / tắt">
                                                <i data-feather="power"></i>
                                            </button>
                                        </form>

                                        <!-- Edit -->
                                        <a href="{{ route('admin.payment-packages.edit', $package) }}"
                                           class="btn btn-ghost btn-sm"
                                           data-bs-toggle="tooltip"
                                           title="Chỉnh sửa">
                                            <i data-feather="edit"></i>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('admin.payment-packages.destroy', $package) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-sm text-danger"
                                                    onclick="return confirm('Xóa gói này?')"
                                                    data-bs-toggle="tooltip"
                                                    title="Xóa">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Không có gói nạp nào được tìm thấy
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $packages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
