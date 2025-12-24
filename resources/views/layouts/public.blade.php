<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Krys & Tell Dental Clinic')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <style>
        :root{
            --brand:#155AC1;
            --brand2:#7c3aed;
            --soft:#f6f8ff;
            --text:#0f172a;
        }
        body{ color:var(--text); background: #fff; }

        /* NAV */
        .kt-nav{
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,.86);
            border-bottom: 1px solid rgba(15,23,42,.08);
        }
        .kt-brand{
            font-weight:900;
            letter-spacing:-.5px;
        }
        .kt-btn{
            border-radius: 999px;
            font-weight: 800;
            padding: .7rem 1.05rem;
        }
        .kt-btn-primary{
            background: linear-gradient(135deg, var(--brand), #60a5fa);
            border: none;
        }
        .kt-btn-outline{
            border: 1px solid rgba(15,23,42,.16);
        }

        /* HERO */
        .hero{
            position:relative;
            min-height: 78vh;
            display:flex;
            align-items:center;
            overflow:hidden;
        }
        .hero::before{
            content:"";
            position:absolute; inset:0;
            background:
                radial-gradient(1200px 560px at 15% 10%, rgba(21,90,193,.55), transparent 60%),
                radial-gradient(900px 520px at 88% 30%, rgba(124,58,237,.35), transparent 55%),
                linear-gradient(180deg, rgba(0,0,0,.25), rgba(0,0,0,.55));
            z-index:1;
        }
        .hero-bg{
            position:absolute; inset:0;
            background-size: cover;
            background-position: center;
            transform: scale(1.02);
        }
        .hero-content{ position:relative; z-index:2; }
        .hero-card{
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 24px;
            padding: 22px;
            box-shadow: 0 30px 90px rgba(0,0,0,.35);
            backdrop-filter: blur(12px);
            color:#fff;
        }
        .hero-title{
            font-weight: 950;
            letter-spacing: -1px;
            line-height: 1.05;
            font-size: clamp(2rem, 4.2vw, 3.2rem);
            margin:0;
        }
        .hero-sub{
            opacity:.92;
            margin-top: 12px;
            font-weight: 700;
            max-width: 58ch;
        }
        .pill{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding: .55rem .85rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.08);
            font-weight: 800;
            font-size: .9rem;
        }

        /* SECTIONS */
        .section{ padding: 72px 0; }
        .section-soft{ background: var(--soft); }
        .sec-title{
            font-weight: 950;
            letter-spacing: -.6px;
            margin-bottom: 12px;
        }
        .sec-sub{ color: rgba(15,23,42,.72); font-weight:700; }

        .card-soft{
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 18px;
            box-shadow: 0 16px 50px rgba(15,23,42,.08);
            overflow:hidden;
        }
        .icon-badge{
            width:46px; height:46px;
            border-radius: 16px;
            display:grid; place-items:center;
            color:#fff;
            background: linear-gradient(135deg, var(--brand), #60a5fa);
            box-shadow: 0 16px 30px rgba(21,90,193,.25);
        }

        /* IMAGE TILES */
        .img-tile{
            border-radius: 18px;
            overflow:hidden;
            border: 1px solid rgba(15,23,42,.10);
            box-shadow: 0 18px 60px rgba(15,23,42,.10);
        }
        .img-tile img{ width:100%; height:100%; object-fit:cover; display:block; }

        /* FOOTER */
        .kt-footer{
            background: #0b1220;
            color: rgba(255,255,255,.86);
        }
        .kt-footer a{ color: rgba(255,255,255,.86); text-decoration:none; }
        .kt-footer a:hover{ text-decoration:underline; }

        /* small helpers */
        .text-muted-2{ color: rgba(15,23,42,.66); }
    </style>

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg kt-nav sticky-top">
    <div class="container">
        <a class="navbar-brand kt-brand" href="{{ url('/') }}">
            Krys &amp; Tell
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ktNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="ktNav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link fw-bold" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link fw-bold" href="{{ url('/about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link fw-bold" href="{{ url('/services') }}">Services</a></li>
                <li class="nav-item"><a class="nav-link fw-bold" href="{{ url('/contact') }}">Contact</a></li>
            </ul>

            <div class="d-flex gap-2 ms-lg-3 mt-3 mt-lg-0">
    @auth
        <a class="btn kt-btn kt-btn-outline" href="{{ route('portal') }}">
            <i class="fa-solid fa-gauge-high me-1"></i> Portal
        </a>

        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn kt-btn kt-btn-outline">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </button>
        </form>
    @endauth

    <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
        <i class="fa-solid fa-calendar-check me-1"></i> Book
    </a>
</div>

        </div>
    </div>
</nav>

@yield('content')

<footer class="kt-footer pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="fw-black" style="font-weight:950;font-size:1.2rem;">Krys &amp; Tell Dental Clinic</div>
                <div class="mt-2" style="opacity:.85;">
                    Gentle care, modern dentistry, and a clinic experience that feels calm and premium.
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="fw-bold mb-2">Pages</div>
                <div class="d-grid gap-1">
                    <a href="{{ url('/') }}">Home</a>
                    <a href="{{ url('/about') }}">About</a>
                    <a href="{{ url('/services') }}">Services</a>
                    <a href="{{ url('/contact') }}">Contact</a>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="fw-bold mb-2">Clinic Hours</div>
                <div style="opacity:.85;">
                    Mon–Sat: 9:00 AM – 6:00 PM<br>
                    Sun: Closed
                </div>
            </div>

            <div class="col-lg-3">
                <div class="fw-bold mb-2">Contact</div>
                <div style="opacity:.85;">
                    Phone: (add your number)<br>
                    Email: (add your email)<br>
                    Address: (add your address)
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,.12);">
        <div class="d-flex flex-wrap justify-content-between" style="opacity:.8;">
            <div>© {{ date('Y') }} Krys &amp; Tell. All rights reserved.</div>
            <div>Built with Laravel</div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
