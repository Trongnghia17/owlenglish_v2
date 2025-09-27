@extends('layouts.app')

@section('content')
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-12">
                    <!-- Page header -->
                    <div class="mb-5">
                        <h3 class="mb-0">Quản lý người dùng</h3>
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
                                        <a class="btn btn-primary me-2" data-bs-toggle="modal"
                                            data-bs-target="#addUserModal">
                                            + Thêm người dùng
                                        </a>
                                    </div>

                                    <!-- Add User Modal -->
                                    <div class="modal fade" id="addUserModal" tabindex="-1"
                                        aria-labelledby="addUserModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Đóng"></button>
                                                </div>
                                                <form action="{{ route('admin.users.store') }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="name" class="form-label">Tên người dùng</label>
                                                                <input type="text" class="form-control" id="name"
                                                                    name="name" required value="{{ old('name') }}">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="phone" class="form-label">Số điện thoại</label>
                                                                <input type="text" class="form-control" id="phone"
                                                                    name="phone" required value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="email" class="form-label">Email</label>
                                                                <input type="email" class="form-control" id="email"
                                                                    name="email" value="{{ old('email') }}" placeholder="Để trống nếu không có email">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="password" class="form-label">Mật khẩu</label>
                                                                <input type="password" class="form-control" id="password"
                                                                    name="password" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                                                <input type="password" class="form-control"
                                                                    id="password_confirmation" name="password_confirmation" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="role" class="form-label">Vai trò</label>
                                                                <select class="form-select" id="role" name="role" required>
                                                                    @foreach (App\Models\User::getRoleOptions() as $value => $name)
                                                                        @if($value != 0)
                                                                            <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                                                                {{ $name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="is_active" class="form-label">Trạng thái</label>
                                                                <select class="form-select" id="is_active" name="is_active">
                                                                    <option value="1" {{ old('is_active', 1) ? 'selected' : '' }}>Hoạt động</option>
                                                                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Không hoạt động</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-primary">Lưu</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex mb-3">
                                            @if(request('role'))
                                                <input type="hidden" name="role" value="{{ request('role') }}">
                                            @endif
                                            <input type="search" name="search" value="{{ request('search') }}"
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
                                                <th>Vai trò</th>
                                                <th>Ngày tạo</th>
                                                <th>Trạng thái</th>
                                                <th>Chức năng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($users as $user)
                                                <tr>
                                                    <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                                                    </td>
                                                    <td class="ps-1">
                                                        <div class="d-flex align-items-center">
                                                            <div class="ms-2">
                                                                <h5 class="mb-0">
                                                                    <a class="text-inherit">{{ $user->name }}</a>
                                                                </h5>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @php $roleInfo = $user->getRoleInfo(); @endphp
                                                        <span class="badge bg-{{ $roleInfo['color'] }}">
                                                            {{ $roleInfo['name'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @if($user->is_active)
                                                            <span class="badge badge-success-soft text-success">Hoạt động</span>
                                                        @else
                                                            <span class="badge badge-danger-soft text-danger">Không hoạt động</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <!-- Edit button -->
                                                        <a class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editUserModal{{ $user->id }}"
                                                            data-template="editOne">
                                                            <i data-feather="edit" class="icon-xs"></i>
                                                        </a>

                                                        <!-- Permissions button -->
                                                        <a class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#permissionsModal{{ $user->id }}"
                                                            data-template="permissions"
                                                            title="Phân quyền">
                                                            <i data-feather="key" class="icon-xs"></i>
                                                        </a>

                                                        <!-- Toggle status button -->
                                                        @if($user->id !== auth()->id())
                                                            <form action="{{ route('admin.users.toggleStatus', $user) }}"
                                                                method="POST" class="d-inline-block">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                                    onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái?')">
                                                                    <i data-feather="{{ $user->is_active ? 'user-x' : 'user-check' }}"
                                                                        class="icon-xs"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <!-- Delete button -->
                                                        @if($user->id !== auth()->id())
                                                            <a class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip"
                                                                data-template="trashOne" data-bs-toggle="modal"
                                                                data-bs-target="#deleteUserModal{{ $user->id }}">
                                                                <i data-feather="trash-2" class="icon-xs"></i>
                                                            </a>
                                                        @endif

                                                        <!-- Edit Modal -->
                                                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1"
                                                            aria-labelledby="editUserModal{{ $user->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Sửa người dùng</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal" aria-label="Đóng"></button>
                                                                    </div>
                                                                    <form action="{{ route('admin.users.update', $user) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <div class="modal-body">
                                                                            <div class="row">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="name" class="form-label">Tên người dùng</label>
                                                                                    <input type="text" class="form-control"
                                                                                        name="name" required value="{{ $user->name }}">
                                                                                </div>
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="phone" class="form-label">Số điện thoại</label>
                                                                                    <input type="text" class="form-control"
                                                                                        name="phone" required value="{{ $user->phone }}" placeholder="Nhập số điện thoại">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="email" class="form-label">Email</label>
                                                                                    <input type="email" class="form-control"
                                                                                        name="email" value="{{ $user->email }}" placeholder="Để trống nếu không có email">
                                                                                </div>
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="password" class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                                                                                    <input type="password" class="form-control" name="password">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                                                                    <input type="password" class="form-control" name="password_confirmation">
                                                                                </div>
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="role" class="form-label">Vai trò</label>
                                                                                    <select class="form-select" name="role"
                                                                                        required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                                                        @foreach (App\Models\User::getRoleOptions() as $value => $name)
                                                                                            @if($value != 0)
                                                                                                <option value="{{ $value }}" {{ $user->role_id == $value ? 'selected' : '' }}>
                                                                                                    {{ $name }}
                                                                                                </option>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </select>
                                                                                    @if($user->id === auth()->id())
                                                                                        <input type="hidden" name="role" value="{{ $user->role_id }}">
                                                                                        <small class="text-muted">Bạn không thể thay đổi vai trò của chính mình</small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-6 mb-3">
                                                                                    <label for="is_active" class="form-label">Trạng thái</label>
                                                                                    <select class="form-select" name="is_active">
                                                                                        <option value="1" {{ $user->is_active ? 'selected' : '' }}>Hoạt động</option>
                                                                                        <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Không hoạt động</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary"
                                                                                data-bs-dismiss="modal">Hủy</button>
                                                                            <button type="submit" class="btn btn-primary">Lưu
                                                                                thay đổi</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Permissions Modal -->
                                                        <div class="modal fade" id="permissionsModal{{ $user->id }}" tabindex="-1"
                                                            aria-labelledby="permissionsModalLabel{{ $user->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-xl">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">
                                                                            <i data-feather="key" class="icon-sm me-2"></i>
                                                                            Phân quyền cho {{ $user->name }}
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                                                    </div>
                                                                    <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                                                                        <div class="mb-3">
                                                                            @php $roleInfo = $user->getRoleInfo(); @endphp
                                                                            <p class="text-muted mb-2">Vai trò hiện tại: 
                                                                                <span class="badge bg-{{ $roleInfo['color'] }}">{{ $roleInfo['name'] }}</span>
                                                                            </p>
                                                                        </div>

                                                                        <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            
                                                                            <!-- Personal Permissions -->
                                                                            <div class="card mb-4">
                                                                                <div class="card-header">
                                                                                    <h6 class="mb-0">Quyền hạn cá nhân</h6>
                                                                                    <small class="text-muted">Các quyền này sẽ được thêm vào quyền từ vai trò</small>
                                                                                </div>
                                                                                <div class="card-body">
                                                                                    @php
                                                                                        $allPermissions = \App\Models\Permission::all()->groupBy('category');
                                                                                        $userPermissions = $user->permissions->pluck('id')->toArray();
                                                                                    @endphp
                                                                                    
                                                                                    @if($allPermissions->count() > 0)
                                                                                        @foreach($allPermissions as $category => $permissions)
                                                                                            <div class="mb-3">
                                                                                                <h6 class="text-primary text-uppercase mb-2">{{ ucfirst($category) }}</h6>
                                                                                                <div class="row">
                                                                                                    @foreach($permissions as $permission)
                                                                                                        <div class="col-md-6 col-lg-4 mb-2">
                                                                                                            <div class="form-check">
                                                                                                                <input class="form-check-input" type="checkbox" 
                                                                                                                       name="permissions[]" value="{{ $permission->name }}"
                                                                                                                       id="permission_{{ $user->id }}_{{ $permission->id }}"
                                                                                                                       {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                                                                                                <label class="form-check-label" for="permission_{{ $user->id }}_{{ $permission->id }}">
                                                                                                                    {{ $permission->display_name }}
                                                                                                                </label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            </div>
                                                                                            <hr>
                                                                                        @endforeach
                                                                                    @else
                                                                                        <div class="text-center py-4">
                                                                                            <i data-feather="shield" class="icon-lg text-muted mb-2"></i>
                                                                                            <p class="text-muted">Không có quyền nào được định nghĩa trong hệ thống</p>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>

                                                                            <!-- Role Permissions -->
                                                                            @if($user->role && $user->role->permissions->count() > 0)
                                                                                <div class="card">
                                                                                    <div class="card-header">
                                                                                        <h6 class="mb-0">Quyền từ vai trò</h6>
                                                                                        <small class="text-muted">Các quyền này được kế thừa từ vai trò {{ $roleInfo['name'] }}</small>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        @foreach($user->role->permissions->groupBy('category') as $category => $rolePermissions)
                                                                                            <div class="mb-3">
                                                                                                <h6 class="text-info text-uppercase mb-2">{{ ucfirst($category) }}</h6>
                                                                                                <div class="row">
                                                                                                    @foreach($rolePermissions as $permission)
                                                                                                        <div class="col-md-6 col-lg-4 mb-1">
                                                                                                            <span class="badge bg-info-soft text-info">
                                                                                                                <i data-feather="check" class="icon-xs me-1"></i>
                                                                                                                {{ $permission->display_name }}
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                            <i data-feather="x" class="icon-xs me-1"></i> Hủy
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i data-feather="save" class="icon-xs me-1"></i> Lưu thay đổi
                                                                        </button>
                                                                    </div>
                                                                        </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Delete Modal -->
                                                        @if($user->id !== auth()->id())
                                                            <div class="modal fade" id="deleteUserModal{{ $user->id }}"
                                                                tabindex="-1" aria-labelledby="deleteUserModalLabel{{ $user->id }}"
                                                                aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <div class="modal-content border-0 shadow">
                                                                            <div class="modal-header bg-danger text-white">
                                                                                <h5 class="modal-title"
                                                                                    id="deleteUserModalLabel{{ $user->id }}">
                                                                                    <i class="fe fe-trash-2 me-2"></i>Xác nhận xoá
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
                                                                                    <h5 class="mb-3">Bạn có chắc chắn muốn xoá?</h5>
                                                                                    <p class="mb-0">Người dùng <span
                                                                                            class="fw-bold text-danger">{{ $user->name }}</span>
                                                                                        sẽ bị xóa khỏi hệ thống.</p>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer border-top-0 pt-0 pb-3 px-4">
                                                                                <button type="button" class="btn btn-light-soft"
                                                                                    data-bs-dismiss="modal">
                                                                                    <i data-feather="x" class="icon-xs me-1"></i>
                                                                                    Huỷ bỏ
                                                                                </button>
                                                                                <button type="submit" class="btn btn-danger">
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

                            <div class="d-flex justify-content-center align-items-center p-5">
                                {{ $users->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

