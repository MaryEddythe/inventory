<table class="table align-middle table-hover mb-0" style="font-size: 1.1rem;">
<table class="table align-middle table-hover mb-0" style="font-size: 1.1rem;">
    <thead style="background: #f3f4f6;">
        <tr class="text-secondary">
            <th>No</th>
            <th>Div.</th>
            <th>User</th>
            <th>Type</th>
            <th>Description</th>
            <th>Condition</th>
            <th>System Boot Up</th>
            <th>Hardware</th>
            <th>Performance</th>
            <th>Cables and Connections</th>
            <th>Peripherals</th>
            <th>Remarks</th>
            <th>Recommendation</th>
            <th>Date Conducted</th>
            <th>Time Started</th>
            <th>Time Ended</th>
            <th>Action</th>
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
                    <select class="form-select form-select-sm condition-select" data-item-id="{{ $item->no }}">
                        <option value="Functional" {{ $item->condition === 'Functional' ? 'selected' : '' }}>Functional</option>
                        <option value="Nonfunctional" {{ $item->condition === 'Nonfunctional' ? 'selected' : '' }}>Nonfunctional</option>
                    </select>
                </td>
                <td><input type="checkbox" class="form-check-input" {{ $item->system_boot_up ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" class="form-check-input" {{ $item->hardware ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" class="form-check-input" {{ $item->performance ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" class="form-check-input" {{ $item->cables_connections ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" class="form-check-input" {{ $item->peripherals ? 'checked' : '' }} disabled></td>

                <td class="item-remarks">
                    {!! request('search')
                        ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->remarks ?? 'N/A')), 20)
                        : e(Str::limit($item->remarks ?? 'N/A', 20)) !!}
                </td>

                <td class="item-recommendation">
                    {!! request('search')
                        ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->recommendation ?? 'N/A')), 20)
                        : e(Str::limit($item->recommendation ?? 'N/A', 20)) !!}
                </td>

                <td class="item-date-conducted">{{ $item->date_conducted ? $item->date_conducted->format('M d, Y') : 'N/A' }}</td>
                <td class="item-time-started">{{ $item->time_started ?? 'N/A' }}</td>
                <td class="item-time-ended">{{ $item->time_ended ?? 'N/A' }}</td>
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

<div class="d-flex justify-content-end mt-4">
    {{ $items->links('vendor.pagination.bootstrap-5') }}
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

