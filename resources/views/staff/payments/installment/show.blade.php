@extends('layouts.staff')

@section('title', 'Installment Plan')

@section('content')

<style>
    :root{
        --i-border: 1px solid rgba(15, 23, 42, .10);
        --i-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --i-text: #0f172a;
        --i-muted: rgba(15, 23, 42, .58);
        --i-soft: rgba(15, 23, 42, .05);
        --i-radius: 16px;
        --i-brand: #0d6efd;
    }

    /* Header */
    .i-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .i-title{
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.3px;
        margin: 0;
        color: var(--i-text);
    }
    .i-subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--i-muted);
    }
    .i-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .i-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15, 23, 42, .12);
        background: rgba(255,255,255,.88);
        color: rgba(15, 23, 42, .80);
        transition: .15s ease;
        white-space: nowrap;
    }
    .i-btn:hover{ background: rgba(15, 23, 42, .04); }

    .i-btn-primary{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
    }
    .i-btn-primary:hover{ transform: translateY(-1px); box-shadow: 0 14px 24px rgba(13, 110, 253, .22); }

    /* Main card */
    .i-card{
        background: rgba(255,255,255,.94);
        border: var(--i-border);
        border-radius: var(--i-radius);
        box-shadow: var(--i-shadow);
        overflow: hidden;
    }
    .i-card-head{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .i-card-head-left{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .i-ref{
        font-weight: 950;
        letter-spacing: .3px;
        color: var(--i-text);
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .i-meta{
        font-size: 12px;
        color: var(--i-muted);
        font-weight: 700;
    }

    .i-badge{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .i-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .st-paid{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .st-pending{ background: rgba(245, 158, 11, .12); color:#b45309; border-color: rgba(245,158,11,.25); }
    .st-info{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }

    .i-card-body{ padding: 16px; }

    /* Two-column summary */
    .i-split{
        display:grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 14px;
        align-items:start;
    }
    @media (max-width: 900px){
        .i-split{ grid-template-columns: 1fr; }
    }

    .i-panel{
        border: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248,250,252,.75);
        border-radius: 14px;
        padding: 14px;
    }

    .i-section-title{
        font-size: 12px;
        font-weight: 900;
        color: rgba(15, 23, 42, .55);
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
    }
    .i-k{ color: rgba(15, 23, 42, .55); font-weight: 800; }
    .i-v{ color: var(--i-text); font-weight: 800; word-break: break-word; }

    .i-amount{
        font-size: 20px;
        font-weight: 950;
        color: var(--i-text);
        letter-spacing: -0.2px;
    }
    .i-small{
        margin-top: 6px;
        font-size: 12px;
        color: var(--i-muted);
        font-weight: 700;
        line-height: 1.6;
    }

    /* Table */
    .i-table-wrap{
        margin-top: 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 14px;
        overflow: hidden;
        background: rgba(255,255,255,.95);
    }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 13px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .9);
        white-space: nowrap;
    }
    tbody td{
        padding: 13px 14px;
        font-size: 14px;
        color: var(--i-text);
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: top;
    }
    .text-end{ text-align:right; }
    .muted{ color: rgba(15,23,42,.65); font-weight:700; }
</style>

@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));
    $patientName = $patientName !== '' ? $patientName : 'N/A';

    $serviceName = $plan->service?->name ?? '—';

    $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
    $months = (int) ($plan->months ?? 0);

    $totalCost = (float) ($plan->total_cost ?? 0);
    $downpayment = (float) ($plan->downpayment ?? 0);

    // ✅ FIX: don’t double count downpayment if Month 1 payment exists
    $payments = $plan->payments ?? collect();
    $paymentsTotal = (float) $payments->sum('amount');
    $hasMonth1Payment = $payments->contains(function ($p) {
        return (int)($p->month_number ?? 0) === 1;
    });

    $paidAmount = $paymentsTotal + ($hasMonth1Payment ? 0 : $downpayment);
    $remaining = max(0, $totalCost - $paidAmount);

    // status display
    $status = strtoupper(trim((string)($plan->status ?? 'PENDING')));
    $isPaid = $remaining <= 0; // use computed remaining, not status text

    $refNo = 'INST-' . str_pad((string)($plan->id ?? 0), 6, '0', STR_PAD_LEFT);

    // helpful lookup by month_number
    $paymentsByMonth = ($payments)->keyBy('month_number');
@endphp

<div class="i-head">
    <div>
        <h2 class="i-title">Installment Plan</h2>
        <p class="i-subtitle">Simple view of plan summary and monthly payments.</p>
    </div>

    <div class="i-actions">
        <x-back-button
            fallback="{{ route('staff.payments.index', ['tab' => 'installment']) }}"
            class="i-btn"
            label="Back"
        />

        @if(!$isPaid)
            <a href="{{ route('staff.installments.pay', [$plan->id, 'return' => url()->full()]) }}" class="i-btn i-btn-primary">
                <i class="fa fa-circle-dollar-to-slot"></i> Pay
            </a>
        @endif

        <a href="{{ route('staff.installments.edit', [$plan->id, 'return' => url()->full()]) }}" class="i-btn">
            <i class="fa fa-pen"></i> Edit
        </a>

        @if($plan->visit_id)
            <a href="{{ route('staff.visits.show', [$plan->visit_id, 'return' => url()->full()]) }}" class="i-btn">
                <i class="fa fa-eye"></i> View Visit
            </a>
        @endif
    </div>
</div>

<div class="i-card">
    <div class="i-card-head">
        <div class="i-card-head-left">
            <div class="i-ref"><i class="fa fa-layer-group"></i> {{ $refNo }}</div>
            <span class="i-badge {{ $isPaid ? 'st-paid' : 'st-pending' }}">
                <span class="i-dot"></span> {{ $isPaid ? 'FULLY PAID' : ($status !== '' ? $status : 'PENDING') }}
            </span>
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
                    <div class="i-v">{{ $months }} month(s)</div>
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
                        <th style="width:90px;">Month</th>
                        <th style="width:140px;">Date</th>
                        <th>Notes</th>
                        <th style="width:120px;">Method</th>
                        <th style="width:120px;" class="text-end">Amount</th>
                        <th style="width:110px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i=1; $i <= max(1,$months); $i++)
                        @php
                            $due = $startDate ? $startDate->copy()->addMonths($i - 1) : null;
                            $pay = $paymentsByMonth->get($i);

                            $paidDate = $pay?->payment_date ? Carbon::parse($pay->payment_date) : null;
                            $showDate = ($paidDate ?? $due);

                            // Downpayment shown on month 1 if no record
                            if ($i === 1) {
                                $amount = $pay?->amount ?? $downpayment;
                            } else {
                                $amount = $pay?->amount ?? null;
                            }

                            $notes = trim((string)($pay?->notes ?? ''));
                            if ($notes === '' && $pay?->visit_id) {
                                $notes = 'Visit #' . $pay->visit_id;
                            }

                            $rowPaid = $pay || ($i === 1 && $downpayment > 0);
                        @endphp

                        <tr>
                            <td style="font-weight:900;">{{ $i }}</td>
                            <td class="muted">{{ $showDate ? $showDate->format('M d, Y') : '—' }}</td>
                            <td class="muted">{{ $notes !== '' ? $notes : '—' }}</td>
                            <td class="muted">{{ $pay?->method ?? '—' }}</td>
                            <td class="text-end" style="font-weight:900;">
                                {{ $amount ? '₱'.number_format((float)$amount, 2) : '—' }}
                            </td>
                            <td>
                                <span class="i-badge {{ $rowPaid ? 'st-paid' : 'st-pending' }}">
                                    <span class="i-dot"></span> {{ $rowPaid ? 'PAID' : 'PENDING' }}
                                </span>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
