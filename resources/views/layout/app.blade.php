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
    @stack('scripts')
</head>
<body>
    <div class="min-vh-100 d-flex flex-column">
        <header class="bg-white shadow-sm px-4 py-3">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-primary" href="{{ route('inventory.index') }}">
                        <i class="bi bi-box-seam"></i>MGB VI - Inventory System
                    </a>
                    @auth
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username) . '&background=0D8ABC&color=fff' }}" alt="user" width="32" height="32" class="rounded-circle me-2">
                            <span class="fw-medium">{{ Auth::user()->username }}</span>
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
                </div>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <div class="bg-white border-bottom">
            <div class="container-fluid px-4">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                            <i class="bi bi-archive"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.ipm') ? 'active' : '' }}" href="{{ route('inventory.ipm') }}">
                            <i class="bi bi-clipboard-check"></i> IPM
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-grow-1 bg-light py-4">
            <div class="container-fluid px-4">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const tabs = document.querySelectorAll('.nav-tabs .nav-link');
            
            tabs.forEach(tab => {
                tab.classList.remove('active');
                
                const tabHref = tab.getAttribute('href');
                if (tabHref === currentPath || 
                    (currentPath === '/' && tabHref.includes('inventory')) ||
                    (currentPath.includes('inventory') && tabHref.includes('inventory'))) {
                    tab.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>