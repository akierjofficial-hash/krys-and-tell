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
            <div class="col-lg-6 col-xl-4"
                 data-appointment-id="{{ $r->id }}"
                 data-service-id="{{ $r->service_id }}"
                 data-doctor-id="{{ $r->doctor_id ?? '' }}"
                 data-date-raw="{{ $r->appointment_date ? \Carbon\Carbon::parse($r->appointment_date)->toDateString() : '' }}"
                 data-time-raw="{{ $r->appointment_time ? \Carbon\Carbon::parse($r->appointment_time)->format('H:i') : '' }}"
                 data-approve-url="{{ route('staff.approvals.approve', $r) }}">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="fw-bold">
                            {{ $r->public_name ?? trim(($r->public_first_name ?? '').' '.($r->public_middle_name ? $r->public_middle_name.' ' : '').($r->public_last_name ?? '')) }}
                        </div>

                        <div class="small text-muted mt-1">
                            <div>
                                <i class="fa-regular fa-calendar me-1"></i>
                                {{ $r->appointment_date }} • {{ $r->appointment_time }}
                            </div>
                            <div><i class="fa-solid fa-tooth me-1"></i> {{ optional($r->service)->name ?? '—' }}</div>
                            <div><i class="fa-solid fa-user-doctor me-1"></i> {{ optional($r->doctor)->name ?? $r->dentist_name ?? '—' }}</div>
                        </div>

                        <hr>

                        <div class="small">
                            <div><strong>Email:</strong> {{ $r->public_email ?? '—' }}</div>
                            <div><strong>Contact:</strong> {{ $r->public_phone ?? '—' }}</div>
                            <div><strong>Address:</strong> {{ $r->public_address ?? '—' }}</div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit-approve">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit & Approve
                            </button>

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

