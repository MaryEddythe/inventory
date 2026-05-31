@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('motor-vehicle.store') }}" method="POST" id="add-motor-vehicle-form">
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
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" class="form-control" id="unit_value" name="unit_value" placeholder="0.00" value="{{ old('unit_value') }}" required>
            </div>
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

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Motor Vehicle</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addMotorVehicleForm = document.getElementById('add-motor-vehicle-form');
    if (addMotorVehicleForm && !addMotorVehicleForm._listenerAttached) {
        addMotorVehicleForm._listenerAttached = true;
        addMotorVehicleForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (this._isSubmitting) {
                console.warn('❌ BLOCKED: Form submission already in progress');
                return;
            }
            this._isSubmitting = true;

            const formData = new FormData(this);

            Swal.fire({
                title: 'Adding Motor Vehicle...',
                text: 'Please wait while we add the vehicle',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'CSRF token not found. Please refresh the page.'
                });
                this._isSubmitting = false;
                return;
            }

            fetch('{{ route("motor-vehicle.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                addMotorVehicleForm._isSubmitting = false;
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#addMotorVehicleModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'An error occurred while adding the vehicle'
                    });
                }
            })
            .catch(error => {
                addMotorVehicleForm._isSubmitting = false;
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error!',
                    text: 'An error occurred while adding the vehicle. Check console for details.'
                });
            });
        });
    }
});
</script>
