<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Admin Dashboard</title>

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/images/favicon.png') }}">

    <!-- Custom Stylesheet -->
    <link href="{{ asset('theme/css/style.css') }}" rel="stylesheet">
</head>

<body>
    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <div class="brand-logo">
                <a href="{{ route('admin.dashboard') }}">
                    <b class="logo-abbr"><img src="{{ asset('theme/images/logo.png') }}" alt=""> </b>
                    <span class="logo-compact"><img src="{{ asset('theme/images/logo-compact.png') }}" alt=""></span>
                    <span class="brand-title">
                        <img src="{{ asset('theme/images/logo-text.png') }}" alt="">
                    </span>
                </a>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">    
            <div class="header-content clearfix">
                <div class="nav-control">
                    <div class="hamburger">
                        <span class="toggle-icon"><i class="icon-menu"></i></span>
                    </div>
                </div>
                <div class="header-right">
                    <ul class="clearfix">
                        <li class="icons dropdown">
                            <div class="user-img c-pointer position-relative" data-toggle="dropdown">
                                <span class="activity active"></span>
                                <img src="{{ asset('theme/images/user/1.png') }}" height="40" width="40" alt="">
                            </div>
                            <div class="drop-down dropdown-profile animated fadeIn dropdown-menu">
                                <div class="dropdown-content-body">
                                    <ul>
                                        <li>
                                            <a href="{{ route('admin.logout') }}"
                                               onclick="event.preventDefault();
                                                             document.getElementById('logout-form').submit();">
                                                <i class="icon-key"></i>
                                                <span>Logout</span>
                                            </a>
                                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--**********************************
            Header end
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <div class="nk-sidebar">           
            <div class="nk-nav-scroll">
                <ul class="metismenu" id="menu">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" aria-expanded="false">
                            <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" aria-expanded="false">
                            <i class="icon-basket menu-icon"></i><span class="nav-text">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.orders.index') }}" aria-expanded="false">
                            <i class="icon-note menu-icon"></i><span class="nav-text">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" aria-expanded="false">
                            <i class="icon-user menu-icon"></i><span class="nav-text">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.streaming.index') }}" aria-expanded="false">
                            <i class="icon-camera menu-icon"></i><span class="nav-text">Live Streaming</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!--**********************************
            Sidebar end
        ***********************************-->

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->
    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="{{ asset('theme/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('theme/js/custom.min.js') }}"></script>
    <script src="{{ asset('theme/js/settings.js') }}"></script>
    <script src="{{ asset('theme/js/gleek.js') }}"></script>
    <script src="{{ asset('theme/js/styleSwitcher.js') }}"></script>
</body>
</html>
