@extends('layouts.app')

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

    /* Card */
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
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
    }

    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }

    .muted{ color: var(--muted); }

    /* Treatments tags */
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
        <h2 class="page-title">Visits</h2>
        <p class="subtitle">Manage and view all patient visits</p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="visitSearch" placeholder="Search by patient name, dentist, date, notes, or treatment…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
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
        </div>

        <button type="button" id="clearFilters" class="btnx btn-ghost">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        <a href="{{ route('staff.visits.create') }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Visit
        </a>
    </div>
</div>

{{-- Table Card --}}
<div class="card-shell">
    <div class="card-head">
        <div class="hint">
            <span class="count-pill">
                <i class="fa fa-calendar-check"></i>
                Showing <strong id="visibleCount">{{ $visits->count() }}</strong> / <strong id="totalCount">{{ $visits->count() }}</strong>
            </span>
        </div>
        <div class="hint">Tip: search + sort works together</div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
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

                        $dentistLabel = $visit->dentist_name
                            ?? ($visit->doctor->name ?? null)
                            ?? '—';
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
                                <div class="tags">
                                    @foreach($visit->procedures->groupBy(fn($p) => $p->service->name) as $serviceName => $rows)
                                        <span class="tag">{{ $serviceName }} ({{ $rows->count() }})</span>
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
        </table>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('visitSearch');
    const sortSelect  = document.getElementById('visitSort');
    const tbody       = document.getElementById('visitTableBody');
    const rowsAll     = Array.from(document.querySelectorAll('.visit-row'));
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');
    const resetBtn    = document.getElementById('clearFilters');

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
        switch(mode){
            case 'vdate_desc':   return Number(ds.vdate || 0);
            case 'vdate_asc':    return Number(ds.vdate || 0);
            case 'created_desc': return Number(ds.created || 0);
            case 'created_asc':  return Number(ds.created || 0);
            case 'patient_asc':  return ds.patient || '';
            case 'patient_desc': return ds.patient || '';
            case 'treat_desc':   return Number(ds.treat || 0);
            case 'treat_asc':    return Number(ds.treat || 0);
            default:             return Number(ds.vdate || 0);
        }
    }

    function applySort() {
        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const va = getComparable(a, mode);
            const vb = getComparable(b, mode);

            // String compare for patient
            if (typeof va === 'string' || typeof vb === 'string') {
                const A = String(va), B = String(vb);
                if (A < B) return mode.endsWith('_desc') ? 1 : -1;
                if (A > B) return mode.endsWith('_desc') ? -1 : 1;
                // tie-breaker: visit date newest first
                return Number(b.dataset.vdate || 0) - Number(a.dataset.vdate || 0);
            }

            // Numeric compare
            if (va === vb) {
                // tie-breaker: created desc
                return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
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

    searchInput.addEventListener('keyup', applySearch);
    sortSelect.addEventListener('change', applyAll);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        sortSelect.value = 'vdate_desc';
        applyAll();
        searchInput.focus();
    });

    // first load
    applyAll();
})();
</script>

@endsection
