<!-- Filter Modal for IPM -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter IPM Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="condition" class="form-label">Condition</label>
                        <select class="form-select" id="condition" name="condition">
                            <option value="">All Conditions</option>
                            <option value="Functional" {{ request('condition') === 'Functional' ? 'selected' : '' }}>Functional</option>
                            <option value="Nonfunctional" {{ request('condition') === 'Nonfunctional' ? 'selected' : '' }}>Nonfunctional</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="division" class="form-label">Department</label>
                        <select class="form-select" id="division" name="division">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department }}" {{ request('division') === $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="clearFilters">Clear Filters</button>
                <button type="submit" class="btn btn-primary" form="filterForm">Apply Filters</button>
            </div>
        </div>
    </div>
</div>
