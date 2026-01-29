@extends('layouts.staff')

@section('kt_live_scope', 'services')
@section('kt_live_interval', 15000)

@section('content')

<style>
    /* ==========================================================
       Services Index (Dark mode compatible)
       - Token-based colors (kt theme variables)
       - Sticky Actions column
       - Search + Sort + Reset
       - ✅ Skeleton shimmer loading
       - ✅ Animated confirm delete modal (no browser confirm)
       ========================================================== */

    :root{
        --card-shadow: var(--kt-shadow, 0 10px 25px rgba(15, 23, 42, .06));
        --card-border: 1px solid var(--kt-border, rgba(15, 23, 42, .10));
        --soft: rgba(148, 163, 184, .14);

        --text: var(--kt-text, #0f172a);
        --muted: var(--kt-muted, rgba(15, 23, 42, .55));
        --muted2: rgba(148,163,184,.72);

        --brand1: #0d6efd;
        --brand2: #1e90ff;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);

        /* Skeleton */
        --skel-base: rgba(148,163,184,.18);
        --skel-shine: rgba(255,255,255,.75);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
        --muted2: rgba(148,163,184,.66);

        --skel-base: rgba(148,163,184,.14);
        --skel-shine: rgba(255,255,255,.10);
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
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.35px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    /* Actions */
    .top-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
        width: 100%;
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
        color: var(--muted2);
        font-size: 14px;
        pointer-events: none;
        opacity: .9;
    }
    html[data-theme="dark"] .search-box i{
        color: rgba(248,250,252,.55);
    }
    .search-box input{
        width: 100%;
        padding: 11px 12px 11px 38px;
        border-radius: 12px;
        border: 1px solid var(--kt-input-border, rgba(15, 23, 42, .14));
        background: var(--kt-input-bg, rgba(255,255,255,.92));
        box-shadow: var(--kt-shadow, 0 6px 16px rgba(15, 23, 42, .04));
        outline: none;
        transition: .15s ease;
        font-size: 14px;
        color: var(--text);
        min-width: 0;
    }
    .search-box input::placeholder{
        color: rgba(148,163,184,.85);
    }
    html[data-theme="dark"] .search-box input::placeholder{
        color: rgba(248,250,252,.55);
    }
    .search-box input:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
        background: var(--kt-surface, #fff);
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
        font-weight: 950;
        color: var(--muted);
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
        color: var(--text);
        outline: none;
        transition: .15s ease;
        box-shadow: var(--kt-shadow, 0 6px 16px rgba(15, 23, 42, .04));
    }
    .sort-select:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
        background: var(--kt-surface, #fff);
    }
    /* make options readable in dark mode */
    html[data-theme="dark"] .sort-select,
    html[data-theme="dark"] .sort-select option{
        background-color: rgba(17,24,39,.98) !important;
        color: var(--kt-text) !important;
    }

    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid var(--kt-border, rgba(15,23,42,.10));
        background: var(--kt-surface-2, rgba(15,23,42,.06));
        color: var(--text);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    .btnx:hover{
        background: rgba(148,163,184,.14);
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .btnx{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.18);
        color: var(--kt-text);
    }
    html[data-theme="dark"] .btnx:hover{
        background: rgba(148,163,184,.14);
    }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 950;
        border-radius: 12px;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
        white-space: nowrap;
        border: 1px solid transparent;
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
        position: relative; /* ✅ for skeleton overlay */
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
        border-bottom-color: rgba(148,163,184,.16);
        background: linear-gradient(180deg, rgba(2,6,23,.55), rgba(2,6,23,0));
    }
    .card-head .hint{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
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
        min-width: 980px; /* space for ID */
    }

    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--muted);
        padding: 14px 14px;
        border-bottom: 1px solid var(--soft);
        background: rgba(148,163,184,.12);
        position: sticky;
        top: 0;
        z-index: 4;
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{
        background: rgba(2, 6, 23, .55);
        border-bottom-color: rgba(148,163,184,.18);
        color: var(--kt-muted);
    }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
        overflow: hidden;
        min-width: 0;
    }
    html[data-theme="dark"] tbody td{
        border-bottom-color: rgba(148,163,184,.14);
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(13,110,253,.06); }
    html[data-theme="dark"] tbody tr:hover{ background: rgba(96,165,250,.08); }

    /* Column sizing (ID added) */
    th:nth-child(1), td:nth-child(1){ width: 80px; }   /* ID */
    th:nth-child(2), td:nth-child(2){ width: 220px; }  /* Name */
    th:nth-child(3), td:nth-child(3){ width: 140px; }  /* Base price */
    th:nth-child(4), td:nth-child(4){ width: 150px; }  /* Custom price */
    th:nth-child(6), td:nth-child(6){ width: 240px; }  /* Actions */

    .muted{ color: var(--muted); }

    /* ID pill */
    .idpill{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.22);
        background: rgba(148,163,184,.12);
        color: var(--text);
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    html[data-theme="dark"] .idpill{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.22);
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
        font-weight: 950;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1;
    }
    .chip-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .chip-yes{
        background: rgba(34, 197, 94, .14);
        color: #22c55e !important;
        border-color: rgba(34,197,94,.25);
    }
    .chip-no{
        background: rgba(148,163,184,.12);
        color: var(--text) !important;
        border-color: rgba(148,163,184,.25);
    }
    html[data-theme="dark"] .chip-no{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.22);
        color: rgba(248,250,252,.86) !important;
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
        font-weight: 950;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
        cursor: pointer;
        user-select: none;
    }
    .pill i{ font-size: 12px; }

    .pill-edit{
        background: rgba(96,165,250,.14);
        color:#60a5fa !important;
        border-color: rgba(96,165,250,.22);
    }
    .pill-edit:hover{ background: rgba(96,165,250,.20); }

    .pill-view{
        background: rgba(124, 58, 237, .14);
        color:#a78bfa !important;
        border-color: rgba(167,139,250,.22);
    }
    .pill-view:hover{ background: rgba(167,139,250,.20); }

    .pill-del{
        background: rgba(239, 68, 68, .14);
        color:#ef4444 !important;
        border-color: rgba(239,68,68,.22);
    }
    .pill-del:hover{ background: rgba(239, 68, 68, .20); }

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

    /* ✅ Sticky Actions column */
    thead th.actions-sticky{
        right: 0;
        z-index: 6;
    }
    tbody td.actions-sticky{
        position: sticky;
        right: 0;
        z-index: 3;
        background: var(--kt-surface, rgba(255,255,255,.92));
    }
    html[data-theme="dark"] tbody td.actions-sticky{
        background: var(--kt-surface, rgba(2, 6, 23, .55));
    }
    tbody tr:hover td.actions-sticky{
        background: rgba(13,110,253,.06);
    }
    html[data-theme="dark"] tbody tr:hover td.actions-sticky{
        background: rgba(96,165,250,.08);
    }

    /* “No results” row */
    .empty-state{
        padding: 18px;
        text-align:center;
        color: var(--muted);
        font-weight: 900;
    }

    /* ==========================================================
       ✅ Skeleton Loading (shimmer)
       ========================================================== */
    .kt-skel{
        position:absolute;
        inset:0;
        background: var(--kt-surface);
        z-index: 30;
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease;
    }
    .card-shell.is-loading .kt-skel{
        opacity: 1;
        pointer-events: auto;
        cursor: progress;
    }
    .kt-skel__inner{ padding: 12px 10px 12px 10px; }

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

    .kt-skel__head,
    .kt-skel__row{
        display:grid;
        gap: 12px;
        padding: 14px 14px;
        align-items: center;
        border-bottom: 1px solid var(--soft);
    }
    .kt-skel__head{ padding: 10px 14px 14px 14px; }

    /* Services = 6 columns */
    .kt-skel__head,
    .kt-skel__row{
        grid-template-columns: 90px 1.1fr 150px 160px 1.3fr 240px;
    }

    @media (prefers-reduced-motion: reduce){
        .kt-skel__bar{ animation: none !important; }
    }

    /* ==========================================================
       ✅ Confirm Modal (animated)
       ========================================================== */
    .kt-confirm{
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }
    .kt-confirm.is-open{ display:flex; }

    .kt-confirm__backdrop{
        position:absolute;
        inset:0;
        background: rgba(2,6,23,.55);
        backdrop-filter: blur(6px);
        opacity: 0;
        transition: opacity 180ms ease;
    }
    .kt-confirm.is-open .kt-confirm__backdrop{ opacity: 1; }

    .kt-confirm__panel{
        position: relative;
        width: 520px;
        max-width: 100%;
        background: var(--kt-surface);
        border: 1px solid var(--kt-border);
        border-radius: 16px;
        box-shadow: var(--kt-shadow);
        transform: translateY(10px) scale(.98);
        opacity: 0;
        transition: transform 180ms ease, opacity 180ms ease;
        overflow: hidden;
    }
    .kt-confirm.is-open .kt-confirm__panel{
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .kt-confirm__head{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(148,163,184,.18);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        background: linear-gradient(180deg, rgba(148,163,184,.10), transparent);
    }
    html[data-theme="dark"] .kt-confirm__head{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
    }
    .kt-confirm__title{
        margin: 0;
        font-weight: 950;
        font-size: 14px;
        color: var(--text);
        letter-spacing: -.1px;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .kt-confirm__close{
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        color: var(--text);
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor: pointer;
        transition: .12s ease;
    }
    .kt-confirm__close:hover{ transform: translateY(-1px); }

    .kt-confirm__body{
        padding: 16px;
        color: var(--text);
        font-weight: 900;
        line-height: 1.35;
    }
    .kt-confirm__sub{
        margin-top: 8px;
        color: var(--muted);
        font-weight: 800;
        font-size: 12px;
    }
    .kt-confirm__foot{
        padding: 14px 16px;
        border-top: 1px solid rgba(148,163,184,.18);
        display:flex;
        justify-content:flex-end;
        gap: 10px;
        flex-wrap: wrap;
        background: rgba(148,163,184,.06);
    }
    html[data-theme="dark"] .kt-confirm__foot{
        background: rgba(2,6,23,.25);
    }
    .kt-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 13px;
        border: 1px solid transparent;
        cursor: pointer;
        user-select: none;
        transition: .12s ease;
        white-space: nowrap;
    }
    .kt-btn:active{ transform: translateY(1px); }

    .kt-btn--ghost{
        background: var(--kt-surface-2);
        border-color: var(--kt-border);
        color: var(--text);
    }
    .kt-btn--ghost:hover{ background: rgba(148,163,184,.14); }

    .kt-btn--danger{
        background: rgba(239,68,68,.14);
        border-color: rgba(239,68,68,.25);
        color: #ef4444;
    }
    .kt-btn--danger:hover{ background: rgba(239,68,68,.20); }

    /* Mobile */
    @media (max-width: 768px){
        .search-box{ width: 100%; flex: 1 1 100%; min-width: 0; }
        .sort-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .pill span{ display:none; }
        .pill{ padding: 8px 10px; }

        th:nth-child(1), td:nth-child(1){ width: 70px; }
        th:nth-child(2), td:nth-child(2){ width: 180px; }
        th:nth-child(3), td:nth-child(3){ width: 120px; }
        th:nth-child(4), td:nth-child(4){ width: 130px; }
        th:nth-child(6), td:nth-child(6){ width: 220px; }
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
<div class="card-shell" id="svcCard">
    <div class="card-head">
        <div class="hint">
            Showing <strong id="visibleCount">{{ $services->count() }}</strong> / <strong id="totalCount">{{ $services->count() }}</strong> service(s)
        </div>
        <div class="hint">Tip: search + sort works together</div>
    </div>

    {{-- ✅ Skeleton overlay --}}
    <div class="kt-skel" id="svcSkeleton" aria-hidden="true">
        <div class="kt-skel__inner">
            <div class="kt-skel__head">
                <div class="kt-skel__bar sm" style="width:70%"></div>
                <div class="kt-skel__bar sm" style="width:78%"></div>
                <div class="kt-skel__bar sm" style="width:62%"></div>
                <div class="kt-skel__bar sm" style="width:68%"></div>
                <div class="kt-skel__bar sm" style="width:82%"></div>
                <div class="kt-skel__bar sm" style="width:60%"></div>
            </div>
            <div id="svcSkelRows"></div>
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
                            <span class="idpill" title="Service ID">#{{ $service->id }}</span>
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
                                <a href="{{ route('staff.services.edit', $service->id) }}" class="pill pill-edit" title="Edit">
                                    <i class="fa fa-pen"></i> <span>Edit</span>
                                </a>

                                <a href="{{ route('staff.services.patients', $service->id) }}" class="pill pill-view" title="Patients">
                                    <i class="fa fa-folder-open"></i> <span>Patients</span>
                                </a>

                                {{-- ✅ Animated confirm delete --}}
                                <form id="del-svc-{{ $service->id }}" action="{{ route('staff.services.destroy', $service->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="pill pill-del"
                                            data-confirm="Delete this service? This can’t be undone."
                                            data-confirm-title="Confirm delete"
                                            data-confirm-yes="Delete"
                                            data-confirm-form="#del-svc-{{ $service->id }}">
                                        <i class="fa fa-trash"></i> <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="no-data-row" id="emptyDataRow">
                        <td colspan="6" class="empty-state">No services found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ✅ Confirm Modal --}}
