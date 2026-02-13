@extends('layouts.staff')

@section('title', 'Installment Plan')

@section('content')

<style>
    /* ==========================================================
       Installment Show (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --i-border: 1px solid var(--kt-border);
        --i-shadow: var(--kt-shadow);
        --i-text: var(--kt-text);
        --i-muted: var(--kt-muted);
        --i-soft: rgba(148,163,184,.14);
        --i-radius: 16px;
    }
    html[data-theme="dark"]{
        --i-soft: rgba(148,163,184,.16);
    }

    /* Header */
    .i-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
        min-width:0;
    }
    .i-title{
        font-size: 26px;
        font-weight: 950;
        letter-spacing: -0.3px;
        margin: 0;
        color: var(--i-text);
    }
    .i-subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--i-muted);
        font-weight: 700;
    }
    .i-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
    }

    /* Buttons */
    .i-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--kt-text) !important;
        transition: .15s ease;
        white-space: nowrap;
        user-select:none;
        cursor: pointer;
    }
    .i-btn:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .i-btn:hover{
        background: rgba(2,6,23,.35);
    }

    .i-btn-primary{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
    }
    .i-btn-primary:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .22);
    }

    /* Card */
    .i-card{
        background: var(--kt-surface);
        border: var(--i-border);
        border-radius: var(--i-radius);
        box-shadow: var(--i-shadow);
        overflow: hidden;
        width: 100%;
        min-width:0;
    }
    .i-card-head{
        padding: 14px 16px;
        border-bottom: 1px solid var(--kt-border);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
    }
    .i-card-head-left{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
    }
    .i-ref{
        font-weight: 950;
        letter-spacing: .3px;
        color: var(--i-text);
        display:flex;
        align-items:center;
        gap: 8px;
        min-width:0;
    }
    .i-meta{
        font-size: 12px;
        color: var(--i-muted);
        font-weight: 800;
        min-width:0;
    }

    /* Badges */
    .i-badge{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .i-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .st-paid{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .st-pending{ background: rgba(245, 158, 11, .12); color:#b45309; border-color: rgba(245,158,11,.25); }
    .st-info{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }

    html[data-theme="dark"] .st-paid{ background: rgba(34,197,94,.14); color: rgba(134,239,172,.95); border-color: rgba(34,197,94,.22); }
    html[data-theme="dark"] .st-pending{ background: rgba(245,158,11,.14); color: rgba(253,230,138,.95); border-color: rgba(245,158,11,.22); }
    html[data-theme="dark"] .st-info{ background: rgba(59,130,246,.14); color: rgba(191,219,254,.95); border-color: rgba(59,130,246,.22); }

    .i-card-body{ padding: 16px; }

    /* Split panels */
    .i-split{
        display:grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 14px;
        align-items:start;
        min-width:0;
    }
    @media (max-width: 900px){
        .i-split{ grid-template-columns: 1fr; }
    }

    .i-panel{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        border-radius: 14px;
        padding: 14px;
        min-width:0;
    }

    .i-section-title{
        font-size: 12px;
        font-weight: 950;
        color: rgba(15, 23, 42, .55);
        text-transform: uppercase;
        letter-spacing: .25px;
        margin-bottom: 10px;
    }
    html[data-theme="dark"] .i-section-title{ color: rgba(226,232,240,.62); }

    /* Key-values */
    .i-kv{
        display:grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 12px;
        font-size: 13px;
        line-height: 1.35;
        min-width:0;
    }
    .i-k{ color: rgba(15, 23, 42, .55); font-weight: 900; }
    html[data-theme="dark"] .i-k{ color: rgba(226,232,240,.62); }

    .i-v{ color: var(--i-text); font-weight: 900; word-break: break-word; min-width:0; }

    .i-amount{
        font-size: 20px;
        font-weight: 1000;
        color: var(--i-text);
        letter-spacing: -0.2px;
    }
    .i-small{
        margin-top: 6px;
        font-size: 12px;
        color: var(--i-muted);
        font-weight: 800;
        line-height: 1.6;
    }

    /* Table */
    .i-table-wrap{
        margin-top: 14px;
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
        min-width:0;
    }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 13px 14px;
        border-bottom: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{ color: rgba(226,232,240,.62); }

    tbody td{
        padding: 13px 14px;
        font-size: 14px;
        color: var(--i-text);
        border-bottom: 1px solid rgba(148,163,184,.18);
        vertical-align: top;
    }
    html[data-theme="dark"] tbody td{
        border-bottom: 1px solid rgba(148,163,184,.14);
    }

    .text-end{ text-align:right; }
    .muted{ color: rgba(15,23,42,.65); font-weight:800; }
    html[data-theme="dark"] .muted{ color: rgba(226,232,240,.70); }

    /* Row actions */
    .i-row-actions{
        display:flex;
        gap: 8px;
        justify-content:flex-end;
        flex-wrap: wrap;
    }
    .i-mini{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 950;
        text-decoration:none;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--kt-text) !important;
        white-space: nowrap;
        transition: .15s ease;
    }
    .i-mini:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .i-mini:hover{
        background: rgba(2,6,23,.35);
    }

    .i-mini-primary{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 8px 14px rgba(13,110,253,.16);
    }
    .i-mini-primary:hover{
        transform: translateY(-1px);
        box-shadow: 0 12px 18px rgba(13,110,253,.20);
    }
</style>

@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: 'N/A';

    $serviceName = $plan->service?->name ?? '—';

    $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
    $months = (int)($plan->months ?? 0);

    $isOpen = (bool)($plan->is_open_contract ?? false);

    $totalCost = (float)($plan->total_cost ?? 0);
    $downpayment = (float)($plan->downpayment ?? 0);

    $payments = $plan->payments ?? collect();

    // ✅ DP detection (new + legacy)
    $planStartStr = $startDate ? $startDate->toDateString() : null;

    $dpPayment = $payments->first(function ($p) use ($downpayment, $planStartStr) {
        $m = (int)($p->month_number ?? -1);
        $notes = strtolower((string)($p->notes ?? ''));

        if ($m === 0) return true;

        if ($m === 1) {
            if (str_contains($notes, 'downpayment')) return true;

            $amt = (float)($p->amount ?? 0);
            $pd  = $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->toDateString() : null;

            if ($downpayment > 0 && abs($amt - $downpayment) < 0.01 && $planStartStr && $pd === $planStartStr) {
                return true;
            }
        }

        return false;
    });

    $hasMonth0 = $payments->contains(fn($p) => (int)($p->month_number ?? -1) === 0);
    $dpIsLegacyMonth1 = (!$hasMonth0 && $dpPayment && (int)($dpPayment->month_number ?? -1) === 1);
    $shift = $dpIsLegacyMonth1 ? 1 : 0;

    // ✅ Balance calculation (DP counted only once)
    $paymentsTotal = (float)$payments->sum('amount');
    $hasDpRecord = (bool)$dpPayment;
    $paidAmount = $paymentsTotal + ($hasDpRecord ? 0 : $downpayment);
    $remaining = max(0, $totalCost - $paidAmount);

    $status = strtoupper(trim((string)($plan->status ?? 'PARTIALLY PAID')));
    $isCompleted = ($status === 'COMPLETED');
    $isPaid = $remaining <= 0;

    $refNo = 'INST-' . str_pad((string)($plan->id ?? 0), 6, '0', STR_PAD_LEFT);

    // DP display
    $showDpRow = ($downpayment > 0) || (bool)$dpPayment;
    $dpAmount = $dpPayment?->amount ?? ($downpayment > 0 ? $downpayment : null);
    $dpDate = $dpPayment?->payment_date
        ? Carbon::parse($dpPayment->payment_date)
        : ($startDate ? $startDate->copy() : null);
    $dpMethod = $dpPayment?->method ?? '—';
    $dpNotes = trim((string)($dpPayment?->notes ?? 'Downpayment'));
    if ($dpNotes === '') $dpNotes = 'Downpayment';

    // Fixed-term map
    $paymentsByMonth = $payments
        ->filter(fn($p) => (int)($p->month_number ?? -1) >= 1)
        ->keyBy('month_number');

    // Open-contract list (exclude DP / legacy shift)
    $openPayments = $payments
        ->filter(function($p) use ($shift){
            return (int)($p->month_number ?? -1) >= (1 + $shift);
        })
        ->sortBy(fn($p) => (int)($p->month_number ?? 0))
        ->values();
@endphp

<div class="i-head">
    <div>
        <h2 class="i-title">Installment Plan</h2>
        <p class="i-subtitle">View plan summary and payments. You can edit payments if needed.</p>
    </div>

    <div class="i-actions">
        <x-back-button
            fallback="{{ route('staff.payments.index', ['tab' => 'installment']) }}"
            class="i-btn"
            label="Back"
        />

        @if(!$isPaid && !$isCompleted)
            <a href="{{ route('staff.installments.pay', [$plan->id, 'return' => url()->full()]) }}" class="i-btn i-btn-primary">
                <i class="fa fa-circle-dollar-to-slot"></i> Pay
            </a>
        @endif

        @if($isOpen)
            @if(!$isCompleted)
                <form action="{{ route('staff.installments.complete', $plan) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Mark this Open Contract plan as COMPLETED? This will stop payments even if balance is not fully paid.');">
                    @csrf
                    <button type="submit" class="i-btn" title="Mark plan as completed">
                        <i class="fa fa-circle-check"></i> Mark Completed
                    </button>
                </form>
            @else
                <form action="{{ route('staff.installments.reopen', $plan) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Reopen this plan?');">
                    @csrf
                    <button type="submit" class="i-btn" title="Reopen plan">
                        <i class="fa fa-rotate-left"></i> Reopen
                    </button>
                </form>
            @endif
        @endif

        <a href="{{ route('staff.installments.payments.template', $plan) }}" class="i-btn" title="Download Excel template">
            <i class="fa fa-file-excel"></i> Template
        </a>

        <form id="installmentPaymentsImportForm"
              action="{{ route('staff.installments.payments.import', $plan) }}"
              method="POST" enctype="multipart/form-data" style="display:inline;">
            @csrf
            <input id="installmentPaymentsImportFile" type="file" name="file" accept=".xlsx,.xls,.csv" style="display:none" required>
            <button type="button" id="installmentPaymentsImportBtn" class="i-btn" title="Import payments from Excel">
                <i class="fa fa-cloud-arrow-up"></i> Import
            </button>
        </form>

        <a href="{{ route('staff.installments.edit', [$plan->id, 'return' => url()->full()]) }}" class="i-btn">
            <i class="fa fa-pen"></i> Edit Plan
        </a>

        @if($plan->visit_id)
            <a href="{{ route('staff.visits.show', [$plan->visit_id, 'return' => url()->full()]) }}" class="i-btn">
                <i class="fa fa-eye"></i> View Visit
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius:12px; font-weight:800;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="border-radius:12px; font-weight:800;">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="border-radius:12px;">
        <ul style="margin:0; padding-left:18px;">
            @foreach($errors->all() as $e)
                <li style="font-weight:800;">{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('import_warnings') && is_array(session('import_warnings')))
    <div class="alert alert-warning" style="border-radius:12px;">
        <div style="font-weight:900; margin-bottom:6px;">Import warnings (some rows skipped):</div>
        <ul style="margin:0; padding-left:18px;">
            @foreach(session('import_warnings') as $w)
                <li style="font-weight:800;">{{ $w }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="i-card">
    <div class="i-card-head">
        <div class="i-card-head-left">
            <div class="i-ref"><i class="fa fa-layer-group"></i> {{ $refNo }}</div>

            <span class="i-badge {{ $isCompleted ? 'st-info' : ($isPaid ? 'st-paid' : 'st-pending') }}">
                <span class="i-dot"></span>
                {{ $isCompleted ? 'COMPLETED' : ($isPaid ? 'FULLY PAID' : ($status !== '' ? $status : 'PENDING')) }}
            </span>

            @if($isOpen)
                <span class="i-badge st-info">
                    <span class="i-dot"></span> OPEN CONTRACT
                </span>
            @endif
        </div>

        <div class="i-meta">
            Start: <strong>{{ $startDate ? $startDate->format('M d, Y') : '—' }}</strong>
        </div>
    </div>

    <div class="i-card-body">

        <div class="i-split">
            <div class="i-panel">
                <div class="i-section-title">Details</div>

                <div class="i-kv">
                    <div class="i-k">Patient</div>
                    <div class="i-v">{{ $patientName }}</div>

                    <div class="i-k">Contact</div>
                    <div class="i-v">{{ $patient?->contact_number ?: '—' }}</div>

                    <div class="i-k">Treatment</div>
                    <div class="i-v">{{ $serviceName }}</div>

                    <div class="i-k">Term</div>
                    <div class="i-v">
                        {{ $isOpen ? 'Open Contract (Unlimited)' : ($months . ' month(s)') }}
                    </div>
                </div>
            </div>

            <div class="i-panel">
                <div class="i-section-title">Summary</div>

                <div class="i-amount">₱{{ number_format($remaining, 2) }}</div>
                <div class="i-small">
                    Remaining<br>
                    Total: <strong>₱{{ number_format($totalCost, 2) }}</strong><br>
                    Down: <strong>₱{{ number_format($downpayment, 2) }}</strong><br>
                    Paid: <strong>₱{{ number_format($paidAmount, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="i-table-wrap table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:120px;">{{ $isOpen ? 'Payment' : 'Month' }}</th>
                        <th style="width:140px;">Date</th>
                        <th style="width:180px;">Dentist</th>
                        <th>Notes</th>
                        <th style="width:120px;">Method</th>
                        <th style="width:120px;" class="text-end">Amount</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:160px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- ✅ DP Row --}}
                    @if($showDpRow)
                        <tr>
                            <td style="font-weight:950;">DP</td>
                            <td class="muted">{{ $dpDate ? $dpDate->format('M d, Y') : '—' }}</td>
                            <td class="muted">{{ $dpPayment?->visit?->dentist_name ?? $plan->visit?->dentist_name ?? '—' }}</td>
                            <td class="muted">{{ $dpNotes }}</td>
                            <td class="muted">{{ $dpMethod ?: '—' }}</td>
                            <td class="text-end" style="font-weight:950;">
                                {{ $dpAmount !== null ? '₱'.number_format((float)$dpAmount, 2) : '—' }}
                            </td>
                            <td>
                                <span class="i-badge {{ ($dpAmount !== null && (float)$dpAmount > 0) ? 'st-paid' : 'st-pending' }}">
                                    <span class="i-dot"></span> {{ ($dpAmount !== null && (float)$dpAmount > 0) ? 'PAID' : 'PENDING' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="i-row-actions">
                                    @if($dpPayment)
                                        <a class="i-mini"
                                           href="{{ route('staff.installments.payments.edit', [$plan->id, $dpPayment->id, 'return' => url()->full()]) }}">
                                            <i class="fa fa-pen"></i> Edit
                                        </a>

                                        @if($dpPayment->visit_id)
                                            <a class="i-mini"
                                               href="{{ route('staff.visits.show', [$dpPayment->visit_id, 'return' => url()->full()]) }}">
                                                <i class="fa fa-eye"></i> Visit
                                            </a>
                                        @endif
                                    @else
                                        <a class="i-mini"
                                           href="{{ route('staff.installments.edit', [$plan->id, 'return' => url()->full()]) }}">
                                            <i class="fa fa-pen"></i> Edit DP
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- ✅ OPEN CONTRACT --}}
                    @if($isOpen)
                        @if($openPayments->isEmpty())
                            <tr>
                                <td colspan="8" class="muted">No payments yet (besides downpayment).</td>
                            </tr>
                        @else
                            @foreach($openPayments as $p)
                                @php
                                    $uiNo = (int)($p->month_number ?? 0) - $shift;
                                    $pDate = $p->payment_date ? Carbon::parse($p->payment_date) : null;
                                    $notes = trim((string)($p->notes ?? ''));
                                    if ($notes === '' && $p->visit_id) $notes = 'Visit #' . $p->visit_id;
                                @endphp
                                <tr>
                                    <td style="font-weight:950;">Payment #{{ $uiNo }}</td>
                                    <td class="muted">{{ $pDate ? $pDate->format('M d, Y') : '—' }}</td>
                                    <td class="muted">{{ $p->visit?->dentist_name ?? '—' }}</td>
                                    <td class="muted">{{ $notes !== '' ? $notes : '—' }}</td>
                                    <td class="muted">{{ $p->method ?? '—' }}</td>
                                    <td class="text-end" style="font-weight:950;">
                                        {{ $p->amount !== null ? '₱'.number_format((float)$p->amount, 2) : '—' }}
                                    </td>
                                    <td>
                                        <span class="i-badge st-paid">
                                            <span class="i-dot"></span> PAID
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="i-row-actions">
                                            <a class="i-mini"
                                               href="{{ route('staff.installments.payments.edit', [$plan->id, $p->id, 'return' => url()->full()]) }}">
                                                <i class="fa fa-pen"></i> Edit
                                            </a>

                                            @if($p->visit_id)
                                                <a class="i-mini"
                                                   href="{{ route('staff.visits.show', [$p->visit_id, 'return' => url()->full()]) }}">
                                                    <i class="fa fa-eye"></i> Visit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    {{-- ✅ FIXED TERM --}}
                    @else
                        @php
                            $uiMonths = max(0, $months);
                            $hasDpForDue = $showDpRow;
                        @endphp

                        @if($uiMonths > 0)
                            @for($i = 1; $i <= $uiMonths; $i++)
                                @php
                                    $dbMonth = $i + $shift;
                                    $pay = $paymentsByMonth->get($dbMonth);

                                    $due = $startDate
                                        ? $startDate->copy()->addMonths(($i - 1) + ($hasDpForDue ? 1 : 0))
                                        : null;

                                    $paidDate = $pay?->payment_date ? Carbon::parse($pay->payment_date) : null;
                                    $showDate = ($paidDate ?? $due);

                                    $amount = $pay?->amount ?? null;

                                    $notes = trim((string)($pay?->notes ?? ''));
                                    if ($notes === '' && $pay?->visit_id) $notes = 'Visit #' . $pay->visit_id;

                                    $rowPaid = (bool) $pay;
                                @endphp

                                <tr>
                                    <td style="font-weight:950;">{{ $i }}</td>
                                    <td class="muted">{{ $showDate ? $showDate->format('M d, Y') : '—' }}</td>
                                    <td class="muted">{{ $pay?->visit?->dentist_name ?? '—' }}</td>
                                    <td class="muted">{{ $notes !== '' ? $notes : '—' }}</td>
                                    <td class="muted">{{ $pay?->method ?? '—' }}</td>
                                    <td class="text-end" style="font-weight:950;">
                                        {{ $amount !== null ? '₱'.number_format((float)$amount, 2) : '—' }}
                                    </td>
                                    <td>
                                        <span class="i-badge {{ $rowPaid ? 'st-paid' : 'st-pending' }}">
                                            <span class="i-dot"></span> {{ $rowPaid ? 'PAID' : 'PENDING' }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="i-row-actions">
                                            @if($pay)
                                                <a class="i-mini"
                                                   href="{{ route('staff.installments.payments.edit', [$plan->id, $pay->id, 'return' => url()->full()]) }}">
                                                    <i class="fa fa-pen"></i> Edit
                                                </a>

                                                @if($pay->visit_id)
                                                    <a class="i-mini"
                                                       href="{{ route('staff.visits.show', [$pay->visit_id, 'return' => url()->full()]) }}">
                                                        <i class="fa fa-eye"></i> Visit
                                                    </a>
                                                @endif
                                            @else
                                                @if(!$isCompleted)
                                                    <a class="i-mini i-mini-primary"
                                                       href="{{ route('staff.installments.pay', [$plan->id, 'month' => $i, 'return' => url()->full()]) }}">
                                                        <i class="fa fa-circle-dollar-to-slot"></i> Pay
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endfor
                        @else
                            <tr>
                                <td colspan="8" class="muted">No monthly installments configured.</td>
                            </tr>
                        @endif
                    @endif

                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
(() => {
    const btn  = document.getElementById('installmentPaymentsImportBtn');
    const file = document.getElementById('installmentPaymentsImportFile');
    const form = document.getElementById('installmentPaymentsImportForm');

    if (!btn || !file || !form) return;

    btn.addEventListener('click', () => file.click());
    file.addEventListener('change', () => {
        if (file.files && file.files.length) form.submit();
    });
})();
</script>

@endsection