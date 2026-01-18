@extends('layouts.staff')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h2 class="m-0" style="font-weight:900;">Message</h2>
        <div class="text-muted">
            Received {{ optional($message->created_at)->format('M d, Y h:i A') }}
            @if($message->read_at) • Read {{ optional($message->read_at)->diffForHumans() }} @endif
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('staff.messages.index') }}" class="btn btn-light">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>

        @if(!$message->read_at)
        <form method="POST" action="{{ route('staff.messages.read', $message) }}">
            @csrf
            <button class="btn btn-outline-primary" type="submit">
                Mark as read
            </button>
        </form>
        @endif

        <form method="POST" action="{{ route('staff.messages.destroy', $message) }}"
              onsubmit="return confirm('Delete this message?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" type="submit">
                Delete
            </button>
        </form>
    </div>
</div>

<div class="card p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="text-muted">Name</div>
            <div style="font-weight:900;">{{ $message->name }}</div>
        </div>
        <div class="col-md-6">
            <div class="text-muted">Email</div>
            <div style="font-weight:900;">{{ $message->email }}</div>
        </div>

        <div class="col-12">
            <div class="text-muted">Message</div>
            <div class="mt-2" style="white-space:pre-wrap; font-weight:650;">
                {{ $message->message }}
            </div>
        </div>

        <div class="col-12">
            <hr>
            <div class="text-muted small">
                IP: {{ $message->ip_address ?? '—' }}<br>
                UA: {{ $message->user_agent ?? '—' }}
            </div>
        </div>
    </div>
</div>
@endsection
