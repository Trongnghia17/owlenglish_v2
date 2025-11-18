@extends('layouts.app')

@section('content')
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-12">
                    <!-- Page header -->
                    <div class="mb-5">
                        <h3 class="mb-0">Quản lý học sinh</h3>
                    </div>
                </div>
            </div>
            <div>
                <!-- row -->
                <div class="row">
                    <div class="col-12">
                        <!-- card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row justify-content-between">
                                    <div class="col-md-6 mb-3">
                                        <a class="btn btn-primary me-2" href="{{ route('admin.students.create') }}">
                                            + Thêm người dùng
                                        </a>
                                    </div>



                                    <div class="col-lg-4 col-md-6">
                                        <form action="{{ route('admin.students.index') }}" method="GET"
                                            class="d-flex mb-3">
                                            @if (request('role'))
                                                <input type="hidden" name="role" value="{{ request('role') }}">
                                            @endif
                                            <input type="search" name="q" value="{{ request('q') }}"
                                                class="form-control" placeholder="Tìm kiếm theo tên, email">
                                            <div class="col-auto ms-3">
                                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table text-nowrap mb-0 table-centered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>STT</th>
                                                <th>Tên</th>
                                                <th>Email</th>
                                                <th>Số điện thoại</th>
                                                <th>Ngày tạo</th>
                                                <th>Trạng thái</th>
                                                <th>Đăng nhập bằng</th>
                                                <th>Chức năng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($students as $user)
                                                <tr>
                                                    <td>{{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                                                    </td>
                                                    <td class="ps-1">
                                                        <div class="d-flex align-items-center">
                                                            <div class="ms-2">
                                                                <h5 class="mb-0">
                                                                    <a class="text-inherit">
                                                                        @if ($user->name)
                                                                            {{ $user->name }}
                                                                        @else
                                                                            <span class="badge bg-secondary"
                                                                                style="font-size: 11px;">Chưa
                                                                                có</span>
                                                                        @endif
                                                                    </a>
                                                                </h5>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if ($user->phone)
                                                            {{ $user->phone }}
                                                        @else
                                                            <span class="badge bg-secondary" style="font-size: 11px;">Chưa
                                                                có</span>
                                                        @endif
                                                    </td>

                                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if ($user->is_active)
                                                            <span class="badge badge-success-soft text-success">Hoạt
                                                                động</span>
                                                        @else
                                                            <span class="badge badge-danger-soft text-danger">Không hoạt
                                                                động</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            @foreach ($user->login_methods as $method)
                                                                <img src="/icons/{{ $method }}.svg"
                                                                    title="{{ strtoupper($method) }}"
                                                                    style="width:20px;height:20px;">
                                                            @endforeach

                                                            {{-- Không có phương thức nào → mặc định là local --}}
                                                            @if ($user->login_methods == null || count($user->login_methods) === 0)
                                                                <img src="/icons/local.svg" title="Local account"
                                                                    style="width:20px;height:20px;">
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                            href="{{ route('admin.students.edit', $user->id) }}"
                                                            data-bs-toggle="tooltip" title="Chỉnh sửa">
                                                            <i data-feather="edit" class="icon-xs"></i>
                                                        </a>


                                                        <!-- Delete button -->
                                                        @if ($user->id !== auth()->id())
                                                            <a class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                                data-template="trashOne" data-bs-toggle="modal"
                                                                data-bs-target="#deleteUserModal{{ $user->id }}">
                                                                <i data-feather="trash-2" class="icon-xs"></i>
                                                            </a>
                                                        @endif

                                                        <!-- Delete Modal -->
                                                        @if ($user->id !== auth()->id())
                                                            <div class="modal fade" id="deleteUserModal{{ $user->id }}"
                                                                tabindex="-1"
                                                                aria-labelledby="deleteUserModalLabel{{ $user->id }}"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <form
                                                                        action="{{ route('admin.students.destroy', $user) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <div class="modal-content border-0 shadow">
                                                                            <div class="modal-header bg-danger text-white">
                                                                                <h5 class="modal-title"
                                                                                    id="deleteUserModalLabel{{ $user->id }}">
                                                                                    <i class="fe fe-trash-2 me-2"></i>Xác
                                                                                    nhận xoá
                                                                                </h5>
                                                                                <button type="button"
                                                                                    class="btn-close btn-close-white"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Đóng"></button>
                                                                            </div>
                                                                            <div class="modal-body p-4">
                                                                                <div class="text-center mb-3">
                                                                                    <div class="avatar avatar-lg mb-3">
                                                                                        <span
                                                                                            class="avatar-text rounded-circle bg-danger-soft text-danger">
                                                                                            <i data-feather="alert-triangle"
                                                                                                class="icon-md"></i>
                                                                                        </span>
                                                                                    </div>
                                                                                    <h5 class="mb-3">Bạn có chắc chắn muốn
                                                                                        xoá?</h5>
                                                                                    <p class="mb-0">Người dùng <span
                                                                                            class="fw-bold text-danger">{{ $user->name }}</span>
                                                                                        sẽ bị xóa khỏi hệ thống.</p>
                                                                                </div>
                                                                            </div>
                                                                            <div
                                                                                class="modal-footer border-top-0 pt-0 pb-3 px-4">
                                                                                <button type="button"
                                                                                    class="btn btn-light-soft"
                                                                                    data-bs-dismiss="modal">
                                                                                    <i data-feather="x"
                                                                                        class="icon-xs me-1"></i>
                                                                                    Huỷ bỏ
                                                                                </button>
                                                                                <button type="submit"
                                                                                    class="btn btn-danger">
                                                                                    <i data-feather="trash-2"
                                                                                        class="icon-xs me-1"></i> Xoá
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i data-feather="users" class="icon-lg text-muted mb-2"></i>
                                                            <p class="text-muted">Không có người dùng nào được tìm thấy</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
