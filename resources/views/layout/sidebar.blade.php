@php($currentUser = auth()->user())
@php($sidebarNavigation = $currentUser?->sidebarNavigation() ?? collect())

<nav class="sidebar-nav" aria-label="Primary navigation">
    <a href="{{ route('inventory.dashboard') }}" class="brand">
        <span class="brand-full">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
            </span>
            <span class="brand-text">
                Inventory System
            </span>
        </span>
        <span class="brand-short">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
            </span>
            IS
        </span>
    </a>

    <div class="nav-links">
        <div class="nav-section-label">Main Menu</div>

        @forelse($sidebarNavigation as $item)
            @include('layout.sidebar-node', ['item' => $item])
        @empty
            <div class="nav-section-label" style="text-transform:none; letter-spacing:0; font-size:0.8rem;">
                No sidebar items assigned.
            </div>
        @endforelse
    </div>

    @auth
        <div class="nav-user">
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr($currentUser->username ?: $currentUser->name ?: 'U', 0, 2)) }}
                </div>
                <div class="user-details">
                    <span class="user-name">{{ $currentUser->username ?: $currentUser->name }}</span>
                    <span class="user-role">{{ $currentUser->role?->name ?? 'Account' }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    @endauth
</nav>

<script>
function toggleSidebarDropdown(id){
    const dropdown = document.getElementById(id);
    if (!dropdown) return;
    dropdown.classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-dropdown-toggle.active').forEach((toggle) => {
        const dropdown = toggle.closest('.nav-dropdown');
        if (dropdown) {
            dropdown.classList.add('open');
        }
    });
});
</script>
