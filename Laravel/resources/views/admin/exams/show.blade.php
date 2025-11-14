@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1 h2 fw-bold">{{ $exam->name }}</h1>
            
                </div>
                <div>
                    <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-warning me-2">
                        <i class="bi bi-pencil me-2"></i>Chỉnh sửa
                    </a>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Info -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12">
            <div class="card">
                <div class="card-body text-center">
                    @if($exam->image)
                        <img src="{{ Storage::url($exam->image) }}" 
                             alt="{{ $exam->name }}" 
                             class="img-fluid rounded mb-3">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    <h5 class="mb-2">{{ $exam->name }}</h5>
                    <span class="badge 
                        @if($exam->type == 'ielts') bg-primary
                        @elseif($exam->type == 'toeic') bg-success
                        @else bg-info
                        @endif mb-2">
                        {{ strtoupper($exam->type) }}
                    </span>
                    <span class="badge {{ $exam->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $exam->is_active ? 'Hoạt động' : 'Tắt' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-lg-9 col-md-6 col-12">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $exam->tests->count() }}</h4>
                                    <p class="mb-0">Tổng số Test</p>
                                </div>
                                <div>
                                    <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12 col-12 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $exam->tests->sum(function($test) { return $test->skills->count(); }) }}</h4>
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
                                        {{ $exam->tests->sum(function($test) { 
                                            return $test->skills->sum(function($skill) { 
                                                return $skill->sections->count(); 
                                            }); 
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
            </div>

            <!-- Description -->
            @if($exam->description)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Mô tả</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $exam->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Tests List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách Test</h5>
                    <a href="{{ route('admin.exams.tests.create', $exam) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Thêm Test
                    </a>
                </div>
                <div class="card-body">
                    @forelse($exam->tests as $test)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                        {{ $test->name }}
                                    </h6>
                                    @if($test->description)
                                        <small class="text-muted">{{ $test->description }}</small>
                                    @endif
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('admin.exams.tests.show', [$exam, $test]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.tests.edit', [$exam, $test]) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @forelse($test->skills as $skill)
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($skill->skill_type == 'reading')
                                                <i class="bi bi-book text-primary fs-4 me-2"></i>
                                            @elseif($skill->skill_type == 'writing')
                                                <i class="bi bi-pencil-square text-success fs-4 me-2"></i>
                                            @elseif($skill->skill_type == 'listening')
                                                <i class="bi bi-headphones text-info fs-4 me-2"></i>
                                            @else
                                                <i class="bi bi-mic text-warning fs-4 me-2"></i>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $skill->name }}</h6>
                                                <small class="text-muted">{{ $skill->time_limit }} phút</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary">{{ $skill->sections->count() }} Section</span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12">
                                    <p class="text-muted mb-0">Chưa có skill nào</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Chưa có test nào</p>
                        <a href="{{ route('admin.exams.tests.create', $exam) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Thêm Test Đầu Tiên
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
