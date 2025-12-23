@extends('layouts.admin')

@push('styles')
<style>
    /* ===== Header (dashboard vibe) ===== */
    .pt-wrap{ padding: 12px 0 18px; border-radius: 18px; }
    .pt-head{
        display:flex; align-items:flex-end; justify-content:space-between;
        gap: 16px; flex-wrap: wrap; margin: 6px 0 12px;
    }
    .pt-title{
        margin:0; font-size: 28px; font-weight: 950;
        letter-spacing: -0.6px; line-height: 1.12; color: var(--text);
    }
    .pt-sub{ margin-top:6px; font-size:13px; color: var(--muted); font-weight: 800; }

    .pt-pill{
        display:inline-flex; align-items:center; gap:8px;
        padding: 9px 12px; border-radius: 999px;
        border: 1px solid rgba(37,99,235,.18);
        background: rgba(37,99,235,.08);
        font-weight: 900; font-size: 12px; color: #1d4ed8;
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(37,99,235,.10);
    }
    html[data-theme="dark"] .pt-pill{
        background: rgba(96,165,250,.10);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
        box-shadow: 0 16px 26px rgba(0,0,0,.35);
    }

    /* ===== Glass card base ===== */
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

    /* ===== Search bar ===== */
    .filters{ padding: 14px; margin-bottom: 14px; }
    .f-row{
        display:flex; gap:10px; flex-wrap:wrap;
        align-items:center; justify-content:space-between;
    }
    .f-left{ display:flex; gap:10px; flex-wrap:wrap; align-items:center; flex:1; min-width: 260px; }
    .kt-input{
        min-width: 260px; flex: 1;
        padding: 11px 12px; border-radius: 14px;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.72);
        color: var(--text);
        font-weight: 900; outline:none;
        transition:.15s ease;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
    }
    .kt-input:focus{
        border-color: rgba(37,99,235,.30);
        box-shadow: 0 16px 26px rgba(37,99,235,.12);
    }
    html[data-theme="dark"] .kt-input{
        background: rgba(2,6,23,.40);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(0,0,0,.35);
        color: var(--text);
    }

    .kt-btn{
        padding: 11px 14px; border-radius: 14px;
        border: 1px solid rgba(37,99,235,.22);
        background: rgba(37,99,235,.10);
        color: var(--text);
        font-weight: 950;
        cursor:pointer;
        transition:.15s ease;
        box-shadow: 0 12px 18px rgba(37,99,235,.10);
        white-space: nowrap;
    }
    .kt-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(37,99,235,.35);
        box-shadow: 0 18px 28px rgba(37,99,235,.14);
    }

    .muted{ color: var(--muted); font-weight: 900; }

    /* ===== Table card ===== */
    .table-card{ padding: 14px; }
    .table-wrap{
        overflow:auto;
        border-radius: 18px;
        border: 1px solid rgba(15,23,42,.08);
        background: rgba(255,255,255,.70);
    }
    html[data-theme="dark"] .table-wrap{
        background: rgba(2,6,23,.20);
        border-color: rgba(148,163,184,.18);
    }

    table.kt-table{
        width:100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 920px;
    }
    .kt-table th, .kt-table td{
        padding: 12px 12px;
        border-bottom: 1px solid rgba(148,163,184,.16);
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 800;
    }
    .kt-table th{
        font-size: 11px;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: rgba(15,23,42,.55);
        background: rgba(248,250,252,.85);
        position: sticky;
        top: 0;
        z-index: 2;
    }
    html[data-theme="dark"] .kt-table th{
        background: rgba(17,24,39,.65);
        color: rgba(248,250,252,.68);
    }

    .kt-table tbody tr{
        cursor:pointer;
        transition:.15s ease;
    }
    .kt-table tbody tr:hover{
        background: rgba(37,99,235,.06);
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .kt-table tbody tr:hover{
        background: rgba(96,165,250,.10);
    }

    /* Name cell */
    .p-name{
        display:flex;
        align-items:center;
        gap:10px;
        font-weight: 950;
        min-width: 260px;
    }
    .avatar{
        width: 36px;
        height: 36px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        font-weight: 950;
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

    /* Footer */
    .footer-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-top: 12px;
        padding: 0 2px;
    }

    /* Small screens: reduce min-width so it scrolls nicer */
    @media (max-width: 640px){
        table.kt-table{ min-width: 860px; }
        .pt-title{ font-size: 24px; }
    }
</style>
@endpush

@section('content')
<div class="pt-wrap">

    <div class="pt-head">
        <div>
            <h2 class="pt-title">Patients</h2>
            <div class="pt-sub">Read-only list (data from Staff side).</div>
        </div>
        <div class="pt-pill">
            <i class="fa fa-users"></i> {{ number_format($patients->total() ?? 0) }} total
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.patients.index') }}">
        <div class="glass filters">
            <div class="glass-inner">
                <div class="f-row">
                    <div class="f-left">
                        <input class="kt-input" type="text" name="q" value="{{ $q }}" placeholder="Search patient (name, email, contact)" />
                        <button class="kt-btn" type="submit">
                            <i class="fa fa-magnifying-glass me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass table-card">
        <div class="glass-inner">
            <div class="table-wrap">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Patient</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $p)
                            @php
                                $name = trim(($p->first_name ?? '').' '.($p->last_name ?? ''));
                                $initials = collect(explode(' ', $name))
                                    ->filter()
                                    ->map(fn($w) => mb_substr($w, 0, 1))
                                    ->take(2)
                                    ->join('') ?: 'P';
                            @endphp
                            <tr onclick="window.location='{{ route('admin.patients.show', $p) }}'">
                                <td class="muted">#{{ $p->id }}</td>
                                <td>
                                    <div class="p-name">
                                        <div class="avatar">{{ $initials }}</div>
                                        <div>
                                            <div style="font-weight:950; line-height:1.1;">{{ $name ?: 'Patient' }}</div>
                                            <div class="muted" style="font-size:12px; font-weight:900; margin-top:2px;">
                                                {{ $p->email ?: '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="muted">{{ $p->gender ?: '—' }}</td>
                                <td class="muted">
                                    {{ $p->birthdate ? \Carbon\Carbon::parse($p->birthdate)->format('M d, Y') : '—' }}
                                </td>
                                <td class="muted">{{ $p->contact_number ?: '—' }}</td>
                                <td class="muted">{{ $p->email ?: '—' }}</td>
                                <td class="muted">{{ $p->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="muted" style="padding:18px;">
                                    No patients found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer-row">
                <div class="muted">
                    Showing {{ $patients->firstItem() ?? 0 }} to {{ $patients->lastItem() ?? 0 }}
                    of {{ $patients->total() }}
                </div>
                <div>
                    {{ $patients->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
