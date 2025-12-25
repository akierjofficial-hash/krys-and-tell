@extends('layouts.app') {{-- change to your staff layout if different --}}
@section('title', 'Approval Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0">Approval Requests</h3>
            <small class="text-muted">Public bookings waiting for approval</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        @forelse($requests as $r)
            <div class="col-lg-6 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-bold">
                            {{ $r->public_name ?? trim(($r->public_first_name ?? '').' '.($r->public_middle_name ? $r->public_middle_name.' ' : '').($r->public_last_name ?? '')) }}
                        </div>

                        <div class="small text-muted mt-1">
                            <div><i class="fa-regular fa-calendar me-1"></i> {{ $r->appointment_date }} • {{ $r->appointment_time }}</div>
                            <div><i class="fa-solid fa-tooth me-1"></i> {{ optional($r->service)->name ?? '—' }}</div>
                            <div><i class="fa-solid fa-user-doctor me-1"></i> {{ optional($r->doctor)->name ?? $r->dentist_name ?? '—' }}</div>
                        </div>

                        <hr>

                        <div class="small">
                            <div><strong>Email:</strong> {{ $r->public_email ?? '—' }}</div>
                            <div><strong>Contact:</strong> {{ $r->public_phone ?? '—' }}</div>
                            <div><strong>Address:</strong> {{ $r->public_address ?? '—' }}</div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <form method="POST" action="{{ route('staff.approvals.approve', $r) }}">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('staff.approvals.decline', $r) }}">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                    <i class="fa-solid fa-xmark me-1"></i> Decline
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        No pending requests 🎉
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>
</div>
@endsection
