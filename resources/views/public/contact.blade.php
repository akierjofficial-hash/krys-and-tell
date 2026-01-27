@extends('layouts.public')
@section('title', 'Contact — Krys&Tell')

@section('content')
@php
    // ✅ exact file: public/images/map.png
    $mapRelative = 'images/map.png';
    $mapExists = file_exists(public_path($mapRelative));

    $u = auth()->user();
    $autoName  = trim(old('name',  $u->name  ?? ''));
    $autoEmail = trim(old('email', $u->email ?? ''));

    $isLoggedIn = auth()->check();
@endphp

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
                                <div class="contact-value">CT Building, Jose Romero Road, Bagacay (Across Hypermart), Dumaguete City, Philippines, 6200</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <span class="contact-ico" style="background:rgba(6,182,212,.14); color:#0891b2;">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <div>
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">0977 244 3595</div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <span class="contact-ico" style="background:rgba(16,185,129,.14); color:#059669;">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <div>
                                <div class="contact-label">Email</div>
                                <div class="contact-value">krysandt@gmail.com</div>
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

            {{-- Right: Message form + Map --}}
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

                    {{-- ✅ Success / Errors --}}
                    @if(session('contact_success'))
                        <div class="alert alert-success mt-3 mb-0" style="border-radius:16px;">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            {{ session('contact_success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mt-3 mb-0" style="border-radius:16px;">
                            <div style="font-weight:900;">Please fix the following:</div>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="mt-4" method="POST" action="{{ route('public.contact.store') }}">
                        @csrf

                        {{-- If not logged in, optionally show a gentle hint --}}
                        @guest
                            <div class="alert alert-info mt-0" style="border-radius:16px;">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Tip: Sign in with Google so your name and email autofill.
                            </div>
                        @endguest

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name</label>

                                @if($isLoggedIn)
                                    <input class="form-control kt-input"
                                           type="text"
                                           value="{{ $autoName }}"
                                           readonly
                                           aria-readonly="true">
                                    {{-- send value via hidden input so controller receives it --}}
                                    <input type="hidden" name="name" value="{{ $autoName }}">
                                    <div class="small text-muted-2 mt-1" style="font-weight:650;">
                                        This comes from your account.
                                    </div>
                                @else
                                    <input class="form-control kt-input"
                                           type="text"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Your name"
                                           required>
                                @endif
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>

                                @if($isLoggedIn)
                                    <input class="form-control kt-input"
                                           type="email"
                                           value="{{ $autoEmail }}"
                                           readonly
                                           aria-readonly="true">
                                    <input type="hidden" name="email" value="{{ $autoEmail }}">
                                    <div class="small text-muted-2 mt-1" style="font-weight:650;">
                                        We’ll reply to this email.
                                    </div>
                                @else
                                    <input class="form-control kt-input"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="you@email.com"
                                           required>
                                @endif
                            </div>

                            {{-- Message --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Message</label>
                                <textarea class="form-control kt-input"
                                          name="message"
                                          rows="5"
                                          placeholder="How can we help?"
                                          required>{{ old('message') }}</textarea>
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

                    {{-- Map --}}
                    <div class="mt-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div>
                                <div class="fw-black" style="font-weight:950;">Map</div>
                                <div class="text-muted-2" style="font-weight:650;">
                                    Find us easily — the clinic is across Hypermart.
                                </div>
                            </div>

                            <a class="btn kt-btn kt-btn-outline"
                               href="https://www.google.com/maps/search/?api=1&query=CT%20Building%20Jose%20Romero%20Road%20Bagacay%20Dumaguete%20City"
                               target="_blank" rel="noopener">
                                <i class="fa-solid fa-location-arrow me-1"></i> Open in Maps
                            </a>
                        </div>

                        <div class="map-shell mt-3">
                            @if($mapExists)
                                <img
                                    src="{{ asset($mapRelative) }}"
                                    alt="Clinic Map"
                                    class="map-img"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="map-missing">
                                    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                    <div class="fw-bold">Map image not found</div>
                                    <div class="text-muted-2" style="font-weight:650;">
                                        Put the file here exactly:
                                        <code>public/images/map.png</code>
                                    </div>
                                    <div class="text-muted-2" style="font-weight:650;">
                                        (Check filename/case: <code>map.png</code> not <code>Map.png</code>)
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2 text-muted-2" style="font-weight:650; font-size:.92rem;">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Tip: Just ask some parking boy there.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
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
        min-height: 260px;
    }
    .map-img{
        width: 100%;
        height: auto;
        display: block;
    }

    .map-missing{
        min-height: 260px;
        display:grid;
        place-items:center;
        text-align:center;
        padding: 18px;
    }
    .map-missing .icon{
        width: 44px; height: 44px;
        border-radius: 16px;
        display:grid; place-items:center;
        background: rgba(239,68,68,.12);
        border: 1px solid rgba(239,68,68,.20);
        color: #ef4444;
        margin-bottom: 10px;
    }

    @media (max-width: 768px){
        .map-shell{ min-height: 220px; }
        .map-missing{ min-height: 220px; }
    }
</style>
@endsection
