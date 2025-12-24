@extends('layouts.public')
@section('title', ($service->name ?? 'Service') . ' — Krys & Tell')

@section('content')
<section class="section">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                     style="background:rgba(176,124,88,.10); border:1px solid rgba(17,17,17,.10); font-weight:850;">
                    <i class="fa-solid fa-tooth" style="color:var(--brand)"></i>
                    <span>Service details</span>
                </div>

                <h1 class="sec-title mt-3">{{ $service->name }}</h1>

                <div class="sec-sub">
                    {{ $service->description ?? 'Professional dental care tailored for your comfort.' }}
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="icon-badge"><i class="fa-solid fa-clock"></i></span>
                                <div style="font-weight:950;">Estimated duration</div>
                            </div>
                            <div class="text-muted-2 mt-2" style="font-weight:650;">
                                {{ $service->duration_minutes ? $service->duration_minutes . ' minutes' : 'Depends on the case' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="icon-badge" style="background:linear-gradient(135deg, rgba(216,193,176,.95), rgba(176,124,88,.95));">
                                    <i class="fa-solid fa-circle-info"></i>
                                </span>
                                <div style="font-weight:950;">What to expect</div>
                            </div>
                            <div class="text-muted-2 mt-2" style="font-weight:650;">
                                Clear explanation, gentle treatment, and after-care guidance.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/book/' . $service->id) }}">
                        <i class="fa-solid fa-calendar-check me-1"></i> Book this service
                    </a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to services
                    </a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card-soft p-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Logo"
                             style="width:52px;height:52px;border-radius:18px;object-fit:cover;border:1px solid rgba(17,17,17,.10);background:#fff;">
                        <div>
                            <div style="font-weight:950;">Krys &amp; Tell Dental Center</div>
                            <div class="text-muted-2" style="font-weight:650;">Comfort-first, clear plans, modern results.</div>
                        </div>
                    </div>

                    <hr style="border-color: rgba(17,17,17,.10);">

                    <div class="text-muted-2" style="font-weight:650; line-height:1.7;">
                        Prefer to ask first? Visit our contact page and we’ll guide you on the best service to book.
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ url('/contact') }}" class="btn kt-btn kt-btn-outline w-100">
                            Contact
                        </a>
                        <a href="{{ url('/services') }}" class="btn kt-btn kt-btn-primary text-white w-100">
                            Browse
                        </a>
                    </div>
                </div>

                <div class="img-tile mt-3" style="height:260px;">
                    <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
