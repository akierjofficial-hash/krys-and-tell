@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Patients Index (Dark mode compatible)
       + Skeleton shimmer loading for table
       ========================================================== */

    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148, 163, 184, .14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);
        --muted2: rgba(148, 163, 184, .75);

        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;

        --thead-h: 44px; /* JS will update this */

        /* Skeleton colors */
        --skel-base: rgba(148,163,184,.18);
        --skel-shine: rgba(255,255,255,.75);
    }

    html[data-theme="dark"]{
        --soft: rgba(148, 163, 184, .16);
        --muted2: rgba(248, 250, 252, .62);

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
        font-weight: 900;
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
    }

    /* Search */
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
        color: var(--muted);
        font-size: 14px;
        pointer-events: none;
        opacity: .85;
    }
    .search-box input{
        width: 100%;
        padding: 11px 12px 11px 38px;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border);
        background: var(--kt-input-bg);
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
        outline: none;
        transition: .15s ease;
        font-size: 14px;
        color: var(--text);
    }
    .search-box input::placeholder{ color: rgba(148, 163, 184, .85); }
    html[data-theme="dark"] .search-box input::placeholder{ color: rgba(248, 250, 252, .55); }
    .search-box input:focus{
        border-color: rgba(96,165,250,.55);
        box-shadow: 0 0 0 4px rgba(96,165,250,.18);
    }

    /* Sort */
    .sort-box{
        display:flex;
        align-items:center;
        gap: 8px;
        padding: 0;
    }
    .sort-box .sort-label{
        font-size: 12px;
        font-weight: 900;
        color: var(--muted);
        letter-spacing: .02em;
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
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .sort-select:focus{
        border-color: rgba(96,165,250,.55);
        box-shadow: 0 0 0 4px rgba(96,165,250,.18);
    }
    html[data-theme="dark"] .sort-select,
    html[data-theme="dark"] .sort-select option{
        background-color: rgba(17,24,39,.98) !important;
        color: var(--kt-text) !important;
    }

    /* Buttons */
    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }

    .add-btn{
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        color: #fff !important;
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
    }
    .add-btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .24);
    }

    .btn-green{
        background: rgba(34, 197, 94, .12);
        border-color: rgba(34, 197, 94, .25);
        color: #15803d !important;
    }
    .btn-green:hover{ background: rgba(34, 197, 94, .18); }

    .btn-purple{
        background: rgba(124, 58, 237, .12);
        border-color: rgba(124, 58, 237, .25);
        color: #5b21b6 !important;
    }
    .btn-purple:hover{ background: rgba(124, 58, 237, .18); }

    .btn-ghost{
        background: rgba(148,163,184,.12);
        border-color: rgba(148,163,184,.22);
        color: var(--text) !important;
    }
    html[data-theme="dark"] .btn-ghost{
        background: rgba(2,6,23,.45);
        border-color: rgba(148,163,184,.22);
        color: var(--kt-text) !important;
    }
    .btn-ghost:hover{ background: rgba(148,163,184,.16); }
    html[data-theme="dark"] .btn-ghost:hover{ background: rgba(17,24,39,.85); }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        color: var(--text);
        position: relative;
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
        font-weight: 900;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--text);
        white-space: nowrap;
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
        color: var(--muted);
        padding: 14px 14px;
        border-bottom: 1px solid var(--soft);
        background: rgba(248, 250, 252, .85);
        position: sticky;
        top: 0;
        z-index: 5;
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{
        background: rgba(2, 6, 23, .55);
        border-bottom-color: rgba(148,163,184,.16);
        color: var(--kt-muted);
    }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        background: transparent;
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }
    html[data-theme="dark"] tbody tr:hover{ background: rgba(96,165,250,.08); }

    .muted{ color: var(--muted); }

    .name-cell{
        display:flex;
        flex-direction:column;
        line-height: 1.1;
        gap: 4px;
    }
    .name-cell .main{
        font-weight: 900;
        letter-spacing: -.1px;
    }
    .name-cell .sub{
        font-size: 12px;
        color: var(--muted2);
        font-weight: 700;
    }

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
        font-weight: 800;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-edit{
        background: rgba(34, 197, 94, .12);
        color: #15803d !important;
        border-color: rgba(34, 197, 94, .22);
    }
    .pill-edit:hover{ background: rgba(34, 197, 94, .18); }

    .pill-view{
        background: rgba(59, 130, 246, .12);
        color: #1d4ed8 !important;
        border-color: rgba(59, 130, 246, .22);
    }
    .pill-view:hover{ background: rgba(59, 130, 246, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color: #b91c1c !important;
        border-color: rgba(239, 68, 68, .22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .18); }

    .toolbar-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
        width: 100%;
    }

    /* ==========================================================
       Option A: Letter separator rows (sticky under thead)
       ========================================================== */
    tr.alpha-row td{
        padding: 10px 14px;
        border-bottom: 1px solid var(--soft);
        background: var(--kt-surface-2);
        position: sticky;
        top: var(--thead-h);
        z-index: 4;
        backdrop-filter: blur(8px);
    }
    html[data-theme="dark"] tr.alpha-row td{
        background: rgba(2,6,23,.55);
    }

    .alpha-pill{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        width: 100%;
    }
    .alpha-left{
        display:flex;
        align-items:center;
        gap: 10px;
        min-width: 0;
    }
    .alpha-letter{
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-weight: 950;
        letter-spacing: .02em;
        background: rgba(96,165,250,.16);
        color: #60a5fa;
        border: 1px solid rgba(96,165,250,.22);
        flex: 0 0 auto;
    }
    .alpha-meta{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
        white-space: nowrap;
    }

    /* ==========================================================
       Option C: A–Z Jump Index (desktop fixed, mobile horizontal)
       ========================================================== */
    .alpha-index{
        position: fixed;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        display:flex;
        flex-direction:column;
        gap: 4px;
        padding: 10px 8px;
        border-radius: 14px;
        background: var(--kt-surface);
        border: 1px solid var(--kt-border);
        box-shadow: var(--kt-shadow);
        z-index: 60;
        max-height: 70vh;
        overflow: auto;
        scrollbar-width: thin;
        user-select: none;
    }
    .alpha-btn{
        width: 28px;
        height: 22px;
        border-radius: 8px;
        border: 1px solid transparent;
        background: transparent;
        color: var(--muted);
        font-weight: 950;
        font-size: 11px;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor: pointer;
        transition: .12s ease;
        flex: 0 0 auto;
    }
    .alpha-btn:hover{
        background: rgba(96,165,250,.12);
        color: var(--text);
    }
    .alpha-btn.active{
        background: rgba(96,165,250,.16);
        color: #60a5fa;
        border-color: rgba(96,165,250,.25);
    }
    .alpha-btn.disabled{
        opacity: .35;
        pointer-events: none;
    }

    @media (max-width: 1024px){
        .alpha-index{
            position: sticky;
            top: 0;
            right: auto;
            left: auto;
            transform: none;
            flex-direction: row;
            max-height: none;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 10px;
            margin: 10px;
            border-radius: 14px;
        }
        .alpha-btn{
            width: 30px;
            height: 26px;
            font-size: 12px;
        }
    }

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .toolbar-row{ flex-direction: column; align-items: stretch; }
        .alpha-index{ margin: 10px 10px 0 10px; }
    }

    /* ==========================================================
       ✅ Skeleton Loading (shimmer)
       ========================================================== */
    .kt-skel{
        position: absolute;
        inset: 0;
        background: var(--kt-surface);
        z-index: 70; /* above card content; alpha-index is fixed so we hide it via JS */
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease;
    }
    .card-shell.is-loading .kt-skel{
        opacity: 1;
        pointer-events: auto; /* block clicks during “loading” */
        cursor: progress;
    }

    .kt-skel__inner{
        padding: 14px 10px 12px 10px;
    }

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
    .kt-skel__bar.lg{ height: 14px; }

    @keyframes ktShimmer{
        to { background-position: -200% 0; }
    }

    /* nice “row” spacing */
    .kt-skel__row{
        display:grid;
        grid-template-columns: 1.8fr .8fr .9fr 1fr .9fr;
        gap: 12px;
        padding: 14px 14px;
        border-bottom: 1px solid var(--soft);
        align-items: center;
    }
    .kt-skel__row:first-child{
        border-top: 1px solid var(--soft);
        border-radius: 12px 12px 0 0;
    }

    /* header mimic */
    .kt-skel__head{
        display:grid;
        grid-template-columns: 1.8fr .8fr .9fr 1fr .9fr;
        gap: 12px;
        padding: 10px 14px 14px 14px;
    }

    /* reduce motion */
    @media (prefers-reduced-motion: reduce){
        .kt-skel__bar{ animation: none !important; }
    }
