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
                        @if(Auth()->user()->role == 4)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.statistic.student') }}">
                                    Thống kê
                                </a>
                            </li>
                        @endif
                        @if(Auth()->user()->role == 3)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.statistic.teacher') }}">
                                    Thống kê
                                </a>
                            </li>
                        @endif
                        @if(Auth()->user()->role == 1 || Auth()->user()->role == 2)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.statistic.class_management') }}">
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
 @if(Auth()->user()->role == 0)
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
                            <li class="nav-item">
                                <a class="nav-link {{ request('role') === 'teacher' ? 'active' : '' }}" 
                                   href="{{ route('admin.users.index', ['role' => 'teacher']) }}">
                                    Giáo viên
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('role') === 'assistant' ? 'active' : '' }}" 
                                   href="{{ route('admin.users.index', ['role' => 'assistant']) }}">
                                    Trợ lý
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request('role') === 'student' ? 'active' : '' }}" 
                                   href="{{ route('admin.users.index', ['role' => 'student']) }}">
                                    Học viên
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