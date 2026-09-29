@extends('layouts.admin')
@section('title', 'Edit Website Account')

@section('content')
<x-admin.page-header title="Edit website account" description="Update the sign-in details and access state for this patient-facing account." parent="Website Accounts" :parent-url="route('admin.user_accounts.index')">
    <x-slot:actions><a href="{{ route('admin.user_accounts.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i>Back to accounts</a></x-slot:actions>
</x-admin.page-header>
<div class="cardx p-3 p-md-4" style="max-width:760px;">

    <div class="alert alert-info">
        <strong>Verified patient access (read-only)</strong>
        @forelse($user->verifiedPatients as $linkedPatient)
            <div><a href="{{ route('admin.patients.show', $linkedPatient) }}">Patient #{{ $linkedPatient->id }} · {{ $linkedPatient->first_name }} {{ $linkedPatient->last_name }}</a> ({{ ucfirst($linkedPatient->pivot->relationship) }})</div>
        @empty
            <div>No clinic patient record is verified for this website account. Staff controls patient linking.</div>
        @endforelse
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

        @if($user->google_id)
        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="establish_local_password" name="establish_local_password" value="1">
            <label class="form-check-label" for="establish_local_password" style="font-weight:900;">Deliberately establish a local password for this Google-connected account</label>
        </div>
        @endif

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
