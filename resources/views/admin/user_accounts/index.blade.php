@extends('layouts.admin')

@section('kt_live_scope', 'user_accounts')
@section('kt_live_interval', 20000)

@push('styles')
<style>
    /* ===== Page shell ===== */
    .uwrap{
        padding: 12px 0 18px;
        background:
            radial-gradient(900px 420px at 12% -10%, rgba(37,99,235,.12), transparent 60%),
            radial-gradient(900px 420px at 92% 12%, rgba(124,58,237,.10), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, rgba(34,197,94,.08), transparent 55%);
        border-radius: 18px;
    }

    /* ===== Header ===== */
    .head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 8px 0 14px;
        padding: 0 2px;
    }
    .head h2{
        margin:0;
        font-weight: 950;
        letter-spacing: -.45px;
        font-size: 22px;
        color: var(--text);
    }
    .head .sub{
        margin-top: 5px;
        color: var(--muted);
        font-weight: 900;
        font-size: 13px;
    }

    /* ===== Glass cards ===== */
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
    .glass-inner{ position:relative; z-index:1; }

    /* ✅ Dark mode glass */
    html[data-theme="dark"] .glass{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    html[data-theme="dark"] .glass::before{
        opacity: .55;
    }

    /* ===== Alerts ===== */
    .alertx{
        border-radius: 16px;
        border: 1px solid rgba(148,163,184,.18);
        box-shadow: 0 12px 22px rgba(15,23,42,.08);
        font-weight: 900;
    }

    /* ===== Filters ===== */
    .filters{ padding: 14px 14px; }

    .filters .form-control,
    .filters .form-select{
        border-radius: 14px;
        font-weight: 900;
        border: 1px solid rgba(148,163,184,.22);
        background: rgba(255,255,255,.75);
        color: var(--text);
        padding: 10px 12px;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
        transition: .15s ease;
    }
    .filters .form-control:focus,
    .filters .form-select:focus{
        outline: none;
        box-shadow: 0 18px 28px rgba(37,99,235,.12);
        border-color: rgba(37,99,235,.35);
        background: #fff;
    }

    /* ✅ Dark mode inputs/selects */
    html[data-theme="dark"] .filters .form-control,
    html[data-theme="dark"] .filters .form-select{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 14px 24px rgba(0,0,0,.35);
        color: var(--text);
    }
    html[data-theme="dark"] .filters .form-control::placeholder{
        color: rgba(248,250,252,.55);
    }
    html[data-theme="dark"] .filters .form-select option{
        background: rgba(2,6,23,.95);
        color: var(--text);
    }

    /* ===== Buttons (filter/reset + actions) ===== */
    .btnx{
        border-radius: 14px;
        font-weight: 950;
        padding: 10px 14px;
        box-shadow: 0 14px 26px rgba(15, 23, 42, .08);
        transition: .15s ease;
        border: 1px solid rgba(148,163,184,.22);
        background: rgba(255,255,255,.70);
        color: var(--text);
    }
    .btnx:hover{ transform: translateY(-1px); }

    /* ✅ Dark mode buttons */
    html[data-theme="dark"] .btnx{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 14px 24px rgba(0,0,0,.35);
        color: var(--text);
    }

    /* ===== Table card ===== */
    .table-card{ overflow:hidden; border-radius: 22px; }
    .table{ margin:0; }

    /* ✅ Dark mode table header */
    .table thead th{
        background: rgba(248,250,252,.85) !important;
        color: rgba(15,23,42,.55) !important;
        font-weight: 950 !important;
        font-size: 11px;
        letter-spacing: .35px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(148,163,184,.18) !important;
        white-space: nowrap;
        padding: 14px 12px !important;
    }
    html[data-theme="dark"] .table thead th{
        background: rgba(17,24,39,.65) !important;
        color: rgba(248,250,252,.70) !important;
        border-bottom-color: rgba(148,163,184,.18) !important;
    }

    .table tbody td{
        border-color: rgba(148,163,184,.16) !important;
        color: var(--text) !important;
        font-weight: 850;
        vertical-align: middle;
        padding: 14px 12px !important;
        white-space: nowrap;
    }

    .table tbody tr{
        transition: .15s ease;
    }
    .table tbody tr:hover{
        background: rgba(37,99,235,.06) !important;
    }
    html[data-theme="dark"] .table tbody tr:hover{
        background: rgba(96,165,250,.10) !important;
    }

    /* ===== Email text ===== */
    .email{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
    }

    /* ===== Avatar + name ===== */
    .urow{
        display:flex;
        align-items:center;
        gap: 12px;
        min-width: 0;
    }
    .avatar{
        width: 40px; height: 40px;
        border-radius: 14px;
        display:grid; place-items:center;
        font-weight: 950;
        color: #1d4ed8;
        background: rgba(37,99,235,.10);
        border: 1px solid rgba(37,99,235,.18);
        flex: 0 0 auto;
    }
    html[data-theme="dark"] .avatar{
        background: rgba(96,165,250,.12);
        border-color: rgba(96,165,250,.20);
        color: #93c5fd;
    }

    .uname{
        font-weight: 950;
        overflow:hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 320px;
    }

    /* ===== Status badges ===== */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        color: var(--text);
    }
    .dot{ width: 8px; height: 8px; border-radius: 999px; }

    .st-active{ background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.18); color: #16a34a; }
    .st-active .dot{ background: #22c55e; }

    .st-inactive{ background: rgba(148,163,184,.12); border-color: rgba(148,163,184,.18); color: var(--text); }
    .st-inactive .dot{ background: rgba(148,163,184,.95); }

    /* ✅ Dark mode badge text clarity */
    html[data-theme="dark"] .badge-soft{
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.18);
        color: rgba(248,250,252,.90);
    }
    html[data-theme="dark"] .st-active{
        color: #86efac;
        background: rgba(34,197,94,.12);
        border-color: rgba(34,197,94,.22);
    }
    html[data-theme="dark"] .st-inactive{
        color: rgba(248,250,252,.85);
        background: rgba(148,163,184,.10);
        border-color: rgba(148,163,184,.18);
    }

    /* ===== Row action buttons ===== */
    .abtn{
        border-radius: 14px !important;
        font-weight: 950 !important;
        padding: 9px 12px !important;
        border: 1px solid rgba(148,163,184,.22) !important;
        background: rgba(255,255,255,.70) !important;
        transition: .15s ease;
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
        color: var(--text) !important;
    }
    .abtn:hover{ transform: translateY(-1px); }

    html[data-theme="dark"] .abtn{
        background: rgba(2,6,23,.30) !important;
        border-color: rgba(148,163,184,.18) !important;
        box-shadow: 0 14px 24px rgba(0,0,0,.35);
        color: var(--text) !important;
    }

    .abtn-danger{
        background: rgba(239,68,68,.12) !important;
        border-color: rgba(239,68,68,.22) !important;
        color: #b91c1c !important;
    }
    .abtn-danger:hover{ background: rgba(239,68,68,.18) !important; }

    html[data-theme="dark"] .abtn-danger{
        background: rgba(239,68,68,.14) !important;
        border-color: rgba(239,68,68,.22) !important;
        color: #fecaca !important;
    }

    /* ===== Footer ===== */
    .footer{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 12px;
        padding: 0 2px;
    }
