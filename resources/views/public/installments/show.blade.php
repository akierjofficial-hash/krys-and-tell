@extends('layouts.public')
@section('title', 'Installment Plan')

@section('content')

<style>
    /* ==========================================================
       Public Installment Show (read-only)
       Inspired by staff/payments/installment/show.blade.php
       ========================================================== */

    :root{
        --i-text: var(--text);
        --i-muted: var(--muted);
        --i-surface: var(--card);
        --i-surface-2: rgba(255,255,255,.72);
        --i-border: 1px solid var(--border);
        --i-shadow: var(--shadow);
        --i-radius: 22px;
        --i-soft: rgba(176, 124, 88, .10);
    }

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
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.35px;
        margin: 0;
        color: var(--i-text);
    }
    .i-subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--i-muted);
        font-weight: 650;
        line-height: 1.6;
    }

    .i-card{
        background: var(--i-surface);
        border: var(--i-border);
        border-radius: var(--i-radius);
        box-shadow: var(--i-shadow);
        overflow: hidden;
        width: 100%;
        min-width:0;
    }
    .i-card-head{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(17,17,17,.10);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
        background: linear-gradient(180deg, rgba(176,124,88,.08), transparent);
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
        letter-spacing: .25px;
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

    .i-card-body{ padding: 16px; }

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
        border: 1px solid rgba(17,17,17,.10);
        background: var(--i-surface-2);
        border-radius: 18px;
        padding: 14px;
        min-width:0;
    }

    .i-section-title{
        font-size: 12px;
        font-weight: 950;
        color: rgba(23, 23, 23, .60);
        text-transform: uppercase;
        letter-spacing: .25px;
        margin-bottom: 10px;
    }

    .i-kv{
        display:grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 12px;
        font-size: 13px;
        line-height: 1.35;
        min-width:0;
    }
    .i-k{ color: rgba(23, 23, 23, .60); font-weight: 900; }
    .i-v{ color: var(--i-text); font-weight: 900; word-break: break-word; min-width:0; }

    .i-amount{
        font-size: 22px;
        font-weight: 950;
        color: var(--i-text);
        letter-spacing: -0.25px;
    }
    .i-small{
        margin-top: 6px;
        font-size: 12px;
        color: var(--i-muted);
        font-weight: 750;
        line-height: 1.7;
    }

    .i-table-wrap{
        margin-top: 14px;
        border: 1px solid rgba(17,17,17,.10);
        border-radius: 18px;
        overflow: hidden;
        background: var(--i-surface);
        min-width:0;
    }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(23, 23, 23, .60);
        padding: 13px 14px;
        border-bottom: 1px solid rgba(17,17,17,.10);
        background: rgba(176, 124, 88, .08);
        white-space: nowrap;
    }
    tbody td{
        padding: 13px 14px;
        font-size: 14px;
        color: var(--i-text);
        border-bottom: 1px solid rgba(17,17,17,.08);
        vertical-align: top;
    }
    tbody tr:last-child td{ border-bottom: none; }

    .text-end{ text-align:right; }
    .muted{ color: rgba(23,23,23,.70); font-weight:800; }

    .i-help{
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 18px;
        border: 1px dashed rgba(176,124,88,.40);
        background: rgba(176,124,88,.06);
        color: rgba(23,23,23,.80);
        font-weight: 650;
        line-height: 1.6;
        font-size: 13px;
    }
</style>

@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: (auth()->user()->name ?? 'N/A');

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
            $pd  = $p->payment_date ? Carbon::parse($p->payment_date)->toDateString() : null;
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

<section class="section section-soft">
    <div class="container">

        <div class="i-head">
            <div>
                <h2 class="i-title">Installment Plan</h2>
                <p class="i-subtitle">View your plan summary and payments. If something looks incorrect, message the clinic.</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('public.installments.index') }}" class="btn kt-btn kt-btn-outline">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="i-card">
            <div class="i-card-head">
                <div class="i-card-head-left">
                    <div class="i-ref"><i class="fa-solid fa-layer-group"></i> {{ $refNo }}</div>

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
                            <div class="i-v">{{ $isOpen ? 'Open Contract (Unlimited)' : ($months . ' month(s)') }}</div>
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
                                <th>Notes</th>
                                <th style="width:120px;">Method</th>
                                <th style="width:120px;" class="text-end">Amount</th>
                                <th style="width:110px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>

                            {{-- ✅ DP Row --}}
                            @if($showDpRow)
                                <tr>
                                    <td style="font-weight:950;">DP</td>
                                    <td class="muted">{{ $dpDate ? $dpDate->format('M d, Y') : '—' }}</td>
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
                                </tr>
                            @endif

                            {{-- ✅ OPEN CONTRACT: list Payment #1, #2, #3... --}}
                            @if($isOpen)
                                @if($openPayments->isEmpty())
                                    <tr>
                                        <td colspan="6" class="muted">No payments yet (besides downpayment).</td>
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
                                        </tr>
                                    @endfor
                                @else
                                    <tr>
                                        <td colspan="6" class="muted">No monthly installments configured.</td>
                                    </tr>
                                @endif
                            @endif

                        </tbody>
                    </table>
                </div>

                <div class="i-help">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    This page is for viewing only. If you need corrections or want an official receipt, please contact the clinic.
                </div>

            </div>
        </div>

    </div>
</section>

@endsection
