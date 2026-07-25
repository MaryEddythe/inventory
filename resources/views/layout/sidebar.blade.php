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
        @php
            // Try to find the employee record for this user
            $empNo = $currentUser->emp_no;
            $employee = null;
            
            if ($empNo) {
                $employee = \App\Models\Employee::find($empNo);
            }
            
            // If not found by emp_no, try the relationship
            if (!$employee && method_exists($currentUser, 'employee')) {
                $employee = $currentUser->employee()->first();
            }
            
            $profileRoute = $employee ? '/employees/' . $employee->emp_no : null;
        @endphp
        <div class="nav-user nav-dropdown">
            <div class="nav-dropdown-toggle user-info" onclick="toggleSidebarDropdown('userDropdown')" style="cursor: pointer;">
                <div class="user-avatar">
                    @if($employee && $employee->profile_image)
                        <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($currentUser->username ?: $currentUser->name ?: 'U', 0, 2)) }}
                    @endif
                </div>
                <div class="user-details">
                    <span class="user-name">{{ $currentUser->username ?: $currentUser->name }}</span>
                    <span class="user-role">{{ $currentUser->role?->name ?? 'Account' }}</span>
                </div>
                <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
            <div class="nav-dropdown-menu" id="userDropdown">
                @if($profileRoute)
                    <a href="{{ $profileRoute }}" class="nav-dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="nav-dropdown-item" style="padding:0;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:inherit; font:inherit; cursor:pointer; display:flex; align-items:center; gap:0.5rem; padding:0.5rem 1rem; width:100%; text-align:left;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
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
