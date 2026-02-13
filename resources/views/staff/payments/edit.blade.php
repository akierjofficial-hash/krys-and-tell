@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Payments Edit (Dark mode compatible)
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

        --radius: 16px;
        --soft: rgba(148,163,184,.14);
        --soft2: rgba(148,163,184,.18);

        --danger: #ef4444;
        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
        --soft2: rgba(148,163,184,.20);
    }

    .form-max{ max-width: 1100px; }

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

    /* Buttons */
    .btn-ghostx,
    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
        user-select: none;
    }

    .btn-ghostx{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--text) !important;
    }
    .btn-ghostx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btn-ghostx:hover{
        background: rgba(2,6,23,.35);
    }

    .btn-primaryx{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
    }

    /* Error box */
    .error-box{
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    html[data-theme="dark"] .error-box{
        background: rgba(239, 68, 68, .12);
        color: rgba(254,202,202,.95);
        border-color: rgba(239,68,68,.25);
    }
    .error-box .title{
        font-weight: 950;
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .error-box ul{
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
    }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        backdrop-filter: blur(8px);
        min-width:0;
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
    }
    .card-bodyx{ padding: 18px; }

    /* Inputs */
    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        color: var(--text);
        opacity: .85;
        margin-bottom: 6px;
    }

    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid var(--kt-border);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: var(--text);
        background: var(--kt-surface-2);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
    }

    .money{ font-variant-numeric: tabular-nums; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Edit Payment</h2>
        <p class="subtitle">Update the details of this payment below.</p>
    </div>

    <x-back-button
        fallback="{{ route('staff.payments.index') }}"
        class="btn-ghostx"
        label="Back to Payments"
    />
</div>

@if ($errors->any())
    <div class="error-box form-max">
        <div class="title"><i class="fa fa-triangle-exclamation"></i> Please fix the following:</div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">Payment ID: <strong>#{{ $payment->id }}</strong></div>
        <div class="hint">Edit carefully before saving</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.payments.update', $payment) }}" method="POST">
            @csrf
            <input type="hidden" name="return" value="{{ old('return', request('return', session('kt.return_url', request()->fullUrl()))) }}">
            @method('PUT')

            <div class="row g-3">

                <!-- Visit Selection -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Visit <span class="text-danger">*</span></label>
                    <select name="visit_id" class="selectx" required>
                        @foreach($visits as $visit)
                            <option value="{{ $visit->id }}" {{ old('visit_id', $payment->visit_id) == $visit->id ? 'selected' : '' }}>
                                {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}
                                - {{ \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Select the visit this payment belongs to.</div>
                </div>

                <!-- Amount -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="amount"
                        value="{{ old('amount', $payment->amount) }}"
                        class="inputx money"
                        required
                    >
                    <div class="helper">Use decimals if needed (e.g., 1500.00).</div>
                </div>

                <!-- Method -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Method <span class="text-danger">*</span></label>
                    <select name="method" class="selectx" required>
                        @php $m = old('method', $payment->method); @endphp
                        <option value="Cash" {{ $m == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ $m == 'GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Card" {{ $m == 'Card' ? 'selected' : '' }}>Card</option>
                        <option value="Bank Transfer" {{ $m == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    <div class="helper">Choose how the patient paid.</div>
                </div>

                <!-- Payment Date -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        name="payment_date"
                        value="{{ old('payment_date', $payment->payment_date) }}"
                        class="inputx"
                        required
                    >
                    <div class="helper">Date the payment was received.</div>
                </div>

                <!-- Notes -->
                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" rows="3" class="textareax" placeholder="Optional internal notes...">{{ old('notes', $payment->notes) }}</textarea>
                    <div class="helper">Optional: add internal notes about this payment.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Payment
                    </button>

                    <a href="{{ route('staff.payments.index') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
