<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</head>
<body>
    <div class="d-flex min-vh-100">
        <nav id="sidebar" class="sidebar bg-white shadow-sm p-3 d-flex flex-column sidebar-collapsible" style="width: 250px; transition: width 0.2s, min-width 0.2s;">
            <div class="mb-4 d-flex align-items-center justify-content-between">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-primary" href="{{ route('inventory.index') }}" style="font-size: 1.4rem;">
                    <i class="bi bi-box-seam"></i> <span class="sidebar-label">Inventory</span>
                </a>
                <button id="sidebarToggle" class="btn btn-sm btn-light d-lg-none" type="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-secondary disabled"><i class="bi bi-speedometer2 me-2"></i><span class="sidebar-label">Dashboard</span></a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active text-primary' : 'text-dark' }}"><i class="bi bi-archive me-2"></i><span class="sidebar-label">Inventory</span></a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-dark"><i class="bi bi-people me-2"></i><span class="sidebar-label">User Management</span></a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link text-dark"><i class="bi bi-gear me-2"></i><span class="sidebar-label">Settings</span></a>
                </li>
            </ul>
            <div class="mt-auto pt-3 border-top">
                <a href="#" class="nav-link text-dark"><i class="bi bi-moon me-2"></i><span class="sidebar-label">Dark mode</span></a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column" style="min-width:0;">
            <!-- Topbar -->
            <header class="bg-white shadow-sm px-4 py-3 d-flex align-items-center justify-content-between position-relative">
                <div class="d-flex align-items-center gap-3">
                    <button id="sidebarBurger" class="btn btn-light d-inline-flex d-lg-none me-2" type="button" aria-label="Open sidebar" style="z-index:1060;">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <span class="fw-semibold text-muted">Dashboard / Inventory</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <form class="d-none d-md-block">
                        <input type="text" class="form-control form-control-sm" placeholder="Search anything here" style="width: 220px;">
                    </form>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff" alt="user" width="32" height="32" class="rounded-circle me-2">
                            <span class="fw-medium">SampleUser</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1 p-4" style="background: #f8f9fa; min-height: 0;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBurger = document.getElementById('sidebarBurger');
        let sidebarOverlay = null;
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            if (sidebar.classList.contains('collapsed')) {
                sidebar.style.width = '0';
                sidebar.style.minWidth = '0';
                sidebar.style.overflow = 'hidden';
                if (sidebarOverlay) sidebarOverlay.remove();
            } else {
                sidebar.style.width = '250px';
                sidebar.style.minWidth = '250px';
                sidebar.style.overflow = '';
                // Add overlay for mobile
                if (window.innerWidth < 992) {
                    sidebarOverlay = document.createElement('div');
                    sidebarOverlay.style.position = 'fixed';
                    sidebarOverlay.style.top = 0;
                    sidebarOverlay.style.left = 0;
                    sidebarOverlay.style.width = '100vw';
                    sidebarOverlay.style.height = '100vh';
                    sidebarOverlay.style.background = 'rgba(0,0,0,0.2)';
                    sidebarOverlay.style.zIndex = 1059;
                    sidebarOverlay.onclick = toggleSidebar;
                    document.body.appendChild(sidebarOverlay);
                }
            }
        }
        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (sidebarBurger) sidebarBurger.addEventListener('click', toggleSidebar);
        // Close sidebar on resize if desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992 && sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                sidebar.style.width = '250px';
                sidebar.style.minWidth = '250px';
                sidebar.style.overflow = '';
                if (sidebarOverlay) sidebarOverlay.remove();
            }
        });
    </script>
</body>
</html>