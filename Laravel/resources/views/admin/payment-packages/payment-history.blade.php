@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <!-- ===== TITLE ===== -->
        <div class="mb-4">
            <h3 class="mb-0">Lịch sử thanh toán</h3>
            <p class="text-muted">Quản lý và thống kê các giao dịch nạp tiền</p>
        </div>

        <!-- ===== STATISTICS ===== -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Tổng đơn</h6>
                        <h4>{{ number_format($stats['total_orders']) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Thành công</h6>
                        <h4 class="text-success">
                            {{ number_format($stats['success_orders']) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Đang chờ</h6>
                        <h4 class="text-warning">
                            {{ number_format($stats['pending_orders']) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Thất bại / Huỷ</h6>
                        <h4 class="text-danger">
                            {{ number_format($stats['failed_orders']) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== REVENUE ===== -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Doanh thu hôm nay</h6>
                        <h4 class="text-primary">
                            {{ number_format($stats['today_revenue']) }} VNĐ
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Doanh thu tháng này</h6>
                        <h4 class="text-primary">
                            {{ number_format($stats['month_revenue']) }} VNĐ
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">Tổng doanh thu</h6>
                        <h4 class="text-success">
                            {{ number_format($stats['total_revenue']) }} VNĐ
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== FILTER ===== -->
        <div class="card mb-4">
            <div class="card-header">
                <form method="GET" class="row g-2 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">Tìm người dùng</label>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               class="form-control"
                               placeholder="Tên hoặc email">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>
                                Thành công
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                Đang chờ
                            </option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>
                                Đã huỷ
                            </option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                Thất bại
                            </option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>
                                Hết hạn
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date"
                               name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date"
                               name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">
                            Lọc dữ liệu
                        </button>
                    </div>

                </form>
            </div>

            <!-- ===== TABLE ===== -->
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-centered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Người dùng</th>
                            <th>Gói nạp</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Phương thức</th>
                            <th>Thời gian</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    {{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}
                                </td>

                                <td>
                                    <strong>{{ $payment->user->name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $payment->user->email }}
                                    </small>
                                </td>

                                <td>
                                    {{ $payment->package->name ?? '-' }}
                                </td>

                                <td class="fw-bold">
                                    {{ number_format($payment->amount) }} VNĐ
                                </td>

                                <td>
                                    @switch($payment->status)
                                        @case('success')
                                            <span class="badge badge-success-soft text-success">
                                                Thành công
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning-soft text-warning">
                                                Đang chờ
                                            </span>
                                            @break
                                        @case('canceled')
                                            <span class="badge badge-secondary-soft text-muted">
                                                Đã huỷ
                                            </span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-dark-soft text-dark">
                                                Hết hạn
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-danger-soft text-danger">
                                                Thất bại
                                            </span>
                                    @endswitch
                                </td>

                                <td>
                                    {{ strtoupper($payment->payment_method) }}
                                </td>

                                <td>
                                    {{ $payment->paid_at
                                        ? $payment->paid_at->format('d/m/Y H:i')
                                        : $payment->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-4">
                                    Chưa có giao dịch nào
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    {{ $payments->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
