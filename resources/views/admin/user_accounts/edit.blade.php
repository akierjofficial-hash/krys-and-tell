@extends('layouts.admin')

@section('content')
<div class="cardx p-3 p-md-4" style="max-width:760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="m-0" style="font-weight:950;">Edit User</h4>
            <div style="color:var(--muted);font-weight:700;font-size:13px;">role=user account</div>
        </div>
        <a href="{{ route('admin.user_accounts.index') }}" class="btn btn-outline-secondary" style="border-radius:14px;font-weight:900;">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius:14px;font-weight:800;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.user_accounts.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label" style="font-weight:900;">Name</label>
            <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:900;">Email</label>
            <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="row g-2">
            <div class="col-12 col-md-6">
                <label class="form-label" style="font-weight:900;">New Password (optional)</label>
                <input class="form-control" type="password" name="password">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" style="font-weight:900;">Confirm New Password</label>
                <input class="form-control" type="password" name="password_confirmation">
            </div>
        </div>

        @if($hasActive)
        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active" style="font-weight:900;">
                Active
            </label>
        </div>
        @endif

        <button class="btn btn-primary mt-3" style="border-radius:14px;font-weight:950;">
            <i class="fa fa-save me-1"></i> Save changes
        </button>
    </form>
</div>
@endsection
