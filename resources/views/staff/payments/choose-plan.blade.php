@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
    }

    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15, 23, 42, .12);
        color: rgba(15, 23, 42, .75);
        background: rgba(255,255,255,.85);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .85);
    }

    /* Wrapper */
    .choose-wrap{
        background: radial-gradient(circle at top left, rgba(13,110,253,.10), transparent 55%),
                    radial-gradient(circle at bottom right, rgba(14,165,233,.10), transparent 55%),
                    rgba(255,255,255,.35);
        border: var(--card-border);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        padding: 18px;
        overflow: hidden;
    }

    /* Plan cards */
    .plan-card{
        display:block;
        text-decoration: none;
        color: inherit;
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        padding: 18px;
        height: 100%;
        transition: .18s ease;
        position: relative;
        overflow: hidden;
    }
    .plan-card::after{
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(circle at top left, rgba(13,110,253,.16), transparent 55%);
        opacity: 0;
        transition: .18s ease;
    }
    .plan-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 14px 35px rgba(15, 23, 42, .10);
        border-color: rgba(13,110,253,.22);
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

    .plan-name{
        font-weight: 900;
        font-size: 16px;
        margin: 0;
        color:#0f172a;
        letter-spacing: -0.2px;
    }
    .plan-desc{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
        position: relative;
        z-index: 1;
    }

    .pill{
        font-size: 11px;
        font-weight: 900;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, .10);
        color: rgba(15, 23, 42, .70);
        background: rgba(248,250,252,.9);
        white-space: nowrap;
        position: relative;
        z-index: 1;
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
        font-weight: 800;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
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

    .plan-card:hover .go i{
        background: rgba(13,110,253,.16);
        transform: translateX(2px);
    }

    /* Nice width on big screens */
    .max-wrap{ max-width: 1200px; }
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

<div class="choose-wrap max-wrap">
    <div class="row g-3">

        {{-- CASH --}}
        <div class="col-12 col-md-6">
            <a href="{{ route('staff.payments.create.cash', ['return' => url()->full()]) }}" class="plan-card">
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
            <a href="{{ route('staff.payments.create.installment', ['return' => url()->full()]) }}" class="plan-card">
                <div class="plan-top">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-pill" style="background:linear-gradient(135deg,#7c3aed,#6f42c1);">
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
