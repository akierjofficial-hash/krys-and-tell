@extends('layouts.staff')

@section('title', 'Payment Details')

@section('content')

<style>
    :root{
        --p-border: 1px solid rgba(15, 23, 42, .10);
        --p-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --p-text: #0f172a;
        --p-muted: rgba(15, 23, 42, .58);
        --p-soft: rgba(15, 23, 42, .05);
        --p-radius: 16px;
        --p-brand: #0d6efd;
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
        font-weight: 900;
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
    }

    .p-btn{
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
    .p-btn:hover{ background: rgba(15, 23, 42, .04); }

    .p-btn-primary{
        border: none;
        color: #fff !important;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .18);
    }
    .p-btn-primary:hover{ transform: translateY(-1px); box-shadow: 0 14px 24px rgba(13, 110, 253, .22); }

    /* Main card */
    .p-card{
        background: rgba(255,255,255,.94);
        border: var(--p-border);
        border-radius: var(--p-radius);
        box-shadow: var(--p-shadow);
        overflow: hidden;
    }
    .p-card-head{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
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
        font-weight: 700;
    }

    .p-badge{
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
    .p-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .p-paid{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .p-info{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }

    .p-card-body{ padding: 16px; }

    /* Simple two-column content */
    .p-split{
        display:grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 14px;
        align-items:start;
    }
    @media (max-width: 900px){
        .p-split{ grid-template-columns: 1fr; }
    }

    .p-panel{
        border: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248,250,252,.75);
        border-radius: 14px;
        padding: 14px;
    }

    .p-section-title{
        font-size: 12px;
        font-weight: 900;
        color: rgba(15, 23, 42, .55);
        text-transform: uppercase;
        letter-spacing: .25px;
        margin-bottom: 10px;
    }

    /* Key-values (simple, not fancy) */
    .p-kv{
        display:grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 12px;
        font-size: 13px;
        line-height: 1.35;
    }
    .p-k{ color: rgba(15, 23, 42, .55); font-weight: 800; }
    .p-v{ color: var(--p-text); font-weight: 800; word-break: break-word; }

    .p-amount{
        font-size: 22px;
        font-weight: 950;
        color: var(--p-text);
        letter-spacing: -0.2px;
    }
    .p-small{
        margin-top: 6px;
        font-size: 12px;
        color: var(--p-muted);
        font-weight: 700;
    }

    /* Notes (one simple section) */
    .p-notes{
        margin-top: 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 14px;
        overflow: hidden;
        background: rgba(255,255,255,.92);
    }
    .p-notes-row{
        padding: 12px 14px;
        border-top: 1px solid rgba(15, 23, 42, .06);
    }
    .p-notes-row:first-child{ border-top: 0; }
    .p-notes-h{
        font-weight: 900;
        color: var(--p-text);
        font-size: 13px;
        margin-bottom: 6px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .p-notes-b{
        font-size: 13px;
        color: rgba(15, 23, 42, .78);
        font-weight: 700;
        white-space: pre-wrap;
        word-break: break-word;
    }

    /* Table */
    .p-table-wrap{
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
        color: var(--p-text);
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: top;
    }
    .text-end{ text-align:right; }
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
        <a href="{{ route('staff.payments.index') }}" class="p-btn">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        @if($visit)
            <a href="{{ route('staff.visits.show', $visit->id) }}" class="p-btn">
                <i class="fa fa-eye"></i> View Visit
            </a>
        @endif

        <a href="{{ route('staff.payments.edit', $payment->id) }}" class="p-btn p-btn-primary">
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
                        <span class="p-badge p-info"><span class="p-dot"></span> {{ strtoupper($visit?->status ?? '—') }}</span>
                    </div>
                </div>
            </div>

            <div class="p-panel">
                <div class="p-section-title">Amount</div>

                <div class="p-amount">₱{{ number_format($amountPaid, 2) }}</div>

                <div class="p-small">
                    Total shown: <strong>₱{{ number_format($totalShown, 2) }}</strong><br>
                    @if($computedTotal > 0)
                        Procedures total: <strong>₱{{ number_format($computedTotal, 2) }}</strong>
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
                        <th>Treatment</th>
                        <th>Tooth</th>
                        <th>Surface</th>
                        <th>Notes</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procedures as $p)
                        @php $pnote = trim((string)($p->notes ?? '')); @endphp
                        <tr>
                            <td style="font-weight:900;">{{ $p->service?->name ?? '—' }}</td>
                            <td style="color:rgba(15,23,42,.70); font-weight:700;">{{ $p->tooth_number ? ('#'.$p->tooth_number) : '—' }}</td>
                            <td style="color:rgba(15,23,42,.70); font-weight:700;">{{ $p->surface ?? '—' }}</td>
                            <td style="color:rgba(15,23,42,.70); font-weight:700; max-width:520px;">
                                {{ $pnote !== '' ? $pnote : '—' }}
                            </td>
                            <td class="text-end" style="font-weight:900;">
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
