@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Visits Index (Dark mode compatible)
       + Skeleton shimmer loading
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148, 163, 184, .14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);

        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);

        /* Skeleton colors */
        --skel-base: rgba(148,163,184,.18);
        --skel-shine: rgba(255,255,255,.75);
    }
    html[data-theme="dark"]{
        --soft: rgba(148, 163, 184, .16);
        --skel-base: rgba(148,163,184,.14);
        --skel-shine: rgba(255,255,255,.10);
    }

    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    .top-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .toggle-group{
        display:flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .search-box{
        position: relative;
        width: 340px;
        max-width: 100%;
        min-width: 0;
    }
    .search-box i{
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: rgba(148,163,184,.9);
        font-size: 14px;
        pointer-events: none;
    }
    html[data-theme="dark"] .search-box i{
        color: rgba(148,163,184,.75);
    }

    .search-box input{
        width: 100%;
        padding: 11px 12px 11px 38px;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border);
        background: var(--kt-input-bg);
        box-shadow: var(--kt-shadow);
        outline: none;
        transition: .15s ease;
        font-size: 14px;
        color: var(--text);
    }
    .search-box input:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
        background: var(--kt-surface);
    }

    .sort-box{
        display:flex;
        align-items:center;
        gap: 8px;
        min-width: 0;
    }
    .sort-box .sort-label{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted);
        white-space: nowrap;
    }
    .sort-select{
        min-width: 210px;
        max-width: 100%;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border);
        background: var(--kt-input-bg);
        padding: 11px 12px;
        font-size: 14px;
        color: var(--text);
        outline: none;
        transition: .15s ease;
        box-shadow: var(--kt-shadow);
    }
    .sort-select:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
        background: var(--kt-surface);
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
        background: var(--kt-surface-2);
        border-color: var(--kt-border);
        color: var(--text) !important;
    }
    .btn-ghost:hover{ background: rgba(148,163,184,.14); }
    html[data-theme="dark"] .btn-ghost:hover{ background: rgba(17,24,39,.75); }

    .btn-active{
        background: rgba(96,165,250,.14);
        border-color: rgba(96,165,250,.28);
        color: #60a5fa !important;
    }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 900;
        border-radius: 12px;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .add-btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .24);
    }

    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        color: var(--text);
        position: relative; /* needed for skeleton overlay */
    }

    .card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(148,163,184,.08), transparent);
    }
    html[data-theme="dark"] .card-head{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
    }

    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
    }

    .count-pill{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid var(--kt-border);
        background: rgba(148,163,184,.10);
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .count-pill{ background: rgba(2,6,23,.35); }

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
        color: var(--muted);
        padding: 14px 14px;
        border-bottom: 1px solid var(--soft);
        background: rgba(148,163,184,.10);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{ background: rgba(2,6,23,.35); }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(96,165,250,.08); }

    .muted{ color: var(--muted); }

    .tags{
        display:flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .tag{
        display:inline-flex;
        align-items:center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(148,163,184,.12);
        color: var(--text);
        border: 1px solid rgba(148,163,184,.18);
        white-space: nowrap;
    }
    html[data-theme="dark"] .tag{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.20);
    }

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

    .pill-view{
        background: rgba(96,165,250,.14);
        color: #60a5fa !important;
        border-color: rgba(96,165,250,.22);
    }
    .pill-view:hover{ background: rgba(96,165,250,.20); }

    .pill-edit{
        background: rgba(52,211,153,.14);
        color: #34d399 !important;
        border-color: rgba(52,211,153,.22);
    }
    .pill-edit:hover{ background: rgba(52,211,153,.20); }

    .pill-del{
        background: rgba(248,113,113,.14);
        color: #f87171 !important;
        border-color: rgba(248,113,113,.22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(248,113,113,.20); }

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .toggle-group{ width: 100%; }
    }

    /* ==========================================================
       ✅ Skeleton Loading (shimmer)
       ========================================================== */
    .kt-skel{
        position:absolute;
        inset:0;
        background: var(--kt-surface);
        z-index: 20;
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease;
    }
    .card-shell.is-loading .kt-skel{
        opacity: 1;
        pointer-events: auto; /* block clicks */
        cursor: progress;
    }
    .kt-skel__inner{ padding: 14px 10px 12px 10px; }

    .kt-skel__bar{
        height: 12px;
        border-radius: 999px;
        background: linear-gradient(
            90deg,
            var(--skel-base) 0%,
            var(--skel-shine) 45%,
            var(--skel-base) 65%
        );
        background-size: 200% 100%;
        animation: ktShimmer 1.15s linear infinite;
    }
    .kt-skel__bar.sm{ height: 10px; }

    @keyframes ktShimmer{
        to { background-position: -200% 0; }
    }

    /* rows layout differs for All Visits vs Patients view */
    .kt-skel__head,
    .kt-skel__row{
        display:grid;
        gap: 12px;
        padding: 14px 14px;
        align-items: center;
        border-bottom: 1px solid var(--soft);
    }
    .kt-skel__head{
        padding: 10px 14px 14px 14px;
        border-bottom: 1px solid var(--soft);
    }

    .card-shell[data-skel="all"] .kt-skel__head,
    .card-shell[data-skel="all"] .kt-skel__row{
        grid-template-columns: 1.2fr .8fr 1fr 1.4fr 1.6fr .8fr;
    }

    .card-shell[data-skel="patients"] .kt-skel__head,
    .card-shell[data-skel="patients"] .kt-skel__row{
        grid-template-columns: 1.4fr 1fr .8fr 1fr;
    }

    @media (prefers-reduced-motion: reduce){
        .kt-skel__bar{ animation: none !important; }
    }
