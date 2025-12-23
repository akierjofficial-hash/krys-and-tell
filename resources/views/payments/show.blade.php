@extends('layouts.app')

@section('title', 'Payment Receipt')

@section('content')

<style>
/* EXACT SHORT BOND / LETTER CANVAS */
@page { size: 8.5in 11in; margin: 0; }

/* Theme (Krys&Tell) */
:root{
    --kt-cream: #fbf4ec;
    --kt-cream-2:#f6eee5;
    --kt-ink:   #2b2623;
    --kt-muted: rgba(43,38,35,.70);
    --kt-line:  rgba(43,38,35,.14);
    --kt-brown: #9c6b4f;
    --kt-brown2:#c1926e;
    --kt-soft:  rgba(156,107,79,.10);
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
    padding: 14mm 14mm;
    box-sizing: border-box;

    position: relative;
    overflow: hidden;

    background: linear-gradient(180deg, #ffffff 0%, #ffffff 55%, var(--kt-cream) 100%);
    border: 1px solid var(--kt-line);
    border-radius: 16px;
    box-shadow: 0 14px 35px rgba(15,23,42,.10);

    color: var(--kt-ink);
    font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;

    display:flex;
    flex-direction:column;
}

/* Soft background blobs */
.receipt-container::before{
    content:"";
    position:absolute;
    inset:-40mm -30mm auto -30mm;
    height: 120mm;
    pointer-events:none;
    opacity:.55;
    background:
        radial-gradient(110mm 70mm at 18% 30%, rgba(193,146,110,.28) 0%, rgba(193,146,110,0) 60%),
        radial-gradient(120mm 80mm at 78% 18%, rgba(156,107,79,.18) 0%, rgba(156,107,79,0) 60%),
        radial-gradient(90mm 60mm at 55% 78%, rgba(246,238,229,.90) 0%, rgba(246,238,229,0) 70%);
}

/* Tooth watermark pattern */
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

/* Bottom wave strip */
.kt-wave{
    position:absolute;
    left:-8%;
    right:-8%;
    bottom:-28px;
    height: 120px;
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
    margin-bottom: 12px;
    border-bottom: 1px solid var(--kt-line);
    position: relative;
    z-index: 2;
}
.brand{ display:flex; align-items:center; gap: 12px; min-width: 240px; }
.brand-logo{
    width: 56px; height: 56px; border-radius: 14px;
    background: linear-gradient(135deg, rgba(156,107,79,.16), rgba(193,146,110,.12));
    display:flex; align-items:center; justify-content:center;
    border: 1px solid rgba(156,107,79,.18);
}
.brand-title{ line-height:1.05; }
.brand-title .clinic{ font-size: 18px; font-weight: 900; letter-spacing: .2px; margin:0; }
.brand-title .sub{ font-size: 12px; margin: 3px 0 0 0; color: var(--kt-muted); font-weight: 700; }
.header-right{ text-align:right; font-size: 11px; line-height: 1.55; color: var(--kt-muted); }
.header-right b{ color: var(--kt-ink); }

/* META (like installment) */
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
    width: 120px;
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
    padding: 10px 12px;
    font-size: 12px;
    text-align: left;
    letter-spacing: .35px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(43,38,35,.12);
}
.receipt-table thead th:last-child{ text-align:right; }
.receipt-table td{
    border-top: 1px solid rgba(43,38,35,.10);
    padding: 10px 12px;
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
.tooth-chip{
    display:inline-block;
    margin-top: 6px;
    padding: 4px 9px;
    border-radius: 999px;
    border: 1px solid rgba(156,107,79,.25);
    background: rgba(156,107,79,.10);
    color: rgba(156,107,79,.95);
    font-weight: 900;
    font-size: 11px;
}

/* TOTALS */
.receipt-totals{
    width: 100%;
    display: flex;
    justify-content: space-between;
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

/* FOOTER */
.goodday{
    text-align:center;
    margin-top: 10px;
    font-weight: 900;
    color: rgba(43,38,35,.75);
    letter-spacing: .25px;
}
.receipt-footer{
    margin-top: auto;
    padding-top: 12px;
    border-top: 1px solid var(--kt-line);
    font-size: 10.5px;
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap: 12px;
    position: relative;
    z-index: 2;
}
.signature{ width: 40%; text-align: center; }
.signature-line{
    margin-top: 22px;
    border-top: 1px solid rgba(43,38,35,.75);
    padding-top: 6px;
    font-weight: 900;
    letter-spacing: .3px;
    color: var(--kt-ink);
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

/* ===== PRINT: SAME WORKING METHOD AS INSTALLMENT ===== */
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

        display:flex !important;
        flex-direction:column !important;
    }

    .print-actions{ display:none !important; }
}
</style>

@php
    $procedures = $payment->visit?->procedures ?? collect();

    $groups = $procedures->groupBy(fn($p) => $p->service?->name ?? 'Service');

    $rows = $groups->map(function($items, $serviceName){
        $teeth = $items->pluck('tooth_number')
            ->filter(fn($t) => trim((string)$t) !== '')
            ->map(fn($t) => trim((string)$t))
            ->unique()
            ->sort()
            ->values()
            ->implode(', ');

        $total = $items->sum(function($p){
            return (float)($p->price
                ?? $p->service?->base_price
                ?? $p->service?->price
                ?? 0);
        });

        return [
            'service' => $serviceName,
            'teeth'   => $teeth,
            'total'   => $total,
        ];
    })->values();

    $computedTotal = $rows->sum('total');
    $shownTotal = $computedTotal > 0 ? $computedTotal : (float)$payment->amount;

    $patient = $payment->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));
    $patientName = $patientName !== '' ? $patientName : 'N/A';

    $receiptNo = 'RCPT-' . str_pad((string)($payment->id ?? 0), 6, '0', STR_PAD_LEFT);
    $paymentDate = $payment->payment_date
        ? \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y')
        : now()->format('m/d/Y');
