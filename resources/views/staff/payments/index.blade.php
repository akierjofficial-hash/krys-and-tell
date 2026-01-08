@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);

        --text: #0f172a;
        --muted: rgba(15, 23, 42, .58);
        --muted2: rgba(15, 23, 42, .45);

        --soft: rgba(15, 23, 42, .04);
        --soft2: rgba(15, 23, 42, .06);
        --brand: #0d6efd;
        --radius: 16px;
    }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -0.35px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    /* Top actions */
    .top-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-box{
        position: relative;
        width: 360px;
        max-width: 100%;
    }
    .search-box i{
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--muted2);
        font-size: 14px;
        pointer-events: none;
    }
    .search-box input{
        width: 100%;
        padding: 11px 12px 11px 38px;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, .12);
        background: rgba(255,255,255,.92);
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
        outline: none;
        transition: .15s ease;
        font-size: 14px;
        color: var(--text);
    }
    .search-box input:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }

    .sort-box{
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .sort-box .sort-label{
        font-size: 12px;
        font-weight: 900;
        color: rgba(15, 23, 42, .60);
        white-space: nowrap;
    }
    .sort-select{
        min-width: 230px;
        max-width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(15, 23, 42, .12);
        background: rgba(255,255,255,.92);
        padding: 11px 12px;
        font-size: 14px;
        color: var(--text);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .sort-select:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }

    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    .btn-ghost{
        background: rgba(15,23,42,.06);
        border-color: rgba(15,23,42,.10);
        color: rgba(15,23,42,.75) !important;
    }
    .btn-ghost:hover{ background: rgba(15,23,42,.08); }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 900;
        border-radius: 12px;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
        white-space: nowrap;
    }
    .add-btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
    }

    /* Segmented tabs */
    .segmented{
        display:inline-flex;
        padding: 6px;
        border-radius: 14px;
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        box-shadow: var(--card-shadow);
        gap: 6px;
        margin: 10px 0 14px 0;
    }
    .seg-btn{
        border: none;
        background: transparent;
        padding: 10px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .65);
        cursor: pointer;
        transition: .15s ease;
        display:flex;
        align-items:center;
        gap: 8px;
        white-space: nowrap;
    }
    .seg-btn.active{
        background: rgba(13,110,253,.10);
        color: #0d6efd;
        box-shadow: inset 0 0 0 1px rgba(13,110,253,.22);
    }

    /* Cards */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-top: 8px;
    }
    .card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        flex-wrap: wrap;
    }
    .card-head .title{
        margin: 0;
        font-weight: 950;
        font-size: 14px;
        color: var(--text);
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
    }

    /* Table */
    .table-wrap{ padding: 10px 10px 12px 10px; }
    .table-scroll{
        max-height: 72vh;
        overflow: auto;
        border-radius: 14px;
    }

    table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 13px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .92);
        position: sticky;
        top: 0;
        z-index: 2;
        white-space: nowrap;
    }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.045); }

    .muted{ color: rgba(15, 23, 42, .55); font-weight: 700; }
    .nowrap{ white-space: nowrap; }
    .money{ font-variant-numeric: tabular-nums; }

    /* Patient mini avatar */
    .pwrap{ display:flex; align-items:center; gap: 10px; }
    .pavatar{
        width: 34px; height: 34px;
        border-radius: 999px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background: rgba(13,110,253,.10);
        color: rgba(13,110,253,.95);
        font-weight: 950;
        font-size: 12px;
        flex: 0 0 auto;
        border: 1px solid rgba(13,110,253,.18);
    }
    .pname{ font-weight: 900; line-height: 1.1; }
    .psub{ font-size: 12px; color: var(--muted); font-weight: 700; margin-top: 2px; }

    /* Treatment tags */
    .tags{ display:flex; flex-wrap: wrap; gap: 6px; }
    .tag{
        display:inline-flex;
        align-items:center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(15, 23, 42, .06);
        color: rgba(15, 23, 42, .78);
        border: 1px solid rgba(15, 23, 42, .08);
        white-space: nowrap;
        max-width: 100%;
    }
    .tag.more{
        background: rgba(13,110,253,.10);
        color: rgba(13,110,253,.95);
        border-color: rgba(13,110,253,.20);
    }

    /* Status badges */
    .badge-soft{
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
    .badge-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    .st-paid{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .st-pending{ background: rgba(245, 158, 11, .12); color:#b45309; border-color: rgba(245,158,11,.25); }
    .st-info{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }

    /* Actions */
    .action-pills{
        display:flex;
        align-items:center;
        gap: 8px;
        justify-content:flex-end;
        flex-wrap: wrap;
    }
    .pill{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-pay{
        background: rgba(34, 197, 94, .12);
        color:#15803d !important;
        border-color: rgba(34,197,94,.22);
    }
    .pill-pay:hover{ background: rgba(34, 197, 94, .18); }

    .pill-edit{
        background: rgba(59, 130, 246, .12);
        color:#1d4ed8 !important;
        border-color: rgba(59,130,246,.22);
    }
    .pill-edit:hover{ background: rgba(59, 130, 246, .18); }

    .pill-view{
        background: rgba(107, 114, 128, .12);
        color: rgba(15, 23, 42, .78) !important;
        border-color: rgba(107,114,128,.22);
    }
    .pill-view:hover{ background: rgba(107, 114, 128, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color:#b91c1c !important;
        border-color: rgba(239,68,68,.22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .18); }

    /* NEW: Progress (Installments) */
    .prog{
        display:flex;
        flex-direction:column;
        gap: 6px;
    }
    .prog-top{
        display:flex;
        align-items:baseline;
        justify-content:space-between;
        gap: 10px;
        font-size: 12px;
        font-weight: 950;
        color: var(--text);
    }
    .prog-sub{
        color: var(--muted);
        font-weight: 900;
        font-size: 12px;
        white-space: nowrap;
    }
    .prog-bar{
        height: 8px;
        background: rgba(15,23,42,.10);
        border-radius: 999px;
        overflow: hidden;
    }
    .prog-bar > span{
        display:block;
        height: 100%;
        width: 0%;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        border-radius: 999px;
    }
    .prog-foot{
        font-size: 11px;
        color: rgba(15,23,42,.55);
        font-weight: 800;
        white-space: nowrap;
    }

    /* Row click affordance */
    .row-link{ cursor: pointer; }

    /* Responsive: hide some heavy columns first */
    @media (max-width: 1100px){
        .col-down, .col-term { display:none; }
    }
    @media (max-width: 900px){
        .col-start, .col-progress { display:none; }
    }

    /* Mobile improvements */
    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }

        .action-pills{ justify-content:flex-start; }

        /* Icon-only pills on mobile */
        .pill span{ display:none; }
        .pill{ padding: 8px 10px; }
    }
</style>

{{-- Header --}}
<div class="page-head">
    <div>
        <h2 class="page-title">Payments</h2>
        <p class="subtitle">Manage cash and installment payments</p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="paymentSearch" placeholder="Search patient, treatment, method, or status…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
            <select id="paymentSort" class="sort-select">
                <option value="date_desc">Date (newest)</option>
                <option value="date_asc">Date (oldest)</option>
                <option value="patient_asc">Patient (A–Z)</option>
                <option value="patient_desc">Patient (Z–A)</option>
                <option value="amount_desc">Amount (high → low)</option>
                <option value="amount_asc">Amount (low → high)</option>

                {{-- Installment-only --}}
                <option value="balance_desc" data-only="installment">Balance (high → low)</option>
                <option value="balance_asc" data-only="installment">Balance (low → high)</option>
            </select>
        </div>

        <button type="button" id="clearFilters" class="btnx btn-ghost">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        <a href="{{ route('staff.payments.choose') }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Payment
        </a>
    </div>
</div>

{{-- Segmented tabs --}}
<div class="segmented">
    <button class="seg-btn active" id="tabCash">
        <i class="fa fa-money-bill-wave"></i> Cash
    </button>
    <button class="seg-btn" id="tabInstallment">
        <i class="fa fa-layer-group"></i> Installments
    </button>
</div>

{{-- CASH TABLE --}}
<div class="card-shell" id="cashTable">
    <div class="card-head">
        <h6 class="title"><i class="fa fa-money-bill-wave"></i> Cash Payments</h6>
        <div class="hint">Showing <strong id="cashVisible">{{ $cashPayments->count() }}</strong> / <strong id="cashTotal">{{ $cashPayments->count() }}</strong></div>
    </div>

    <div class="table-wrap">
        <div class="table-scroll table-responsive">
            <table>
                <colgroup>
                    <col style="width: 210px;">
                    <col style="width: auto;">
                    <col style="width: 140px;">
                    <col style="width: 130px;">
                    <col style="width: 110px;">
                    <col style="width: 120px;">
                    <col style="width: 220px;">
                </colgroup>

                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Treatment</th>
                        <th class="nowrap">Date</th>
                        <th class="nowrap">Amount</th>
                        <th class="nowrap">Method</th>
                        <th class="nowrap">Status</th>
                        <th class="text-end nowrap">Actions</th>
                    </tr>
                </thead>

                <tbody id="cashTbody">
                @forelse ($cashPayments as $payment)
                    @php
                        $first = $payment->visit?->patient?->first_name ?? '';
                        $last  = $payment->visit?->patient?->last_name ?? '';
                        $full  = trim($first.' '.$last);
                        $initials = strtoupper(mb_substr($first,0,1).mb_substr($last,0,1));
                        $pname = strtolower(trim(($last ?? '').', '.($first ?? '')));

                        $dateTs = $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->timestamp : 0;
                        $amount = (float)($payment->amount ?? 0);
                        $dateLabel = $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '—';
                    @endphp

                    <tr class="payment-row cash-row"
                        data-patient="{{ $pname }}"
                        data-date="{{ $dateTs }}"
                        data-amount="{{ $amount }}"
                    >
                        <td>
                            <div class="pwrap">
                                <span class="pavatar">{{ $initials ?: 'P' }}</span>
                                <div>
                                    <div class="pname">{{ $full ?: '—' }}</div>
                                    <div class="psub">Cash</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @php
                                $procs = $payment->visit?->procedures ?? collect();
                                $labels = $procs->map(function($p){
                                    $name = $p->service?->name ?? '—';
                                    $tooth = $p->tooth_number ? ('#'.$p->tooth_number) : null;
                                    $surface = $p->surface ? ($p->surface) : null;

                                    $note = trim((string)($p->notes ?? ''));
                                    if ($note !== '') {
                                        $name .= ' — ' . \Illuminate\Support\Str::limit($note, 24);
                                    }
                                    return trim($name.' '.trim(($tooth ?? '').' '.($surface ?? '')));
                                })->filter()->values();

                                $allLabelsTitle = $labels->implode(' • ');
                                $shown = $labels->take(2);
                                $moreCount = max(0, $labels->count() - $shown->count());

                                $vnote = trim((string)($payment->visit?->notes ?? ''));
                            @endphp

                            @if($labels->count() > 0)
                                <div class="tags" title="{{ $allLabelsTitle }}">
                                    @foreach($shown as $label)
                                        <span class="tag">{{ $label }}</span>
                                    @endforeach
                                    @if($moreCount > 0)
                                        <span class="tag more">+{{ $moreCount }} more</span>
                                    @endif
                                </div>

                                @if($vnote !== '')
                                    <div class="muted" style="font-size:12px; margin-top:6px;">
                                        Notes: {{ \Illuminate\Support\Str::limit($vnote, 120) }}
                                    </div>
                                @endif
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>

                        <td class="nowrap">{{ $dateLabel }}</td>
                        <td class="nowrap money" style="font-weight:900;">₱{{ number_format($payment->amount, 2) }}</td>
                        <td class="muted nowrap">{{ $payment->method }}</td>

                        <td class="nowrap">
                            <span class="badge-soft st-paid">
                                <span class="badge-dot"></span> PAID
                            </span>
                        </td>

                        <td class="text-end nowrap">
                            <div class="action-pills">
                                <a href="{{ route('staff.payments.edit', $payment->id) }}" class="pill pill-edit" title="Edit">
                                    <i class="fa fa-pen"></i> <span>Edit</span>
                                </a>

                                <a href="{{ route('staff.payments.show', $payment->id) }}" class="pill pill-view" title="View">
                                    <i class="fa fa-eye"></i> <span>View</span>
                                </a>

                                <form action="{{ route('staff.payments.destroy', $payment->id) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('Delete this payment?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="pill pill-del" title="Delete">
                                        <i class="fa fa-trash"></i> <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No cash payments found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- INSTALLMENT TABLE --}}
<div class="card-shell" id="installmentTable" style="display:none;">
    <div class="card-head">
        <h6 class="title"><i class="fa fa-layer-group"></i> Installment Plans</h6>
        <div class="hint">Showing <strong id="insVisible">{{ $installments->count() }}</strong> / <strong id="insTotal">{{ $installments->count() }}</strong></div>
    </div>

    <div class="table-wrap">
        <div class="table-scroll table-responsive">
            <table>
                <colgroup>
                    <col style="width: 210px;">
                    <col style="width: auto;">
                    <col style="width: 140px;">
                    <col style="width: 130px;">
                    <col style="width: 120px;">
                    <col style="width: 130px;">
                    <col style="width: 95px;">
                    <col style="width: 140px;">
                    <col style="width: 220px;"> {{-- NEW: Progress --}}
                    <col style="width: 240px;">
                </colgroup>

                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Treatment</th>
                        <th class="nowrap col-start">Start Date</th>
                        <th class="nowrap">Total</th>
                        <th class="nowrap col-down">Down</th>
                        <th class="nowrap">Balance</th>
                        <th class="nowrap col-term">Term</th>
                        <th class="nowrap">Status</th>
                        <th class="nowrap col-progress">Progress</th>
                        <th class="text-end nowrap">Actions</th>
                    </tr>
                </thead>

                <tbody id="insTbody">
                @forelse ($installments as $plan)
                    @php
                        $first2 = $plan->patient->first_name ?? '';
                        $last2  = $plan->patient->last_name ?? '';
                        $full2  = trim($first2.' '.$last2);
                        $initials2 = strtoupper(mb_substr($first2,0,1).mb_substr($last2,0,1));
                        $pname2 = strtolower(trim(($last2 ?? '').', '.($first2 ?? '')));

                        $startTs = $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->timestamp : 0;
                        $totalCost = (float)($plan->total_cost ?? 0);
                        $balance = (float)($plan->balance ?? 0);
                        $paid = max(0, $totalCost - $balance);
                        $pct = $totalCost > 0 ? (int) round(($paid / $totalCost) * 100) : 0;
                        $pct = max(0, min(100, $pct));

                        $startLabel = $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('M d, Y') : '—';
                        $isPaid = strtolower($plan->status ?? '') === 'fully paid';

                        $returnUrl = url()->full();
                    @endphp

                    <tr class="payment-row ins-row row-link"
                        data-patient="{{ $pname2 }}"
                        data-date="{{ $startTs }}"
                        data-amount="{{ $totalCost }}"
                        data-balance="{{ $balance }}"
                        data-href="{{ route('staff.installments.show', [$plan->id, 'return' => $returnUrl]) }}"
                    >
                        <td>
                            <div class="pwrap">
                                <span class="pavatar">{{ $initials2 ?: 'P' }}</span>
                                <div>
                                    <div class="pname">{{ $full2 ?: '—' }}</div>
                                    <div class="psub">Installment</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @php
                                $tags = collect();

                                if ($plan->service) {
                                    $tags = collect([$plan->service->name]);
                                } elseif ($plan->visit && $plan->visit->procedures) {
                                    $tags = $plan->visit->procedures->map(function($p){
                                        $name = $p->service?->name ?? '—';
                                        $tooth = $p->tooth_number ? ('#'.$p->tooth_number) : null;
                                        $surface = $p->surface ? ($p->surface) : null;

                                        $note = trim((string)($p->notes ?? ''));
                                        if ($note !== '') {
                                            $name .= ' — ' . \Illuminate\Support\Str::limit($note, 24);
                                        }
                                        return trim($name.' '.trim(($tooth ?? '').' '.($surface ?? '')));
                                    })->filter()->values();
                                }

                                $allTagsTitle = $tags->implode(' • ');
                                $shownTags = $tags->take(2);
                                $moreTags = max(0, $tags->count() - $shownTags->count());

                                $vnote2 = trim((string)(optional($plan->visit)->notes ?? ''));
                            @endphp

                            @if($tags->count())
                                <div class="tags" title="{{ $allTagsTitle }}">
                                    @foreach($shownTags as $t)
                                        <span class="tag">{{ $t }}</span>
                                    @endforeach
                                    @if($moreTags > 0)
                                        <span class="tag more">+{{ $moreTags }} more</span>
                                    @endif
                                </div>

                                @if($vnote2 !== '')
                                    <div class="muted" style="font-size:12px; margin-top:6px;">
                                        Notes: {{ \Illuminate\Support\Str::limit($vnote2, 120) }}
                                    </div>
                                @endif
                            @else
                                <span class="muted">N/A</span>
                            @endif
                        </td>

                        <td class="nowrap col-start">{{ $startLabel }}</td>

                        <td class="nowrap money" style="font-weight:900;">
                            ₱{{ number_format($plan->total_cost, 2) }}
                        </td>

                        <td class="muted nowrap col-down">
                            ₱{{ number_format($plan->downpayment, 2) }}
                        </td>

                        <td class="nowrap money" style="font-weight:900;">
                            ₱{{ number_format($plan->balance, 2) }}
                        </td>

                        <td class="muted nowrap col-term">{{ $plan->months }} mos</td>

                        <td class="nowrap">
                            <span class="badge-soft {{ $isPaid ? 'st-paid' : 'st-pending' }}">
                                <span class="badge-dot"></span> {{ strtoupper($plan->status ?? 'PENDING') }}
                            </span>
                        </td>

                        {{-- NEW: Progress --}}
                        <td class="col-progress">
                            <div class="prog">
                                <div class="prog-top">
                                    <span class="money">₱{{ number_format($paid, 2) }}</span>
                                    <span class="prog-sub">{{ $pct }}%</span>
                                </div>
                                <div class="prog-bar" aria-label="Progress">
                                    <span style="width: {{ $pct }}%;"></span>
                                </div>
                                <div class="prog-foot money">Paid / Total ₱{{ number_format($totalCost, 2) }}</div>
                            </div>
                        </td>

                        <td class="text-end nowrap">
                            <div class="action-pills">
                                @if(!$isPaid)
                                    <a href="{{ route('staff.installments.pay', [$plan->id, 'return' => $returnUrl]) }}" class="pill pill-pay" title="Pay">
                                        <i class="fa fa-circle-dollar-to-slot"></i> <span>Pay</span>
                                    </a>
                                @endif

                                <a href="{{ route('staff.installments.edit', [$plan->id, 'return' => $returnUrl]) }}" class="pill pill-edit" title="Edit">
                                    <i class="fa fa-pen"></i> <span>Edit</span>
                                </a>

                                <a href="{{ route('staff.installments.show', [$plan->id, 'return' => $returnUrl]) }}" class="pill pill-view" title="View">
                                    <i class="fa fa-eye"></i> <span>View</span>
                                </a>

                                <form action="{{ route('staff.installments.destroy', $plan) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('Delete this installment plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="pill pill-del" title="Delete">
                                        <i class="fa fa-trash"></i> <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No installment plans found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const tabCash = document.getElementById('tabCash');
    const tabInstallment = document.getElementById('tabInstallment');
    const cashTable = document.getElementById('cashTable');
    const installmentTable = document.getElementById('installmentTable');

    const searchInput = document.getElementById('paymentSearch');
    const sortSelect = document.getElementById('paymentSort');
    const resetBtn = document.getElementById('clearFilters');

    const cashTbody = document.getElementById('cashTbody');
    const insTbody  = document.getElementById('insTbody');

    const cashRows = Array.from(document.querySelectorAll('.cash-row'));
    const insRows  = Array.from(document.querySelectorAll('.ins-row'));

    const cashVisibleEl = document.getElementById('cashVisible');
    const cashTotalEl   = document.getElementById('cashTotal');
    const insVisibleEl  = document.getElementById('insVisible');
    const insTotalEl    = document.getElementById('insTotal');

    cashTotalEl.textContent = cashRows.length;
    insTotalEl.textContent  = insRows.length;

    function currentTab(){
        return cashTable.style.display !== 'none' ? 'cash' : 'installment';
    }

    function updateSortOptions(){
        const tab = currentTab();
        const opts = Array.from(sortSelect.options);

        opts.forEach(opt => {
            const only = opt.dataset.only; // e.g. "installment"
            opt.hidden = !!only && only !== tab;
        });

        // if currently selected option is hidden, fallback
        const selected = sortSelect.selectedOptions[0];
        if (selected && selected.hidden) sortSelect.value = 'date_desc';
    }

    function showCash(save=true){
        tabCash.classList.add('active');
        tabInstallment.classList.remove('active');
        cashTable.style.display = 'block';
        installmentTable.style.display = 'none';
        if (save) localStorage.setItem('payments_tab', 'cash');
        updateSortOptions();
        applyAll();
    }

    function showInstallment(save=true){
        tabInstallment.classList.add('active');
        tabCash.classList.remove('active');
        cashTable.style.display = 'none';
        installmentTable.style.display = 'block';
        if (save) localStorage.setItem('payments_tab', 'installment');
        updateSortOptions();
        applyAll();
    }

    tabCash.addEventListener('click', () => showCash(true));
    tabInstallment.addEventListener('click', () => showInstallment(true));

    // Auto-open tab:
    // 1) URL param (?tab=installment) wins
    // 2) otherwise remember last tab using localStorage
    (function () {
        const params = new URLSearchParams(window.location.search);
        const tab = (params.get('tab') || '').toLowerCase();

        if (tab === 'installment' || tab === 'installments') return showInstallment(false);
        if (tab === 'cash') return showCash(false);

        const saved = (localStorage.getItem('payments_tab') || '').toLowerCase();
        if (saved === 'installment') return showInstallment(false);

        return showCash(false);
    })();

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    function sortRows(rows, tbody, mode){
        const sorted = [...rows].sort((a, b) => {
            const da = a.dataset;
            const db = b.dataset;

            const patientA = da.patient || '';
            const patientB = db.patient || '';
            const dateA = Number(da.date || 0);
            const dateB = Number(db.date || 0);
            const amtA  = Number(da.amount || 0);
            const amtB  = Number(db.amount || 0);
            const balA  = Number(da.balance || 0);
            const balB  = Number(db.balance || 0);

            switch(mode){
                case 'date_desc': return dateB - dateA;
                case 'date_asc':  return dateA - dateB;
                case 'patient_asc': return patientA.localeCompare(patientB) || (dateB - dateA);
                case 'patient_desc': return patientB.localeCompare(patientA) || (dateB - dateA);
                case 'amount_desc': return (amtB - amtA) || (dateB - dateA);
                case 'amount_asc':  return (amtA - amtB) || (dateB - dateA);

                // installment-only
                case 'balance_desc': return (balB - balA) || (dateB - dateA);
                case 'balance_asc':  return (balA - balB) || (dateB - dateA);

                default: return dateB - dateA;
            }
        });

        sorted.forEach(r => tbody.appendChild(r));
    }

    function applySearch(rows, visibleEl){
        const q = normalize(searchInput.value);
        let visible = 0;

        rows.forEach(row => {
            const show = normalize(row.textContent).includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleEl.textContent = visible;
    }

    function applyAll(){
        const mode = sortSelect.value;

        if (currentTab() === 'cash'){
            sortRows(cashRows, cashTbody, mode);
            applySearch(cashRows, cashVisibleEl);
        } else {
            sortRows(insRows, insTbody, mode);
            applySearch(insRows, insVisibleEl);
        }
    }

    // Row click → view installment plan (but not when clicking buttons/links)
    function enableRowClick(rows){
        rows.forEach(row => {
            const href = row.dataset.href;
            if (!href) return;

            row.addEventListener('click', (e) => {
                if (e.target.closest('a,button,form,input,select,textarea,label')) return;
                window.location.href = href;
            });
        });
    }
    enableRowClick(insRows);

    // tiny debounce for large tables
    let t = null;
    function debounceApply(){
        clearTimeout(t);
        t = setTimeout(applyAll, 120);
    }

    searchInput.addEventListener('input', debounceApply);
    sortSelect.addEventListener('change', applyAll);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        sortSelect.value = 'date_desc';
        updateSortOptions();
        applyAll();
        searchInput.focus();
    });

    // Esc clears search (nice UX)
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            applyAll();
            searchInput.blur();
        }
    });

    updateSortOptions();
    applyAll();
})();
</script>

@endsection
