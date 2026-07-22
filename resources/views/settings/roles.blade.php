@extends('layout.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Roles</h2>
            <p class="text-muted mb-0">Assign each employee a role and the sidebar items they can access.</p>
        </div>
    </div>

    @foreach($users as $user)
        @php($selectedIds = $user->sidebarItems->pluck('id')->all())
        <form class="card mb-4 shadow-sm" method="POST" action="{{ route('roles.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row g-3 align-items-start">
                    <div class="col-lg-4">
                        <h5 class="mb-1">{{ $user->username ?: $user->name }}</h5>
                        <div class="text-muted small mb-3">{{ $user->email }}</div>

                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <option value="">No role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) $user->role_id === (string) $role->id)>
                                    {{ $role->name }}{{ $role->is_superadmin ? ' (Superadmin)' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text mt-2">
                            Direct sidebar items override the role default for this user.
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <label class="form-label">Sidebar Items</label>
                        <div class="border rounded p-3 bg-light">
                            @foreach($sidebarItems as $item)
                                @include('settings.sidebar-item-checkbox', [
                                    'item' => $item,
                                    'selectedIds' => $selectedIds,
                                    'level' => 0,
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Save Role Access</button>
            </div>
        </form>
    @endforeach
</div>
@endsection
