<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Aira Admin</title>

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('theme/images/favicon.png') }}">
    <!-- Custom Stylesheet -->
    <link href="{{ asset('theme/css/style.css') }}" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">

    @stack('styles')
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

    <div id="main-wrapper">
        <!-- Nav header start -->
        <div class="nav-header">
            <div class="brand-logo">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('theme/images/3.png') }}" alt="Logo" style="height: 30px;" class="mr-2">
                    <span class="brand-title">Aira Admin</span>
                </a>
            </div>
        </div>
        <!-- Nav header end -->

        <!-- Header start -->
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
                            <a href="#" data-toggle="dropdown">
                                <i class="mdi mdi-bell"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <div class="pulse-css"></div>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="dropdown-content-heading">
                                    <span>Notifications</span>
                                </div>
                                <div class="dropdown-content-body">
                                    <ul>
                                        @forelse(auth()->user()->notifications->take(5) as $notification)
                                            <li class="{{ $notification->read_at ? '' : 'notification-unread' }}">
                                                <a href="#">
                                                    <img class="float-left mr-3 avatar-img" src="{{ asset('theme/images/avatar/1.jpg') }}" alt="">
                                                    <div class="notification-content">
                                                        <div class="notification-text">{{ $notification->data['message'] ?? 'New Notification' }}</div>
                                                        <div class="notification-timestamp">{{ $notification->created_at->diffForHumans() }}</div>
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <li>
                                                <a href="javascript:void()">
                                                    <span class="mr-3"><i class="fa fa-bell-slash"></i></span>
                                                    <div class="notification-content">
                                                        <h6 class="notification-heading">No notifications</h6>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </li>
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
        <!-- Header end -->

        <!-- Sidebar start -->
        <div class="nk-sidebar">
            <div class="nk-nav-scroll">
                <ul class="metismenu" id="menu">
                    <li class="nav-label">Dashboard</li>
                    <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-speedometer"></i> <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-label">Live Streaming</li>
                    <li class="{{ request()->routeIs('admin.streaming.*') ? 'active' : '' }}">
                        <a class="has-arrow" href="javascript:void()">
                            <i class="icon-camera"></i> <span>Live Streaming</span>
                        </a>
                        <ul aria-expanded="false">
                            <li class="{{ request()->routeIs('admin.streaming.index') ? 'active' : '' }}">
                                <a href="{{ route('admin.streaming.index') }}">Dashboard Live</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.streaming.vouchers.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.streaming.vouchers.index') }}">Voucher Live</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.streaming.orders.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.streaming.orders.index') }}">Pesanan Live</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-label">E-Commerce</li>
                    <li class="{{ request()->routeIs('admin.products.*') || request()->routeIs('admin.orders.*') || request()->routeIs('admin.payments.*') || request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
                        <a class="has-arrow" href="javascript:void()">
                            <i class="icon-basket"></i> <span>E-Commerce</span>
                        </a>
                        <ul aria-expanded="false">
                            <li class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.products.index') }}">Produk</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.orders.index') }}">Pesanan</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.payments.index') }}">Konfirmasi Pembayaran</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.shipping.index') }}">Pengiriman</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-label">Manajemen</li>
                    <li class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                        <a class="has-arrow" href="javascript:void()">
                            <i class="icon-user"></i> <span>Manajemen</span>
                        </a>
                        <ul aria-expanded="false">
                            <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.users.index') }}">Pengguna</a>
                            </li>
                            <li class="{{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.whatsapp.index') }}">
                                    <i class="icon-bubble"></i> WhatsApp
                                    @if(isset($unreadWhatsAppCount) && $unreadWhatsAppCount > 0)
                                        <span class="badge badge-success" id="unread-whatsapp-count">{{ $unreadWhatsAppCount }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-label">Pengaturan</li>
                    <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <a class="has-arrow" href="javascript:void()">
                            <i class="icon-settings"></i> <span>Pengaturan</span>
                        </a>
                        <ul aria-expanded="false">
                            <li class="{{ request()->routeIs('admin.settings.payment') ? 'active' : '' }}">
                                <a href="{{ route('admin.settings.payment') }}">Pengaturan Pembayaran</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Sidebar end -->

        <!-- Content body start -->
        <div class="content-body">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        <!-- Content body end -->

        <!-- Footer start -->
        <div class="footer">
            <div class="copyright">
                <p>Copyright &copy; {{ date('Y') }} Aira Store</p>
            </div>
        </div>
        <!-- Footer end -->
    </div>

    <!-- Scripts -->
    <script src="{{ asset('theme/plugins/common/common.min.js') }}"></script>
    <script src="{{ asset('theme/js/custom.min.js') }}"></script>
    <script src="{{ asset('theme/js/settings.js') }}"></script>
    <script src="{{ asset('theme/js/gleek.js') }}"></script>
    <script src="{{ asset('theme/js/styleSwitcher.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // CSRF Token setup for Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function updateWhatsAppCount() {
            axios.get('{{ route("admin.whatsapp.statistics") }}')
                .then(response => {
                    const unreadCount = response.data.unread || 0;
                    const badge = document.getElementById('unread-whatsapp-count');
                    if (badge) {
                        badge.textContent = unreadCount;
                        badge.style.display = unreadCount > 0 ? 'inline' : 'none';
                    }
                })
                .catch(error => console.error('Failed to fetch WhatsApp statistics:', error));
        }

        // Update count every 30 seconds
        setInterval(updateWhatsAppCount, 30000);

        // Initial update
        document.addEventListener('DOMContentLoaded', updateWhatsAppCount);
    </script>

    @stack('scripts')
</body>
</html>
