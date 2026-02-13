@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Installment Plan Edit (Dark mode compatible)
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
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--text) !important;
        text-decoration: none;
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
        box-shadow: 0 10px 18px rgba(13,110,253,.18);
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13,110,253,.24);
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
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
        min-width:0;
    }

    .card-bodyx{ padding: 18px; }

    /* Inputs */
    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        margin-bottom: 6px;
        color: rgba(15, 23, 42, .82);
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
        background: var(--kt-surface-2);
    }

    .readonlyx{
        background: rgba(148,163,184,.10) !important;
        color: var(--text) !important;
    }
    html[data-theme="dark"] .readonlyx{
        background: rgba(2,6,23,.35) !important;
    }

    .helper{
        font-size: 12px;
        color: var(--muted);
        margin-top: 6px;
        font-weight: 700;
    }

    /* Treatment tags */
    .tags{
        display:flex;
        flex-wrap: wrap;
        gap: 6px;
        padding-top: 2px;
    }
    .tag{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        background: rgba(148,163,184,.10);
        border: 1px solid var(--kt-border);
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .tag{
        background: rgba(2,6,23,.30);
    }
</style>

@php
    $openOld = old('is_open_contract');
    $isOpen = !is_null($openOld)
        ? (bool)$openOld
        : (bool)($plan->is_open_contract ?? false);

    $openMonthlyVal = old('open_monthly_payment', $plan->open_monthly_payment ?? '');
@endphp

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Edit Installment Plan</h2>
        <p class="subtitle">Update the editable details of this installment plan.</p>
    </div>

    <x-back-button
        fallback="{{ route('staff.payments.index', ['tab' => 'installment']) }}"
        class="btn-ghostx"
        label="Back"
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
            Plan ID: <strong>#{{ $plan->id }}</strong>
        </div>
        <div class="hint">
            Balance & status are read-only
        </div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.installments.update', $plan) }}" method="POST">
            @csrf
            <input type="hidden" name="return" value="{{ old('return', request('return', session('kt.return_url', request()->fullUrl()))) }}">
            @method('PUT')

            <div class="row g-3">

                {{-- Patient --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Patient</label>
                    <input class="inputx readonlyx" value="{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}" readonly>
                </div>

                {{-- Treatments --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Treatments</label>

                    @if($plan->visit && $plan->visit->procedures->count())
                        <div class="tags">
                            @foreach($plan->visit->procedures as $proc)
                                <span class="tag">
                                    <i class="fa fa-stethoscope"></i>
                                    {{ $proc->service->name ?? 'Service' }}
                                </span>
                            @endforeach
                        </div>
                    @elseif($plan->service)
                        <div class="tags">
                            <span class="tag"><i class="fa fa-stethoscope"></i> {{ $plan->service->name }}</span>
                        </div>
                    @else
                        <div class="helper">N/A</div>
                    @endif
                </div>

                {{-- Total Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Total Cost <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="total_cost" class="inputx"
                           value="{{ old('total_cost', $plan->total_cost) }}" required>
                </div>

                {{-- Downpayment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Downpayment <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="downpayment" class="inputx"
                           value="{{ old('downpayment', $plan->downpayment) }}" required>
                </div>

                {{-- Open Contract --}}
                <div class="col-12">
                    <div class="form-check" style="margin-top:2px;">
                        <input class="form-check-input" type="checkbox" id="isOpenContract" name="is_open_contract" value="1"
                            {{ $isOpen ? 'checked' : '' }}>
                        <label class="form-check-label" for="isOpenContract" style="font-weight:950; color:var(--text);">
                            Open Contract (no fixed months — pay any amount until fully paid)
                        </label>
                        <div class="helper">
                            If enabled, months will be removed (no fixed schedule). Payments will be auto-numbered (Payment #1, #2, #3...).
                        </div>
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
                        value="{{ $openMonthlyVal }}"
                    >
                    <div class="helper">Example: 2000 — this will auto-fill in Pay page Amount Paid (editable).</div>
                </div>

                {{-- Months --}}
                <div class="col-12 col-md-6" id="monthsWrap">
                    <label class="form-labelx">Payment Term (Months) <span class="text-danger">*</span></label>
                    <input type="number" id="monthsInput" name="months" class="inputx"
                           value="{{ old('months', $plan->months) }}" min="1" required>
                    <div class="helper">Fixed-term plans only.</div>
                </div>

                {{-- Start Date --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="inputx"
                           value="{{ old('start_date', $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('Y-m-d') : '') }}" required>
                </div>

                {{-- Balance --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Remaining Balance</label>
                    <input class="inputx readonlyx" value="{{ number_format((float)($plan->balance ?? 0), 2) }}" readonly>
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Status</label>
                    <input class="inputx readonlyx" value="{{ $plan->status }}" readonly>
                </div>

                <div class="col-12 pt-2 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Plan
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
function toggleOpenEdit() {
    const cb = document.getElementById('isOpenContract');

    const monthsWrap = document.getElementById('monthsWrap');
    const months = document.getElementById('monthsInput');

    const openWrap = document.getElementById('openMonthlyWrap');
    const openMonthly = document.getElementById('openMonthlyInput');

    const on = cb && cb.checked;

    // Months
    if (monthsWrap) monthsWrap.style.display = on ? 'none' : '';
    if (months){
        if (on){
            months.required = false;
            months.disabled = true;
            months.value = '';
        } else {
            months.disabled = false;
            months.required = true;
            if (months.value === '' || Number(months.value) < 1) months.value = 6;
        }
    }

    // Open Monthly Payment
    if (openWrap) openWrap.classList.toggle('d-none', !on);
    if (openMonthly){
        if (on){
            openMonthly.disabled = false;
            openMonthly.required = true;

            // normalize if empty
            if (openMonthly.value === '') openMonthly.value = '0.00';
        } else {
            openMonthly.required = false;
            openMonthly.disabled = true;
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('isOpenContract');
    if (cb) cb.addEventListener('change', toggleOpenEdit);

    // Init state
    toggleOpenEdit();
});
</script>

@endsection