</style>

{{-- Header --}}
<div class="page-head">
    <div>
        <h2 class="page-title">Patients</h2>
        <p class="subtitle">Manage patient records</p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="patientSearch" placeholder="Search by name, gender, birthdate, or contact…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
            <select id="patientSort" class="sort-select">
                <option value="created_desc">Date added (newest)</option>
                <option value="created_asc">Date added (oldest)</option>

                {{-- ✅ default sort --}}
                <option value="lname_asc" selected>Last name (A–Z)</option>

                <option value="lname_desc">Last name (Z–A)</option>
                <option value="fname_asc">First name (A–Z)</option>
                <option value="fname_desc">First name (Z–A)</option>
                <option value="bday_asc">Birthdate (oldest first)</option>
                <option value="bday_desc">Birthdate (youngest first)</option>
            </select>
        </div>

        <button type="button" id="clearFilters" class="btnx btn-ghost">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        {{-- Export --}}
        <a href="{{ route('staff.patients.export') }}" class="btnx btn-green">
            <i class="fa fa-file-excel"></i> Export
        </a>

        {{-- Import --}}
        <form id="importForm" action="{{ route('staff.patients.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;">
            @csrf
            <input id="patientFile" type="file" name="file" accept=".xlsx,.xls,.csv" style="display:none" required>
            <button type="button" id="importBtn" class="btnx btn-purple">
                <i class="fa fa-cloud-arrow-up"></i> Import
            </button>
        </form>

        <a href="{{ route('staff.patients.create') }}" class="btnx add-btn">
            <i class="fa fa-plus"></i> Add Patient
        </a>
    </div>
