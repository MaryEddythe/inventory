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
        @forelse($paginator as $enduser => $items)
        @php
            $firstItem = $items->first();
            $itemCount = $items->count();
            $badgeColors = ['bg-secondary', 'bg-primary', 'bg-info', 'bg-success', 'bg-warning text-dark', 'bg-danger', 'bg-dark'];
        @endphp
        <tr data-enduser="{{ $enduser }}">
            <td class="fw-semibold text-muted">
                @if($itemCount > 1)
                    <span class="badge bg-light text-dark">{{ $itemCount }} items</span>
                @else
                    {{ $firstItem->no }}
                @endif
            </td>
            <td>
                <span class="badge fw-normal badge-division badge-division-{{ $firstItem->division }}">
                    {{ $firstItem->division }}
                </span>
            </td>
            <td class="item-enduser">
                @if(request('search'))
                    {!! preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $enduser) !!}
                @else
                    {{ $enduser }}
                @endif
            </td>
            <td>
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal me-1 mb-1">{{ $item->classification }}</span>
                    @endforeach
                @else
                    <span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $firstItem->classification }}</span>
                @endif
            </td>
            <td class="item-description">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal me-1">{{ Str::limit($item->description, 20) }}</span>
                        </div>
                    @endforeach
                @else
                    @if(request('search'))
                        {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $firstItem->description), 40) !!}
                    @else
                        {{ Str::limit($firstItem->description, 40) }}
                    @endif
                @endif
            </td>
            <td class="item-serial">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">{{ $item->serial_number ?? 'N/A' }}</span>
                        </div>
                    @endforeach
                @else
                    {{ $firstItem->serial_number ?? 'N/A' }}
                @endif
            </td>
            <td class="item-property">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">{{ $item->property_number }}</span>
                        </div>
                    @endforeach
                @else
                    {{ $firstItem->property_number }}
                @endif
            </td>
            <td class="item-price">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">₱{{ number_format($item->unit_price, 2) }}</span>
                        </div>
                    @endforeach
                @else
                    ₱{{ number_format($firstItem->unit_price, 2) }}
                @endif
            </td>
            <td class="item-comooe">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">{{ $item->co_mooe }}</span>
                        </div>
                    @endforeach
                @else
                    {{ $firstItem->co_mooe }}
                @endif
            </td>
            <td class="item-date">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">{{ $item->date_acquired->format('M d, Y') }}</span>
                        </div>
                    @endforeach
                @else
                    {{ $firstItem->date_acquired->format('M d, Y') }}
                @endif
            </td>
            <td class="item-remarks">
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="mb-1">
                            <span class="badge {{ $badgeColors[$index % count($badgeColors)] }} fw-normal">{{ Str::limit($item->remarks, 15) ?? 'N/A' }}</span>
                        </div>
                    @endforeach
                @else
                    {{ Str::limit($firstItem->remarks, 20) ?? 'N/A' }}
                @endif
            </td>
            <td>
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        @php
                            $yearsSinceAcquisition = \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now());
                        @endphp
                        <div class="mb-1">
                            <span class="badge {{ $yearsSinceAcquisition <= 5 ? 'badge-age-new' : 'badge-age-old' }} fw-normal" title="{{ $yearsSinceAcquisition }} years old">
                                {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'FOR REPLACEMENT' }}
                            </span>
                        </div>
                    @endforeach
                @else
                    @php
                        $yearsSinceAcquisition = \Carbon\Carbon::parse($firstItem->date_acquired)->diffInYears(\Carbon\Carbon::now());
                    @endphp
                    <span class="badge {{ $yearsSinceAcquisition <= 5 ? 'badge-age-new' : 'badge-age-old' }} fw-normal" title="{{ $yearsSinceAcquisition }} years old">
                        {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'FOR REPLACEMENT' }}
                    </span>
                @endif
            </td>
            <td>
                @if($itemCount > 1)
                    @foreach($items as $index => $item)
                        <div class="d-flex gap-1 mb-1">
                            <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#editInventoryModal{{ $item->no }}"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-primary btn-sm" title="Edit" data-bs-toggle="modal" data-bs-target="#editInventoryModal{{ $firstItem->no }}"><i class="bi bi-pencil"></i></button>
                        <form action="{{ route('inventory.destroy', $firstItem->no) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="13" class="text-center py-4">No items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>


@foreach($items as $item)
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