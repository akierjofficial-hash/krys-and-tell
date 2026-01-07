@extends('layouts.admin')
@section('title', 'Activity Log')

@push('styles')
<style>
    /* Page shell (matches admin glass style) */
    .alog-wrap{
        padding: 12px 0 18px;
        background:
            radial-gradient(900px 420px at 12% -10%, rgba(37,99,235,.12), transparent 60%),
            radial-gradient(900px 420px at 92% 12%, rgba(124,58,237,.10), transparent 55%),
            radial-gradient(900px 520px at 40% 110%, rgba(34,197,94,.08), transparent 55%);
        border-radius: 18px;
    }

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
    .sub{
        margin-top: 5px;
        color: var(--muted);
        font-weight: 900;
        font-size: 13px;
    }

    /* Glass card */
    .glass{
        position:relative;
        overflow:hidden;
        border-radius: 22px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
    }
    html[data-theme="dark"] .glass{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    .glass-inner{ position:relative; z-index:1; }

    /* Back button */
    .btnx{
        border-radius: 14px;
        font-weight: 950;
        padding: 10px 14px;
        border: 1px solid rgba(148,163,184,.22);
        background: rgba(255,255,255,.70);
        box-shadow: 0 12px 18px rgba(15,23,42,.06);
        transition: .15s ease;
    }
    .btnx:hover{ transform: translateY(-1px); }
    html[data-theme="dark"] .btnx{
        background: rgba(2,6,23,.30) !important;
        border-color: rgba(148,163,184,.18) !important;
        color: var(--text) !important;
        box-shadow: 0 14px 24px rgba(0,0,0,.35);
    }

    /* Table dark-safe */
    .table{ margin:0; color: var(--text) !important; }
    .table > :not(caption) > * > *{
        background-color: transparent !important;
        color: inherit !important;
        border-color: rgba(148,163,184,.16) !important;
    }

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
        color: rgba(248,250,252,.68) !important;
        border-bottom-color: rgba(148,163,184,.18) !important;
    }

    .table tbody td{
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
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .table tbody tr:hover{
        background: rgba(96,165,250,.10) !important;
    }

    .muted{ color: var(--muted) !important; font-weight: 900; }
    .smallx{ font-size: 12px; }

    /* Badges */
    .badge-soft{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        color: var(--text);
    }

    .b-get{ background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.18); }
    .b-post{ background: rgba(37,99,235,.10); border-color: rgba(37,99,235,.18); }
    .b-put{ background: rgba(124,58,237,.10); border-color: rgba(124,58,237,.18); }
    .b-del{ background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.18); }

    .status{
        font-weight: 950;
    }
    .st-ok{ color: #16a34a; }
    .st-bad{ color: #dc2626; }
    html[data-theme="dark"] .st-ok{ color: #22c55e; }
    html[data-theme="dark"] .st-bad{ color: #ef4444; }

    /* Pagination dark-safe */
    html[data-theme="dark"] .pagination .page-link{
        background: rgba(2,6,23,.35) !important;
        border-color: rgba(148,163,184,.18) !important;
        color: var(--text) !important;
    }
    html[data-theme="dark"] .pagination .page-item.active .page-link{
        background: rgba(37,99,235,.35) !important;
        border-color: rgba(37,99,235,.45) !important;
    }
</style>
@endpush

@section('content')
<div class="alog-wrap">

    <div class="head">
        <div>
            <h2>Activity Log</h2>
            <div class="sub">
                {{ $user->name ?? ($user->email ?? 'User') }}
                @if($user->last_login_at)
                    • Last login: {{ \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y h:i A') }}
                @endif
            </div>
        </div>

        <x-back-button
            fallback="{{ route('admin.users.index') }}"
            class="btn btnx"
            icon_class="fa-solid fa-arrow-left me-1"
            label="Back"
        />
    </div>

    <div class="glass">
        <div class="glass-inner">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:190px;">When</th>
                            <th>Action</th>
                            <th style="width:110px;">Method</th>
                            <th style="width:90px;">Status</th>
                            <th>URL</th>
                            <th style="width:160px;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $m = strtoupper((string)($log->method ?? ''));
                                $badgeClass =
                                    $m === 'GET' ? 'b-get' :
                                    ($m === 'POST' ? 'b-post' :
                                    ($m === 'PUT' || $m === 'PATCH' ? 'b-put' :
                                    ($m === 'DELETE' ? 'b-del' : '')));

                                $sc = (int)($log->status_code ?? 0);
                                $statusClass = ($sc >= 200 && $sc < 400) ? 'st-ok' : 'st-bad';
                            @endphp
                            <tr>
                                <td class="smallx">
                                    {{ optional($log->created_at)->format('M d, Y') }}<br>
                                    <span class="muted">{{ optional($log->created_at)->format('h:i A') }}</span>
                                </td>

                                <td style="white-space: normal; min-width: 240px;">
                                    <div style="font-weight:950;">
                                        {{ $log->event ?? 'request' }}
                                    </div>
                                    @if(!empty($log->description))
                                        <div class="smallx muted">{{ $log->description }}</div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge-soft {{ $badgeClass }}">{{ $m ?: '—' }}</span>
                                </td>

                                <td class="status {{ $sc ? $statusClass : '' }}">
                                    {{ $log->status_code ?? '—' }}
                                </td>

                                <td class="smallx muted" style="white-space: normal; min-width: 280px;">
                                    {{ \Illuminate\Support\Str::limit((string)($log->url ?? ''), 90) }}
                                </td>

                                <td class="smallx">
                                    {{ $log->ip ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center muted py-5">No activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
