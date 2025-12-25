@extends('layouts.app')

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

    /* Actions */
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
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(15,23,42,.06);
        color: rgba(15,23,42,.75);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    .btnx:hover{ background: rgba(15,23,42,.08); }

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

    /* Card */
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

    /* Yes/No chips */
    .chip{
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
    .chip-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    .chip-yes{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .chip-no{ background: rgba(107, 114, 128, .12); color: rgba(15, 23, 42, .70); border-color: rgba(107,114,128,.25); }

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

    .pill-edit{
        background: rgba(59, 130, 246, .12);
        color:#1d4ed8 !important;
        border-color: rgba(59,130,246,.22);
    }
    .pill-edit:hover{ background: rgba(59, 130, 246, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color:#b91c1c !important;
        border-color: rgba(239,68,68,.22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .18); }

    /* Description clamp */
    .desc{
        max-width: 520px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .desc{ max-width: 240px; }
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
            <input type="text" id="serviceSearch" placeholder="Search service name, description, or price…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
            <select id="serviceSort" class="sort-select">
                <option value="created_desc">Date added (newest)</option>
                <option value="created_asc">Date added (oldest)</option>
                <option value="name_asc">Name (A–Z)</option>
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

    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Base Price</th>
                    <th>Custom Price</th>
                    <th>Description</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody id="servicesTableBody">
                @forelse ($services as $service)
                    @php
                        $nameKey = strtolower(trim($service->name ?? ''));
                        $createdTs = optional($service->created_at)->timestamp ?? 0;
                        $price = (float)($service->base_price ?? 0);
                        $custom = $service->allow_custom_price ? 1 : 0;
                    @endphp

                    <tr class="service-row"
                        data-name="{{ $nameKey }}"
                        data-created="{{ $createdTs }}"
                        data-price="{{ $price }}"
                        data-custom="{{ $custom }}"
                    >
                        <td class="fw-semibold">{{ $service->name }}</td>

                        <td class="fw-semibold">₱{{ number_format($service->base_price, 2) }}</td>

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

                        <td class="text-end">
                            <div class="action-pills">
                                <a href="{{ route('staff.services.edit', $service) }}" class="pill pill-edit">
                                    <i class="fa fa-pen"></i> Edit
                                </a>

                                <form action="{{ route('staff.services.destroy', $service) }}"
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
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
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

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    function applySearch(){
        const q = normalize(searchInput.value);
        let visible = 0;

        rowsAll.forEach(row => {
            const show = normalize(row.textContent).includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCountEl.textContent = visible;
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

                case 'custom_yes': return (customB - customA) || nameA.localeCompare(nameB); // 1 first
                case 'custom_no':  return (customA - customB) || nameA.localeCompare(nameB); // 0 first

                default: return createdB - createdA;
            }
        });

        sorted.forEach(r => tbody.appendChild(r));
    }

    function applyAll(){
        applySort();
        applySearch();
    }

    searchInput.addEventListener('keyup', applySearch);
    sortSelect.addEventListener('change', applyAll);

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        sortSelect.value = 'created_desc';
        applyAll();
        searchInput.focus();
    });

    applyAll();
})();
</script>

@endsection
