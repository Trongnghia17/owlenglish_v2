<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Codescandy">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-M8S4MT3EYG"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'G-M8S4MT3EYG');
    </script>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link href="{{ asset('assets/libs/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/%40mdi/font/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    @stack('styles')
    <title>Admin Trung Tâm</title>


</head>
<style>
    #alert-message {
        opacity: 0;
        transition: opacity 1s ease-in-out;
        /* 1 giây để hiện ra và ẩn đi */
        visibility: hidden;
    }

    #alert-message.show {
        opacity: 1;
        visibility: visible;
    }
</style>

<body>
<main id="main-wrapper" class="main-wrapper">
    <!-- header -->
    @include('admin.common.header')
    <!-- navbar vertical -->

    <!-- Sidebar -->

    @include('admin.common.sidebar')
    <!-- page content -->
    <div id="app-content">
        <div class="app-content-area">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade alert_message">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-dark alert-dismissible fade alert_message">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-dark alert-dismissible fade alert_message">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            @yield('content')

            {{--            --}}{{-- Content Here --}}
            {{--            <div class="container mt-5">--}}
            {{--                @yield('center', 'Default center section not found')--}}
            {{--            </div>   --}}

        </div>
    </div>

</main>

<!-- Libs JS -->
<script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/dist/feather.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

<!-- Theme JS -->
<script src="{{ asset('assets/js/theme.min.js') }}"></script>
<script src="{{ asset('assets/js/slug.js') }}"></script>
<!-- popper js -->
<script src="{{ asset('assets/libs/%40popperjs/core/dist/umd/popper.min.js') }}"></script>
<!-- tippy js -->
{{-- <script src="{{ asset('assets/libs/tippy.js/dist/tippy-bundle.umd.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/js/vendors/tooltip.js') }}"></script> --}}

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const successAlert = document.querySelector('.alert-success');
        const errorAlert = document.querySelector('.alert-dark');
        if (successAlert) {
            successAlert.classList.add('show');
            setTimeout(() => {
                successAlert.classList.add('fade-out');
                setTimeout(() => successAlert.remove(),
                    1000);
            }, 5000);
        }
        if (errorAlert) {
            errorAlert.classList.add('show');
            setTimeout(() => {
                errorAlert.classList.add('fade-out');
                setTimeout(() => errorAlert.remove(),
                    1000);
            }, 5000);
        }

        // Ẩn thông báo khi click nút đóng
        const closeButtons = document.querySelectorAll('.btn-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function () {
                const alert = this.closest('.alert');
                alert.classList.remove('show');
                setTimeout(() => alert.remove(),
                    500);
            });
        });
    });
</script>
@stack('scripts')
</body>

</html>
