@extends('layout.app')
@section('title', 'CTO')

@section('content')
<style>
    .credits-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .credits-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    /* Popup modal for Add CTO */
    .modal-overlay{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content{
        background: #fff;
        border-radius: 10px;
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }
    .modal-header{
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display:flex;
        align-items:center;
        justify-content: space-between;
        gap:1rem;
    }
    .modal-title{ font-size: 1.2rem; font-weight: 800; color:#0f172a; }
    .modal-close{
        background:none;
        border:none;
        font-size: 1.5rem;
        cursor:pointer;
        color:#64748b;
        line-height:1;
    }
    .modal-body{ padding: 1.25rem 1.5rem; }
    .modal-footer{
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display:flex;
        justify-content:flex-end;
        gap:0.75rem;
    }

    .form-group-label{
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display:block;
    }

    /* ===== MULTIPLE EMPLOYEE SEARCH STYLES ===== */
    .search-container {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .search-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        width: 100%;
        min-height: 46px;
        padding: 0.55rem 0.75rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        background: #fff;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .search-bar.focused {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08), 0 0 0 1px rgba(0, 102, 204, 0.2);
    }

    #selectedEmployeesList {
        display: contents;
    }
    .employee-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.35rem 0.2rem 0.55rem;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border: 1px solid #93c5fd;
        border-radius: 16px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #1e40af;
        line-height: 1.4;
        white-space: nowrap;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        animation: pillIn 0.15s ease-out;
    }
    @keyframes pillIn {
        from { opacity: 0; transform: scale(0.85); }
        to { opacity: 1; transform: scale(1); }
    }
    .employee-pill-name {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .employee-pill-remove {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        line-height: 1;
        color: #3b82f6;
    }

    .employee-pill-remove:hover {
        color: #1e3a8a;
    }
    .search-bar-input {
        flex: 1;
        min-width: 140px;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0.25rem 0 !important;
        margin: 0 !important;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        height: auto !important;
    }   
    .search-bar-input:focus {
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }

    .search-bar-input::placeholder {
        color: #94a3b8;
    }

    .modal-body .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
        transition: all 0.2s ease;
        height: 44px;
        box-sizing: border-box;
    }
    .modal-body .form-control:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08), 0 0 0 1px rgba(0, 102, 204, 0.2);
    }

    .search-results {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        margin-top: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        max-height: 260px;
        overflow-y: auto;
        z-index: 20;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .search-result-item {
        width: 100%;
        padding: 0.65rem 1rem;
        border: none;
        background: transparent;
        text-align: left;
        cursor: pointer;
        font-family: inherit;
        font-size: 0.88rem;
        color: #111827;
        transition: background-color 0.12s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    .search-result-item:hover {
        background-color: #f0f7ff;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item.already-selected {
        background-color: #f0f9ff;
    }
    .search-result-empty {
        padding: 1rem;
        text-align: center;
        color: #94a3b8;
        font-size: 0.85rem;
    }

    #selectedEmployeeIds {
        display: none;
    }
    .cto-group-title {
        font-weight: 800;
        color: #0f172a;
        overflow-wrap: anywhere;
    }
    .cto-group-meta {
        color: #64748b;
        font-size: 0.82rem;
        margin-top: 0.2rem;
    }

    /* ===== SHOW EMPLOYEES POPUP MODAL ===== */
    .employees-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .employees-modal-overlay.active {
        display: flex;
    }
    .employees-modal-content {
        background: #fff;
        border-radius: 10px;
        width: 100%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }
    .employees-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #f8fafc;
    }
    .employees-modal-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .employees-modal-close {
        background: transparent;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        border-radius: 5px;
        padding: 0.4rem 0.6rem;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.85rem;
        line-height: 1;
        transition: all 0.2s ease;
    }
    .employees-modal-close:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .employees-modal-body {
        padding: 1.5rem;
    }

    /* ===== EDIT CTO POPUP MODAL ===== */
    .edit-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 4000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .edit-modal-overlay.active {
        display: flex;
    }
    .edit-modal-content {
        background: #fff;
        border-radius: 10px;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }
    .edit-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #f8fafc;
    }
    .edit-modal-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .edit-modal-body {
        padding: 1.5rem;
    }
    .edit-modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    /* Action buttons */
    .btn-action-edit {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.15s ease;
    }
    .btn-action-edit:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }
