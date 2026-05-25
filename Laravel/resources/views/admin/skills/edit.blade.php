@extends('layouts.app')

@push('styles')
    @include('admin.skills.edit.styles')
@endpush

@section('content')
    {{-- Page header, form fields, navigation sidebar, and section builder markup. --}}
    @include('admin.skills.edit.content')
@endsection

@push('scripts')
    {{-- Dynamic section/question builder logic and filter checkbox behavior. --}}
    @include('admin.skills.edit.scripts')
    @include('admin.skills.edit.exam-filter-script')
@endpush
