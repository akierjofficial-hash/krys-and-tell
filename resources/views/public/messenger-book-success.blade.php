@extends('layouts.public')

@section('title', 'Request Sent')

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 text-center">
            <h4 class="mb-2">✅ Request sent!</h4>
            <p class="text-muted mb-0">
                Thanks! We received your appointment request. We’ll confirm your schedule via Messenger or phone.
            </p>
        </div>
    </div>
</div>
@endsection
