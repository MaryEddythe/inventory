<table class="table align-middle table-hover mb-0" style="font-size: 1.1rem;">
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
                    {{ $firstItem->id }}
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
                <span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $item->classification }}</span>
            </td>
            <td class="item-description">
                @if(request('search'))
                    {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->description), 40) !!}
                @else
                    {{ Str::limit($item->description, 40) }}
                @endif
            </td>
            <td class="item-serial">{{ $item->serial_number ?? 'N/A' }}</td>
            <td class="item-property">{{ $item->property_number }}</td>
            <td class="item-price">₱{{ number_format($item->unit_price, 2) }}</td>
            <td class="item-comooe">{{ $item->co_mooe }}</td>
            <td class="item-date">{{ $item->date_acquired->format('M d, Y') }}</td>
            <td class="item-remarks">{{ Str::limit($item->remarks, 20) ?? 'N/A' }}</td>
            <td>
                @php
                    $yearsSinceAcquisition = \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now());
                @endphp
                <span class="badge {{ $yearsSinceAcquisition <= 5 ? 'badge-age-new' : 'badge-age-old' }} fw-normal" title="{{ $yearsSinceAcquisition }} years old">
                    {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'FOR REPLACEMENT' }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#editInventoryModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                    <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
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


@php
    $allItems = $groupedItems->flatten();
@endphp

@foreach($allItems as $item)
    <div class="modal fade" id="editInventoryModal{{ $item->id }}" tabindex="-1" aria-labelledby="editInventoryModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInventoryModalLabel{{ $item->id }}">Edit Inventory Item {{ $item->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('inventory.modals.edit-modal', ['item' => $item])
                </div>
            </div>
        </div>
    </div>
@endforeach
