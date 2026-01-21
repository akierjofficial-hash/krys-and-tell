@extends('layouts.public')
@section('title', 'About — Krys & Tell')

@section('content')
<section class="section section-soft about-wrap">
    <div class="container">
        {{-- HERO --}}
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="about-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-tooth"></i>
                    <span>About the clinic</span>
                </div>

                <h1 class="sec-title mt-3">A brighter smile starts with a gentler visit.</h1>
                <div class="sec-sub">
                    At <b>Krys &amp; Tell</b>, we blend modern dentistry with a calm, caring experience.
                    From your first hello at reception to your final check, we focus on comfort, clarity,
                    and results you can feel confident about.
                </div>

                {{-- Highlights --}}
                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <div class="about-card p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico"><i class="fa-solid fa-hand-holding-heart"></i></span>
                                <div class="about-card-title">Comfort first</div>
                            </div>
                            <div class="about-card-text mt-2">
                                A clean, relaxing space designed to feel calm—so your mind can breathe while we care for your smile.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="about-card p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico alt"><i class="fa-solid fa-clipboard-check"></i></span>
                                <div class="about-card-title">Clear plans</div>
                            </div>
                            <div class="about-card-text mt-2">
                                We explain your options, costs, and next steps before anything begins—no pressure, just clarity.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="about-card p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico alt2"><i class="fa-solid fa-shield-heart"></i></span>
                                <div class="about-card-title">Gentle hands</div>
                            </div>
                            <div class="about-card-text mt-2">
                                We move at your pace, use gentle techniques, and always prioritize what feels comfortable for you.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="about-card p-4 h-100">
                            <div class="d-flex align-items-center gap-2">
                                <span class="about-ico alt3"><i class="fa-solid fa-star"></i></span>
                                <div class="about-card-title">Confidence boost</div>
                            </div>
                            <div class="about-card-text mt-2">
                                From cleanings to smile makeovers, our goal is simple: help you smile bigger—without worry.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                        View services <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                    <a class="btn kt-btn kt-btn-outline" href="{{ url('/contact') }}">
                        Book / inquire
                    </a>
                </div>

                {{-- Micro trust line --}}
                <div class="about-trust mt-3">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Warm staff • Clear pricing • Clean &amp; modern clinic</span>
                </div>
            </div>

            <div class="col-lg-6">
                {{-- Right image + badge --}}
                <div class="img-tile hero-tile">
                    <img src="{{ asset('assets/img/public/pic6.jpg') }}" alt="Clinic">
                    <div class="hero-badge">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-dot"></span>
                            <div>
                                <div class="hero-badge-title">Dental Center, gentle approach</div>
                                <div class="hero-badge-sub">Designed to feel welcoming — not intimidating.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-fact mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/krysandtelllogo.jpg') }}" class="about-logo" alt="Logo">
                        <div>
                            <div class="fact-title">A clinic that feels like care.</div>
                            <div class="fact-sub">
                                We treat patients like family—because comfort and trust matter.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STAFF SECTION --}}
        <div class="about-divider"></div>

        <div class="row align-items-end g-3">
            <div class="col-lg-7">
                <div class="about-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-people-group"></i>
                    <span>Meet the team</span>
                </div>
                <h2 class="about-h2 mt-3">Friendly faces. Expert care. Zero judgment.</h2>
                <p class="about-p">
                    Our team is here to make dental visits feel easier—especially if you’re nervous.
                    Expect a warm welcome, gentle guidance, and a treatment plan that truly fits you.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="about-quote">
                    <i class="fa-solid fa-quote-left"></i>
                    <div>
                        “We don’t just fix teeth — we build confidence, one smile at a time.”
                        <div class="quote-sub">— Krys &amp; Tell Team</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <div class="staff-card">
                    <div class="staff-img">
                        <img src="{{ asset('assets/img/public/staffimg1.jpg') }}" alt="Staff photo 1">
                    </div>
                    <div class="staff-body">
                        <div class="staff-name">Your Caring Team</div>
                        <div class="staff-role">Comfort-focused care</div>
                        <div class="staff-desc">
                            Gentle, attentive, and always ready to guide you—especially if it’s your first visit.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="staff-card">
                    <div class="staff-img">
                        <img src="{{ asset('assets/img/public/staffimg2.jpg') }}" alt="Staff photo 2">
                    </div>
                    <div class="staff-body">
                        <div class="staff-name">Modern Dentistry</div>
                        <div class="staff-role">Clear &amp; honest plans</div>
                        <div class="staff-desc">
                            We explain everything simply—options, costs, and next steps—so you feel confident.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="staff-card">
                    <div class="staff-img">
                        <img src="{{ asset('assets/img/public/staffimg3.jpg') }}" alt="Staff photo 3">
                    </div>
                    <div class="staff-body">
                        <div class="staff-name">Smile Partners</div>
                        <div class="staff-role">Results you’ll love</div>
                        <div class="staff-desc">
                            From cleanings to transformations—our goal is a healthier smile you’re proud of.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GALLERY STRIP --}}
        <div class="about-divider"></div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="img-tile strip-tile">
                    <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Reception">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile strip-tile">
                    <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                </div>
            </div>
            <div class="col-md-4">
                <div class="img-tile strip-tile">
                    <img src="{{ asset('assets/img/public/pic8.jpg') }}" alt="Clinic interior">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ==========================================================
   About Page — Public
   - Responsive, modern, mobile-first
   ========================================================== */

.about-wrap{
    --ink: rgba(17,17,17,.92);
    --muted: rgba(17,17,17,.62);
    --border: rgba(17,17,17,.10);
    --shadow: 0 18px 55px rgba(11,18,32,.10);
    --soft: rgba(176,124,88,.10);
    --soft2: rgba(216,193,176,.22);
}

