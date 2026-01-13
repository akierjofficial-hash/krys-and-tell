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
    $remainingBalanceAttr = number_format($remainingBalance, 2, '.', '');

    // ✅ selected month safe-guard (fixed-term)
    $selectedMonth = (int) old('month_number', $nextMonth ?? 1);

    // ✅ Monthly suggested amount (fixed-term): (Total - Downpayment) / Months
    $monthsTerm = max(0, (int)($plan->months ?? 0));
    $principal = max(0, $total - $down);
    $monthlySuggested = ($monthsTerm > 0) ? round($principal / $monthsTerm, 2) : 0.00;

    $computedDefault = $isOpenContract
        ? $remainingBalance
        : min($remainingBalance, ($monthlySuggested > 0 ? $monthlySuggested : $remainingBalance));

    $computedDefaultAttr = number_format($computedDefault, 2, '.', '');
    $monthlySuggestedAttr = number_format($monthlySuggested, 2, '.', '');

    // ✅ DP date (base month). Month 1 due date = DP date + 1 month.
    $dpDateObj = $dpPayment?->payment_date
        ? Carbon::parse($dpPayment->payment_date)
        : ($startDate ? $startDate->copy() : now());

    $dpDateStr = $dpDateObj->toDateString();

    // ✅ Map UI month => paid date (YYYY-MM-DD) for fixed-term (uses shift)
    $uiPaidDates = [];
    if (!$isOpenContract) {
        for ($i = 1; $i <= $monthsTerm; $i++) {
            $dbMonth = $i + $shift;
            $p = $payments->first(function($x) use ($dbMonth){
                return (int)($x->month_number ?? -999) === (int)$dbMonth;
            });

            if ($p && $p->payment_date) {
                $uiPaidDates[$i] = Carbon::parse($p->payment_date)->toDateString();
            }
        }
    }

    // ✅ Suggested date for initial render:
    // - fixed-term: month i uses (prev paid month date +1) else (dp date + i months)
    // - open: last paid date +1 month
    $suggestedDate = now()->toDateString();

    if ($isOpenContract) {
        $lastNonDp = $payments
            ->filter(fn($p) => (int)($p->month_number ?? -1) >= (1 + $shift))
            ->sortByDesc(fn($p) => (int)($p->month_number ?? 0))
            ->first();

        $lastDateObj = $lastNonDp?->payment_date
            ? Carbon::parse($lastNonDp->payment_date)
            : $dpDateObj->copy();

        $suggestedDate = $lastDateObj->copy()->addMonth()->toDateString();
    } else {
        $sel = max(1, $selectedMonth);
        $prevDate = null;

        for ($j = $sel - 1; $j >= 1; $j--) {
            if (!empty($uiPaidDates[$j])) { $prevDate = $uiPaidDates[$j]; break; }
        }

        if ($prevDate) {
            $suggestedDate = Carbon::parse($prevDate)->addMonth()->toDateString();
        } else {
            $suggestedDate = $dpDateObj->copy()->addMonths($sel)->toDateString(); // Month 1 = dp +1 month
        }
    }
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

<div class="card-shell form-max" id="installmentPayShell"
     data-open="{{ $isOpenContract ? '1' : '0' }}"
     data-dp-date="{{ $dpDateStr }}"
     data-paid-dates='@json($uiPaidDates)'