</style>


<div class="page-header">
    <div class="credits-info">
        <div class="page-title">CTO</div>
        <div class="page-subtitle">Credited Time-Off credits and credit status</div>
    </div>

    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="openCreateCtoModal()">+ Add CTO</button>
    </div>
</div>

<div class="modal-overlay" id="createCtoModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Add CTO</h2>
            <button class="modal-close" onclick="closeCreateCtoModal()">×</button>
        </div>

        <form method="POST" action="{{ route('credits.store') }}" onsubmit="handleSubmit(event)">
            @csrf
            <div class="modal-body">

                <div class="search-container" id="employeeSearchWrap">
                    <label class="form-group-label">Employees (Multiple Selection) *</label>

                    <div class="search-bar" id="employeeSearchBar">
                        <div id="selectedEmployeesList"></div>

                        <input
                            type="text"
                            id="ctoEmployeeSearch"
                            class="search-bar-input"
                            placeholder="Type to search employees..."
                            autocomplete="off"
                        >
                    </div>

                    <div
                        class="search-results"
                        id="ctoSearchResults"
                        style="display:none;"
                    ></div>

                    <input
                        type="hidden"
                        id="selectedEmployeeIds"
                        name="employee_ids"
                        value="[]"
                    >
                </div>

                <input type="hidden" name="credit_type" value="Credited Time-Off" />
                <input type="hidden" name="cto_action" value="add" />

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">S.O / T.O No *</label>
                        <input type="text" name="so_to_no" class="form-control" placeholder="Enter SO/TO number" required>
                    </div>
                    <div>
                        <label class="form-group-label">Special Order / Basis</label>
                        <input type="text" name="remarks" class="form-control" placeholder="SO name, business hours, or beyond schedule">
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="Enter location">
                    </div>
                    <div></div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Start Date *</label>
                        <input type="date" name="start_date" id="ctoStartDate" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">End Date</label>
                        <input type="date" name="end_date" id="ctoEndDate" class="form-control">
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Credit Hours *</label>
                        <input type="number" name="credit_hours" id="ctoCreditHours" class="form-control" min="0" step="1" placeholder="Enter hours" required />
                    </div>
                    <div></div>
                </div>

                <input type="hidden" name="date_applied" id="ctoDateApplied" />
                <input type="hidden" name="date_effective" id="ctoDateEffective" />

            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCreateCtoModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Create CTO</button>
            </div>
        </form>
    </div>
</div>


<!-- ===== SHOW EMPLOYEES POPUP MODAL ===== -->
<div class="employees-modal-overlay" id="showEmployeesModal">
    <div class="employees-modal-content">
        <div class="employees-modal-header">
            <div>
                <div class="employees-modal-title" id="employeesModalTitle">Employees</div>
                <div class="text-secondary" style="font-size:0.85rem;" id="employeesModalCount"></div>
            </div>
            <button class="employees-modal-close" onclick="closeShowEmployeesModal()">Close</button>
        </div>
        <div class="employees-modal-body" id="employeesModalBody">
            <!-- Dynamically populated -->
        </div>
    </div>
</div>


