@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

    /* Header */
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

    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        border: none;
        color: #fff;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        box-shadow: 0 10px 18px rgba(34,197,94,.18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(34,197,94,.24);
        color:#fff;
    }

    /* Card */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }
    .card-bodyx{ padding: 18px; }

    /* Summary tiles */
    .summary-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    @media (min-width: 768px){
        .summary-grid{ grid-template-columns: repeat(2, 1fr); }
    }

    .tile{
        border: 1px solid rgba(15,23,42,.10);
        border-radius: 14px;
        background: rgba(248,250,252,.9);
        padding: 12px 12px;
    }
    .tile .k{
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.58);
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .tile .v{
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
        word-break: break-word;
    }
    .balance{
        color: #dc2626;
        font-weight: 1000;
    }

    /* Inputs */
    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
        margin-bottom: 6px;
    }
    .inputx, .selectx{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: #0f172a;
        background: rgba(255,255,255,.95);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .inputx:focus, .selectx:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }
    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    /* Tiny badges */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(59,130,246,.22);
        background: rgba(59,130,246,.10);
        color: rgba(15,23,42,.85);
        white-space: nowrap;
    }

    .form-max{ max-width: 1100px; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Installment Payment</h2>
        <p class="subtitle">Record a payment for this installment plan.</p>
    </div>

    <a href="{{ route('staff.installments.show', $plan) }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back to Plan
    </a>
</div>

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">
            <span class="badge-soft"><i class="fa fa-file-invoice"></i> Plan ID: #{{ $plan->id }}</span>
            <span class="badge-soft"><i class="fa fa-calendar"></i> Term: {{ $plan->months }} months</span>
        </div>
        <div class="hint">Make sure the amount does not exceed the remaining balance.</div>
    </div>

    <div class="card-bodyx">

        {{-- Plan Info --}}
        <div class="summary-grid">
            <div class="tile">
                <div class="k"><i class="fa fa-user"></i> Patient Name</div>
                <div class="v">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-peso-sign"></i> Total Treatment Cost</div>
                <div class="v">₱{{ number_format($plan->total_cost, 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-arrow-down"></i> Downpayment</div>
                <div class="v">₱{{ number_format($plan->downpayment, 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-circle-exclamation"></i> Remaining Balance</div>
                <div class="v balance">₱{{ number_format($plan->balance, 2) }}</div>
            </div>
        </div>

        {{-- Payment Form --}}
        <form action="{{ route('staff.installments.pay.store', $plan) }}" method="POST">
            @csrf

            <div class="row g-3">

                {{-- Month --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Month Paid <span class="text-danger">*</span></label>
                    <select name="month_number" class="selectx" required>
                        @for($i = 1; $i <= $plan->months; $i++)
                            <option value="{{ $i }}">Month {{ $i }}</option>
                        @endfor
                    </select>
                    <div class="helper">Choose the installment month you’re recording.</div>
                </div>

                {{-- Amount --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount Paid <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="inputx" required>
                    <div class="helper">Tip: keep it ≤ remaining balance.</div>
                </div>

                {{-- Method --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Method <span class="text-danger">*</span></label>
                    <select name="method" class="selectx" required>
                        <option>Cash</option>
                        <option>GCash</option>
                        <option>Card</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Record Payment
                    </button>

                    <a href="{{ route('staff.payments.index', $plan) }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>

    </div>
</div>

@endsection
