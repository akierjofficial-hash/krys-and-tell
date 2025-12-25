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
        min-width: 240px;
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
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        background: rgba(15,23,42,.06);
        color: rgba(15,23,42,.75);
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
        border: 1px solid transparent;
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

    /* Status badges */
    .badge-soft{
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
    .badge-dot{ width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    .st-pending{ background: rgba(245, 158, 11, .12); color:#b45309; border-color: rgba(245,158,11,.25); }
    .st-confirmed{ background: rgba(59, 130, 246, .12); color:#1d4ed8; border-color: rgba(59,130,246,.25); }
    .st-done{ background: rgba(34, 197, 94, .12); color:#15803d; border-color: rgba(34,197,94,.25); }
    .st-cancelled{ background: rgba(239, 68, 68, .12); color:#b91c1c; border-color: rgba(239,68,68,.25); }
    .st-default{ background: rgba(107, 114, 128, .12); color: rgba(15, 23, 42, .75); border-color: rgba(107,114,128,.25); }

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
        font-weight: 700;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-view{
        background: rgba(59, 130, 246, .12);
        color:#1d4ed8 !important;
        border-color: rgba(59,130,246,.22);
    }
    .pill-view:hover{ background: rgba(59, 130, 246, .18); }

    .pill-edit{
        background: rgba(34, 197, 94, .12);
        color:#15803d !important;
        border-color: rgba(34,197,94,.22);
    }
    .pill-edit:hover{ background: rgba(34, 197, 94, .18); }

    .pill-del{
        background: rgba(239, 68, 68, .12);
        color:#b91c1c !important;
        border-color: rgba(239,68,68,.22);
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
<div class="card-shell">
    <div class="card-head">
        <div class="hint">
            Showing <strong id="visibleCount">{{ $appointments->count() }}</strong> /
            <strong id="totalCount">{{ $appointments->count() }}</strong> appointment(s)
        </div>
        <div class="hint">Tip: search + sort works together</div>
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

                    // ✅ patient safe fallbacks (patient can be null)
                    $pFirst = $appointment->patient->first_name ?? $appointment->public_first_name ?? '';
                    $pLast  = $appointment->patient->last_name  ?? $appointment->public_last_name  ?? '';
                    $patientName = trim($pFirst.' '.$pLast);
                    if ($patientName === '') $patientName = $appointment->public_name ?? 'N/A';

                    $patientKey = strtolower(trim(($pLast ?: '').', '.($pFirst ?: '')));

                    // ✅ service safe fallback (service can be null)
                    $serviceName = $appointment->service->name ?? 'N/A';

                    // ✅ dentist safe fallback (dentist_name OR doctor relation)
                    $dentistName = $appointment->dentist_name
                        ?? ($appointment->doctor->name ?? null)
                        ?? 'N/A';

                    $dentistKey = strtolower(trim($dentistName));
                    $statusKey  = strtolower(trim($appointment->status ?? ''));

                    // ✅ date/time safe
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
                                <i class="fa fa-eye"></i> View
                            </a>

                            <a href="{{ route('staff.appointments.edit', $appointment) }}" class="pill pill-edit">
                                <i class="fa fa-pen"></i> Edit
                            </a>

                            <form action="{{ route('staff.appointments.destroy', $appointment) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Delete appointment?');">
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
                        No appointments found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('appointmentSearch');
    const sortSelect  = document.getElementById('appointmentSort');
    const resetBtn    = document.getElementById('clearFilters');

    const tbody       = document.getElementById('appointmentTableBody');
    const rowsAll     = Array.from(document.querySelectorAll('.appointment-row'));

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

    function getComparable(row, mode){
        const d = row.dataset;
        switch(mode){
            case 'dt_desc': return Number(d.dt || 0);
            case 'dt_asc':  return Number(d.dt || 0);
            case 'patient_asc':
            case 'patient_desc': return d.patient || '';
            case 'dentist_asc':
            case 'dentist_desc': return d.dentist || '';
            case 'status_asc':
            case 'status_desc': return d.status || '';
            default: return Number(d.dt || 0);
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
                return Number(b.dataset.dt || 0) - Number(a.dataset.dt || 0);
            }

            if (va === vb) return 0;
            const asc = mode.endsWith('_asc');
            return asc ? (va - vb) : (vb - va);
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
        sortSelect.value = 'dt_desc';
        applyAll();
        searchInput.focus();
    });

    applyAll();
})();
</script>

@endsection