@endphp

<div class="receipt-page">
    <div class="print-sheet">

        <div class="receipt-container">
            <div class="kt-wave"></div>

            {{-- HEADER --}}
            <div class="receipt-header">
                <div class="brand">
                    <div class="brand-logo">
                        @if(file_exists(public_path('images/krys-tell-logo.png')))
                            <img src="{{ asset('images/krys-tell-logo.png') }}" alt="Krys &amp; Tell" style="width:44px;height:44px;object-fit:contain;">
                        @else
                            <svg width="34" height="34" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M32 10c-10.6 0-19 7.7-19 19 0 7.6 2.8 13.1 5.6 22.1 2 6.6 4.2 18.9 13.4 18.9s11.4-12.3 13.4-18.9C48.2 42.1 51 36.6 51 29c0-11.3-8.4-19-19-19Z"
                                      stroke="url(#g)" stroke-width="3.2" />
                                <path d="M24.5 30.5c2.2-3.1 5.6-4.8 7.5-4.8s5.3 1.7 7.5 4.8" stroke="url(#g)" stroke-width="3.2" stroke-linecap="round"/>
                                <defs>
                                    <linearGradient id="g" x1="16" y1="14" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#9c6b4f"/>
                                        <stop offset="1" stop-color="#c1926e"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        @endif
                    </div>
                    <div class="brand-title">
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

            {{-- META (fixed + uses payment_date) --}}
            <div class="receipt-meta">
                <div class="meta-pill"><b>Date:</b> <span>{{ $paymentDate }}</span></div>
                <div class="meta-pill"><b>Receipt #:</b> <span>{{ $receiptNo }}</span></div>
            </div>

            {{-- PATIENT INFO --}}
            <div class="section">
                <div class="section-title">PATIENT INFORMATION</div>
                <table class="info-table">
                    <tr>
                        <td>Name</td>
                        <td>{{ $patientName }}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td>{{ $patient->address ?? '______________________________' }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $patient->contact_number ?? '______________________________' }}</td>
                    </tr>
                </table>
            </div>

            {{-- SERVICES --}}
            <div class="section" style="margin-top:10px;">
                <div class="section-title">DESCRIPTION OF SERVICES</div>

                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th>TREATMENT</th>
                            <th style="width:160px;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows->count() > 0)
                            @foreach($rows as $r)
                                <tr>
                                    <td>
                                        <strong>{{ $r['service'] }}</strong>
                                        @if($r['teeth'])
                                            <div class="tooth-chip">Tooth {{ $r['teeth'] }}</div>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($r['total'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" style="text-align:center;">No treatments available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- TOTALS --}}
            <div class="receipt-totals">
                <div class="left-details">
                    <p><strong>PAYMENT METHOD :</strong> {{ ucfirst($payment->method) }}</p>
                    <p><strong>REMARKS :</strong> ____________________________</p>
                </div>

                <div class="right-details">
                    <div class="row"><b>Subtotal</b> <span>₱{{ number_format($shownTotal, 2) }}</span></div>
                    <div class="row"><b>Discount</b> <span>₱0.00</span></div>
                    <div class="row"><b>TAX / VAT</b> <span>₱0.00</span></div>
                    <div class="row total"><b>Total Amount Due</b> <span>₱{{ number_format($shownTotal, 2) }}</span></div>
                    <div class="row"><b>Amount Paid</b> <span>₱{{ number_format($shownTotal, 2) }}</span></div>
                </div>
            </div>

            <div class="goodday">Have a Nice Day!</div>

            {{-- FOOTER --}}
            <div class="receipt-footer">
                <p style="width:55%; text-align:left; margin:0; color:rgba(43,38,35,.72);">
                    For dental appointments or inquiries, you may contact <br>
                    <strong>Krys &amp; Tell Dental Center</strong> at <strong>0912-345-6789</strong> or visit us <br>
                    at 872X+C92 Aldrich Autocare, Jose Romero Road,<br>
                    Dumaguete City, Negros Oriental
                </p>

                <div class="signature">
                    <div class="signature-line">KRYS &amp; TELL DENTAL CENTER</div>
                    <div style="font-size:11px; color:rgba(43,38,35,.70);">Authorized Signature</div>
                </div>
            </div>

        </div>
    </div>

    {{-- PRINT BUTTONS --}}
    <div class="print-actions">
        <a href="{{ route('payments.index') }}" class="back-btn">
            <i class="fa fa-arrow-left"></i> Back to Payments
        </a>
        <button onclick="window.print()" class="print-btn">
            <i class="fa fa-print"></i> Print Receipt
        </button>
    </div>
</div>

@endsection
