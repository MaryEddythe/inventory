<table class="table align-middle table-hover mb-0 ipm-table">
    <thead style="background: #f3f4f6;">
        <tr class="text-secondary">
            <th title="Number">No</th>
            <th title="Division">Div.</th>
            <th title="User">User</th>
            <th title="Type">Type</th>
            <th title="Description">Desc</th>
            <th title="Condition">Condition</th>
            <th title="System Boot Up">Boot Up</th>
            <th title="Hardware">HW</th>
            <th title="Performance">Perf</th>
            <th title="Cables and Connections">Cables/Conn</th>
            <th title="Peripherals">Periph</th>
            <th title="Recommendation">Rec</th>
            <th title="Date Conducted">Date</th>
            <th title="Time Started">Start</th>
            <th title="Time Ended">End</th>
            <th title="Action">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr data-item-id="{{ $item->no }}">
                <td class="fw-semibold text-muted">{{ $item->no }}</td>
                <td>
                    <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                        {{ $item->division }}
                    </span>
                </td>
                <td class="item-enduser">
                    {!! request('search') 
                        ? preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->enduser)) 
                        : e($item->enduser) !!}
                </td>
                <td><span class="badge bg-secondary-subtle text-dark fw-normal">{{ $item->classification }}</span></td>
                <td class="item-description">
                    {!! request('search') 
                        ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->description)), 40) 
                        : e(Str::limit($item->description, 40)) !!}
                </td>
                <td>
                    <span class="badge {{ $item->condition === 'Functional' ? 'bg-success' : 'bg-warning text-dark' }} fw-normal">
                        {{ $item->condition === 'Functional' ? 'FUNC' : 'NONFUNC' }}
                    </span>
                </td>
                <td class="text-center">{{ $item->system_boot_up ? '✓' : '✗' }}</td>
                <td class="text-center">{{ $item->hardware ? '✓' : '✗' }}</td>
                <td class="text-center">{{ $item->performance ? '✓' : '✗' }}</td>
                <td class="text-center">{{ $item->cables_connections ? '✓' : '✗' }}</td>
                <td class="text-center">{{ $item->peripherals ? '✓' : '✗' }}</td>
                <td class="item-recommendation">
                    {!! request('search')
                        ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->recommendation ?? 'N/A')), 20)
                        : e(Str::limit($item->recommendation ?? 'N/A', 20)) !!}
                </td>
                <td class="item-date-conducted">{{ $item->date_conducted ? $item->date_conducted->format('M d, Y') : 'N/A' }}</td>
                <td class="item-time-started">{{ $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('h:iA') : 'N/A' }}</td>
                <td class="item-time-ended">{{ $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('h:iA') : 'N/A' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-primary btn-sm" title="Edit IPM"
                                data-bs-toggle="modal" data-bs-target="#editIpmModal{{ $item->no }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('inventory.destroy', $item->no) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="17" class="text-center py-4">No items found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries</div>
    <div>
        {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

{{-- Separate modals to avoid Blade compile collision --}}
@foreach($items as $item)
    <div class="modal fade" id="editIpmModal{{ $item->no }}" tabindex="-1" aria-labelledby="editIpmModalLabel{{ $item->no }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit IPM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('inventory.modals.edit-ipm-modal', ['item' => $item])
                </div>
            </div>
        </div>
    </div>
@endforeach