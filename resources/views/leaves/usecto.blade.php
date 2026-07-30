<div id="ctoLeaveFields" class="leave-form-grid full d-none">
    <div class="border rounded-3 p-3 bg-light">
        <div class="fw-bold mb-1">Use Credited Time-Off</div>
        <div class="text-muted small mb-3">Choose the CTO credit shown in Leave Credits History, using its recorded remarks.</div>
        <div class="mb-3">
            <label class="leave-form-label" for="ctoLeaveHistoryId">Available CTO *</label>
            <select name="cto_leave_history_id" id="ctoLeaveHistoryId" class="leave-form-control" disabled>
                <option value="">-- Select CTO credit --</option>
                @forelse($ctoHistory as $credit)
                    <option value="{{ $credit->id }}">
                        {{ $credit->remarks }} — {{ $credit->credit_hours }} credited hours
                        @if($credit->start_date) ({{ $credit->start_date->format('M d, Y') }}) @endif
                    </option>
                @empty
                    <option value="" disabled>No available CTO credits found.</option>
                @endforelse
            </select>
        </div>
        <div class="mb-3">
            <label class="leave-form-label" for="ctoSoToNo">S.O / T.O No:</label>
            <input type="text" name="cto_so_to_no" id="ctoSoToNo" class="leave-form-control" placeholder="Enter SO/TO number" disabled>
        </div>
        <div>
            <label class="leave-form-label" for="ctoDuration">Time to use *</label>
            <select name="cto_duration" id="ctoDuration" class="leave-form-control" disabled>
                <option value="">-- Select duration --</option>
                <option value="am">AM — deduct 4 hours</option>
                <option value="pm">PM — deduct 6 hours</option>
                <option value="whole_day">Whole Day — deduct 10 hours (1 day)</option>
            </select>
        </div>
    </div>
</div>