>
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

            @if(!$isOpenContract && (int)($plan->months ?? 0) > 0)
                <div class="tile">
                    <div class="k"><i class="fa fa-calculator"></i> Suggested Monthly Payment</div>
                    <div class="v">₱{{ number_format($monthlySuggested, 2) }}</div>
                    <div class="helper">(Total − Downpayment) ÷ Months. You can still edit the amount.</div>
                </div>
            @endif
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

                            if (isset($paidMonths) && $paidMonths->contains($selectedMonth)) {
                                $selectedMonth = (int) ($nextMonth ?? 1);
                            }
                        @endphp

                        <select id="monthSelect" name="month_number" class="selectx" required>
                            @for($i = 1; $i <= $maxMonthsLocal; $i++)
                                @php
                                    $isAlreadyPaid = isset($paidMonths) && $paidMonths->contains($i);
                                    $shouldDisable = $isAlreadyPaid;
                                    $shouldSelect  = ((int)$selectedMonth === $i) && !$shouldDisable;
                                @endphp

                                <option value="{{ $i }}"
                                    {{ $shouldDisable ? 'disabled' : '' }}
                                    {{ $shouldSelect ? 'selected' : '' }}
                                >
                                    Month {{ $i }}{{ $isAlreadyPaid ? ' (paid)' : '' }}
                                </option>
                            @endfor
                        </select>
                        <div class="helper">Only unpaid months can be selected. Date auto-advances from DP / previous paid month.</div>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount Paid <span class="text-danger">*</span></label>
                    <input
                        id="amountPaid"
                        type="number"
                        name="amount"
                        class="inputx"
                        step="0.01"
                        min="0"
                        max="{{ $remainingBalanceAttr }}"
                        value="{{ old('amount', $computedDefaultAttr) }}"
                        data-suggested="{{ $isOpenContract ? $computedDefaultAttr : $monthlySuggestedAttr }}"
                        required
                    >
                    @if(!$isOpenContract && (int)($plan->months ?? 0) > 0)
                        <div class="helper">Auto-filled with suggested monthly payment. Edit if the patient pays more.</div>
                    @else
                        <div class="helper">You can edit this amount anytime.</div>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input
                        id="paymentDate"
                        type="date"
                        name="payment_date"
                        class="inputx"
                        value="{{ old('payment_date', $suggestedDate) }}"
                        required
                    >
                    <div class="helper">
                        Fixed-term: Month 1 = DP month + 1 month. Next months follow the previous paid month date + 1 month.
                    </div>
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

<script>
(() => {
    const shell = document.getElementById('installmentPayShell');
    const monthSelect = document.getElementById('monthSelect');
    const dateInput = document.getElementById('paymentDate');

    if (!shell || !dateInput) return;

    const isOpen = shell.dataset.open === '1';

    function addMonthsSafe(yyyy_mm_dd, monthsToAdd){
        if (!yyyy_mm_dd) return '';

        const parts = yyyy_mm_dd.split('-').map(Number);
        if (parts.length !== 3) return '';

        const y = parts[0];
        const m = parts[1] - 1;
        const d = parts[2];

        let totalMonths = (m + monthsToAdd);
        let year = y + Math.floor(totalMonths / 12);
        let month = totalMonths % 12;
        if (month < 0) { month += 12; year -= 1; }

        const lastDay = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
        const day = Math.min(d, lastDay);

        const dt = new Date(Date.UTC(year, month, day));
        return dt.toISOString().slice(0, 10);
    }

    function getPaidDates(){
        try { return JSON.parse(shell.dataset.paidDates || '{}') || {}; }
        catch(e){ return {}; }
    }

    function suggestedDateForMonth(uiMonth){
        const dpDate = shell.dataset.dpDate || '';
        const paid = getPaidDates();

        // if previous month has a paid date, next month follows that date + 1 month
        for (let j = uiMonth - 1; j >= 1; j--){
            if (paid[j]) return addMonthsSafe(paid[j], 1);
        }

        // otherwise, month 1 = DP + 1 month, month 2 = DP + 2 months, etc.
        return addMonthsSafe(dpDate, uiMonth);
    }

    // mark if user manually edits date
    dateInput.addEventListener('input', () => dateInput.dataset.touched = '1');
    dateInput.addEventListener('change', () => dateInput.dataset.touched = '1');

    if (!isOpen && monthSelect){
        monthSelect.addEventListener('change', () => {
            if (dateInput.dataset.touched === '1') return;

            const m = parseInt(monthSelect.value || '1', 10);
            const s = suggestedDateForMonth(isNaN(m) ? 1 : m);
            if (s) dateInput.value = s;
        });

        // on load: if no old value OR user didn't touch, keep computed by server; this stays as-is
    }
})();
</script>

@endsection
