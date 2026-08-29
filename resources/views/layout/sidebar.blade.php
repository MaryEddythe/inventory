@php($currentUser = auth()->user())
@php($sidebarNavigation = $currentUser?->sidebarNavigation() ?? collect())

<nav class="sidebar-nav" aria-label="Primary navigation">
        <a href="{{ route('inventory.dashboard') }}" class="brand">
        <span class="brand-full">
            <span class="brand-icon">
                <img src="{{ asset('assets/' . rawurlencode('mgb logo.png')) }}" alt="Mines and Geosciences Bureau VI logo" class="img-fluid">
            </span>
            <span class="brand-text">
                Mines and Geosciences Bureau VI
            </span>
        </span>
        <span class="brand-short">
            MGB VI
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
        <div class="nav-user">
            @if($profileRoute)
                <a href="{{ $profileRoute }}" class="user-info" style="cursor: pointer; text-decoration: none; color: inherit;">
            @else
                <div class="user-info" style="cursor: default;">
            @endif
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
            @if($profileRoute)
                </a>
            @else
                </div>
            @endif
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