</style>

@php
    $isAll = ($view ?? 'patients') === 'all';
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Visits</h2>
        <p class="subtitle">
            {{ $isAll ? 'Manage and view all visits (every record)' : 'Select a patient to view their visit records' }}
        </p>
    </div>

    <div class="top-actions">
        <div class="toggle-group">
            <a href="{{ route('staff.visits.index') }}"
               class="btnx {{ !$isAll ? 'btn-active' : 'btn-ghost' }}">
                <i class="fa fa-user"></i> Patients
            </a>

            <a href="{{ route('staff.visits.index', ['view' => 'all']) }}"
               class="btnx {{ $isAll ? 'btn-active' : 'btn-ghost' }}">
                <i class="fa fa-list"></i> All Visits
            </a>
        </div>

        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="visitSearch"
                   placeholder="{{ $isAll ? 'Search by patient name, dentist, date, notes, or treatment…' : 'Search patient name…' }}">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>

            @if($isAll)
                <select id="visitSort" class="sort-select">
                    <option value="vdate_desc">Visit date (newest)</option>
                    <option value="vdate_asc">Visit date (oldest)</option>
                    <option value="created_desc">Date added (newest)</option>
                    <option value="created_asc">Date added (oldest)</option>
                    <option value="patient_asc">Patient (A–Z)</option>
                    <option value="patient_desc">Patient (Z–A)</option>
                    <option value="treat_desc">Most treatments</option>
                    <option value="treat_asc">Least treatments</option>
                </select>
            @else
                <select id="visitSort" class="sort-select">
                    <option value="patient_asc">Patient (A–Z)</option>
                    <option value="patient_desc">Patient (Z–A)</option>
                    <option value="last_desc">Last visit (newest)</option>
                    <option value="last_asc">Last visit (oldest)</option>
                    <option value="count_desc">Total visits (most)</option>
                    <option value="count_asc">Total visits (least)</option>
                </select>
            @endif
        </div>

        <button type="button" id="clearFilters" class="btnx btn-ghost">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        {{-- Visits template download --}}
        <a href="{{ route('staff.visits.template') }}" class="btnx btn-ghost" title="Download Excel template">
            <i class="fa fa-file-excel"></i> Template
        </a>

        {{-- Visits import --}}
        <form id="visitImportForm" action="{{ route('staff.visits.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;">
            @csrf
            <input id="visitImportFile" type="file" name="file" accept=".xlsx,.xls,.csv" style="display:none" required>
            <button type="button" id="visitImportBtn" class="btnx btn-ghost" title="Import Excel file">
                <i class="fa fa-cloud-arrow-up"></i> Import
            </button>
        </form>

        <a href="{{ route('staff.visits.create') }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Visit
        </a>
    </div>
