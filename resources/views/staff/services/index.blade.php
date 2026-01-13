@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: var(--kt-shadow, 0 10px 25px rgba(15, 23, 42, .06));
        --card-border: 1px solid var(--kt-border, rgba(15, 23, 42, .10));
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
        font-weight: 800;
        letter-spacing: -0.3px;
        margin: 0;
        color: var(--kt-text, #0f172a);
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--kt-muted, rgba(15, 23, 42, .55));
    }

    /* Actions */
    .top-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }
    .search-box{
        position: relative;
        width: 340px;
        max-width: 100%;
        min-width: 240px;
        flex: 1 1 320px;
    }
    .search-box i{
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: var(--kt-muted, rgba(15, 23, 42, .45));
        font-size: 14px;
        pointer-events: none;
    }
    .search-box input{
        width: 100%;
        padding: 11px 12px 11px 38px;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border, rgba(15, 23, 42, .14));
        background: var(--kt-input-bg, rgba(255,255,255,.92));
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
        outline: none;
        transition: .15s ease;
        font-size: 14px;
        color: var(--kt-text, #0f172a);
    }
    .search-box input:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: var(--kt-surface-2, #fff);
    }

    .sort-box{
        display:flex;
        align-items:center;
        gap: 8px;
        min-width: 0;
        flex: 0 1 auto;
    }
    .sort-box .sort-label{
        font-size: 12px;
        font-weight: 900;
        color: var(--kt-muted, rgba(15, 23, 42, .60));
        white-space: nowrap;
    }
    .sort-select{
        min-width: 230px;
        max-width: 100%;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border, rgba(15, 23, 42, .14));
        background: var(--kt-input-bg, rgba(255,255,255,.92));
        padding: 11px 12px;
        font-size: 14px;
        color: var(--kt-text, #0f172a);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }
    .sort-select:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: var(--kt-surface-2, #fff);
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
        border: 1px solid var(--kt-border, rgba(15,23,42,.10));
        background: rgba(15,23,42,.06);
        color: var(--kt-text, rgba(15,23,42,.78));
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    .btnx:hover{ background: rgba(15,23,42,.08); transform: translateY(-1px); }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 800;
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
        filter: brightness(1.02);
    }

    /* Card */
    .card-shell{
        background: var(--kt-surface, rgba(255,255,255,.92));
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
        background: linear-gradient(180deg, rgba(248,250,252,.85), rgba(255,255,255,0));
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--kt-muted, rgba(15, 23, 42, .55));
        font-weight: 700;
    }

    /* Table wrap */
    .table-wrap{
        padding: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        background: transparent;
        min-width: 980px; /* was 880, add space for ID column */
    }

    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--kt-muted, rgba(15, 23, 42, .55));
        padding: 14px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .9);
        position: sticky;
        top: 0;
        z-index: 4;
        white-space: nowrap;
    }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--kt-text, #0f172a);
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: middle;
        overflow: hidden;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }

    /* Column sizing (UPDATED because we added ID column) */
    th:nth-child(1), td:nth-child(1){ width: 80px; }   /* ID */
    th:nth-child(2), td:nth-child(2){ width: 220px; }  /* Name */
    th:nth-child(3), td:nth-child(3){ width: 140px; }  /* Base price */
    th:nth-child(4), td:nth-child(4){ width: 150px; }  /* Custom price */
    th:nth-child(6), td:nth-child(6){ width: 240px; }  /* Actions */

    .muted{ color: var(--kt-muted, rgba(15, 23, 42, .55)); }

    /* ID pill */
    .idpill{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid rgba(148,163,184,.22);
        background: rgba(148,163,184,.12);
        color: var(--kt-text, #0f172a);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    html[data-theme="dark"] .idpill{
        border-color: rgba(148,163,184,.22);
        background: rgba(148,163,184,.10);
        color: var(--kt-text);
    }

    /* Chips */
    .chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1;
    }
    .chip-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .chip-yes{
        background: rgba(34, 197, 94, .12);
        color: #16a34a !important;
        border-color: rgba(34,197,94,.25);
    }
    .chip-no{
        background: rgba(107, 114, 128, .12);
        color: rgba(15, 23, 42, .80) !important;
        border-color: rgba(107,114,128,.25);
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
        background: rgba(59, 130, 246, .12);
        color:#1d4ed8 !important;
        border-color: rgba(59,130,246,.22);
    }
    .pill-edit:hover{ background: rgba(59, 130, 246, .18); }

    .pill-view{
        background: rgba(124, 58, 237, .12);
        color:#5b21b6 !important;
        border-color: rgba(124,58,237,.22);
    }
    .pill-view:hover{ background: rgba(124, 58, 237, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color:#b91c1c !important;
        border-color: rgba(239,68,68,.22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .18); }

    /* Description clamp */
    .desc{
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
        line-height: 1.35;
    }

    /* ✅ Sticky Actions column (NO overlay slab) */
    thead th.actions-sticky{
        right: 0;
        z-index: 6;
        background: rgba(248, 250, 252, .9);
    }
    tbody td.actions-sticky{
        position: sticky;
        right: 0;
        z-index: 3;
        background: var(--kt-surface, rgba(255,255,255,.92));
    }
    tbody tr:hover td.actions-sticky{
        background: rgba(13,110,253,.06);
    }

    /* “No results” row */
    .empty-state{
        padding: 18px;
        text-align:center;
        color: var(--kt-muted, rgba(15,23,42,.55));
        font-weight: 800;
    }

    /* ---------- Dark mode tweaks ---------- */
    html[data-theme="dark"] .card-head{
        border-bottom-color: rgba(148,163,184,.16);
        background: linear-gradient(180deg, rgba(2,6,23,.55), rgba(2,6,23,0));
    }
    html[data-theme="dark"] thead th{
        background: rgba(2, 6, 23, .55);
        border-bottom-color: rgba(148,163,184,.18);
        color: var(--kt-muted);
    }
    html[data-theme="dark"] thead th.actions-sticky{
        background: rgba(2, 6, 23, .55);
    }
    html[data-theme="dark"] tbody td{
        border-bottom-color: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] tbody td.actions-sticky{
        background: var(--kt-surface, rgba(2, 6, 23, .55));
    }
    html[data-theme="dark"] tbody tr:hover{
        background: rgba(96,165,250,.08);
    }
    html[data-theme="dark"] tbody tr:hover td.actions-sticky{
        background: rgba(96,165,250,.08);
    }
    html[data-theme="dark"] .btnx{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.18);
        color: var(--kt-text);
    }
    html[data-theme="dark"] .btnx:hover{
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .chip-no{
        color: rgba(248,250,252,.86) !important;
        border-color: rgba(148,163,184,.22);
        background: rgba(148,163,184,.10);
    }
    html[data-theme="dark"] .search-box i{
        color: rgba(248,250,252,.55);
    }

    @media (max-width: 768px){
        .search-box{ width: 100%; flex: 1 1 100%; }
        .sort-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }

        th:nth-child(1), td:nth-child(1){ width: 70px; }   /* ID */
        th:nth-child(2), td:nth-child(2){ width: 180px; }  /* Name */
        th:nth-child(3), td:nth-child(3){ width: 120px; }  /* Base price */
        th:nth-child(4), td:nth-child(4){ width: 130px; }  /* Custom price */
        th:nth-child(6), td:nth-child(6){ width: 220px; }  /* Actions */

        table{ min-width: 860px; }
    }
