@extends('layouts.staff')

@section('title', 'Edit Installment Payment')

@section('content')

<style>
    /* ==========================================================
       Installment Payment Edit (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);

        --text: var(--kt-text);
        --muted: var(--kt-muted);

        --soft: rgba(148,163,184,.14);
        --brand1:#0d6efd;
        --brand2:#1e90ff;

        --radius: 16px;
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
    }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        min-width:0;
        max-width: 980px;
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

    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .22);
    }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        max-width: 980px;
        min-width:0;
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid var(--kt-border);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
    }
    .card-bodyx{ padding: 18px; }

    /* Summary tiles */
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
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        background: rgba(148,163,184,.10);
        padding: 12px 12px;
        min-width:0;
    }
    html[data-theme="dark"] .tile{
        background: rgba(2,6,23,.30);
    }
    .tile .k{
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .tile .v{
        font-size: 14px;
        font-weight: 950;
        color: var(--text);
        word-break: break-word;
        font-variant-numeric: tabular-nums;
    }

    /* Error box */
    .error-box{
        max-width: 980px;
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    html[data-theme="dark"] .error-box{
        background: rgba(239, 68, 68, .12);
        color: rgba(254, 202, 202, .95);
        border-color: rgba(239, 68, 68, .25);
    }
    .error-box .title{
        font-weight: 950;
        margin-bottom: 6px;
    }
    .error-box ul{
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
        font-weight: 700;
    }

    /* Inputs */
    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        color: rgba(15, 23, 42, .80);
        margin-bottom: 6px;
    }
    html[data-theme="dark"] .form-labelx{
        color: rgba(226,232,240,.90);
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
    html[data-theme="dark"] .inputx,
    html[data-theme="dark"] .selectx,
    html[data-theme="dark"] .textareax{
        box-shadow: none;
    }

    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.14);
    }

    .readonlyx{
        background: rgba(148,163,184,.10) !important;
        color: var(--text) !important;
    }
    html[data-theme="dark"] .readonlyx{
        background: rgba(2,6,23,.35) !important;
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
        font-weight: 700;
    }

    /* Tiny live preview */
    .live{
        margin-top: 10px;
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        background: rgba(148,163,184,.10);
        padding: 12px 14px;
        display:flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items:center;
        justify-content:space-between;
    }
    html[data-theme="dark"] .live{
        background: rgba(2,6,23,.30);
    }
    .live .k{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .live .v{
        font-size: 14px;
        font-weight: 950;
        color: var(--text);
        font-variant-numeric: tabular-nums;
    }
</style>

@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: 'N/A';
    $serviceName = $plan->service?->name ?? '—';

    $payDate = $payment->payment_date ? Carbon::parse($payment->payment_date)->toDateString() : now()->toDateString();

    $planTotal = (float)($plan->total_cost ?? 0);
    $planBal   = (float)($plan->balance ?? 0);
@endphp

<div class="page-head">
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
            Editing Payment #
            <strong>{{ (int)($payment->month_number ?? 0) }}</strong>
            @if($payment->visit_id)
                • Visit #{{ $payment->visit_id }}
            @endif
        </div>
    </div>

    <div class="card-bodyx">

        <div class="grid">
            <div class="tile">
                <div class="k"><i class="fa fa-calendar"></i> Payment #</div>
                <div class="v">#{{ (int)($payment->month_number ?? 0) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-peso-sign"></i> Remaining Balance (current)</div>
                <div class="v">₱{{ number_format($planBal, 2) }}</div>
            </div>
        </div>

        <form action="{{ route('staff.installments.payments.update', [$plan->id, $payment->id]) }}" method="POST" id="editInstallmentPaymentForm">
            @csrf
            @method('PUT')

            <input type="hidden" name="return" value="{{ $return ?? '' }}">

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment #</label>
                    <input class="inputx readonlyx" value="{{ (int)($payment->month_number ?? 0) }}" readonly>
                    <div class="helper">Payment number cannot be changed (keeps numbering consistent).</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="inputx"
                           value="{{ old('payment_date', $payDate) }}" required>
                    <div class="helper">If linked to a Visit, it can also update the Visit date (based on your controller logic).</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="amount"
                        id="amountInput"
                        class="inputx"
                        value="{{ old('amount', (float)($payment->amount ?? 0)) }}"
                        required
                    >
                    <div class="helper">System should prevent exceeding the remaining balance / total cost.</div>
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
                    <textarea
                        name="notes"
                        rows="3"
                        class="textareax"
                        placeholder="e.g. Adjustments, materials used, special notes..."
                    >{{ old('notes', (string)($payment->notes ?? '')) }}</textarea>
                    <div class="helper">Optional internal notes for this installment payment.</div>
                </div>

                {{-- Live preview (client-side only) --}}
                <div class="col-12">
                    <div class="live" id="liveBox" style="display:none;">
                        <div>
                            <div class="k">New Remaining (preview)</div>
                            <div class="v" id="newRemainingText">₱0.00</div>
                        </div>
                        <div>
                            <div class="k">Plan Total</div>
                            <div class="v">₱{{ number_format($planTotal, 2) }}</div>
                        </div>
                    </div>
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

<script>
(function(){
    const amountInput = document.getElementById('amountInput');
    const liveBox = document.getElementById('liveBox');
    const newRemainingText = document.getElementById('newRemainingText');

    // These are server-rendered "current" values; the real recompute happens in controller.
    const currentBalance = Number({{ (float)($plan->balance ?? 0) }});
    const originalAmount = Number({{ (float)($payment->amount ?? 0) }});

    function money(v){
        const x = (isFinite(v) ? v : 0);
        return '₱' + x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updatePreview(){
        if (!amountInput || !liveBox || !newRemainingText) return;

        const newAmt = Number(amountInput.value || 0);

        // Preview math (approx):
        // If you change payment amount, remaining balance changes by (original - new).
        const delta = originalAmount - (isFinite(newAmt) ? newAmt : 0);
        const preview = Math.max(currentBalance + delta, 0);

        liveBox.style.display = '';
        newRemainingText.textContent = money(preview);
    }

    if (amountInput){
        amountInput.addEventListener('input', updatePreview);
        window.addEventListener('load', updatePreview);
    }
})();
</script>

@endsection
