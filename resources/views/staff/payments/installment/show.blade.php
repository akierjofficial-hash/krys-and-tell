@extends('layouts.staff')

@section('title', 'Installment Payment Receipt')

@section('content')

<style>
/* SHORT BOND PAPER = LETTER (8.5 x 11 in) */
@page { size: 8.5in 11in; margin: 0; }

:root{
    --kt-cream: #fbf4ec;
    --kt-cream-2:#f6eee5;
    --kt-ink:   #2b2623;
    --kt-muted: rgba(43,38,35,.70);
    --kt-line:  rgba(43,38,35,.14);
    --kt-brown: #9c6b4f;
    --kt-brown2:#c1926e;
}

/* ===== Screen wrapper ===== */
.receipt-page{ padding: 18px 12px 26px; }
.print-sheet{
    width: 100%;
    max-width: 8.5in;
    margin: 0 auto;
}

/* ===== Receipt card ===== */
.receipt-container{
    width: 100%;
    max-width: 7.6in;
    margin: 0 auto;
    padding: 12mm 12mm;
    box-sizing: border-box;

    position: relative;
    overflow: hidden;

    background: linear-gradient(180deg, #ffffff 0%, #ffffff 55%, var(--kt-cream) 100%);
    border: 1px solid var(--kt-line);
    border-radius: 16px;
    box-shadow: 0 14px 35px rgba(15,23,42,.10);

    color: var(--kt-ink);
    font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
}

/* soft blobs */
.receipt-container::before{
    content:"";
    position:absolute;
    inset:-40mm -30mm auto -30mm;
    height: 110mm;
    pointer-events:none;
    opacity:.55;
    background:
        radial-gradient(110mm 70mm at 18% 30%, rgba(193,146,110,.28) 0%, rgba(193,146,110,0) 60%),
        radial-gradient(120mm 80mm at 78% 18%, rgba(156,107,79,.18) 0%, rgba(156,107,79,0) 60%),
        radial-gradient(90mm 60mm at 55% 78%, rgba(246,238,229,.90) 0%, rgba(246,238,229,0) 70%);
}

/* tooth watermark */
.receipt-container::after{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    opacity:.07;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'%3E%3Cg fill='none' stroke='%239c6b4f' stroke-width='3'%3E%3Cpath d='M90 26c-22 0-40 16-40 40 0 16 6 28 12 48 4 13 9 38 28 38s24-25 28-38c6-20 12-32 12-48 0-24-18-40-40-40z'/%3E%3Cpath d='M77 64c4-6 10-9 13-9s9 3 13 9'/%3E%3C/g%3E%3C/svg%3E");
    background-size: 220px 220px;
    background-repeat: repeat;
}

/* bottom wave */
.kt-wave{
    position:absolute;
    left:-8%;
    right:-8%;
    bottom:-26px;
    height: 100px;
    pointer-events:none;
    background: linear-gradient(180deg, rgba(251,244,236,0) 0%, rgba(251,244,236,.95) 30%, rgba(246,238,229,.98) 100%);
    border-top-left-radius: 999px;
    border-top-right-radius: 999px;
    transform: rotate(-1.5deg);
}
.kt-wave::after{
    content:"";
    position:absolute;
    left:6%;
    right:6%;
    top:16px;
    height: 6px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(156,107,79,.0) 0%, rgba(156,107,79,.35) 25%, rgba(193,146,110,.35) 75%, rgba(156,107,79,.0) 100%);
}

/* HEADER */
.receipt-header{
    display:flex;
    justify-content:space-between;
    gap: 14px;
    padding-bottom: 12px;
    margin-bottom: 10px;
    border-bottom: 1px solid var(--kt-line);
    position: relative;
    z-index: 2;
}

/* ✅ FIX: use receipt-only brand classes (no conflict with layout sidebar .brand) */
.receipt-brand{ display:flex; align-items:center; gap: 12px; min-width: 240px; }
.receipt-brand-logo{
    width: 56px; height: 56px; border-radius: 14px;
    background: linear-gradient(135deg, rgba(156,107,79,.16), rgba(193,146,110,.12));
    display:flex; align-items:center; justify-content:center;
    border: 1px solid rgba(156,107,79,.18);
    overflow:hidden;
}
.receipt-brand-logo img{
    width:44px; height:44px; object-fit:contain; display:block;
}
.receipt-brand-title{ line-height:1.05; }
.receipt-brand-title .clinic{ font-size: 18px; font-weight: 900; margin:0; }
.receipt-brand-title .sub{ font-size: 12px; margin: 3px 0 0 0; color: var(--kt-muted); font-weight: 700; }

