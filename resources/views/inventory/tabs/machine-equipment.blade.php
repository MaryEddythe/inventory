@extends('layout.app')

@section('content')
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Machine & Equipment</h1>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle"></i> Coming Soon
            </button>
        </div>
    </div>

    <div class="text-center py-5">
        <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #ccc;"></i>
        <p class="text-muted mt-3">This tab is coming soon. Please check back later.</p>
    </div>
</div>
@endsection
