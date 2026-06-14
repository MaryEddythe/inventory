<nav class="sidebar" id="appSidebar">
    <a href="{{ route('employees.index') }}" class="brand">
        <span class="brand-full">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
            </span>
            <span class="brand-text">
                HR File System
            </span>
        </span>
        <span class="brand-short">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
            </span>
            HR
        </span>
    </a>

    <div class="nav-links">
        <div class="nav-section-label">Main Menu</div>

        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Employees
        </a>

        <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Calendar
        </a>

        <div class="nav-dropdown">
            <button type="button" class="nav-dropdown-toggle {{ request()->routeIs('credits.*') ? 'active' : '' }}" onclick="toggleCreditsDropdown()">
                <span class="nav-dropdown-left">
                    <svg cNaNinecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    Leave Credits
                </span>
                <span class="nav-dropdown-arrow" id="creditsDropdownArrow">&#9662;</span>
            </button>
            <div class="nav-dropdown-menu" id="creditsDropdownMenu">
                <a href="{{ route('credits.cto') }}" class="{{ request()->routeIs('credits.cto') ? 'active' : '' }}">CTO</a>
                <a href="{{ route('credits.index') }}" class="{{ request()->routeIs('credits.index') ? 'active' : '' }}">Leave Credits</a>
            </div>
        </div>
    </div>

    <div class="nav-user">
        <div class="user-info">
            <div class="user-avatar">AD</div>
            <div class="user-details">
                <span class="user-name">Admin User</span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
        <a href="#logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Logout
        </a>
    </div>
</nav>

<script>
function toggleCreditsDropdown(){
    const dropdown = document.querySelector('.nav-dropdown');
    if (!dropdown) return;
    dropdown.classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', function () {
    window.toggleCreditsDropdown = toggleCreditsDropdown;

    // Auto-open dropdown if a child route is active
    const dropdown = document.querySelector('.nav-dropdown');
    const toggle = document.querySelector('.nav-dropdown-toggle');
    if (dropdown && toggle && toggle.classList.contains('active')) {
        dropdown.classList.add('open');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e){
        const dropdown = document.querySelector('.nav-dropdown');
        const btn = document.querySelector('.nav-dropdown-toggle');
        if (!dropdown || !btn) return;
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });
});
</script>