.header-right{ text-align:right; font-size: 11px; line-height: 1.55; color: var(--kt-muted); }
.header-right b{ color: var(--kt-ink); }

/* META */
.receipt-meta{
    display:flex;
    justify-content:space-between;
    gap: 12px;
    font-size: 12px;
    color: var(--kt-ink);
    margin: 2px 0 10px;
    position: relative;
    z-index: 2;
}
.meta-pill{
    border: 1px solid rgba(43,38,35,.14);
    background: rgba(255,255,255,.90);
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 800;
}
.meta-pill span{ font-weight: 900; }

/* SECTION */
.section{ position: relative; z-index: 2; }
.section-title{
    background: linear-gradient(90deg, rgba(156,107,79,.20), rgba(193,146,110,.12));
    border: 1px solid rgba(156,107,79,.22);
    color: var(--kt-ink);
    font-weight: 900;
    text-align:center;
    padding: 8px 10px;
    border-radius: 10px;
    font-size: 12px;
    letter-spacing: .3px;
    margin: 6px 0 8px;
}

.info-table{
    width: 100%;
    border-collapse: collapse;
    border: 1px solid var(--kt-line);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255,255,255,.92);
}
.info-table td{
    padding: 9px 10px;
    font-size: 12px;
    border-top: 1px solid rgba(43,38,35,.10);
}
.info-table tr:first-child td{ border-top: 0; }
.info-table td:first-child{
    width: 140px;
    color: rgba(43,38,35,.75);
    font-weight: 800;
    background: rgba(246,238,229,.75);
    border-right: 1px solid rgba(43,38,35,.10);
}

/* TABLE */
.receipt-table{
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 10px 0 12px 0;
    overflow: hidden;
    border-radius: 12px;
    border: 1px solid var(--kt-line);
    position: relative;
    z-index: 2;
    background: rgba(255,255,255,.92);
}
.receipt-table thead th{
    background: rgba(246,238,229,.90);
    color: var(--kt-ink);
    padding: 10px 10px;
    font-size: 12px;
    text-align: left;
    letter-spacing: .35px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(43,38,35,.12);
    white-space: nowrap;
}
.receipt-table thead th:last-child{ text-align:right; }
.receipt-table td{
    border-top: 1px solid rgba(43,38,35,.10);
    padding: 10px 10px;
    font-size: 12px;
    color: var(--kt-ink);
    vertical-align: top;
}
.receipt-table tbody tr:nth-child(even){ background: rgba(251,244,236,.60); }
.receipt-table td:last-child{
    text-align: right;
    white-space: nowrap;
    font-weight: 900;
}

/* TOTALS */
.receipt-totals{
    width: 100%;
    display:flex;
    justify-content:space-between;
    gap: 14px;
    margin-top: 6px;
    font-size: 12px;
    position: relative;
    z-index: 2;
}
.left-details{
    width: 55%;
    border: 1px solid var(--kt-line);
    border-radius: 12px;
    padding: 12px;
    background: rgba(255,255,255,.92);
}
.left-details p{ margin: 6px 0; color: rgba(43,38,35,.82); }
.left-details strong{ color: var(--kt-ink); }

.right-details{
    width: 45%;
    border: 1px solid var(--kt-line);
    border-radius: 12px;
    overflow:hidden;
    background: rgba(255,255,255,.92);
}
.right-details .row{
    display:flex;
    justify-content:space-between;
    gap: 10px;
    padding: 9px 12px;
    border-top: 1px solid rgba(43,38,35,.10);
    font-size: 12px;
}
.right-details .row:first-child{ border-top: 0; }
.right-details .row b{ color: rgba(43,38,35,.78); }
.right-details .row span{ font-weight: 900; color: var(--kt-ink); }
.right-details .row.total{
    background: rgba(246,238,229,.90);
    border-top: 1px solid rgba(43,38,35,.14);
}
.goodday{
    text-align:center;
    margin-top: 10px;
    font-weight: 900;
    color: rgba(43,38,35,.75);
    letter-spacing: .25px;
}

