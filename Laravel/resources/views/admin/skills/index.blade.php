@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">Quản lý Skills</h1>
                    <p class="text-muted mb-0">Quản lý tất cả các kỹ năng (Reading, Writing, Listening, Speaking)</p>
                </div>
                <div>
                    <a href="{{ route('admin.skills.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Thêm Skill Mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.skills.index') }}" method="GET" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Tên skill...">
                </div>

                <!-- Skill Type -->
                <div class="col-md-2">
                    <label class="form-label">Loại Skill</label>
                    <select class="form-select" name="skill_type">
                        <option value="">Tất cả</option>
                        <option value="reading" {{ request('skill_type') == 'reading' ? 'selected' : '' }}>Reading</option>
                        <option value="writing" {{ request('skill_type') == 'writing' ? 'selected' : '' }}>Writing</option>
                        <option value="listening" {{ request('skill_type') == 'listening' ? 'selected' : '' }}>Listening</option>
                        <option value="speaking" {{ request('skill_type') == 'speaking' ? 'selected' : '' }}>Speaking</option>
                    </select>
                </div>

                <!-- Exam -->
                <div class="col-md-3">
                    <label class="form-label">Exam</label>
                    <select class="form-select" name="exam_id">
                        <option value="">Tất cả</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Test -->
                <div class="col-md-3">
                    <label class="form-label">Test</label>
                    <select class="form-select" name="test_id">
                        <option value="">Tất cả</option>
                        @foreach($tests as $test)
                            <option value="{{ $test->id }}" {{ request('test_id') == $test->id ? 'selected' : '' }}>
                                {{ $test->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('admin.skills.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Skills List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Danh sách Skills ({{ $skills->total() }})</h5>
        </div>
        <div class="card-body p-0">
            @if($skills->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Skill</th>
                                <th>Loại</th>
                                <th>Exam</th>
                                <th>Test</th>
                                <th>Thời gian</th>
                                <th>Sections</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skills as $skill)
                                <tr>
                                    <td>{{ $skill->id }}</td>
                                    <td>
                                        <strong>{{ $skill->name }}</strong>
                                        @if($skill->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($skill->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'reading' => 'bg-primary',
                                                'writing' => 'bg-success',
                                                'listening' => 'bg-info',
                                                'speaking' => 'bg-warning'
                                            ][$skill->skill_type] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ strtoupper($skill->skill_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($skill->examTest && $skill->examTest->exam)
                                            {{ $skill->examTest->exam->name }}
                                            <br>
                                            <span class="badge bg-secondary">{{ strtoupper($skill->examTest->exam->type) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($skill->examTest)
                                            {{ $skill->examTest->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="bi bi-clock me-1"></i>{{ $skill->time_limit }} phút
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $skill->sections->count() }} sections</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.skills.toggle-active', $skill) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $skill->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $skill->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.skills.edit', $skill) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.skills.destroy', $skill) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa skill này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">Chưa có skill nào</p>
                    <a href="{{ route('admin.skills.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Thêm Skill Đầu Tiên
                    </a>
                </div>
            @endif
        </div>
        
        @if($skills->hasPages())
            <div class="card-footer">
                {{ $skills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
