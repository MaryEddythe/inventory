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
        @forelse($items as $item)
        @php
            // Calculate years since acquisition
            $yearsSinceAcquisition = \Carbon\Carbon::parse($item->date_acquired)->diffInYears(\Carbon\Carbon::now());
            
            // Determine badge class based on age
            if ($yearsSinceAcquisition <= 5) {
                $ageBadgeClass = 'badge-age-new'; // Green for 0-5 years
            } else {
                $ageBadgeClass = 'badge-age-old'; // Yellow for 6+ years
            }
            
            // Determine status badge class
            if ($item->status === 'NEW') {
                $statusBadgeClass = 'badge-new'; // Green for NEW status
            } else {
                $statusBadgeClass = 'bg-warning text-dark'; // Yellow for other statuses
            }
        @endphp
        <tr data-item-id="{{ $item->no }}">
            <td class="fw-semibold text-muted">{{ $item->no }}</td>
            <td>
                <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                    {{ $item->division }}
                </span>
            </td>
            <td class="item-enduser">
                @if(request('search'))
                    {!! preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->enduser) !!}
                @else
                    {{ $item->enduser }}
                @endif
            </td>
            <td><span class="badge bg-secondary-subtle text-dark fw-normal item-classification">{{ $item->classification }}</span></td>
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
                <span class="badge {{ $ageBadgeClass }} fw-normal" title="{{ $yearsSinceAcquisition }} years old">
                    {{ $yearsSinceAcquisition <= 5 ? 'NEW' : 'FOR REPLACEMENT' }}
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
        @empty
        <tr>
            <td colspan="13" class="text-center py-4">No items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-end mt-4">
    {{ $items->links('vendor.pagination.bootstrap-5') }}
</div>