@extends('layouts.admin')

@push('styles')
<style>
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 8px 0 14px;
    }
    .page-head h2{
        margin:0;
        font-weight: 900;
        letter-spacing: -.3px;
        font-size: 22px;
    }
    .page-head .sub{
        margin-top: 4px;
        color: var(--muted);
        font-weight: 800;
        font-size: 13px;
    }

    .toolbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 10px 0 14px;
    }
    .toolbar .left{
        display:flex;
        gap:10px;
        flex-wrap: wrap;
        align-items:center;
        flex: 1;
    }
    .toolbar .right{
        display:flex;
        gap:10px;
        align-items:center;
    }

    .kt-input{
        width: 420px;
        max-width: 100%;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.28);
        background: transparent;
        color: var(--text);
        font-weight: 800;
        outline: none;
        transition: .15s ease;
    }
    .kt-input:focus{
        border-color: rgba(21,90,193,.40);
        box-shadow: 0 0 0 4px rgba(21,90,193,.10);
    }

    .kt-select{
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.28);
        background: transparent;
        color: var(--text);
        font-weight: 900;
        outline: none;
        transition: .15s ease;
    }
    .kt-select:focus{
        border-color: rgba(21,90,193,.40);
        box-shadow: 0 0 0 4px rgba(21,90,193,.10);
    }

    .kt-btn{
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.28);
        background: rgba(21,90,193,.10);
        color: var(--text);
        font-weight: 950;
        cursor: pointer;
        transition: .15s ease;
        display:inline-flex;
        align-items:center;
        gap:8px;
        text-decoration:none;
        white-space: nowrap;
    }
    .kt-btn:hover{
        transform: translateY(-1px);
        background: rgba(21,90,193,.14);
        border-color: rgba(21,90,193,.28);
    }

    .kt-primary{
        background: var(--primary);
        border-color: rgba(21,90,193,.35);
        color: #fff;
        box-shadow: 0 10px 22px rgba(21,90,193,.20);
    }
    .kt-primary:hover{
        background: #0f4faa;
        border-color: rgba(21,90,193,.50);
    }

    .table-card{ padding: 14px; }
    .table-wrap{
        overflow:auto;
        border-radius: 16px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.45);
    }
    html[data-theme="dark"] .table-wrap{
        background: rgba(2,6,23,.20);
    }

    table.kt-table{
        width:100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 980px;
    }
    .kt-table th, .kt-table td{
        padding: 12px 12px;
        border-bottom: 1px solid rgba(148,163,184,.18);
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 800;
    }
    .kt-table th{
        font-size: 12px;
        color: var(--muted);
        background: rgba(148,163,184,.06);
        position: sticky;
        top: 0;
        z-index: 1;
        letter-spacing: .2px;
    }
    .kt-table tbody tr{
        transition: .12s ease;
    }
    .kt-table tbody tr:hover{
        background: rgba(21,90,193,.06);
        transform: translateY(-1px);
    }

    .doc{
        display:flex;
        align-items:center;
        gap:10px;
        min-width: 0;
    }
    .avatar{
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        font-weight: 950;
        color: var(--primary);
        background: rgba(21,90,193,.12);
        border: 1px solid rgba(21,90,193,.18);
        flex: 0 0 auto;
    }
    html[data-theme="dark"] .avatar{
        color: #93c5fd;
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.22);
    }
    .doc .name{
        font-weight: 950;
        letter-spacing: -.2px;
        overflow:hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .doc .subtxt{
        font-size: 12px;
        color: var(--muted);
        font-weight: 800;
        margin-top: 1px;
        overflow:hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 520px;
    }

    .pill{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.08);
    }
    .dot{ width: 8px; height: 8px; border-radius: 999px; }

    .pill-active{
        background: rgba(34,197,94,.12);
        border-color: rgba(34,197,94,.22);
        color: #16a34a;
    }
    .pill-inactive{
        background: rgba(148,163,184,.14);
        border-color: rgba(148,163,184,.22);
        color: var(--text);
    }

    .actions{
        display:flex;
        justify-content:flex-end;
        gap: 8px;
        align-items:center;
    }
    .btn-mini{
        padding: 8px 10px;
        border-radius: 12px;
        border: 1px solid rgba(148,163,184,.22);
        background: transparent;
        color: var(--text);
        font-weight: 950;
        transition: .15s ease;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:8px;
    }
    .btn-mini:hover{
        transform: translateY(-1px);
        border-color: rgba(21,90,193,.30);
        background: rgba(21,90,193,.08);
    }
    .btn-warn:hover{
        border-color: rgba(245,158,11,.35);
        background: rgba(245,158,11,.10);
    }
    .btn-good:hover{
        border-color: rgba(34,197,94,.30);
        background: rgba(34,197,94,.10);
    }

    .muted{ color: var(--muted); font-weight: 800; }

    .footer-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
