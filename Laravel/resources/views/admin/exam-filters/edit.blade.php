@extends('layouts.app')

@section('content')
<div class="app-content">
    <div class="container-fluid">

        <h3 class="mb-4">Chỉnh sửa bộ lọc</h3>

        <div class="card">
            <div class="card-body">

                <form method="POST"
                      action="{{ route('admin.exam-filters.update',$examFilter) }}">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name',$examFilter->name) }}"
                               required>
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label">Loại</label>
                        <select name="type"
                                class="form-select"
                                id="typeSelect"
                                required>
                            @foreach(['skill','group','value'] as $type)
                                <option value="{{ $type }}"
                                    @selected($examFilter->type === $type)>
                                    {{ strtoupper($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Parent --}}
                    <div class="mb-3">
                        <label class="form-label">Thuộc bộ lọc cha</label>
                        <select name="parent_id"
                                class="form-select"
                                id="parentSelect">
                            <option value="">-- Không có --</option>

                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                        data-type="{{ $parent->type }}"
                                        @selected($examFilter->parent_id == $parent->id)>
                                    {{ $parent->name }} ({{ strtoupper($parent->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Active --}}
                    <div class="form-check mb-3">
                        <input type="checkbox"
                               name="is_active"
                               class="form-check-input"
                               @checked($examFilter->is_active)>
                        <label class="form-check-label">Hoạt động</label>
                    </div>

                    <button class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('admin.exam-filters.index') }}"
                       class="btn btn-secondary">
                        Quay lại
                    </a>
                </form>

            </div>
        </div>

    </div>
</div>

{{-- JS xử lý logic cha – con --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect   = document.getElementById('typeSelect');
    const parentSelect = document.getElementById('parentSelect');

    function handleParentOptions() {
        const type = typeSelect.value;
        let hasVisibleOption = false;

        Array.from(parentSelect.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const parentType = option.dataset.type;

            if (type === 'skill') {
                option.hidden = true;
            }
            else if (type === 'group') {
                option.hidden = parentType !== 'skill';
            }
            else if (type === 'value') {
                option.hidden = parentType !== 'group';
            }

            if (!option.hidden) {
                hasVisibleOption = true;
            }
        });

        if (type === 'skill') {
            parentSelect.value = '';
            parentSelect.disabled = true;
        } else {
            parentSelect.disabled = !hasVisibleOption;
        }
    }

    typeSelect.addEventListener('change', handleParentOptions);

    handleParentOptions(); // chạy ngay khi load edit
});
</script>
@endsection