/* FOOTER */
.receipt-footer{
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--kt-line);
    font-size: 10.5px;
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap: 12px;
    position: relative;
    z-index: 2;
    color: rgba(43,38,35,.72);
}
.signature{ width: 40%; text-align:center; color: var(--kt-ink); }
.signature-line{
    margin-top: 22px;
    border-top: 1px solid rgba(43,38,35,.75);
    padding-top: 6px;
    font-weight: 900;
    letter-spacing: .3px;
}

/* Buttons */
.print-actions{
    max-width: 7.6in;
    margin: 14px auto 0;
    display:flex;
    justify-content:flex-end;
    gap: 10px;
}
.print-btn{
    border: none;
    padding: 11px 14px;
    border-radius: 12px;
    font-weight: 900;
    color: #fff;
    background: linear-gradient(135deg, var(--kt-brown), var(--kt-brown2));
    box-shadow: 0 10px 18px rgba(156,107,79,.22);
    cursor:pointer;
    transition: .15s ease;
}
.print-btn:hover{ transform: translateY(-1px); }
.back-btn{
    text-decoration:none;
    padding: 11px 14px;
    border-radius: 12px;
    font-weight: 900;
    border: 1px solid rgba(43,38,35,.14);
    color: rgba(43,38,35,.75);
    background: rgba(255,255,255,.92);
}

