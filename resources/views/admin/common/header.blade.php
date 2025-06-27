<div class="header">
    <div class="navbar-custom navbar navbar-expand-lg">
        <div class="container-fluid px-0">
            <a class="navbar-brand d-md-none" href=""></a>
            <a id="nav-toggle" href="#!" class="ms-auto ms-md-0 me-0 me-lg-3 ">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor"
                     class="bi bi-text-indent-left text-muted" viewBox="0 0 16 16">
                    <path
                        d="M2 3.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5zm.646 2.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L4.293 8 2.646 6.354a.5.5 0 0 1 0-.708zM7 6.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm0 3a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </a>
            <div class="d-none d-md-none d-lg-block">
                <form action="#" class="d-none">
                    <div class="input-group ">
                        <input class="form-control rounded-3" type="search" value="" id="searchInput"
                               placeholder="Search">
                        <span class="input-group-append">
                            <button class="btn  ms-n10 rounded-0 rounded-end" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     class="feather feather-search text-dark">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
            <!--Navbar nav -->
            <ul class="navbar-nav navbar-right-wrap ms-lg-auto d-flex nav-top-wrap align-items-center ms-4 ms-lg-0">
                <a href="#"
                   class="form-check form-switch theme-switch btn btn-ghost btn-icon rounded-circle mb-0 ">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                    <label class="form-check-label" for="flexSwitchCheckDefault"></label>

                </a>
                </li>


                <!-- List -->
                <li class="dropdown ms-2">
                    <a class="rounded-circle" href="#!" role="button" id="dropdownUser" data-bs-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-md avatar-indicators avatar-online">
                            <img alt="avatar" src="{{ Auth::user()?->profile_image && file_exists(public_path(Auth::user()?->profile_image)) ? asset(Auth::user()?->profile_image) : asset('assets/images/avatar/avatar-11.jpg') }}" class="rounded-circle" />
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <ul class="list-unstyled">
                            <li>
{{--                                <a href="{{ route('admin.profile.edit') }}" class="dropdown-item mb-0">--}}
{{--                                    <i class="me-2 icon-xxs dropdown-item-icon" data-feather="user"></i>--}}
{{--                                    <span class="align-middle">Chỉnh sửa tài khoản</span>--}}
{{--                                </a>--}}
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="me-2 icon-xxs dropdown-item-icon" data-feather="power"></i>Đăng xuất
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>

                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
