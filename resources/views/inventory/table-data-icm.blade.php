<table class="table align-middle table-hover mb-0 icm-table table-compact">
    <thead style="background: #f3f4f6;">
        <tr class="text-secondary">
            <th title="ICM Number">ICM No</th>
            <th title="Division">Div.</th>
            <th title="Requesting Personnel">Personnel</th>
            <th title="Problem Description">Problem</th>
            <th title="Type">Type</th>
            <th title="Priority">Priority</th>
            <th title="Hardware/Software">HW/SW</th>
            <th title="Brand/Model">Brand/Model</th>
            <th title="Serial Number">Serial No</th>
            <th title="Property Number">Prop. No</th>
            <th title="Open Date">Open Date</th>
            <th title="Close Date">Close Date</th>
            <th title="Findings">Findings</th>
            <th title="Actions Taken">Actions</th>
            <th title="Recommendations">Recommendations</th>
            <th title="Action">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
        <tr data-item-id="{{ $item->icm_no }}">
            <td class="fw-semibold">{{ $item->icm_no }}</td>
            <td>
                <span class="badge fw-normal badge-division badge-division-{{ $item->division }}">
                    {{ $item->division }}
                </span>
            </td>
            <td class="item-personnel">{{ preg_replace('/\s*\(\d+\)$/', '', $item->requesting_personnel ?? 'N/A') }}</td>
            <td class="item-problem">
                @if(request('search'))
                    {!! Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', $item->problem_description ?? ''), 12) !!}
                @else
                    {{ Str::limit($item->problem_description ?? '', 12) }}
                @endif
            </td>
            <td>
                <span class="badge {{ $item->icm_type === 'Assistance' ? 'bg-info' : 'bg-warning text-dark' }} fw-normal">
                    {{ $item->icm_type ?? 'N/A' }}
                </span>
            </td>
            <td>
                <span class="badge {{ 
                    $item->priority === 'P1-Critical' ? 'bg-danger' : 
                    ($item->priority === 'P2-Important' ? 'bg-warning text-dark' : 
                    ($item->priority === 'P3-Normal' ? 'bg-info' : 
                    ($item->priority === 'P4-Low' ? 'bg-success' : 
                    ($item->priority === 'P5-Very Low' ? 'bg-secondary' : 'bg-secondary')))) 
                }} fw-normal">
                    {{ substr($item->priority ?? '', 0, 2) ?: 'N/A' }}
                </span>
            </td>
            <td>{{ $item->hardware_software ?? 'N/A' }}</td>
            <td class="item-brand-model">
                {{ Str::limit($item->brand_model ?? 'N/A', 15) }}
            </td>
            <td class="item-serial">
                {{ Str::limit($item->serial_number ?? 'N/A', 12) }}
            </td>
            <td class="item-property">{{ $item->property_number ?? 'N/A' }}</td>
            <td class="item-open-date">{{ $item->open_date ? $item->open_date->format('M d, Y') : 'N/A' }}</td>
            <td class="item-close-date">{{ $item->close_date ? $item->close_date->format('M d, Y') : 'N/A' }}</td>
            <td class="item-findings">
                {!! request('search')
                    ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->icm_findings ?? 'N/A')), 15)
                    : e(Str::limit($item->icm_findings ?? 'N/A', 15)) !!}
            </td>
            <td class="item-actions">
                {!! request('search')
                    ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->actions_taken ?? 'N/A')), 15)
                    : e(Str::limit($item->actions_taken ?? 'N/A', 15)) !!}
            </td>
            <td class="item-recommendations">
                {!! request('search')
                    ? Str::limit(preg_replace('/('.preg_quote(request('search'), '/').')/i', '<mark>$1</mark>', e($item->icm_recommendations ?? 'N/A')), 15)
                    : e(Str::limit($item->icm_recommendations ?? 'N/A', 15)) !!}
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm" title="Edit ICM" data-bs-toggle="modal" data-bs-target="#editIcmModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="17" class="text-center py-4">No ICM items found.</td>
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

@foreach($items as $item)
    <!-- Edit ICM Modal -->
    <div class="modal fade" id="editIcmModal{{ $item->id }}" tabindex="-1" aria-labelledby="editIcmModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIcmModalLabel{{ $item->id }}">Edit ICM {{ $item->icm_no }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('inventory.modals.edit-icm-modal', ['item' => $item])
                </div>
            </div>
        </div>
    </div>
@endforeach
