@extends(request()->header('X-KT-Live-Search') === '1' ? 'layouts.live-search' : 'layouts.staff')

@section('title', 'Patient Portal Access')

@section('content')
<div class="staff-page-header">
    <div class="staff-page-header__copy">
        <div class="staff-page-header__eyebrow">Verified access</div>
        <h1>Patient Portal Access</h1>
        <p>Only Staff can connect or disconnect website accounts from clinical records.</p>
    </div>
    <div class="staff-page-header__actions">
        <a class="btn btn-outline-secondary" href="{{ route('staff.patients.show', $patient) }}"><i class="fa fa-arrow-left"></i> Back to patient</a>
    </div>
</div>

<section class="staff-section-card mb-3">
    <div class="staff-section-card__header"><div><h2>{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name }}</h2><p>Patient #{{ $patient->id }}</p></div></div>
    <div class="row g-3">
        <div class="col-md-3"><strong>Birthdate</strong><div>{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('M d, Y') : 'Not recorded' }}</div></div>
        <div class="col-md-3"><strong>Email</strong><div>{{ $patient->email ?: 'Not recorded' }}</div></div>
        <div class="col-md-3"><strong>Contact</strong><div>{{ $patient->contact_number ?: 'Not recorded' }}</div></div>
        <div class="col-md-3"><strong>Address</strong><div>{{ $patient->address ?: 'Not recorded' }}</div></div>
    </div>
</section>

<section class="staff-section-card mb-3">
    <div class="staff-section-card__header"><div><h2>Current verified accounts</h2><p>A guardian may manage more than one patient. Shared email or phone values do not create access.</p></div></div>
    @forelse($links as $link)
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 py-3 border-bottom">
            <div><strong>{{ $link->user?->name }}</strong><div class="text-muted">{{ $link->user?->email }} · {{ ucfirst($link->relationship) }}</div><small>Verified {{ $link->verified_at?->format('M d, Y g:i A') }} by {{ $link->verifiedBy?->name ?: 'Staff' }}</small></div>
            <form method="POST" action="{{ route('staff.patients.account-links.destroy', [$patient, $link]) }}" style="min-width:min(100%,360px)">
                @csrf @method('DELETE')
                <input class="form-control mb-2" name="unlink_reason" maxlength="1000" placeholder="Reason for removing access" required>
                <label class="form-check-label d-block mb-2"><input class="form-check-input me-1" type="checkbox" name="confirm_unlink" value="1" required> I confirm this account must lose access.</label>
                <button class="btn btn-outline-danger" type="submit">Unlink account</button>
            </form>
        </div>
    @empty
        <div class="staff-empty-state"><div class="staff-empty-state__icon"><i class="fa fa-link-slash"></i></div><strong>No verified account links</strong><span>This patient’s clinical and financial history is not available to any website account.</span></div>
    @endforelse
</section>

<section class="staff-section-card">
    <div class="staff-section-card__header"><div><h2>Find a website account</h2><p>Search by account name or sign-in email, compare the details above, then explicitly confirm the match.</p></div></div>
    <form class="d-flex gap-2 mb-3" method="GET" data-live-search>
        <input class="form-control" type="search" name="q" value="{{ $q }}" placeholder="Search account name or email" required>
        <button class="btn btn-primary" type="submit">Search</button>
    </form>

    <div data-live-results>
    @foreach($accounts as $account)
        <form class="border rounded p-3 mb-2" method="POST" action="{{ route('staff.patients.account-links.store', $patient) }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $account->id }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4"><strong>{{ $account->name }}</strong><div>{{ $account->email }}</div><small class="text-muted">Account #{{ $account->id }} · {{ $account->appointments_count }} booking(s) · {{ $account->verified_patients_count }} linked patient(s)</small></div>
                <div class="col-lg-2"><label class="form-label">Relationship</label><select class="form-select" name="relationship" required><option value="self">Self</option><option value="parent">Parent</option><option value="guardian">Guardian</option><option value="caregiver">Caregiver</option><option value="other">Other</option></select></div>
                <div class="col-lg-3"><label class="form-label">Verification note</label><input class="form-control" name="verification_note" maxlength="1000" placeholder="How identity was verified" required></div>
                <div class="col-lg-3"><label class="form-check-label d-block mb-2"><input class="form-check-input me-1" type="checkbox" name="confirm_identity" value="1" required> I verified the correct patient and account.</label><button class="btn btn-primary w-100" type="submit">Link verified account</button></div>
            </div>
        </form>
    @endforeach

    @if($q !== '' && $accounts->isEmpty())<div class="text-muted">No active website account matched that search.</div>@endif
    @if($q !== '' && $accounts->hasPages())<div class="mt-3">{{ $accounts->links('pagination::bootstrap-5') }}</div>@endif
    </div>
</section>
@endsection
