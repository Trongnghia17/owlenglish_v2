@extends('layouts.app')

@section('content')
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-1 h2 fw-bold">Chấm Writing IELTS</h1>
                        <p class="mb-0 text-muted">Quản lý bài Writing học sinh đã nộp và phản hồi sau khi giáo viên chấm.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.writing-submissions.index') }}" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Mã bài, học sinh, email, đề thi..."
                                    value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ chấm</option>
                                    <option value="graded" {{ $status === 'graded' ? 'selected' : '' }}>Đã chấm</option>
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i>Lọc
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('admin.writing-submissions.index') }}" class="btn btn-outline-secondary w-100">
                                    Làm mới
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã bài</th>
                                        <th>Học sinh</th>
                                        <th>Đề Writing</th>
                                        <th class="text-center">Thời gian nộp</th>
                                        <th class="text-center">Band</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submissions as $submission)
                                        @php
                                            $skill = $submission->skill ?? $submission->section?->examSkill;
                                            $title = $submission->test?->name ?? $skill?->name ?? $submission->section?->title ?? 'Writing Test';
                                            $isGraded = !empty($submission->writing_feedback);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">#{{ $submission->id }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $submission->user?->name ?? 'Chưa có tên' }}</div>
                                                <div class="text-muted small">{{ $submission->user?->email ?? $submission->user?->phone }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $title }}</div>
                                                <div class="text-muted small">{{ $submission->section?->title ?? ucfirst($skill?->skill_type ?? 'writing') }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div>{{ $submission->created_at?->format('d/m/Y') }}</div>
                                                <div class="text-muted small">{{ $submission->created_at?->format('H:i') }}</div>
                                            </td>
                                            <td class="text-center">
                                                @if($isGraded)
                                                    <span class="badge bg-primary">{{ number_format((float) $submission->score, 1) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($isGraded)
                                                    <span class="badge bg-success">Đã chấm</span>
                                                    <div class="text-muted small">{{ $submission->graded_at?->format('d/m/Y H:i') }}</div>
                                                @else
                                                    <span class="badge bg-warning text-dark">Chờ chấm</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.writing-submissions.edit', $submission) }}"
                                                    class="btn btn-sm {{ $isGraded ? 'btn-outline-primary' : 'btn-primary' }}">
                                                    <i class="bi bi-pencil-square me-1"></i>{{ $isGraded ? 'Sửa chấm' : 'Chấm bài' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0">Không có bài Writing phù hợp.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($submissions->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $submissions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
