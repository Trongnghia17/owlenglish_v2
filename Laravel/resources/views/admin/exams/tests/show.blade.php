@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">{{ $test->name }}</h1>
                
                </div>
                <div>
                    <a href="{{ route('admin.exams.tests.edit', [$exam, $test]) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-2"></i>Chỉnh sửa
                    </a>
                    <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Info -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12">
            <div class="card">
                <div class="card-body text-center">
                    @if($test->image)
                        <img src="{{ Storage::url($test->image) }}" 
                             alt="{{ $test->name }}" 
                             class="img-fluid rounded mb-3">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    <h5 class="mb-2">{{ $test->name }}</h5>
                    <span class="badge bg-secondary mb-2">Thứ tự: {{ $test->order }}</span>
                    @if($test->description)
                        <p class="text-muted small mt-2">{{ $test->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-lg-9 col-md-6 col-12">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $test->skills->count() }}</h4>
                                    <p class="mb-0">Tổng số Skill</p>
                                </div>
                                <div>
                                    <i class="bi bi-grid" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">
                                        {{ $test->skills->sum(function($skill) { 
                                            return $skill->sections->count(); 
                                        }) }}
                                    </h4>
                                    <p class="mb-0">Tổng số Section</p>
                                </div>
                                <div>
                                    <i class="bi bi-layers" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">
                                        {{ $test->skills->sum(function($skill) { 
                                            return $skill->sections->sum(function($section) { 
                                                return $section->questionGroups->sum(function($group) { 
                                                    return $group->questions->count(); 
                                                }); 
                                            }); 
                                        }) }}
                                    </h4>
                                    <p class="mb-0">Tổng số Câu hỏi</p>
                                </div>
                                <div>
                                    <i class="bi bi-question-circle" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Exam: {{ $exam->name }}</h5>
                </div>
                <div class="card-body">
                    <span class="badge 
                        @if($exam->type == 'ielts') bg-primary
                        @elseif($exam->type == 'toeic') bg-success
                        @else bg-info
                        @endif">
                        {{ strtoupper($exam->type) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Skills List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách Skill</h5>
                    <a href="{{ route('admin.exams.tests.skills.create', [$exam, $test]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Thêm Skill
                    </a>
                </div>
                <div class="card-body">
                    @forelse($test->skills as $skill)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        @if($skill->skill_type == 'reading')
                                            <i class="bi bi-book text-primary me-2"></i>
                                        @elseif($skill->skill_type == 'writing')
                                            <i class="bi bi-pencil-square text-success me-2"></i>
                                        @elseif($skill->skill_type == 'listening')
                                            <i class="bi bi-headphones text-info me-2"></i>
                                        @else
                                            <i class="bi bi-mic text-warning me-2"></i>
                                        @endif
                                        {{ $skill->name }}
                                    </h6>
                                    <small class="text-muted">
                                        Thời gian: {{ $skill->time_limit }} phút | 
                                        Thứ tự: {{ $skill->order }} |
                                        {{ $skill->sections->count() }} Section
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('admin.exams.tests.skills.show', [$exam, $test, $skill]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.tests.skills.edit', [$exam, $test, $skill]) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($skill->sections->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Section</th>
                                            <th>Tiêu đề</th>
                                            <th>Format</th>
                                            <th>Câu hỏi</th>
                                            <th width="100">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($skill->sections as $section)
                                        <tr>
                                            <td><span class="badge bg-secondary">Part {{ $section->order }}</span></td>
                                            <td>{{ $section->title }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ ucfirst($section->content_format) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $section->questionGroups->sum(function($group) { 
                                                        return $group->questions->count(); 
                                                    }) }} câu
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.exams.tests.skills.sections.show', [$exam, $test, $skill, $section]) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted mb-0">Chưa có section nào</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Chưa có skill nào</p>
                        <a href="{{ route('admin.exams.tests.skills.create', [$exam, $test]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Thêm Skill Đầu Tiên
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thao tác</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.exams.tests.duplicate', [$exam, $test]) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info" 
                                    onclick="return confirm('Bạn có chắc muốn sao chép test này?')">
                                <i class="bi bi-files me-2"></i>Sao chép Test
                            </button>
                        </form>

                        <form action="{{ route('admin.exams.tests.destroy', [$exam, $test]) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Bạn có chắc muốn xóa test này? Tất cả skill, section và câu hỏi sẽ bị xóa!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-2"></i>Xóa Test
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
