@extends('layout.app')
@section('title', 'Leave Credits')

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
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        padding: 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .modal-close:hover {
        color: #0f172a;
    }
    .modal-body {
        padding: 1.75rem;
    }
    .modal-footer {
        padding: 1.75rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
    .search-container {
        position: relative;
        margin-bottom: 1.25rem;
    }
    .search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
    }
    .search-input:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08);
    }
    .search-results {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-top: none;
        border-radius: 0 0 5px 5px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .search-results.active {
        display: block;
    }
    .search-result-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background: #f8fafc;
    }
    .search-result-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.9rem;
    }
    .search-result-info {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.2rem;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .form-grid.full {
        grid-template-columns: 1fr;
    }
    .form-group-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
        transition: all 0.2s ease;
    }
    .form-control:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08);
    }
    .form-control:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
</style>

<div class="page-header">
    <div class="credits-info">
        <div class="page-title">Leave Credits</div>
        <div class="page-subtitle">All employee leave benefits and credit status</div>
    </div>
    <button onclick="openCreateModal()" class="btn btn-primary">+ Add Leave Credit</button>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Division</th>
                <th>Position</th>
                <th>Employment Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Leave Type</th>
                <th>Status</th>
                <th>Actions</th>

            </tr>
        </thead>
        <tbody>
            @forelse($allBenefits as $benefit)
            <tr>
                <td>
                    <span class="badge badge-blue">{{ $benefit->employee->employee_id ?? 'N/A' }}</span>
                </td>
                <td>
                    <div class="leave-type-cell">{{ $benefit->name }}</div>
                </td>
                <td>{{ $benefit->division ?? 'N/A' }}</td>
                <td>{{ $benefit->position ?? 'N/A' }}</td>
                <td>{{ $benefit->employment_type === 'PERMANENT' ? 'Permanent' : 'COS' }}</td>
                <td>
                    <div class="leave-date">{{ $benefit->start_date->format('M d, Y') }}</div>
                </td>
                <td>
                    <div class="leave-date">
                        @if($benefit->end_date)
                            {{ $benefit->end_date->format('M d, Y') }}
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="leave-type-cell">{{ $benefit->credit_type }}</div>
                </td>
                <td>
                    <span class="status-badge status-{{ strtolower($benefit->status) }}">
                        {{ $benefit->status }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:0.5rem;">
                        <button type="button" onclick="openEditModal({{ $benefit->id }})" class="btn btn-outline btn-sm">Edit</button>
                        <form method="POST" action="{{ route('credits.destroy', $benefit->id) }}" onsubmit="return confirm('Delete leave credit?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <div class="empty-state-icon">–</div>
                        <div class="empty-state-text">No leave credits found</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Add Leave Credit</h2>
            <button class="modal-close" onclick="closeCreateModal()">×</button>
        </div>
        <form method="POST" action="{{ route('credits.store') }}" onsubmit="handleSubmit(event)">
            @csrf
            <input type="hidden" name="cto_action" id="ctoAction" value="deduct">
                <div class="modal-body">
                <div class="search-container">
                    <label class="form-group-label">Employee *</label>
                    <input type="text" id="employeeSearch" class="search-input" placeholder="Search employees..." autocomplete="off" required>
                    <div class="search-results" id="searchResults"></div>
                    <input type="hidden" id="employeeId" name="employee_id" required>
                </div>


                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Division</label>
                        <input type="text" id="division" class="form-control" disabled>
                    </div>
                    <div>
                        <label class="form-group-label">Position</label>
                        <input type="text" id="position" class="form-control" disabled>
                    </div>
                </div>

                <div>
                    <label class="form-group-label">Employment Type</label>
                    <input type="text" id="employmentType" class="form-control" disabled>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>

                <div>
                    <label class="form-group-label">Leave Type *</label>
                    <select name="credit_type" id="creditTypeSelect" class="form-control" required>
                        <option value="">-- Select Leave Type --</option>
                        @foreach($leaveTypesFromDb as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="creditHoursWrapper" style="display:none;">
                    <div class="form-grid full">
                        <div>
                            <label class="form-group-label">Credit Hours *</label>
                            <input type="number" name="credit_hours" id="creditHoursInput" class="form-control" min="0" step="1" placeholder="Enter hours" />
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Date Applied *</label>
                        <input type="date" name="date_applied" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">Date Effective *</label>
                        <input type="date" name="date_effective" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeCreateModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Leave Credit</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Leave Credit</h2>
            <button class="modal-close" onclick="closeEditModal()">×</button>
        </div>
        <form method="POST" id="editForm" onsubmit="handleEditSubmit(event)">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-grid full">
                    <div>
                        <label class="form-group-label">Employee</label>
                        <input type="text" id="editEmployeeName" class="form-control" disabled>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Division</label>
                        <input type="text" id="editDivision" class="form-control" disabled>
                    </div>
                    <div>
                        <label class="form-group-label">Position</label>
                        <input type="text" id="editPosition" class="form-control" disabled>
                    </div>
                </div>

                <div class="form-grid full">
                    <div>
                        <label class="form-group-label">Employment Type</label>
                        <input type="text" id="editEmploymentType" class="form-control" disabled>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Start Date *</label>
                        <input type="date" id="editStartDate" name="start_date" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">End Date</label>
                        <input type="date" id="editEndDate" name="end_date" class="form-control">
                    </div>
                </div>

                <div class="form-grid full">
                    <div>
                        <label class="form-group-label">Leave Type *</label>
                        <select name="credit_type" id="editCreditType" class="form-control" required>
                            <option value="">-- Select Leave Type --</option>
                        </select>
                    </div>
                </div>

                <div id="editCreditHoursWrapper" style="display:none;">
                    <div class="form-grid full">
                        <div>
                            <label class="form-group-label">Credit Hours *</label>
                            <input type="number" name="credit_hours" id="editCreditHours" class="form-control" min="0" step="1" placeholder="Enter hours" />
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label class="form-group-label">Date Applied *</label>
                        <input type="date" id="editDateApplied" name="date_applied" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-group-label">Date Effective *</label>
                        <input type="date" id="editDateEffective" name="date_effective" class="form-control" required>
                    </div>
                </div>

                <div class="form-grid full">
                    <div>
                        <label class="form-group-label">Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    const employeeSearch = document.getElementById('employeeSearch');
    const searchResults = document.getElementById('searchResults');
    const employeeIdInput = document.getElementById('employeeId');
    const divisionField = document.getElementById('division');
    const positionField = document.getElementById('position');
    const employmentTypeField = document.getElementById('employmentType');
    const creditTypeSelect = document.getElementById('creditTypeSelect');

    const leaveTypesByEmploymentType = {
        'PERMANENT': @json($leaveTypesPermanent),
        'COS': @json($leaveTypesCos),
    };

    function updateCreditTypeOptions() {
        // Normalize employment type from API/inputs to match keys in leaveTypesByEmploymentType:
        // 'PERMANENT' or 'COS'
        let employmentType = (employmentTypeField?.value || '').toString().trim();

        const upper = employmentType.toUpperCase();
        if (upper.includes('PER')) {
            employmentType = 'PERMANENT';
        } else if (upper.includes('COS')) {
            employmentType = 'COS';
        }

        const allowed = leaveTypesByEmploymentType[employmentType] || [];

        creditTypeSelect.innerHTML = '<option value="">-- Select Leave Type --</option>';

        allowed.forEach(type => {
            const opt = document.createElement('option');
            opt.value = type;
            opt.textContent = type;
            creditTypeSelect.appendChild(opt);
        });

        updateCreditHoursVisibility();

        if (window.__createCtoMode === true) {
            if (allowed.includes('Credited Time-Off')) {
                creditTypeSelect.value = 'Credited Time-Off';
                updateCreditHoursVisibility();
                return;
            }

            if (employmentType === 'COS' && allowed.includes('Wellness Leave')) {
                creditTypeSelect.value = 'Wellness Leave';
                updateCreditHoursVisibility();
            }
        } else {
            if (employmentType === 'COS' && allowed.includes('Wellness Leave') && creditTypeSelect.value === '') {
                creditTypeSelect.value = 'Wellness Leave';
                updateCreditHoursVisibility();
            }
        }
    }

    function updateCreditHoursVisibility() {
        const wrapper = document.getElementById('creditHoursWrapper');
        const input = document.getElementById('creditHoursInput');
        if (!wrapper || !input) return;

        const creditType = (creditTypeSelect?.value || '').toString().toLowerCase();
        const isCto = creditType.includes('cto') || creditType.includes('credited time-off') || creditType.includes('credited time off') || creditType.includes('credited');

        // Always deduct for CTO (backend reads cto_action when it detects CTO credit_type)
        const ctoActionHidden = document.getElementById('ctoAction');
        if (ctoActionHidden) {
            ctoActionHidden.value = isCto ? 'deduct' : '';
        }

        wrapper.style.display = isCto ? 'block' : 'none';
        if (isCto) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
            input.value = '';
        }
    }

    updateCreditTypeOptions();

    creditTypeSelect.addEventListener('change', () => {
        updateCreditHoursVisibility();
    });

    employeeSearch.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        if (query.length < 1) {
            searchResults.classList.remove('active');
            return;
        }

        try {
            const response = await fetch(`{{ route('api.employees.search') }}?q=${encodeURIComponent(query)}`);
            const employees = await response.json();

            if (employees.length === 0) {
                searchResults.innerHTML = '<div style="padding: 0.75rem 1rem; color: #94a3b8;">No employees found</div>';
                searchResults.classList.add('active');
                return;
            }

            searchResults.innerHTML = employees.map(emp => `
                <div class="search-result-item" onclick="selectEmployee(${emp.id}, '${emp.full_name}', '${emp.division_code}', '${emp.position}', '${emp.employment_type}')">
                    <div class="search-result-name">${emp.full_name}</div>
                    <div class="search-result-info">${emp.employee_id} · ${emp.division_code} · ${emp.position}</div>
                </div>
            `).join('');
            searchResults.classList.add('active');
        } catch (error) {
            console.error('Search error:', error);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            searchResults.classList.remove('active');
        }
    });

    function selectEmployee(id, name, division, position, employmentType) {
        employeeSearch.value = name;
        employeeIdInput.value = id;
        divisionField.value = division;
        positionField.value = position;
        employmentTypeField.value = employmentType;

        updateCreditTypeOptions();

        if (window.__createCtoMode === true) {
            const trimmed = (employmentType || '').toString().trim();
            const allowed = leaveTypesByEmploymentType[trimmed] || [];
            if (allowed.includes('Credited Time-Off')) {
                creditTypeSelect.value = 'Credited Time-Off';
            } else if (trimmed === 'COS' && allowed.includes('Wellness Leave')) {
                creditTypeSelect.value = 'Wellness Leave';
            } else if (allowed.length) {
                creditTypeSelect.value = allowed[0];
            }
            updateCreditHoursVisibility();
        }

        searchResults.classList.remove('active');
    }

    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
        const form = document.querySelector('#createModal form');
        if (form) form.reset();
        employeeIdInput.value = '';
        divisionField.value = '';
        positionField.value = '';
        employmentTypeField.value = '';
        employeeSearch.value = '';

        window.__createCtoMode = false;
    }

    function openCreateCtoModal() {
        window.__createCtoMode = true;
        openCreateModal();
        creditTypeSelect.value = '';
        updateCreditHoursVisibility();
    }

    function openEditModal(benefitId) {
        try {
            const benefit = allBenefits.find(b => Number(b.id) === Number(benefitId));
            if (!benefit) {
                console.error('Benefit not found in allBenefits for id:', benefitId, allBenefits);
                return;
            }

            document.getElementById('editEmployeeName').value = benefit.name ?? benefit.employee?.full_name ?? 'N/A';
            document.getElementById('editDivision').value = benefit.division ?? benefit.employee?.division?.code ?? 'N/A';
            document.getElementById('editPosition').value = benefit.position ?? benefit.employee?.position ?? 'N/A';
            document.getElementById('editEmploymentType').value = (benefit.employment_type === 'PERMANENT') ? 'Permanent' : 'COS';
            document.getElementById('editStartDate').value = benefit.start_date ?? '';
            document.getElementById('editEndDate').value = benefit.end_date ?? '';
            document.getElementById('editDateApplied').value = benefit.date_applied ?? '';
            document.getElementById('editDateEffective').value = benefit.date_effective ?? '';
            document.getElementById('editStatus').value = benefit.status ?? 'ACTIVE';

            updateEditCreditTypeOptions(benefit.employment_type, benefit.credit_type);
            document.getElementById('editCreditHours').value = benefit.credit_hours ?? '';
            updateEditCreditHoursVisibility();

            document.getElementById('editForm').action = `/credits/${benefitId}`;
            document.getElementById('editModal').classList.add('active');
        } catch (e) {
            console.error('openEditModal failed:', e);
        }
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function updateEditCreditTypeOptions(employmentType, selectedType) {
        const allowed = leaveTypesByEmploymentType[employmentType] || [];
        const select = document.getElementById('editCreditType');
        select.innerHTML = '<option value="">-- Select Leave Type --</option>';

        allowed.forEach(type => {
            const opt = document.createElement('option');
            opt.value = type;
            opt.textContent = type;
            if (type === selectedType) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function updateEditCreditHoursVisibility() {
        const wrapper = document.getElementById('editCreditHoursWrapper');
        const input = document.getElementById('editCreditHours');
        const creditType = (document.getElementById('editCreditType')?.value || '').toString().toLowerCase();
        const isCto = creditType.includes('cto') || creditType.includes('credited time-off') || creditType.includes('credited time off') || creditType.includes('credited');

        wrapper.style.display = isCto ? 'block' : 'none';
        if (isCto) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
        }
    }

    document.getElementById('editCreditType')?.addEventListener('change', updateEditCreditHoursVisibility);

    function handleEditSubmit(event) {
        event.preventDefault();
        document.getElementById('editForm').submit();
    }

    const allBenefits = @json($allBenefits);
</script>

@endsection

