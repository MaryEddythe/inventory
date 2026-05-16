<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('bootstrap-icons/bootstrap-icons.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('modals')  
    @stack('scripts')
</head>
<body>
    <div class="app-shell">
        <aside class="app-sidebar">
            <a class="sidebar-brand" href="{{ route('inventory.dashboard') }}">
                <span class="sidebar-brand-icon"><i class="bi bi-box-seam"></i></span>
                <span>
                    <span class="sidebar-brand-title">MGB VI</span>
                    <span class="sidebar-brand-subtitle">Inventory System</span>
                </span>
            </a>

            <nav class="sidebar-nav" aria-label="Primary navigation">
                <a class="sidebar-nav-link {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a class="sidebar-nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                    <i class="bi bi-archive"></i>
                    <span>Inventory</span>
                </a>
                <a class="sidebar-nav-link {{ request()->routeIs('inventory.ipm') ? 'active' : '' }}" href="{{ route('inventory.ipm') }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>IPM</span>
                </a>
                <a class="sidebar-nav-link {{ request()->routeIs('inventory.icm') ? 'active' : '' }}" href="{{ route('inventory.icm') }}">
                    <i class="bi bi-tools"></i>
                    <span>ICM</span>
                </a>
            </nav>

            @auth
            <div class="sidebar-account dropdown">
                <a href="#" class="sidebar-account-toggle dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username) . '&background=0D8ABC&color=fff' }}" alt="user" width="40" height="40" class="rounded-circle">
                    <span class="sidebar-account-meta">
                        <span class="sidebar-account-name">{{ Auth::user()->username }}</span>
                        <span class="sidebar-account-label">Account</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </aside>

        <!-- Main Content -->
        <main class="app-main bg-light">
            <div class="container-fluid px-4 py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