<div class="kt-confirm" id="ktConfirm" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="kt-confirm__backdrop" data-kt-close></div>

    <div class="kt-confirm__panel" role="document">
        <div class="kt-confirm__head">
            <h6 class="kt-confirm__title">
                <i class="fa fa-triangle-exclamation" style="color:#f59e0b;"></i>
                <span id="ktConfirmTitle">Confirm</span>
            </h6>
            <button type="button" class="kt-confirm__close" data-kt-close aria-label="Close">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <div class="kt-confirm__body">
            <div id="ktConfirmMsg">Are you sure?</div>
            <div class="kt-confirm__sub">Tip: Press Esc to cancel.</div>
        </div>

        <div class="kt-confirm__foot">
            <button type="button" class="kt-btn kt-btn--ghost" data-kt-close>Cancel</button>
            <button type="button" class="kt-btn kt-btn--danger" id="ktConfirmYes">Delete</button>
        </div>
    </div>
</div>

<script>
(() => {
    const card = document.getElementById('svcCard');

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

    /* ==========================================================
       ✅ Skeleton helpers
       ========================================================== */
    const skelRowsEl = document.getElementById('svcSkelRows');
    function buildSkelRows(n = 9){
        if (!skelRowsEl) return;
        skelRowsEl.innerHTML = '';
        for (let i=0;i<n;i++){
            const row = document.createElement('div');
            row.className = 'kt-skel__row';
            row.innerHTML = `
                <div class="kt-skel__bar" style="width:${48 + (i%4)*10}%"></div>
                <div class="kt-skel__bar" style="width:${66 + (i%3)*12}%"></div>
                <div class="kt-skel__bar" style="width:${56 + (i%4)*8}%"></div>
                <div class="kt-skel__bar" style="width:${54 + (i%5)*7}%"></div>
                <div class="kt-skel__bar" style="width:${78 + (i%3)*7}%"></div>
                <div class="kt-skel__bar" style="width:${58 + (i%4)*8}%"></div>
            `;
            skelRowsEl.appendChild(row);
        }
    }
    buildSkelRows(9);

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
        clearTimeout(skelTimer);
        skelTimer = setTimeout(() => showSkeletonImmediate(220), 90);
    }
    function hideSkeleton(){
        if (!card) return;
        const elapsed = Date.now() - (skelShownAt || 0);
        const minMs = 220;
        const wait = Math.max(0, minMs - elapsed);
        clearTimeout(skelTimer);
        setTimeout(() => card.classList.remove('is-loading'), wait);
    }

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

    // Smooth UX: skeleton on interactions
    let t = null;
    function debounceApply(){
        clearTimeout(t);
        showSkeletonSoft();
        t = setTimeout(() => {
            applyAll();
            hideSkeleton();
        }, 140);
    }

    searchInput?.addEventListener('input', debounceApply);

    sortSelect?.addEventListener('change', () => {
        showSkeletonImmediate(260);
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
        });
    });

    resetBtn?.addEventListener('click', () => {
        showSkeletonImmediate(260);
        searchInput.value = '';
        sortSelect.value = 'name_asc';
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
            searchInput.focus();
        });
    });

    // Escape clears search
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape'){
            searchInput.value = '';
            showSkeletonImmediate(220);
            requestAnimationFrame(() => {
                applyAll();
                hideSkeleton();
                searchInput.blur();
            });
        }
    });

    // Initial
    sortSelect.value = sortSelect.value || 'name_asc';
    showSkeletonImmediate(240);
    requestAnimationFrame(() => {
        applyAll();
        hideSkeleton();
    });

    /* ==========================================================
       ✅ Animated Confirm Delete
       ========================================================== */
    const modal = document.getElementById('ktConfirm');
    const titleEl = document.getElementById('ktConfirmTitle');
    const msgEl = document.getElementById('ktConfirmMsg');
    const yesBtn = document.getElementById('ktConfirmYes');

    let pendingFormSelector = null;

    function openConfirm({ title, message, yesText, formSelector }){
        pendingFormSelector = formSelector || null;

        titleEl.textContent = title || 'Confirm';
        msgEl.textContent = message || 'Are you sure?';
        yesBtn.textContent = yesText || 'Delete';

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        setTimeout(() => yesBtn.focus(), 40);
        document.body.style.overflow = 'hidden';
    }

    function closeConfirm(){
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingFormSelector = null;
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm-form]');
        if (!btn) return;

        const title = btn.getAttribute('data-confirm-title') || 'Confirm delete';
        const message = btn.getAttribute('data-confirm') || 'Delete this item?';
        const yesText = btn.getAttribute('data-confirm-yes') || 'Delete';
        const formSelector = btn.getAttribute('data-confirm-form');

        if (!formSelector) return;
        openConfirm({ title, message, yesText, formSelector });
    });

    modal.addEventListener('click', (e) => {
        if (e.target.closest('[data-kt-close]')) closeConfirm();
    });

    yesBtn.addEventListener('click', () => {
        const form = pendingFormSelector ? document.querySelector(pendingFormSelector) : null;
        closeConfirm();
        form?.submit();
    });

    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeConfirm();
    });
})();
</script>

@endsection
