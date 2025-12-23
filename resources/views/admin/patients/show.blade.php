@extends('layouts.admin')

@push('styles')
<style>
    /* ===== Page header / breadcrumb ===== */
    .pwrap{ padding: 12px 0 18px; border-radius: 18px; }
    .crumb{
        display:flex; align-items:center; gap:10px; flex-wrap:wrap;
        margin: 6px 0 14px;
        font-weight: 950;
        color: var(--muted);
    }
    .crumb a{
        text-decoration:none;
        color: var(--muted);
        font-weight: 950;
        transition:.15s ease;
    }
    .crumb a:hover{ color: var(--text); }
    .crumb .sep{ opacity:.7; }

    /* ===== Glass card base (same vibe as dashboard/patients index) ===== */
    .glass{
        position:relative;
        overflow:hidden;
        border-radius: 22px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
        transition: .18s ease;
    }
    .glass::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(900px 260px at 18% 0%, rgba(37,99,235,.12), transparent 55%),
            radial-gradient(900px 260px at 82% 0%, rgba(124,58,237,.10), transparent 60%);
        opacity:.9;
        pointer-events:none;
    }
    .glass:hover{
        transform: translateY(-1px);
        box-shadow: 0 22px 44px rgba(15,23,42,.14);
    }
    html[data-theme="dark"] .glass{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    html[data-theme="dark"] .glass:hover{
        box-shadow: 0 26px 60px rgba(0,0,0,.55);
    }
    .glass-inner{ position:relative; z-index:1; }
    .card-pad{ padding: 16px 16px; }

    /* ===== Left profile card ===== */
    .p-head{
        display:flex;
        align-items:center;
        gap: 12px;
    }
    .avatar{
        width: 54px; height: 54px;
        border-radius: 18px;
        display:grid; place-items:center;
        font-weight: 950;
        letter-spacing: .5px;
        color: #1d4ed8;
        background: rgba(37,99,235,.10);
        border: 1px solid rgba(37,99,235,.18);
        box-shadow: 0 12px 18px rgba(37,99,235,.10);
        flex: 0 0 auto;
        text-transform: uppercase;
    }
    html[data-theme="dark"] .avatar{
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
        box-shadow: 0 18px 28px rgba(0,0,0,.35);
    }
    .hname{
        font-size: 22px;
        font-weight: 950;
        letter-spacing: -.35px;
        margin: 0;
        line-height: 1.1;
        color: var(--text);
    }
    .muted{ color: var(--muted); font-weight: 900; }

    .section-title{
        font-weight: 950;
        letter-spacing: -.2px;
        margin: 0 0 10px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        color: var(--text);
    }
    .section-pill{
        font-size: 12px;
        font-weight: 950;
        color: var(--muted);
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.65);
        padding: 6px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }
    html[data-theme="dark"] .section-pill{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
    }

    .info-grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }
    @media (max-width: 992px){
        .info-grid{ grid-template-columns: 1fr; }
    }
    .info-item{
        background: rgba(255,255,255,.65);
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 16px;
        padding: 10px 12px;
        transition:.15s ease;
    }
    .info-item:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(15,23,42,.08);
    }
    html[data-theme="dark"] .info-item{
        background: rgba(2,6,23,.28);
        border-color: rgba(148,163,184,.18);
        box-shadow: none;
    }
    .i-label{ font-size: 11px; font-weight: 950; color: var(--muted); }
    .i-val{ font-weight: 950; margin-top: 3px; color: var(--text); }

    /* ===== Files list ===== */
    .files{
        display:flex;
        flex-direction:column;
        gap: 10px;
        margin-top: 10px;
    }
    .file-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 12px 12px;
        border-radius: 16px;
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(255,255,255,.65);
        transition: .15s ease;
        min-width: 0;
    }
    .file-row:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(15,23,42,.08);
    }
    html[data-theme="dark"] .file-row{
        background: rgba(2,6,23,.28);
        border-color: rgba(148,163,184,.18);
    }
    .file-left{
        display:flex;
        align-items:center;
        gap: 10px;
        min-width: 0;
    }
    .file-ico{
        width: 38px; height: 38px;
        border-radius: 14px;
        display:grid; place-items:center;
        background: rgba(37,99,235,.10);
        border: 1px solid rgba(37,99,235,.18);
        color: #1d4ed8;
        flex: 0 0 auto;
    }
    html[data-theme="dark"] .file-ico{
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
    }
    .file-title{
        font-weight: 950;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 420px;
    }
    .file-sub{ font-size: 12px; color: var(--muted); font-weight: 900; margin-top:2px; }

    .icon-btn{
        width: 42px; height: 42px;
        border-radius: 14px;
        display:grid; place-items:center;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.70);
        color: var(--text);
        text-decoration:none;
        transition:.15s ease;
        flex: 0 0 auto;
    }
    .icon-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(37,99,235,.30);
        box-shadow: 0 16px 26px rgba(37,99,235,.12);
    }
    html[data-theme="dark"] .icon-btn{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
    }

    /* ===== Tabs ===== */
    .tabbar{
        display:flex;
        gap: 10px;
        flex-wrap:wrap;
        margin-top: 12px;
        margin-bottom: 10px;
    }
    .tabbtn{
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.60);
        color: var(--text);
        font-weight: 950;
        cursor:pointer;
        transition:.15s ease;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
    }
    .tabbtn:hover{ transform: translateY(-1px); }
    .tabbtn.active{
        border-color: rgba(37,99,235,.25);
        background: rgba(37,99,235,.10);
        box-shadow: 0 16px 26px rgba(37,99,235,.12);
        color: var(--text);
    }
    html[data-theme="dark"] .tabbtn{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(0,0,0,.35);
    }
    html[data-theme="dark"] .tabbtn.active{
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.25);
    }

    /* ===== Tables ===== */
    .table-wrap{
        overflow:auto;
        border-radius: 18px;
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(255,255,255,.60);
    }
    html[data-theme="dark"] .table-wrap{
        background: rgba(2,6,23,.20);
        border-color: rgba(148,163,184,.18);
    }
    table.simple{ width:100%; border-collapse: separate; border-spacing: 0; min-width: 520px; }
    table.simple th, table.simple td{
        padding: 11px 10px;
        border-bottom: 1px solid rgba(148,163,184,.16);
        font-weight: 800;
        white-space: nowrap;
        vertical-align: middle;
    }
    table.simple th{
        font-size: 11px;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.55);
        background: rgba(248,250,252,.85);
        position: sticky;
        top: 0;
        z-index: 1;
        text-align:left;
    }
    html[data-theme="dark"] table.simple th{
        background: rgba(17,24,39,.65);
        color: rgba(248,250,252,.68);
    }

    tbody tr{ transition:.15s ease; }
    tbody tr:hover{
        background: rgba(37,99,235,.06);
        transform: translateY(-1px);
    }
    html[data-theme="dark"] tbody tr:hover{
        background: rgba(96,165,250,.10);
    }

    .cell-strong{ font-weight: 950; }

    /* ===== Layout helpers ===== */
    .two-grid{ margin-top: 12px; }