{{-- ✅ Edit & Approve Modal --}}
<div class="modal fade" id="editApproveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title">Edit booking before approving</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editApproveForm">
        <div class="modal-body">
            <input type="hidden" id="eaApproveUrl" value="">
            <input type="hidden" id="eaServiceId" value="">
            <input type="hidden" id="eaAppointmentId" value="">

            <div class="mb-2">
                <div class="fw-semibold" id="eaPatientLabel">Patient</div>
                <div class="small text-muted" id="eaHint">Select doctor/date/time then approve.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Doctor</label>
                <select class="form-select" id="eaDoctor" name="doctor_id">
                    <option value="">— Select doctor —</option>
                    @foreach($doctors ?? [] as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                @if(empty($doctors) || count($doctors) === 0)
                    <div class="small text-muted mt-1">No doctors configured.</div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" id="eaDate" name="appointment_date">
                <div class="small text-muted mt-1">Must be today or later.</div>
            </div>

            <div class="mb-2">
                <label class="form-label">Time</label>
                <select class="form-select" id="eaTime" name="appointment_time">
                    <option value="">— Select time —</option>
                </select>
                <div class="small text-muted mt-1" id="eaTimeHelp"></div>
            </div>

            <div class="alert alert-danger d-none mt-3" id="eaError" style="border-radius:14px;"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fa-solid fa-check me-1"></i> Save & Approve
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
    const widgetUrl = @json(route('staff.approvals.widget'));
    const slotsBase = @json(url('/book')); // /book/{service}/slots
    const doctorRequired = @json((bool)($doctorRequired ?? false));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());

    const grid = document.getElementById('approvalsGrid');
    const pendingBadge = document.getElementById('pendingCountBadge');
    const notice = document.getElementById('liveNotice');

    if(!grid || !pendingBadge) return;

    // Bootstrap modal
    const modalEl = document.getElementById('editApproveModal');
    const editModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    // Modal fields
    const eaForm = document.getElementById('editApproveForm');
    const eaApproveUrl = document.getElementById('eaApproveUrl');
    const eaServiceId = document.getElementById('eaServiceId');
    const eaAppointmentId = document.getElementById('eaAppointmentId');
    const eaDoctor = document.getElementById('eaDoctor');
    const eaDate = document.getElementById('eaDate');
    const eaTime = document.getElementById('eaTime');
    const eaTimeHelp = document.getElementById('eaTimeHelp');
    const eaError = document.getElementById('eaError');
    const eaPatientLabel = document.getElementById('eaPatientLabel');

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

    function setError(msg){
        if(!eaError) return;
        if(!msg){
            eaError.classList.add('d-none');
            eaError.textContent = '';
            return;
        }
        eaError.classList.remove('d-none');
        eaError.textContent = msg;
    }

    function clearTime(){
        if(!eaTime) return;
        eaTime.innerHTML = `<option value="">— Select time —</option>`;
        eaTime.disabled = false;
        if(eaTimeHelp) eaTimeHelp.textContent = '';
    }

    async function loadSlots(){
        setError('');
        clearTime();

        const serviceId = eaServiceId?.value;
        const date = eaDate?.value;
        const doctorId = eaDoctor?.value;

        if(!serviceId || !date){
            if(eaTimeHelp) eaTimeHelp.textContent = 'Select a date to load available times.';
            return;
        }

        if(doctorRequired && !doctorId){
            if(eaTimeHelp) eaTimeHelp.textContent = 'Select a doctor to load available times.';
            return;
        }

        const url = `${slotsBase}/${encodeURIComponent(serviceId)}/slots?date=${encodeURIComponent(date)}${doctorId ? `&doctor_id=${encodeURIComponent(doctorId)}` : ''}`;

        try{
            eaTime.disabled = true;
            if(eaTimeHelp) eaTimeHelp.textContent = 'Loading slots…';

            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });

            const data = await res.json().catch(() => ({}));

            if(!res.ok){
                throw new Error(data?.message || 'Failed to load slots.');
            }

            // walk-in: no slots
            if(data?.meta?.walk_in){
                eaTime.innerHTML = `<option value="">Walk-in (no time slots)</option>`;
                eaTime.disabled = true;
                if(eaTimeHelp) eaTimeHelp.textContent = 'This service is marked as walk-in.';
                return;
            }

            const slots = Array.isArray(data?.slots) ? data.slots : [];
            if(slots.length === 0){
                eaTime.innerHTML = `<option value="">No available times</option>`;
                eaTime.disabled = true;
                if(eaTimeHelp) eaTimeHelp.textContent = 'Try another date/doctor.';
                return;
            }

            eaTime.innerHTML = `<option value="">— Select time —</option>` + slots.map(t => {
                return `<option value="${esc(t)}">${esc(t)}</option>`;
            }).join('');

            eaTime.disabled = false;
            if(eaTimeHelp) eaTimeHelp.textContent = `${slots.length} available slot(s).`;

        }catch(err){
            setError(err.message || 'Failed to load slots.');
            if(eaTimeHelp) eaTimeHelp.textContent = '';
            eaTime.disabled = false;
        }
    }

    function makeCard(item){
        const id = item.id;

        const serviceId = item.service_id ?? '';
        const doctorId = item.doctor_id ?? '';
        const dateRaw = item.date_raw ?? '';
        const timeRaw = item.time_raw ?? '';

        return `
        <div class="col-lg-6 col-xl-4"
            data-appointment-id="${esc(id)}"
            data-service-id="${esc(serviceId)}"
            data-doctor-id="${esc(doctorId)}"
            data-date-raw="${esc(dateRaw)}"
            data-time-raw="${esc(timeRaw)}"
            data-approve-url="${esc(item.approve_url)}">
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

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit-approve">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit & Approve
                        </button>

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
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
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

                document.getElementById('emptyState')?.remove();

                grid.insertAdjacentHTML('afterbegin', makeCard(item));
                seen.add(id);
                added++;
            }

            if (added > 0) {
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

    // ✅ Open modal on "Edit & Approve"
    grid.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-edit-approve');
        if(!btn) return;

        const card = btn.closest('[data-appointment-id]');
        if(!card) return;

        setError('');
        clearTime();

        const apptId = card.getAttribute('data-appointment-id') || '';
        const serviceId = card.getAttribute('data-service-id') || '';
        const doctorId = card.getAttribute('data-doctor-id') || '';
        const dateRaw = card.getAttribute('data-date-raw') || '';
        const timeRaw = card.getAttribute('data-time-raw') || '';
        const approveUrl = card.getAttribute('data-approve-url') || '';

        eaApproveUrl.value = approveUrl;
        eaServiceId.value = serviceId;
        eaAppointmentId.value = apptId;

        // label patient name
        const name = card.querySelector('.fw-bold')?.textContent?.trim() || 'Patient';
        if(eaPatientLabel) eaPatientLabel.textContent = name;

        // prefill fields
        if(eaDoctor) eaDoctor.value = doctorId;
        if(eaDate) eaDate.value = dateRaw;
        if(eaTime) eaTime.value = '';

        // load slots then preselect time if it exists
        await loadSlots();
        if(timeRaw && eaTime && !eaTime.disabled){
            const opt = eaTime.querySelector(`option[value="${CSS.escape(timeRaw)}"]`);
            if(opt) eaTime.value = timeRaw;
        }

        editModal?.show();
    });

    eaDoctor?.addEventListener('change', loadSlots);
    eaDate?.addEventListener('change', loadSlots);

    // ✅ Submit modal form (Save & Approve)
    eaForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        setError('');

        const approveUrl = eaApproveUrl?.value;
        if(!approveUrl){
            setError('Missing approve URL.');
            return;
        }

        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('doctor_id', eaDoctor?.value || '');
        fd.append('appointment_date', eaDate?.value || '');
        fd.append('appointment_time', eaTime?.disabled ? '' : (eaTime?.value || ''));

        const submitBtn = eaForm.querySelector('button[type="submit"]');
        if(submitBtn){ submitBtn.disabled = true; submitBtn.style.opacity = '.7'; }

        try{
            const res = await fetch(approveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: fd
            });

            const data = await res.json().catch(() => ({}));

            if(!res.ok || data.ok === false){
                throw new Error(data.message || 'Approval failed');
            }

            // remove card from grid
            const apptId = eaAppointmentId?.value;
            const card = apptId ? grid.querySelector(`[data-appointment-id="${CSS.escape(apptId)}"]`) : null;
            if(card) card.remove();

            if (typeof data.pendingCount !== 'undefined') {
                pendingBadge.textContent = data.pendingCount;
                lastCount = parseInt(data.pendingCount || 0, 10);
            }

            editModal?.hide();

            showNotice(`<div class="alert alert-success" style="border-radius:14px;">
                <i class="fa-solid fa-circle-check me-1"></i>
                ${esc(data.message || 'Approved')}
            </div>`);
        }catch(err){
            setError(err.message || 'Approval failed.');
        }finally{
            if(submitBtn){ submitBtn.disabled = false; submitBtn.style.opacity = ''; }
        }
    });

    // poll now + every 5s
    poll();
    setInterval(poll, 5000);
})();
</script>
@endsection
