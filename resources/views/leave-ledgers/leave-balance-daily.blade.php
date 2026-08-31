<div class="daily-balance-card">
    <div class="daily-balance-hero">
        <div>
            <p class="text-uppercase text-muted fw-semibold mb-1" style="letter-spacing: 0.08em;">Daily Accrual</p>
            <h3>Leave credit earned per hour</h3>
            <p>
                This reference uses the same monthly rate of <strong>1.25 days per 160 hours</strong>.
                That means every hour worked earns <strong>0.0078125 day</strong>, and a 10-hour workday earns
                <strong>0.0781250 day</strong>.
            </p>
        </div>

        <div class="daily-balance-calculator">
            <label for="hoursWorked" class="form-label fw-semibold">Hours Worked</label>
            <input
                type="number"
                id="hoursWorked"
                class="form-control"
                min="0"
                step="0.25"
                value="10"
            >
            <div class="daily-balance-output">
                <div class="small text-uppercase opacity-75">Equivalent leave credit</div>
                <strong id="hoursWorkedResult">0.0781250</strong>
                <div class="small">day</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0 daily-balance-table">
            <thead>
                <tr>
                    <th>Hours Worked</th>
                    <th class="text-end">Earned Leave Credit (day)</th>
                </tr>
            </thead>
            <tbody>
        @foreach($dailyRows ?? [] as $row)
                    <tr>
                        <td>
                            @if($row['hours'] === 10)
                                <strong>{{ $row['hours'] }} hours (1 workday)</strong>
                            @elseif($row['hours'] === 160)
                                <strong>{{ $row['hours'] }} hours (1 month)</strong>
                            @else
                                {{ $row['hours'] }} hour{{ $row['hours'] > 1 ? 's' : '' }}
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($row['days'], 7) }}</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="balance-card-note mt-3 mb-0">
        *Daily leave credits are calculated from the same conversion rate used by the monthly balance: 160 hours = 1.25 days.
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hoursInput = document.getElementById('hoursWorked');
    const result = document.getElementById('hoursWorkedResult');

    if (!hoursInput || !result) {
        return;
    }

    const rate = 0.0078125;

    const updateResult = () => {
        const hours = Number(hoursInput.value || 0);
        const value = hours * rate;
        result.textContent = value.toFixed(7);
    };

    hoursInput.addEventListener('input', updateResult);
    updateResult();
});
</script>