/* ===== PRINT (alignment fix) ===== */
@media print{
    html, body{
        width: 8.5in !important;
        height: 11in !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    *{
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body *{ visibility: hidden; }
    .receipt-page, .receipt-page *{ visibility: visible; }

    .receipt-page{
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 8.5in !important;
        height: 11in !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-sheet{
        width: 8.5in !important;
        height: 11in !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }

    .receipt-container{
        width: 8.5in !important;
        height: 11in !important;
        max-width: none !important;
        margin: 0 !important;

        box-sizing: border-box !important;
        padding: 10mm !important;

        border-radius: 0 !important;
        box-shadow: none !important;
        border: none !important;

        display: flex !important;
        flex-direction: column !important;
    }

    .receipt-footer{ margin-top: auto !important; }
    .print-actions{ display:none !important; }
}
</style>

@php
use Carbon\Carbon;

$startDate = Carbon::parse($plan->start_date);
$months = $plan->months;
$totalCost = $plan->total_cost;
$downpayment = $plan->downpayment;

$paidAmount = $downpayment + $plan->payments->sum('amount');
$remainingBalance = $totalCost - $paidAmount;

$patient = $plan->patient ?? $plan->visit?->patient ?? null;

$patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));
$patientName = $patientName !== '' ? $patientName : 'N/A';

$patientAddress = trim((string)($patient->address ?? ''));
$patientAddress = $patientAddress !== '' ? $patientAddress : '_____________________________';

$receiptNo = 'INST-' . str_pad((string)($plan->id ?? 0), 6, '0', STR_PAD_LEFT);

function ordinal($number) {
    if (in_array($number % 100, [11,12,13])) return $number.'th';
    return match ($number % 10) {
        1 => $number.'st',
        2 => $number.'nd',
        3 => $number.'rd',
        default => $number.'th',
    };
}
@endphp

<div class="receipt-page">
    <div class="print-sheet">
        <div class="receipt-container">
            <div class="kt-wave"></div>

            {{-- HEADER --}}
            <div class="receipt-header">
                <div class="receipt-brand">
                    <div class="receipt-brand-logo">
                        {{-- ✅ Correct logo path --}}
                        <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys &amp; Tell">
                    </div>
                    <div class="receipt-brand-title">
                        <p class="clinic">KRYS &amp; TELL</p>
                        <p class="sub">Dental Center</p>
                    </div>
                </div>

                <div class="header-right">
                    <div><b>Krys &amp; Tell Dental Center</b></div>
                    <div>872X+C92 Aldrich Autocare, Jose Romero Road</div>
                    <div>Dumaguete City, Negros Oriental</div>
                    <div><b>Phone:</b> 0912-345-6789</div>
                </div>
            </div>

            {{-- META --}}
            <div class="receipt-meta">
                <div class="meta-pill"><b>Date:</b> <span>{{ now()->format('m/d/Y') }}</span></div>
                <div class="meta-pill"><b>Receipt #:</b> <span>{{ $receiptNo }}</span></div>
            </div>

            {{-- PATIENT INFO --}}
            <div class="section">
                <div class="section-title">PATIENT INFORMATION</div>
                <table class="info-table">
                    <tr><td>Name</td><td>{{ $patientName }}</td></tr>
                    <tr><td>Address</td><td>{{ $patientAddress }}</td></tr>
                    <tr><td>Payment Plan</td><td>{{ $months }} Months Installment</td></tr>
                </table>
            </div>

            {{-- TABLE --}}
            <div class="section" style="margin-top:10px;">
                <div class="section-title">INSTALLMENT SCHEDULE</div>

                <table class="receipt-table">
                    <thead>
                        <tr>
		                            <th style="width:110px;">DATE</th>
		                            <th style="width:220px;">DESCRIPTION</th>
		                            <th>TREATMENT</th>
		                            <th style="width:260px;">NOTES</th>
		                            <th style="width:130px; text-align:right;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= $months; $i++)
                            @php
		                                $scheduled = $startDate->copy()->addMonths($i - 1);
		                                $payment = $plan->payments->firstWhere('month_number', $i);
		                                $paidDate = $payment?->payment_date ? \Carbon\Carbon::parse($payment->payment_date) : null;
		                                $date = ($paidDate ?? $scheduled)->format('m/d/Y');

		                                if ($i == 1) {
		                                    $amount = $payment?->amount ?? $downpayment;
		                                } else {
		                                    $amount = $payment?->amount ?? null;
		                                }

		                                $notesRaw = $payment?->notes ?? $payment?->visit?->notes;
		                                $notes = trim((string)($notesRaw ?? ''));
                            @endphp
                            <tr>
                                <td>{{ $date }}</td>
                                <td>{{ ordinal($i) }} Payment @if($i == 1) (Downpayment) @endif</td>
                                <td>{{ $plan->service->name ?? 'Dental Treatment' }}</td>
		                                <td>
		                                    @if($notes !== '')
		                                        @php $short = \Illuminate\Support\Str::limit($notes, 60); @endphp
		                                        <span title="{{ $notes }}">{{ $short }}</span>
		                                        @if($payment?->visit_id)
		                                            <div style="margin-top:4px; font-size:12px; opacity:.75;">
		                                                Visit: <a href="{{ route('staff.visits.show', $payment->visit_id) }}" style="color:#0d6efd; text-decoration:none;">#{{ $payment->visit_id }}</a>
		                                            </div>
		                                        @endif
		                                    @else
		                                        —
		                                    @endif
		                                </td>
		                                <td style="text-align:right;">@if($amount) ₱{{ number_format($amount, 2) }} @endif</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- TOTALS --}}
            <div class="receipt-totals">
                <div class="left-details">
                    <p><strong>PAYMENT METHOD :</strong> Installment</p>
                    <p><strong>NOTES :</strong> ____________________________</p>
                </div>

                <div class="right-details">
                    <div class="row"><b>Total Cost</b> <span>₱{{ number_format($totalCost,2) }}</span></div>
                    <div class="row"><b>Paid</b> <span>₱{{ number_format($paidAmount,2) }}</span></div>
                    <div class="row total"><b>Remaining</b> <span>₱{{ number_format($remainingBalance,2) }}</span></div>
                </div>
            </div>

            <div class="goodday">Have a Nice Day!</div>

            {{-- FOOTER --}}
            <div class="receipt-footer">
                <p style="margin:0;">
                    For dental inquiries, contact <strong>Krys &amp; Tell Dental Center</strong><br>
                    0912-345-6789 – Dumaguete City
                </p>

                <div class="signature">
                    <div class="signature-line">KRYSTEL XYZA BETONIO</div>
                    <div style="font-size:11px; color:rgba(43,38,35,.70);">Doctor’s Signature</div>
                </div>
            </div>

        </div>
    </div>

    <div class="print-actions">
        <a href="{{ route('staff.payments.index', ['tab' => 'installment']) }}" class="back-btn">
            <i class="fa fa-arrow-left"></i> Back to Plan
        </a>
        <button onclick="window.print()" class="print-btn">
            <i class="fa fa-print"></i> Print Receipt
        </button>
    </div>
</div>

@endsection
