@extends('layouts.public')
@section('title', 'About — Krys & Tell')

@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h1 class="sec-title">About Krys &amp; Tell</h1>
                <div class="sec-sub">
                    We combine modern dental care with a calm clinic experience — so you feel safe, informed, and confident.
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="card card-soft p-4 h-100">
                            <div class="fw-black" style="font-weight:950;">Comfort first</div>
                            <div class="text-muted-2 mt-2">A clinic atmosphere designed to feel clean and relaxing.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-soft p-4 h-100">
                            <div class="fw-black" style="font-weight:950;">Clear plans</div>
                            <div class="text-muted-2 mt-2">We explain options, costs, and next steps before anything starts.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">View services</a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/contact') }}">Get in touch</a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="img-tile" style="height:460px;">
                    <img src="{{ asset('assets/img/public/pic6.jpg') }}" alt="Doctor photo">
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-4">
                <div class="img-tile" style="height:210px;">
                    <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile" style="height:210px;">
                    <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile" style="height:210px;">
                    <img src="{{ asset('assets/img/public/pic5.jpg') }}" alt="Clinic interior">
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
