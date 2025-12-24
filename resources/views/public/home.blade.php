@extends('layouts.public')
@section('title', 'Krys & Tell — Dental Clinic')

@section('content')
<section class="hero">
    <div class="hero-bg" style="background-image:url('{{ asset('assets/img/public/pic1.jpg') }}');"></div>

    <div class="container hero-content">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="hero-card">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="pill"><i class="fa-solid fa-shield-heart"></i> Gentle Care</span>
                        <span class="pill"><i class="fa-solid fa-sparkles"></i> Modern Clinic</span>
                        <span class="pill"><i class="fa-solid fa-star"></i> Trusted Team</span>
                    </div>

                    <h1 class="hero-title">A brighter smile starts with a calmer clinic experience.</h1>
                    <p class="hero-sub">
                        Welcome to <b>Krys &amp; Tell</b> — where dentistry feels premium, friendly, and straightforward.
                        Browse services and book your appointment in minutes.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                            <i class="fa-solid fa-calendar-check me-1"></i> Book an Appointment
                        </a>
                        <a class="btn kt-btn kt-btn-outline text-white" style="border-color:rgba(255,255,255,.35);" href="{{ url('/about') }}">
                            Learn More
                        </a>
                    </div>

                    <div class="row g-2 mt-4">
                        <div class="col-6 col-md-4">
                            <div class="pill w-100 justify-content-center"><i class="fa-solid fa-tooth"></i> General</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="pill w-100 justify-content-center"><i class="fa-solid fa-teeth"></i> Ortho</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="pill w-100 justify-content-center"><i class="fa-solid fa-face-smile"></i> Aesthetic</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="img-tile" style="height:210px;">
                            <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Clinic reception">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="img-tile" style="height:190px;">
                            <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="img-tile" style="height:190px;">
                            <img src="{{ asset('assets/img/public/pic5.jpg') }}" alt="Clinic interior">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-6">
                <h2 class="sec-title">Care that’s clear, gentle, and well-explained.</h2>
                <div class="sec-sub">
                    Our goal is simple: you leave feeling confident — about your smile and your plan.
                </div>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                    View all services <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <div class="card card-soft p-4 h-100">
                    <div class="icon-badge mb-3"><i class="fa-solid fa-user-doctor"></i></div>
                    <div class="fw-black" style="font-weight:950;">Expert Team</div>
                    <div class="text-muted-2 mt-2">
                        Friendly doctors and staff who explain everything clearly and honestly.
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft p-4 h-100">
                    <div class="icon-badge mb-3" style="background:linear-gradient(135deg,var(--brand2),#a78bfa);">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div class="fw-black" style="font-weight:950;">Modern Dentistry</div>
                    <div class="text-muted-2 mt-2">
                        Clean setup, calm environment, and a clinic feel that’s comfortable.
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-soft p-4 h-100">
                    <div class="icon-badge mb-3" style="background:linear-gradient(135deg,#22c55e,#10b981);">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="fw-black" style="font-weight:950;">Easy Booking</div>
                    <div class="text-muted-2 mt-2">
                        Choose a service, pick a time, and you’re set. (We’ll wire this next.)
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <div class="img-tile" style="height:420px;">
                    <img src="{{ asset('assets/img/public/pic9.jpg') }}" alt="Team photo">
                </div>
            </div>
            <div class="col-lg-7">
                <h2 class="sec-title">Meet Krys &amp; Tell</h2>
                <div class="sec-sub">
                    A clinic built around comfort, clarity, and results — without the intimidating vibe.
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="card card-soft p-4 h-100">
                            <div class="fw-black" style="font-weight:950;">Our Mission</div>
                            <div class="text-muted-2 mt-2">To deliver gentle dental care with honest recommendations.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-soft p-4 h-100">
                            <div class="fw-black" style="font-weight:950;">Our Promise</div>
                            <div class="text-muted-2 mt-2">No pressure — just clear options and quality treatment.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/about') }}">Read our story</a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/contact') }}">Contact us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-7">
                <h2 class="sec-title">Smile Gallery</h2>
                <div class="sec-sub">A small peek at results and treatments (we can curate these later).</div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-3 col-6">
                <div class="img-tile" style="height:170px;">
                    <img src="{{ asset('assets/img/public/treatments1.jpg') }}" alt="Treatment photo">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="img-tile" style="height:170px;">
                    <img src="{{ asset('assets/img/public/treatments2.jpg') }}" alt="Treatment photo">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="img-tile" style="height:170px;">
                    <img src="{{ asset('assets/img/public/treatments3.jpg') }}" alt="Treatment photo">
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="img-tile" style="height:170px;">
                    <img src="{{ asset('assets/img/public/treatments4.jpg') }}" alt="Treatment photo">
                </div>
            </div>
        </div>

        <div class="card card-soft p-4 mt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-black" style="font-weight:950;font-size:1.05rem;">Ready to book?</div>
                    <div class="text-muted-2">Choose a service and reserve a schedule that fits you.</div>
                </div>
                <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                    Start booking <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
