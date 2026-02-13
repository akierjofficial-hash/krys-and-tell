@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Choose Plan (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);

        --text: var(--kt-text);
        --muted: var(--kt-muted);

        --brand1: #0d6efd;
        --brand2: #1e90ff;

        --radius: 18px;
        --soft: rgba(148,163,184,.14);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
    }

    /* Nice width on big screens */
    .max-wrap{ max-width: 1200px; }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        min-width:0;
    }
    .page-title{
        font-size: 26px;
        font-weight: 950;
        letter-spacing: -0.3px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    /* Back button */
    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid var(--kt-border);
        color: var(--text) !important;
        background: var(--kt-surface-2);
        transition: .15s ease;
        white-space: nowrap;
        user-select: none;
    }
    .btn-ghostx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btn-ghostx:hover{
        background: rgba(2,6,23,.35);
    }

    /* Wrapper */
    .choose-wrap{
        background:
            radial-gradient(circle at top left, rgba(13,110,253,.10), transparent 55%),
            radial-gradient(circle at bottom right, rgba(14,165,233,.10), transparent 55%),
            var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        padding: 18px;
        overflow: hidden;
        min-width:0;
    }
    html[data-theme="dark"] .choose-wrap{
        background:
            radial-gradient(circle at top left, rgba(13,110,253,.12), transparent 55%),
            radial-gradient(circle at bottom right, rgba(14,165,233,.10), transparent 55%),
            var(--kt-surface);
    }

    /* Plan cards */
    .plan-card{
        display:block;
        text-decoration: none;
        color: inherit;
        background: var(--kt-surface-2);
        border: 1px solid var(--kt-border);
        border-radius: 16px;
        padding: 18px;
        height: 100%;
        transition: .18s ease;
        position: relative;
        overflow: hidden;
        min-width:0;
    }
    .plan-card::after{
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(circle at top left, rgba(13,110,253,.16), transparent 55%);
        opacity: 0;
        transition: .18s ease;
        pointer-events:none;
    }
    .plan-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 14px 35px rgba(15, 23, 42, .10);
        border-color: rgba(13,110,253,.30);
    }
    html[data-theme="dark"] .plan-card:hover{
        box-shadow: 0 16px 40px rgba(0,0,0,.35);
        border-color: rgba(96,165,250,.35);
    }
    .plan-card:hover::after{ opacity: 1; }

    .plan-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        position: relative;
        z-index: 1;
        margin-bottom: 10px;
        min-width:0;
    }

    .icon-pill{
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        color:#fff;
        box-shadow: 0 10px 18px rgba(13,110,253,.20);
        flex: 0 0 auto;
    }
    html[data-theme="dark"] .icon-pill{
        box-shadow: 0 14px 26px rgba(0,0,0,.35);
    }

    .plan-name{
        font-weight: 950;
        font-size: 16px;
        margin: 0;
        color: var(--text);
        letter-spacing: -0.2px;
    }
    .plan-desc{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
        position: relative;
        z-index: 1;
    }

    .pill{
        font-size: 11px;
        font-weight: 950;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid var(--kt-border);
        color: rgba(15, 23, 42, .72);
        background: rgba(248,250,252,.85);
        white-space: nowrap;
        position: relative;
        z-index: 1;
    }
    html[data-theme="dark"] .pill{
        color: rgba(226,232,240,.88);
        background: rgba(2,6,23,.35);
    }

    .go{
        margin-top: 14px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        position: relative;
        z-index: 1;
    }

    .go span{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .80);
    }
    html[data-theme="dark"] .go span{
        color: rgba(226,232,240,.88);
    }

    .go i{
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display:grid;
        place-items:center;
        background: rgba(13,110,253,.10);
        color: #0d6efd;
        border: 1px solid rgba(13,110,253,.18);
        transition: .18s ease;
    }
    html[data-theme="dark"] .go i{
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.22);
        color: rgba(147,197,253,.95);
    }

    .plan-card:hover .go i{
        background: rgba(13,110,253,.16);
        transform: translateX(2px);
    }
</style>

<div class="page-head max-wrap">
    <div>
        <h2 class="page-title">Add New Payment</h2>
        <p class="subtitle">Choose a payment type to continue.</p>
    </div>

    <x-back-button
        fallback="{{ route('staff.payments.index') }}"
        class="btn-ghostx"
        label="Back to Payments"
    />
</div>


@php
    $ktReturn = request('return') ?? old('return') ?? session('kt.return_url') ?? route('staff.payments.index');
    $host = parse_url($ktReturn, PHP_URL_HOST);
    if ($host && $host !== request()->getHost()) {
        $ktReturn = route('staff.payments.index');
    }
@endphp

<div class="choose-wrap max-wrap">
    <div class="row g-3">

        {{-- CASH --}}
        <div class="col-12 col-md-6">
            <a href="{{ route('staff.payments.create.cash', ['return' => $ktReturn]) }}" class="plan-card">
                <div class="plan-top">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-pill" style="background:linear-gradient(135deg,#0d6efd,#1e90ff);">
                            <i class="fa fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <p class="plan-name mb-0">Cash Payment</p>
                            <div class="plan-desc">One-time payment for completed treatment(s).</div>
                        </div>
                    </div>
                    <span class="pill">Fast</span>
                </div>

                <div class="go">
                    <span>Continue with cash</span>
                    <i class="fa fa-arrow-right"></i>
                </div>
            </a>
        </div>

        {{-- INSTALLMENT --}}
        <div class="col-12 col-md-6">
            <a href="{{ route('staff.payments.create.installment', ['return' => $ktReturn]) }}" class="plan-card">
                <div class="plan-top">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-pill" style="background:linear-gradient(135deg,#7c3aed,#6f42c1); box-shadow:0 10px 18px rgba(124,58,237,.22);">
                            <i class="fa fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="plan-name mb-0">Installment Plan</p>
                            <div class="plan-desc">Downpayment + monthly payments until fully paid.</div>
                        </div>
                    </div>
                    <span class="pill">Flexible</span>
                </div>

                <div class="go">
                    <span>Continue with installment</span>
                    <i class="fa fa-arrow-right"></i>
                </div>
            </a>
        </div>

    </div>
</div>

@endsection
