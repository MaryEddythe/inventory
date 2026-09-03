<div class="modal fade" id="attendanceRecordEditModal" tabindex="-1" aria-labelledby="attendanceRecordEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5 fw-bold mb-1" id="attendanceRecordEditModalLabel">Edit Attendance Record</h2>
                    <div class="text-muted small" id="attendanceRecordEditSubtitle">Update the selected employee's check-in and check-out times.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="" id="attendanceRecordEditForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="attendance_record_form" value="1">
                    <input type="hidden" name="attendance_record_id" id="attendanceRecordId" value="{{ old('attendance_record_id') }}">
                    <input type="hidden" name="attendance_record_employee_name" id="attendanceRecordEmployeeNameHidden" value="{{ old('attendance_record_employee_name') }}">
                    <input type="hidden" name="attendance_record_date" id="attendanceRecordDateHidden" value="{{ old('attendance_record_date') }}">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee</label>
                            <input type="text" class="form-control" id="attendanceRecordEmployee" value="{{ old('attendance_record_employee_name', old('attendance_record_id') ? 'Attendance record #' . old('attendance_record_id') : '') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attendance Date</label>
                            <input type="text" class="form-control" id="attendanceRecordDate" value="{{ old('attendance_record_date', old('attendance_date')) }}" readonly>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in Time</label>
                            <input type="time" name="check_in_at" id="attendanceRecordCheckIn" class="form-control" value="{{ old('check_in_at') }}">
                            @error('check_in_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out Time</label>
                            <input type="time" name="check_out_at" id="attendanceRecordCheckOut" class="form-control" value="{{ old('check_out_at') }}">
                            @error('check_out_at')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        function getModal() {
            const modalElement = document.getElementById('attendanceRecordEditModal');
            if (!modalElement || !window.bootstrap) {
                return null;
            }

            return bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        function setValue(id, value) {
            const field = document.getElementById(id);
            if (!field) {
                return;
            }

            field.value = value ?? '';
        }

        function openAttendanceRecordEditModal(trigger) {
            const modal = getModal();
            const form = document.getElementById('attendanceRecordEditForm');

            if (!modal || !form || !trigger) {
                return;
            }

            form.action = trigger.dataset.updateUrl || form.action;
            setValue('attendanceRecordId', trigger.dataset.recordId);
            setValue('attendanceRecordEmployee', trigger.dataset.employeeName);
            setValue('attendanceRecordDate', trigger.dataset.attendanceDate);
            setValue('attendanceRecordEmployeeNameHidden', trigger.dataset.employeeName);
            setValue('attendanceRecordDateHidden', trigger.dataset.attendanceDate);
            setValue('attendanceRecordCheckIn', trigger.dataset.checkIn);
            setValue('attendanceRecordCheckOut', trigger.dataset.checkOut);

            const subtitle = document.getElementById('attendanceRecordEditSubtitle');
            if (subtitle) {
                subtitle.textContent = `${trigger.dataset.employeeName || 'Attendance record'} on ${trigger.dataset.attendanceDate || ''}`;
            }

            modal.show();
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-attendance-record-edit="true"]');
            if (!trigger) {
                return;
            }

            if (trigger.tagName === 'BUTTON' || trigger.tagName === 'A') {
                event.preventDefault();
            }

            openAttendanceRecordEditModal(trigger);
        });

        document.addEventListener('keydown', function (event) {
            const trigger = event.target.closest('[data-attendance-record-edit="true"]');
            if (!trigger) {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openAttendanceRecordEditModal(trigger);
            }
        });

        @if(old('attendance_record_form') && old('attendance_record_id'))
        document.addEventListener('DOMContentLoaded', function () {
            const trigger = {
                dataset: {
                    updateUrl: @json(route('attendance.records.update', old('attendance_record_id'))),
                    recordId: @json(old('attendance_record_id')),
                    employeeName: @json('Attendance record #' . old('attendance_record_id')),
                    attendanceDate: @json(old('attendance_record_date', old('attendance_date', ''))),
                    checkIn: @json(old('check_in_at', '')),
                    checkOut: @json(old('check_out_at', '')),
                },
            };

            openAttendanceRecordEditModal(trigger);
        });
        @endif
    })();
</script>
