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
        border: 1px solid rgba(15, 23, 42, .12);
        background: rgba(255,255,255,.85);
        color: rgba(15, 23, 42, .75);
        text-decoration: none;
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
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13,110,253,.18);
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13,110,253,.24);
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
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    .card-bodyx{ padding: 18px; }

    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 6px;
        color: rgba(15, 23, 42, .75);
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
                    <label class="form-labelx">Total Cost <span class="text-danger">*</span></label>
                    <input type="number" name="total_cost" class="inputx"
                           value="{{ old('total_cost', $plan->total_cost) }}" required>
                </div>

                {{-- Downpayment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Downpayment <span class="text-danger">*</span></label>
                    <input type="number" name="downpayment" class="inputx"
                           value="{{ old('downpayment', $plan->downpayment) }}" required>
                </div>

                {{-- ✅ Open Contract --}}
                <div class="col-12">
                    @php
                        $openOld = old('is_open_contract');
                        $isOpen = !is_null($openOld)
                            ? (bool)$openOld
                            : (bool)($plan->is_open_contract ?? false);
                    @endphp

                    <div class="form-check" style="margin-top:2px;">
                        <input class="form-check-input" type="checkbox" id="isOpenContract" name="is_open_contract" value="1"
                            {{ $isOpen ? 'checked' : '' }}>
                        <label class="form-check-label" for="isOpenContract" style="font-weight:900;">
                            Open Contract (no fixed months — pay any amount until fully paid)
                        </label>
                        <div class="helper">
                            If enabled, the pay form will auto-number payments (Payment #1, #2, #3...) and Month dropdown will be removed.
                        </div>
                    </div>
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
                           value="{{ old('start_date', optional($plan->start_date)->format('Y-m-d')) }}" required>
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

                <div class="col-12 pt-2 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Plan
                    </button>

                    <a href="{{ route('staff.payments.index', ['tab' => 'installment']) }}" class="btn-ghostx">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
// ✅ Same behavior as create: disable months so browser validation won't block submit
function toggleMonthsEdit() {
    const cb = document.getElementById('isOpenContract');
    const wrap = document.getElementById('monthsWrap');
    const months = document.getElementById('monthsInput');

    const on = cb && cb.checked;

    if (wrap) wrap.style.display = on ? 'none' : '';
    if (!months) return;

    if (on) {
        months.required = false;
        months.disabled = true;  // ✅ not sent to server + no HTML5 validation
        months.value = '';       // ✅ do NOT use 0 (min=1)
    } else {
        months.disabled = false;
        months.required = true;
        if (months.value === '' || Number(months.value) < 1) months.value = 6;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('isOpenContract');
    if (cb) cb.addEventListener('change', toggleMonthsEdit);
    toggleMonthsEdit();
});
</script>

@endsection
