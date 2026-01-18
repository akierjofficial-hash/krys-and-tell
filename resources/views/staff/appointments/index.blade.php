@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Appointments Index (Dark mode compatible)
       - Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                           --kt-border, --kt-input-bg, --kt-input-border, --kt-shadow
       - Skeleton shimmer loading
       - Animated confirm delete (built-in modal)
       ========================================================== */

    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148, 163, 184, .14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);
        --muted2: rgba(148,163,184,.72);

        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);

        /* Skeleton */
        --skel-base: rgba(148,163,184,.18);
        --skel-shine: rgba(255,255,255,.75);
    }
    html[data-theme="dark"]{
        --soft: rgba(148, 163, 184, .16);
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
        color: var(--muted2);
        font-size: 14px;
        pointer-events: none;
        opacity: .9;
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
        min-width: 240px;
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
        border: 1px solid var(--kt-border);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        background: var(--kt-surface-2);
        color: var(--text) !important;
    }
    .btnx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btnx:hover{
        background: rgba(2,6,23,.35);
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
    }

    /* Card */
    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-top: 8px;
        color: var(--text);
        position: relative; /* for skeleton overlay */
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
        font-weight: 800;
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
        background: rgba(148,163,184,.12);
        position: sticky;
        top: 0;
        z-index: 2;
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{
        background: rgba(2,6,23,.35);
    }
    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
    }
    tbody tr{ transition: .12s ease; }
    tbody tr:hover{ background: rgba(96,165,250,.08); }

    .muted{ color: var(--muted); font-weight: 700; }

    /* Status badges */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .badge-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    .st-pending{ background: rgba(245, 158, 11, .14); color:#f59e0b; border-color: rgba(245,158,11,.25); }
    .st-confirmed{ background: rgba(59, 130, 246, .14); color:#60a5fa; border-color: rgba(96,165,250,.25); }
    .st-done{ background: rgba(34, 197, 94, .14); color:#22c55e; border-color: rgba(34,197,94,.25); }
    .st-cancelled{ background: rgba(239, 68, 68, .14); color:#ef4444; border-color: rgba(239,68,68,.25); }
    .st-default{ background: rgba(148, 163, 184, .14); color: var(--text); border-color: rgba(148,163,184,.25); }

    /* Action pills */
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

    .pill-view{
        background: rgba(96,165,250,.14);
        color:#60a5fa !important;
        border-color: rgba(96,165,250,.22);
    }
    .pill-view:hover{ background: rgba(96,165,250,.20); }

    .pill-edit{
        background: rgba(34, 197, 94, .14);
        color:#22c55e !important;
        border-color: rgba(34,197,94,.22);
    }
    .pill-edit:hover{ background: rgba(34,197,94,.20); }

    .pill-del{
        background: rgba(239, 68, 68, .14);
        color:#ef4444 !important;
        border-color: rgba(239,68,68,.22);
    }
    .pill-del:hover{ background: rgba(239,68,68,.20); }

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

    /* Appointments = 6 columns */
    .kt-skel__head,
    .kt-skel__row{
        grid-template-columns: 1.2fr 1.2fr 1fr 1fr .8fr 1.1fr;
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
        font-weight: 800;
        line-height: 1.35;
    }
    .kt-confirm__sub{
        margin-top: 8px;
        color: var(--muted);
        font-weight: 700;
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
    .kt-btn--ghost:hover{
        background: rgba(148,163,184,.14);
    }

    .kt-btn--danger{
        background: rgba(239,68,68,.14);
        border-color: rgba(239,68,68,.25);
        color: #ef4444;
    }
    .kt-btn--danger:hover{
        background: rgba(239,68,68,.20);
    }

    /* Responsive */
    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .sort-select{ width: 100%; min-width: 0; }
        .top-actions{ width: 100%; }
        .action-pills{ justify-content:flex-start; }
        .pill span{ display:none; }
        .pill{ padding: 8px 10px; }
    }
</style>

{{-- Header --}}
<div class="page-head">
    <div>
        <h2 class="page-title">Appointments</h2>
        <p class="subtitle">Manage all appointments</p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="appointmentSearch" placeholder="Search patient, service, dentist, status…">
        </div>

        <div class="sort-box">
            <span class="sort-label">Sort</span>
            <select id="appointmentSort" class="sort-select">
                <option value="dt_desc">Date & time (newest)</option>
                <option value="dt_asc">Date & time (oldest)</option>
                <option value="patient_asc">Patient (A–Z)</option>
                <option value="patient_desc">Patient (Z–A)</option>
                <option value="dentist_asc">Dentist (A–Z)</option>
                <option value="dentist_desc">Dentist (Z–A)</option>
                <option value="status_asc">Status (A–Z)</option>
                <option value="status_desc">Status (Z–A)</option>
            </select>
        </div>

        <button type="button" id="clearFilters" class="btnx">
            <i class="fa fa-rotate-left"></i> Reset
        </button>

        <a href="{{ route('staff.appointments.create') }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Appointment
        </a>
    </div>
</div>

{{-- Table Card --}}
<div class="card-shell" id="apptCard">
    <div class="card-head">
        <div class="hint">
            Showing <strong id="visibleCount">{{ $appointments->count() }}</strong> /
            <strong id="totalCount">{{ $appointments->count() }}</strong> appointment(s)
        </div>
        <div class="hint">Tip: search + sort works together</div>
    </div>

    {{-- ✅ Skeleton overlay --}}
    <div class="kt-skel" id="apptSkeleton" aria-hidden="true">
        <div class="kt-skel__inner">
            <div class="kt-skel__head">
                <div class="kt-skel__bar sm" style="width:64%"></div>
                <div class="kt-skel__bar sm" style="width:70%"></div>
                <div class="kt-skel__bar sm" style="width:56%"></div>
                <div class="kt-skel__bar sm" style="width:62%"></div>
                <div class="kt-skel__bar sm" style="width:50%"></div>
                <div class="kt-skel__bar sm" style="width:48%"></div>
            </div>
            <div id="apptSkelRows"></div>
        </div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Service</th>
                    <th>Dentist</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody id="appointmentTableBody">
            @forelse ($appointments as $appointment)
                @php
                    $status = strtolower($appointment->status ?? '');

                    $statusClass = match($status) {
                        'pending' => 'st-pending',
                        'confirmed', 'approved', 'upcoming', 'scheduled' => 'st-confirmed',
                        'done', 'completed' => 'st-done',
                        'cancelled', 'canceled', 'declined', 'rejected' => 'st-cancelled',
                        default => 'st-default',
                    };

                    // patient safe fallbacks
                    $pFirst = $appointment->patient->first_name ?? $appointment->public_first_name ?? '';
                    $pLast  = $appointment->patient->last_name  ?? $appointment->public_last_name  ?? '';
                    $patientName = trim($pFirst.' '.$pLast);
                    if ($patientName === '') $patientName = $appointment->public_name ?? 'N/A';

                    $patientKey = strtolower(trim(($pLast ?: '').', '.($pFirst ?: '')));

                    // service safe fallback
                    $serviceName = $appointment->service->name ?? 'N/A';

                    // dentist safe fallback
                    $dentistName = $appointment->dentist_name
                        ?? ($appointment->doctor->name ?? null)
                        ?? 'N/A';

                    $dentistKey = strtolower(trim($dentistName));
                    $statusKey  = strtolower(trim($appointment->status ?? ''));

                    // date/time safe
                    $dateLabel = '—';
                    $timeLabel = '—';
                    $dtTs = 0;

                    try {
                        if (!empty($appointment->appointment_date)) {
                            $dateLabel = \Carbon\Carbon::parse($appointment->appointment_date)->format('m/d/Y');
                        }
                        if (!empty($appointment->appointment_time)) {
                            $timeLabel = \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A');
                        }
                        if (!empty($appointment->appointment_date) && !empty($appointment->appointment_time)) {
                            $dtTs = \Carbon\Carbon::parse($appointment->appointment_date.' '.$appointment->appointment_time)->timestamp;
                        }
                    } catch (\Throwable $e) {
                        $dtTs = 0;
                    }
                @endphp

                <tr class="appointment-row"
                    data-patient="{{ $patientKey }}"
                    data-dentist="{{ $dentistKey }}"
                    data-status="{{ $statusKey }}"
                    data-dt="{{ $dtTs }}"
                >
                    <td class="fw-semibold">
                        {{ $patientName }}
                        @if(empty($appointment->patient_id))
                            <div class="muted" style="font-size:12px;">(Not linked yet)</div>
                        @endif
                    </td>

                    <td>{{ $serviceName }}</td>

                    <td class="muted">{{ $dentistName }}</td>

                    <td>
                        <div class="fw-semibold">{{ $dateLabel }}</div>
                        <div class="muted" style="font-size:12px;">{{ $timeLabel }}</div>
                    </td>

                    <td>
                        <span class="badge-soft {{ $statusClass }}">
                            <span class="badge-dot"></span>
                            {{ ucfirst($appointment->status ?? 'N/A') }}
                        </span>
                    </td>

                    <td class="text-end">
                        <div class="action-pills">
                            <a href="{{ route('staff.appointments.show', $appointment) }}" class="pill pill-view">
                                <i class="fa fa-eye"></i> <span>View</span>
                            </a>

                            <a href="{{ route('staff.appointments.edit', $appointment) }}" class="pill pill-edit">
                                <i class="fa fa-pen"></i> <span>Edit</span>
                            </a>

                            {{-- ✅ Animated confirm delete --}}
                            <form id="del-appt-{{ $appointment->id }}" action="{{ route('staff.appointments.destroy', $appointment) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="pill pill-del"
                                        data-confirm="Delete this appointment? This can’t be undone."
                                        data-confirm-title="Confirm delete"
                                        data-confirm-yes="Delete"
                                        data-confirm-form="#del-appt-{{ $appointment->id }}">
                                    <i class="fa fa-trash"></i> <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="emptyStateRow">
                    <td colspan="6" class="text-center text-muted py-4">
                        No appointments found.
                    </td>
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
            <button type="button" class="kt-btn kt-btn--ghost" data-kt-close>
                Cancel
            </button>
            <button type="button" class="kt-btn kt-btn--danger" id="ktConfirmYes">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    const card = document.getElementById('apptCard');

    const searchInput = document.getElementById('appointmentSearch');
    const sortSelect  = document.getElementById('appointmentSort');
    const resetBtn    = document.getElementById('clearFilters');

    const tbody       = document.getElementById('appointmentTableBody');
    const rowsAll     = Array.from(document.querySelectorAll('.appointment-row'));

    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');
    const emptyStateRow  = document.getElementById('emptyStateRow');

    totalCountEl.textContent = rowsAll.length;
    visibleCountEl.textContent = rowsAll.length;

    function normalize(s){ return (s || '').toString().toLowerCase().trim(); }

    /* ==========================================================
       ✅ Skeleton helpers
       ========================================================== */
    const skelRowsEl = document.getElementById('apptSkelRows');

    function buildSkelRows(n = 8){
        if (!skelRowsEl) return;
        skelRowsEl.innerHTML = '';
        for (let i=0;i<n;i++){
            const row = document.createElement('div');
            row.className = 'kt-skel__row';
            row.innerHTML = `
                <div class="kt-skel__bar" style="width:${62 + (i%3)*12}%"></div>
                <div class="kt-skel__bar" style="width:${70 + (i%4)*7}%"></div>
                <div class="kt-skel__bar" style="width:${56 + (i%3)*10}%"></div>
                <div class="kt-skel__bar" style="width:${62 + (i%4)*6}%"></div>
                <div class="kt-skel__bar" style="width:${46 + (i%5)*8}%"></div>
                <div class="kt-skel__bar" style="width:${50 + (i%4)*8}%"></div>
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

    /* ==========================================================
       Search + Sort
       ========================================================== */
    function applySearch(){
        const q = normalize(searchInput.value);
        let visible = 0;

        rowsAll.forEach(row => {
            const show = normalize(row.textContent).includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCountEl.textContent = visible;

        if (emptyStateRow){
            emptyStateRow.style.display = (visible === 0) ? '' : 'none';
        }
    }

    function getComparable(row, mode){
        const d = row.dataset;
        switch(mode){
            case 'dt_desc':
            case 'dt_asc':
                return Number(d.dt || 0);

            case 'patient_asc':
            case 'patient_desc':
                return d.patient || '';

            case 'dentist_asc':
            case 'dentist_desc':
                return d.dentist || '';

            case 'status_asc':
            case 'status_desc':
                return d.status || '';

            default:
                return Number(d.dt || 0);
        }
    }

    function applySort(){
        const mode = sortSelect.value;

        const sorted = [...rowsAll].sort((a, b) => {
            const va = getComparable(a, mode);
            const vb = getComparable(b, mode);

            if (typeof va === 'string' || typeof vb === 'string'){
                const A = String(va), B = String(vb);
                if (A < B) return mode.endsWith('_desc') ? 1 : -1;
                if (A > B) return mode.endsWith('_desc') ? -1 : 1;

                // tie-breaker newest first
                return Number(b.dataset.dt || 0) - Number(a.dataset.dt || 0);
            }

            if (va === vb) return 0;
            const asc = mode.endsWith('_asc');
            return asc ? (va - vb) : (vb - va);
        });

        sorted.forEach(r => tbody.appendChild(r));
        if (emptyStateRow) tbody.appendChild(emptyStateRow);
    }

    function applyAll(){
        applySort();
        applySearch();
    }

    let t = null;
    function debounceApply(){
        clearTimeout(t);
        showSkeletonSoft();
        t = setTimeout(() => {
            applyAll();
            hideSkeleton();
        }, 140);
    }

    searchInput.addEventListener('input', debounceApply);

    sortSelect.addEventListener('change', () => {
        showSkeletonImmediate(260);
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
        });
    });

    resetBtn.addEventListener('click', () => {
        showSkeletonImmediate(260);
        searchInput.value = '';
        sortSelect.value = 'dt_desc';
        requestAnimationFrame(() => {
            applyAll();
            hideSkeleton();
            searchInput.focus();
        });
    });

    // Initial feel
    showSkeletonImmediate(240);
    requestAnimationFrame(() => {
        applyAll();
        hideSkeleton();
    });

    /* ==========================================================
       ✅ Animated Confirm Delete (built-in modal)
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

        // focus
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
        if (!pendingFormSelector) return closeConfirm();

        const form = document.querySelector(pendingFormSelector);
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
