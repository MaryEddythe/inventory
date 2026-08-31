<div class="hr-modal-backdrop cto-detail-modal" id="ctoDetailModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ctoDetailModalTitle">
    <div class="hr-modal-dialog" role="document">
        <div class="hr-modal-header">
            <div>
                <h2 class="hr-modal-title" id="ctoDetailModalTitle">Credited Time-Off Details</h2>
                <div class="hr-modal-subtitle">
                    {{ $employee?->full_name ?? 'Employee' }}
                </div>
            </div>
            <button type="button" class="hr-modal-close" onclick="closeCtoDetailModal()" aria-label="Close credited time-off details">Close</button>
        </div>

        <div class="hr-modal-body">
            @if(($ctoHistory ?? collect())->isNotEmpty())
                <div class="table-responsive">
                    <table class="cto-detail-table">
                        <thead>
                            <tr>
                                <th>Special Order / Basis</th>
                                <th>S.O / T.O No</th>
                                <th>Location</th>
                                <th>Credited Hours</th>
                                <th>Used Hours</th>
                                <th>Remaining Hours</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ctoHistory as $credit)
                                <tr>
                                    <td>{{ $credit->remarks ?: '-' }}</td>
                                    <td>{{ $credit->so_to_no ?: '-' }}</td>
                                    <td>{{ $credit->location ?: '-' }}</td>
                                    <td>{{ (int) ($credit->credit_hours ?? 0) }}</td>
                                    <td>{{ (int) ($credit->used_hours ?? 0) }}</td>
                                    <td><strong>{{ (int) ($credit->remaining_hours ?? 0) }}</strong></td>
                                    <td>{{ $credit->start_date ? $credit->start_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $credit->end_date ? $credit->end_date->format('M d, Y') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted mb-0">No available credited time-off records found for this employee.</div>
            @endif
        </div>
    </div>
</div>
