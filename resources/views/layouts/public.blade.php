<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Krys & Tell Dental Center')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root{
            /* Palette inspired by your logo */
            --brand:#B07C58;     /* bronze */
            --brand2:#D8C1B0;    /* warm sand */
            --ink:#0b1220;
            --text:#171717;
            --muted: rgba(23,23,23,.68);
            --soft:#FBF7F2;      /* warm off-white */
            --card:#ffffff;
            --border: rgba(17,17,17,.10);
            --shadow: 0 18px 55px rgba(11,18,32,.08);
            --radius: 22px;
        }

        html, body{ height:100%; }
        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--text);
            background: #fff;
        }
        a{ color: inherit; }
        .text-muted-2{ color: var(--muted); }

        /* Buttons */
        .kt-btn{
            border-radius: 999px;
            font-weight: 850;
            padding: .72rem 1.1rem;
            letter-spacing: -.01em;
        }
        .kt-btn-primary{
            background: linear-gradient(135deg, var(--brand), #d2a07a);
            border: none;
            box-shadow: 0 16px 40px rgba(176,124,88,.22);
        }
        .kt-btn-primary:hover{ filter: brightness(.98); }
        .kt-btn-outline{
            border: 1px solid rgba(17,17,17,.18);
            background: rgba(255,255,255,.90);
        }

        /* Navbar */
        .kt-nav{
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(251,247,242,.78);
            border-bottom: 1px solid rgba(17,17,17,.10);
        }
        .kt-brand{
            font-weight: 950;
            letter-spacing: -0.04em;
            display:flex;
            align-items:center;
            gap:.7rem;
            text-decoration: none;
        }
        .kt-logo{
            width: 44px;
            height: 44px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid rgba(17,17,17,.10);
            box-shadow: 0 16px 40px rgba(11,18,32,.10);
            background: #fff;
        }
        .kt-brand-text{ line-height: 1.05; }
        .kt-brand-text .name{ font-weight: 950; color: #3b3b3b; }
        .kt-brand-text .sub{ font-weight: 750; font-size: .82rem; color: rgba(23,23,23,.62); }

        .navbar .nav-link{
            font-weight: 850;
            color: rgba(23,23,23,.72);
            border-radius: 999px;
            padding: .5rem .9rem !important;
            transition: background .15s ease, color .15s ease;
        }
        .navbar .nav-link:hover{
            background: rgba(176,124,88,.10);
            color: rgba(23,23,23,.92);
        }
        .navbar .nav-link.active{
            background: rgba(176,124,88,.14);
            color: rgba(23,23,23,.95);
        }

        /* Sections */
        .section{ padding: 78px 0; }
        .section-soft{
            background:
                radial-gradient(1200px 650px at 10% 0%, rgba(176,124,88,.10), transparent 58%),
                radial-gradient(900px 520px at 90% 20%, rgba(216,193,176,.16), transparent 55%),
                var(--soft);
        }
        .sec-title{
            font-weight: 950;
            letter-spacing: -0.04em;
            margin-bottom: 10px;
        }
        .sec-sub{ color: var(--muted); font-weight: 650; line-height: 1.7; }

        /* Cards */
        .card-soft{
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card);
            box-shadow: var(--shadow);
            overflow:hidden;
        }

        .icon-badge{
            width:48px; height:48px;
            border-radius: 18px;
            display:grid; place-items:center;
            color:#fff;
            background: linear-gradient(135deg, var(--brand), #d2a07a);
            box-shadow: 0 16px 35px rgba(176,124,88,.22);
        }

        /* Tiles */
        .img-tile{
            border-radius: var(--radius);
            overflow:hidden;
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 18px 60px rgba(11,18,32,.14);
        }
        .img-tile img{ width:100%; height:100%; object-fit:cover; display:block; transform: scale(1.01); }

        /* Inputs (shared) */
        .kt-input{
            border-radius: 16px;
            border: 1px solid rgba(17,17,17,.14);
            padding: .85rem .95rem;
            font-weight: 650;
            box-shadow: none;
        }
        .kt-input:focus{
            border-color: rgba(176,124,88,.45);
            box-shadow: 0 0 0 .22rem rgba(176,124,88,.12);
        }

        /* Footer */
        .kt-footer{
            position: relative;
            color: rgba(255,255,255,.90);
            background:
                radial-gradient(900px 500px at 15% 15%, rgba(176,124,88,.28), transparent 55%),
                radial-gradient(800px 480px at 85% 25%, rgba(216,193,176,.18), transparent 55%),
                linear-gradient(180deg, #1b1b1b 0%, #0f0f0f 100%);
            border-top: 1px solid rgba(255,255,255,.10);
            overflow: hidden;
        }
        .kt-footer-top{ padding: 56px 0 36px; }

        .kt-footer-brand{ text-decoration: none; }
        .kt-footer-logo{
            width:48px;height:48px;border-radius:16px;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 16px 50px rgba(0,0,0,.25);
            background: #fff;
        }
        .kt-footer-name{
            font-weight: 950;
            letter-spacing: -0.02em;
            color:#fff;
            font-size: 1.15rem;
            line-height:1.1;
        }
        .kt-footer-name small{
            font-weight: 700;
            color: rgba(255,255,255,.72);
            font-size: .85rem;
            margin-top: .2rem;
        }
        .kt-footer-desc{
            color: rgba(255,255,255,.72);
            line-height: 1.7;
            max-width: 46ch;
            font-weight: 600;
        }

        .kt-footer-title{
            font-weight: 950;
            color:#fff;
            margin-bottom: 14px;
            letter-spacing: -0.01em;
        }
        .kt-footer-links{
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }
        .kt-footer-links a{
            text-decoration: none;
            color: rgba(255,255,255,.78);
            font-weight: 650;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            transition: transform .15s ease, color .15s ease;
        }
        .kt-footer-links a:hover{
            color: rgba(255,255,255,.98);
            transform: translateX(2px);
        }

        .kt-social{
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            text-decoration: none;
            color: rgba(255,255,255,.92);
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 16px 40px rgba(0,0,0,.25);
            transition: transform .15s ease, background .15s ease;
        }
        .kt-social:hover{
            transform: translateY(-2px);
            background: rgba(255,255,255,.14);
        }

        .kt-footer-info{ display: grid; gap: 12px; }
        .kt-info-item{
            display:flex;
            gap: 12px;
            padding: 12px 12px;
            border-radius: 18px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.10);
        }
        .kt-info-ico{
            width: 38px;
            height: 38px;
            border-radius: 14px;
            display:grid;
            place-items:center;
            background: rgba(176,124,88,.18);
            border: 1px solid rgba(255,255,255,.10);
            color:#fff;
            flex: 0 0 auto;
        }
        .kt-info-main{
            font-weight: 900;
            color: rgba(255,255,255,.95);
            line-height: 1.1;
        }
        .kt-info-sub{
            color: rgba(255,255,255,.72);
            font-weight: 650;
            margin-top: 2px;
        }

        .kt-footer-hr{
            border-color: rgba(255,255,255,.10);
            opacity: 1;
        }
        .kt-footer-copy{
            color: rgba(255,255,255,.70);
            font-weight: 650;
        }
        .kt-footer-mini-links a{
            text-decoration:none;
            color: rgba(255,255,255,.70);
            font-weight: 700;
            transition: color .15s ease;
        }
        .kt-footer-mini-links a:hover{ color: rgba(255,255,255,.98); }
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg kt-nav">
    <div class="container py-2">
        <a class="kt-brand" href="{{ url('/') }}">
            <img class="kt-logo" src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys & Tell logo">
            <div class="kt-brand-text">
                <div class="name">Krys &amp; Tell</div>
                <div class="sub">Dental Center</div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ktNav" aria-controls="ktNav" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        @php
            $path = request()->path(); // "" for home
            $isActive = fn($p) => ($p === '/' ? $path === '' : str_starts_with($path, ltrim($p,'/')));
        @endphp

        <div id="ktNav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-lg-1 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ $isActive('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isActive('/about') ? 'active' : '' }}" href="{{ url('/about') }}">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isActive('/services') ? 'active' : '' }}" href="{{ url('/services') }}">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isActive('/contact') ? 'active' : '' }}" href="{{ url('/contact') }}">Contact</a>
                </li>
            </ul>

            {{-- Public: no login/portal buttons --}}
            <div class="d-flex gap-2 ms-lg-3 mt-3 mt-lg-0">
                <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                    <i class="fa-solid fa-calendar-check me-1"></i> Book
                </a>
            </div>
        </div>
    </div>
