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
        <aside class="app-sidebar" id="appSidebar">
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

                <!-- Inventory Dropdown -->
                <div class="sidebar-nav-dropdown">
                    <button class="sidebar-nav-link sidebar-dropdown-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#inventoryDropdown" aria-expanded="false">
                        <i class="bi bi-archive"></i>
                        <span>Inventory</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </button>
                    <div class="collapse" id="inventoryDropdown">
                        <div class="sidebar-dropdown-menu">
                            <a class="sidebar-dropdown-item {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                <span>Inventory</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.moto-vehicle') }}">
                                <span>Motor Vehicle</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.cip') }}">
                                <span>CIP</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.machine-equipment') }}">
                                <span>Machine & Equipment</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.office-equipment') }}">
                                <span>Office Equipment</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.technical-scientific-equipment') }}">
                                <span>Technical and Scientific Equipment</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.other-ppe') }}">
                                <span>Other PPE</span>
                            </a>
                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.furniture-fixtures') }}">
                                <span>Furnitures and Fixtures</span>
                            </a>

                            <a class="sidebar-dropdown-item" href="{{ route('inventory.tabs.military-police-security') }}">
                                <span>Military, Police &amp; Security Equipment</span>
                            </a>
                        </div>
                    </div>
                </div>

                <a class="sidebar-nav-link {{ request()->routeIs('inventory.ipm') ? 'active' : '' }}" href="{{ route('inventory.ipm') }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>IPM</span>
                </a>
                <a class="sidebar-nav-link {{ request()->routeIs('inventory.icm') ? 'active' : '' }}" href="{{ route('inventory.icm') }}">
                    <i class="bi bi-tools"></i>
                    <span>ICM</span>
                </a>
                <a class="sidebar-nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Employees</span>
                </a>
                <a href="#" onclick="return false;" class="" aria-disabled="true">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Calendar
                </a>
                </a>

                    <div class="nav-dropdown">
                    <button type="button" class="nav-dropdown-toggle {{ request()->routeIs('credits.*') ? 'active' : '' }}" onclick="toggleCreditsDropdown()" type="button">

                        <span class="nav-dropdown-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            Leave Credits
                        </span>
                        <span class="nav-dropdown-arrow" id="creditsDropdownArrow">&#9662;</span>
                    </button>
                    <div class="nav-dropdown-menu" id="creditsDropdownMenu">
                        <a href="#" onclick="return false;" class="{{ request()->routeIs('credits.cto') ? 'active' : '' }}">CTO</a>
                        <a href="#" onclick="return false;" class="{{ request()->routeIs('credits.index') ? 'active' : '' }}">Leave Credits</a>

                    </div>
                </div>
            </nav>


            @auth
            <div class="sidebar-account dropdown">
                <a href="#" class="sidebar-account-toggle dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username ?: Auth::user()->name) . '&background=0D8ABC&color=fff' }}" alt="user" width="40" height="40" class="rounded-circle">
                    <span class="sidebar-account-meta">
                        <span class="sidebar-account-name">{{ Auth::user()->username ?: Auth::user()->name }}</span>
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
        <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close sidebar"></button>

        <!-- Main Content -->
        <main class="app-main bg-light">
            <div class="app-main-toolbar">
                <button class="sidebar-toggle" type="button" id="sidebarToggle" aria-controls="appSidebar" aria-expanded="true" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="container-fluid px-4 pb-4">
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
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const toggle = document.getElementById('sidebarToggle');
            const closeButtons = document.querySelectorAll('[data-sidebar-close]');
            const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
            const mobileQuery = window.matchMedia('(max-width: 991.98px)');
            const storageKey = 'inventorySidebarCollapsed';

            function isCollapsed() {
                return mobileQuery.matches
                    ? !body.classList.contains('sidebar-open')
                    : body.classList.contains('sidebar-collapsed');
            }

            function syncToggle() {
                toggle.setAttribute('aria-expanded', String(!isCollapsed()));
            }

            function closeSidebar() {
                if (mobileQuery.matches) {
                    body.classList.remove('sidebar-open');
                } else {
                    body.classList.add('sidebar-collapsed');
                    localStorage.setItem(storageKey, 'true');
                }
                syncToggle();
            }

            function openSidebar() {
                if (mobileQuery.matches) {
                    body.classList.add('sidebar-open');
                } else {
                    body.classList.remove('sidebar-collapsed');
                    localStorage.setItem(storageKey, 'false');
                }
                syncToggle();
            }

            if (!mobileQuery.matches && localStorage.getItem(storageKey) === 'true') {
                body.classList.add('sidebar-collapsed');
            }

            toggle.addEventListener('click', function () {
                isCollapsed() ? openSidebar() : closeSidebar();
            });

            closeButtons.forEach(button => button.addEventListener('click', closeSidebar));
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    // Don't close sidebar if clicking dropdown toggle
                    if (this.classList.contains('sidebar-dropdown-toggle')) {
                        return;
                    }
                    if (mobileQuery.matches) closeSidebar();
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && mobileQuery.matches && body.classList.contains('sidebar-open')) {
                    closeSidebar();
                }
            });

            mobileQuery.addEventListener('change', function () {
                body.classList.remove('sidebar-open');
                if (!mobileQuery.matches && localStorage.getItem(storageKey) === 'true') {
                    body.classList.add('sidebar-collapsed');
                }
                syncToggle();
            });

            syncToggle();
        });
    </script>
</body>
</html>
