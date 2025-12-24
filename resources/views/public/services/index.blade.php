@extends('layouts.public')
@section('title', 'Services — Krys & Tell')

@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="row align-items-end g-3">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill"
                     style="background:rgba(176,124,88,.10); border:1px solid rgba(17,17,17,.10); font-weight:850;">
                    <i class="fa-solid fa-briefcase-medical" style="color:var(--brand)"></i>
                    <span>Services</span>
                </div>
                <h1 class="sec-title mt-3">Dental services</h1>
                <div class="sec-sub">Choose a service and book a schedule that fits you.</div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            @forelse($services as $service)
                <div class="col-md-6 col-lg-4">
                    <div class="service-card h-100">
                        <div class="service-top">
                            <div class="service-ico">
                                <i class="fa-solid fa-tooth"></i>
                            </div>
                            <div class="service-title">{{ $service->name }}</div>
                            @if(!empty($service->duration_minutes))
                                <div class="service-meta">
                                    <i class="fa-solid fa-clock me-1"></i> {{ $service->duration_minutes }} mins
                                </div>
                            @endif
                        </div>

                        <div class="service-body">
                            <div class="text-muted-2" style="font-weight:650; line-height:1.7;">
                                {{ \Illuminate\Support\Str::limit($service->description ?? 'Professional dental care tailored for your comfort.', 110) }}
                            </div>
                        </div>

                        <div class="service-actions">
                            <a class="btn kt-btn kt-btn-outline" href="{{ url('/services/' . $service->id) }}">
                                View details
                            </a>
                            <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/book/' . $service->id) }}">
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
    .service-card{
        border-radius: var(--radius);
        background: rgba(255,255,255,.95);
        border: 1px solid rgba(17,17,17,.10);
        box-shadow: var(--shadow);
        overflow:hidden;
        display:flex;
        flex-direction:column;
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .service-card:hover{
        transform: translateY(-3px);
        box-shadow: 0 26px 75px rgba(11,18,32,.10);
    }
    .service-top{
        padding: 18px 18px 10px;
        border-bottom: 1px solid rgba(17,17,17,.08);
        background:
            radial-gradient(700px 240px at 10% 0%, rgba(176,124,88,.12), transparent 60%),
            rgba(255,255,255,.90);
    }
    .service-ico{
        width:52px;height:52px;border-radius: 18px;
        display:grid;place-items:center;
        background: linear-gradient(135deg, var(--brand), #d2a07a);
        color:#fff;
        box-shadow: 0 16px 40px rgba(176,124,88,.22);
        margin-bottom: 10px;
    }
    .service-title{
        font-weight: 950;
        letter-spacing: -0.02em;
        font-size: 1.1rem;
        margin: 0;
    }
    .service-meta{
        margin-top: 6px;
        color: rgba(23,23,23,.62);
        font-weight: 700;
        font-size: .9rem;
    }
    .service-body{ padding: 14px 18px 4px; flex: 1 1 auto; }
    .service-actions{
        padding: 14px 18px 18px;
        display:flex;
        gap: .6rem;
        justify-content: space-between;
        flex-wrap: wrap;
    }
</style>
@endsection
