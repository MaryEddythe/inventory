<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Status</label>
                        <select class="form-select" id="statusSelect" name="status">
                            <option value="">All Status</option>
                            <option value="≤ 5 years">≤ 5 years</option>
                            <option value="> 5 years">> 5 years</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="divisionSelect" class="form-label">Division</label>
                        <select class="form-select" id="divisionSelect" name="division">
                            <option value="">All Divisions</option>
                            @if(isset($departments) && $departments)
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Acquired Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="dateFrom" class="form-label small">From</label>
                                <input type="date" class="form-control" id="dateFrom" name="date_from" placeholder="From">
                            </div>
                            <div class="col-6">
                                <label for="dateTo" class="form-label small">To</label>
                                <input type="date" class="form-control" id="dateTo" name="date_to" placeholder="To">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" id="clearFilters">Clear Filters</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
