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
            <th>Serviceability</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php $enduserCounter = 0; @endphp
        @forelse($groupedItems as $enduser => $items)
        @php
            $firstItem = $items->first();
            $itemCount = $items->count();
            $rowClass = $enduserCounter % 2 === 0 ? 'table-row-even' : 'table-row-odd';
            $enduserCounter++;
        @endphp
        @foreach($items as $index => $item)
        <tr data-enduser="{{ $enduser }}" class="{{ $rowClass }}">
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
                <span class="badge badge-classification {{ in_array(strtolower($item->classification), ['laptop', 'desktop', 'scanner', 'monitor', 'photocopier', 'printer']) ? 'badge-classification-' . strtolower($item->classification) : 'badge-classification-default' }} fw-normal item-classification">{{ $item->classification }}</span>
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
                @php
                    $yearsSinceAcquisition = $item->date_acquired ? \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now()) : 10;
                @endphp
                <span class="badge {{ $yearsSinceAcquisition <= 5 ? 'badge-age-new' : 'badge-age-old' }} fw-normal" title="Status">
                    {{ $item->status }}
                </span>
            </td>
                            <td>
                                <span class="badge fw-normal {{ $item->serviceability ? 'badge-serviceability-' . strtolower(str_replace(' ', '-', $item->serviceability)) : '' }}" title="Serviceability">
                                    {{ $item->serviceability ?? 'N/A' }}
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
            <td colspan="14" class="text-center py-4">No items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>



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
