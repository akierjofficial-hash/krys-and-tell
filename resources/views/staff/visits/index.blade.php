@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        --card-border: 1px solid rgba(15, 23, 42, .10);
        --soft: rgba(15, 23, 42, .06);
        --text: #0f172a;
        --muted: rgba(15, 23, 42, .58);
        --muted2: rgba(15, 23, 42, .45);
        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;
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

    .toggle-group{
        display:flex;
        gap: 8px;
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
        min-width: 210px;
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
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }

    .btn-ghost{
        background: rgba(15,23,42,.05);
        border-color: rgba(15,23,42,.10);
        color: rgba(15,23,42,.75) !important;
    }
    .btn-ghost:hover{ background: rgba(15,23,42,.07); }

    .btn-active{
        background: rgba(13,110,253,.12);
        border-color: rgba(13,110,253,.25);
        color: rgba(13,110,253,.95) !important;
    }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 800;
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
        background: rgba(255,255,255,.94);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .card-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        flex-wrap: wrap;
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
        border: 1px solid rgba(15, 23, 42, .10);
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .75);
        white-space: nowrap;
    }

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
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }

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
        font-weight: 700;
        background: rgba(15, 23, 42, .06);
        color: rgba(15, 23, 42, .75);
        border: 1px solid rgba(15, 23, 42, .08);
        white-space: nowrap;
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
        font-weight: 800;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-view{
        background: rgba(59, 130, 246, .12);
        color: #1d4ed8 !important;
        border-color: rgba(59, 130, 246, .22);
    }
    .pill-view:hover{ background: rgba(59, 130, 246, .18); }

    .pill-edit{
        background: rgba(34, 197, 94, .12);
        color: #15803d !important;
        border-color: rgba(34, 197, 94, .22);
    }
    .pill-edit:hover{ background: rgba(34, 197, 94, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color: #b91c1c !important;
        border-color: rgba(239, 68, 68, .22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .18); }

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .toggle-group{ width: 100%; }
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

        <a href="{{ route('staff.visits.create') }}" class="add-btn">
    <i class="fa fa-plus"></i> Add Visit
</a>


    </div>
</div>

<div class="card-shell">
    <div class="card-head">
        <div class="hint">
            <span class="count-pill">
                <i class="fa fa-calendar-check"></i>
                Showing <strong id="visibleCount">0</strong> / <strong id="totalCount">0</strong>
            </span>
        </div>
        <div class="hint">Tip: search + sort works together</div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
            @if($isAll)
                {{-- ✅ ALL VISITS (your original table) --}}
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
                                    <a href="{{ route('staff.visits.edit', $visit->id) }}" class="pill pill-edit">
                                        <i class="fa fa-pen"></i> Edit
                                    </a>

                                    <a href="{{ route('staff.visits.show', $visit->id) }}" class="pill pill-view">
                                        <i class="fa fa-eye"></i> View
                                    </a>

                                    <form action="{{ route('staff.visits.destroy', $visit->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete this visit?');">
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
                            <td colspan="6" class="text-center text-muted py-4">
                                No visits found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            @else
                {{-- ✅ UNIQUE PATIENTS (new default view) --}}
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
    const searchInput = document.getElementById('visitSearch');
    const sortSelect  = document.getElementById('visitSort');
    const tbody       = document.getElementById('visitTableBody');
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');
    const resetBtn    = document.getElementById('clearFilters');

    const rowsAll = Array.from(document.querySelectorAll('.visit-row'));

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

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
        if (!sortSelect) return; // just in case

        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const va = getComparable(a, mode);
            const vb = getComparable(b, mode);

            // String compare for patient
            if (typeof va === 'string' || typeof vb === 'string') {
                const A = String(va), B = String(vb);
                if (A < B) return mode.endsWith('_desc') ? 1 : -1;
                if (A > B) return mode.endsWith('_desc') ? -1 : 1;

                // tie-breaker: last/visit date newest first
                const tb = Number(b.dataset.vdate || b.dataset.last || 0);
                const ta = Number(a.dataset.vdate || a.dataset.last || 0);
                return tb - ta;
            }

            if (va === vb) {
                // tie-breaker: created desc if exists, else patient
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

    // use input so paste/mobile works too
    searchInput.addEventListener('input', applySearch);
    sortSelect && sortSelect.addEventListener('change', applyAll);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        if (sortSelect) sortSelect.selectedIndex = 0;
        applyAll();
        searchInput.focus();
    });

    applyAll();
})();
</script>

@endsection
