<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="NEW">New</option>
                            <option value="FOR REPLACEMENT">For Replacement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Division</label>
                        <select class="form-select" name="division">
                            <option value="">All Divisions</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Acquired Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" class="form-control" name="date_from" placeholder="From">
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control" name="date_to" placeholder="To">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="clearFilters">Clear Filters</button>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
