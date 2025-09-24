<form action="{{ route('inventory.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="division" class="form-label">Division</label>
            <select class="form-select" id="division" name="division" required>
                <option value="" disabled selected>Select Division</option>
                <option value="MMD">MMD</option>
                <option value="MSESDD">MSESDD</option>
                <option value="GSD">GSD</option>
                <option value="GSS">GSS</option>
                <option value="ORD">ORD</option>
                <option value="FAD">FAD</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="enduser" class="form-label">End User</label>
            <input type="text" class="form-control" id="enduser" name="enduser" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="classification" class="form-label">Classification</label>
            <input type="text" class="form-control" id="classification" name="classification" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="property_number" class="form-label">Property Number</label>
            <input type="text" class="form-control" id="property_number" name="property_number" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="serial_number" class="form-label">Serial Number</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number">
        </div>
        <div class="col-md-6 mb-3">
            <label for="unit_price" class="form-label">Unit Price</label>
            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="co_mooe" class="form-label">CO/MOOE</label>
            <input type="text" class="form-control" id="co_mooe" name="co_mooe" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="date_acquired" class="form-label">Date Acquired</label>
            <input type="date" class="form-control" id="date_acquired" name="date_acquired" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks" rows="2"></textarea>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="button" class="btn btn-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Item</button>
    </div>
</form>
