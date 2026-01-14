@extends('layouts.app')

@section('content')
    <div class="app-content">
        <div class="container-fluid">

            <h3 class="mb-4">Thêm bộ lọc</h3>

            <div class="card">
                <div class="card-body">

                    <form method="POST" action="{{ route('admin.exam-filters.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tên</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Loại</label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Chọn --</option>
                                <option value="skill">Skill</option>
                                <option value="group">Group</option>
                                <option value="value">Value</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thuộc bộ lọc cha</label>
                            <select name="parent_id" class="form-select" id="parentSelect">
                                <option value="">-- Không có --</option>

                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" data-type="{{ $parent->type }}">
                                        {{ $parent->name }} ({{ ucfirst($parent->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const typeSelect = document.querySelector('select[name="type"]');
                                const parentSelect = document.getElementById('parentSelect');

                                function handleParentOptions() {
                                    const type = typeSelect.value;

                                    parentSelect.value = '';
                                    let hasVisibleOption = false;

                                    Array.from(parentSelect.options).forEach(option => {
                                        if (!option.value) {
                                            option.hidden = false;
                                            return;
                                        }

                                        const parentType = option.dataset.type;

                                        // Skill → không có cha
                                        if (type === 'skill') {
                                            option.hidden = true;
                                        }

                                        // Group → chỉ nhận Skill
                                        else if (type === 'group') {
                                            option.hidden = parentType !== 'skill';
                                        }

                                        // Value → chỉ nhận Group
                                        else if (type === 'value') {
                                            option.hidden = parentType !== 'group';
                                        }

                                        if (!option.hidden) {
                                            hasVisibleOption = true;
                                        }
                                    });

                                    // Nếu không có option hợp lệ → disable select
                                    parentSelect.disabled = !hasVisibleOption || type === 'skill';
                                }

                                typeSelect.addEventListener('change', handleParentOptions);

                                handleParentOptions(); // chạy lần đầu
                            });
                        </script>


                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" class="form-check-input" checked>
                            <label class="form-check-label">Hoạt động</label>
                        </div>

                        <button class="btn btn-primary">Lưu</button>
                        <a href="{{ route('admin.exam-filters.index') }}" class="btn btn-secondary">
                            Quay lại
                        </a>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
