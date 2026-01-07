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

    <a href="{{ route('staff.payments.choose') }}" class="btn-ghostx">
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
        <div class="hint">Only <strong>unpaid / with balance</strong> visits show up here.</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('staff.payments.store.cash') }}" method="POST">
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
                                // Treatments preview
                                $treatmentsPreview = $visit->procedures
                                    ->map(function($p){
                                        $svc = $p->service?->name ?? '—';
                                        $tooth = $p->tooth_number ? ('#'.$p->tooth_number) : '';
                                        $surface = $p->surface ? (' '.$p->surface) : '';
                                        $note = trim((string)($p->notes ?? ''));
                                        $noteLabel = $note !== '' ? (' — '.\Illuminate\Support\Str::limit($note, 24)) : '';
                                        return trim($svc.' '.$tooth.$surface.$noteLabel);
                                    })
                                    ->implode(', ');

                                // Total due
                                $due = $visit->price !== null
    ? (float) $visit->price
    : (float) ($visit->procedures->sum('price') ?? 0);


                                // Total paid (safe even if relation not eager loaded)
                                $paid = 0.0;
                                if (property_exists($visit, 'total_paid') && $visit->total_paid !== null) {
                                    $paid = (float) $visit->total_paid;
                                } else {
                                    // If Visit has payments() relation, this will work; otherwise fallback to Payment model.
                                    if (method_exists($visit, 'payments')) {
                                        $paid = (float) $visit->payments()->sum('amount');
                                    } else {
                                        $paid = (float) \App\Models\Payment::where('visit_id', $visit->id)->sum('amount');
                                    }
                                }

                                // If Visit is already tied to an installment plan, do NOT show it in cash dropdown
                                $inInstallment = \App\Models\InstallmentPlan::where('visit_id', $visit->id)->exists();

                                $balance = max($due - $paid, 0);

                                // Skip: no procedures cost, fully paid, or already in installment
                                if ($due <= 0 || $balance <= 0 || $inInstallment) {
                                    continue;
                                }

                                $shownVisitCount++;

                                $pName = trim(($visit->patient?->first_name ?? '').' '.($visit->patient?->last_name ?? '')) ?: 'Patient';
                                $dateLabel = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') : '—';
                            @endphp

                            <option value="{{ $visit->id }}"
                                data-type="visit"
                                data-treatments="{{ $treatmentsPreview }}"
                                data-amount="{{ $balance }}"
                                {{ old('visit_id') == $visit->id ? 'selected' : '' }}
                            >
                                Visit - {{ $pName }} ({{ $dateLabel }}) — Balance ₱{{ number_format($balance, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <div class="helper">
                        @if($shownVisitCount === 0)
                            No unpaid visits available.
                        @else
                            Shows only visits that still have a remaining balance (not fully paid, not in installment).
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
                    <div class="helper">Choose an appointment to pay for. Paying an appointment will create a visit automatically.</div>
                </div>

                {{-- Cost --}}
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Cost <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="amountInput" name="amount" class="inputx"
                           value="{{ old('amount') }}" required>
                    <div class="helper">Auto-filled from selection (balance for visits). You can override if needed.</div>
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

                    {{-- keep value for old() by submitting it --}}
                    <input type="hidden" name="treatments_preview" id="treatmentsHidden" value="{{ old('treatments_preview') }}">

                    <textarea id="treatmentsBox" class="textareax readonlyx" rows="3" readonly>{{ old('treatments_preview') }}</textarea>
                    <div class="helper">Readonly: updates based on selection.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Submit Payment
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
    const visitSelect = document.getElementById('visitSelect');
    const appointmentSelect = document.getElementById('appointmentSelect');
    const amountInput = document.getElementById('amountInput');
    const treatmentsBox = document.getElementById('treatmentsBox');
    const treatmentsHidden = document.getElementById('treatmentsHidden');

    function updateFields(optionEl) {
        if (!optionEl) return;

        const amt = parseFloat(optionEl.getAttribute('data-amount') || 0);
        if (amountInput) amountInput.value = isFinite(amt) ? amt.toFixed(2) : '0.00';

        const tx = optionEl.getAttribute('data-treatments') || '';
        if (treatmentsBox) treatmentsBox.value = tx;
        if (treatmentsHidden) treatmentsHidden.value = tx;
    }

    function clearFieldsIfNone() {
        const hasAny = (visitSelect && visitSelect.value) || (appointmentSelect && appointmentSelect.value);
        if (!hasAny) {
            if (treatmentsBox) treatmentsBox.value = '';
            if (treatmentsHidden) treatmentsHidden.value = '';
        }
    }

    if (visitSelect) {
        visitSelect.addEventListener('change', function() {
            if (this.value !== "") {
                if (appointmentSelect) appointmentSelect.value = "";
                updateFields(this.selectedOptions[0]);
            } else {
                clearFieldsIfNone();
            }
        });
    }

    if (appointmentSelect) {
        appointmentSelect.addEventListener('change', function() {
            if (this.value !== "") {
                if (visitSelect) visitSelect.value = "";
                updateFields(this.selectedOptions[0]);
            } else {
                clearFieldsIfNone();
            }
        });
    }

    window.addEventListener('load', () => {
        if (visitSelect && visitSelect.value) updateFields(visitSelect.selectedOptions[0]);
        if (appointmentSelect && appointmentSelect.value) updateFields(appointmentSelect.selectedOptions[0]);
    });
})();
</script>

@endsection
