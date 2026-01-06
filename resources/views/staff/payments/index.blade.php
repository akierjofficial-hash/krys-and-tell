@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

    /* Header */
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
        font-weight: 700;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
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
        width: 340px;
        max-width: 100%;
    }
    .search-box i{
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: rgba(15, 23, 42, .45);
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
        color: #0f172a;
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
        font-weight: 800;
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
        color: #0f172a;
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
        font-weight: 700;
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
        font-weight: 600;
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
        background: rgba(255,255,255,.9);
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
        font-weight: 700;
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
        box-shadow: inset 0 0 0 1px rgba(13,110,253,.20);
    }

    /* Cards */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
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
        font-weight: 800;
        font-size: 14px;
        color: #0f172a;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    /* Table */
    .table-wrap{ padding: 8px 10px 10px 10px; }
    table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 14px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .9);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: #0f172a;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }
    .muted{ color: rgba(15, 23, 42, .55); }

    /* Treatment tags */
    .tags{ display:flex; flex-wrap: wrap; gap: 6px; }
    .tag{
        display:inline-flex;
        align-items:center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(15, 23, 42, .06);
        color: rgba(15, 23, 42, .75);
        border: 1px solid rgba(15, 23, 42, .08);
        white-space: nowrap;
    }

    /* Status badges */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
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
        font-weight: 700;
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
        color: rgba(15, 23, 42, .75) !important;
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

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
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

    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Treatment</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody id="cashTbody">
            @forelse ($cashPayments as $payment)
                @php
                    $pname = strtolower(trim(($payment->visit?->patient?->last_name ?? '').', '.($payment->visit?->patient?->first_name ?? '')));
                    $dateTs = $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->timestamp : 0;
                    $amount = (float)($payment->amount ?? 0);
                @endphp

                <tr class="payment-row cash-row"
                    data-patient="{{ $pname }}"
                    data-date="{{ $dateTs }}"
                    data-amount="{{ $amount }}"
                >
                    <td class="fw-semibold">
                        {{ $payment->visit?->patient?->first_name }} {{ $payment->visit?->patient?->last_name }}
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
                        @endphp

                        @if($labels->count() > 0)
                            <div class="tags">
                                @foreach($labels as $label)
                                    <span class="tag">{{ $label }}</span>
                                @endforeach
                            </div>

                            @php $vnote = trim((string)($payment->visit?->notes ?? '')); @endphp
                            @if($vnote !== '')
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    Notes: {{ \Illuminate\Support\Str::limit($vnote, 120) }}
                                </div>
                            @endif
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>

                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                    <td class="fw-semibold">₱{{ number_format($payment->amount, 2) }}</td>
                    <td class="muted">{{ $payment->method }}</td>

                    <td>
                        <span class="badge-soft st-paid">
                            <span class="badge-dot"></span> PAID
                        </span>
                    </td>

                    <td class="text-end">
                        <div class="action-pills">
                            <a href="{{ route('staff.payments.edit', $payment->id) }}" class="pill pill-edit">
                                <i class="fa fa-pen"></i> Edit
                            </a>

                            {{-- ✅ changed Receipt -> View --}}
                            <a href="{{ route('staff.payments.show', $payment->id) }}" class="pill pill-view">
                                <i class="fa fa-eye"></i> View
                            </a>

                            <form action="{{ route('staff.payments.destroy', $payment->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Delete this payment?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="pill pill-del">
                                    <i class="fa fa-trash"></i> Delete
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

