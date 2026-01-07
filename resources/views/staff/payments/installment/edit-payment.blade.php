@extends('layouts.staff')

@section('title', 'Edit Installment Payment')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .10);
        --text: #0f172a;
        --muted: rgba(15, 23, 42, .58);
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
        color: var(--text);
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--muted);
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
        background: rgba(255,255,255,.88);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .88);
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
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .22);
        color:#fff;
    }

    .card-shell{
        background: rgba(255,255,255,.94);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        max-width: 980px;
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
        color: var(--muted);
        font-weight: 700;
    }
    .card-bodyx{ padding: 18px; }

    .grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    @media (min-width: 768px){
        .grid{ grid-template-columns: repeat(2, 1fr); }
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
        color: var(--text);
        word-break: break-word;
    }

    .error-box{
        max-width: 980px;
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .error-box .title{
        font-weight: 900;
        margin-bottom: 6px;
    }
    .error-box ul{
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
    }

    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
        margin-bottom: 6px;
    }
    .inputx, .selectx, .textareax{
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
    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }
    .readonlyx{
        background: rgba(248,250,252,.9) !important;
        color: rgba(15, 23, 42, .75) !important;
    }
    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }
</style>

@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: 'N/A';
    $serviceName = $plan->service?->name ?? '—';

    $payDate = $payment->payment_date ? Carbon::parse($payment->payment_date)->toDateString() : now()->toDateString();
@endphp

<div class="page-head" style="max-width:980px;">
    <div>
        <h2 class="page-title">Edit Installment Payment</h2>
        <p class="subtitle">Update the amount/method/date/notes for this month. Balance will auto-recompute.</p>
    </div>

    <a href="{{ $return ?? route('staff.installments.show', $plan->id) }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

@if ($errors->any())
    <div class="error-box">
        <div class="title"><i class="fa fa-triangle-exclamation"></i> Please fix the following:</div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-shell">
    <div class="card-head">
        <div class="hint">
            Plan #{{ $plan->id }} • Patient: <strong>{{ $patientName }}</strong> • Treatment: <strong>{{ $serviceName }}</strong>
        </div>
        <div class="hint">
            Editing Month <strong>{{ (int)($payment->month_number ?? 0) }}</strong>
            @if($payment->visit_id)
                • Visit #{{ $payment->visit_id }}
            @endif
        </div>
    </div>

    <div class="card-bodyx">

        <div class="grid">
            <div class="tile">
                <div class="k"><i class="fa fa-calendar"></i> Month</div>
                <div class="v">Month {{ (int)($payment->month_number ?? 0) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-peso-sign"></i> Remaining Balance (current)</div>
                <div class="v">₱{{ number_format((float)($plan->balance ?? 0), 2) }}</div>
            </div>
        </div>

        <form action="{{ route('staff.installments.payments.update', [$plan->id, $payment->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="return" value="{{ $return ?? '' }}">

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Month Number</label>
                    <input class="inputx readonlyx" value="{{ (int)($payment->month_number ?? 0) }}" readonly>
                    <div class="helper">Month number cannot be changed (keeps the plan schedule clean).</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="inputx"
                           value="{{ old('payment_date', $payDate) }}" required>
                    <div class="helper">If linked to a Visit, it will also update the Visit date.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" class="inputx"
                           value="{{ old('amount', (float)($payment->amount ?? 0)) }}" required>
                    <div class="helper">Keep it reasonable (system prevents exceeding total plan cost).</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Method <span class="text-danger">*</span></label>
                    @php $m = old('method', (string)($payment->method ?? 'Cash')); @endphp
                    <select name="method" class="selectx" required>
                        <option value="Cash" {{ $m==='Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ $m==='GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Card" {{ $m==='Card' ? 'selected' : '' }}>Card</option>
                        <option value="Bank Transfer" {{ $m==='Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" rows="3" class="textareax"
                        placeholder="e.g. Upper wire changed, recementation, adjustments...">{{ old('notes', (string)($payment->notes ?? '')) }}</textarea>
                    <div class="helper">If linked to a Visit, notes can be used to update the Visit notes too.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Save Changes
                    </button>

                    <a href="{{ $return ?? route('staff.installments.show', $plan->id) }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>

                    @if($payment->visit_id)
                        <a href="{{ route('staff.visits.show', [$payment->visit_id, 'return' => url()->full()]) }}" class="btn-ghostx">
                            <i class="fa fa-eye"></i> View Visit
                        </a>
                    @endif
                </div>

            </div>
        </form>

    </div>
</div>

@endsection
