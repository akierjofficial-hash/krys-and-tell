@extends('layouts.public')
@section('title', $service->name . ' — Krys & Tell')

@section('content')
<section class="section">
    <div class="container">
        <a href="{{ route('public.services.index') }}" class="text-decoration-none fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Services
        </a>

        <div class="row g-4 mt-2 align-items-start">
            <div class="col-lg-7">
                <h1 class="sec-title mb-2">{{ $service->name }}</h1>

                @if(!empty($service->description))
                    <div class="sec-sub">{{ $service->description }}</div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if(!empty($service->base_price))
                        <span class="pill" style="border-color:rgba(15,23,42,.12); background:#fff; color:var(--text);">
                            Starting at ₱{{ number_format((float)$service->base_price, 0) }}
                        </span>
                    @endif
                    <span class="pill" style="border-color:rgba(15,23,42,.12); background:#fff; color:var(--text);">
                        <i class="fa-solid fa-circle-check"></i> Fast booking
                    </span>
                </div>

                <div class="card card-soft p-4 mt-4">
                    <div class="fw-black" style="font-weight:950;">Ready to book this service?</div>
                    <div class="text-muted-2 mt-1">Pick your preferred date & time — we’ll confirm it as Pending.</div>

                    <a class="btn kt-btn kt-btn-primary text-white mt-3" href="{{ route('public.booking.create', $service->id) }}">
                        <i class="fa-solid fa-calendar-check me-1"></i> Book Now
                    </a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="img-tile" style="height:360px;">
                    <img src="{{ asset('assets/img/public/pic6.jpg') }}" alt="Clinic">
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
