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

            <div class="writing-admin-dashboard mb-4">
                <div class="writing-admin-score-grid">
                    <div class="writing-admin-score-card writing-admin-score-card--primary">
                        <span>Writing Overall</span>
                        <strong data-overall-score-preview>{{ is_numeric($overallScore) ? number_format((float) $overallScore, 1) : '-' }}</strong>
                        <small data-overall-raw-preview>
                            {{ is_numeric($rawOverallScore) ? 'Raw ' . number_format((float) $rawOverallScore, 4) : 'Tự tính khi nhập điểm' }}
                        </small>
                    </div>
                    <div class="writing-admin-formula-card">
                        <div class="writing-admin-formula-title">Công thức chấm</div>
                        <p>Task score = (TA/TR + CC + LR + GRA) / 4.</p>
                        <p>Writing Overall = (Task 1 + Task 2 x 2) / 3, làm tròn về .0 hoặc .5.</p>
                    </div>
                    <div class="writing-admin-note-card">
                        <label class="form-label fw-semibold" for="teacher_note">Nhận xét chung cho toàn bài</label>
                        <textarea id="teacher_note" name="teacher_note" class="form-control" rows="3"
                            placeholder="Lời nhắn chung cho học sinh sau khi hoàn thành cả Task 1 và Task 2">{{ old('teacher_note', $feedback['teacher_note']) }}</textarea>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills writing-task-tabs" role="tablist">
                @foreach($taskRows as $taskIndex => $task)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="task-tab-{{ $taskIndex }}"
                            data-bs-toggle="pill"
                            data-bs-target="#task-panel-{{ $taskIndex }}"
                            type="button"
                            role="tab"
                            aria-controls="task-panel-{{ $taskIndex }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $task['title'] ?? 'Writing Task ' . ($taskIndex + 1) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content writing-task-content">
                @foreach($taskRows as $taskIndex => $task)
                    @php
                        $taskTitle = $task['title'] ?? 'Writing Task ' . ($taskIndex + 1);
                        $taskDetails = $task['details'] ?? [
                            ['original' => '', 'explanation' => '', 'correction' => ''],
                            ['original' => '', 'explanation' => '', 'correction' => ''],
                        ];
                        $taskNumber = $task['task_number'] ?? ($taskIndex + 1);
                        $taskScore = $task['rounded_task_score'] ?? ($task['scores']['rounded_task_score'] ?? null);
                        $taskRawScore = $task['raw_task_score'] ?? ($task['scores']['raw_task_score'] ?? null);
                    @endphp
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                        id="task-panel-{{ $taskIndex }}"
                        role="tabpanel"
                        aria-labelledby="task-tab-{{ $taskIndex }}"
                        tabindex="0">
                        <div class="row g-4 writing-admin-workspace">
                            <div class="col-xl-5">
                                <aside class="writing-admin-panel writing-grade-sticky">
                                    <div class="writing-admin-panel-header">
                                        <div>
                                            <h2>Bài làm của học sinh</h2>
                                            <p>Nộp lúc {{ $submission->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <span>{{ $task['word_count'] ?? 0 }} từ</span>
                                    </div>
                                    <div class="writing-preview-content">
                                        <div class="writing-essay-meta">
                                            <span>Đề bài</span>
                                            <span>Task {{ $taskNumber }}</span>
                                        </div>
                                        @if(!empty($task['prompt']))
                                            <div class="writing-prompt-preview">
                                                {!! $task['prompt'] !!}
                                            </div>
                                        @endif
                                        <div class="writing-essay-label">Bài viết của học sinh</div>
                                        <div class="writing-essay-preview">
                                            {!! nl2br(e($task['answer'] ?? 'Học sinh chưa có nội dung bài viết.')) !!}
                                        </div>
                                    </div>
                                </aside>
                            </div>

                            <div class="col-xl-7">
                                <section class="writing-task-card" data-task-card data-task-number="{{ $taskNumber }}">
                                    <div class="writing-task-card-header">
                                        <div>
                                            <span>Task {{ $taskNumber }}</span>
                                            <h2>{{ $taskTitle }}</h2>
                                        </div>
                                    </div>

                                    <input type="hidden" name="tasks[{{ $taskIndex }}][key]" value="{{ $task['key'] ?? 'task:' . ($taskIndex + 1) }}">
                                    <input type="hidden" name="tasks[{{ $taskIndex }}][title]" value="{{ $taskTitle }}">
                                    <input type="hidden" name="tasks[{{ $taskIndex }}][task_number]" value="{{ $taskNumber }}">
                                    <input type="hidden" name="tasks[{{ $taskIndex }}][section_id]" value="{{ $task['section_id'] ?? '' }}">
                                    <input type="hidden" name="tasks[{{ $taskIndex }}][question_id]" value="{{ $task['question_id'] ?? '' }}">
                                    <textarea name="tasks[{{ $taskIndex }}][prompt]" class="d-none">{{ $task['prompt'] ?? '' }}</textarea>
                                    <textarea name="tasks[{{ $taskIndex }}][answer]" class="d-none">{{ $task['answer'] ?? '' }}</textarea>

                                    <div class="writing-task-score-grid">
                                        <div class="writing-task-score-total">
                                            <span>Task Band</span>
                                            <strong class="task-score-value">{{ is_numeric($taskScore) ? number_format((float) $taskScore, 1) : '-' }}</strong>
                                            <small class="task-raw-score">
                                                {{ is_numeric($taskRawScore) ? 'Raw ' . number_format((float) $taskRawScore, 4) : 'Chưa đủ 4 tiêu chí' }}
                                            </small>
                                        </div>

                                        @foreach($criteria as $scoreKey => $scoreLabel)
                                            <label class="writing-score-field">
                                                <span>{{ $scoreLabel }}</span>
                                                <input type="number"
                                                    name="tasks[{{ $taskIndex }}][scores][{{ $scoreKey }}]"
                                                    class="form-control writing-score-input"
                                                    min="0" max="9" step="0.5" inputmode="decimal"
                                                    value="{{ $task['scores'][$scoreKey] ?? '' }}"
                                                    placeholder="0.0"
                                                    required>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="writing-grade-section">
                                        <div class="writing-grade-section-header writing-grade-section-header--collapsible">
                                            <div>
                                                <span>1. Overall</span>
                                                <small>Nhận xét tổng quan và 4 tiêu chí IELTS Writing</small>
                                            </div>
                                            <button type="button" class="writing-section-toggle toggle-grade-section"
                                                aria-expanded="true" aria-label="Thu gọn phần Overall"
                                                title="Thu gọn">
                                                <i class="bi bi-chevron-up"></i>
                                            </button>
                                        </div>

                                        <div class="writing-grade-section-body">
                                            <div class="writing-task-note-editor">
                                                <label class="form-label fw-semibold">Nhận xét riêng cho {{ $taskTitle }}</label>
                                                <textarea name="tasks[{{ $taskIndex }}][teacher_note]" class="form-control" rows="3"
                                                    placeholder="Nhận xét tổng quan riêng cho task này">{{ $task['teacher_note'] ?? '' }}</textarea>
                                            </div>

                                            <div class="writing-criteria-list">
                                                @foreach($criteriaTitles as $criterionKey => $criterionTitle)
                                                    @php $criterion = $task['criteria'][$criterionKey] ?? []; @endphp
                                                    <div class="writing-criterion-card">
                                                        <h3>{{ $criterionTitle }}</h3>
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
                                    </div>

                                    <div class="writing-grade-section">
                                        <div class="writing-grade-section-header writing-grade-section-header--detail">
                                            <div>
                                                <span>2. Detail</span>
                                                <small>Câu/đoạn cần highlight, giải thích lỗi và cách sửa</small>
                                            </div>
                                            <div class="writing-grade-section-actions">
                                                <button type="button" class="btn btn-sm btn-outline-primary add-detail-row"
                                                    data-task-index="{{ $taskIndex }}">
                                                    <i class="bi bi-plus-circle me-1"></i>Thêm lỗi
                                                </button>
                                                <button type="button" class="writing-section-toggle toggle-grade-section"
                                                    aria-expanded="true" aria-label="Thu gọn phần Detail"
                                                    title="Thu gọn">
                                                    <i class="bi bi-chevron-up"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="writing-grade-section-body">
                                            <div class="detail-rows writing-detail-list" data-task-index="{{ $taskIndex }}">
                                                @foreach($taskDetails as $detailIndex => $detail)
                                                    <div class="detail-row writing-detail-card">
                                                        <div class="writing-detail-card-header">
                                                            <div class="detail-row-title">Lỗi #{{ $detailIndex + 1 }}</div>
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
                                </section>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="writing-admin-actions">
                <a href="{{ route('admin.writing-submissions.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Lưu kết quả chấm
                </button>
            </div>
        </form>
    </div>

    <template id="detail-row-template">
        <div class="detail-row writing-detail-card">
            <div class="writing-detail-card-header">
                <div class="detail-row-title">Lỗi</div>
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
            .writing-admin-dashboard,
            .writing-admin-panel,
            .writing-task-card {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1), 0 1px 2px rgba(15, 23, 42, 0.06);
            }

            .writing-admin-dashboard {
                padding: 16px;
            }

            .writing-admin-score-grid {
                display: grid;
                grid-template-columns: minmax(180px, 220px) minmax(260px, 1fr) minmax(320px, 1.2fr);
                gap: 16px;
                align-items: stretch;
            }

            .writing-admin-score-card,
            .writing-admin-formula-card,
            .writing-admin-note-card {
                min-width: 0;
                border-radius: 10px;
            }

            .writing-admin-score-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 6px;
                min-height: 132px;
                border: 1px solid #dbeafe;
                background: #eff6ff;
                color: #2563eb;
                text-align: center;
            }

            .writing-admin-score-card span,
            .writing-task-score-total span {
                font-size: 14px;
                font-weight: 600;
            }

            .writing-admin-score-card strong,
            .writing-task-score-total strong {
                font-size: 42px;
                font-weight: 800;
                line-height: 1;
            }

            .writing-admin-score-card small,
            .writing-task-score-total small {
                color: #4b5563;
                font-size: 12px;
                font-weight: 600;
            }

            .writing-admin-formula-card {
                padding: 16px;
                border: 1px solid #e2e8f0;
                background: #f8fafc;
            }

            .writing-admin-formula-title {
                margin-bottom: 8px;
                color: #0f172a;
                font-size: 15px;
                font-weight: 800;
            }

            .writing-admin-formula-card p {
                margin: 0 0 6px;
                color: #4b5563;
                font-size: 14px;
                line-height: 1.55;
            }

            .writing-admin-formula-card p:last-child {
                margin-bottom: 0;
            }

            .writing-admin-note-card {
                padding: 14px 16px;
                border: 1px solid #dbeafe;
                background: #eff6ff;
            }

            .writing-admin-note-card textarea {
                min-height: 86px;
                resize: vertical;
            }

            .writing-admin-workspace {
                align-items: flex-start;
            }

            .writing-grade-sticky {
                position: sticky;
                top: 96px;
            }

            .writing-admin-panel {
                overflow: hidden;
            }

            .writing-admin-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 16px 18px;
                border-bottom: 1px solid #e2e8f0;
                background: #f8fafc;
            }

            .writing-admin-panel-header h2 {
                margin: 0 0 4px;
                color: #0f172a;
                font-size: 18px;
                font-weight: 800;
            }

            .writing-admin-panel-header p {
                margin: 0;
                color: #64748b;
                font-size: 13px;
                font-weight: 600;
            }

            .writing-admin-panel-header span {
                display: inline-flex;
                align-items: center;
                min-height: 30px;
                padding: 4px 10px;
                border: 1px solid #dbeafe;
                border-radius: 999px;
                background: #eff6ff;
                color: #045cce;
                font-size: 13px;
                font-weight: 700;
                white-space: nowrap;
            }

            .writing-preview-content {
                padding: 16px 18px 18px;
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

            .writing-essay-label {
                margin: 16px 0 8px;
                color: #0f172a;
                font-size: 14px;
                font-weight: 800;
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
                white-space: normal;
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

            .writing-prompt-preview p {
                margin: 0 0 8px;
            }

            .writing-prompt-preview p:last-child {
                margin-bottom: 0;
            }

            .writing-prompt-preview img {
                display: block;
                max-width: 100%;
                height: auto;
                margin: 0 auto 12px;
                border-radius: 8px;
                object-fit: contain;
            }

            .writing-prompt-preview table {
                width: 100%;
                border-collapse: collapse;
            }

            .writing-prompt-preview th,
            .writing-prompt-preview td {
                padding: 8px;
                border: 1px solid #e2e8f0;
                vertical-align: top;
            }

            .writing-task-tabs {
                gap: 8px;
                margin-bottom: 12px;
                padding: 8px;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #f8fafc;
            }

            .writing-task-tabs .nav-link {
                min-width: 130px;
                border: 1px solid transparent;
                border-radius: 8px;
                color: #4b5563;
                font-weight: 700;
            }

            .writing-task-tabs .nav-link.active {
                border-color: #dbeafe;
                background: #ffffff;
                color: #045cce;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            }

            .writing-task-content > .tab-pane {
                outline: none;
            }

            .writing-task-card {
                overflow: hidden;
            }

            .writing-task-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 16px 18px;
                border-bottom: 1px solid #dbeafe;
                background: rgba(4, 92, 206, 0.07);
            }

            .writing-task-card-header span {
                display: block;
                margin-bottom: 2px;
                color: #045cce;
                font-size: 13px;
                font-weight: 800;
            }

            .writing-task-card-header h2 {
                margin: 0;
                color: #0537a5;
                font-size: 18px;
                font-weight: 800;
            }

            .writing-task-score-grid {
                display: grid;
                grid-template-columns: minmax(150px, 180px) repeat(4, minmax(0, 1fr));
                gap: 12px;
                padding: 16px 18px;
                border-bottom: 1px solid #e2e8f0;
            }

            .writing-task-score-total,
            .writing-score-field {
                min-width: 0;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #ffffff;
            }

            .writing-task-score-total {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                min-height: 104px;
                border-color: #dbeafe;
                background: #eff6ff;
                color: #2563eb;
                text-align: center;
            }

            .writing-score-field {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 10px;
                min-height: 104px;
                margin: 0;
                padding: 12px;
                background: #f8fafc;
            }

            .writing-score-field span {
                color: #1f2937;
                font-size: 14px;
                font-weight: 800;
                line-height: 1.2;
            }

            .writing-score-field input {
                height: 42px;
                font-size: 18px;
                font-weight: 800;
                text-align: center;
            }

            .writing-grade-section {
                padding: 18px;
                border-bottom: 1px solid #e2e8f0;
            }

            .writing-grade-section:last-child {
                border-bottom: 0;
            }

            .writing-grade-section-header {
                margin-bottom: 14px;
                padding: 14px 16px;
                border-radius: 8px;
                background: rgba(4, 92, 206, 0.07);
                color: #0537a5;
            }

            .writing-grade-section-header span {
                display: block;
                font-size: 16px;
                font-weight: 800;
            }

            .writing-grade-section-header small {
                display: block;
                margin-top: 3px;
                color: #4b5563;
                font-weight: 600;
            }

            .writing-grade-section-header--detail {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .writing-grade-section-header--collapsible {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .writing-grade-section-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .writing-section-toggle {
                display: inline-flex;
                flex: 0 0 34px;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                padding: 0;
                border: 1px solid #bfdbfe;
                border-radius: 8px;
                background: #ffffff;
                color: #045cce;
            }

            .writing-section-toggle:hover {
                border-color: #045cce;
                background: #eff6ff;
            }

            .writing-grade-section.is-collapsed .writing-grade-section-header {
                margin-bottom: 0;
            }

            .writing-grade-section.is-collapsed .writing-grade-section-body {
                display: none;
            }

            .writing-task-note-editor {
                margin-bottom: 14px;
            }

            .writing-criteria-list,
            .writing-detail-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .writing-criterion-card {
                overflow: hidden;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #ffffff;
            }

            .writing-criterion-card h3 {
                margin: 0;
                padding: 12px 14px;
                border-bottom: 1px solid #e2e8f0;
                background: rgba(249, 250, 251, 0.8);
                color: #0f172a;
                font-size: 15px;
                font-weight: 800;
            }

            .writing-criterion-card .row {
                padding: 14px;
            }

            .writing-detail-card {
                padding: 14px;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #f8fafc;
            }

            .writing-detail-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
            }

            .detail-row-title {
                color: #0f172a;
                font-size: 15px;
                font-weight: 800;
            }

            .writing-admin-actions {
                position: sticky;
                bottom: 0;
                z-index: 2;
                display: flex;
                justify-content: flex-end;
                gap: 8px;
                margin-top: 18px;
                padding: 14px 0 0;
                background: linear-gradient(to bottom, rgba(255, 255, 255, 0), #ffffff 32%);
            }

            @media (max-width: 1199.98px) {
                .writing-admin-score-grid {
                    grid-template-columns: 1fr;
                }

                .writing-grade-sticky {
                    position: static;
                }

                .writing-essay-preview {
                    min-height: 320px;
                    max-height: none;
                }

                .writing-task-score-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767.98px) {
                .writing-task-score-grid {
                    grid-template-columns: 1fr;
                }

                .writing-grade-section-header--detail,
                .writing-task-card-header,
                .writing-admin-panel-header {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const template = document.getElementById('detail-row-template');
                const formatBand = (value) => Number.isFinite(value) ? value.toFixed(1) : '-';
                const formatRaw = (value) => Number.isFinite(value) ? `Raw ${value.toFixed(4)}` : 'Chưa đủ 4 tiêu chí';
                const roundIeltsBand = (score) => {
                    const safeScore = Math.max(0, Math.min(9, score));
                    const base = Math.floor(safeScore);
                    const fraction = safeScore - base;

                    if (fraction < 0.25) {
                        return base;
                    }

                    if (fraction < 0.75) {
                        return Math.min(9, base + 0.5);
                    }

                    return Math.min(9, base + 1);
                };

                const calculateTaskScore = (taskCard) => {
                    const inputs = Array.from(taskCard.querySelectorAll('.writing-score-input'));
                    const scores = inputs
                        .map((input) => Number.parseFloat(input.value))
                        .filter((value) => Number.isFinite(value));
                    const scoreValue = taskCard.querySelector('.task-score-value');
                    const rawValue = taskCard.querySelector('.task-raw-score');

                    if (scores.length !== inputs.length || scores.length === 0) {
                        taskCard.dataset.rawTaskScore = '';
                        if (scoreValue) scoreValue.textContent = '-';
                        if (rawValue) rawValue.textContent = 'Chưa đủ 4 tiêu chí';
                        return null;
                    }

                    const rawScore = scores.reduce((sum, value) => sum + value, 0) / scores.length;
                    const roundedScore = roundIeltsBand(rawScore);

                    taskCard.dataset.rawTaskScore = rawScore.toString();
                    if (scoreValue) scoreValue.textContent = formatBand(roundedScore);
                    if (rawValue) rawValue.textContent = formatRaw(rawScore);

                    return {
                        taskNumber: Number.parseInt(taskCard.dataset.taskNumber || '0', 10),
                        rawScore,
                    };
                };

                const calculateOverallScore = () => {
                    const taskScores = Array.from(document.querySelectorAll('[data-task-card]'))
                        .map(calculateTaskScore)
                        .filter(Boolean);
                    const scorePreview = document.querySelector('[data-overall-score-preview]');
                    const rawPreview = document.querySelector('[data-overall-raw-preview]');

                    if (taskScores.length === 0) {
                        if (scorePreview) scorePreview.textContent = '-';
                        if (rawPreview) rawPreview.textContent = 'Tự tính khi nhập điểm';
                        return;
                    }

                    let rawOverall = taskScores[0].rawScore;

                    if (taskScores.length > 1) {
                        const taskOne = taskScores.find((task) => task.taskNumber === 1) || taskScores[0];
                        const taskTwo = taskScores.find((task) => task.taskNumber === 2) || taskScores[1];
                        rawOverall = (taskOne.rawScore + (taskTwo.rawScore * 2)) / 3;
                    }

                    if (scorePreview) scorePreview.textContent = formatBand(roundIeltsBand(rawOverall));
                    if (rawPreview) rawPreview.textContent = Number.isFinite(rawOverall)
                        ? `Raw ${rawOverall.toFixed(4)}`
                        : 'Tự tính khi nhập điểm';
                };

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
                document.querySelectorAll('.writing-score-input').forEach((input) => {
                    input.addEventListener('input', calculateOverallScore);
                });
                calculateOverallScore();

                document.addEventListener('click', function (event) {
                    const toggleButton = event.target.closest('.toggle-grade-section');
                    if (toggleButton) {
                        const section = toggleButton.closest('.writing-grade-section');
                        const isCollapsed = section.classList.toggle('is-collapsed');
                        const icon = toggleButton.querySelector('i');

                        toggleButton.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                        toggleButton.setAttribute(
                            'aria-label',
                            isCollapsed ? 'Mở rộng phần chấm' : 'Thu gọn phần chấm'
                        );
                        toggleButton.title = isCollapsed ? 'Mở rộng' : 'Thu gọn';

                        if (icon) {
                            icon.classList.toggle('bi-chevron-up', !isCollapsed);
                            icon.classList.toggle('bi-chevron-down', isCollapsed);
                        }
                        return;
                    }

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
