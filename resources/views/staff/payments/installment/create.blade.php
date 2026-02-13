@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Installment Create (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);

        --text: var(--kt-text);
        --muted: var(--kt-muted);

        --soft: rgba(148,163,184,.14);

        --brand1: #7c3aed;
        --brand2: #6f42c1;

        --radius: 16px;
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
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
        text-decoration: none;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 10px 18px rgba(124, 58, 237, .18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(124, 58, 237, .24);
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
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
    }
    .card-bodyx{ padding: 18px; }

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
        border-color: rgba(124,58,237,.55);
        box-shadow: 0 0 0 4px rgba(124,58,237,.14);
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

    .chip{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(124,58,237,.22);
        background: rgba(124,58,237,.10);
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .chip{
        color: rgba(226,232,240,.92);
        background: rgba(124,58,237,.14);
        border-color: rgba(124,58,237,.25);
    }

    /* Mini summary */
    .mini-sum{
        border: 1px solid var(--kt-border);
        background: rgba(148,163,184,.10);
        border-radius: 14px;
        padding: 12px 14px;
        margin-top: 6px;
        display:flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items:center;
        justify-content:space-between;
    }
    html[data-theme="dark"] .mini-sum{
        background: rgba(2,6,23,.30);
    }
    .mini-sum .k{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .mini-sum .v{
        font-size: 14px;
        font-weight: 950;
        color: var(--text);
        font-variant-numeric: tabular-nums;
    }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Create Installment Plan</h2>
        <p class="subtitle">Choose a visit or appointment, then set downpayment and payment term.</p>
    </div>

    <x-back-button
        fallback="{{ route('staff.payments.choose') }}"
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
            Tip: Choose either <strong>Visit</strong> or <strong>Appointment</strong> (not both).
        </div>
        <div class="hint">
            <span class="chip"><i class="fa fa-calculator"></i> Downpayment auto = 50%</span>
        </div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.payments.store.installment') }}" method="POST" id="installmentForm">
            @csrf

            <div class="row g-3">

                {{-- Visit --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Visit (With Balance)</label>
                    <select name="visit_id" id="visitSelect" class="selectx">
                        <option value="">-- Select Visit --</option>

                        @php $shownVisitCount = 0; @endphp

                        @foreach($visits as $visit)
                            @php
                                $procs = $visit->procedures ?? collect();

                                $treatmentsText = $procs->map(function($p){
                                    $name = $p->service?->name ?? '—';
                                    $tooth = $p->tooth_number ? '#'.$p->tooth_number : '';
                                    $surface = $p->surface ? $p->surface : '';
                                    $note = trim((string)($p->notes ?? ''));
                                    $noteLabel = $note !== '' ? (' — '.\Illuminate\Support\Str::limit($note, 24)) : '';
                                    return trim($name.' '.trim($tooth.' '.$surface).$noteLabel);
                                })->filter()->implode(', ');

                                $due = $visit->price !== null
                                    ? (float) $visit->price
                                    : (float) ($procs->sum('price') ?? 0);

                                $paid = 0.0;
                                if (property_exists($visit, 'total_paid') && $visit->total_paid !== null) {
                                    $paid = (float) $visit->total_paid;
                                } else {
                                    if (method_exists($visit, 'payments')) {
                                        $paid = (float) $visit->payments()->sum('amount');
                                    } else {
                                        $paid = (float) \App\Models\Payment::where('visit_id', $visit->id)->sum('amount');
                                    }
                                }

                                $inInstallment = \App\Models\InstallmentPlan::where('visit_id', $visit->id)->exists();

                                $balance = max($due - $paid, 0);

                                if ($due <= 0 || $balance <= 0 || $inInstallment) {
                                    continue;
                                }

                                $shownVisitCount++;

                                $pName = trim(($visit->patient?->first_name ?? '').' '.($visit->patient?->last_name ?? '')) ?: 'Patient';
                                $dateLabel = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') : '—';
                                $dateForInput = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d') : '';
                            @endphp

                            <option value="{{ $visit->id }}"
                                data-type="visit"
                                data-treatments="{{ $treatmentsText }}"
                                data-amount="{{ $balance }}"
                                data-date="{{ $dateForInput }}"
                                {{ old('visit_id') == $visit->id ? 'selected' : '' }}
                            >
                                Visit - {{ $pName }} ({{ $dateLabel }}) — Balance ₱{{ number_format($balance, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <div class="helper">
                        @if($shownVisitCount === 0)
                            No visits with remaining balance are available (or they’re already in an installment plan).
                        @else
                            Shows only visits with remaining balance and not already in an installment plan.
                        @endif
                    </div>
                </div>

                {{-- Appointment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment</label>
                    <select name="appointment_id" id="appointmentSelect" class="selectx">
                        <option value="">-- Select Appointment --</option>

                        @foreach($appointments as $app)
                            @php
                                $pFirst = $app->patient?->first_name ?? $app->public_first_name ?? '';
                                $pLast  = $app->patient?->last_name  ?? $app->public_last_name  ?? '';
                                $pName  = trim($pFirst.' '.$pLast) ?: ($app->public_name ?? 'Patient');

                                $svcName  = $app->service?->name ?? '—';
                                $svcPrice = (float) ($app->service?->base_price ?? 0);

                                $dateLabel = $app->appointment_date
                                    ? \Carbon\Carbon::parse($app->appointment_date)->format('m/d/Y')
                                    : '—';
                                $timeLabel = $app->appointment_time
                                    ? \Carbon\Carbon::parse($app->appointment_time)->format('h:i A')
                                    : '—';

                                $dateForInput = $app->appointment_date
                                    ? \Carbon\Carbon::parse($app->appointment_date)->format('Y-m-d')
                                    : '';
                            @endphp

                            <option value="{{ $app->id }}"
                                data-type="appointment"
                                data-treatments="{{ $svcName }}"
                                data-amount="{{ $svcPrice }}"
                                data-date="{{ $dateForInput }}"
                                {{ old('appointment_id') == $app->id ? 'selected' : '' }}
                            >
                                Appointment - {{ $pName }} ({{ $dateLabel }} {{ $timeLabel }})
                            </option>
                        @endforeach
                    </select>

                    <div class="helper">
                        Paying an appointment will create a visit automatically, then attach the installment plan.
                    </div>
                </div>

                {{-- Total Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Total Cost <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="totalCostInput"
                        name="total_cost"
                        class="inputx"
                        value="{{ old('total_cost') }}"
                        required
                    >
                    <div class="helper">Auto-filled from selection (editable). This stays required even for Open Contract.</div>

                    <div class="mini-sum" id="calcSummary" style="display:none;">
                        <div>
                            <div class="k">Balance</div>
                            <div class="v" id="balanceText">₱0.00</div>
                        </div>
                        <div id="monthlyWrap">
                            <div class="k" id="monthlyLabel">Monthly</div>
                            <div class="v" id="monthlyText">₱0.00</div>
                        </div>
                    </div>
                </div>

                {{-- Downpayment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Downpayment <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="downpaymentInput"
                        name="downpayment"
                        class="inputx"
                        value="{{ old('downpayment') }}"
                        required
                    >
                    <div class="helper">Defaults to 50% of total cost (you can override).</div>
                </div>

                {{-- Open Contract --}}
                <div class="col-12">
                    <div class="form-check" style="margin-top:2px;">
                        <input class="form-check-input" type="checkbox" id="isOpenContract" name="is_open_contract" value="1"
                            {{ old('is_open_contract') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isOpenContract" style="font-weight:950; color:var(--text);">
                            Open Contract (no fixed months — pay any amount until fully paid)
                        </label>
                        <div class="helper">If enabled, Payment Term is hidden and you must set Monthly Payment (auto-fills in Pay page).</div>
                    </div>
                </div>

                {{-- ✅ Open Contract Monthly Payment --}}
                <div class="col-12 col-md-6 d-none" id="openMonthlyWrap">
                    <label class="form-labelx">Monthly Payment (Open Contract) <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="openMonthlyInput"
                        name="open_monthly_payment"
                        class="inputx"
                        value="{{ old('open_monthly_payment') }}"
                    >
                    <div class="helper">Example: 2000 — this will automatically appear as Amount Paid in Pay page (editable).</div>
                </div>

                {{-- Payment Term --}}
                <div class="col-12 col-md-6" id="monthsWrap">
                    <label class="form-labelx">Payment Term (months) <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        id="monthsInput"
                        name="months"
                        class="inputx"
                        value="{{ old('months', 6) }}"
                        min="1"
                        required
                    >
                    <div class="helper">Fixed-term plans only.</div>
                </div>

                {{-- Start Date --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Start Date <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        id="startDateInput"
                        name="start_date"
                        class="inputx"
                        value="{{ old('start_date', date('Y-m-d')) }}"
                        required
                    >
                    <div class="helper">Auto-fills from Visit/Appointment date when available.</div>
                </div>

                {{-- Treatments --}}
                <div class="col-12">
                    <label class="form-labelx">Treatments (auto-filled)</label>

                    <input type="hidden" name="treatments_preview" id="treatmentsHidden" value="{{ old('treatments_preview') }}">

                    <textarea id="treatmentsBox" class="textareax readonlyx" rows="3" readonly>{{ old('treatments_preview') }}</textarea>
                    <div class="helper">Readonly: updates based on selection.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Create Installment Plan
                    </button>

                    <a href="{{ route('staff.payments.choose') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const visitSelect       = document.getElementById('visitSelect');
    const appointmentSelect = document.getElementById('appointmentSelect');

    const totalCostInput    = document.getElementById('totalCostInput');
    const downpaymentInput  = document.getElementById('downpaymentInput');

    const monthsWrap        = document.getElementById('monthsWrap');
    const monthsInput       = document.getElementById('monthsInput');
    const isOpenContract    = document.getElementById('isOpenContract');

    const openMonthlyWrap   = document.getElementById('openMonthlyWrap');
    const openMonthlyInput  = document.getElementById('openMonthlyInput');

    const startDateInput    = document.getElementById('startDateInput');

    const treatmentsBox     = document.getElementById('treatmentsBox');
    const treatmentsHidden  = document.getElementById('treatmentsHidden');

    const calcSummary       = document.getElementById('calcSummary');
    const balanceText       = document.getElementById('balanceText');
    const monthlyText       = document.getElementById('monthlyText');
    const monthlyLabel      = document.getElementById('monthlyLabel');

    let autoDownpayment = true;
    let openMonthlyTouched = false;

    function n(v){
        const x = parseFloat(v);
        return isFinite(x) ? x : 0;
    }
    function money(v){
        const x = n(v);
        return '₱' + x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setFromOption(opt){
        if(!opt) return;

        const amt = n(opt.getAttribute('data-amount'));
        const tx  = opt.getAttribute('data-treatments') || '';
        const dt  = opt.getAttribute('data-date') || '';

        // total cost
        if (totalCostInput) totalCostInput.value = amt ? amt.toFixed(2) : '0.00';

        // treatments
        if (treatmentsBox) treatmentsBox.value = tx;
        if (treatmentsHidden) treatmentsHidden.value = tx;

        // start date
        if (startDateInput && dt) startDateInput.value = dt;

        // default downpayment 50%
        autoDownpayment = true;
        if (downpaymentInput) downpaymentInput.value = (amt / 2).toFixed(2);

        // suggest open monthly (only if user hasn't typed)
        if (openMonthlyInput && !openMonthlyTouched && !openMonthlyInput.value){
            const bal = Math.max(amt - n(downpaymentInput?.value), 0);
            const guessMonths = Math.max(1, Math.round(n(monthsInput?.value) || 6));
            openMonthlyInput.value = (bal / guessMonths).toFixed(2);
        }

        recalc();
    }

    function clearTreatmentsIfNone(){
        const hasAny = (visitSelect && visitSelect.value) || (appointmentSelect && appointmentSelect.value);
        if (!hasAny){
            if (treatmentsBox) treatmentsBox.value = '';
            if (treatmentsHidden) treatmentsHidden.value = '';
        }
    }

    function toggleOpenContractUI(){
        const open = !!(isOpenContract && isOpenContract.checked);

        // Months UI (fixed-term)
        if (monthsWrap) monthsWrap.style.display = open ? 'none' : '';
        if (monthsInput){
            if (open){
                monthsInput.required = false;
                monthsInput.disabled = true;
                monthsInput.value = '';
            } else {
                monthsInput.disabled = false;
                monthsInput.required = true;
                if (monthsInput.value === '' || Number(monthsInput.value) < 1) monthsInput.value = 6;
            }
        }

        // Open monthly UI
        if (openMonthlyWrap) openMonthlyWrap.classList.toggle('d-none', !open);
        if (openMonthlyInput){
            if (open){
                openMonthlyInput.disabled = false;
                openMonthlyInput.required = true;

                if (!openMonthlyTouched && !openMonthlyInput.value){
                    const total = n(totalCostInput?.value);
                    const down  = n(downpaymentInput?.value);
                    const bal   = Math.max(total - down, 0);
                    const guessMonths = Math.max(1, Math.round(n(monthsInput?.value) || 6));
                    openMonthlyInput.value = (bal / guessMonths).toFixed(2);
                }
            } else {
                openMonthlyInput.required = false;
                openMonthlyInput.disabled = true;
            }
        }

        recalc();
    }

    function recalc(){
        const total = n(totalCostInput && totalCostInput.value);
        let down = n(downpaymentInput && downpaymentInput.value);

        if (down > total) down = total;

        const bal = Math.max(total - down, 0);
        const open = !!(isOpenContract && isOpenContract.checked);

        if (calcSummary){
            calcSummary.style.display = (totalCostInput && totalCostInput.value !== '') ? '' : 'none';
        }
        if (balanceText) balanceText.textContent = money(bal);

        if (open){
            if (monthlyLabel) monthlyLabel.textContent = 'Monthly Payment';
            const om = n(openMonthlyInput && openMonthlyInput.value);
            if (monthlyText) monthlyText.textContent = money(om);
        } else {
            if (monthlyLabel) monthlyLabel.textContent = 'Monthly';
            const months = Math.max(n(monthsInput && monthsInput.value), 1);
            if (monthlyText) monthlyText.textContent = money(months ? (bal / months) : 0);
        }
    }

    // Events
    if (visitSelect){
        visitSelect.addEventListener('change', function(){
            if (this.value !== ''){
                if (appointmentSelect) appointmentSelect.value = '';
                setFromOption(this.selectedOptions[0]);
            } else {
                clearTreatmentsIfNone();
                recalc();
            }
        });
    }

    if (appointmentSelect){
        appointmentSelect.addEventListener('change', function(){
            if (this.value !== ''){
                if (visitSelect) visitSelect.value = '';
                setFromOption(this.selectedOptions[0]);
            } else {
                clearTreatmentsIfNone();
                recalc();
            }
        });
    }

    if (downpaymentInput){
        downpaymentInput.addEventListener('input', () => {
            autoDownpayment = false;
            recalc();
        });
        downpaymentInput.addEventListener('blur', () => {
            const total = n(totalCostInput && totalCostInput.value);
            let down = n(downpaymentInput.value);
            if (down > total) down = total;
            downpaymentInput.value = down.toFixed(2);
            recalc();
        });
    }

    if (totalCostInput){
        totalCostInput.addEventListener('input', () => {
            const total = n(totalCostInput.value);
            if (autoDownpayment && downpaymentInput){
                downpaymentInput.value = (total / 2).toFixed(2);
            }
            if (isOpenContract?.checked && openMonthlyInput && !openMonthlyTouched){
                // keep suggested open monthly updated only if user didn't type
                const bal = Math.max(total - n(downpaymentInput?.value), 0);
                const guessMonths = Math.max(1, Math.round(n(monthsInput?.value) || 6));
                openMonthlyInput.value = (bal / guessMonths).toFixed(2);
            }
            recalc();
        });
        totalCostInput.addEventListener('blur', () => {
            const total = n(totalCostInput.value);
            totalCostInput.value = total.toFixed(2);
            if (autoDownpayment && downpaymentInput){
                downpaymentInput.value = (total / 2).toFixed(2);
            }
            recalc();
        });
    }

    if (monthsInput){
        monthsInput.addEventListener('input', () => {
            if (isOpenContract?.checked && openMonthlyInput && !openMonthlyTouched){
                const total = n(totalCostInput?.value);
                const down  = n(downpaymentInput?.value);
                const bal   = Math.max(total - down, 0);
                const m     = Math.max(1, Math.round(n(monthsInput.value) || 6));
                openMonthlyInput.value = (bal / m).toFixed(2);
            }
            recalc();
        });
    }

    if (openMonthlyInput){
        openMonthlyInput.addEventListener('input', () => {
            openMonthlyTouched = true;
            recalc();
        });
        openMonthlyInput.addEventListener('blur', () => {
            const v = n(openMonthlyInput.value);
            openMonthlyInput.value = v.toFixed(2);
            recalc();
        });
    }

    if (isOpenContract){
        isOpenContract.addEventListener('change', toggleOpenContractUI);
    }

    // Init
    window.addEventListener('load', () => {
        if (visitSelect && visitSelect.value) setFromOption(visitSelect.selectedOptions[0]);
        else if (appointmentSelect && appointmentSelect.value) setFromOption(appointmentSelect.selectedOptions[0]);
        else recalc();

        // If old value exists, consider it "touched"
        if (openMonthlyInput && openMonthlyInput.value) openMonthlyTouched = true;

        // Ensure correct enable/disable on load
        if (openMonthlyInput && !(isOpenContract && isOpenContract.checked)) {
            openMonthlyInput.disabled = true;
            openMonthlyInput.required = false;
        }

        toggleOpenContractUI();
    });

})();
</script>

@endsection
