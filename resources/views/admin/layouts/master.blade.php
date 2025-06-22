<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - AIRA Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/aira-logo.svg') }}">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom-admin.css') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <!-- Navbar -->
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                    <img src="{{ asset('assets/img/aira-logo.svg') }}" alt="AIRA" class="navbar-brand-img d-none d-lg-block">
                </form>
                <ul class="navbar-nav navbar-right">
                    <!-- Notifications -->
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg {{ auth()->user()->unreadNotifications->count() > 0 ? 'beep' : '' }}">
                            <i class="far fa-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">Notifikasi
                                <div class="float-right">
                                    <a href="#">Tandai semua telah dibaca</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons">
                                @forelse(auth()->user()->notifications->take(5) as $notification)
                                    <a href="#" class="dropdown-item {{ $notification->read_at ? '' : 'dropdown-item-unread' }}">
                                        <div class="dropdown-item-icon bg-primary text-white">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div class="dropdown-item-desc">
                                            {{ $notification->data['message'] ?? '' }}
                                            <div class="time">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-muted text-center p-3">Tidak ada notifikasi</p>
                                @endforelse
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="{{ route('admin.notifications.index') }}">Lihat Semua <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <!-- User Menu -->
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{ route('admin.logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Sidebar -->
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="{{ route('admin.dashboard') }}">
                            <img src="{{ asset('assets/img/aira-logo.svg') }}" alt="AIRA" class="mb-2">
                            <div>AIRA Admin</div>
                        </a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ route('admin.dashboard') }}">
                            <img src="{{ asset('assets/img/aira-logo.svg') }}" alt="AIRA" style="height: 30px;">
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>
                        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                <i class="fas fa-fire"></i><span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-header">Live Streaming</li>
                        <li class="dropdown {{ request()->routeIs('admin.streaming.*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown">
                                <i class="fas fa-video"></i><span>Live Streaming</span>
                            </a>
                            <ul class="dropdown-menu">
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

                        <li class="menu-header">E-Commerce</li>
                        <li class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.products.index') }}" class="nav-link">
                                <i class="fas fa-box"></i><span>Produk</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.orders.index') }}" class="nav-link">
                                <i class="fas fa-shopping-cart"></i><span>Pesanan</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.payments.index') }}" class="nav-link">
                                <i class="fas fa-credit-card"></i><span>Konfirmasi Pembayaran</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.shipping.index') }}" class="nav-link">
                                <i class="fas fa-truck"></i><span>Pengiriman</span>
                            </a>
                        </li>

                        <li class="menu-header">Manajemen</li>
                        <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}" class="nav-link">
                                <i class="fas fa-users"></i><span>Pengguna</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.whatsapp.index') }}" class="nav-link">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp</span>
                                @if(isset($unreadWhatsAppCount) && $unreadWhatsAppCount > 0)
                                    <span class="badge badge-success" id="unread-whatsapp-count">{{ $unreadWhatsAppCount }}</span>
                                @endif
                            </a>
                        </li>

                        <li class="menu-header">Pengaturan</li>
                        <li class="{{ request()->routeIs('admin.settings.payment') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings.payment') }}" class="nav-link">
                                <i class="fas fa-cog"></i><span>Pengaturan Pembayaran</span>
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </section>
            </div>

            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; {{ date('Y') }} <div class="bullet"></div> AIRA Store
                </div>
                <div class="footer-right">
                    v1.0.0
                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    
    <!-- Axios -->
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
