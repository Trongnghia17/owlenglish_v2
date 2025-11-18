<div class="navbar-vertical navbar nav-dashboard">
    <div class="h-100" data-simplebar>
        <!-- Brand logo -->
        <a class="navbar-brand" href="">
            <img src="{{ asset('assets/uploads/Logo.png') }}" alt="logo trung tâm" class="center-logo" style="   max-width: 80px;
                            height: auto;
                            object-fit: contain;
                            margin: 0 auto;
                            display: block;" width="80" height="80">
        </a>
        <!-- Navbar nav -->
        <ul class="navbar-nav flex-column" id="sideNavbar">
            <!-- Nav item -->
            <li class="nav-item">
                <a class="nav-link has-arrow " href="#!" data-bs-toggle="collapse" data-bs-target="#navDashboard"
                    aria-expanded="false" aria-controls="navDashboard">
                    <i data-feather="home" class="nav-icon me-2 icon-xxs"></i>
                    THỐNG KÊ
                </a>

                <div id="navDashboard" class="collapse " data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
{{--                        @if(Auth()->user()->role_id == 4)--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" href="{{ route('admin.statistic.student') }}">--}}
{{--                                    Thống kê--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                        @if(Auth()->user()->role_id == 3)--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" href="{{ route('admin.statistic.teacher') }}">--}}
{{--                                    Thống kê--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        @endif--}}
                        @if(Auth()->user()->role_id == 1 || Auth()->user()->role_id == 2)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    Thống kê
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
                <div class="navbar-heading">QUẢN LÝ</div>
            </li>

            @if(Auth()->user()->role_id == 1 || Auth()->user()->role_id == 2)
                <!-- Quản lý Exam -->
                <li class="nav-item">
                    <a class="nav-link has-arrow {{ request()->routeIs('admin.exams.*') ? '' : 'collapsed' }}"
                       href="#!" data-bs-toggle="collapse" data-bs-target="#navExams"
                       aria-expanded="{{ request()->routeIs('admin.exams.*') ? 'true' : 'false' }}"
                       aria-controls="navExams">
                        <i data-feather="file-text" class="nav-icon me-2 icon-xxs"></i>
                        Quản lý bộ đề thi
                    </a>
                    <div id="navExams" class="collapse {{ request()->routeIs('admin.exams.*') ? 'show' : '' }}"
                         data-bs-parent="#sideNavbar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.exams.index') ? 'active' : '' }}"
                                   href="{{ route('admin.exams.index') }}">
                                    Danh sách bộ đề thi
                                </a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.exams.create') ? 'active' : '' }}"
                                   href="{{ route('admin.exams.create') }}">
                                    Thêm Exam mới
                                </a>
                            </li> -->
                        </ul>
                    </div>
                </li>

                <!-- Quản lý Skills -->
                <li class="nav-item">
                    <a class="nav-link has-arrow {{ request()->routeIs('admin.skills.*') ? '' : 'collapsed' }}"
                       href="#!" data-bs-toggle="collapse" data-bs-target="#navSkills"
                       aria-expanded="{{ request()->routeIs('admin.skills.*') ? 'true' : 'false' }}"
                       aria-controls="navSkills">
                        <i data-feather="book-open" class="nav-icon me-2 icon-xxs"></i>
                        Quản lý đề thi
                    </a>
                    <div id="navSkills" class="collapse {{ request()->routeIs('admin.skills.*') ? 'show' : '' }}"
                         data-bs-parent="#sideNavbar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.skills.index') ? 'active' : '' }}"
                                   href="{{ route('admin.skills.index') }}">
                                    Danh sách đề thi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.skills.create') ? 'active' : '' }}"
                                   href="{{ route('admin.skills.create') }}">
                                    Thêm đề thi mới
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Quản lý người dùng -->
                <li class="nav-item">
                    <a class="nav-link has-arrow {{ request()->routeIs('admin.users.*') ? '' : 'collapsed' }}"
                       href="#!" data-bs-toggle="collapse" data-bs-target="#navUsers"
                       aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}"
                       aria-controls="navUsers">
                        <i data-feather="users" class="nav-icon me-2 icon-xxs"></i>
                        Quản lý người dùng
                    </a>
                    <div id="navUsers" class="collapse {{ request()->routeIs('admin.users.*') ? 'show' : '' }}"
                         data-bs-parent="#sideNavbar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users.index') && !request('role') ? 'active' : '' }}"
                                   href="{{ route('admin.users.index') }}">
                                    Tất cả người dùng
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link has-arrow {{ request()->routeIs('admin.students.*') ? '' : 'collapsed' }}"
                       href="#!" data-bs-toggle="collapse" data-bs-target="#navStudent"
                       aria-expanded="{{ request()->routeIs('admin.students.*') ? 'true' : 'false' }}"
                       aria-controls="navStudent">
                        <i data-feather="user-check" class="nav-icon me-2 icon-xxs"></i>
                        Quản lý học sinh
                    </a>
                    <div id="navStudent" class="collapse {{ request()->routeIs('admin.students.*') ? 'show' : '' }}"
                         data-bs-parent="#sideNavbar">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.students.index') && !request('role') ? 'active' : '' }}"
                                   href="{{ route('admin.students.index') }}">
                                    Tất cả học sinh
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.students.create') && !request('role') ? 'active' : '' }}"
                                   href="{{ route('admin.students.create') }}">
                                    Thêm học sinh
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
            <style>
                .navbar-vertical .navbar-nav .nav .nav-item .nav-link.active {
                    font-weight: bold;
                }
            </style>
        </ul>
    </div>
</div>
