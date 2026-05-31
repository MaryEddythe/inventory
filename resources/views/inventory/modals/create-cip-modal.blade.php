@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('cip.store') }}" method="POST" id="add-cip-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="cip_article" class="form-label">Article <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cip_article" name="article" value="{{ old('article') }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="cip_property_number" class="form-label">Property Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cip_property_number" name="property_number" value="{{ old('property_number') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="cip_description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" id="cip_description" name="description" rows="3" required>{{ old('description') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="cip_unit_value" class="form-label">Unit Value <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">PHP</span>
                <input type="number" step="0.01" class="form-control" id="cip_unit_value" name="unit_value" placeholder="0.00" value="{{ old('unit_value') }}" required>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="cip_date_acquired" class="form-label">Date Acquired <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="cip_date_acquired" name="date_acquired" value="{{ old('date_acquired') }}" {{ old('date_acquired_type', 'date') === 'na' ? 'disabled' : 'required' }}>
            <div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" id="cip_date_acquired_na" {{ old('date_acquired_type') === 'na' ? 'checked' : '' }}>
                <label class="form-check-label" for="cip_date_acquired_na">NA</label>
            </div>
            <input type="hidden" id="cip_date_acquired_type" name="date_acquired_type" value="{{ old('date_acquired_type', 'date') }}">
        </div>
    </div>

    <div class="mb-3">
        <label for="cip_remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="cip_remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add CIP</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('cip_date_acquired');
    const naCheckbox = document.getElementById('cip_date_acquired_na');
    const typeInput = document.getElementById('cip_date_acquired_type');

    if (!dateInput || !naCheckbox || !typeInput) return;

    naCheckbox.addEventListener('change', function() {
        if (this.checked) {
            dateInput.value = '';
            dateInput.disabled = true;
            dateInput.removeAttribute('required');
            typeInput.value = 'na';
        } else {
            dateInput.disabled = false;
            dateInput.setAttribute('required', 'required');
            typeInput.value = 'date';
        }
    });
});
</script>
