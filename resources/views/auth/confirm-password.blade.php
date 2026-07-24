@extends('layout.app')
@section('title', 'Confirm Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="fw-bold mb-2">Confirm Password</h4>
                <p class="text-muted mb-4">Please re-enter your password to continue with this action.</p>

                <form method="POST" action="{{ route('password.confirm.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autofocus>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Confirm Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
