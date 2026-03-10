<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter ICM Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="filter-priority" class="form-label">Priority</label>
                            <select class="form-select form-select-sm" id="filter-priority" name="priority">
                                <option value="">All Priorities</option>
                                <option value="P1-Critical" {{ request('priority') === 'P1-Critical' ? 'selected' : '' }}>P1-Critical</option>
                                <option value="P2-Important" {{ request('priority') === 'P2-Important' ? 'selected' : '' }}>P2-Important</option>
                                <option value="P3-Normal" {{ request('priority') === 'P3-Normal' ? 'selected' : '' }}>P3-Normal</option>
                                <option value="P4-Low" {{ request('priority') === 'P4-Low' ? 'selected' : '' }}>P4-Low</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="filter-icm-type" class="form-label">Type</label>
                            <select class="form-select form-select-sm" id="filter-icm-type" name="icm_type">
                                <option value="">All Types</option>
                                <option value="Assistance" {{ request('icm_type') === 'Assistance' ? 'selected' : '' }}>Assistance</option>
                                <option value="Troubleshoot" {{ request('icm_type') === 'Troubleshoot' ? 'selected' : '' }}>Troubleshoot</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="filter-division" class="form-label">Division</label>
                            <select class="form-select form-select-sm" id="filter-division" name="division">
                                <option value="">All Divisions</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department }}" {{ request('division') === $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="filter-date-from" class="form-label">Open Date From</label>
                            <input type="date" class="form-control form-control-sm" id="filter-date-from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="filter-date-to" class="form-label">Open Date To</label>
                            <input type="date" class="form-control form-control-sm" id="filter-date-to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">Clear Filters</button>
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
