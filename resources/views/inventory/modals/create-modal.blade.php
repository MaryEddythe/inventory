@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('inventory.store') }}" method="POST" id="add-inventory-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="division" class="form-label">Division</label>
            <select class="form-select" id="division" name="division" required>
                <option value="" disabled {{ old('division') ? '' : 'selected' }}>Select Division</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department }}" {{ old('division') == $dept->department ? 'selected' : '' }}>{{ $dept->department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="enduser" class="form-label">End User</label>
            <input type="text" class="form-control" id="enduser" name="enduser" value="{{ old('enduser') }}" oninput="filterEmployees()" required>
            <div id="employee-suggestions" class="suggestions-list"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="classification" class="form-label">Classification</label>
            <input type="text" class="form-control" id="classification" name="classification" value="{{ old('classification') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="property_number" class="form-label">Property Number</label>
            <input type="text" class="form-control" id="property_number" name="property_number" value="{{ old('property_number') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="unit_price" class="form-label">Unit Price</label>
            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" value="{{ old('unit_price') }}" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe" class="form-label">CO/MOOE</label>
            <input type="text" class="form-control" id="co_mooe" name="co_mooe" value="{{ old('co_mooe') }}" required>
        </div>  
        <div class="col-md-6 mb-3">
            <label for="date_acquired" class="form-label">Date Acquired</label>
            <input type="date" class="form-control" id="date_acquired" name="date_acquired" value="{{ old('date_acquired') }}" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Item</button>
    </div>
</form>

<script>
    var employees = [
        @foreach($employees as $employee)
            {name: '{{ $employee->firstname }} {{ $employee->lastname }}'},
        @endforeach
    ];

    function filterEmployees() {
        var input = document.getElementById('enduser');
        var filter = input.value.toLowerCase();
        var suggestions = document.getElementById('employee-suggestions');
        suggestions.innerHTML = '';

        if (filter.length === 0) {
            suggestions.style.display = 'none';
            return;
        }

        var filtered = employees.filter(function(emp) {
            return emp.name.toLowerCase().includes(filter);
        });

        if (filtered.length > 0) {
            suggestions.style.display = 'block';
            filtered.forEach(function(emp) {
                var div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = highlightMatch(emp.name, filter);
                div.onclick = function() {
                    input.value = emp.name;
                    suggestions.style.display = 'none';
                };
                suggestions.appendChild(div);
            });
        } else {
            suggestions.style.display = 'none';
        }
    }

    function highlightMatch(text, filter) {
        var regex = new RegExp('(' + filter.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    document.addEventListener('click', function(e) {
        var suggestions = document.getElementById('employee-suggestions');
        var input = document.getElementById('enduser');
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
</script>
