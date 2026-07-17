@php($isChild = $isChild ?? false)
@php($hasChildren = isset($item['children']) && count($item['children']) > 0)

@if($hasChildren)
    <div class="sidebar-nav-dropdown">
        <button class="sidebar-nav-link sidebar-dropdown-toggle {{ $item['active'] ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sidebarItem{{ $item['id'] }}"
                aria-expanded="{{ $item['active'] ? 'true' : 'false' }}"
                aria-controls="sidebarItem{{ $item['id'] }}">
            @if(!empty($item['icon']))
                <i class="{{ $item['icon'] }}"></i>
            @endif
            <span>{{ $item['label'] }}</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </button>

        <div class="collapse {{ $item['active'] ? 'show' : '' }}" id="sidebarItem{{ $item['id'] }}">
            <div class="sidebar-dropdown-menu">
                @foreach($item['children'] as $child)
                    @include('layout.sidebar-item', ['item' => $child, 'isChild' => true])
                @endforeach
            </div>
        </div>
    </div>
@else
    <a class="{{ $isChild ? 'sidebar-dropdown-item' : 'sidebar-nav-link' }} {{ $item['active'] ? 'active' : '' }}"
       @if(!empty($item['url'])) href="{{ $item['url'] }}" @else role="button" tabindex="0" @endif>
        @if(!empty($item['icon']))
            <i class="{{ $item['icon'] }}"></i>
        @endif
        <span>{{ $item['label'] }}</span>
    </a>
@endif