</nav>

@yield('content')

<footer class="kt-footer mt-5">
    <div class="kt-footer-top">
        <div class="container">
            <div class="row g-4">
                {{-- Brand --}}
                <div class="col-lg-4">
                    <a href="{{ url('/') }}" class="kt-footer-brand d-inline-flex align-items-center gap-2">
                        <img class="kt-footer-logo" src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys & Tell logo">
                        <span class="kt-footer-name">
                            Krys <span style="color:rgba(210,160,122,.95)">&amp;</span> Tell
                            <small class="d-block">Dental Center</small>
                        </span>
                    </a>

                    <p class="kt-footer-desc mt-3 mb-4">
                        Gentle dental care with clear explanations and modern treatment —
                        designed to feel calm, clean, and comfortable.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="kt-social" href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a class="kt-social" href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a class="kt-social" href="#" aria-label="Tiktok"><i class="fa-brands fa-tiktok"></i></a>
                        <a class="kt-social" href="#" aria-label="Messenger"><i class="fa-brands fa-facebook-messenger"></i></a>
                    </div>
                </div>

                {{-- Quick links --}}
                <div class="col-6 col-lg-2">
                    <div class="kt-footer-title">Quick Links</div>
                    <ul class="kt-footer-links">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/about') }}">About</a></li>
                        <li><a href="{{ url('/services') }}">Services</a></li>
                        <li><a href="{{ url('/contact') }}">Contact</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div class="col-6 col-lg-3">
                    <div class="kt-footer-title">Popular Services</div>
                    <ul class="kt-footer-links">
                        <li><a href="{{ url('/services') }}"><i class="fa-solid fa-tooth me-2"></i>General Dentistry</a></li>
                        <li><a href="{{ url('/services') }}"><i class="fa-solid fa-teeth me-2"></i>Orthodontics</a></li>
                        <li><a href="{{ url('/services') }}"><i class="fa-solid fa-face-smile me-2"></i>Cosmetic Dentistry</a></li>
                        <li><a href="{{ url('/services') }}"><i class="fa-solid fa-shield-heart me-2"></i>Cleaning & Prevention</a></li>
                    </ul>
                </div>

                {{-- Contact / Hours --}}
                <div class="col-lg-3">
                    <div class="kt-footer-title">Visit Us</div>

                    <div class="kt-footer-info">
                        <div class="kt-info-item">
                            <span class="kt-info-ico"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <div class="kt-info-main">Clinic Address</div>
                                <div class="kt-info-sub">Your street, barangay, city</div>
                            </div>
                        </div>

                        <div class="kt-info-item">
                            <span class="kt-info-ico"><i class="fa-solid fa-phone"></i></span>
                            <div>
                                <div class="kt-info-main">Phone</div>
                                <div class="kt-info-sub">+63 9XX XXX XXXX</div>
                            </div>
                        </div>

                        <div class="kt-info-item">
                            <span class="kt-info-ico"><i class="fa-solid fa-clock"></i></span>
                            <div>
                                <div class="kt-info-main">Clinic Hours</div>
                                <div class="kt-info-sub">Mon–Sat: 9:00 AM – 6:00 PM</div>
                            </div>
                        </div>
                    </div>

                    <a class="btn kt-btn kt-btn-primary text-white w-100 mt-3" href="{{ url('/services') }}">
                        <i class="fa-solid fa-calendar-check me-1"></i> Book an Appointment
                    </a>

                    <div class="mt-2" style="font-size:.9rem;color:rgba(255,255,255,.72);font-weight:650;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Online booking is quick — choose service, date, and time.
                    </div>
                </div>
            </div>

            <hr class="kt-footer-hr my-4">

            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between pb-2">
                <div class="kt-footer-copy">
                    © {{ date('Y') }} Krys &amp; Tell Dental Center. All rights reserved.
                </div>

                <div class="kt-footer-mini-links d-flex flex-wrap gap-3">
                    <a href="#" class="mini">Privacy Policy</a>
                    <a href="#" class="mini">Terms</a>
                    <a href="{{ url('/contact') }}" class="mini">Support</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