{{-- INSTALLMENT TABLE --}}
<div class="card-shell" id="installmentTable" style="display:none;">
    <div class="card-head">
        <h6 class="title"><i class="fa fa-layer-group"></i> Installment Plans</h6>
        <div class="hint">Showing <strong id="insVisible">{{ $installments->count() }}</strong> / <strong id="insTotal">{{ $installments->count() }}</strong></div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Treatment</th>
                    <th>Start Date</th>
                    <th>Total</th>
                    <th>Down</th>
                    <th>Balance</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody id="insTbody">
            @forelse ($installments as $plan)
                @php
                    $pname2 = strtolower(trim(($plan->patient->last_name ?? '').', '.($plan->patient->first_name ?? '')));
                    $startTs = $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->timestamp : 0;
                    $totalCost = (float)($plan->total_cost ?? 0);
                    $balance = (float)($plan->balance ?? 0);
                @endphp

                <tr class="payment-row ins-row"
                    data-patient="{{ $pname2 }}"
                    data-date="{{ $startTs }}"
                    data-amount="{{ $totalCost }}"
                    data-balance="{{ $balance }}"
                >
                    <td class="fw-semibold">
                        {{ $plan->patient->first_name ?? '' }} {{ $plan->patient->last_name ?? '' }}
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
                        @endphp

                        @if($tags->count())
                            <div class="tags">
                                @foreach($tags as $t)
                                    <span class="tag">{{ $t }}</span>
                                @endforeach
                            </div>

                            @php $vnote2 = trim((string)(optional($plan->visit)->notes ?? '')); @endphp
                            @if($vnote2 !== '')
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    Notes: {{ \Illuminate\Support\Str::limit($vnote2, 120) }}
                                </div>
                            @endif
                        @else
                            <span class="muted">N/A</span>
                        @endif
                    </td>

                    <td>{{ \Carbon\Carbon::parse($plan->start_date)->format('M d, Y') }}</td>
                    <td class="fw-semibold">₱{{ number_format($plan->total_cost, 2) }}</td>
                    <td class="muted">₱{{ number_format($plan->downpayment, 2) }}</td>
                    <td class="fw-semibold">₱{{ number_format($plan->balance, 2) }}</td>
                    <td class="muted">{{ $plan->months }} mos</td>

                    <td>
                        @php $isPaid = strtolower($plan->status ?? '') === 'fully paid'; @endphp
                        <span class="badge-soft {{ $isPaid ? 'st-paid' : 'st-pending' }}">
                            <span class="badge-dot"></span> {{ strtoupper($plan->status ?? 'PENDING') }}
                        </span>
                    </td>

                    <td class="text-end">
                        <div class="action-pills">
                            @if(!$isPaid)
                                <a href="{{ route('staff.installments.pay', $plan->id) }}" class="pill pill-pay">
                                    <i class="fa fa-circle-dollar-to-slot"></i> Pay
                                </a>
                            @endif

                            <a href="{{ route('staff.installments.edit', $plan->id) }}" class="pill pill-edit">
                                <i class="fa fa-pen"></i> Edit
                            </a>

                            {{-- ✅ changed Receipt -> View --}}
                            <a href="{{ route('staff.installments.show', $plan->id) }}" class="pill pill-view">
                                <i class="fa fa-eye"></i> View
                            </a>

                            <form action="{{ route('staff.installments.destroy', $plan) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Delete this installment plan?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pill pill-del">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No installment plans found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
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

    function showCash(){
        tabCash.classList.add('active');
        tabInstallment.classList.remove('active');
        cashTable.style.display = 'block';
        installmentTable.style.display = 'none';
        applyAll();
    }

    function showInstallment(){
        tabInstallment.classList.add('active');
        tabCash.classList.remove('active');
        cashTable.style.display = 'none';
        installmentTable.style.display = 'block';
        applyAll();
    }

    tabCash.addEventListener('click', showCash);
    tabInstallment.addEventListener('click', showInstallment);

    // Auto-open tab based on URL (?tab=installment)
    (function () {
        const params = new URLSearchParams(window.location.search);
        const tab = (params.get('tab') || '').toLowerCase();

        if (tab === 'installment' || tab === 'installments') showInstallment();
        else showCash();
    })();

    function currentTab(){
        return cashTable.style.display !== 'none' ? 'cash' : 'installment';
    }

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

            switch(mode){
                case 'date_desc': return dateB - dateA;
                case 'date_asc':  return dateA - dateB;
                case 'patient_asc': return patientA.localeCompare(patientB) || (dateB - dateA);
                case 'patient_desc': return patientB.localeCompare(patientA) || (dateB - dateA);
                case 'amount_desc': return (amtB - amtA) || (dateB - dateA);
                case 'amount_asc':  return (amtA - amtB) || (dateB - dateA);
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

    searchInput.addEventListener('keyup', applyAll);
    sortSelect.addEventListener('change', applyAll);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        sortSelect.value = 'date_desc';
        applyAll();
        searchInput.focus();
    });

    // initial apply (after tab auto-open)
    applyAll();
})();
</script>

@endsection
