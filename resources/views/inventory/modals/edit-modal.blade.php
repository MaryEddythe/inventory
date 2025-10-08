@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('inventory.update', $item->no) }}" method="POST" id="edit-inventory-form-{{ $item->no }}" class="edit-inventory-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="_method" value="PUT">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="division-{{ $item->no }}" class="form-label">Division</label>
            <select class="form-select" id="division-{{ $item->no }}" name="division" required>
                <option value="" disabled>Select Division</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department }}" {{ old('division', $item->division) == $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="enduser-{{ $item->no }}" class="form-label">End User</label>
            <input type="text" class="form-control" id="enduser-{{ $item->no }}" name="enduser" value="{{ old('enduser', $item->enduser) }}" oninput="filterEmployees('{{ $item->no }}')" required>
            <input type="hidden" id="emp_no-{{ $item->no }}" name="emp_no" value="{{ old('emp_no', $item->emp_no) }}">
            <div id="employee-suggestions-{{ $item->no }}" class="suggestions-list"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="classification-{{ $item->no }}" class="form-label">Classification</label>
            <input type="text" class="form-control" id="classification-{{ $item->no }}" name="classification" value="{{ old('classification', $item->classification) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="property_number-{{ $item->no }}" class="form-label">Property Number</label>
            <input type="text" class="form-control" id="property_number-{{ $item->no }}" name="property_number" value="{{ old('property_number', $item->property_number) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description-{{ $item->no }}" class="form-label">Description</label>
        <textarea class="form-control" id="description-{{ $item->no }}" name="description" rows="3" required>{{ old('description', $item->description) }}</textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serial_number-{{ $item->no }}" class="form-label">Serial Number</label>
            <input type="text" class="form-control" id="serial_number-{{ $item->no }}" name="serial_number" value="{{ old('serial_number', $item->serial_number) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="unit_price-{{ $item->no }}" class="form-label">Unit Price</label>
            <input type="number" step="0.01" class="form-control" id="unit_price-{{ $item->no }}" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe-{{ $item->no }}" class="form-label">CO/MOOE</label>
            <select class="form-select" id="co_mooe-{{ $item->no }}" name="co_mooe" required>
                <option value="" disabled>Select CO/MOOE</option>
                <option value="CO" {{ old('co_mooe', $item->co_mooe) == 'Capital Outlay' ? 'selected' : '' }}>Capital Outlay</option>
                <option value="MOOE" {{ old('co_mooe', $item->co_mooe) == 'MOOE' ? 'selected' : '' }}>MOOE</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_acquired-{{ $item->no }}" class="form-label">Date Acquired</label>
            <input type="date" class="form-control" id="date_acquired-{{ $item->no }}" name="date_acquired" value="{{ old('date_acquired', $item->date_acquired->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks-{{ $item->no }}" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks-{{ $item->no }}" name="remarks" rows="2">{{ old('remarks', $item->remarks) }}</textarea>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Item</button>
    </div>
</form>

<script>
    var employees = [
        @foreach($employees as $employee)
            {name: '{{ $employee->firstname }} {{ $employee->lastname }}', emp_no: '{{ $employee->emp_no }}'},
        @endforeach
    ];

    function filterEmployees(itemId) {
        var input = document.getElementById('enduser-' + itemId);
        var empNoInput = document.getElementById('emp_no-' + itemId);
        var filter = input.value.toLowerCase();
        var suggestions = document.getElementById('employee-suggestions-' + itemId);
        suggestions.innerHTML = '';

        if (filter.length === 0) {
            suggestions.style.display = 'none';
            empNoInput.value = '';
            return;
        }

        var filtered = employees.filter(function(emp) {
            return emp.name.toLowerCase().includes(filter) || emp.emp_no.toLowerCase().includes(filter);
        });

        if (filtered.length > 0) {
            suggestions.style.display = 'block';
            filtered.forEach(function(emp) {
                var div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = highlightMatch(`${emp.name} (${emp.emp_no})`, filter);
                div.onclick = function() {
                    input.value = emp.name;
                    empNoInput.value = emp.emp_no;
                    suggestions.style.display = 'none';
                };
                suggestions.appendChild(div);
            });
        } else {
            suggestions.style.display = 'none';
            empNoInput.value = '';
        }
    }

    function highlightMatch(text, filter) {
        var regex = new RegExp('(' + filter.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    document.addEventListener('click', function(e) {
        var suggestions = document.getElementById('employee-suggestions-{{ $item->no }}');
        var input = document.getElementById('enduser-{{ $item->no }}');
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
</script>