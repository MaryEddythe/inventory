@php($isChild = $isChild ?? false)
@php($hasChildren = isset($item['children']) && count($item['children']) > 0)
@php($dropdownId = 'sidebar-dropdown-' . $item['id'])

@if($hasChildren)
    <div class="nav-dropdown {{ $item['active'] ? 'open' : '' }}" id="{{ $dropdownId }}">
        <button type="button" class="nav-dropdown-toggle sidebar-nav-link {{ $item['active'] ? 'active' : '' }}" onclick="toggleSidebarDropdown('{{ $dropdownId }}')">
            <span class="nav-dropdown-left">
                @if(!empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                {{ $item['label'] }}
            </span>
            <span class="nav-dropdown-arrow">&#9662;</span>
        </button>
        <div class="nav-dropdown-menu">
            @foreach($item['children'] as $child)
                @include('layout.sidebar-node', ['item' => $child, 'isChild' => true])
            @endforeach
        </div>
    </div>
@else
    <a href="{{ $item['url'] ?? '#' }}" class="{{ $isChild ? '' : 'sidebar-nav-link ' }}{{ $isChild ? 'sidebar-dropdown-item ' : '' }}{{ $item['active'] ? 'active' : '' }}">
        @if(!empty($item['icon']))
            <i class="{{ $item['icon'] }}"></i>
        @endif
        {{ $item['label'] }}
    </a>
@endif