</style>
@endpush

@section('content')

@php
    $fullName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));
    $age = $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->age : null;

    $initials = collect(explode(' ', $fullName))
        ->filter()
        ->map(fn($w) => mb_substr($w, 0, 1))
        ->take(2)
        ->join('') ?: 'P';
@endphp

<div class="pwrap">

    <div class="crumb">
        <a href="{{ route('admin.patients.index') }}">Patients</a>
        <span class="sep">›</span>
        <span style="color:var(--text);">{{ $fullName ?: 'Patient' }}</span>
    </div>

    <div class="row g-3">
        {{-- LEFT: Profile --}}
        <div class="col-lg-4">
            <div class="glass card-pad">
                <div class="glass-inner">

                    <div class="p-head">
                        <div class="avatar">{{ $initials }}</div>
                        <div style="min-width:0;">
                            <div class="hname">{{ $fullName ?: 'Patient' }}</div>
                            <div class="muted" style="margin-top:4px;">Patient Profile</div>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <div class="section-title">
                            <span>General Info</span>
                            <span class="section-pill">Read-only</span>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="i-label">Gender</div>
                                <div class="i-val">{{ $patient->gender ?: '—' }}</div>
                            </div>

                            <div class="info-item">
                                <div class="i-label">Date of Birth</div>
                                <div class="i-val">
                                    {{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('d M Y') : '—' }}
                                    @if($age !== null)
                                        <span class="muted" style="font-size:12px;"> • {{ $age }} y.o.</span>
                                    @endif
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="i-label">Patient ID</div>
                                <div class="i-val">#{{ $patient->id }}</div>
                            </div>

                            <div class="info-item">
                                <div class="i-label">Registered Since</div>
                                <div class="i-val">{{ $patient->created_at?->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:14px;">
                        <div class="section-title">
                            <span>Contact Info</span>
                        </div>

                        <div class="info-grid" style="grid-template-columns: 1fr;">
                            <div class="info-item">
                                <div class="i-label">Phone</div>
                                <div class="i-val">{{ $patient->contact_number ?: '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="i-label">Email</div>
                                <div class="i-val">{{ $patient->email ?: '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="i-label">Address</div>
                                <div class="i-val">{{ $patient->address ?: '—' }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT: Files + History + Treatment --}}
        <div class="col-lg-8">
            {{-- Files --}}
            <div class="glass card-pad">
                <div class="glass-inner">
                    <div class="section-title" style="margin-bottom: 6px;">
                        <span>Files</span>
                        <span class="section-pill">{{ $patient->files?->count() ?? 0 }} items</span>
                    </div>

                    <div class="files">
                        @forelse($patient->files as $f)
                            <div class="file-row">
                                <div class="file-left">
                                    <div class="file-ico"><i class="fa-regular fa-file-lines"></i></div>
                                    <div style="min-width:0;">
                                        <div class="file-title">{{ $f->title }}</div>
                                        <div class="file-sub">Download file</div>
                                    </div>
                                </div>

                                <a class="icon-btn"
                                   href="{{ \Illuminate\Support\Facades\Storage::url($f->file_path) }}"
                                   target="_blank"
                                   title="Download">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        @empty
                            <div class="muted" style="padding: 8px 2px;">No files uploaded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Two cards --}}
            <div class="row g-3 two-grid">
                {{-- Appointment history --}}
                <div class="col-lg-6">
                    <div class="glass card-pad">
                        <div class="glass-inner">
                            <div class="section-title" style="margin-bottom: 8px;">
                                <span>Appointments History</span>
                            </div>

                            <div class="tabbar">
                                <button type="button" class="tabbtn active" data-tab="upcoming">Upcoming</button>
                                <button type="button" class="tabbtn" data-tab="past">Past</button>
                            </div>

                            <div id="tab-upcoming">
                                <div class="table-wrap">
                                    <table class="simple">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Procedure</th>
                                                <th>Records</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($upcoming->take(8) as $a)
                                                @php
                                                    $proc = $a->service?->name ?? '—';
                                                    $dt = ($a->appointment_date && $a->appointment_time)
                                                        ? \Carbon\Carbon::parse($a->appointment_date.' '.$a->appointment_time)
                                                        : null;
                                                @endphp
                                                <tr>
                                                    <td class="muted">{{ $dt ? $dt->format('d.m.Y') : '—' }}</td>
                                                    <td class="cell-strong">{{ $proc }}</td>
                                                    <td class="muted"><i class="fa-regular fa-file-lines"></i></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="muted">No upcoming appointments.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="tab-past" style="display:none;">
                                <div class="table-wrap">
                                    <table class="simple">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Procedure</th>
                                                <th>Records</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($past->take(8) as $a)
                                                @php
                                                    $proc = $a->service?->name ?? '—';
                                                    $dt = ($a->appointment_date && $a->appointment_time)
                                                        ? \Carbon\Carbon::parse($a->appointment_date.' '.$a->appointment_time)
                                                        : null;
                                                @endphp
                                                <tr>
                                                    <td class="muted">{{ $dt ? $dt->format('d.m.Y') : '—' }}</td>
                                                    <td class="cell-strong">{{ $proc }}</td>
                                                    <td class="muted"><i class="fa-regular fa-file-lines"></i></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="muted">No past appointments.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Treatment --}}
                <div class="col-lg-6">
                    <div class="glass card-pad">
                        <div class="glass-inner">
                            <div class="section-title" style="margin-bottom: 8px;">
                                <span>Treatment</span>
                            </div>

                            <div class="table-wrap">
                                <table class="simple">
                                    <thead>
                                        <tr>
                                            <th>Procedure</th>
                                            <th>Tooth</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($procedures->take(10) as $vp)
                                            <tr>
                                                <td class="cell-strong">{{ $vp->service?->name ?? '—' }}</td>
                                                <td class="muted">{{ $vp->tooth_number ?: '—' }}</td>
                                                <td class="muted">
                                                    {{ $vp->visit_date ? \Carbon\Carbon::parse($vp->visit_date)->format('d.m.Y') : '—' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="muted">No treatments yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.tabbtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tabbtn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const tab = btn.getAttribute('data-tab');
        const up = document.getElementById('tab-upcoming');
        const past = document.getElementById('tab-past');

        if (up) up.style.display = (tab === 'upcoming') ? '' : 'none';
        if (past) past.style.display = (tab === 'past') ? '' : 'none';
    });
});
</script>
@endpush

@endsection