</style>
@endpush

@section('content')
<div class="uwrap">

    <div class="head">
        <div>
            <h2>User Accounts</h2>
            <div class="sub">Patients / website accounts (role = user)</div>
        </div>
        {{-- ✅ No create button (users are created via Google / registration flow) --}}
    </div>

    @if(session('success'))
        <div class="alert alert-success alertx">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alertx">{{ session('error') }}</div>
    @endif

    <div class="glass filters mb-3">
        <div class="glass-inner">
            <form class="row g-2" method="GET" action="{{ route('admin.user_accounts.index') }}">
                <div class="col-md-6">
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Search name or email...">
                </div>

                @if($hasActive)
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All status</option>
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        </select>
                    </div>
                @endif

                <div class="col-md-{{ $hasActive ? '3' : '6' }}">
                    <div class="d-flex gap-2">
                        <button class="btn btnx w-100" type="submit">
                            <i class="fa fa-filter me-2"></i> Filter
                        </button>
                        <a class="btn btnx w-100" href="{{ route('admin.user_accounts.index') }}">
                            <i class="fa fa-rotate-left me-2"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="glass table-card">
        <div class="glass-inner">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 320px;">User</th>
                            @if($hasActive)
                                <th style="min-width: 140px;">Status</th>
                            @endif
                            <th style="min-width: 220px;">Last Login</th>
                            <th class="text-end" style="min-width: 260px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            @php
                                $name = $u->name ?? 'User';
                                $initials = collect(explode(' ', $name))
                                    ->filter()
                                    ->map(fn($w) => mb_substr($w, 0, 1))
                                    ->take(2)
                                    ->join('') ?: 'U';

                                $isActive = (bool)($u->is_active ?? false);
                            @endphp

                            <tr>
                                <td>
                                    <div class="urow">
                                        <div class="avatar">{{ strtoupper($initials) }}</div>
                                        <div style="min-width:0;">
                                            <div class="uname">{{ $name }}</div>
                                            <div class="email">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                @if($hasActive)
                                    <td>
                                        @if($isActive)
                                            <span class="badge-soft st-active">
                                                <span class="dot"></span> Active
                                            </span>
                                        @else
                                            <span class="badge-soft st-inactive">
                                                <span class="dot"></span> Inactive
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                <td>
                                    @if($u->last_login_at)
                                        <div style="font-weight:950;">
                                            {{ \Carbon\Carbon::parse($u->last_login_at)->format('M d, Y h:i A') }}
                                        </div>
                                        <div class="email">IP: {{ $u->last_login_ip ?? '—' }}</div>
                                    @else
                                        <span class="email">Never</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.user_accounts.edit', $u) }}" class="btn btn-sm abtn me-1">
                                        <i class="fa fa-pen me-1"></i> Edit
                                    </a>

                                    <form class="d-inline"
                                          method="POST"
                                          action="{{ route('admin.user_accounts.destroy', $u) }}"
                                          onsubmit="return confirm('Delete this user account? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm abtn abtn-danger" type="submit">
                                            <i class="fa fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hasActive ? 4 : 3 }}" class="text-center" style="color:var(--muted);font-weight:950;padding:18px;">
                                    No user accounts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="email" style="font-size:13px;">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
        </div>
        <div>{{ $users->links() }}</div>
    </div>

</div>
@endsection
