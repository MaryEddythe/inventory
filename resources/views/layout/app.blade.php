<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('styles.css') }}" rel="stylesheet">
    <link href="{{ asset('hr.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('bootstrap-icons/bootstrap-icons.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                @php($sidebarNavigation = auth()->user()?->sidebarNavigation() ?? collect())
                @foreach($sidebarNavigation as $item)
                    @include('layout.sidebar-item', ['item' => $item])
                @endforeach
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

        <main class="app-main bg-light">
            <div class="app-main-toolbar">
                <button class="sidebar-toggle" type="button" id="sidebarToggle" aria-controls="appSidebar" aria-expanded="true" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>

                @php
                    $headerUser = auth()->user();
                    $headerNotifications = $headerUser?->notifications()->latest()->take(5)->get() ?? collect();
                    $headerUnreadCount = $headerUser?->unreadNotifications()->count() ?? 0;
                @endphp

                <div class="app-toolbar-actions">
                    <div class="dropdown notification-dropdown">
                        <button class="btn btn-link notification-btn dropdown-toggle" type="button" id="notificationDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            @if($headerUnreadCount > 0)
                                <span class="notification-badge">{{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}</span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationDropdown">
                            <div class="notification-menu-header d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="notification-menu-title">Notifications</div>
                                    <div class="notification-menu-subtitle">{{ $headerUnreadCount }} unread</div>
                                </div>

                                @if($headerUnreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            <div class="notification-menu-list">
                                @forelse($headerNotifications as $notification)
                                    @php
                                        $notificationData = $notification->data ?? [];
                                        $isUnread = is_null($notification->read_at);
                                    @endphp
                                    <a class="notification-item {{ $isUnread ? 'is-unread' : '' }}"
                                       href="{{ route('notifications.read', $notification->id) }}">
                                        <div class="notification-item-top">
                                            <span class="notification-item-title">{{ $notificationData['headline'] ?? 'Leave update' }}</span>
                                            @if($isUnread)
                                                <span class="notification-item-dot"></span>
                                            @endif
                                        </div>
                                        <div class="notification-item-message">{{ $notificationData['message'] ?? 'You have a new leave update.' }}</div>
                                        <div class="notification-item-meta">
                                            <span>{{ $notificationData['step'] ?? 'Leave Application' }}</span>
                                            <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="notification-empty">
                                        No notifications yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
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
            const sidebarLinks = document.querySelectorAll('.sidebar-nav-link, .sidebar-dropdown-item');
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
                link.addEventListener('click', function () {
                    if (this.classList.contains('sidebar-dropdown-toggle')) {
                        return;
                    }

                    if (mobileQuery.matches) {
                        closeSidebar();
                    }
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
    @stack('modals')
</body>
</html>
