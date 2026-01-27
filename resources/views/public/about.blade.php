@extends('layouts.public')
@section('title', 'About — Krys&Tell')

@section('content')
<section class="section section-soft about-min">
    <div class="container">

        {{-- HERO --}}
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="about-kicker">
                    <i class="fa-solid fa-tooth me-2"></i>
                    About Krys &amp; Tell
                </div>

                <h1 class="sec-title mt-3">Gentle dentistry, modern care.</h1>

                <p class="sec-sub mt-2 mb-0">
                    We keep things simple: a calm clinic, clear treatment plans, and a team that takes care of you
                    like family — so you can smile with confidence.
                </p>

                {{-- Minimal highlights --}}
                <div class="row g-3 mt-4">
                    <div class="col-12 col-sm-4">
                        <div class="mini-point">
                            <div class="mini-ico"><i class="fa-solid fa-hand-holding-heart"></i></div>
                            <div class="mini-title">Comfort</div>
                            <div class="mini-sub">Relaxed, clean, welcoming.</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="mini-point">
                            <div class="mini-ico"><i class="fa-solid fa-clipboard-check"></i></div>
                            <div class="mini-title">Clarity</div>
                            <div class="mini-sub">Simple plans, honest guidance.</div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-4">
                        <div class="mini-point">
                            <div class="mini-ico"><i class="fa-solid fa-star"></i></div>
                            <div class="mini-title">Confidence</div>
                            <div class="mini-sub">Results you’ll love.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                        View services <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/contact') }}">
                        Contact us
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-tile-min">
                    <img src="{{ asset('assets/img/public/pic6.jpg') }}" alt="Clinic">
                </div>
            </div>
        </div>

        {{-- TEAM --}}
        <div class="about-sep"></div>

        <div class="row align-items-end g-3">
            <div class="col-lg-8">
                <h2 class="about-h2">Meet the team</h2>
                <p class="about-p mb-0">
                    Friendly faces you can trust — gentle hands, clear communication, and a calm experience every visit.
                </p>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12 col-md-4">
                <div class="staff-tile">
                    <img src="{{ asset('assets/img/public/staffimg1.jpg') }}" alt="Staff photo 1">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="staff-tile">
                    <img src="{{ asset('assets/img/public/staffimg2.jpg') }}" alt="Staff photo 2">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="staff-tile">
                    <img src="{{ asset('assets/img/public/staffimg3.jpg') }}" alt="Staff photo 3">
                </div>
            </div>
        </div>

        {{-- GALLERY --}}
        <div class="about-sep"></div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="gallery-tile">
                    <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                </div>
            </div>
            <div class="col-md-4">
                <div class="gallery-tile">
                    <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                </div>
            </div>
            <div class="col-md-4">
                <div class="gallery-tile">
                    <img src="{{ asset('assets/img/public/pic8.jpg') }}" alt="Clinic interior">
                </div>
            </div>
        </div>

    </div>
</section>

<style>
/* ==========================================================
   About — Minimal + Aesthetic
   ========================================================== */
.about-min{
    --ink: rgba(17,17,17,.92);
    --muted: rgba(17,17,17,.62);
    --border: rgba(17,17,17,.10);
    --shadow: 0 18px 55px rgba(11,18,32,.10);
}

/* Kicker */
.about-kicker{
    display:inline-flex;
    align-items:center;
    padding: 10px 14px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: rgba(176,124,88,.08);
    color: var(--ink);
    font-weight: 900;
    box-shadow: 0 14px 35px rgba(11,18,32,.05);
}
.about-kicker i{ color: var(--brand); }

/* Hero image */
.hero-tile-min{
    border-radius: 26px;
    overflow:hidden;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    background: rgba(255,255,255,.8);
    height: 460px;
}
.hero-tile-min img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
}
@media (max-width: 991.98px){
    .hero-tile-min{ height: 360px; }
}
@media (max-width: 575.98px){
    .hero-tile-min{ height: 300px; border-radius: 22px; }
}

/* Minimal points */
.mini-point{
    border: 1px solid var(--border);
    background: rgba(255,255,255,.86);
    border-radius: 20px;
    padding: 14px 14px;
    box-shadow: 0 12px 35px rgba(11,18,32,.05);
    height: 100%;
}
.mini-ico{
    width:40px;height:40px;
    border-radius: 14px;
    display:grid;place-items:center;
    background: rgba(176,124,88,.16);
    color: var(--brand);
    border: 1px solid rgba(17,17,17,.08);
    margin-bottom: 10px;
}
.mini-title{
    font-weight: 950;
    color: var(--ink);
    letter-spacing: -.2px;
}
.mini-sub{
    margin-top: 4px;
    color: var(--muted);
    font-weight: 650;
    line-height: 1.5;
    font-size: 13px;
}

/* Section text */
.about-h2{
    font-weight: 950;
    letter-spacing: -.4px;
    color: var(--ink);
    margin: 0;
}
.about-p{
    color: var(--muted);
    font-weight: 650;
    line-height: 1.75;
}

/* Staff tiles */
.staff-tile{
    border-radius: 26px;
    overflow:hidden;
    border: 1px solid var(--border);
    box-shadow: 0 18px 55px rgba(11,18,32,.08);
    background: rgba(255,255,255,.9);
    height: 320px;
}
.staff-tile img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
}
@media (max-width: 575.98px){
    .staff-tile{ height: 260px; border-radius: 22px; }
}

/* Gallery tiles */
.gallery-tile{
    border-radius: 22px;
    overflow:hidden;
    border: 1px solid var(--border);
    box-shadow: 0 14px 45px rgba(11,18,32,.07);
    background: rgba(255,255,255,.9);
    height: 210px;
}
.gallery-tile img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
}
@media (max-width: 575.98px){
    .gallery-tile{ height: 180px; }
}

/* Separator */
.about-sep{
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(17,17,17,.12), transparent);
    margin: 34px 0;
}
</style>
@endsection
