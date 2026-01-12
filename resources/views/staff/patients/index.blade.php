@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Patients Index (Dark mode compatible)
       - Removes hardcoded white backgrounds & black text vars
       - Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-border, --kt-input-bg
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
    }

    html[data-theme="dark"]{
        --soft: rgba(148, 163, 184, .16);
        --muted2: rgba(248, 250, 252, .62);
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
    .search-box input::placeholder{
        color: rgba(148, 163, 184, .85);
    }
    html[data-theme="dark"] .search-box input::placeholder{
        color: rgba(248, 250, 252, .55);
    }
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

    /* make options readable in dark mode */
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
        z-index: 1;
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

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .toolbar-row{ flex-direction: column; align-items: stretch; }
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
                <option value="lname_asc">Last name (A–Z)</option>
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
<div class="card-shell">
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

    <div class="table-wrap table-responsive">
        <table>
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
                        data-lname="{{ strtolower($patient->last_name) }}"
                        data-fname="{{ strtolower($patient->first_name) }}"
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

                                <form
                                    action="{{ route('staff.patients.destroy', $patient->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this patient?');"
                                    style="display:inline;"
                                >
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
    const rowsAll     = Array.from(document.querySelectorAll('.patient-row'));
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
            case 'created_desc': return Number(ds.created || 0);
            case 'created_asc':  return Number(ds.created || 0);
            case 'lname_asc':    return ds.lname || '';
            case 'lname_desc':   return ds.lname || '';
            case 'fname_asc':    return ds.fname || '';
            case 'fname_desc':   return ds.fname || '';
            case 'bday_asc':     return Number(ds.bday || 0);
            case 'bday_desc':    return Number(ds.bday || 0);
            default:             return Number(ds.created || 0);
        }
    }

    function applySort() {
        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const va = getComparable(a, mode);
            const vb = getComparable(b, mode);

            if (typeof va === 'string' || typeof vb === 'string') {
                const A = String(va), B = String(vb);
                if (A < B) return (mode.endsWith('_desc') ? 1 : -1);
                if (A > B) return (mode.endsWith('_desc') ? -1 : 1);
                const ta = a.dataset.fname || '';
                const tb = b.dataset.fname || '';
                if (ta < tb) return -1;
                if (ta > tb) return 1;
                return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);
            }

            if (va === vb) return Number(b.dataset.created || 0) - Number(a.dataset.created || 0);

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
        sortSelect.value = 'created_desc';
        applyAll();
        searchInput.focus();
    });

    const importBtn = document.getElementById('importBtn');
    const patientFile = document.getElementById('patientFile');
    const importForm = document.getElementById('importForm');

    importBtn?.addEventListener('click', () => patientFile.click());
    patientFile?.addEventListener('change', () => {
        if (patientFile.files && patientFile.files.length > 0) importForm.submit();
    });

    applyAll();
})();
</script>

@endsection
