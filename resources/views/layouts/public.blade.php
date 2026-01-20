<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Krys & Tell Dental Center')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Palette inspired by your logo */
            --brand: #B07C58;
            --brand2: #D8C1B0;
            --ink: #0b1220;
            --text: #171717;
            --muted: rgba(23, 23, 23, .68);
            --soft: #FBF7F2;
            --card: #ffffff;
            --border: rgba(17, 17, 17, .10);
            --shadow: 0 18px 55px rgba(11, 18, 32, .08);
            --radius: 22px;
            --kt-safe-bottom: env(safe-area-inset-bottom, 0px);
        }

        html, body { height: 100%; }

        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--text);
            background: #fff;
        }

        a { color: inherit; }
        .text-muted-2 { color: var(--muted); }

        /* Buttons */
        .kt-btn {
            border-radius: 999px;
            font-weight: 850;
            padding: .72rem 1.1rem;
            letter-spacing: -.01em;
        }
        .kt-btn-primary {
            background: linear-gradient(135deg, var(--brand), #d2a07a);
            border: none;
            box-shadow: 0 16px 40px rgba(176, 124, 88, .22);
        }
        .kt-btn-primary:hover { filter: brightness(.98); }

        .kt-btn-outline {
            border: 1px solid rgba(17, 17, 17, .18);
            background: rgba(255, 255, 255, .90);
        }

        /* Navbar */
        .kt-nav {
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(251, 247, 242, .78);
            border-bottom: 1px solid rgba(17, 17, 17, .10);
        }

        .kt-brand {
            font-weight: 950;
            letter-spacing: -0.04em;
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            min-width: 0;
        }

        .kt-logo {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid rgba(17, 17, 17, .10);
            box-shadow: 0 16px 40px rgba(11, 18, 32, .10);
            background: #fff;
            display: block;
            flex: 0 0 auto;
        }

        .kt-brand-text { line-height: 1.05; min-width: 0; }
        .kt-brand-text .name { font-weight: 950; color: #3b3b3b; }
        .kt-brand-text .sub { font-weight: 750; font-size: .82rem; color: rgba(23, 23, 23, .62); }

        .navbar .nav-link {
            font-weight: 850;
            color: rgba(23, 23, 23, .72);
            border-radius: 999px;
            padding: .5rem .9rem !important;
            transition: background .15s ease, color .15s ease;
            white-space: nowrap;
        }
        .navbar .nav-link:hover {
            background: rgba(176, 124, 88, .10);
            color: rgba(23, 23, 23, .92);
        }
        .navbar .nav-link.active {
            background: rgba(176, 124, 88, .14);
            color: rgba(23, 23, 23, .95);
        }

        /* Improve toggler look */
        .navbar-toggler {
            border: 1px solid rgba(17, 17, 17, .14);
            border-radius: 14px;
            padding: .55rem .7rem;
            background: rgba(255, 255, 255, .65);
        }

        /* ✅ Desktop: keep navbar on one row (prevents “messy” wrap) */
        @media (min-width: 992px){
            .kt-nav .container { padding-top: 10px !important; padding-bottom: 10px !important; }
        }

        /* Sections */
        .section { padding: 78px 0; }
        .section-soft {
            background:
                radial-gradient(1200px 650px at 10% 0%, rgba(176, 124, 88, .10), transparent 58%),
                radial-gradient(900px 520px at 90% 20%, rgba(216, 193, 176, .16), transparent 55%),
                var(--soft);
        }
        .sec-title { font-weight: 950; letter-spacing: -0.04em; margin-bottom: 10px; }
        .sec-sub { color: var(--muted); font-weight: 650; line-height: 1.7; }

        /* Cards */
        .card-soft {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), #d2a07a);
            box-shadow: 0 16px 35px rgba(176, 124, 88, .22);
        }

        /* Tiles */
        .img-tile {
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .18);
            box-shadow: 0 18px 60px rgba(11, 18, 32, .14);
        }
        .img-tile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transform: scale(1.01);
        }

        /* Inputs */
        .kt-input {
            border-radius: 16px;
            border: 1px solid rgba(17, 17, 17, .14);
            padding: .85rem .95rem;
            font-weight: 650;
            box-shadow: none;
        }
        .kt-input:focus {
            border-color: rgba(176, 124, 88, .45);
            box-shadow: 0 0 0 .22rem rgba(176, 124, 88, .12);
        }

        /* Profile / Avatar */
        .kt-avatar-btn {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid rgba(17, 17, 17, .18);
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 16px 40px rgba(11, 18, 32, .10);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .kt-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            font-weight: 950;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), #d2a07a);
        }

        .kt-dropdown {
            border-radius: 18px;
            border: 1px solid rgba(17, 17, 17, .10);
            box-shadow: 0 26px 70px rgba(11, 18, 32, .18);
            padding: 8px;
            min-width: 240px;
        }
        .kt-dropdown .dropdown-item {
            border-radius: 14px;
            font-weight: 850;
            padding: 10px 12px;
        }
        .kt-dropdown .dropdown-item:active {
            background: rgba(176, 124, 88, .18);
            color: inherit;
        }

        /* Mobile UX pack */
        * { -webkit-tap-highlight-color: transparent; }

        @media (max-width: 768px) {
            .container { padding-left: 16px !important; padding-right: 16px !important; }
            .section { padding: 22px 0 !important; }

            .sec-title {
                font-size: clamp(28px, 7vw, 38px) !important;
                line-height: 1.12 !important;
            }

            .sec-sub { font-size: 14px !important; line-height: 1.6 !important; }

            /* prevent iOS zoom */
            input, select, textarea, .form-control, .form-select { font-size: 16px !important; }

            .btn, .kt-btn {
                min-height: 46px !important;
                padding-top: 12px !important;
                padding-bottom: 12px !important;
            }

            .card-soft { border-radius: 20px !important; }
            .img-tile { height: 220px !important; border-radius: 22px !important; }
            .img-tile img { border-radius: 22px !important; }

            .kt-mobile-stack { flex-direction: column !important; align-items: stretch !important; }
            .kt-mobile-stack > .btn,
            .kt-mobile-stack > .kt-btn { width: 100% !important; }
        }

        /* Generic sticky CTA */
        .kt-sticky-cta {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 10px 14px calc(10px + var(--kt-safe-bottom));
            background: linear-gradient(to top,
                rgba(255, 255, 255, .92),
                rgba(255, 255, 255, .55),
                rgba(255, 255, 255, 0)
            );
            backdrop-filter: blur(10px);
            z-index: 5000;
        }

        /* Footer (unchanged) */
        .kt-footer {
            position: relative;
            color: rgba(255, 255, 255, .90);
            background:
                radial-gradient(900px 500px at 15% 15%, rgba(176, 124, 88, .28), transparent 55%),
                radial-gradient(800px 480px at 85% 25%, rgba(216, 193, 176, .18), transparent 55%),
                linear-gradient(180deg, #1b1b1b 0%, #0f0f0f 100%);
            border-top: 1px solid rgba(255, 255, 255, .10);
            overflow: hidden;
        }

        .kt-footer-top { padding: 56px 0 36px; }
        .kt-footer-brand { text-decoration: none; }
        .kt-footer-logo {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: 0 16px 50px rgba(0, 0, 0, .25);
            background: #fff;
        }

        .kt-footer-name {
            font-weight: 950;
            letter-spacing: -0.02em;
            color: #fff;
            font-size: 1.15rem;
            line-height: 1.1;
        }
        .kt-footer-name small {
            font-weight: 700;
            color: rgba(255, 255, 255, .72);
            font-size: .85rem;
            margin-top: .2rem;
        }

        .kt-footer-desc {
            color: rgba(255, 255, 255, .72);
            line-height: 1.7;
            max-width: 46ch;
            font-weight: 600;
        }

        .kt-footer-title { font-weight: 950; color: #fff; margin-bottom: 14px; letter-spacing: -0.01em; }

        .kt-footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .kt-footer-links a {
            text-decoration: none;
            color: rgba(255, 255, 255, .78);
            font-weight: 650;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            transition: transform .15s ease, color .15s ease;
        }
        .kt-footer-links a:hover { color: rgba(255, 255, 255, .98); transform: translateX(2px); }

        .kt-social {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            text-decoration: none;
            color: rgba(255, 255, 255, .92);
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: 0 16px 40px rgba(0, 0, 0, .25);
            transition: transform .15s ease, background .15s ease;
        }
        .kt-social:hover { transform: translateY(-2px); background: rgba(255, 255, 255, .14); }

        .kt-footer-info { display: grid; gap: 12px; }
        .kt-info-item {
            display: flex;
            gap: 12px;
            padding: 12px 12px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .10);
        }
        .kt-info-ico {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(176, 124, 88, .18);
            border: 1px solid rgba(255, 255, 255, .10);
            color: #fff;
            flex: 0 0 auto;
        }
        .kt-info-main { font-weight: 900; color: rgba(255, 255, 255, .95); line-height: 1.1; }
        .kt-info-sub { color: rgba(255, 255, 255, .72); font-weight: 650; margin-top: 2px; }

        .kt-footer-hr { border-color: rgba(255, 255, 255, .10); opacity: 1; }
        .kt-footer-copy { color: rgba(255, 255, 255, .70); font-weight: 650; }

        .kt-footer-mini-links a {
            text-decoration: none;
            color: rgba(255, 255, 255, .70);
            font-weight: 700;
            transition: color .15s ease;
        }
        .kt-footer-mini-links a:hover { color: rgba(255, 255, 255, .98); }
    </style>

    @stack('styles')
</head>

<body>
@php
    $path = request()->path(); // "" for home
    $isActive = fn($p) => ($p === '/' ? $path === '' : str_starts_with($path, ltrim($p,'/')));
@endphp

<nav class="navbar navbar-expand-lg kt-nav">
    <div class="container py-2">
        <div class="d-flex flex-wrap flex-lg-nowrap align-items-center w-100">

            {{-- Hamburger (mobile left) --}}
            <button class="navbar-toggler order-0 me-2"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#ktNav"
                    aria-controls="ktNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Brand (center on mobile, left on desktop) --}}
            <a class="kt-brand order-1 mx-auto mx-lg-0" href="{{ url('/') }}">
                <img class="kt-logo" src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys & Tell logo">
                <div class="kt-brand-text">
                    <div class="name">Krys &amp; Tell</div>
                    <div class="sub">Dental Center</div>
                </div>
            </a>

            {{-- Actions (always visible, top-right on mobile) --}}
            <div class="d-flex align-items-center gap-2 order-2 ms-auto order-lg-3">

                @auth
                    <div class="dropdown">
                        <button class="kt-avatar-btn"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Open profile menu">
                            <div class="kt-avatar">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end kt-dropdown">
                            <li class="px-2 pt-2 pb-1">
                                <div style="font-weight:950;line-height:1.1;">{{ auth()->user()->name }}</div>
                                <div class="text-muted-2" style="font-size:.85rem;font-weight:650;">
                                    {{ auth()->user()->email }}
                                </div>
                            </li>

                            <li><hr class="dropdown-divider my-2"></li>

                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fa-regular fa-user me-2"></i> My Profile
                                </a>
                            </li>

                            @if(in_array(auth()->user()->role ?? '', ['admin','staff']))
                                <li>
                                    <a class="dropdown-item" href="{{ route('portal') }}">
                                        <i class="fa-solid fa-gauge me-2"></i> Portal
                                    </a>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider my-2"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="btn kt-btn kt-btn-outline d-inline-flex d-lg-none px-3"
                       href="{{ route('login') }}"
                       title="Sign in">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </a>
                    <a class="btn kt-btn kt-btn-outline d-none d-lg-inline-flex"
                       href="{{ route('login') }}">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign in
                    </a>
                @endauth
            </div>

            {{-- Collapsible links (no w-100 so desktop doesn't wrap) --}}
            <div id="ktNav" class="collapse navbar-collapse order-3 order-lg-2 mt-3 mt-lg-0">
                <ul class="navbar-nav ms-lg-auto gap-lg-1 align-items-lg-center">
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
                        <a class="kt-social" href="https://www.facebook.com/Ktelzaflats" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a class="kt-social" href="https://www.instagram.com/krysandtelldental/" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a class="kt-social" href="https://www.tiktok.com/@krysandtell2023" aria-label="Tiktok"><i class="fa-brands fa-tiktok"></i></a>
                        <a class="kt-social" href="https://www.messenger.com/t/346771123068701" aria-label="Messenger"><i class="fa-brands fa-facebook-messenger"></i></a>
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
                                <div class="kt-info-sub">
                                    C-T Bldg. Jose Romero Rd Bagacay Dumaguete City, Negros Oriental (Across Hypermart)
                                </div>
                            </div>
                        </div>

                        <div class="kt-info-item">
                            <span class="kt-info-ico"><i class="fa-solid fa-phone"></i></span>
                            <div>
                                <div class="kt-info-main">Phone</div>
                                <div class="kt-info-sub">+639772443595</div>
                            </div>
                        </div>

                        <div class="kt-info-item">
                            <span class="kt-info-ico"><i class="fa-solid fa-clock"></i></span>
                            <div>
                                <div class="kt-info-main">Clinic Hours</div>
                                <div class="kt-info-sub">Mon–Sat: 10:00 AM – 5:00 PM</div>
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
