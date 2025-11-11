@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Quản lý Exam</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Exams</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Thêm Exam Mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.exams.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tên exam..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loại</label>
                            <select name="type" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="ielts" {{ request('type') == 'ielts' ? 'selected' : '' }}>IELTS</option>
                                <option value="toeic" {{ request('type') == 'toeic' ? 'selected' : '' }}>TOEIC</option>
                                <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="is_active" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-lg">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Hình ảnh</th>
                                    <th>Tên Exam</th>
                                    <th>Loại</th>
                                    <th width="100">Trạng thái</th>
                                    <th width="120">Số Test</th>
                                    <th width="180">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                <tr>
                                    <td>
                                        @if($exam->image)
                                            <img src="{{ Storage::url($exam->image) }}" 
                                                 alt="{{ $exam->name }}" 
                                                 class="rounded"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-image text-muted fs-3"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $exam->name }}</div>
                                        <small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($exam->type == 'ielts') bg-primary
                                            @elseif($exam->type == 'toeic') bg-success
                                            @else bg-info
                                            @endif">
                                            {{ strtoupper($exam->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.exams.toggle-active', $exam) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $exam->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $exam->is_active ? 'Hoạt động' : 'Tắt' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $exam->tests->count() }} Test</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.exams.show', $exam) }}" 
                                               class="btn btn-sm btn-info" title="Xem">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.exams.edit', $exam) }}" 
                                               class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.exams.destroy', $exam) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                        <p class="text-muted">Không có exam nào</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($exams->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $exams->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