<!-- ===== EDIT CTO POPUP MODAL ===== -->
<div class="edit-modal-overlay" id="editCtoModal">
    <div class="edit-modal-content">
        <div class="edit-modal-header">
            <div class="edit-modal-title">Edit CTO</div>
            <button class="employees-modal-close" onclick="closeEditCtoModal()">Close</button>
        </div>
        <form method="POST" action="" id="editCtoForm" onsubmit="handleEditSubmit(event)">
            @csrf
            @method('PUT')
            <div class="edit-modal-body">
                <div class="search-container" id="editEmployeeSearchWrap">
                    <label class="form-group-label">Employees (Multiple Selection) *</label>

                    <div class="search-bar" id="editEmployeeSearchBar">
                        <div id="editSelectedEmployeesList"></div>

                        <input
                            type="text"
                            id="editCtoEmployeeSearch"
                            class="search-bar-input"
                            placeholder="Type to search employees..."
                            autocomplete="off"
                        >
                    </div>

                    <div
                        class="search-results"
                        id="editCtoSearchResults"
                        style="display:none;"
                    ></div>

                    <input
                        type="hidden"
                        id="editSelectedEmployeeIds"
                        name="employee_ids"
                        value="[]"
                    >
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Start Date *</label>
                        <input type="date" name="start_date" id="editStartDate" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">End Date</label>
                        <input type="date" name="end_date" id="editEndDate" class="form-control">
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1rem;">
                    <div>
                        <label class="form-group-label">Credit Hours *</label>
                        <input type="number" name="credit_hours" id="editCreditHours" class="form-control" min="0" step="1" required />
                    </div>
                    <div>
                        <label class="form-group-label">Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1rem;">
                    <div>
                        <label class="form-group-label">S.O / T.O No</label>
                        <input type="text" name="so_to_no" id="editSoToNo" class="form-control" placeholder="Enter SO/TO number">
                    </div>
                    <div>
                        <label class="form-group-label">Special Order / Basis</label>
                        <input type="text" name="remarks" id="editRemarks" class="form-control" placeholder="SO name, business hours, or beyond schedule">
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1rem;">
                    <div>
                        <label class="form-group-label">Location</label>
                        <input type="text" name="location" id="editLocation" class="form-control" placeholder="Enter location">
                    </div>
                    <div></div>
                </div>

                <input type="hidden" id="editDateApplied" name="date_applied" />
                <input type="hidden" name="credit_type" value="Credited Time-Off" />
            </div>

            <div class="edit-modal-footer">
                <button type="button" onclick="closeEditCtoModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Update CTO</button>
            </div>
        </form>
    </div>
</div>


