@extends('layouts.public')
@section('title', 'Contact — Krys & Tell')

@section('content')
<section class="section">
    <div class="container">
        {{-- Header --}}
        <div class="row align-items-end g-3">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                     style="background:rgba(29,78,216,.08); border:1px solid rgba(15,23,42,.08); font-weight:800;">
                    <i class="fa-solid fa-envelope-open-text text-primary"></i>
                    <span>Contact</span>
                </div>

                <h1 class="sec-title mt-3">We’re here to help</h1>
                <div class="sec-sub">
                    Message us or visit the clinic. We’ll respond as soon as possible.
                </div>
            </div>

            <div class="col-lg-5 text-lg-end">
                <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                    Book an appointment <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-3 mt-4">
            {{-- Left: Clinic info --}}
            <div class="col-lg-5">
                <div class="card-soft p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-black" style="font-weight:950;font-size:1.1rem;">Clinic Info</div>
                        <span class="contact-badge">
                            <i class="fa-solid fa-shield-heart me-1"></i> Open Mon–Sat
                        </span>
                    </div>

                    <div class="mt-3 contact-list">
                        <div class="contact-item">
                            <span class="contact-ico"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <div class="contact-label">Address</div>
                                <div class="contact-value">(Your address here)</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <span class="contact-ico" style="background:rgba(6,182,212,.14); color:#0891b2;">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <div>
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">(Your phone here)</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <span class="contact-ico" style="background:rgba(16,185,129,.14); color:#059669;">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <div>
                                <div class="contact-label">Email</div>
                                <div class="contact-value">(Your email here)</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <span class="contact-ico" style="background:rgba(168,85,247,.14); color:#7c3aed;">
                                <i class="fa-solid fa-clock"></i>
                            </span>
                            <div>
                                <div class="contact-label">Hours</div>
                                <div class="contact-value">Mon–Sat: 9:00 AM – 6:00 PM</div>
                            </div>
                        </div>
                    </div>

                    <div class="img-tile mt-4" style="height:260px;">
                        <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                    </div>

                    <div class="mt-3 text-muted-2" style="font-weight:650;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        For booking, go to Services and choose a schedule that fits you.
                    </div>
                </div>
            </div>

            {{-- Right: Message form --}}
            <div class="col-lg-7">
                <div class="card-soft p-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="contact-form-ico">
                            <i class="fa-solid fa-paper-plane"></i>
                        </span>
                        <div>
                            <div class="fw-black" style="font-weight:950;font-size:1.1rem;">Send a message</div>
                            <div class="text-muted-2" style="font-weight:650;">We’ll get back to you as soon as we can.</div>
                        </div>
                    </div>

                    <form class="mt-4" method="POST" action="#">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name</label>
                                <input class="form-control kt-input" type="text" name="name" placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input class="form-control kt-input" type="email" name="email" placeholder="you@email.com">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Message</label>
                                <textarea class="form-control kt-input" name="message" rows="5" placeholder="How can we help?"></textarea>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button class="btn kt-btn kt-btn-primary text-white" type="submit">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send message
                                </button>
                                <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Book instead
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- Optional Map --}}
                    <div class="mt-4">
                        <div class="fw-black" style="font-weight:950;">Map</div>
                        <div class="text-muted-2" style="font-weight:650;">
                            Add your exact clinic address and we’ll embed Google Maps here.
                        </div>

                        <div class="map-shell mt-3">
                            <div class="map-placeholder">
                                <i class="fa-solid fa-map-location-dot"></i>
                                <div class="mt-2 fw-bold">Map placeholder</div>
                                <div class="text-muted-2" style="font-weight:650;">
                                    Replace this with an iframe embed when ready.
                                </div>
                            </div>

                            {{-- Example embed (use later)
                            <iframe
                                src="https://www.google.com/maps/embed?pb=..."
                                width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Contact page polish */
    .contact-badge{
        display:inline-flex; align-items:center; gap:.35rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        background: rgba(16,185,129,.10);
        border: 1px solid rgba(15,23,42,.08);
        color: rgba(15,23,42,.78);
        font-weight: 850;
        font-size: .85rem;
        white-space: nowrap;
    }

    .contact-list{ display:grid; gap: 12px; }

    .contact-item{
        display:flex;
        gap: 12px;
        padding: 12px 12px;
        border-radius: 18px;
        background: rgba(15,23,42,.02);
        border: 1px solid rgba(15,23,42,.08);
    }
    .contact-ico{
        width: 40px; height: 40px;
        border-radius: 16px;
        display:grid; place-items:center;
        background: rgba(29,78,216,.14);
        color: #1d4ed8;
        border: 1px solid rgba(15,23,42,.06);
        flex: 0 0 auto;
    }
    .contact-label{
        font-weight: 900;
        letter-spacing: -0.01em;
        line-height: 1.1;
    }
    .contact-value{
        color: rgba(15,23,42,.70);
        font-weight: 650;
        margin-top: 2px;
        line-height: 1.4;
    }

    .contact-form-ico{
        width: 44px; height: 44px;
        border-radius: 16px;
        display:grid; place-items:center;
        background: linear-gradient(135deg, rgba(29,78,216,.16), rgba(6,182,212,.12));
        color: #1d4ed8;
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 14px 35px rgba(2,6,23,.06);
        flex: 0 0 auto;
    }

    .kt-input{
        border-radius: 16px;
        border: 1px solid rgba(15,23,42,.12);
        padding: .85rem .95rem;
        font-weight: 650;
        box-shadow: none;
    }
    .kt-input:focus{
        border-color: rgba(29,78,216,.35);
        box-shadow: 0 0 0 .22rem rgba(29,78,216,.10);
    }

    .map-shell{
        border-radius: 22px;
        border: 1px solid rgba(15,23,42,.10);
        overflow:hidden;
        background: rgba(15,23,42,.02);
        box-shadow: 0 18px 55px rgba(2,6,23,.06);
        min-height: 320px;
        display:grid;
        place-items:center;
    }
    .map-placeholder{
        text-align:center;
        padding: 18px;
        color: rgba(15,23,42,.75);
    }
    .map-placeholder i{
        font-size: 2rem;
        color: rgba(29,78,216,.75);
    }
</style>
@endsection
