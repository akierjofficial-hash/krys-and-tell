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
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
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

    .form-max{ max-width: 1100px; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Add Cash Payment</h2>
        <p class="subtitle">Select a visit or appointment, confirm the cost, then submit the payment.</p>
    </div>

    <a href="{{ route('payments.choose') }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back to Plan
    </a>
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
        <div class="hint">Treatments will auto-fill</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('payments.store.cash') }}" method="POST">
            @csrf

            <div class="row g-3">

                {{-- Visit --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Visit (Today)</label>
                    <select name="visit_id" id="visitSelect" class="selectx">
                        <option value="">-- Select Visit --</option>

                        @foreach($visits as $visit)
                            @php
                                $treatmentsPreview = $visit->procedures
                                    ->map(function($p){
                                        $svc = $p->service?->name ?? '—';
                                        $tooth = $p->tooth_number ? ('#'.$p->tooth_number) : '';
                                        $surface = $p->surface ? (' '.$p->surface) : '';
                                        return trim($svc.' '.$tooth.$surface);
                                    })
                                    ->implode(', ');

                                $amountPreview = $visit->procedures->sum('price') ?? 0;
                            @endphp

                            <option value="{{ $visit->id }}"
                                data-type="visit"
                                data-treatments="{{ $treatmentsPreview }}"
                                data-amount="{{ $amountPreview }}"
                                {{ old('visit_id') == $visit->id ? 'selected' : '' }}
                            >
                                Visit - {{ $visit->patient?->first_name }} {{ $visit->patient?->last_name }}
                                ({{ \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Shows available visits you can charge today.</div>
                </div>

                {{-- Appointment --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Appointment</label>
                    <select name="appointment_id" id="appointmentSelect" class="selectx">
                        <option value="">-- Select Appointment --</option>
                        @foreach($appointments as $app)
                            <option value="{{ $app->id }}"
                                data-type="appointment"
                                data-treatments="{{ $app->service?->name }}"
                                data-amount="{{ $app->service?->base_price ?? 0 }}"
                                {{ old('appointment_id') == $app->id ? 'selected' : '' }}
                            >
                                Appointment - {{ $app->patient?->first_name }} {{ $app->patient?->last_name }}
                                ({{ \Carbon\Carbon::parse($app->appointment_date)->format('m/d/Y') }}
                                {{ \Carbon\Carbon::parse($app->appointment_time)->format('h:i A') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Choose an appointment to pay for.</div>
                </div>

                {{-- Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Cost <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="amountInput" name="amount" class="inputx"
                           value="{{ old('amount') }}" required>
                    <div class="helper">You can override the amount if needed.</div>
                </div>

                {{-- Payment Method --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Method <span class="text-danger">*</span></label>
                    <select name="method" class="selectx" required>
                        @php $m = old('method', 'Cash'); @endphp
                        <option value="Cash" {{ $m=='Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ $m=='GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Card" {{ $m=='Card' ? 'selected' : '' }}>Card</option>
                        <option value="Bank Transfer" {{ $m=='Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                {{-- Payment Date --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="inputx"
                           value="{{ old('payment_date') }}" required>
                </div>

                {{-- Treatments --}}
                <div class="col-12">
                    <label class="form-labelx">Treatments (auto-filled)</label>
                    <textarea id="treatmentsBox" class="textareax readonlyx" rows="3" readonly>{{ old('treatments_preview') }}</textarea>
                    <div class="helper">This field is readonly and updates based on selection.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Submit Payment
                    </button>

                    <a href="{{ route('payments.choose') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function updateFields(selected) {
    const amt = parseFloat(selected.getAttribute('data-amount') || 0);
    document.getElementById('amountInput').value = amt.toFixed(2);

    document.getElementById('treatmentsBox').value =
        selected.getAttribute('data-treatments') || '';
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

window.addEventListener('load', () => {
    const visitSel = document.getElementById('visitSelect');
    const appSel  = document.getElementById('appointmentSelect');

    if (visitSel.value) updateFields(visitSel.selectedOptions[0]);
    if (appSel.value) updateFields(appSel.selectedOptions[0]);
});
</script>

@endsection
