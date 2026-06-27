@extends('layouts.app')

@section('content')
    @php
        $taskRows = old('tasks', $tasks);
        $skill = $submission->skill ?? $submission->section?->examSkill;
        $title = $submission->test?->name ?? $skill?->name ?? $submission->section?->title ?? 'Writing Test';
        $overallScore = $feedback['overall_score'];
        $rawOverallScore = $feedback['raw_overall_score'];
    @endphp

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-1 h2 fw-bold">Chấm bài Writing #{{ $submission->id }}</h1>
                        <p class="mb-0 text-muted">{{ $title }} - {{ $submission->user?->name ?? 'Học sinh' }}</p>
                    </div>
                    <a href="{{ route('admin.writing-submissions.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.writing-submissions.update', $submission) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-xl-5">
                    <div class="card writing-grade-sticky">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="h5 mb-1">Bài làm của học sinh</h2>
                                    <div class="text-muted small">
                                        Nộp lúc {{ $submission->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ count($taskRows) }} task</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills writing-task-preview-tabs mb-3" role="tablist">
                                @foreach($taskRows as $index => $task)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="preview-tab-{{ $index }}"
                                            data-bs-toggle="pill" data-bs-target="#preview-panel-{{ $index }}" type="button"
                                            role="tab" aria-controls="preview-panel-{{ $index }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            {{ $task['title'] ?? 'Writing Task ' . ($index + 1) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content">
                                @foreach($taskRows as $index => $task)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="preview-panel-{{ $index }}" role="tabpanel"
                                        aria-labelledby="preview-tab-{{ $index }}" tabindex="0">
                                        <div class="writing-essay-meta">
                                            <span>{{ $task['word_count'] ?? 0 }} từ</span>
                                            <span>Task {{ $task['task_number'] ?? ($index + 1) }}</span>
                                        </div>
                                        @if(!empty($task['prompt']))
                                            <div class="writing-prompt-preview">
                                                {!! $task['prompt'] !!}
                                            </div>
                                        @endif
                                        <div class="writing-essay-preview">
                                            {!! nl2br(e($task['answer'] ?? 'Học sinh chưa có nội dung bài viết.')) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0">Writing Overall</h2>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="writing-overall-score">
                                        <span>Overall</span>
                                        <strong>{{ is_numeric($overallScore) ? number_format((float) $overallScore, 1) : '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <p class="mb-2 fw-semibold">Công thức hệ thống</p>
                                    <p class="mb-1 text-muted">
                                        Task score = (TA/TR + CC + LR + GRA) / 4.
                                    </p>
                                    <p class="mb-1 text-muted">
                                        Writing Overall = (Task 1 + Task 2 * 2) / 3, sau đó làm tròn về .0 hoặc .5 theo ngưỡng 0.25/0.75.
                                    </p>
                                    @if(is_numeric($rawOverallScore))
                                        <p class="mb-0 small text-primary">
                                            Điểm thô lần lưu trước: {{ number_format((float) $rawOverallScore, 4) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0">Nhận xét chung cho toàn bài Writing</h2>
                        </div>
                        <div class="card-body">
                            <textarea name="teacher_note" class="form-control" rows="4"
                                placeholder="Lời nhắn chung cho học sinh sau khi hoàn thành cả Task 1 và Task 2">{{ old('teacher_note', $feedback['teacher_note']) }}</textarea>
                        </div>
                    </div>

                    <div class="accordion writing-task-grading" id="taskGradingAccordion">
                        @foreach($taskRows as $taskIndex => $task)
                            @php
                                $taskTitle = $task['title'] ?? 'Writing Task ' . ($taskIndex + 1);
                                $taskDetails = $task['details'] ?? [
                                    ['original' => '', 'explanation' => '', 'correction' => ''],
                                    ['original' => '', 'explanation' => '', 'correction' => ''],
                                ];
                            @endphp
                            <div class="accordion-item mb-4">
                                <h2 class="accordion-header" id="task-heading-{{ $taskIndex }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#task-collapse-{{ $taskIndex }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="task-collapse-{{ $taskIndex }}">
                                        {{ $taskTitle }}
                                    </button>
                                </h2>
                                <div id="task-collapse-{{ $taskIndex }}"
                                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="task-heading-{{ $taskIndex }}" data-bs-parent="#taskGradingAccordion">
                                    <div class="accordion-body">
                                        <input type="hidden" name="tasks[{{ $taskIndex }}][key]" value="{{ $task['key'] ?? 'task:' . ($taskIndex + 1) }}">
                                        <input type="hidden" name="tasks[{{ $taskIndex }}][title]" value="{{ $taskTitle }}">
                                        <input type="hidden" name="tasks[{{ $taskIndex }}][task_number]" value="{{ $task['task_number'] ?? ($taskIndex + 1) }}">
                                        <input type="hidden" name="tasks[{{ $taskIndex }}][section_id]" value="{{ $task['section_id'] ?? '' }}">
                                        <input type="hidden" name="tasks[{{ $taskIndex }}][question_id]" value="{{ $task['question_id'] ?? '' }}">
                                        <textarea name="tasks[{{ $taskIndex }}][prompt]" class="d-none">{{ $task['prompt'] ?? '' }}</textarea>
                                        <textarea name="tasks[{{ $taskIndex }}][answer]" class="d-none">{{ $task['answer'] ?? '' }}</textarea>

                                        <div class="row g-3 mb-4">
                                            @foreach($criteria as $scoreKey => $scoreLabel)
                                                <div class="col-md-3">
                                                    <label class="form-label">{{ $scoreLabel }}</label>
                                                    <input type="number"
                                                        name="tasks[{{ $taskIndex }}][scores][{{ $scoreKey }}]"
                                                        class="form-control"
                                                        min="0" max="9" step="0.5" inputmode="decimal"
                                                        value="{{ $task['scores'][$scoreKey] ?? '' }}"
                                                        placeholder="0.0-9.0"
                                                        required>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="alert alert-light border">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Hệ thống chỉ nhận điểm .0 hoặc .5. Điểm Task và Writing Overall sẽ được tự tính khi lưu.
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Nhận xét riêng cho {{ $taskTitle }}</label>
                                            <textarea name="tasks[{{ $taskIndex }}][teacher_note]" class="form-control" rows="3"
                                                placeholder="Nhận xét tổng quan riêng cho task này">{{ $task['teacher_note'] ?? '' }}</textarea>
                                        </div>

                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h3 class="h6 mb-0">Nhận xét theo tiêu chí của {{ $taskTitle }}</h3>
                                            </div>
                                            <div class="card-body">
                                                @foreach($criteriaTitles as $criterionKey => $criterionTitle)
                                                    @php $criterion = $task['criteria'][$criterionKey] ?? []; @endphp
                                                    <div class="writing-criterion-block">
                                                        <h4>{{ $criterionTitle }}</h4>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label text-success fw-semibold">Điểm mạnh</label>
                                                                <textarea name="tasks[{{ $taskIndex }}][criteria][{{ $criterionKey }}][strengths]"
                                                                    class="form-control" rows="3"
                                                                    placeholder="Ví dụ: Bài có cấu trúc rõ, bao quát ý chính...">{{ $criterion['strengths'] ?? '' }}</textarea>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label text-danger fw-semibold">Điểm yếu</label>
                                                                <textarea name="tasks[{{ $taskIndex }}][criteria][{{ $criterionKey }}][weaknesses]"
                                                                    class="form-control" rows="3"
                                                                    placeholder="Ví dụ: Ý còn mâu thuẫn, thiếu ví dụ...">{{ $criterion['weaknesses'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h3 class="h6 mb-0">Lỗi chi tiết của {{ $taskTitle }}</h3>
                                                <button type="button" class="btn btn-sm btn-outline-primary add-detail-row"
                                                    data-task-index="{{ $taskIndex }}">
                                                    <i class="bi bi-plus-circle me-1"></i>Thêm lỗi
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="detail-rows d-flex flex-column gap-3" data-task-index="{{ $taskIndex }}">
                                                    @foreach($taskDetails as $detailIndex => $detail)
                                                        <div class="detail-row border rounded p-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div class="fw-semibold detail-row-title">Lỗi #{{ $detailIndex + 1 }}</div>
                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-detail-row">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                            <label class="form-label">Câu/đoạn cần highlight</label>
                                                            <textarea name="tasks[{{ $taskIndex }}][details][{{ $detailIndex }}][original]"
                                                                data-name="original" class="form-control mb-3" rows="2"
                                                                placeholder="Nhập đúng đoạn trong bài viết để học sinh thấy highlight đỏ">{{ $detail['original'] ?? '' }}</textarea>

                                                            <label class="form-label">Giải thích lỗi</label>
                                                            <textarea name="tasks[{{ $taskIndex }}][details][{{ $detailIndex }}][explanation]"
                                                                data-name="explanation" class="form-control mb-3" rows="3"
                                                                placeholder="Vì sao sai?">{{ $detail['explanation'] ?? '' }}</textarea>

                                                            <label class="form-label">Cách sửa</label>
                                                            <textarea name="tasks[{{ $taskIndex }}][details][{{ $detailIndex }}][correction]"
                                                                data-name="correction" class="form-control" rows="2"
                                                                placeholder="Gợi ý sửa câu">{{ $detail['correction'] ?? '' }}</textarea>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <a href="{{ route('admin.writing-submissions.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Lưu kết quả chấm
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="detail-row-template">
        <div class="detail-row border rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold detail-row-title">Lỗi</div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-detail-row">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <label class="form-label">Câu/đoạn cần highlight</label>
            <textarea data-name="original" class="form-control mb-3" rows="2"
                placeholder="Nhập đúng đoạn trong bài viết để học sinh thấy highlight đỏ"></textarea>

            <label class="form-label">Giải thích lỗi</label>
            <textarea data-name="explanation" class="form-control mb-3" rows="3"
                placeholder="Vì sao sai?"></textarea>

            <label class="form-label">Cách sửa</label>
            <textarea data-name="correction" class="form-control" rows="2"
                placeholder="Gợi ý sửa câu"></textarea>
        </div>
    </template>

    @push('styles')
        <style>
            .writing-grade-sticky {
                position: sticky;
                top: 96px;
            }

            .writing-task-preview-tabs {
                gap: 8px;
            }

            .writing-task-preview-tabs .nav-link {
                border: 1px solid #e7e7e7;
                border-radius: 5px;
                color: #454545;
                font-weight: 600;
            }

            .writing-task-preview-tabs .nav-link.active {
                border-color: #045cce;
                background: rgba(4, 92, 206, 0.07);
                color: #045cce;
            }

            .writing-essay-meta {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 10px;
                color: #6b7280;
                font-size: 13px;
                font-weight: 600;
            }

            .writing-essay-preview {
                min-height: 520px;
                max-height: calc(100vh - 260px);
                overflow: auto;
                padding: 16px;
                border-radius: 12px;
                background: #f8fafc;
                color: #1f2937;
                font-size: 14px;
                line-height: 1.7;
                white-space: pre-wrap;
            }

            .writing-prompt-preview {
                margin-bottom: 10px;
                padding: 14px 16px;
                border-radius: 12px;
                background: #fff;
                color: #0a0a0a;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.6;
                white-space: pre-wrap;
            }

            .writing-prompt-preview img {
                display: block;
                max-width: 100%;
                height: auto;
                margin: 0 auto 12px;
                border-radius: 8px;
                object-fit: contain;
            }

            .writing-overall-score {
                display: flex;
                min-height: 110px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                border: 1px solid #dbeafe;
                border-radius: 12px;
                background: #eff6ff;
                color: #2563eb;
            }

            .writing-overall-score span {
                font-size: 14px;
            }

            .writing-overall-score strong {
                font-size: 42px;
                line-height: 1;
            }

            .writing-task-grading .accordion-button {
                background: rgba(4, 92, 206, 0.07);
                color: #0537a5;
                font-weight: 700;
            }

            .writing-criterion-block {
                padding-bottom: 18px;
                margin-bottom: 18px;
                border-bottom: 1px solid #e7e7e7;
            }

            .writing-criterion-block:last-child {
                padding-bottom: 0;
                margin-bottom: 0;
                border-bottom: 0;
            }

            .writing-criterion-block h4 {
                margin-bottom: 12px;
                color: #0a0a0a;
                font-size: 15px;
                font-weight: 700;
            }

            @media (max-width: 1199.98px) {
                .writing-grade-sticky {
                    position: static;
                }

                .writing-essay-preview {
                    min-height: 320px;
                    max-height: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const template = document.getElementById('detail-row-template');

                const refreshRows = (rowsContainer) => {
                    const taskIndex = rowsContainer.dataset.taskIndex;
                    rowsContainer.querySelectorAll('.detail-row').forEach((row, detailIndex) => {
                        const title = row.querySelector('.detail-row-title');
                        if (title) {
                            title.textContent = `Lỗi #${detailIndex + 1}`;
                        }

                        row.querySelectorAll('[data-name]').forEach((field) => {
                            field.name = `tasks[${taskIndex}][details][${detailIndex}][${field.dataset.name}]`;
                        });
                    });
                };

                document.querySelectorAll('.detail-rows').forEach(refreshRows);

                document.addEventListener('click', function (event) {
                    const addButton = event.target.closest('.add-detail-row');
                    if (addButton) {
                        const rowsContainer = document.querySelector(`.detail-rows[data-task-index="${addButton.dataset.taskIndex}"]`);
                        const clone = template.content.firstElementChild.cloneNode(true);
                        rowsContainer.appendChild(clone);
                        refreshRows(rowsContainer);
                        return;
                    }

                    const removeButton = event.target.closest('.remove-detail-row');
                    if (!removeButton) return;

                    const rowsContainer = removeButton.closest('.detail-rows');
                    const rows = rowsContainer.querySelectorAll('.detail-row');
                    if (rows.length === 1) {
                        removeButton.closest('.detail-row').querySelectorAll('textarea').forEach((textarea) => {
                            textarea.value = '';
                        });
                        return;
                    }

                    removeButton.closest('.detail-row').remove();
                    refreshRows(rowsContainer);
                });
            });
        </script>
    @endpush
@endsection
