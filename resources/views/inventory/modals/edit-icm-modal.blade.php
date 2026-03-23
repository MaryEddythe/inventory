<form id="edit-icm-form-{{ $item->id }}" class="edit-icm-form" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" value="{{ $item->division }}" disabled>
            <input type="hidden" name="division" value="{{ $item->division }}">
        </div>
        <div class="col-md-6">
            <label for="icm_no" class="form-label">ICM No</label>
            <input type="text" class="form-control form-control-sm" value="{{ $item->icm_no }}" disabled>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="requesting_personnel" class="form-label">Requesting Personnel <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="requesting_personnel" value="{{ $item->requesting_personnel }}" required>
        </div>
        <div class="col-md-6">
            <label for="classification" class="form-label">Item Classification <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="classification" value="{{ $item->classification ?? '' }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="text" class="form-control form-control-sm" name="serial_number" value="{{ $item->serial_number }}">
        </div>
        <div class="col-md-6">
            <label for="property_number" class="form-label">Property Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="property_number" value="{{ $item->property_number }}" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="brand_model" class="form-label">Brand/Model Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="brand_model" value="{{ $item->brand_model }}" required>
        </div>
        <div class="col-md-6">
            <label for="hardware_software" class="form-label">Hardware or Software <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" name="hardware_software">
                <option value="Hardware" {{ $item->hardware_software === 'Hardware' ? 'selected' : '' }}>Hardware</option>
                <option value="Software" {{ $item->hardware_software === 'Software' ? 'selected' : '' }}>Software</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="icm_type" class="form-label">Type <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" name="icm_type">
                <option value="Assistance" {{ $item->icm_type === 'Assistance' ? 'selected' : '' }}>Assistance</option>
                <option value="Troubleshoot" {{ $item->icm_type === 'Troubleshoot' ? 'selected' : '' }}>Troubleshoot</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" name="priority">
                <option value="P1-Critical" {{ $item->priority === 'P1-Critical' ? 'selected' : '' }}>P1-Critical</option>
                <option value="P2-Important" {{ $item->priority === 'P2-Important' ? 'selected' : '' }}>P2-Important</option>
                <option value="P3-Normal" {{ $item->priority === 'P3-Normal' ? 'selected' : '' }}>P3-Normal</option>
                <option value="P4-Low" {{ $item->priority === 'P4-Low' ? 'selected' : '' }}>P4-Low</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <label for="problem_description" class="form-label">Problem/Assistance Description <span class="text-danger">*</span></label>
            <textarea class="form-control form-control-sm" name="problem_description" rows="3" required>{{ $item->problem_description }}</textarea>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="open_date" class="form-label">Open Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm" name="open_date" value="{{ $item->open_date?->format('Y-m-d') }}" required>
        </div>
        <div class="col-md-6">
            <label for="open_time" class="form-label">Open Time <span class="text-danger">*</span></label>
            <input type="time" class="form-control form-control-sm" name="open_time" value="{{ $item->open_time }}" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="close_date" class="form-label">Close Date</label>
            <input type="date" class="form-control form-control-sm" name="close_date" value="{{ $item->close_date?->format('Y-m-d') }}">
        </div>
        <div class="col-md-6">
            <label for="close_time" class="form-label">Close Time</label>
            <input type="time" class="form-control form-control-sm" name="close_time" value="{{ $item->close_time }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <label for="icm_findings" class="form-label">Findings</label>
            <textarea class="form-control form-control-sm" name="icm_findings" rows="3">{{ $item->icm_findings }}</textarea>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <label for="actions_taken" class="form-label">Actions Taken</label>
            <textarea class="form-control form-control-sm" name="actions_taken" rows="3">{{ $item->actions_taken }}</textarea>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <label for="icm_recommendations" class="form-label">Recommendations</label>
            <textarea class="form-control form-control-sm" name="icm_recommendations" rows="3">{{ $item->icm_recommendations }}</textarea>
        </div>
    </div>

    <div class="text-end">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Update ICM</button>
    </div>
</form>
