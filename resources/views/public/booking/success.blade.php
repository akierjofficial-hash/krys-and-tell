@extends('layouts.public')
@section('title', 'Booking Successful — Krys & Tell')

@section('content')
<section class="section">
    <div class="container">
        <div class="card card-soft p-4">
            <h1 class="sec-title mb-2">Booking submitted ✅</h1>
            <div class="sec-sub">Your appointment is saved as <b>Pending</b>. Staff will review/confirm it.</div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="fw-bold">Service</div>
                    <div class="text-muted-2">{{ $appointment->service->name ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold">Date</div>
                    <div class="text-muted-2">{{ $appointment->appointment_date ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="fw-bold">Time</div>
                    <div class="text-muted-2">{{ $appointment->appointment_time ?? '—' }}</div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn kt-btn kt-btn-primary text-white" href="{{ route('public.services.index') }}">
                    Book another
                </a>
                <a class="btn kt-btn kt-btn-outline" href="{{ url('/') }}">
                    Back home
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
