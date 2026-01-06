@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        --card-border: 1px solid rgba(15, 23, 42, .10);
        --soft: rgba(15, 23, 42, .06);
        --text: #0f172a;
        --muted: rgba(15, 23, 42, .60);
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

    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15,23,42,.12);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        background: rgba(255,255,255,.92);
        color: rgba(15,23,42,.78) !important;
    }
    .btnx:hover{
        transform: translateY(-1px);
        background: #fff;
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
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
    tbody tr:hover{ background: rgba(13,110,253,.06); }

    .pill-open{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        background: rgba(59, 130, 246, .12);
        color: #1d4ed8 !important;
        border: 1px solid rgba(59, 130, 246, .22);
        white-space: nowrap;
    }
    .pill-open:hover{ background: rgba(59, 130, 246, .18); }

    @media (max-width: 768px){
        .search-box{ width: 100%; }
        .top-actions{ width: 100%; }
    }
</style>

<div class="page-head">
    <div>
        <h2 class="page-title">Service Folder</h2>
        <p class="subtitle">
            Patients who took: <strong>{{ $service->name }}</strong>
        </p>
    </div>

    <div class="top-actions">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="patientSearch" placeholder="Search patient name…">
        </div>

        <a href="{{ route('staff.services.index') }}" class="btnx">
            <i class="fa fa-arrow-left"></i> Back to Services
        </a>
    </div>
</div>

<div class="card-shell">
    <div class="card-head">
        <span class="count-pill">
            <i class="fa fa-folder-open"></i>
            Showing <strong id="visibleCount">{{ $patients->count() }}</strong> / <strong id="totalCount">{{ $patients->count() }}</strong>
        </span>
        <div style="font-size:12px; color: rgba(15,23,42,.55); font-weight:800;">Sorted by last name (A–Z)</div>
    </div>

    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th class="text-end">Open</th>
                </tr>
            </thead>
            <tbody id="patientTable">
                @forelse($patients as $patient)
                    <tr class="p-row">
                        <td class="fw-semibold">
                            {{ $patient->last_name }}, {{ $patient->first_name }}
                        </td>
                        <td class="text-end">
                            {{-- If your patient route is different, change this to your correct route --}}
                            <a class="pill-open" href="{{ route('staff.patients.show', $patient->id) }}">
                                <i class="fa fa-user"></i> Profile
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">No patients found for this service.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('patientSearch');
    const rows  = Array.from(document.querySelectorAll('.p-row'));
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl   = document.getElementById('totalCount');

    const normalize = s => (s || '').toString().toLowerCase().trim();

    totalCountEl.textContent = rows.length;
    visibleCountEl.textContent = rows.length;

    input?.addEventListener('keyup', () => {
        const q = normalize(input.value);
        let visible = 0;

        rows.forEach(r => {
            const text = normalize(r.textContent);
            const show = text.includes(q);
            r.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        visibleCountEl.textContent = visible;
    });
})();
</script>

@endsection
