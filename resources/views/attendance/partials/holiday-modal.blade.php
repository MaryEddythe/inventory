<div class="modal fade" id="attendanceHolidayModal" tabindex="-1" aria-labelledby="attendanceHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 fw-bold mb-1" id="attendanceHolidayModalLabel">Holiday Dates</h2>
                    <div class="text-muted small">Powered by the Philippine calendar, with HR overrides and extra holidays layered on top.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-info d-flex flex-wrap justify-content-between gap-2 align-items-center">
                    <div>
                        Official Philippine holidays are loaded automatically. HR-added holidays can still override or add dates when needed.
                    </div>
                    <a href="{{ route('attendance.holidays.index', ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-outline-primary">
                        Open full holiday page
                    </a>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 p-3 h-100 bg-light">
                            <div class="fw-semibold mb-3">Add Holiday</div>
                            <form method="POST" action="{{ route('attendance.holidays.store') }}" class="row g-3">
                                @csrf
                                <input type="hidden" name="holiday_form" value="1">
                                <div class="col-12">
                                    <label class="form-label">Holiday Date</label>
                                    <input type="date" name="holiday_date" class="form-control" value="{{ old('holiday_date', $selectedDate ?? now()->toDateString()) }}" required>
                                    @error('holiday_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Holiday Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Holiday name" value="{{ old('title') }}" required>
                                    @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes" value="{{ old('notes') }}">
                                    @error('notes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100" type="submit">Save Holiday</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-3">
                                <div>
                                    <div class="fw-semibold">Holiday Dates for {{ $monthStart->format('F Y') }}</div>
                                    <div class="text-muted small">Official Philippine holidays appear here together with HR-added holidays.</div>
                                </div>
                                <span class="badge text-bg-secondary">{{ $holidaysForMonth->count() }} date(s)</span>
                            </div>

                            <div class="table-responsive holiday-table-wrapper">
                                <table class="table table-hover align-middle mb-0 holiday-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Title</th>
                                            <th>Source</th>
                                            <th>Notes</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($holidaysForMonth as $holiday)
                                            <tr>
                                                <td class="fw-semibold">{{ $holiday->holiday_date->format('M d, Y') }}</td>
                                                <td>{{ $holiday->holiday_date->format('l') }}</td>
                                                <td>{{ $holiday->title }}</td>
                                                <td>
                                                    <span class="badge {{ ($holiday->is_custom ?? false) ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                                        {{ $holiday->source ?? 'Philippine calendar' }}
                                                    </span>
                                                </td>
                                                <td>{{ $holiday->notes ?: 'N/A' }}</td>
                                                <td class="text-end">
                                                    @if($holiday->is_custom ?? false)
                                                        <form method="POST" action="{{ route('attendance.holidays.destroy', $holiday->id) }}" onsubmit="return confirm('Remove this holiday?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">Official holiday</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No holidays found for this month.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .holiday-table-wrapper {
        max-height: min(60vh, 680px);
    }

    .holiday-table th,
    .holiday-table td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>

@if(old('holiday_form'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('attendanceHolidayModal');
            if (modal && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
@endif