<div class="card border-0 shadow-sm rounded-4" style="background: #fff;">
    <div class="card-body p-0">
        <div class="table-responsive">
            @php
                $ctoGroups = $ctoBenefits->groupBy(function ($benefit) {
                    $remarks = trim((string) $benefit->remarks);
                    return $remarks !== '' ? $remarks : 'No special order / basis';
                });
            @endphp

            <table class="table table-hover align-middle" style="margin:0;">
                <thead>
                    <tr class="text-muted" style="font-size:0.85rem; letter-spacing:0.02em;">
                        <th style="width:20%;">Special Order / Basis</th>
                        <th style="width:10%;">S.O / T.O No</th>
                        <th style="width:10%;">Location</th>
                        <th style="width:9%;">Employees</th>
                        <th style="width:10%;">Total Hours</th>
                        <th style="width:11%;">Start Date</th>
                        <th style="width:11%;">End Date</th>
                        <th style="width:10%;">Status</th>
                        <th style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ctoGroups as $basis => $groupBenefits)
                        @php
                            $groupId = 'cto-group-' . md5($basis);
                            $firstBenefit = $groupBenefits->first();
                            $startDate = $groupBenefits->min('start_date');
                            $endDate = $groupBenefits->filter(fn ($benefit) => $benefit->end_date)->max('end_date');
                            $groupLocation = $groupBenefits->map(fn ($benefit) => trim((string) ($benefit->location ?? '')))->filter()->first();
                        @endphp

                        <tr id="{{ $groupId }}">
                            <td>
                                <div class="fw-bold" style="color:#0f172a;">{{ $basis }}</div>
                                <div class="text-secondary" style="font-size:0.8rem;">{{ $firstBenefit->credit_type ?? 'Credited Time-Off' }}</div>
                            </td>
                            <td class="fw-semibold">{{ $firstBenefit->so_to_no ?: '—' }}</td>
                            <td class="fw-semibold">{{ $groupLocation ?: '—' }}</td>
                            <td class="fw-semibold">{{ $groupBenefits->count() }}</td>
                            @php
                                $firstB = $groupBenefits->first();
                                $gS = !empty($firstB->start_date) ? \Carbon\Carbon::parse($firstB->start_date) : null;
                                $gE = !empty($firstB->end_date)   ? \Carbon\Carbon::parse($firstB->end_date)   : $gS;
                                $groupTotalHours = $gS ? (($gS->diffInDays($gE) + 1) * 10) : 0;
                            @endphp
                            <td class="fw-semibold">{{ $groupTotalHours }} hrs</td>
                            <td>{{ $startDate?->format('M d, Y') }}</td>
                            <td>
                                @if($endDate)
                                    {{ $endDate->format('M d, Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2" style="background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; font-weight:700;">
                                    {{ $firstBenefit->status ?? 'ACTIVE' }}
                                </span>
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <button type="button" class="btn-action-edit" onclick="openShowEmployeesModal('{{ $groupId }}', '{{ addslashes($basis) }}', {{ $groupBenefits->count() }})">
                                    Show Employees
                                </button>
                                <button type="button" class="btn-action-edit" onclick="openEditCtoModal('{{ $firstBenefit->id }}', '{{ $firstBenefit->start_date }}', '{{ $firstBenefit->end_date ?? '' }}', '{{ $firstBenefit->credit_hours }}', '{{ $firstBenefit->status }}', '{{ addslashes($firstBenefit->remarks ?? '') }}', '{{ addslashes($firstBenefit->location ?? '') }}', '{{ addslashes($firstBenefit->so_to_no ?? '') }}', '{{ $firstBenefit->date_applied }}', {{ json_encode($groupBenefits->map(function($b) { return ['id' => (string) $b->emp_no, 'full_name' => $b->name ?? optional($b->employee)->full_name ?? '', 'employee_id' => (string) $b->emp_no, 'division_code' => $b->departments ?? 'N/A']; })->values()) }})">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-state-icon" style="font-size:1.8rem; color:#94a3b8;">–</div>
                                    <div class="empty-state-text" style="color:#64748b; font-weight:600;">No CTO credits found</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== HIDDEN EMPLOYEE DETAILS FOR MODAL ===== -->
<div style="display:none;" id="employeeDetailsContainer">
    @forelse($ctoGroups as $basis => $groupBenefits)
        @php
            $detailsId = 'cto-group-' . md5($basis);
        @endphp
        <div id="details-{{ $detailsId }}">
            <table class="table table-sm align-middle" style="background:#fff; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden;">
                <thead style="background:#f8fafc;">
                    <tr class="text-muted" style="font-size:0.78rem;">
                        <th>Name</th>
                        <th>Division</th>
                        <th>Position</th>
                        <th>Employment Type</th>
                        <th class="text-end">Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupBenefits as $benefit)
                        @php
                            $empDivision = optional($benefit->employee->division)->code ?? $benefit->departments ?? 'N/A';
                            $empPosition = $benefit->employee->position ?? $benefit->role ?? 'N/A';
                        @endphp
                        <tr>
                            <td class="fw-semibold" style="color:#0f172a;">{{ $benefit->name }}</td>
                            <td class="text-secondary">{{ $empDivision }}</td>
                            <td class="text-secondary">{{ $empPosition }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2" style="background:#f1f5f9; color:#334155; border:1px solid #e2e8f0; font-weight:800;">
                                    {{ $benefit->employment_type === 'PERMANENT' ? 'Permanent' : 'COS' }}
                                </span>
                            </td>
                            <td class="fw-semibold text-end">
                                @php
                                    $s = !empty($benefit->start_date) ? \Carbon\Carbon::parse($benefit->start_date) : null;
                                    $e = !empty($benefit->end_date)   ? \Carbon\Carbon::parse($benefit->end_date)   : $s;
                                    $hrs = $s ? (($s->diffInDays($e) + 1) * 10) : 0;
                                @endphp
                                {{ $hrs }} hrs
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
    @endforelse
</div>

<script>
    let selectedEmployees = [];
    let lastSearchItems = [];

    // ===== SHOW EMPLOYEES MODAL =====
    function openShowEmployeesModal(groupId, basis, count) {
        const modal = document.getElementById('showEmployeesModal');
        if (!modal) return;

        document.getElementById('employeesModalTitle').textContent = 'Employees - ' + basis;
        document.getElementById('employeesModalCount').textContent = count + ' selected';

        // Get the employees table from the hidden details container
        const detailsContainer = document.getElementById('details-' + groupId);
        let bodyContent = '';
        if (detailsContainer) {
            bodyContent = detailsContainer.innerHTML;
        }

        if (!bodyContent) {
            bodyContent = '<div class="text-center py-4 text-secondary">No employee details available.</div>';
        }

        document.getElementById('employeesModalBody').innerHTML = bodyContent;
        modal.classList.add('active');
    }

    function closeShowEmployeesModal() {
        const modal = document.getElementById('showEmployeesModal');
        if (modal) modal.classList.remove('active');
    }

    // ===== EDIT CTO MODAL =====
    let editSelectedEmployees = [];
    let editLastSearchItems = [];

    function openEditCtoModal(id, startDate, endDate, creditHours, status, remarks, location, soToNo, dateApplied, employees) {
        const modal = document.getElementById('editCtoModal');
        if (!modal) return;

        document.getElementById('editCtoForm').action = '{{ url("/credits") }}/' + id;
        document.getElementById('editStartDate').value = startDate;
        document.getElementById('editEndDate').value = endDate;
        document.getElementById('editCreditHours').value = creditHours;
        document.getElementById('editStatus').value = status;
        document.getElementById('editRemarks').value = remarks;
        document.getElementById('editLocation').value = location;
        document.getElementById('editSoToNo').value = soToNo;
        document.getElementById('editDateApplied').value = dateApplied || new Date().toISOString().slice(0, 10);

        // Populate employees
        editSelectedEmployees = (employees || []).map(e => ({
            id: String(e.id || e.emp_no || ''),
            full_name: e.full_name || '',
            employee_id: String(e.employee_id || e.id || e.emp_no || ''),
            division_code: e.division_code || 'N/A',
        }));

        editRenderSelectedEmployees();
        document.getElementById('editCtoSearchResults').style.display = 'none';
        document.getElementById('editCtoEmployeeSearch').value = '';

        modal.classList.add('active');
    }

    function closeEditCtoModal() {
        const modal = document.getElementById('editCtoModal');
        if (modal) modal.classList.remove('active');
        editSelectedEmployees = [];
        editLastSearchItems = [];
        document.getElementById('editSelectedEmployeesList').innerHTML = '';
        document.getElementById('editCtoSearchResults').style.display = 'none';
    }

    function handleEditSubmit(event) {
        const hiddenField = document.getElementById('editSelectedEmployeeIds');
        if (editSelectedEmployees.length === 0) {
            event.preventDefault();
            alert('Please select at least one employee.');
            return false;
        }
        hiddenField.value = JSON.stringify(editSelectedEmployees.map(e => String(e.id)));
        return true;
    }

    function editShowDropdown() {
        const el = document.getElementById('editCtoSearchResults');
        if (el) el.style.display = 'block';
    }

    function editHideDropdown() {
        const el = document.getElementById('editCtoSearchResults');
        if (el) el.style.display = 'none';
    }

    function editRenderSearchResults(items) {
        const resultsEl = document.getElementById('editCtoSearchResults');
        if (!resultsEl) return;

        if (!items || items.length === 0) {
            resultsEl.innerHTML = '<div class="search-result-empty">No employees found</div>';
            editShowDropdown();
            return;
        }

        const selectedIds = new Set(editSelectedEmployees.map(e => e.id));
        resultsEl.innerHTML = items.map(item => {
            const id = item.emp_no ?? item.id;
            const fullName = item.fullname ?? item.full_name ?? '';
            const employeeId = item.emp_no ?? item.employee_id ?? '';
            const divisionCode = item.department_name ?? item.division_code ?? '';

            const isSelected = selectedIds.has(String(id));

            return `
                <button type="button" class="search-result-item ${isSelected ? 'already-selected' : ''}" data-employee-id="${id}">
                    <div style="font-weight:700">${fullName}${isSelected ? ' <span style="color:#3b82f6;font-size:0.75em;">✓ selected</span>' : ''}</div>
                    <div style="font-size:0.82em; color:#64748b;">${employeeId} · ${divisionCode}</div>
                </button>
            `;
        }).join('');

        editShowDropdown();
    }

    function editAttachResultClickHandlers() {
        const resultsEl = document.getElementById('editCtoSearchResults');
        if (!resultsEl) return;

        resultsEl.querySelectorAll('.search-result-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const employeeIdRaw = btn.getAttribute('data-employee-id');
                const employeeId = employeeIdRaw != null ? String(employeeIdRaw) : '';

                const selected = editLastSearchItems.find(x => String(x.emp_no ?? x.id) === employeeId);
                if (!selected) return;

                const existsIndex = editSelectedEmployees.findIndex(er => String(er.id) === employeeId);
                if (existsIndex > -1) {
                    editSelectedEmployees.splice(existsIndex, 1);
                } else {
                    editSelectedEmployees.push({
                        id: String(selected.emp_no ?? selected.id),
                        full_name: selected.fullname ?? selected.full_name ?? '',
                        employee_id: selected.emp_no ?? selected.employee_id ?? '',
                        division_code: selected.department_name ?? selected.division_code ?? '',
                    });
                }

                const searchInput = document.getElementById('editCtoEmployeeSearch');
                searchInput.value = '';
                searchInput.focus();

                editRenderSelectedEmployees();
                editRenderSearchResults(editLastSearchItems);
                editAttachResultClickHandlers();
            });
        });
    }

    function editRenderSelectedEmployees() {
        const listEl = document.getElementById('editSelectedEmployeesList');
        const searchBar = document.getElementById('editEmployeeSearchBar');
        const searchInput = document.getElementById('editCtoEmployeeSearch');

        if (!listEl || !searchBar) return;

        if (editSelectedEmployees.length === 0) {
            listEl.innerHTML = '';
            searchBar.classList.remove('has-pills');
            searchInput.placeholder = 'Type to search employees...';
            return;
        }

        searchBar.classList.add('has-pills');
        searchInput.placeholder = '';

        listEl.innerHTML = editSelectedEmployees.map(emp => `
            <span class="employee-pill">
                <span class="employee-pill-name">${emp.full_name || ''}</span>
                <button type="button" class="employee-pill-remove" onclick="editRemoveSelectedEmployee('${emp.id}', event)">&times;</button>
            </span>
        `).join('');
    }

    function editRemoveSelectedEmployee(employeeId, event) {
        event.preventDefault();
        event.stopPropagation();
        const id = String(employeeId);
        editSelectedEmployees = editSelectedEmployees.filter(e => String(e.id) !== id);
        editRenderSelectedEmployees();

        if (editLastSearchItems.length > 0) {
            editRenderSearchResults(editLastSearchItems);
            editAttachResultClickHandlers();
        }

        document.getElementById('editCtoEmployeeSearch').focus();
    }

    // ===== CREATE CTO MODAL =====
    function openCreateCtoModal(){
        const el = document.getElementById('createCtoModal');
        if(!el) return;
        el.classList.add('active');
        selectedEmployees = [];
        lastSearchItems = [];
        renderSelectedEmployees();
        document.getElementById('ctoEmployeeSearch').value = '';
        document.getElementById('ctoSearchResults').style.display = 'none';
    }

    function closeCreateCtoModal(){
        const el = document.getElementById('createCtoModal');
        if(!el) return;
        el.classList.remove('active');
        selectedEmployees = [];
        lastSearchItems = [];
        document.getElementById('selectedEmployeesList').innerHTML = '';
        document.getElementById('ctoSearchResults').style.display = 'none';
    }

    function handleSubmit(event){
        const hiddenField = document.getElementById('selectedEmployeeIds');
        const startDate = document.getElementById('ctoStartDate')?.value;
        const today = new Date().toISOString().slice(0, 10);

        const dateApplied = document.getElementById('ctoDateApplied');
        const dateEffective = document.getElementById('ctoDateEffective');
        if (dateApplied) dateApplied.value = dateApplied.value || today;
        if (dateEffective) dateEffective.value = dateEffective.value || startDate || today;

        if (selectedEmployees.length === 0) {
            event.preventDefault();
            alert('Please select at least one employee.');
            return false;
        }

        hiddenField.value = JSON.stringify(selectedEmployees.map(e => String(e.id)));
    }

    async function searchEmployees(query) {
        if (!query || query.trim().length < 1) return [];

        const res = await fetch(`{{ route('api.employees.search') }}?q=${encodeURIComponent(query)}`, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!res.ok) {
            try {
                const err = await res.json();
                console.error('Employee search failed:', err);
            } catch {
                console.error('Employee search failed with status:', res.status);
            }
            return [];
        }
        return await res.json();
    }

    function showDropdown() {
        const resultsEl = document.getElementById('ctoSearchResults');
        if (resultsEl) resultsEl.style.display = 'block';
    }

    function hideDropdown() {
        const resultsEl = document.getElementById('ctoSearchResults');
        if (resultsEl) resultsEl.style.display = 'none';
    }

    function renderSearchResults(items) {
        const resultsEl = document.getElementById('ctoSearchResults');
        if (!resultsEl) return;

        if (!items || items.length === 0) {
            resultsEl.innerHTML = '<div class="search-result-empty">No employees found</div>';
            showDropdown();
            return;
        }

        const selectedIds = new Set(selectedEmployees.map(e => e.id));
        resultsEl.innerHTML = items.map(item => {
            const id = item.emp_no ?? item.id;
            const fullName = item.fullname ?? item.full_name ?? '';
            const employeeId = item.emp_no ?? item.employee_id ?? '';
            const divisionCode = item.department_name ?? item.division_code ?? '';

            const isSelected = selectedIds.has(id);

            return `
                <button type="button" class="search-result-item ${isSelected ? 'already-selected' : ''}" data-employee-id="${id}">
                    <div style="font-weight:700">${fullName}${isSelected ? ' <span style="color:#3b82f6;font-size:0.75em;">✓ selected</span>' : ''}</div>
                    <div style="font-size:0.82em; color:#64748b;">${employeeId} · ${divisionCode}</div>
                </button>
            `;
        }).join('');

        showDropdown();
    }

    function attachResultClickHandlers() {
        const resultsEl = document.getElementById('ctoSearchResults');
        if (!resultsEl) return;

        resultsEl.querySelectorAll('.search-result-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const employeeIdRaw = btn.getAttribute('data-employee-id');
                const employeeId = employeeIdRaw != null ? String(employeeIdRaw) : '';

                const selected = lastSearchItems.find(x => String(x.emp_no ?? x.id) === employeeId);
                if (!selected) return;

                const existsIndex = selectedEmployees.findIndex(er => String(er.id) === employeeId);
                if (existsIndex > -1) {
                    selectedEmployees.splice(existsIndex, 1);
                } else {
                    selectedEmployees.push({
                        id: String(selected.emp_no ?? selected.id),
                        full_name: selected.fullname ?? selected.full_name ?? '',
                        employee_id: selected.emp_no ?? selected.employee_id ?? '',
                        division_code: selected.department_name ?? selected.division_code ?? '',
                    });
                }

                const searchInput = document.getElementById('ctoEmployeeSearch');
                searchInput.value = '';
                searchInput.focus();

                renderSelectedEmployees();
                renderSearchResults(lastSearchItems);
                attachResultClickHandlers();
            });
        });
    }

    function renderSelectedEmployees() {
        const listEl = document.getElementById('selectedEmployeesList');
        const searchBar = document.getElementById('employeeSearchBar');
        const searchInput = document.getElementById('ctoEmployeeSearch');

        if (!listEl || !searchBar) return;

        if (selectedEmployees.length === 0) {
            listEl.innerHTML = '';
            searchBar.classList.remove('has-pills');
            searchInput.placeholder = 'Type to search employees...';
            return;
        }

        searchBar.classList.add('has-pills');
        searchInput.placeholder = '';

        listEl.innerHTML = selectedEmployees.map(emp => `
            <span class="employee-pill">
                <span class="employee-pill-name">${emp.full_name || ''}</span>
                <button type="button" class="employee-pill-remove" onclick="removeSelectedEmployee('${emp.id}', event)">&times;</button>
            </span>
        `).join('');
    }

    function removeSelectedEmployee(employeeId, event) {
        event.preventDefault();
        event.stopPropagation();
        const id = String(employeeId);
        selectedEmployees = selectedEmployees.filter(e => String(e.id) !== id);
        renderSelectedEmployees();

        if (lastSearchItems.length > 0) {
            renderSearchResults(lastSearchItems);
            attachResultClickHandlers();
        }

        document.getElementById('ctoEmployeeSearch').focus();
    }

    function setupEditSearch() {
        const searchInput = document.getElementById('editCtoEmployeeSearch');
        const searchBar = document.getElementById('editEmployeeSearchBar');

        if (!searchInput || !searchBar) return;

        let debounceTimer = null;

        searchInput.addEventListener('focus', () => {
            searchBar.classList.add('focused');
        });

        searchInput.addEventListener('blur', () => {
            searchBar.classList.remove('focused');
            setTimeout(() => { editHideDropdown(); }, 150);
        });

        searchInput.addEventListener('input', () => {
            const q = searchInput.value;

            clearTimeout(debounceTimer);
            if (!q || q.trim().length === 0) {
                editHideDropdown();
                return;
            }

            debounceTimer = setTimeout(async () => {
                const items = await searchEmployees(q);
                editLastSearchItems = items;
                editRenderSearchResults(items);
                editAttachResultClickHandlers();
            }, 200);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('ctoEmployeeSearch');
        const searchBar = document.getElementById('employeeSearchBar');

        if (!searchInput || !searchBar) return;

        let debounceTimer = null;

        searchInput.addEventListener('focus', () => {
            searchBar.classList.add('focused');
        });

        searchInput.addEventListener('blur', () => {
            searchBar.classList.remove('focused');
            setTimeout(() => { hideDropdown(); }, 150);
        });

        searchInput.addEventListener('input', () => {
            const q = searchInput.value;

            clearTimeout(debounceTimer);
            if (!q || q.trim().length === 0) {
                hideDropdown();
                return;
            }

            debounceTimer = setTimeout(async () => {
                const items = await searchEmployees(q);
                lastSearchItems = items;
                renderSearchResults(items);
                attachResultClickHandlers();
            }, 200);
        });

        setupEditSearch();

        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){
                closeCreateCtoModal();
                closeShowEmployeesModal();
                closeEditCtoModal();
            }
        });
    });
</script>

@endsection