</style>

{{-- Header --}}
<div class="page-head">
    <div>
        <h2 class="page-title">Services</h2>
        <p class="subtitle">Manage dental services and pricing</p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="serviceSearch" placeholder="Search service id, name, description, or price…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
            <select id="serviceSort" class="sort-select">
                <option value="created_desc">Date added (newest)</option>
                <option value="created_asc">Date added (oldest)</option>

                {{-- ✅ Default: A–Z --}}
                <option value="name_asc" selected>Name (A–Z)</option>

                <option value="name_desc">Name (Z–A)</option>
                <option value="price_desc">Base price (high → low)</option>
                <option value="price_asc">Base price (low → high)</option>
                <option value="custom_yes">Custom price (Yes first)</option>
                <option value="custom_no">Custom price (No first)</option>
            </select>
        </div>

        <button type="button" id="clearFilters" class="btnx">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        <a href="{{ route('staff.services.create') }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Service
        </a>
    </div>
</div>

{{-- Table Card --}}
<div class="card-shell">
    <div class="card-head">
        <div class="hint">
            Showing <strong id="visibleCount">{{ $services->count() }}</strong> / <strong id="totalCount">{{ $services->count() }}</strong> service(s)
        </div>
        <div class="hint">
            Tip: search + sort works together
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Base Price</th>
                    <th>Custom Price</th>
                    <th>Description</th>
                    <th class="text-end actions-sticky">Actions</th>
                </tr>
            </thead>

            <tbody id="servicesTableBody">
                @forelse ($services as $service)
                    @php
                        $idKey = (string)($service->id ?? '');
                        $nameKey = strtolower(trim($service->name ?? ''));
                        $createdTs = optional($service->created_at)->timestamp ?? 0;
                        $price = (float)($service->base_price ?? 0);
                        $custom = $service->allow_custom_price ? 1 : 0;
                    @endphp

                    <tr class="service-row"
                        data-id="{{ $idKey }}"
                        data-name="{{ $nameKey }}"
                        data-created="{{ $createdTs }}"
                        data-price="{{ $price }}"
                        data-custom="{{ $custom }}"
                    >
                        <td>
                            <span class="idpill" title="Service ID">
                                #{{ $service->id }}
                            </span>
                        </td>

                        <td class="fw-semibold">{{ $service->name }}</td>

                        <td class="fw-semibold">₱{{ number_format((float)($service->base_price ?? 0), 2) }}</td>

                        <td>
                            @if($service->allow_custom_price)
                                <span class="chip chip-yes">
                                    <span class="chip-dot"></span> Yes
                                </span>
                            @else
                                <span class="chip chip-no">
                                    <span class="chip-dot"></span> No
                                </span>
                            @endif
                        </td>

                        <td class="muted">
                            <span class="desc" title="{{ $service->description ?? '' }}">
                                {{ $service->description ?? '—' }}
                            </span>
                        </td>

                        <td class="text-end actions-sticky">
                            <div class="action-pills">
                                <a href="{{ route('staff.services.edit', $service->id) }}" class="pill pill-edit">
                                    <i class="fa fa-pen"></i> Edit
                                </a>

                                <a href="{{ route('staff.services.patients', $service->id) }}" class="pill pill-view">
                                    <i class="fa fa-folder-open"></i> Patients
                                </a>

                                <form action="{{ route('staff.services.destroy', $service->id) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Delete this service?');">
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
                    <tr class="no-data-row">
                        <td colspan="6" class="empty-state">
                            No services found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('serviceSearch');
    const sortSelect  = document.getElementById('serviceSort');
    const resetBtn    = document.getElementById('clearFilters');

    const tbody       = document.getElementById('servicesTableBody');
    const rowsAll     = Array.from(document.querySelectorAll('.service-row'));

    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');

    totalCountEl.textContent = rowsAll.length;
    visibleCountEl.textContent = rowsAll.length;

    // “No results” row (only for search/filter)
    const noResultsRow = document.createElement('tr');
    noResultsRow.innerHTML = `<td colspan="6" class="empty-state">No matching services.</td>`;
    noResultsRow.style.display = 'none';

    if (rowsAll.length) tbody.appendChild(noResultsRow);

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    function applySearch(){
        const q = normalize(searchInput.value);
        let visible = 0;

        rowsAll.forEach(row => {
            const hay = normalize(row.textContent) + ' ' + normalize(row.dataset.id);
            const show = hay.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCountEl.textContent = visible;

        if (rowsAll.length){
            noResultsRow.style.display = (visible === 0) ? '' : 'none';
        }
    }

    function applySort(){
        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const da = a.dataset;
            const db = b.dataset;

            const nameA = da.name || '';
            const nameB = db.name || '';
            const createdA = Number(da.created || 0);
            const createdB = Number(db.created || 0);
            const priceA = Number(da.price || 0);
            const priceB = Number(db.price || 0);
            const customA = Number(da.custom || 0);
            const customB = Number(db.custom || 0);

            switch(mode){
                case 'created_desc': return createdB - createdA;
                case 'created_asc':  return createdA - createdB;

                case 'name_asc':  return nameA.localeCompare(nameB) || (createdB - createdA);
                case 'name_desc': return nameB.localeCompare(nameA) || (createdB - createdA);

                case 'price_desc': return (priceB - priceA) || nameA.localeCompare(nameB);
                case 'price_asc':  return (priceA - priceB) || nameA.localeCompare(nameB);

                case 'custom_yes': return (customB - customA) || nameA.localeCompare(nameB);
                case 'custom_no':  return (customA - customB) || nameA.localeCompare(nameB);

                default: return nameA.localeCompare(nameB) || (createdB - createdA);
            }
        });

        sorted.forEach(r => tbody.appendChild(r));
        if (rowsAll.length) tbody.appendChild(noResultsRow);
    }

    function applyAll(){
        applySort();
        applySearch();
    }

    searchInput?.addEventListener('input', applySearch);
    sortSelect?.addEventListener('change', applyAll);

    resetBtn?.addEventListener('click', () => {
        searchInput.value = '';
        // ✅ Reset back to alphabetical A–Z
        sortSelect.value = 'name_asc';
        applyAll();
        searchInput.focus();
    });

    // ✅ default load: alphabetical A–Z
    sortSelect.value = 'name_asc';
    applyAll();
})();
</script>

@endsection
