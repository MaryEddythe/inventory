<form id="add-military-police-security-form" action="{{ route('military-police-security.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="article" class="form-label">Article <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="article" name="article" value="{{ old('article') }}" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="property_number" class="form-label">Property Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="property_number" name="property_number" value="{{ old('property_number') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="unit_value" class="form-label">Unit Value <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="unit_value" name="unit_value" value="{{ old('unit_value') }}" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="date_acquired" class="form-label">Date Acquired <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="date_acquired" name="date_acquired" value="{{ old('date_acquired') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Military, Police &amp; Security</button>
    </div>
</form>
