@extends('layouts.public')
@section('title', 'About — Krys & Tell')

@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                     style="background:rgba(176,124,88,.10); border:1px solid rgba(17,17,17,.10); font-weight:850;">
                    <i class="fa-solid fa-tooth" style="color:var(--brand)"></i>
                    <span>About the clinic</span>
                </div>

                <h1 class="sec-title mt-3">About Krys &amp; Tell</h1>
                <div class="sec-sub">
                    We combine modern dental care with a calm clinic experience — so you feel safe, informed,
                    and confident every step of the way.
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico"><i class="fa-solid fa-hand-holding-heart"></i></span>
                                <div style="font-weight:950;">Comfort first</div>
                            </div>
                            <div class="text-muted-2 mt-2" style="font-weight:650; line-height:1.7;">
                                A clean, relaxing space — designed to feel calm from reception to treatment.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-soft p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico" style="background:rgba(216,193,176,.28); color:#6f6d6b;">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </span>
                                <div style="font-weight:950;">Clear plans</div>
                            </div>
                            <div class="text-muted-2 mt-2" style="font-weight:650; line-height:1.7;">
                                We explain options, costs, and next steps before anything starts.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                        View services <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/contact') }}">
                        Get in touch
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="img-tile" style="height:460px;">
                    <img src="{{ asset('assets/img/public/pic6.jpg') }}" alt="Doctor photo">
                </div>

                <div class="about-fact mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/krysandtelllogo.jpg') }}" class="about-logo" alt="Logo">
                        <div>
                            <div style="font-weight:950;">Dental Center, gentle approach</div>
                            <div class="text-muted-2" style="font-weight:650;">
                                Designed to feel welcoming — not intimidating.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gallery strip --}}
        <div class="row g-3 mt-5">
            <div class="col-md-4">
                <div class="img-tile" style="height:220px;">
                    <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile" style="height:220px;">
                    <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile" style="height:220px;">
                    <img src="{{ asset('assets/img/public/pic5.jpg') }}" alt="Clinic interior">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .about-ico{
        width:44px; height:44px;
        border-radius: 16px;
        display:grid; place-items:center;
        background: rgba(176,124,88,.20);
        color: #fff;
        border: 1px solid rgba(17,17,17,.10);
        box-shadow: 0 14px 35px rgba(11,18,32,.06);
        flex: 0 0 auto;
    }
    .about-fact{
        border-radius: 20px;
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(17,17,17,.10);
        box-shadow: 0 18px 50px rgba(11,18,32,.08);
        padding: 14px 16px;
    }
    .about-logo{
        width:44px; height:44px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid rgba(17,17,17,.10);
        background:#fff;
    }
</style>
@endsection
