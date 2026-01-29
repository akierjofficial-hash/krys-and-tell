@extends('layouts.public')
@section('title', 'Services — Krys&Tell')

@section('content')
<section class="section section-soft services-wrap">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-8">
                <div class="svc-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-briefcase-medical"></i>
                    <span>Services</span>
                </div>
                <h1 class="sec-title mt-3">Dental services</h1>
                <div class="sec-sub">
                    Choose a service and book a schedule that fits you — gentle care, clear plans, and a smile you’ll love.
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            @forelse($services as $service)
                {{-- 2 per row on md+ (iPad/desktop), 1 per row on mobile --}}
                <div class="col-12 col-md-6">
                    <div class="service-card h-100">
                        <div class="service-top">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="service-ico">
                                        <i class="fa-solid fa-tooth"></i>
                                    </div>

                                    <div>
                                        <div class="service-title">{{ $service->name }}</div>

                                        @if(!empty($service->duration_minutes))
                                            <div class="service-meta">
                                                <i class="fa-solid fa-clock me-1"></i> {{ $service->duration_minutes }} mins
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="svc-chip">
                                    <i class="fa-solid fa-sparkles me-1"></i> Comfort care
                                </div>
                            </div>
                        </div>

                        <div class="service-body">
                            <div class="service-desc">
                                {{ \Illuminate\Support\Str::limit($service->description ?? 'Professional dental care tailored for your comfort.', 140) }}
                            </div>
                        </div>

                        <div class="service-actions">
                            <a class="btn kt-btn kt-btn-outline flex-grow-1" href="{{ url('/services/' . $service->id) }}">
                                View details
                            </a>

                            {{-- ✅ FIXED: Book should go to USER login, not staff/admin login --}}
                            @php
                                $bookUrl = url('/book/' . $service->id);
                                $loginThenBackToBook = route('userlogin', ['redirect' => $bookUrl]);
                            @endphp

                            <a class="btn kt-btn kt-btn-primary text-white flex-grow-1"
                               href="{{ auth()->check() ? $bookUrl : $loginThenBackToBook }}">
                                <i class="fa-solid fa-calendar-check me-1"></i> Book
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card-soft p-4">
                        <div style="font-weight:950;">No services yet</div>
                        <div class="text-muted-2" style="font-weight:650;">Add services in the admin panel.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<style>
/* ==========================================================
   Services Index — Public (2 per row on iPad/desktop)
   ========================================================== */

.services-wrap{
    --ink: rgba(17,17,17,.92);
    --muted: rgba(17,17,17,.62);
    --border: rgba(17,17,17,.10);
    --shadow: 0 18px 55px rgba(11,18,32,.10);
}

/* Top pill */
.svc-pill{
    background: rgba(176,124,88,.10);
    border: 1px solid var(--border);
    font-weight: 900;
    color: var(--ink);
    box-shadow: 0 14px 35px rgba(11,18,32,.06);
}
.svc-pill i{ color: var(--brand); }

/* Card */
.service-card{
    border-radius: 26px;
    background: rgba(255,255,255,.95);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    overflow:hidden;
    display:flex;
    flex-direction:column;
    transition: transform .16s ease, box-shadow .16s ease;
}
.service-card:hover{
    transform: translateY(-3px);
    box-shadow: 0 26px 75px rgba(11,18,32,.12);
}

.service-top{
    padding: 18px 18px 12px;
    border-bottom: 1px solid rgba(17,17,17,.08);
    background:
        radial-gradient(700px 240px at 10% 0%, rgba(176,124,88,.12), transparent 60%),
        rgba(255,255,255,.90);
}

/* Icon */
.service-ico{
    width:54px; height:54px;
    border-radius: 18px;
    display:grid; place-items:center;
    background: linear-gradient(135deg, var(--brand), #d2a07a);
    color:#fff;
    box-shadow: 0 16px 40px rgba(176,124,88,.22);
    flex: 0 0 auto;
}

/* Title/meta */
.service-title{
    font-weight: 950;
    letter-spacing: -0.02em;
    font-size: 1.12rem;
    line-height: 1.15;
    margin: 0;
    color: var(--ink);
}
.service-meta{
    margin-top: 6px;
    color: var(--muted);
    font-weight: 750;
    font-size: .92rem;
}

/* Small chip (right side) */
.svc-chip{
    border-radius: 999px;
    padding: 8px 10px;
    background: rgba(255,255,255,.85);
    border: 1px solid rgba(17,17,17,.10);
    color: var(--muted);
    font-weight: 850;
    font-size: 12px;
    white-space: nowrap;
    box-shadow: 0 14px 35px rgba(11,18,32,.06);
}

/* Body */
.service-body{
    padding: 14px 18px 6px;
    flex: 1 1 auto;
}
.service-desc{
    color: var(--muted);
    font-weight: 650;
    line-height: 1.75;
}

/* Actions: big taps on mobile, neat on desktop */
.service-actions{
    padding: 14px 18px 18px;
    display:flex;
    gap: .65rem;
}
.service-actions .btn{
    border-radius: 14px;
    padding: 11px 12px;
    font-weight: 850;
}

/* Mobile fine-tuning */
@media (max-width: 575.98px){
    .service-top{ padding: 16px 14px 10px; }
    .service-body{ padding: 12px 14px 4px; }
    .service-actions{ padding: 12px 14px 14px; flex-direction: column; }
    .svc-chip{ display:none; }
    .service-ico{ width:50px; height:50px; border-radius: 16px; }
    .service-title{ font-size: 1.08rem; }
}
</style>
@endsection
