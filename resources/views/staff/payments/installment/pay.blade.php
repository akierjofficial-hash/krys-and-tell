@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Installment Pay (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);

        --text: var(--kt-text);
        --muted: var(--kt-muted);

        --soft: rgba(148,163,184,.14);
        --radius: 16px;
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
    }

    .form-max{ max-width: 1100px; min-width: 0; }

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
        background: linear-gradient(135deg, #16a34a, #22c55e);
        box-shadow: 0 10px 18px rgba(34,197,94,.18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(34,197,94,.24);
    }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
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
        min-width:0;
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
        min-width:0;
    }
    .card-bodyx{ padding: 18px; }

    /* Summary tiles */
    .summary-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 14px;
        min-width:0;
    }
    @media (min-width: 768px){
        .summary-grid{ grid-template-columns: repeat(2, 1fr); }
    }

    .tile{
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        background: var(--kt-surface-2);
        padding: 12px 12px;
        min-width:0;
    }
    .tile .k{
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.55);
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    html[data-theme="dark"] .tile .k{ color: rgba(226,232,240,.62); }

    .tile .v{
        font-size: 14px;
        font-weight: 950;
        color: var(--text);
        word-break: break-word;
    }
    .balance{
        color: #dc2626;
        font-weight: 1000;
    }
    html[data-theme="dark"] .balance{
        color: rgba(248,113,113,.95);
    }

    /* Inputs */
    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        color: rgba(15, 23, 42, .82);
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

    .readonlyx{
        background: rgba(148,163,184,.10) !important;
        color: var(--text) !important;
    }
    html[data-theme="dark"] .readonlyx{
        background: rgba(2,6,23,.35) !important;
    }

    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.14);
        background: var(--kt-surface-2);
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
        font-weight: 700;
    }

    /* Badges */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(59,130,246,.22);
        background: rgba(59,130,246,.10);
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .badge-soft{
        background: rgba(59,130,246,.14);
        border-color: rgba(59,130,246,.25);
        color: rgba(226,232,240,.92);
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
</style>

@php
    use Carbon\Carbon;

    $isOpenContract = (bool)($plan->is_open_contract ?? false);

    $payments = $plan->payments ?? collect();
    $paymentsTotal = (float) $payments->sum('amount');

    $total = (float) ($plan->total_cost ?? 0);
    $down  = (float) ($plan->downpayment ?? 0);

    $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
    $planStartStr = $startDate ? $startDate->toDateString() : null;

    $dpPayment = $payments->first(function ($p) use ($down, $planStartStr) {
        $m = (int)($p->month_number ?? -1);
        $notes = strtolower((string)($p->notes ?? ''));

        if ($m === 0) return true;

        if ($m === 1) {
            if (str_contains($notes, 'downpayment')) return true;

            $amt = (float)($p->amount ?? 0);
            $pd  = $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->toDateString() : null;

            if ($down > 0 && abs($amt - $down) < 0.01 && $planStartStr && $pd === $planStartStr) {
                return true;
            }
        }

        return false;
    });

    $hasMonth0 = $payments->contains(fn($p) => (int)($p->month_number ?? -1) === 0);
    $dpIsLegacyMonth1 = (!$hasMonth0 && $dpPayment && (int)($dpPayment->month_number ?? -1) === 1);

    $shift = $dpIsLegacyMonth1 ? 1 : 0;

    $hasDpRecord = (bool) $dpPayment;
    $totalPaid = $paymentsTotal + ($hasDpRecord ? 0 : $down);

    $remainingBalance = max(0, $total - $totalPaid);

    // ✅ clean numeric for HTML attributes (no commas)
    $remainingBalanceAttr = number_format($remainingBalance, 2, '.', '');

    // ✅ selected month safe-guard (fixed-term)
    $selectedMonth = (int) old('month_number', $nextMonth ?? 1);
@endphp

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Installment Payment</h2>
        <p class="subtitle">Record a payment for this installment plan.</p>
    </div>

    <x-back-button
        fallback="{{ route('staff.installments.show', $plan) }}"
        class="btn-ghostx"
        label="Back to Plan"
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
        <div class="hint">
            <span class="badge-soft"><i class="fa fa-file-invoice"></i> Plan ID: #{{ $plan->id }}</span>

            @if($isOpenContract)
                <span class="badge-soft"><i class="fa fa-infinity"></i> Term: Open Contract</span>
            @else
                <span class="badge-soft"><i class="fa fa-calendar"></i> Term: {{ (int)($plan->months ?? 0) }} months</span>
            @endif
        </div>
        <div class="hint">
            Make sure the amount does not exceed the remaining balance. A <strong>Visit</strong> entry will be auto-created for this payment.
        </div>
    </div>

    <div class="card-bodyx">

        <div class="summary-grid">
            <div class="tile">
                <div class="k"><i class="fa fa-user"></i> Patient Name</div>
                <div class="v">{{ $plan->patient?->first_name }} {{ $plan->patient?->last_name }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-tooth"></i> Service / Treatment</div>
                <div class="v">{{ $plan->service?->name ?? '—' }}</div>
                <div class="helper">Use <strong>Notes</strong> for braces details (upper/lower wire, recementation, adjustments, etc.).</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-peso-sign"></i> Total Treatment Cost</div>
                <div class="v">₱{{ number_format((float)($plan->total_cost ?? 0), 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-arrow-down"></i> Downpayment</div>
                <div class="v">₱{{ number_format((float)($plan->downpayment ?? 0), 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-circle-exclamation"></i> Remaining Balance</div>
                <div class="v balance">₱{{ number_format($remainingBalance, 2) }}</div>
            </div>
        </div>

        <form action="{{ route('staff.installments.pay.store', $plan) }}" method="POST">
            @csrf

            <div class="row g-3">

                {{-- Month / Payment # --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">{{ $isOpenContract ? 'Payment #' : 'Month Paid' }} <span class="text-danger">*</span></label>

                    @if($isOpenContract)
                        @php $val = (int)old('month_number', $nextMonth ?? 1); @endphp
                        <input type="hidden" name="month_number" value="{{ $val }}">
                        <input class="inputx readonlyx" value="Payment #{{ $val }}" readonly>
                        <div class="helper">Open contract: payments are auto-numbered (server will enforce next number).</div>
                    @else
                        @php
                            $maxMonthsLocal = $maxMonths ?? max(0, (int)($plan->months ?? 0));

                            // ✅ If old selected month is already paid AND disabled, force select nextMonth
                            if (isset($paidMonths) && $paidMonths->contains($selectedMonth)) {
                                $selectedMonth = (int) ($nextMonth ?? 1);
                            }
                        @endphp

                        <select name="month_number" class="selectx" required>
                            @for($i = 1; $i <= $maxMonthsLocal; $i++)
                                @php
                                    $isAlreadyPaid = isset($paidMonths) && $paidMonths->contains($i);
                                    $shouldDisable = $isAlreadyPaid;
                                    $shouldSelect  = ($selectedMonth === $i) && !$shouldDisable;
                                @endphp

                                <option value="{{ $i }}"
                                    {{ $shouldDisable ? 'disabled' : '' }}
                                    {{ $shouldSelect ? 'selected' : '' }}
                                >
                                    Month {{ $i }}{{ $isAlreadyPaid ? ' (paid)' : '' }}
                                </option>
                            @endfor
                        </select>
                        <div class="helper">Only unpaid months can be selected.</div>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount Paid <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        name="amount"
                        class="inputx"
                        step="0.01"
                        min="0"
                        max="{{ $remainingBalanceAttr }}"
                        value="{{ old('amount') }}"
                        required
                    >
                    <div class="helper">Tip: keep it ≤ remaining balance.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="inputx" value="{{ old('payment_date', now()->toDateString()) }}" required>
                    <div class="helper">This will also be used as the Visit date.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Method <span class="text-danger">*</span></label>
                    <select name="method" class="selectx" required>
                        @php $m = old('method', 'Cash'); @endphp
                        <option value="Cash" {{ $m === 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ $m === 'GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Card" {{ $m === 'Card' ? 'selected' : '' }}>Card</option>
                        <option value="Bank Transfer" {{ $m === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-labelx">Treatment Notes / Visit Notes</label>
                    <textarea name="notes" rows="3" class="textareax"
                        placeholder="e.g. Upper wire changed, recementation on #11, patient complains of soreness...">{{ old('notes') }}</textarea>
                    <div class="helper">Shown in Visits and linked to this installment payment.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Record Payment
                    </button>

                    <a href="{{ route('staff.payments.index', ['tab' => 'installment']) }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>

    </div>
</div>

@endsection
