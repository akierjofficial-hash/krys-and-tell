@extends('layouts.app')

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
        border: 1px solid rgba(15, 23, 42, .12);
        background: rgba(255,255,255,.85);
        color: rgba(15, 23, 42, .75);
        text-decoration: none;
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
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13,110,253,.18);
        text-decoration: none;
    }

    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-bodyx{ padding: 18px; }

    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 6px;
        color: rgba(15, 23, 42, .75);
    }

    .inputx{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        background: rgba(255,255,255,.95);
    }

    .readonlyx{
        background: rgba(248,250,252,.9);
        color: rgba(15, 23, 42, .75);
    }

    .helper{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
        margin-top: 6px;
    }

    .tag{
        display:inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(15,23,42,.06);
        border: 1px solid rgba(15,23,42,.10);
        margin: 4px 4px 0 0;
    }

    .form-max{ max-width: 1100px; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Edit Installment Plan</h2>
        <p class="subtitle">Update the editable details of this installment plan.</p>
    </div>

    <a href="{{ route('payments.index', ['tab' => 'installment']) }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">
            Plan ID: <strong>#{{ $plan->id }}</strong>
        </div>
        <div class="hint">
            Balance & status are read-only
        </div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('installments.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Patient --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Patient</label>
                    <input class="inputx readonlyx" value="{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}" readonly>
                </div>

                {{-- Treatments (from VISIT PROCEDURES) --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Treatments</label>

                    @if($plan->visit && $plan->visit->procedures->count())
                        <div>
                            @foreach($plan->visit->procedures as $proc)
                                <span class="tag">{{ $proc->service->name ?? 'Service' }}</span>
                            @endforeach
                        </div>
                    @elseif($plan->service)
                        <span class="tag">{{ $plan->service->name }}</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>

                {{-- Total Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Total Cost</label>
                    <input type="number" name="total_cost" class="inputx"
                           value="{{ old('total_cost', $plan->total_cost) }}" required>
                </div>

                {{-- Downpayment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Downpayment</label>
                    <input type="number" name="downpayment" class="inputx"
                           value="{{ old('downpayment', $plan->downpayment) }}" required>
                </div>

                {{-- Months --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Term (Months)</label>
                    <input type="number" name="months" class="inputx"
                           value="{{ old('months', $plan->months) }}" min="1" required>
                </div>

                {{-- Start Date --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Start Date</label>
                    <input type="date" name="start_date" class="inputx"
                           value="{{ old('start_date', $plan->start_date) }}" required>
                </div>

                {{-- Balance --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Remaining Balance</label>
                    <input class="inputx readonlyx" value="{{ $plan->balance }}" readonly>
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Status</label>
                    <input class="inputx readonlyx" value="{{ $plan->status }}" readonly>
                </div>

                <div class="col-12 pt-2 d-flex gap-2">
                    <button class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Plan
                    </button>

                    <a href="{{ route('payments.index', ['tab' => 'installment']) }}" class="btn-ghostx">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
