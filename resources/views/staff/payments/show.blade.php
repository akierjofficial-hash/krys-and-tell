@extends('layouts.staff')

@section('title', 'Payment Details')

@section('content')

<style>
    /* ==========================================================
       Payment Show (Dark mode compatible)
       Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                         --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --p-border: 1px solid var(--kt-border);
        --p-shadow: var(--kt-shadow);

        --p-text: var(--kt-text);
        --p-muted: var(--kt-muted);

        --p-soft: rgba(148,163,184,.14);
        --p-soft2: rgba(148,163,184,.18);

        --p-radius: 16px;
        --p-brand: #0d6efd;
        --p-brand2:#1e90ff;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);
    }
    html[data-theme="dark"]{
        --p-soft: rgba(148,163,184,.16);
        --p-soft2: rgba(148,163,184,.20);
    }

    /* Header */
    .p-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .p-title{
        font-size: 26px;
        font-weight: 950;
        letter-spacing: -0.3px;
        margin: 0;
        color: var(--p-text);
    }
    .p-subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--p-muted);
    }
    .p-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .p-btn{
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
        color: var(--p-text) !important;
        transition: .15s ease;
        white-space: nowrap;
        user-select:none;
    }
    .p-btn:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .p-btn:hover{
        background: rgba(2,6,23,.35);
    }

    .p-btn-primary{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, var(--p-brand), var(--p-brand2));
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
    }
    .p-btn-primary:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .22);
        background: linear-gradient(135deg, var(--p-brand), var(--p-brand2));
    }

    /* Main card */
    .p-card{
        background: var(--kt-surface);
        border: var(--p-border);
        border-radius: var(--p-radius);
        box-shadow: var(--p-shadow);
        overflow: hidden;
        backdrop-filter: blur(8px);
        min-width: 0;
    }
    .p-card-head{
        padding: 14px 16px;
        border-bottom: 1px solid var(--p-soft);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .p-card-head-left{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width:0;
    }
    .p-ref{
        font-weight: 950;
        letter-spacing: .3px;
        color: var(--p-text);
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .p-meta{
        font-size: 12px;
        color: var(--p-muted);
        font-weight: 800;
    }

    .p-badge{
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
    .p-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .p-paid{ background: rgba(34, 197, 94, .14); color:#22c55e; border-color: rgba(34,197,94,.25); }
    .p-info{ background: rgba(96,165,250,.14); color:#60a5fa; border-color: rgba(96,165,250,.25); }

    .p-card-body{ padding: 16px; }

    /* Two-column content */
    .p-split{
        display:grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 14px;
        align-items:start;
        min-width: 0;
    }
    @media (max-width: 900px){
        .p-split{ grid-template-columns: 1fr; }
    }

    .p-panel{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        border-radius: 14px;
        padding: 14px;
        min-width: 0;
    }

    .p-section-title{
        font-size: 12px;
        font-weight: 950;
        color: var(--p-muted);
        text-transform: uppercase;
        letter-spacing: .25px;
        margin-bottom: 10px;
    }

    /* Key-values */
    .p-kv{
        display:grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 12px;
        font-size: 13px;
        line-height: 1.35;
        min-width: 0;
    }
    @media (max-width: 520px){
        .p-kv{ grid-template-columns: 1fr; }
        .p-k{ margin-top: 4px; }
    }
    .p-k{ color: var(--p-muted); font-weight: 900; }
    .p-v{ color: var(--p-text); font-weight: 900; word-break: break-word; min-width:0; }

    .p-amount{
        font-size: 22px;
        font-weight: 950;
        color: var(--p-text);
        letter-spacing: -0.2px;
        font-variant-numeric: tabular-nums;
    }
    .p-small{
        margin-top: 6px;
        font-size: 12px;
        color: var(--p-muted);
        font-weight: 800;
    }

    /* Notes */
    .p-notes{
        margin-top: 14px;
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
    }
    .p-notes-row{
        padding: 12px 14px;
        border-top: 1px solid var(--p-soft);
    }
    .p-notes-row:first-child{ border-top: 0; }
    .p-notes-h{
        font-weight: 950;
        color: var(--p-text);
        font-size: 13px;
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .p-notes-b{
        font-size: 13px;
        color: var(--p-text);
        font-weight: 800;
        opacity: .9;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .dash{ color: var(--p-muted); font-weight: 900; }

    /* Table */
    .p-table-wrap{
        margin-top: 14px;
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
    }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--p-muted);
        padding: 13px 14px;
        border-bottom: 1px solid var(--p-soft);
        background: rgba(148,163,184,.12);
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{
        background: rgba(2,6,23,.35);
    }
    tbody td{
        padding: 13px 14px;
        font-size: 14px;
        color: var(--p-text);
        border-bottom: 1px solid var(--p-soft);
        vertical-align: top;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    tbody tr:hover td{ background: rgba(96,165,250,.08); }

    .text-end{ text-align:right; }
    .nowrap{ white-space: nowrap; }
    .money{ font-variant-numeric: tabular-nums; }

    .svc{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.20);
        background: rgba(148,163,184,.12);
        font-weight: 950;
        font-size: 13px;
        color: var(--p-text);
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    html[data-theme="dark"] .svc{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.22);
    }
</style>

@php
    $visit = $payment->visit;
    $patient = $visit?->patient;

    $patientName = trim(($patient?->first_name ?? '').' '.($patient?->last_name ?? ''));
    $patientName = $patientName !== '' ? $patientName : 'N/A';

    $paymentDate = $payment->payment_date
        ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y')
        : '—';

    $visitDate = $visit?->visit_date
        ? \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y')
        : '—';

    $procedures = $visit?->procedures ?? collect();
    $computedTotal = (float) $procedures->sum('price');
    $amountPaid = (float) ($payment->amount ?? 0);
    $totalShown = $computedTotal > 0 ? $computedTotal : $amountPaid;

    $receiptNo = 'PMT-' . str_pad((string)($payment->id ?? 0), 6, '0', STR_PAD_LEFT);

    $visitNotes = trim((string)($visit?->notes ?? ''));
    $paymentNotes = trim((string)($payment->notes ?? ''));
@endphp

<div class="p-head">
    <div>
        <h2 class="p-title">Payment Details</h2>
        <p class="p-subtitle">Simple view of payment, visit, and treatments.</p>
    </div>

    <div class="p-actions">
        <x-back-button
            fallback="{{ route('staff.payments.index') }}"
            class="p-btn"
            label="Back"
        />

        @if($visit)
            <a href="{{ route('staff.visits.show', [$visit->id, 'return' => url()->full()]) }}" class="p-btn">
                <i class="fa fa-eye"></i> View Visit
            </a>
        @endif

        <a href="{{ route('staff.payments.edit', [$payment->id, 'return' => url()->full()]) }}" class="p-btn p-btn-primary">
            <i class="fa fa-pen"></i> Edit
        </a>
    </div>
</div>

<div class="p-card">
    <div class="p-card-head">
        <div class="p-card-head-left">
            <div class="p-ref">
                <i class="fa fa-receipt"></i> {{ $receiptNo }}
            </div>
            <span class="p-badge p-paid"><span class="p-dot"></span> PAID</span>
        </div>

        <div class="p-meta">
            Payment Date: <strong>{{ $paymentDate }}</strong>
        </div>
    </div>

    <div class="p-card-body">

        <div class="p-split">
            <div class="p-panel">
                <div class="p-section-title">Details</div>

                <div class="p-kv">
                    <div class="p-k">Patient</div>
                    <div class="p-v">{{ $patientName }}</div>

                    <div class="p-k">Contact</div>
                    <div class="p-v">{{ $patient?->contact_number ?: '—' }}</div>

                    <div class="p-k">Address</div>
                    <div class="p-v">{{ $patient?->address ?: '—' }}</div>

                    <div class="p-k">Method</div>
                    <div class="p-v">{{ $payment->method ?: '—' }}</div>

                    <div class="p-k">Visit Date</div>
                    <div class="p-v">{{ $visitDate }}</div>

                    <div class="p-k">Visit Status</div>
                    <div class="p-v">
                        <span class="p-badge p-info">
                            <span class="p-dot"></span> {{ strtoupper($visit?->status ?? '—') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-panel">
                <div class="p-section-title">Amount</div>

                <div class="p-amount">₱{{ number_format($amountPaid, 2) }}</div>

                <div class="p-small">
                    Total shown: <strong class="money">₱{{ number_format($totalShown, 2) }}</strong><br>
                    @if($computedTotal > 0)
                        Procedures total: <strong class="money">₱{{ number_format($computedTotal, 2) }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-notes">
            <div class="p-notes-row">
                <div class="p-notes-h"><i class="fa fa-notes-medical"></i> Visit Notes</div>
                <div class="p-notes-b">{{ $visitNotes !== '' ? $visitNotes : '—' }}</div>
            </div>
            <div class="p-notes-row">
                <div class="p-notes-h"><i class="fa fa-comment-dots"></i> Payment Notes</div>
                <div class="p-notes-b">{{ $paymentNotes !== '' ? $paymentNotes : '—' }}</div>
            </div>
        </div>

        <div class="p-table-wrap table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 240px;">Treatment</th>
                        <th style="width: 90px;">Tooth</th>
                        <th style="width: 90px;">Surface</th>
                        <th>Notes</th>
                        <th class="text-end" style="width: 120px;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procedures as $p)
                        @php $pnote = trim((string)($p->notes ?? '')); @endphp
                        <tr>
                            <td>
                                <span class="svc">
                                    <i class="fa fa-stethoscope"></i>
                                    {{ $p->service?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="nowrap" style="color:var(--p-muted); font-weight:900;">
                                {{ $p->tooth_number ? ('#'.$p->tooth_number) : '—' }}
                            </td>
                            <td class="nowrap" style="color:var(--p-muted); font-weight:900;">
                                {{ $p->surface ?? '—' }}
                            </td>
                            <td style="color:var(--p-text); font-weight:800; opacity:.9;">
                                {{ $pnote !== '' ? $pnote : '—' }}
                            </td>
                            <td class="text-end money" style="font-weight:950;">
                                ₱{{ number_format((float)($p->price ?? 0), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No treatments found for this payment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
