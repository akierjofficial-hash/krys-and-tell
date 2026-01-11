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
        background: linear-gradient(135deg, #16a34a, #22c55e);
        box-shadow: 0 10px 18px rgba(34,197,94,.18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(34,197,94,.24);
        color:#fff;
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

    .summary-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }
    @media (min-width: 768px){
        .summary-grid{ grid-template-columns: repeat(2, 1fr); }
    }

    .tile{
        border: 1px solid rgba(15,23,42,.10);
        border-radius: 14px;
        background: rgba(248,250,252,.9);
        padding: 12px 12px;
    }
    .tile .k{
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.58);
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .tile .v{
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
        word-break: break-word;
    }
    .balance{
        color: #dc2626;
        font-weight: 1000;
    }

    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
        margin-bottom: 6px;
    }
    .inputx, .selectx{
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
    .inputx:focus, .selectx:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }
    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(59,130,246,.22);
        background: rgba(59,130,246,.10);
        color: rgba(15,23,42,.85);
        white-space: nowrap;
    }

    .form-max{ max-width: 1100px; }
</style>

@php
    use Carbon\Carbon;

    $payments = $plan->payments ?? collect();
    $paymentsTotal = (float) $payments->sum('amount');

    $total = (float) ($plan->total_cost ?? 0);
    $down  = (float) ($plan->downpayment ?? 0);

    // ✅ DP detect (month 0 or legacy month 1 downpayment)
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

    // ✅ UI months count (shift legacy only)
    $shift = $dpIsLegacyMonth1 ? 1 : 0;
    $maxMonths = $maxMonths ?? max(0, (int)($plan->months ?? 0));

    $hasDpRecord = (bool) $dpPayment;
    $totalPaid = $paymentsTotal + ($hasDpRecord ? 0 : $down);

    $remainingBalance = max(0, $total - $totalPaid);
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

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">
            <span class="badge-soft"><i class="fa fa-file-invoice"></i> Plan ID: #{{ $plan->id }}</span>
            <span class="badge-soft"><i class="fa fa-calendar"></i>
    Term: {{ ($plan->is_open_contract ?? false) ? 'Open Contract' : ($plan->months.' months') }}
</span>

        </div>
        <div class="hint">Make sure the amount does not exceed the remaining balance. A <strong>Visit</strong> entry will be auto-created for this payment.</div>
    </div>

    <div class="card-bodyx">

        <div class="summary-grid">
            <div class="tile">
                <div class="k"><i class="fa fa-user"></i> Patient Name</div>
                <div class="v">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-tooth"></i> Service / Treatment</div>
                <div class="v">{{ $plan->service?->name ?? '—' }}</div>
                <div class="helper">Use the <strong>Notes</strong> field below for braces details (upper/lower wire, recementation, adjustments, etc.).</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-peso-sign"></i> Total Treatment Cost</div>
                <div class="v">₱{{ number_format($plan->total_cost, 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-arrow-down"></i> Downpayment</div>
                <div class="v">₱{{ number_format($plan->downpayment, 2) }}</div>
            </div>

            <div class="tile">
                <div class="k"><i class="fa fa-circle-exclamation"></i> Remaining Balance</div>
                <div class="v balance">₱{{ number_format($remainingBalance, 2) }}</div>
            </div>
        </div>

        <form action="{{ route('staff.installments.pay.store', $plan) }}" method="POST">
            @csrf

            @php $isOpen = (bool)($plan->is_open_contract ?? false); @endphp

@if($isOpen)
    <div class="col-12 col-md-6">
        <label class="form-labelx">Payment No.</label>
        <input class="inputx" value="Payment {{ $nextMonth ?? 1 }}" disabled>
        <input type="hidden" name="month_number" value="{{ $nextMonth ?? 1 }}">
        <div class="helper">Open contract: payments auto-increment (no fixed months).</div>
    </div>
@else
    <div class="col-12 col-md-6">
        <label class="form-labelx">Month Paid <span class="text-danger">*</span></label>
        <select name="month_number" class="selectx" required>
            @for($i = 1; $i <= $maxMonths; $i++)
                @php $isPaid = isset($paidMonths) && $paidMonths->contains($i); @endphp
                <option value="{{ $i }}"
                    {{ $isPaid ? 'disabled' : '' }}
                    {{ (int)old('month_number', $nextMonth ?? 1) === $i ? 'selected' : '' }}
                >
                    Month {{ $i }}{{ $isPaid ? ' (paid)' : '' }}
                </option>
            @endfor
        </select>
        <div class="helper">Only unpaid months can be selected.</div>
    </div>
@endif


                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount Paid <span class="text-danger">*</span></label>
                    <input
                        type="number"
                        name="amount"
                        class="inputx"
                        step="0.01"
                        min="0"
                        max="{{ $remainingBalance }}"
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
                    <textarea name="notes" rows="3" class="inputx" placeholder="e.g. Upper wire changed, recementation on #11, patient complains of soreness...">{{ old('notes') }}</textarea>
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
