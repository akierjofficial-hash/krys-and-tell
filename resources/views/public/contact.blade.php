@extends('layouts.public')
@section('title', 'Contact — Krys & Tell')

@section('content')
<section class="section">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-6">
                <h1 class="sec-title">Contact</h1>
                <div class="sec-sub">Message us or visit the clinic. We’ll respond as soon as possible.</div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-lg-5">
                <div class="card card-soft p-4 h-100">
                    <div class="fw-black" style="font-weight:950;">Clinic Info</div>
                    <div class="text-muted-2 mt-2">
                        <div class="mt-2"><i class="fa-solid fa-location-dot me-2"></i> (Your address here)</div>
                        <div class="mt-2"><i class="fa-solid fa-phone me-2"></i> (Your phone here)</div>
                        <div class="mt-2"><i class="fa-solid fa-envelope me-2"></i> (Your email here)</div>
                        <div class="mt-2"><i class="fa-solid fa-clock me-2"></i> Mon–Sat 9AM–6PM</div>
                    </div>

                    <div class="img-tile mt-4" style="height:240px;">
                        <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-soft p-4">
                    <div class="fw-black" style="font-weight:950;">Send a message</div>

                    <form class="mt-3" method="POST" action="#">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name</label>
                                <input class="form-control" type="text" name="name" placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input class="form-control" type="email" name="email" placeholder="you@email.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Message</label>
                                <textarea class="form-control" name="message" rows="5" placeholder="How can we help?"></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn kt-btn kt-btn-primary text-white" type="submit">
                                    Send message
                                </button>
                                <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                                    Book instead
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="fw-bold">Map (optional)</div>
                        <div class="text-muted-2">We can embed Google Maps once you give the exact clinic address.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
