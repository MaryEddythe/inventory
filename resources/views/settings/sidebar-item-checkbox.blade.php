@php($level = $level ?? 0)
@php($selectedIds = $selectedIds ?? [])
@php($children = $item->children ?? collect())
@php($hasChildren = $children->isNotEmpty())

<div class="mb-2" style="margin-left: {{ $level * 18 }}px;">
    <div class="form-check">
        <input
            class="form-check-input"
            type="checkbox"
            name="sidebar_item_ids[]"
            value="{{ $item->id }}"
            id="sidebar-item-{{ $item->id }}-{{ $level }}"
            @checked(in_array($item->id, $selectedIds, true))
        >
        <label class="form-check-label" for="sidebar-item-{{ $item->id }}-{{ $level }}">
            {{ $item->label }}
        </label>
    </div>

    @if($hasChildren)
        <div class="mt-2">
            @foreach($children as $child)
                @include('settings.sidebar-item-checkbox', [
                    'item' => $child,
                    'selectedIds' => $selectedIds,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
