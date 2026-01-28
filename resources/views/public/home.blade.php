@extends('layouts.public')
@section('title', 'Krys&Tell — Dental Center')

@section('content')
<section class="kt-hero position-relative overflow-hidden">
    <div class="kt-hero-bg" style="background-image:url('{{ asset('assets/img/public/pic1.jpg') }}');"></div>
    <div class="kt-hero-overlay"></div>

    <div class="container position-relative py-5 kt-hero-container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="kt-hero-card">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="kt-pill"><i class="fa-solid fa-shield-heart"></i> Gentle care</span>
                        <span class="kt-pill"><i class="fa-solid fa-circle-check"></i> Clean & safe</span>
                        <span class="kt-pill"><i class="fa-solid fa-star"></i> Trusted clinic</span>
                    </div>

                    <h1 class="kt-hero-title">
                        Welcome to Krys&Tell.<br class="d-none d-md-block">
                    </h1>

                    <p class="kt-hero-sub">
                        <b>Krys &amp; Tell</b> — clear explanations, gentle hands,
                        and a clinic that feels warm and comfortable. Book in minutes.
                    </p>

                    {{-- ✅ Mobile-friendly CTA stack --}}
                    <div class="kt-cta-row mt-4">
                        <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                            <i class="fa-solid fa-calendar-check me-1"></i> Book an Appointment
                        </a>
                        <a class="btn kt-btn kt-btn-outline" href="{{ url('/about') }}">
                            Learn More <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="row g-2 mt-4">
                        <div class="col-6 col-md-4">
                            <div class="kt-chip"><i class="fa-solid fa-tooth"></i><span>General</span></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="kt-chip"><i class="fa-solid fa-teeth"></i><span>Orthodontics</span></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="kt-chip"><i class="fa-solid fa-face-smile"></i><span>Cosmetic</span></div>
                        </div>
                    </div>

                    <div class="kt-hero-stats mt-4">
                        <div class="kt-stat">
                            <div class="kt-stat-ico"><i class="fa-solid fa-hand-holding-heart"></i></div>
                            <div>
                                <div class="kt-stat-title">Comfort-first</div>
                                <div class="kt-stat-sub">Gentle approach & clear steps</div>
                            </div>
                        </div>
                        <div class="kt-stat">
                            <div class="kt-stat-ico"><i class="fa-solid fa-clock"></i></div>
                            <div>
                                <div class="kt-stat-title">Fast booking</div>
                                <div class="kt-stat-sub">Pick service, date, time</div>
                            </div>
                        </div>
                    </div>

                    <div class="kt-hero-note mt-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Need help? Visit the <a href="{{ url('/contact') }}" class="text-white fw-bold text-decoration-underline">Contact</a> page.
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="img-tile tile-lg">
                            <img src="{{ asset('assets/img/public/pic2.jpg') }}" alt="Clinic reception">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="img-tile tile-sm">
                            <img src="{{ asset('assets/img/public/pic3.jpg') }}" alt="Clinic room">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="img-tile tile-sm">
                            <img src="{{ asset('assets/img/public/pic1.jpg') }}" alt="Clinic interior">
                        </div>
                    </div>
                </div>

                <div class="kt-trust mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/krysandtelllogo.jpg') }}" class="kt-trust-logo" alt="Logo">
                        <div>
                            <div style="font-weight:950;">Dental Center, modern comfort</div>
                            <div class="text-muted-2" style="font-weight:650;">A clinic experience that feels easy from start to finish.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-blob kt-blob-1"></div>
    <div class="kt-blob kt-blob-2"></div>

    <style>
        .kt-hero{ min-height: 78vh; display:flex; align-items:center; }
        .kt-hero-container{ padding-left: 14px; padding-right: 14px; }

        .kt-hero-bg{
            position:absolute; inset:0;
            background-size:cover; background-position:center;
            transform:scale(1.02);
        }
        .kt-hero-overlay{
            position:absolute; inset:0;
            background:
                radial-gradient(1200px 600px at 20% 30%, rgba(176,124,88,.35), transparent 55%),
                radial-gradient(900px 500px at 80% 40%, rgba(216,193,176,.22), transparent 55%),
                linear-gradient(180deg, rgba(15,15,15,.26), rgba(15,15,15,.72));
        }

        .kt-hero-card{
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 18px 60px rgba(0,0,0,.28);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 22px;
            padding: 28px;
            color:#fff;
        }

        .kt-hero-title{
            font-weight: 950;
            letter-spacing: -0.04em;
            font-size: clamp(2.05rem, 3.4vw, 3.1rem);
            line-height: 1.08;
            margin:0;
        }

        .kt-hero-sub{
            margin-top: 12px;
            color: rgba(255,255,255,.88);
            font-size: 1.05rem;
            line-height: 1.6;
            max-width: 54ch;
            font-weight: 650;
        }

        .kt-pill{
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.5rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            color: rgba(255,255,255,.92);
            font-weight: 800;
            font-size: .9rem;
            white-space: nowrap;
        }

        /* ✅ CTA row becomes stacked on mobile */
        .kt-cta-row{
            display:flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .kt-chip{
            display:flex; align-items:center; justify-content:center; gap:.55rem;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.16);
            color: rgba(255,255,255,.92);
            border-radius: 16px;
            padding: .72rem .85rem;
            font-weight: 850;
            height: 100%;
        }

        .kt-hero-stats{
            display:grid;
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        @media (min-width: 768px){
            .kt-hero-stats{ grid-template-columns: 1fr 1fr; }
        }

        .kt-stat{
            display:flex; align-items:center; gap:.75rem;
            padding:.85rem .95rem;
            border-radius: 18px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.14);
        }

        .kt-stat-ico{
            width:42px; height:42px; border-radius: 14px;
            display:grid; place-items:center;
            background: rgba(176,124,88,.26);
            color:#fff;
            flex: 0 0 auto;
        }

        .kt-stat-title{ color:#fff; font-weight: 900; line-height:1.1; }
        .kt-stat-sub{ color: rgba(255,255,255,.78); font-weight: 650; font-size:.92rem; }

        /* ✅ Note becomes pill for readability on mobile */
        .kt-hero-note{
            color: rgba(255,255,255,.85);
            font-weight: 650;
            font-size: .92rem;
            padding: .7rem .85rem;
            border-radius: 16px;
            background: rgba(0,0,0,.18);
            border: 1px solid rgba(255,255,255,.14);
        }

        /* ✅ Image tiles: responsive heights */
        .img-tile{
            border-radius: 20px;
            overflow:hidden;
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.06);
            box-shadow: 0 18px 50px rgba(0,0,0,.18);
        }
        .img-tile img{
            width:100%;
            height:100%;
            object-fit: cover;
            display:block;
        }
        .tile-lg{ height:220px; }
        .tile-sm{ height:190px; }

        .kt-trust{
            border-radius: 20px;
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(17,17,17,.10);
            box-shadow: 0 18px 50px rgba(11,18,32,.10);
            padding: 14px 16px;
        }
        .kt-trust-logo{
            width:44px; height:44px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid rgba(17,17,17,.10);
            background:#fff;
            flex: 0 0 auto;
        }

        .kt-blob{
            position:absolute; width:520px; height:520px; border-radius: 50%;
            filter: blur(70px);
            opacity:.40;
            pointer-events:none;
        }
        .kt-blob-1{
            left:-160px; top:-180px;
            background: radial-gradient(circle at 30% 30%, rgba(176,124,88,.95), rgba(216,193,176,.12));
        }
        .kt-blob-2{
            right:-220px; bottom:-240px;
            background: radial-gradient(circle at 30% 30%, rgba(216,193,176,.75), rgba(176,124,88,.10));
        }

        /* =========================
           ✅ MOBILE IMPROVEMENTS
           ========================= */
        @media (max-width: 576px){
            .kt-hero{ min-height: auto; }
            .kt-hero-container{ padding-top: 12px; padding-bottom: 18px; }

            .kt-hero-card{
                padding: 18px;
                border-radius: 20px;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }

            .kt-hero-title{
                font-size: 1.95rem;
                line-height: 1.06;
            }

            .kt-hero-sub{
                font-size: .98rem;
                line-height: 1.55;
                margin-top: 10px;
            }

            .kt-pill{
                font-size: .78rem;
                padding: .42rem .62rem;
            }

            .kt-cta-row{
                display:grid;
                grid-template-columns: 1fr;
                gap: .6rem;
            }
            .kt-cta-row .btn{
                width: 100%;
                justify-content:center;
            }

            .kt-chip{
                justify-content:flex-start;
                padding: .62rem .72rem;
                border-radius: 14px;
            }

            .kt-stat{
                padding: .75rem .8rem;
                border-radius: 16px;
            }
            .kt-stat-ico{
                width: 38px;
                height: 38px;
                border-radius: 13px;
            }

            .tile-lg{ height:170px; }
            .tile-sm{ height:150px; }

            .kt-trust{
                padding: 12px 12px;
                border-radius: 18px;
            }
            .kt-trust-logo{
                width: 40px;
                height: 40px;
                border-radius: 14px;
            }
        }
    </style>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-7">
                <h2 class="sec-title">Care that’s clear, gentle, and well-explained.</h2>
                <div class="sec-sub">
                    We keep things comfortable and simple — so you always know what to expect.
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <a class="btn kt-btn kt-btn-outline" href="{{ url('/services') }}">
                    View services <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-4">
                <div class="card-soft p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="icon-badge"><i class="fa-solid fa-user-doctor"></i></span>
                        <div style="font-weight:950;">Friendly dental team</div>
                    </div>
                    <div class="text-muted-2" style="font-weight:650;line-height:1.7;">
                        Honest recommendations and step-by-step explanations — no pressure.
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-soft p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="icon-badge" style="background:linear-gradient(135deg, rgba(216,193,176,.95), rgba(176,124,88,.95));">
                            <i class="fa-solid fa-microscope"></i>
                        </span>
                        <div style="font-weight:950;">Clean & modern setup</div>
                    </div>
                    <div class="text-muted-2" style="font-weight:650;line-height:1.7;">
                        A bright space with a warm vibe — designed to feel calm.
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-soft p-4 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="icon-badge"><i class="fa-solid fa-calendar-check"></i></span>
                        <div style="font-weight:950;">Easy online booking</div>
                    </div>
                    <div class="text-muted-2" style="font-weight:650;line-height:1.7;">
                        Choose a service, pick a schedule, and you’re set.
                    </div>
                </div>
            </div>
        </div>

        <div class="card-soft p-4 mt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div style="font-weight:950;font-size:1.08rem;">Ready to book?</div>
                    <div class="text-muted-2" style="font-weight:650;">Choose a service and reserve a schedule that fits you.</div>
                </div>
                <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                    Start booking <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
