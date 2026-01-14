@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <!-- Title -->
        <div class="mb-5">
            <h3 class="mb-0">Quản lý bộ lọc đề thi</h3>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <div class="row align-items-center">

                    <!-- Add -->
                    <div class="col-md-4 mb-2">
                        <a href="{{ route('admin.exam-filters.create') }}" class="btn btn-primary">
                            + Thêm bộ lọc
                        </a>
                    </div>

                    <!-- Filters -->
                    <div class="col-md-8">
                        <form method="GET" class="row g-2">
                            <div class="col">
                                <input type="text"
                                       name="q"
                                       value="{{ request('q') }}"
                                       class="form-control"
                                       placeholder="Tìm theo tên">
                            </div>

                            <div class="col">
                                <select name="type" class="form-select">
                                    <option value="">-- Loại --</option>
                                    <option value="skill" @selected(request('type')=='skill')>Skill</option>
                                    <option value="group" @selected(request('type')=='group')>Group</option>
                                    <option value="value" @selected(request('type')=='value')>Value</option>
                                </select>
                            </div>

                            <div class="col">
                                <button class="btn btn-primary">Lọc</button>
                            </div>
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
                            <th>Tên</th>
                            <th>Loại</th>
                            <th>Thuộc</th>
                            <th>Trạng thái</th>
                            <th width="120">Chức năng</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($filters as $filter)
                            <tr>
                                <td>
                                    {{ ($filters->currentPage()-1)*$filters->perPage() + $loop->iteration }}
                                </td>

                                <td>
                                    {{ $filter->name }}
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ strtoupper($filter->type) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $filter->parent?->name ?? '—' }}
                                </td>

                                <td>
                                    @if($filter->is_active)
                                        <span class="badge badge-success-soft text-success">Hoạt động</span>
                                    @else
                                        <span class="badge badge-danger-soft text-danger">Ẩn</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.exam-filters.edit',$filter) }}"
                                       class="btn btn-ghost btn-sm">
                                        <i data-feather="edit"></i>
                                    </a>

                                    <form action="{{ route('admin.exam-filters.destroy',$filter) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost btn-sm text-danger"
                                                onclick="return confirm('Xóa bộ lọc này?')">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Chưa có bộ lọc nào
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    {{ $filters->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
