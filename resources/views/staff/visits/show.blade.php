@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Visits Show (Dark mode compatible)
       - Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                           --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148,163,184,.14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);
        --muted2: rgba(148,163,184,.72);

        --brand1: #0d6efd;
        --brand2: #1e90ff;

        --radius: 16px;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
        --muted2: rgba(148,163,184,.68);
    }

    .max-wrap{ max-width: 1100px; }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin: 8px 0 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    .btn-ghostx, .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
        user-select: none;
    }
    .btn-ghostx{
        border: 1px solid var(--kt-border);
        color: var(--text);
        background: var(--kt-surface-2);
    }
    .btn-ghostx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btn-ghostx:hover{
        background: rgba(17,24,39,.75);
    }

    .btn-primaryx{
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
    }
    .btn-primaryx:hover{ transform: translateY(-1px); filter: brightness(1.02); }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        backdrop-filter: blur(8px);
        color: var(--text);
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
        background: linear-gradient(180deg, rgba(148,163,184,.08), transparent);
    }
    html[data-theme="dark"] .card-head{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
    }

    .card-title{
        margin: 0;
        font-weight: 950;
        font-size: 14px;
        color: var(--text);
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .card-hint{
        font-size: 12px;
        color: var(--muted2);
        font-weight: 900;
    }
    .card-bodyx{ padding: 18px; }

    /* Pills / Chips */
    .pill{
        font-size: 12px;
        font-weight: 950;
        color: var(--text);
        background: rgba(148,163,184,.12);
        border: 1px solid rgba(148,163,184,.18);
        padding: 6px 10px;
        border-radius: 999px;
        display:inline-flex;
        align-items:center;
        gap: 8px;
        white-space: nowrap;
    }
    html[data-theme="dark"] .pill{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }

    .pill-blue{
        background: rgba(96,165,250,.14);
        border-color: rgba(96,165,250,.22);
        color: #60a5fa;
    }
    .pill-green{
        background: rgba(34,197,94,.14);
        border-color: rgba(34,197,94,.22);
        color: #22c55e;
    }

    /* Summary cards */
    .summary-grid{
        display:grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    @media (min-width: 768px){
        .summary-grid{ grid-template-columns: 1fr 1fr; }
    }
    @media (min-width: 992px){
        .summary-grid{ grid-template-columns: 1.2fr .8fr .8fr .9fr; }
    }

    .summary-card{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        border-radius: 16px;
        padding: 14px 14px;
        display:flex;
        gap: 12px;
        align-items:flex-start;
        min-height: 86px;
    }
    .summary-ic{
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        background: rgba(96,165,250,.14);
        border: 1px solid rgba(96,165,250,.20);
        flex: 0 0 auto;
        color: #60a5fa;
    }
    .summary-ttl{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted2);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin: 0 0 4px 0;
    }
    .summary-val{
        font-size: 15px;
        font-weight: 950;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
        word-break: break-word;
    }
    .summary-sub{
        font-size: 12px;
        color: var(--muted);
        margin-top: 6px;
        font-weight: 800;
    }

    /* Notes */
    .notes-box{
        margin-top: 12px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface);
        border-radius: 16px;
        padding: 14px;
    }
    .notes-title{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted2);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 8px;
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .notes-text{
        font-size: 14px;
        font-weight: 750;
        color: var(--text);
        opacity: .92;
        margin: 0;
        white-space: pre-wrap;
    }
    .muted-dash{ color: var(--muted2); font-weight: 900; }

    /* Procedures */
    .section-title{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin: 14px 0 10px;
        flex-wrap: wrap;
    }
    .section-title h5{
        margin: 0;
        font-weight: 950;
        letter-spacing: -.2px;
        color: var(--text);
    }

    .table-wrap{
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
    }
    table.proc-table{
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .proc-table thead th{
        background: rgba(148,163,184,.12);
        color: var(--muted);
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
        white-space: nowrap;
    }
    html[data-theme="dark"] .proc-table thead th{
        background: rgba(2,6,23,.35);
    }

    .proc-table tbody td{
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
        color: var(--text);
        font-size: 14px;
        font-weight: 750;
    }
    .proc-table tbody tr:hover td{
        background: rgba(96,165,250,.08);
    }

    .chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        font-weight: 950;
        font-size: 13px;
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .chip{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }

    .chip-blue{
        border-color: rgba(96,165,250,.22);
        background: rgba(96,165,250,.14);
        color: #60a5fa;
    }

    .teeth-chips{
        display:flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
</style>

@php
    $patientName = trim(($visit->patient->first_name ?? '') . ' ' . ($visit->patient->last_name ?? ''));
    $datePretty = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') : '—';

    $dentistLabel = trim((string)($visit->dentist_name ?? '')) !== ''
        ? $visit->dentist_name
        : (optional($visit->doctor)->name ?: '—');

    $procCount = $visit->procedures->count();
    $totalCost = (float) $visit->procedures->sum(fn($p) => (float)($p->price ?? 0));

    $teeth = $visit->procedures
        ->pluck('tooth_number')
        ->filter(fn($t) => trim((string)$t) !== '')
        ->map(fn($t) => trim((string)$t))
        ->unique()
        ->values();
@endphp

<div class="page-head max-wrap">
    <div>
        <h2 class="page-title">Visit Details</h2>
        <p class="subtitle">Overview of this patient visit.</p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <x-back-button
            fallback="{{ route('staff.visits.index') }}"
            class="btn-ghostx"
            label="Back"
        />

        <a href="{{ route('staff.visits.edit', [$visit->id, 'return' => url()->full()]) }}" class="btn-primaryx">
            <i class="fa fa-pen"></i> Edit Visit
        </a>
    </div>
</div>

<div class="card-shell max-wrap">
    <div class="card-head">
        <div class="card-title">
            <i class="fa fa-calendar-check"></i> Visit Summary
            <span class="pill pill-blue">#{{ $visit->id }}</span>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <span class="pill">
                <i class="fa fa-list-check"></i>
                {{ $procCount }} procedure{{ $procCount === 1 ? '' : 's' }}
            </span>
            <span class="pill pill-green">
                <i class="fa fa-peso-sign"></i>
                ₱{{ number_format($totalCost, 2) }}
            </span>
        </div>
    </div>

    <div class="card-bodyx">

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-ic"><i class="fa fa-user"></i></div>
                <div>
                    <div class="summary-ttl">Patient</div>
                    <div class="summary-val">{{ $patientName ?: '—' }}</div>
                    <div class="summary-sub">Record linked to this visit</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-ic"><i class="fa fa-user-doctor"></i></div>
                <div>
                    <div class="summary-ttl">Assigned Dentist</div>
                    <div class="summary-val">{{ $dentistLabel }}</div>
                    <div class="summary-sub">Chosen from Admin → Doctors</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-ic"><i class="fa fa-calendar-day"></i></div>
                <div>
                    <div class="summary-ttl">Visit Date</div>
                    <div class="summary-val">{{ $datePretty }}</div>
                    <div class="summary-sub">Date of procedure</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-ic"><i class="fa fa-tooth"></i></div>
                <div>
                    <div class="summary-ttl">Teeth Touched</div>
                    <div class="summary-val">{{ $teeth->count() ?: '—' }}</div>
                    <div class="summary-sub">Unique tooth numbers</div>
                </div>
            </div>
        </div>

        @if($teeth->count() > 0)
            <div class="teeth-chips">
                @foreach($teeth as $t)
                    <span class="chip chip-blue"><i class="fa fa-tooth"></i> {{ $t }}</span>
                @endforeach
            </div>
        @endif

        <div class="notes-box">
            <div class="notes-title">
                <i class="fa fa-note-sticky"></i> Notes
            </div>
            <p class="notes-text">{{ trim((string)$visit->notes) !== '' ? $visit->notes : '—' }}</p>
        </div>

        <div class="section-title">
            <h5>Treatments / Procedures</h5>
            <span class="pill"><i class="fa fa-clock"></i> Updated automatically</span>
        </div>

        @if($procCount > 0)
            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="proc-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Tooth</th>
                                <th>Surface</th>
                                <th>Shade</th>
                                <th>Notes</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visit->procedures as $p)
                                <tr>
                                    <td>
                                        <span class="chip">
                                            <i class="fa fa-stethoscope"></i>
                                            {{ $p->service?->name ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if(trim((string)$p->tooth_number) !== '')
                                            <span class="chip chip-blue"><i class="fa fa-tooth"></i> {{ $p->tooth_number }}</span>
                                        @else
                                            <span class="muted-dash">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if(trim((string)$p->surface) !== '')
                                            <span class="chip">{{ $p->surface }}</span>
                                        @else
                                            <span class="muted-dash">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if(trim((string)$p->shade) !== '')
                                            <span class="chip">{{ $p->shade }}</span>
                                        @else
                                            <span class="muted-dash">—</span>
                                        @endif
                                    </td>

                                    <td>{{ trim((string)$p->notes) !== '' ? $p->notes : '—' }}</td>

                                    <td class="text-end" style="font-weight:950;">
                                        {{ $p->price !== null ? '₱'.number_format((float)$p->price, 2) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="notes-box">
                <div class="notes-title"><i class="fa fa-circle-info"></i> No procedures</div>
                <p class="notes-text">No procedures recorded for this visit.</p>
            </div>
        @endif

    </div>
</div>

@endsection