</div>

{{-- Table Card --}}
<div class="card-shell" id="patientsCard">
    <div class="card-head">
        <div class="toolbar-row">
            <div class="hint">
                <span class="count-pill">
                    <i class="fa fa-users"></i>
                    Showing <strong id="visibleCount">{{ $patients->count() }}</strong> / <strong id="totalCount">{{ $patients->count() }}</strong>
                </span>
            </div>
            <div class="hint">Tip: search + sort works together</div>
        </div>
    </div>

    {{-- ✅ Option C: A–Z jump index (JS fills this) --}}
    <div class="alpha-index" id="alphaIndex" style="display:none;" aria-label="Jump to letter"></div>

    {{-- ✅ Skeleton overlay (JS fills rows) --}}
    <div class="kt-skel" id="patientsSkeleton" aria-hidden="true">
        <div class="kt-skel__inner">
            <div class="kt-skel__head">
                <div class="kt-skel__bar sm" style="width:55%"></div>
                <div class="kt-skel__bar sm" style="width:60%"></div>
                <div class="kt-skel__bar sm" style="width:70%"></div>
                <div class="kt-skel__bar sm" style="width:65%"></div>
                <div class="kt-skel__bar sm" style="width:45%"></div>
            </div>
            <div id="patientsSkelRows"></div>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        <table id="patientsTable">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Gender</th>
                    <th>Birthdate</th>
                    <th>Contact</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody id="patientTableBody">
                @forelse ($patients as $patient)
                    <tr class="patient-row"
                        data-lname="{{ strtolower($patient->last_name ?? '') }}"
                        data-fname="{{ strtolower($patient->first_name ?? '') }}"
                        data-created="{{ optional($patient->created_at)->timestamp ?? 0 }}"
                        data-bday="{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->timestamp : 0 }}"
                    >
                        <td>
                            <div class="name-cell">
                                <div class="main">
                                    {{ $patient->last_name }}, {{ $patient->first_name }}
                                    @if(!empty($patient->middle_name))
                                        <span class="muted"> {{ $patient->middle_name }}</span>
                                    @endif
                                </div>
                                <div class="sub">
                                    Added:
                                    {{ optional($patient->created_at)->format('m/d/Y') ?? '—' }}
                                </div>
                            </div>
                        </td>

                        <td>{{ $patient->gender }}</td>
                        <td>{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('m/d/Y') : '—' }}</td>
                        <td class="muted">{{ $patient->contact_number ?? '—' }}</td>

                        <td class="text-end">
                            <div class="action-pills">
                                <a href="{{ route('staff.patients.edit', $patient->id) }}" class="pill pill-edit">
                                    <i class="fa fa-pen"></i> Edit
                                </a>

                                <a href="{{ route('staff.patients.show', $patient->id) }}" class="pill pill-view">
                                    <i class="fa fa-eye"></i> View
                                </a>

                                {{-- ✅ Animated confirm delete (NO nested forms) --}}
                                <form id="del-{{ $patient->id }}" action="{{ route('staff.patients.destroy', $patient->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="pill pill-del"
                                            data-confirm="Delete this patient? This can’t be undone."
                                            data-confirm-title="Confirm delete"
                                            data-confirm-yes="Delete"
                                            data-confirm-form="#del-{{ $patient->id }}">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyStateRow">
                        <td colspan="5" class="text-center text-muted py-4">
                            No patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('patientSearch');
    const sortSelect  = document.getElementById('patientSort');
    const tbody       = document.getElementById('patientTableBody');
    const table       = document.getElementById('patientsTable');
    const alphaIndex  = document.getElementById('alphaIndex');

    const card        = document.getElementById('patientsCard');
    const skelWrap    = document.getElementById('patientsSkeleton');
    const skelRowsEl  = document.getElementById('patientsSkelRows');

    const rowsAll     = Array.from(document.querySelectorAll('.patient-row'));
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');
    const resetBtn    = document.getElementById('clearFilters');

    const emptyStateRow = document.getElementById('emptyStateRow');

    // Build skeleton rows once
    function buildSkeletonRows(n = 8){
        if (!skelRowsEl) return;
        skelRowsEl.innerHTML = '';
        for (let i=0;i<n;i++){
            const row = document.createElement('div');
            row.className = 'kt-skel__row';
            row.innerHTML = `
                <div class="kt-skel__bar" style="width:${60 + (i%3)*12}%"></div>
                <div class="kt-skel__bar" style="width:${40 + (i%4)*10}%"></div>
                <div class="kt-skel__bar" style="width:${45 + (i%5)*8}%"></div>
                <div class="kt-skel__bar" style="width:${52 + (i%4)*9}%"></div>
                <div class="kt-skel__bar" style="width:${38 + (i%3)*12}%"></div>
            `;
            skelRowsEl.appendChild(row);
        }
    }
    buildSkeletonRows(9);

    // Skeleton controls
    let skelTimer = null;
    let skelShownAt = 0;

    function showSkeletonImmediate(minMs = 240){
        if (!card || !skelWrap) return;
        clearTimeout(skelTimer);

        // Hide fixed alpha index while loading (desktop)
        if (alphaIndex) alphaIndex.style.display = 'none';

        card.classList.add('is-loading');
        skelShownAt = Date.now();

        skelTimer = setTimeout(() => {
            // allow hide after minMs; actual hide happens in hideSkeleton()
        }, minMs);
    }

    function showSkeletonSoft(){
        // small delay so it doesn’t flicker while typing fast
        if (!card || !skelWrap) return;
        clearTimeout(skelTimer);
        skelTimer = setTimeout(() => showSkeletonImmediate(220), 90);
    }

    function hideSkeleton(){
        if (!card || !skelWrap) return;
        clearTimeout(skelTimer);

        const elapsed = Date.now() - skelShownAt;
        const minMs = 220;
        const wait = Math.max(0, minMs - elapsed);

        setTimeout(() => {
            card.classList.remove('is-loading');
            // alphaIndex visibility will be restored by buildAlphaRowsAndIndex()
        }, wait);
    }

    // Update --thead-h based on actual thead height (for sticky alpha rows)
    function updateTheadHeight(){
        const thead = table?.querySelector('thead');
        if (!thead) return;
        const h = Math.round(thead.getBoundingClientRect().height || 44);
        document.documentElement.style.setProperty('--thead-h', h + 'px');
    }

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    function stripDiacritics(s){
        try{
            return (s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } catch(e){
            return (s || '');
        }
    }

    function isAlphaGroupingMode(){
        const v = sortSelect.value;
        return v === 'lname_asc' || v === 'lname_desc';
    }

    function firstLetterFromRow(row){
        const raw = stripDiacritics((row.dataset.lname || '').trim());
        const ch = raw ? raw[0] : '';
        if (ch && /[A-Za-z]/.test(ch)) return ch.toUpperCase();
        return '#';
    }

    function clearAlphaRows(){
        tbody.querySelectorAll('tr.alpha-row').forEach(r => r.remove());
    }

    function getVisibleRowsInDomOrder(){
        return rowsAll.filter(r => r.style.display !== 'none');
    }

    function buildAlphaRowsAndIndex(){
        clearAlphaRows();

        // Only show A–Z features when sorting by last name
        if (!isAlphaGroupingMode()){
            alphaIndex.style.display = 'none';
            return;
        }

        const visibleRows = getVisibleRowsInDomOrder();
        if (!visibleRows.length){
            alphaIndex.style.display = 'none';
            return;
        }

        // Insert alpha separator rows before the first visible row of each letter
        const counts = new Map(); // letter -> count
        visibleRows.forEach(r => {
            const L = firstLetterFromRow(r);
            counts.set(L, (counts.get(L) || 0) + 1);
        });

        let lastLetter = null;
        visibleRows.forEach(row => {
            const letter = firstLetterFromRow(row);
            if (letter !== lastLetter){
                const tr = document.createElement('tr');
                tr.className = 'alpha-row';
                tr.dataset.letter = letter;
                tr.innerHTML = `
                    <td colspan="5">
                        <div class="alpha-pill">
                            <div class="alpha-left">
                                <span class="alpha-letter">${letter}</span>
                                <span class="alpha-meta">${counts.get(letter) || 0} patient(s)</span>
                            </div>
                        </div>
                    </td>
                `;
                tbody.insertBefore(tr, row);
                lastLetter = letter;
            }
        });

        // Build the A–Z index
        alphaIndex.innerHTML = '';
        const letters = ['#', ...'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')];

        letters.forEach(L => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'alpha-btn';
            btn.textContent = L;

            const available = counts.has(L) || (L === '#' && counts.has('#'));
            if (!available) btn.classList.add('disabled');

            btn.title = available ? `Jump to ${L}` : `No ${L} patients`;

            btn.addEventListener('click', () => {
                if (btn.classList.contains('disabled')) return;

                if (L === '#'){
                    const topEl = document.getElementById('patientsCard');
                    if (topEl){
                        const y = window.scrollY + topEl.getBoundingClientRect().top - 12;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }
                    return;
                }

                const anchor = tbody.querySelector(`tr.alpha-row[data-letter="${L}"]`);
                if (!anchor) return;

                const offset = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--thead-h')) || 44) + 12;
                const y = window.scrollY + anchor.getBoundingClientRect().top - offset;
                window.scrollTo({ top: y, behavior: 'smooth' });
            });

            alphaIndex.appendChild(btn);
        });

        alphaIndex.style.display = '';
        updateActiveLetterHighlight(); // initial
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

        if (emptyStateRow){
            emptyStateRow.style.display = (visible === 0) ? '' : 'none';
        }
    }

    function applySort() {
        const mode = sortSelect.value;
        const collator = new Intl.Collator(undefined, { sensitivity: 'base', numeric: true });

        const sorted = [...rowsAll].sort((a, b) => {
            const da = a.dataset;
            const db = b.dataset;

            const lnameA = stripDiacritics(da.lname || '');
            const lnameB = stripDiacritics(db.lname || '');
            const fnameA = stripDiacritics(da.fname || '');
            const fnameB = stripDiacritics(db.fname || '');

            const createdA = Number(da.created || 0);
            const createdB = Number(db.created || 0);

            const bdayA = Number(da.bday || 0);
            const bdayB = Number(db.bday || 0);

            switch(mode){
                case 'lname_asc':
                    return collator.compare(lnameA, lnameB)
                        || collator.compare(fnameA, fnameB)
                        || (createdB - createdA);

                case 'lname_desc':
                    return collator.compare(lnameB, lnameA)
                        || collator.compare(fnameB, fnameA)
                        || (createdB - createdA);

                case 'fname_asc':
                    return collator.compare(fnameA, fnameB)
                        || collator.compare(lnameA, lnameB)
                        || (createdB - createdA);

                case 'fname_desc':
                    return collator.compare(fnameB, fnameA)
                        || collator.compare(lnameB, lnameA)
                        || (createdB - createdA);

                case 'created_asc':
                    return createdA - createdB;

                case 'created_desc':
                    return createdB - createdA;

                case 'bday_asc':
                    return bdayA - bdayB || collator.compare(lnameA, lnameB);

                case 'bday_desc':
                    return bdayB - bdayA || collator.compare(lnameA, lnameB);

                default:
                    return collator.compare(lnameA, lnameB) || collator.compare(fnameA, fnameB);
            }
        });

        sorted.forEach(r => tbody.appendChild(r));
        if (emptyStateRow) tbody.appendChild(emptyStateRow);
    }

    function applyAll(){
        updateTheadHeight();
        applySort();
        applySearch();
        buildAlphaRowsAndIndex();
    }

    // Highlight active letter as you scroll (lightweight)
    let raf = null;
    function updateActiveLetterHighlight(){
        if (!isAlphaGroupingMode()) return;

        const headers = Array.from(tbody.querySelectorAll('tr.alpha-row'));
        if (!headers.length) return;

        const offset = (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--thead-h')) || 44) + 16;
        let active = null;

        for (const h of headers){
            const top = h.getBoundingClientRect().top;
            if (top - offset <= 0) active = h.dataset.letter;
            else break;
        }

        const btns = Array.from(alphaIndex.querySelectorAll('.alpha-btn'));
        btns.forEach(b => b.classList.remove('active'));
        if (active){
            const b = btns.find(x => x.textContent === active);
            if (b) b.classList.add('active');
        }
    }

    function onScroll(){
        if (!isAlphaGroupingMode()) return;
        if (raf) return;
        raf = requestAnimationFrame(() => {
            raf = null;
            updateActiveLetterHighlight();
        });
    }

    // Counts
    totalCountEl.textContent = rowsAll.length;
    visibleCountEl.textContent = rowsAll.length;

    // ---- Events ----

    // Search (soft skeleton)
    let searchDeb = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDeb);
        showSkeletonSoft();
        searchDeb = setTimeout(() => {
            applySearch();
            buildAlphaRowsAndIndex();
            hideSkeleton();
        }, 140);
    });

    // Sort (immediate skeleton)
    sortSelect.addEventListener('change', () => {
        showSkeletonImmediate(260);
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
        });
    });

    // Reset (immediate skeleton)
    resetBtn.addEventListener('click', () => {
        showSkeletonImmediate(260);
        searchInput.value = '';
        sortSelect.value = 'lname_asc';
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
            searchInput.focus();
        });
    });

    // Import
    const importBtn = document.getElementById('importBtn');
    const patientFile = document.getElementById('patientFile');
    const importForm = document.getElementById('importForm');

    importBtn?.addEventListener('click', () => patientFile.click());
    patientFile?.addEventListener('change', () => {
        if (patientFile.files && patientFile.files.length > 0) importForm.submit();
    });

    // Scroll/resize
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', () => {
        updateTheadHeight();
        buildAlphaRowsAndIndex();
        updateActiveLetterHighlight();
    });

    // Initial (nice “website feel”)
    showSkeletonImmediate(220);
    requestAnimationFrame(() => {
        if (!sortSelect.value) sortSelect.value = 'lname_asc';
        applyAll();
        hideSkeleton();
    });
})();
</script>

@endsection
