@extends('layouts.app')
@section('title', 'Divisions')

@section('content')
<style>
    .divisions-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.75rem;
    }
    .form-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 2rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease;
    }
    .form-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    .form-card .card-title {
        margin-bottom: 1.75rem;
    }
    .optional-label {
        color: #64748b;
        font-weight: 600;
    }
    .table-wrapper {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .division-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }
    .empty-divisions {
        text-align: center;
        padding: 3rem 2rem;
        color: #64748b;
    }
    .empty-divisions::before {
        content: '';
        display: block;
        width: 50px;
        height: 50px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #e2e8f0 0%, #f1f5f9 100%);
        border-radius: 6px;
    }
    @media (max-width: 1024px) {
        .divisions-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <div class="page-title">Divisions</div>
        <div class="page-subtitle">Manage divisions shown in employee forms</div>
    </div>
</div>

<div class="divisions-container">

    {{-- Add Division Form --}}
    <div class="form-card">
        <div class="card-title">Add New Division</div>
        <form method="POST" action="{{ route('divisions.store') }}">
            @csrf
            <div class="form-group">
                <label>Division Name <span class="required-indicator">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Information Technology" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Code <span class="optional-label">(optional)</span></label>
                <input type="text" name="code" value="{{ old('code') }}"
                       placeholder="e.g. IT">
            </div>
            <div class="form-group">
                <label>Description <span class="optional-label">(optional)</span></label>
                <input type="text" name="description" value="{{ old('description') }}"
                       placeholder="Short description">
            </div>
            <button type="submit" class="btn btn-primary">Add Division</button>
        </form>
    </div>

    {{-- Division List --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Employees</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($divisions as $div)
                <tr>
                    <td><span class="division-name">{{ $div->name }}</span></td>
                    <td>{{ $div->code ?? '—' }}</td>
                    <td>{{ $div->employees_count ?? $div->employees()->count() }}</td>
                    <td>
                        @if($div->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-yellow">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('divisions.destroy', $div) }}"
                              onsubmit="return confirm('Delete {{ $div->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-divisions">
                            No divisions yet.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
    .required-indicator {
        color: #dc2626;
    }
</style>
@endsection