</style>
@endpush

@section('content')

<div class="page-head">
    <div>
        <h2>Doctors</h2>
        <div class="sub">Manage associate doctors (active/inactive) reflected in Staff side.</div>
    </div>

    <a href="{{ route('admin.doctors.create') }}" class="kt-btn kt-primary">
        <i class="fa fa-plus"></i> New Doctor
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius:12px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="border-radius:12px;">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.doctors.index') }}">
    <div class="toolbar">
        <div class="left">
            <input
                class="kt-input"
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Search doctor (name, email, specialty)"
            />

            <select class="kt-select" name="status">
                <option value="" @selected(($status ?? '') === '')>All Status</option>
                <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
            </select>

            <button class="kt-btn" type="submit">
                <i class="fa fa-filter"></i> Filter
            </button>

            <a class="kt-btn" href="{{ route('admin.doctors.index') }}">
                <i class="fa fa-rotate-left"></i> Reset
            </a>
        </div>

        <div class="right">
            <div class="muted" style="font-size:12px;">
                Total: {{ $doctors->total() }}
            </div>
        </div>
    </div>
</form>

<div class="cardx table-card">
    <div class="table-wrap">
        <table class="kt-table">
            <thead>
            <tr>
                <th style="width: 80px;">ID</th>
                <th style="min-width: 320px;">Doctor</th>
                <th>Specialty</th>
                <th>Contact</th>
                <th>Status</th>
                <th style="min-width: 180px;">Created</th>
                <th class="text-end" style="min-width: 260px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($doctors as $d)
                @php
                    $initials = strtoupper(
                        collect(explode(' ', trim($d->name)))
                            ->filter()
                            ->take(2)
                            ->map(fn($p) => mb_substr($p, 0, 1))
                            ->implode('')
                    ) ?: 'DR';

                    $statusPill = $d->is_active ? 'pill-active' : 'pill-inactive';
                    $statusDot  = $d->is_active ? 'background:#22c55e;' : 'background:#94a3b8;';
                @endphp

                <tr>
                    <td class="muted">#{{ $d->id }}</td>

                    <td>
                        <div class="doc">
                            <div class="avatar">{{ $initials }}</div>
                            <div style="min-width:0;">
                                <div class="name">{{ $d->name }}</div>
                                <div class="subtxt">
                                    {{ $d->email ?: 'No email' }}
                                    @if($d->phone)
                                        <span class="muted" style="font-weight:900;"> • </span>{{ $d->phone }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="muted">{{ $d->specialty ?: '—' }}</td>

                    <td class="muted">
                        {{ $d->phone ?: '—' }}
                    </td>

                    <td>
                        <span class="pill {{ $statusPill }}">
                            <span class="dot" style="{{ $statusDot }}"></span>
                            {{ $d->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="muted">
                        {{ $d->created_at?->format('M d, Y') }}
                    </td>

                    <td class="text-end">
                        <div class="actions">
                            <a href="{{ route('admin.doctors.edit', $d) }}" class="btn-mini">
                                <i class="fa fa-pen"></i> Edit
                            </a>

                            <form method="POST" action="{{ route('admin.doctors.toggleActive', $d) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="btn-mini {{ $d->is_active ? 'btn-warn' : 'btn-good' }}"
                                    title="{{ $d->is_active ? 'Deactivate doctor' : 'Activate doctor' }}"
                                >
                                    <i class="fa {{ $d->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $d->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color:var(--muted);font-weight:900;padding:18px;">
                        No doctors found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer-row">
        <div class="muted">
            Showing {{ $doctors->firstItem() ?? 0 }} to {{ $doctors->lastItem() ?? 0 }}
            of {{ $doctors->total() }} doctors
        </div>
        <div>
            {{ $doctors->links() }}
        </div>
    </div>
</div>

@endsection
