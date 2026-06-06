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
                    <span class="badge bg-light text-dark">1 item</span>
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
            <td class="item-price" data-unit-price="{{ $item->unit_price ?? null }}">{{ $item->unit_price ? '₱' . number_format($item->unit_price, 2) : 'NA' }}</td>
            <td class="item-comooe">{{ $item->co_mooe }}</td>
            <td class="item-date">{{ $item->date_acquired ? $item->date_acquired->format('M d, Y') : 'NA' }}</td>
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
                                @php
                                    $serviceability = $item->serviceability ?? 'N/A';
                                    // Make a safe CSS slug: "N/A" -> "n-a", "Beyond Economic Repair" -> "beyond-economic-repair"
                                    $serviceabilitySlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($serviceability)));
                                @endphp

                                @php
                                    // Inline fallback colors (so badge colors still work even if public/styles.css has parse errors)
                                    // IMPORTANT: switch on normalized DB value (not slug) to avoid mismatches due to spacing/casing.
                                    $serviceabilityBadgeStyle = '';
                                    $serviceabilityNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $serviceability)));

                                    switch ($serviceabilityNormalized) {
                                        case 'good condition':
                                            $serviceabilityBadgeStyle = 'background:#006400 !important; color:#fff !important;';
                                            break;
                                        case 'for replacement':
                                            $serviceabilityBadgeStyle = 'background:#A65E00 !important; color:#fff !important;';
                                            break;
                                        case 'beyond economic repair':
                                            $serviceabilityBadgeStyle = 'background:#7A0000 !important; color:#fff !important;';
                                            break;
                                        case 'n/a':
                                        case 'na':
                                            $serviceabilityBadgeStyle = 'background:#6c757d !important; color:#fff !important;';
                                            break;
                                        default:
                                            // leave empty -> badge will fall back to bootstrap/default badge styling
                                            $serviceabilityBadgeStyle = '';
                                            break;
                                    }
                                @endphp

                                <span class="badge fw-normal badge-serviceability-{{ $serviceabilitySlug }} {{ $serviceabilitySlug === 'n-a' ? 'badge-secondary' : '' }} {{ $serviceabilitySlug === 'n-a' ? 'text-white' : '' }}"
                                      style="{{ $serviceabilityBadgeStyle }}"
                                      title="Serviceability">
                                    {{ $serviceability }}
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
                    @include('inventory.modals.edit-modal', compact('item', 'departments', 'employees'))
                </div>
            </div>
        </div>
    </div>
@endforeach