</div>

{{-- Flash messages --}}
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

<div class="card-shell" id="visitsCard" data-skel="{{ $isAll ? 'all' : 'patients' }}">
    <div class="card-head">
        <div class="hint">
            <span class="count-pill">
                <i class="fa fa-calendar-check"></i>
                Showing <strong id="visibleCount">0</strong> / <strong id="totalCount">0</strong>
            </span>
        </div>
        <div class="hint">Tip: search + sort works together</div>
    </div>

    {{-- ✅ Skeleton overlay --}}
    <div class="kt-skel" id="visitsSkeleton" aria-hidden="true">
        <div class="kt-skel__inner">
            <div class="kt-skel__head">
                @if($isAll)
                    <div class="kt-skel__bar sm" style="width:60%"></div>
                    <div class="kt-skel__bar sm" style="width:70%"></div>
                    <div class="kt-skel__bar sm" style="width:55%"></div>
                    <div class="kt-skel__bar sm" style="width:75%"></div>
                    <div class="kt-skel__bar sm" style="width:62%"></div>
                    <div class="kt-skel__bar sm" style="width:45%"></div>
                @else
                    <div class="kt-skel__bar sm" style="width:65%"></div>
                    <div class="kt-skel__bar sm" style="width:58%"></div>
                    <div class="kt-skel__bar sm" style="width:46%"></div>
                    <div class="kt-skel__bar sm" style="width:40%"></div>
                @endif
            </div>
            <div id="visitsSkelRows"></div>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
            @if($isAll)
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Visit Date</th>
                        <th>Assigned Dentist</th>
                        <th>Reason / Notes</th>
                        <th>Treatments</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody id="visitTableBody">
                    @forelse ($visits as $visit)
                        @php
                            $patientName = strtolower(($visit->patient->last_name ?? '').', '.($visit->patient->first_name ?? ''));
                            $visitTs = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->timestamp : 0;
                            $createdTs = optional($visit->created_at)->timestamp ?? 0;
                            $treatCount = $visit->procedures->count();

                            $dentistLabel = trim((string)($visit->dentist_name ?? '')) !== ''
                                ? $visit->dentist_name
                                : (optional($visit->doctor)->name ?: '—');
                        @endphp

                        <tr class="visit-row"
                            data-patient="{{ $patientName }}"
                            data-vdate="{{ $visitTs }}"
                            data-created="{{ $createdTs }}"
                            data-treat="{{ $treatCount }}"
                        >
                            <td class="fw-semibold">
                                {{ $visit->patient->last_name }}, {{ $visit->patient->first_name }}
                            </td>

                            <td>
                                {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') : '—' }}
                            </td>

                            <td class="muted">
                                {{ $dentistLabel }}
                            </td>

                            <td class="muted">
                                {{ $visit->notes ?? '—' }}
                            </td>

                            <td>
                                @if($visit->procedures->count() > 0)
                                    @php
                                        $chips = collect();

                                        foreach($visit->procedures->groupBy(fn($p) => $p->service?->name ?? '—') as $serviceName => $rows){
                                            $rows = $rows->values();

                                            $hasTooth = $rows->contains(fn($p) => !empty($p->tooth_number) || !empty($p->surface));

                                            $notes = $rows->pluck('notes')
                                                ->filter(fn($n) => trim((string)$n) !== '')
                                                ->map(fn($n) => trim((string)$n))
                                                ->unique()
                                                ->values();

                                            if(!$hasTooth && $notes->count()){
                                                foreach($notes as $n){
                                                    $chips->push($serviceName.' — '.\Illuminate\Support\Str::limit($n, 28));
                                                }
                                            } else {
                                                $chips->push($serviceName.' ('.$rows->count().')');
                                            }
                                        }
                                    @endphp

                                    <div class="tags">
                                        @foreach($chips as $chip)
                                            <span class="tag">{{ $chip }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="action-pills">
                                    <a href="{{ route('staff.visits.edit', [$visit->id, 'return' => url()->full()]) }}" class="pill pill-edit">
                                        <i class="fa fa-pen"></i> Edit
                                    </a>

                                    <a href="{{ route('staff.visits.show', [$visit->id, 'return' => url()->full()]) }}" class="pill pill-view">
                                        <i class="fa fa-eye"></i> View
                                    </a>

                                    {{-- ✅ Animated confirm delete --}}
                                    <form id="del-visit-{{ $visit->id }}" action="{{ route('staff.visits.destroy', $visit->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="pill pill-del"
                                                data-confirm="Delete this visit? This can’t be undone."
                                                data-confirm-title="Confirm delete"
                                                data-confirm-yes="Delete"
                                                data-confirm-form="#del-visit-{{ $visit->id }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No visits found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            @else
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Last Visit</th>
                        <th>Total Visits</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody id="visitTableBody">
                    @forelse ($patients as $patient)
                        @php
                            $patientLabel = trim(($patient->last_name ?? '').', '.($patient->first_name ?? ''));
                            $patientKey = strtolower($patientLabel);
                            $lastTs = $patient->last_visit_date ? \Carbon\Carbon::parse($patient->last_visit_date)->timestamp : 0;
                            $count = (int)($patient->visits_count ?? 0);
                        @endphp

                        <tr class="visit-row"
                            data-patient="{{ $patientKey }}"
                            data-last="{{ $lastTs }}"
                            data-count="{{ $count }}"
                        >
                            <td class="fw-semibold">
                                {{ $patientLabel }}
                            </td>

                            <td class="muted">
                                {{ $patient->last_visit_date ? \Carbon\Carbon::parse($patient->last_visit_date)->format('m/d/Y') : '—' }}
                            </td>

                            <td>
                                <span class="count-pill">
                                    <i class="fa fa-calendar"></i>
                                    <strong>{{ $count }}</strong>
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="action-pills">
                                    <a href="{{ route('staff.patients.visits', $patient->id) }}" class="pill pill-view">
                                        <i class="fa fa-eye"></i> View Records
                                    </a>

                                    <a href="{{ route('staff.visits.create', ['patient_id' => $patient->id]) }}" class="pill pill-edit">
                                        <i class="fa fa-plus"></i> Add Visit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No patients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            @endif
        </table>
    </div>
</div>

<script>
(() => {
    // Import button wiring
    const importBtn = document.getElementById('visitImportBtn');
    const importFile = document.getElementById('visitImportFile');
    const importForm = document.getElementById('visitImportForm');

    importBtn?.addEventListener('click', () => importFile?.click());
    importFile?.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) {
            importForm?.submit();
        }
    });

    const card = document.getElementById('visitsCard');
    const skelRowsEl = document.getElementById('visitsSkelRows');

    const searchInput = document.getElementById('visitSearch');
    const sortSelect  = document.getElementById('visitSort');
    const tbody       = document.getElementById('visitTableBody');
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');
    const resetBtn    = document.getElementById('clearFilters');

    const rowsAll = Array.from(document.querySelectorAll('.visit-row'));

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    // Skeleton
    function buildSkeletonRows(n = 8){
        if (!skelRowsEl || !card) return;
        const mode = card.getAttribute('data-skel') || 'patients';

        skelRowsEl.innerHTML = '';
        for (let i=0;i<n;i++){
            const row = document.createElement('div');
            row.className = 'kt-skel__row';

            if (mode === 'all'){
                row.innerHTML = `
                    <div class="kt-skel__bar" style="width:${62 + (i%3)*10}%"></div>
                    <div class="kt-skel__bar" style="width:${48 + (i%4)*9}%"></div>
                    <div class="kt-skel__bar" style="width:${55 + (i%3)*8}%"></div>
                    <div class="kt-skel__bar" style="width:${72 + (i%3)*7}%"></div>
                    <div class="kt-skel__bar" style="width:${64 + (i%4)*6}%"></div>
                    <div class="kt-skel__bar" style="width:${42 + (i%3)*10}%"></div>
                `;
            } else {
                row.innerHTML = `
                    <div class="kt-skel__bar" style="width:${65 + (i%3)*10}%"></div>
                    <div class="kt-skel__bar" style="width:${55 + (i%4)*8}%"></div>
                    <div class="kt-skel__bar" style="width:${40 + (i%5)*8}%"></div>
                    <div class="kt-skel__bar" style="width:${46 + (i%3)*10}%"></div>
                `;
            }

            skelRowsEl.appendChild(row);
        }
    }
    buildSkeletonRows(9);

    let skelTimer = null;
    let skelShownAt = 0;

    function showSkeletonImmediate(minMs = 240){
        if (!card) return;
        clearTimeout(skelTimer);
        card.classList.add('is-loading');
        skelShownAt = Date.now();
        skelTimer = setTimeout(() => {}, minMs);
    }

    function showSkeletonSoft(){
        if (!card) return;
        clearTimeout(skelTimer);
        skelTimer = setTimeout(() => showSkeletonImmediate(220), 90);
    }

    function hideSkeleton(){
        if (!card) return;
        clearTimeout(skelTimer);
        const elapsed = Date.now() - skelShownAt;
        const minMs = 220;
        const wait = Math.max(0, minMs - elapsed);
        setTimeout(() => card.classList.remove('is-loading'), wait);
    }

    function applySearch() {
        const keyword = normalize(searchInput.value);

        let visible = 0;
        rowsAll.forEach(row => {
            const text = normalize(row.textContent);
            const show = text.includes(keyword);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCountEl.textContent = visible;
    }

    function getComparable(row, mode){
        const ds = row.dataset;

        // ALL VISITS view modes
        if (mode && mode.startsWith('vdate')) return Number(ds.vdate || 0);
        if (mode && mode.startsWith('created')) return Number(ds.created || 0);
        if (mode && mode.startsWith('treat')) return Number(ds.treat || 0);

        // PATIENTS view modes
        if (mode && mode.startsWith('last')) return Number(ds.last || 0);
        if (mode && mode.startsWith('count')) return Number(ds.count || 0);

        // Shared
        if (mode && mode.startsWith('patient')) return ds.patient || '';

        return Number(ds.vdate || ds.last || 0);
    }

    function applySort() {
        if (!sortSelect) return;

        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const va = getComparable(a, mode);
            const vb = getComparable(b, mode);

            if (typeof va === 'string' || typeof vb === 'string') {
                const A = String(va), B = String(vb);
                if (A < B) return mode.endsWith('_desc') ? 1 : -1;
                if (A > B) return mode.endsWith('_desc') ? -1 : 1;

                const tb = Number(b.dataset.vdate || b.dataset.last || 0);
                const ta = Number(a.dataset.vdate || a.dataset.last || 0);
                return tb - ta;
            }

            if (va === vb) {
                const cb = Number(b.dataset.created || 0);
                const ca = Number(a.dataset.created || 0);
                if (cb !== ca) return cb - ca;
                return String(a.dataset.patient || '').localeCompare(String(b.dataset.patient || ''));
            }

            const asc = mode.endsWith('_asc');
            return asc ? (va - vb) : (vb - va);
        });

        sorted.forEach(r => tbody.appendChild(r));
    }

    function applyAll(){
        applySort();
        applySearch();
    }

    totalCountEl.textContent = rowsAll.length;
    visibleCountEl.textContent = rowsAll.length;

    // Events with skeleton
    let searchDeb = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDeb);
        showSkeletonSoft();
        searchDeb = setTimeout(() => {
            applySearch();
            hideSkeleton();
        }, 140);
    });

    sortSelect && sortSelect.addEventListener('change', () => {
        showSkeletonImmediate(260);
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
        });
    });

    resetBtn.addEventListener('click', () => {
        showSkeletonImmediate(260);
        searchInput.value = '';
        if (sortSelect) sortSelect.selectedIndex = 0;
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
            searchInput.focus();
        });
    });

    // Initial load feel
    showSkeletonImmediate(220);
    requestAnimationFrame(() => {
        applyAll();
        hideSkeleton();
    });
})();
</script>

@endsection
