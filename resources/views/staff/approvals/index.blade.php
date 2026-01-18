@extends('layouts.staff') 
@section('title', 'Approval Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0">Approval Requests</h3>
            <small class="text-muted">Public bookings waiting for approval</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark" style="border-radius:999px;font-weight:900;">
                Pending: <span id="pendingCountBadge">{{ $requests->total() }}</span>
            </span>

            <span class="badge bg-light text-dark" style="border-radius:999px;font-weight:900;">
                <i class="fa-solid fa-circle text-success me-1" style="font-size:10px;"></i>
                Live
            </span>
        </div>
    </div>

    <div id="liveNotice"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3" id="approvalsGrid">
        @forelse($requests as $r)
            <div class="col-lg-6 col-xl-4" data-appointment-id="{{ $r->id }}">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-bold">
                            {{ $r->public_name ?? trim(($r->public_first_name ?? '').' '.($r->public_middle_name ? $r->public_middle_name.' ' : '').($r->public_last_name ?? '')) }}
                        </div>

                        <div class="small text-muted mt-1">
                            <div><i class="fa-regular fa-calendar me-1"></i> {{ $r->appointment_date }} • {{ $r->appointment_time }}</div>
                            <div><i class="fa-solid fa-tooth me-1"></i> {{ optional($r->service)->name ?? '—' }}</div>
                            <div><i class="fa-solid fa-user-doctor me-1"></i> {{ optional($r->doctor)->name ?? $r->dentist_name ?? '—' }}</div>
                        </div>

                        <hr>

                        <div class="small">
                            <div><strong>Email:</strong> {{ $r->public_email ?? '—' }}</div>
                            <div><strong>Contact:</strong> {{ $r->public_phone ?? '—' }}</div>
                            <div><strong>Address:</strong> {{ $r->public_address ?? '—' }}</div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <form method="POST" action="{{ route('staff.approvals.approve', $r) }}" data-ajax="1">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('staff.approvals.decline', $r) }}" data-ajax="1">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                    <i class="fa-solid fa-xmark me-1"></i> Decline
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12" id="emptyState">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        No pending requests 🎉
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>
</div>

<script>
(function(){
    const widgetUrl = @json(route('staff.approvals.widget'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());

    const grid = document.getElementById('approvalsGrid');
    const pendingBadge = document.getElementById('pendingCountBadge');
    const notice = document.getElementById('liveNotice');

    if(!grid || !pendingBadge) return;

    const seen = new Set(
        Array.from(grid.querySelectorAll('[data-appointment-id]'))
            .map(el => parseInt(el.getAttribute('data-appointment-id') || '0', 10))
            .filter(Boolean)
    );

    let lastCount = parseInt(pendingBadge.textContent || '0', 10) || 0;
    let loading = false;

    const esc = (s) => String(s ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'",'&#039;');

    function showNotice(html){
        if(!notice) return;
        notice.innerHTML = html;
        setTimeout(() => { notice.innerHTML = ''; }, 2500);
    }

    function makeCard(item){
        const id = item.id;

        return `
        <div class="col-lg-6 col-xl-4" data-appointment-id="${id}">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="fw-bold">${esc(item.patient)}</div>

                    <div class="small text-muted mt-1">
                        <div><i class="fa-regular fa-calendar me-1"></i> ${esc(item.date)} • ${esc(item.time)}</div>
                        <div><i class="fa-solid fa-tooth me-1"></i> ${esc(item.service)}</div>
                        <div><i class="fa-solid fa-user-doctor me-1"></i> ${esc(item.doctor)}</div>
                    </div>

                    <hr>

                    <div class="small">
                        <div><strong>Email:</strong> ${esc(item.email)}</div>
                        <div><strong>Contact:</strong> ${esc(item.phone)}</div>
                        <div><strong>Address:</strong> ${esc(item.address)}</div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <form method="POST" action="${esc(item.approve_url)}" data-ajax="1">
                            <input type="hidden" name="_token" value="${esc(csrf)}">
                            <button class="btn btn-success btn-sm" type="submit">
                                <i class="fa-solid fa-check me-1"></i> Approve
                            </button>
                        </form>

                        <form method="POST" action="${esc(item.decline_url)}" data-ajax="1">
                            <input type="hidden" name="_token" value="${esc(csrf)}">
                            <button class="btn btn-outline-danger btn-sm" type="submit">
                                <i class="fa-solid fa-xmark me-1"></i> Decline
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;
    }

    async function poll(){
        if (loading) return;
        if (document.hidden) return;

        loading = true;
        try{
            const res = await fetch(widgetUrl + '?limit=12', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if(!res.ok) throw new Error('poll failed');

            const data = await res.json();

            const incomingCount = parseInt(data.pendingCount || 0, 10);
            pendingBadge.textContent = incomingCount;

            if (incomingCount > lastCount) {
                showNotice(`<div class="alert alert-info" style="border-radius:14px;">
                    <i class="fa-solid fa-bell me-1"></i>
                    New booking request received.
                </div>`);
            }
            lastCount = incomingCount;

            const items = Array.isArray(data.items) ? data.items : [];
            let added = 0;

            for (const item of items) {
                const id = parseInt(item?.id || 0, 10);
                if(!id || seen.has(id)) continue;

                // remove empty state if exists
                document.getElementById('emptyState')?.remove();

                grid.insertAdjacentHTML('afterbegin', makeCard(item));
                seen.add(id);
                added++;
            }

            if (added > 0) {
                // quick highlight flash
                grid.style.transition = 'box-shadow .2s ease';
                grid.style.boxShadow = '0 0 0 4px rgba(34,197,94,.18)';
                setTimeout(() => grid.style.boxShadow = '', 600);
            }
        }catch(e){
            console.warn(e);
        }finally{
            loading = false;
        }
    }

    // ✅ AJAX approve/decline (no page reload)
    grid.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!form.matches('form[data-ajax="1"]')) return;

        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }

        try{
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: new FormData(form)
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok || data.ok === false) {
                throw new Error(data.message || 'Action failed');
            }

            // remove the card
            const card = form.closest('[data-appointment-id]');
            if (card) card.remove();

            if (typeof data.pendingCount !== 'undefined') {
                pendingBadge.textContent = data.pendingCount;
                lastCount = parseInt(data.pendingCount || 0, 10);
            }

            showNotice(`<div class="alert alert-success" style="border-radius:14px;">
                <i class="fa-solid fa-circle-check me-1"></i>
                ${esc(data.message || 'Done')}
            </div>`);
        }catch(err){
            showNotice(`<div class="alert alert-danger" style="border-radius:14px;">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                ${esc(err.message || 'Action failed')}
            </div>`);
        }finally{
            if (btn) { btn.disabled = false; btn.style.opacity = ''; }
        }
    });

    // poll now + every 5s
    poll();
    setInterval(poll, 5000);
})();
</script>
@endsection
