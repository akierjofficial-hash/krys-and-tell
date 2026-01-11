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
        text-decoration: none;
        background: linear-gradient(135deg, #7c3aed, #6f42c1);
        box-shadow: 0 10px 18px rgba(124, 58, 237, .18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(124, 58, 237, .24);
        color:#fff;
    }

    .error-box{
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
        border-color: rgba(124,58,237,.55);
        box-shadow: 0 0 0 4px rgba(124,58,237,.12);
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

    .chip{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(124,58,237,.22);
        background: rgba(124,58,237,.10);
        color: #0f172a;
        white-space: nowrap;
    }

    .form-max{ max-width: 1100px; }
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
        <div class="hint">Tip: Choose either <strong>Visit</strong> or <strong>Appointment</strong> (not both).</div>
        <div class="hint"><span class="chip"><i class="fa fa-calculator"></i> Downpayment auto = 50%</span></div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.payments.store.installment') }}" method="POST">
            @csrf

            <div class="row g-3">

                {{-- Visit --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Visit</label>
                    <select name="visit_id" id="visitSelect" class="selectx">
                        <option value="">-- Select Visit --</option>

                        @foreach($visits as $visit)
                            @php
                                $procs = $visit->procedures ?? collect();

                                $treatmentsText = $procs->map(function($p){
                                    $name = $p->service?->name ?? '';
                                    $tooth = $p->tooth_number ? '#'.$p->tooth_number : '';
                                    $surface = $p->surface ?? '';
                                    return trim($name.' '.trim($tooth.' '.$surface));
                                })->filter()->implode(', ');

                                $amount = $procs->sum('price') ?? 0;
                            @endphp

                            <option value="{{ $visit->id }}"
                                data-type="visit"
                                data-treatments="{{ $treatmentsText }}"
                                data-amount="{{ $amount }}"
                                {{ old('visit_id') == $visit->id ? 'selected' : '' }}
                            >
                                Visit - {{ $visit->patient?->first_name }} {{ $visit->patient?->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Select a visit to set as installment.</div>
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
                                $svcPrice = $app->service?->base_price ?? 0;

                                $dateLabel = $app->appointment_date
                                    ? \Carbon\Carbon::parse($app->appointment_date)->format('m/d/Y')
                                    : '—';

                                $timeLabel = $app->appointment_time
                                    ? \Carbon\Carbon::parse($app->appointment_time)->format('h:i A')
                                    : '—';
                            @endphp

                            <option value="{{ $app->id }}"
                                data-type="appointment"
                                data-treatments="{{ $svcName }}"
                                data-amount="{{ $svcPrice }}"
                                {{ old('appointment_id') == $app->id ? 'selected' : '' }}
                            >
                                Appointment - {{ $pName }} ({{ $dateLabel }} {{ $timeLabel }})
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Or select an appointment instead.</div>
                </div>

                {{-- Total Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Total Cost <span class="text-danger">*</span></label>
                    <input type="number" id="totalCostInput" name="total_cost" class="inputx"
                           value="{{ old('total_cost') }}" required>
                    <div class="helper">Auto-filled from selection (editable if needed).</div>
                </div>

                {{-- Downpayment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Downpayment <span class="text-danger">*</span></label>
                    <input type="number" id="downpaymentInput" name="downpayment" class="inputx"
                           value="{{ old('downpayment') }}" required>
                    <div class="helper">Defaults to 50% of total cost.</div>
                </div>

                {{-- Open Contract --}}
                <div class="col-12">
                    <div class="form-check" style="margin-top:2px;">
                        <input class="form-check-input" type="checkbox" id="isOpenContract" name="is_open_contract" value="1"
                            {{ old('is_open_contract') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isOpenContract" style="font-weight:900;">
                            Open Contract (no fixed months — pay any amount until fully paid)
                        </label>
                        <div class="helper">If enabled, the system will auto-number payments (Payment #1, #2, #3...).</div>
                    </div>
                </div>

                {{-- Payment Term --}}
                <div class="col-12 col-md-6" id="monthsWrap">
                    <label class="form-labelx">Payment Term (months) <span class="text-danger">*</span></label>
                    <input type="number" id="monthsInput" name="months" class="inputx"
                           value="{{ old('months', 6) }}" min="1" required>
                    <div class="helper">Fixed-term plans only.</div>
                </div>

                {{-- Start Date --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="inputx"
                           value="{{ old('start_date', date('Y-m-d')) }}" required>
                </div>

                {{-- Treatments --}}
                <div class="col-12">
                    <label class="form-labelx">Treatments (auto-filled)</label>
                    <textarea id="treatmentsBox" class="textareax readonlyx" rows="3" readonly>{{ old('treatments_preview') }}</textarea>
                    <div class="helper">This field is readonly and updates based on selection.</div>
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
function updateFields(selected) {
    let amount = selected.getAttribute('data-amount') || 0;
    let treatments = selected.getAttribute('data-treatments') || '';

    const amt = parseFloat(amount || 0);

    document.getElementById('totalCostInput').value = isNaN(amt) ? '' : amt.toFixed(2);
    document.getElementById('treatmentsBox').value = treatments;

    // Default downpayment = 50%
    document.getElementById('downpaymentInput').value = isNaN(amt) ? '' : (amt / 2).toFixed(2);
}

document.getElementById('visitSelect').addEventListener('change', function() {
    if (this.value !== "") {
        document.getElementById('appointmentSelect').value = "";
        updateFields(this.selectedOptions[0]);
    } else {
        document.getElementById('treatmentsBox').value = "";
    }
});

document.getElementById('appointmentSelect').addEventListener('change', function() {
    if (this.value !== "") {
        document.getElementById('visitSelect').value = "";
        updateFields(this.selectedOptions[0]);
    } else {
        document.getElementById('treatmentsBox').value = "";
    }
});

// ✅ Open contract toggle (disable months input so browser won't block submit)
function toggleMonths() {
    const cb = document.getElementById('isOpenContract');
    const wrap = document.getElementById('monthsWrap');
    const months = document.getElementById('monthsInput');

    const on = cb && cb.checked;

    if (wrap) wrap.style.display = on ? 'none' : '';
    if (!months) return;

    if (on) {
        months.required = false;
        months.disabled = true;   // not sent to server
        months.value = '';        // do NOT use 0 (min=1)
    } else {
        months.disabled = false;
        months.required = true;
        if (months.value === '' || Number(months.value) < 1) months.value = 6;
    }
}

document.getElementById('isOpenContract').addEventListener('change', toggleMonths);

window.addEventListener('load', () => {
    const visitSel = document.getElementById('visitSelect');
    const appSel  = document.getElementById('appointmentSelect');

    if (visitSel.value) updateFields(visitSel.selectedOptions[0]);
    if (appSel.value) updateFields(appSel.selectedOptions[0]);

    toggleMonths();
});
</script>

@endsection
