@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('inventory.update', $item->id) }}" method="POST" id="edit-ipm-form-{{ $item->id }}" class="edit-ipm-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="emp_no" value="{{ $item->emp_no }}">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="condition-{{ $item->id }}" class="form-label">Condition</label>
            <select class="form-select" id="condition-{{ $item->id }}" name="condition" required>
                <option value="Functional" {{ old('condition', $item->condition) == 'Functional' ? 'selected' : '' }}>Functional</option>
                <option value="Nonfunctional" {{ old('condition', $item->condition) == 'Nonfunctional' ? 'selected' : '' }}>Nonfunctional</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_conducted-{{ $item->id }}" class="form-label">Date Conducted</label>
            <input type="date" class="form-control" id="date_conducted-{{ $item->id }}" name="date_conducted" value="{{ old('date_conducted', $item->date_conducted ? $item->date_conducted->format('Y-m-d') : '') }}">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="time_started-{{ $item->id }}" class="form-label">Time Started</label>
            <input type="time" class="form-control" id="time_started-{{ $item->id }}" name="time_started" value="{{ old('time_started', $item->time_started ? \Carbon\Carbon::parse($item->time_started)->format('H:i') : '') }}">
        </div>
        <div class="col-md-6 mb-3">
            <label for="time_ended-{{ $item->id }}" class="form-label">Time Ended</label>
            <input type="time" class="form-control" id="time_ended-{{ $item->id }}" name="time_ended" value="{{ old('time_ended', $item->time_ended ? \Carbon\Carbon::parse($item->time_ended)->format('H:i') : '') }}">
        </div>
    </div>
    <div class="mb-3">
        <label for="recommendation-{{ $item->id }}" class="form-label">Recommendation</label>
        <textarea class="form-control" id="recommendation-{{ $item->id }}" name="recommendation" rows="2">{{ old('recommendation', $item->recommendation) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Checks</label>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="system_boot_up-{{ $item->id }}" name="system_boot_up" value="1" {{ old('system_boot_up', $item->system_boot_up) ? 'checked' : '' }}>
                    <label class="form-check-label" for="system_boot_up-{{ $item->id }}">System Boot Up</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="hardware-{{ $item->id }}" name="hardware" value="1" {{ old('hardware', $item->hardware) ? 'checked' : '' }}>
                    <label class="form-check-label" for="hardware-{{ $item->id }}">Hardware</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="performance-{{ $item->id }}" name="performance" value="1" {{ old('performance', $item->performance) ? 'checked' : '' }}>
                    <label class="form-check-label" for="performance-{{ $item->id }}">Performance</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cables_connections-{{ $item->id }}" name="cables_connections" value="1" {{ old('cables_connections', $item->cables_connections) ? 'checked' : '' }}>
                    <label class="form-check-label" for="cables_connections-{{ $item->id }}">Cables and Connections</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="peripherals-{{ $item->id }}" name="peripherals" value="1" {{ old('peripherals', $item->peripherals) ? 'checked' : '' }}>
                    <label class="form-check-label" for="peripherals-{{ $item->id }}">Peripherals</label>
                </div>
            </div>
        </div>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update IPM</button>
    </div>
</form>
