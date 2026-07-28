@extends('layout.app')
@section('title', 'Edit Leave Ledger')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Edit Leave Ledger</h1>
            <div class="text-muted">{{ $employee->lastname }}, {{ $employee->firstname }}</div>
        </div>
        <a href="{{ route('leave-ledgers.show', $employee) }}" class="btn btn-outline-secondary btn-sm">Back to Ledger</a>
    </div>

    <form method="POST" action="{{ route('leave-ledgers.update', $employee) }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">1st Day of Service</label>
            <input type="date" name="first_day_of_service" value="{{ old('first_day_of_service', optional($setting->first_day_of_service)->format('Y-m-d')) }}" class="form-control">
            @error('first_day_of_service') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Opening Balance Date</label>
            <input type="date" name="opening_balance_date" value="{{ old('opening_balance_date', optional($setting->opening_balance_date)->format('Y-m-d')) }}" class="form-control">
            @error('opening_balance_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Opening Vacation Leave Balance</label>
            <input type="number" step="0.001" min="0" name="opening_vacation_balance" value="{{ old('opening_vacation_balance', $setting->opening_vacation_balance ?? 0) }}" class="form-control" required>
            @error('opening_vacation_balance') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Opening Sick Leave Balance</label>
            <input type="number" step="0.001" min="0" name="opening_sick_balance" value="{{ old('opening_sick_balance', $setting->opening_sick_balance ?? 0) }}" class="form-control" required>
            @error('opening_sick_balance') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $setting->remarks) }}</textarea>
            @error('remarks') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('leave-ledgers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Ledger Settings</button>
        </div>
    </form>
</div>
@endsection
