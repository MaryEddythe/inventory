<!-- Filter Dropdown Panel -->
<div id="filterDropdown" class="filter-dropdown-panel shadow-lg rounded-2 p-3" style="display: none; position: absolute; top: 100%; right: 0; min-width: 350px; z-index: 1000; background: white; margin-top: 8px;">
    <form id="filterForm" method="get" class="row g-3">
        <!-- Status Filter -->
        <div class="col-12">
            <label for="statusSelect" class="form-label fw-600 small text-muted">Status</label>
            <select class="form-select form-select-sm" id="statusSelect" name="status">
                <option value="">All Status</option>
                <option value="≤ 5 years">≤ 5 years</option>
                <option value="> 5 years">> 5 years</option>
            </select>
        </div>

        <!-- RPCSP / PPE Filter -->
        <div class="col-12">
            <label for="ppeTypeSelect" class="form-label fw-600 small text-muted">Type</label>
            <select class="form-select form-select-sm" id="ppeTypeSelect" name="ppe_type">
                <option value="">All</option>
                <option value="rpcsp">RPCSP (≤ 49,999)</option>
                <option value="ppe">PPE (≥ 50,000)</option>
            </select>
        </div>

        <!-- Classification Filter -->
        <div class="col-12">
            <span class="form-label fw-600 small text-muted d-block">Classification</span>
            @php
                $selectedClassifications = (array) request('classification', []);
            @endphp
            <div class="row g-2">
                @if(isset($classifications))
                    @foreach($classifications as $classification)
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    id="classification-{{ Str::slug($classification) }}"
                                    name="classification[]" value="{{ $classification }}"
                                    {{ in_array($classification, $selectedClassifications, true) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="classification-{{ Str::slug($classification) }}">
                                    {{ $classification }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>


        <!-- Division Filter -->
        <div class="col-12">
            <label for="divisionSelect" class="form-label fw-600 small text-muted">Division</label>
            <select class="form-select form-select-sm" id="divisionSelect" name="division">
                <option value="">All Divisions</option>
                @if(isset($departments) && $departments->count() > 0)
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Date Range Filter -->
        <div class="col-12">
            <label class="form-label fw-600 small text-muted">Date Acquired Range</label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="date" class="form-control form-control-sm" id="dateFrom" name="date_from">
                </div>
                <div class="col-6">
                    <input type="date" class="form-control form-control-sm" id="dateTo" name="date_to">
                </div>
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="col-12 d-flex gap-2 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="clearFilters">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear
            </button>
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-check-circle me-1"></i>Apply
            </button>
        </div>
    </form>
</div>

