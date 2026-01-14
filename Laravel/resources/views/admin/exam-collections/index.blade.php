@extends('layouts.app')

@section('content')
    <div class="app-content">
        <div class="container-fluid">

            <!-- Title -->
            <div class="mb-5">
                <h3 class="mb-0">Quản lý bộ đề</h3>
            </div>

            <!-- Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="row justify-content-between align-items-center">

                        <!-- Add button -->
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('admin.exam-collections.create') }}" class="btn btn-primary">
                                + Thêm bộ đề
                            </a>
                        </div>

                        <!-- Search -->
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.exam-collections.index') }}" class="d-flex">
                                <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                                    placeholder="Tìm theo tên bộ đề">
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
                                    <th>Tên bộ đề</th>
                                    <th>Loại</th>
                                    <th>Trạng thái</th>
                                    <th>Chức năng</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($collections as $collection)
                                    <tr>
                                        <td>
                                            {{ ($collections->currentPage() - 1) * $collections->perPage() + $loop->iteration }}
                                        </td>

                                        <td>{{ $collection->name }}</td>

                                        <td>
                                            @if ($collection->type === 'ielts')
                                                <span class="badge badge-primary-soft text-primary">
                                                    IELTS
                                                </span>
                                            @else
                                                <span class="badge badge-primary-soft text-secondary">
                                                    TOEIC
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td>
                                            @if ($collection->status)
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
                                            <form action="{{ route('admin.exam-collections.toggleStatus', $collection) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-ghost btn-sm" title="Bật / tắt">
                                                    <i data-feather="power"></i>
                                                </button>
                                            </form>

                                            <!-- Edit -->
                                            <a href="{{ route('admin.exam-collections.edit', $collection) }}"
                                                class="btn btn-ghost btn-sm" title="Chỉnh sửa">
                                                <i data-feather="edit"></i>
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('admin.exam-collections.destroy', $collection) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-ghost btn-sm text-danger"
                                                    onclick="return confirm('Xóa bộ đề này?')" title="Xóa">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Không có bộ đề nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{ $collections->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
