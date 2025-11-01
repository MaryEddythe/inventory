<table class="table align-middle table-hover mb-0 table-compact">
    <thead style="background: #f3f4f6;">
        <tr class="text-secondary">
            <th>No</th>
            <th>Division</th>
            <th>Enduser</th>
            <th>Classification</th>
            <th>Description</th>
            <th>Serial Number</th>
            <th>Property Number</th>
            <th>Unit Price</th>
            <th>CO/MOOE</th>
            <th>Date Acquired</th>
            <th>Remarks</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($groupedItems as $enduser => $items)
        @php
            $firstItem = $items->first();
            $itemCount = $items->count();
        @endphp
        @foreach($items as $index => $item)
        <tr data-enduser="{{ $enduser }}">
            @if($index === 0)
            <td class="fw-semibold text-muted" rowspan="{{ $itemCount }}">
                @if($itemCount > 1)
                    <span class="badge bg-light text-dark">{{ $itemCount }} items</span>
                @else
                    {{ $firstItem->no }}
                @endif
            </td>
            <td rowspan="{{ $itemCount }}">
                <span class="badge fw-normal badge-division badge-division-{{ $firstItem->division }}">
                    {{ $firstItem->division }}
                </span>
            </td>
            <td class="item-enduser" rowspan="{{ $itemCount }}">
                @if(request('search'))
                    {!! preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $enduser) !!}
                @else
                    {{ $enduser }}
                @endif
            </td>
            @endif
            <td>
                <span class="badge badge-classification badge-classification-{{ strtolower($item->classification) }} fw-normal item-classification">{{ $item->classification }}</span>
            </td>
            <td class="item-description">
                @if(request('search'))
                    {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->description), 8) !!}
                @else
                    {{ Str::limit($item->description, 8) }}
                @endif
            </td>
            <td class="item-serial">{{ $item->serial_number ?? 'N/A' }}</td>
            <td class="item-property">{{ $item->property_number }}</td>
            <td class="item-price">₱{{ number_format($item->unit_price, 2) }}</td>
            <td class="item-comooe">{{ $item->co_mooe }}</td>
            <td class="item-date">{{ $item->date_acquired->format('M d, Y') }}</td>
            <td class="item-remarks">{{ Str::limit($item->remarks, 20) ?? 'N/A' }}</td>
            <td>
                <span class="badge {{ $item->status == 'New' ? 'badge-age-new' : 'badge-age-old' }} fw-normal" title="Status">
                    {{ $item->status }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#editInventoryModal{{ $item->no }}"><i class="bi bi-pencil"></i></button>
                    <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
        @empty
        <tr>
            <td colspan="13" class="text-center py-4">No items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- bottom pagination --}}
@if(method_exists($items, 'hasPages') && $items->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3 table-pagination-bottom">
    <div class="text-muted small">
        Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
    </div>
    <div>
        {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endif

@php
    $allItems = $groupedItems->flatten();
@endphp

@foreach($allItems as $item)
    <div class="modal fade" id="editInventoryModal{{ $item->no }}" tabindex="-1" aria-labelledby="editInventoryModalLabel{{ $item->no }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInventoryModalLabel{{ $item->no }}">Edit Inventory Item {{ $item->no }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('inventory.modals.edit-modal', ['item' => $item])
                </div>
            </div>
        </div>
    </div>
@endforeach
