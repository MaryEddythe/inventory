<div id="ctoLeaveFields" class="leave-form-grid full d-none">
    <div class="border rounded-3 p-3 bg-light">
        <div class="fw-bold mb-1">Use Credited Time-Off</div>
        <div class="text-muted small mb-3">Choose the CTO credit using its recorded remarks, then select the time to use.</div>

        <div class="mb-3">
            <label class="leave-form-label" for="ctoLeaveHistoryId">Available CTO *</label>
            <select name="cto_leave_history_id" id="ctoLeaveHistoryId" class="leave-form-control" disabled>
                <option value="">-- Select CTO credit --</option>
                @forelse($ctoHistory as $history)
                    <option value="{{ $history->id }}">
                        {{ $history->remarks ?: 'No remarks provided' }} — {{ $history->credits_added }} credited hours
                        @if($history->created_at) ({{ $history->created_at->format('M d, Y') }}) @endif
                    </option>
                @empty
                    <option value="" disabled>No CTO credits with remarks are available.</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="leave-form-label">Time to use *</label>
            <select name="cto_duration" id="ctoDuration" class="leave-form-control" disabled>
                <option value="">-- Select duration --</option>
                <option value="am">AM — deduct 4 hours</option>
                <option value="pm">PM — deduct 6 hours</option>
                <option value="whole_day">Whole Day — deduct 10 hours (1 day)</option>
            </select>
        </div>
    </div>
</div>
