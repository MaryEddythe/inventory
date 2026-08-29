@extends('layout.app')

@section('content')
@php
    $currentUser = auth()->user();
    $currentEmployee = $currentUser?->employee;
    $displayBio = trim((string) ($currentUser->bio ?? $currentEmployee?->position ?? $currentUser?->role?->name ?? ''));
    $displayBio = $displayBio !== '' ? $displayBio : 'Bio not set yet.';
    $displayDivision = trim((string) ($currentEmployee?->division?->name ?? $currentEmployee?->division?->code ?? ''));
    $displayDivision = $displayDivision !== '' ? $displayDivision : 'Division not set';
    $displayRole = $currentUser->role?->name ?? 'Employee';
    $profileImageUrl = $currentUser->profile_image
        ? asset('storage/' . $currentUser->profile_image)
        : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser->username ?: $currentUser->name) . '&background=0D8ABC&color=fff';
    $attendanceChecks = [
        ['label' => 'Logged in morning', 'state' => 'Pending'],
        ['label' => 'Logged out morning', 'state' => 'Pending'],
        ['label' => 'Logged in afternoon', 'state' => 'Pending'],
        ['label' => 'Logged out afternoon', 'state' => 'Pending'],
    ];
@endphp

<style>
    .company-dashboard {
        min-height: calc(100vh - 96px);
    }
    .company-panel {
        position: sticky;
        top: 1rem;
        background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        color: #fff;
        border-radius: 1.5rem;
        padding: 1.5rem;
        min-height: calc(100vh - 128px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
    }
    .company-panel .badge-soft {
        background: rgba(255, 255, 255, 0.12);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .attendance-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
    }
    .attendance-check {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .55rem .65rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: .65rem;
        background: rgba(255, 255, 255, 0.07);
    }
    .attendance-check-label,
    .attendance-check-state {
        font-size: .65rem;
        line-height: 1.2;
    }
    .attendance-check-state {
        opacity: .7;
        white-space: nowrap;
    }
    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(7, 1cm);
        gap: .5rem;
        align-items: center;
    }
    .attendance-tile {
        width: 1cm;
        height: 1cm;
        border-radius: .3rem;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: grid;
        place-items: center;
        padding: 0;
    }
    .attendance-tile .tile-icon {
        width: .45cm;
        height: .45cm;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: rgba(56, 189, 248, 0.15);
        color: #7dd3fc;
    }
    .attendance-tile .tile-icon i {
        font-size: .55rem;
    }
    .attendance-tile .tile-label,
    .attendance-tile .tile-state {
        display: none;
    }
    .announcement-card {
        border: 0;
        border-radius: 1.25rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }
    .announcement-card .card-header {
        background: #fff;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        border-top-left-radius: 1.25rem;
        border-top-right-radius: 1.25rem;
    }
    .announcement-meta {
        font-size: .875rem;
        color: #64748b;
    }
</style>

<div class="company-dashboard container-fluid py-3">
    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="company-panel">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $profileImageUrl }}" alt="{{ $currentUser->name }}" class="rounded-circle" style="width:72px;height:72px;object-fit:cover;">
                    <div>
                        <div class="small text-uppercase text-info-emphasis opacity-75">Logged in user</div>
                        <h2 class="h4 fw-bold mb-1">{{ $currentUser->name }}</h2>
                        <div class="small opacity-75 mb-2">{{ $displayDivision }}</div>
                        <div class="badge badge-soft rounded-pill px-3 py-2">Role: {{ $displayRole }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="small text-uppercase opacity-75 mb-2">Bio</div>
                    <div class="lead mb-0" style="font-size: 1rem; line-height: 1.7;">
                        {{ $displayBio }}
                    </div>
                </div>

                <div class="mb-4">
                    <div class="small text-uppercase opacity-75 mb-2">Attendance status</div>
                    <div class="attendance-summary">
                        @foreach($attendanceChecks as $check)
                            <div class="attendance-check">
                                <span class="attendance-check-label">{{ $check['label'] }}</span>
                                <span class="attendance-check-state">{{ $check['state'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="small text-uppercase opacity-75">Attendance</div>
                            <div class="fw-semibold">Weekly biometric placeholders</div>
                        </div>
                        <span class="badge badge-soft rounded-pill">{{ $attendanceSlots->count() }} slots</span>
                    </div>

                    <div class="attendance-grid">
                        @foreach($attendanceSlots as $slot)
                            <div class="attendance-tile">
                                <div class="tile-icon">
                                    <i class="bi bi-fingerprint"></i>
                                </div>
                                <div class="tile-label fw-semibold">{{ $slot['label'] }}</div>
                                <div class="tile-state">{{ $slot['state'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="alert alert-info bg-info-subtle border-0 mb-0">
                    Attendance integration can later connect to the biometric module without changing this layout.
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8" id="announcementsFeed">
            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Please fix the following:</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($canManageAnnouncements)
                <div class="card announcement-card mb-4">
                    <div class="card-header py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h3 class="h5 fw-bold mb-1">Post a new announcement</h3>
                                <div class="announcement-meta">Visible to all employees once published.</div>
                            </div>
                            <span class="badge text-bg-primary">Manager roles only</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('company-announcements.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Title</label>
                                    <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title') }}" placeholder="Type the announcement title">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message</label>
                                    <textarea name="body" rows="5" class="form-control" placeholder="Share the company update here">{{ old('body') }}</textarea>
                                </div>
                                <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_published" value="0">
                                        <input class="form-check-input" type="checkbox" id="isPublished" name="is_published" value="1" checked>
                                        <label class="form-check-label fw-semibold" for="isPublished">Publish immediately</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-send me-2"></i>Publish announcement
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card announcement-card">
                <div class="card-header py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h3 class="h5 fw-bold mb-1">Home</h3>
                            <div class="announcement-meta">{{ $announcements->count() }} item(s) currently posted</div>
                        </div>
                        <span class="badge text-bg-secondary">All employees</span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @forelse($announcements as $announcement)
                        <div class="p-4 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                                <div>
                                    <h4 class="h5 fw-bold mb-1">{{ $announcement->title }}</h4>
                                    <div class="announcement-meta">
                                        Posted {{ $announcement->created_at?->diffForHumans() ?? 'recently' }}
                                        @if($announcement->creator)
                                            by {{ $announcement->creator->name }}
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $announcement->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                    @if($canManageAnnouncements)
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editAnnouncement{{ $announcement->id }}" aria-expanded="false" aria-controls="editAnnouncement{{ $announcement->id }}">
                                            Edit
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <p class="mb-0" style="white-space: pre-wrap;">{{ $announcement->body }}</p>

                            @if($canManageAnnouncements)
                                <div class="collapse mt-4" id="editAnnouncement{{ $announcement->id }}">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('company-announcements.update', $announcement) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Title</label>
                                                        <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Message</label>
                                                        <textarea name="body" rows="5" class="form-control">{{ old('body', $announcement->body) }}</textarea>
                                                    </div>
                                                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" name="is_published" value="0">
                                                            <input class="form-check-input" type="checkbox" id="isPublished{{ $announcement->id }}" name="is_published" value="1" @checked($announcement->is_published)>
                                                            <label class="form-check-label fw-semibold" for="isPublished{{ $announcement->id }}">Published</label>
                                                        </div>
                                                        <button type="submit" class="btn btn-outline-primary">
                                                            Save changes
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-megaphone display-5 d-block mb-3"></i>
                            <div class="fw-semibold">No announcements yet</div>
                            <div class="small">Company updates will appear here for everyone.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
