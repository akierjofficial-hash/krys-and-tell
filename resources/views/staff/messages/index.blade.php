@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: var(--kt-shadow, 0 10px 25px rgba(15, 23, 42, .06));
        --card-border: 1px solid var(--kt-border, rgba(15, 23, 42, .10));
        --text: var(--kt-text, #0f172a);
        --muted: var(--kt-muted, rgba(15, 23, 42, .55));
        --soft: rgba(148, 163, 184, .14);
        --radius: 16px;
    }
    html[data-theme="dark"]{ --soft: rgba(148, 163, 184, .16); }

    .wrap{ max-width: 1220px; }

    .page-head{
        display:flex; align-items:flex-end; justify-content:space-between;
        gap: 12px; margin-bottom: 14px; flex-wrap: wrap;
    }
    .page-title{
        margin:0; font-size: 26px; font-weight: 950;
        letter-spacing: -.3px; color: var(--text);
    }
    .subtitle{ margin: 4px 0 0 0; font-size: 13px; color: var(--muted); }

    .pill{
        display:inline-flex; align-items:center; gap: 8px;
        padding: 8px 12px; border-radius: 999px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148, 163, 184, .10));
        color: var(--text); font-weight: 900; font-size: 13px;
        white-space: nowrap;
    }
    .dot{ width: 8px; height: 8px; border-radius: 50%; background: currentColor; opacity: .9; }
    .pill.unread{ color:#f59e0b; border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.10); }
    .pill.live{ color:#22c55e; border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.10); }

    .cardx{
        background: var(--kt-surface, #fff);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        color: var(--text);
    }

    .toolbar{
        display:flex; align-items:center; justify-content:space-between;
        gap: 10px; padding: 12px 14px;
        border-bottom: 1px solid var(--soft);
        background: linear-gradient(180deg, rgba(148,163,184,.08), transparent);
        flex-wrap: wrap;
    }
    html[data-theme="dark"] .toolbar{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
    }

    .search{
        position: relative;
        flex: 1 1 320px;
        max-width: 520px;
        min-width: 240px;
    }
    .search input{
        width:100%;
        border-radius: 12px;
        border: 1px solid var(--kt-border);
        background: var(--kt-input-bg, rgba(148,163,184,.08));
        color: var(--text);
        padding: 10px 12px 10px 38px;
        outline: none;
        font-weight: 650;
        font-size: 13px;
    }
    .search svg{
        position:absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        opacity: .6; pointer-events:none;
    }

    .table-wrap{ overflow-x:auto; }
    table{ width:100%; border-collapse: separate; border-spacing:0; }
    thead th{
        padding: 12px 12px;
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--muted);
        background: rgba(148,163,184,.10);
        border-bottom: 1px solid var(--soft);
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{ background: rgba(2,6,23,.35); }

    tbody td{
        padding: 12px 12px;
        font-size: 14px;
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
        color: var(--text);
    }

    tbody tr{ transition: background .12s ease, transform .12s ease; cursor: pointer; }
    tbody tr:hover{ background: rgba(96,165,250,.08); transform: translateY(-1px); }

    .row-unread td{ font-weight: 800; }

    .status-badge{
        display:inline-flex; align-items:center; gap: 8px;
        border-radius: 999px; padding: 6px 10px;
        font-size: 12px; font-weight: 950;
        border: 1px solid transparent;
        white-space: nowrap; line-height: 1;
    }
    .status-unread{ background: rgba(245,158,11,.10); color:#f59e0b; border-color: rgba(245,158,11,.35); }
    .status-read{ background: rgba(148,163,184,.10); color: rgba(148,163,184,.95); border-color: rgba(148,163,184,.22); }

    .name{
        display:flex; align-items:center; gap: 10px; min-width: 200px;
    }
    .avatar{
        width: 34px; height: 34px;
        border-radius: 12px;
        display:grid; place-items:center;
        font-weight: 950; color:#60a5fa;
        background: rgba(96,165,250,.12);
        border: 1px solid rgba(96,165,250,.22);
        flex: 0 0 auto;
    }
    .nmeta{ display:flex; flex-direction: column; min-width: 0; }
    .nmeta .n{
        font-weight: 950; line-height: 1.1;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 280px;
    }
    .nmeta .e{
        font-size: 12px;
        color: var(--muted);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 320px;
    }

    .preview{
        color: var(--muted);
        max-width: 520px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .btnx{
        display:inline-flex; align-items:center; gap: 8px;
        padding: 9px 12px; border-radius: 12px;
        font-weight: 950; font-size: 13px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148,163,184,.10));
        color: var(--text);
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
    }
    .btnx:hover{ transform: translateY(-1px); }
    .btnx-primary{
        border: none;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        color:#fff;
        box-shadow: 0 10px 18px rgba(13,110,253,.20);
    }
    .btnx-primary:hover{ color:#fff; box-shadow: 0 14px 24px rgba(13,110,253,.26); }

    .new-row{ animation: ktFlash 1.2s ease both; }
    @keyframes ktFlash{
        0%{ background: rgba(34,197,94,.18); }
        100%{ background: transparent; }
    }

    .pagination { margin: 0; gap: 6px; flex-wrap: wrap; }
    .pagination .page-link{
        border-radius: 10px;
        font-weight: 900;
        border: 1px solid var(--kt-border);
        color: var(--text);
        background: var(--kt-surface-2);
    }
    .pagination .page-item.active .page-link{
        background: rgba(96,165,250,.14);
        border-color: rgba(96,165,250,.28);
        color:#60a5fa;
    }

    @media (max-width: 768px){
        .col-email, .col-received{ display:none; }
        thead .col-email, thead .col-received{ display:none; }
        .preview{ max-width: 320px; }
    }
</style>

<div class="wrap">
    <div class="page-head">
        <div>
            <h2 class="page-title">Messages</h2>
            <div class="subtitle">Contact form inbox</div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <span class="pill unread">
                <span class="dot"></span>
                <span id="unreadCountText">{{ (int)$unreadCount }}</span> unread
            </span>

            <span class="pill live">
                <span class="dot"></span> Live
            </span>
        </div>
    </div>

    <div class="cardx">
        <div class="toolbar">
            <div style="font-weight:950;">Inbox</div>

            <div class="search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                </svg>
                <input id="msgSearch" type="text" placeholder="Search name, email, message…" autocomplete="off">
            </div>

            <a class="btnx" href="{{ route('staff.messages.index') }}" title="Refresh">
                <i class="fa-solid fa-rotate-right"></i> Refresh
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:120px;">Status</th>
                        <th>Name</th>
                        <th class="col-email">Email</th>
                        <th>Message</th>
                        <th class="col-received" style="width:190px;">Received</th>
                        <th style="width:140px;">Action</th>
                    </tr>
                </thead>

                <tbody id="msgTbody">
                    @forelse($messages as $m)
                        @php
                            $isUnread = !$m->read_at;
                            $name = $m->name ?? 'Message';
                            $initials = strtoupper(mb_substr($name, 0, 1) . mb_substr($name, -1, 1));
                        @endphp

                        <tr class="{{ $isUnread ? 'row-unread' : '' }}"
                            data-msg-id="{{ $m->id }}"
                            data-kt-href="{{ route('staff.messages.show', $m) }}" data-href="{{ route('staff.messages.show', $m) }}"
                            data-search="{{ strtolower(($m->name ?? '').' '.($m->email ?? '').' '.($m->message ?? '')) }}">
                            <td>
                                @if($m->read_at)
                                    <span class="status-badge status-read">Read</span>
                                @else
                                    <span class="status-badge status-unread">Unread</span>
                                @endif
                            </td>

                            <td>
                                <div class="name">
                                    <div class="avatar">{{ $initials }}</div>
                                    <div class="nmeta">
                                        <div class="n">{{ $m->name }}</div>
                                        <div class="e d-md-none">{{ $m->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="col-email" style="min-width:220px;">
                                <span class="text-muted">{{ $m->email }}</span>
                            </td>

                            <td>
                                <div class="preview">{{ $m->message }}</div>
                            </td>

                            <td class="col-received">
                                <span class="text-muted">
                                    {{ optional($m->created_at)->format('M d, Y h:i A') }}
                                </span>
                            </td>

                            <td>
                                <a class="btnx btnx-primary"
                                   href="{{ route('staff.messages.show', $m) }}" data-kt-return
                                   onclick="event.stopPropagation();">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center py-4" style="color:var(--muted);">
                                No messages yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $messages->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
(function(){
    if (window.KTListState) {
        window.KTListState.bindInput('#msgSearch', 'q');
        window.KTListState.injectReturn();
    }
    const input = document.getElementById('msgSearch');
    const tbody = document.getElementById('msgTbody');
    const unreadText = document.getElementById('unreadCountText');

    function escapeHtml(str){
        return (str ?? '').toString()
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function initialsFromName(name){
        const s = (name || 'M').trim();
        const a = s.charAt(0) || 'M';
        const b = s.charAt(s.length - 1) || 'S';
        return (a + b).toUpperCase();
    }

    // click row -> open
    if (tbody){
        tbody.addEventListener('click', (e) => {
            const tr = e.target.closest('tr[data-href]');
            if (!tr) return;
            const href = tr.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    }

    // search filter
    input?.addEventListener('input', () => {
        const q = (input.value || '').toLowerCase().trim();
        const rows = tbody?.querySelectorAll('tr[data-search]') || [];
        rows.forEach(r => {
            const hay = r.getAttribute('data-search') || '';
            r.style.display = hay.includes(q) ? '' : 'none';
        });
    });

    // live unread count updates from layout poller
    window.addEventListener('kt:messages:count', (ev) => {
        const n = Number(ev?.detail?.unreadCount || 0);
        if (unreadText) unreadText.textContent = String(n);
    });

    // insert new messages (only if user is on first page and no search query)
    window.addEventListener('kt:messages:new', (ev) => {
        const msgs = ev?.detail?.messages || [];
        if (!Array.isArray(msgs) || msgs.length === 0) return;

        if (input && (input.value || '').trim() !== '') return;

        const url = new URL(window.location.href);
        const page = url.searchParams.get('page');
        if (page && page !== '1') return;

        document.getElementById('emptyRow')?.remove();

        const ordered = msgs.slice().sort((a,b) => Number(a.id) - Number(b.id));

        ordered.forEach(m => {
            const id = Number(m.id || 0);
            const name = m.name || 'Message';
            const email = m.email || '';
            const message = m.message || '';
            const received = m.created_human || m.created_at || '';
            const showUrl = m.show_url || '#';

            if (tbody.querySelector(`tr[data-msg-id="${id}"]`)) return;

            const tr = document.createElement('tr');
            tr.className = 'row-unread new-row';
            tr.setAttribute('data-msg-id', String(id));
            tr.setAttribute('data-kt-href', showUrl);
            tr.setAttribute('data-href', showUrl);
            tr.setAttribute('data-search', (name + ' ' + email + ' ' + message).toLowerCase());

            tr.innerHTML = `
                <td><span class="status-badge status-unread">Unread</span></td>
                <td>
                    <div class="name">
                        <div class="avatar">${escapeHtml(initialsFromName(name))}</div>
                        <div class="nmeta">
                            <div class="n">${escapeHtml(name)}</div>
                            <div class="e d-md-none">${escapeHtml(email)}</div>
                        </div>
                    </div>
                </td>
                <td class="col-email" style="min-width:220px;">
                    <span class="text-muted">${escapeHtml(email)}</span>
                </td>
                <td><div class="preview">${escapeHtml(message)}</div></td>
                <td class="col-received"><span class="text-muted">${escapeHtml(received)}</span></td>
                <td>
                    <a class="btnx btnx-primary" href="${escapeHtml(showUrl)}" data-kt-return onclick="event.stopPropagation();">
                        <i class="fa-solid fa-eye"></i> View
                    </a>
                </td>
            `;

            tbody.prepend(tr);
            if (window.KTListState) window.KTListState.injectReturn();

            const rows = tbody.querySelectorAll('tr[data-msg-id]');
            if (rows.length > 20) rows[rows.length - 1].remove();
        });
    });
})();
</script>

@endsection