/* Pills */
.about-pill{
    background: var(--soft);
    border: 1px solid var(--border);
    font-weight: 900;
    color: var(--ink);
    box-shadow: 0 14px 35px rgba(11,18,32,.06);
}
.about-pill i{ color: var(--brand); }

/* Hero image tile */
.img-tile{
    border-radius: 26px;
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    background: rgba(255,255,255,.75);
    position: relative;
}
.img-tile img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display:block;
    transform: scale(1.01);
}

/* Hero tile sizing */
.hero-tile{
    height: 460px;
}
@media (max-width: 991.98px){
    .hero-tile{ height: 360px; }
}
@media (max-width: 575.98px){
    .hero-tile{ height: 300px; border-radius: 22px; }
}

/* Badge on hero image */
.hero-badge{
    position:absolute;
    left: 14px;
    right: 14px;
    bottom: 14px;
    padding: 12px 14px;
    border-radius: 18px;
    background: rgba(255,255,255,.90);
    border: 1px solid var(--border);
    box-shadow: 0 18px 50px rgba(11,18,32,.10);
    backdrop-filter: blur(6px);
}
.badge-dot{
    width:10px; height:10px;
    border-radius:999px;
    background: var(--brand);
    box-shadow: 0 0 0 6px rgba(176,124,88,.18);
    flex: 0 0 auto;
}
.hero-badge-title{
    font-weight: 950;
    color: var(--ink);
    line-height: 1.2;
}
.hero-badge-sub{
    color: var(--muted);
    font-weight: 650;
    font-size: 13px;
    margin-top: 2px;
}

/* Cards */
.about-card{
    border-radius: 22px;
    background: rgba(255,255,255,.92);
    border: 1px solid var(--border);
    box-shadow: 0 18px 50px rgba(11,18,32,.06);
    transition: transform .18s ease, box-shadow .18s ease;
}
.about-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 22px 60px rgba(11,18,32,.10);
}
.about-card-title{
    font-weight: 950;
    color: var(--ink);
}
.about-card-text{
    color: var(--muted);
    font-weight: 650;
    line-height: 1.7;
}

/* Icons */
.about-ico{
    width:44px; height:44px;
    border-radius: 16px;
    display:grid; place-items:center;
    background: rgba(176,124,88,.22);
    color: #fff;
    border: 1px solid var(--border);
    box-shadow: 0 14px 35px rgba(11,18,32,.06);
    flex: 0 0 auto;
}
.about-ico.alt{ background: rgba(216,193,176,.30); color:#6f6d6b; }
.about-ico.alt2{ background: rgba(176,124,88,.16); color: var(--brand); }
.about-ico.alt3{ background: rgba(11,18,32,.06); color: var(--brand); }

/* Fact block */
.about-fact{
    border-radius: 22px;
    background: rgba(255,255,255,.92);
    border: 1px solid var(--border);
    box-shadow: 0 18px 50px rgba(11,18,32,.08);
    padding: 14px 16px;
}
.about-logo{
    width:44px; height:44px;
    border-radius: 16px;
    object-fit: cover;
    border: 1px solid var(--border);
    background:#fff;
}
.fact-title{
    font-weight: 950;
    color: var(--ink);
}
.fact-sub{
    color: var(--muted);
    font-weight: 650;
    font-size: 13px;
    line-height: 1.5;
}

/* Trust line */
.about-trust{
    display:flex;
    align-items:center;
    gap:10px;
    color: var(--muted);
    font-weight: 700;
}
.about-trust i{
    color: var(--brand);
}

/* Section headings */
.about-h2{
    font-weight: 950;
    letter-spacing: -.4px;
    color: var(--ink);
}
.about-p{
    color: var(--muted);
    font-weight: 650;
    line-height: 1.75;
    margin-bottom: 0;
}

/* Quote */
.about-quote{
    border-radius: 22px;
    background: rgba(255,255,255,.90);
    border: 1px solid var(--border);
    box-shadow: 0 18px 50px rgba(11,18,32,.06);
    padding: 14px 16px;
    display:flex;
    gap: 10px;
    color: var(--ink);
    font-weight: 850;
}
.about-quote i{
    color: var(--brand);
    margin-top: 2px;
}
.quote-sub{
    margin-top: 6px;
    color: var(--muted);
    font-weight: 700;
    font-size: 13px;
}

/* Staff cards */
.staff-card{
    border-radius: 26px;
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 18px 55px rgba(11,18,32,.08);
    background: rgba(255,255,255,.92);
    transition: transform .18s ease, box-shadow .18s ease;
    height: 100%;
}
.staff-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 24px 70px rgba(11,18,32,.12);
}
.staff-img{
    height: 250px;
    background: rgba(255,255,255,.8);
}
.staff-img img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
}
.staff-body{
    padding: 14px 16px 16px;
}
.staff-name{
    font-weight: 950;
    color: var(--ink);
    letter-spacing: -.2px;
}
.staff-role{
    margin-top: 2px;
    color: var(--brand);
    font-weight: 850;
    font-size: 13px;
}
.staff-desc{
    margin-top: 8px;
    color: var(--muted);
    font-weight: 650;
    line-height: 1.65;
}

/* Gallery strip tiles */
.strip-tile{ height: 220px; }
@media (max-width: 991.98px){
    .strip-tile{ height: 200px; }
}
@media (max-width: 575.98px){
    .strip-tile{ height: 180px; border-radius: 22px; }
    .staff-img{ height: 220px; }
}

/* Divider */
.about-divider{
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(17,17,17,.12), transparent);
    margin: 34px 0;
}
</style>
@endsection
