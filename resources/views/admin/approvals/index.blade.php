@extends('layouts.admin') 
@section('title', 'Approval Requests')

@push('styles')
<style>
    .kt-approval-card {
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--surface);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .kt-approval-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .kt-approval-card__name {
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.25;
        margin: 0;
        color: var(--text);
    }

    .kt-approval-card__sub {
        margin-top: 4px;
        font-size: 12px;
        color: var(--muted);
    }

    .kt-approval-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .kt-approval-badge--pending {
        background: rgba(245, 158, 11, .15);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, .22);
    }

    .kt-approval-badge--walkin {
        background: rgba(37, 99, 235, .12);
        color: #1d4ed8;
        border: 1px solid rgba(37, 99, 235, .18);
    }

    .kt-approval-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin: 16px 0;
    }

    .kt-approval-grid__item {
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .55);
    }

    .kt-approval-grid__label {
        display: block;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .kt-approval-grid__value {
        display: block;
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.4;
    }

    .kt-approval-card__contact {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(148, 163, 184, .08);
        border: 1px solid var(--border);
    }

    .kt-approval-card__contact-row {
        display: flex;
        gap: 10px;
        font-size: 13px;
        color: var(--text);
    }

    .kt-approval-card__contact-row + .kt-approval-card__contact-row {
        margin-top: 8px;
    }

    .kt-approval-card__contact-label {
        width: 64px;
        flex: 0 0 64px;
        font-weight: 800;
        color: var(--muted);
    }

    .kt-approval-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .kt-approval-card__actions form {
        margin: 0;
    }

    .kt-approval-primary-btn {
        border-radius: 14px;
        font-weight: 900;
        padding-inline: 14px;
    }

    .kt-approval-modal .modal-content {
        border: 1px solid var(--border);
        box-shadow: var(--card-shadow);
    }

    .kt-approval-modal__snapshot {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: rgba(255, 255, 255, .6);
        padding: 16px;
        margin-bottom: 18px;
    }

    .kt-approval-modal__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .kt-approval-modal__eyebrow::before {
        content: '';
        width: 22px;
        height: 1px;
        background: currentColor;
        opacity: .45;
    }

    .kt-approval-modal__patient {
        font-size: 1.05rem;
        font-weight: 900;
        color: var(--text);
        line-height: 1.3;
    }

    .kt-approval-modal__patient-meta {
        margin-top: 4px;
        font-size: 12px;
        color: var(--muted);
        line-height: 1.5;
    }

    .kt-approval-summary {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .kt-approval-summary__item {
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid var(--border);
        background: rgba(148, 163, 184, .08);
    }

    .kt-approval-summary__item--accent {
        background: rgba(34, 197, 94, .08);
        border-color: rgba(34, 197, 94, .18);
    }

    .kt-approval-summary__label {
        display: block;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .kt-approval-summary__value {
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
    }

    .kt-approval-note-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .kt-approval-note-required {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(245, 158, 11, .14);
        color: #b45309;
        font-size: 11px;
        font-weight: 900;
    }

    .kt-approval-empty {
        border: 1px dashed var(--border);
        border-radius: 22px;
        background: rgba(255, 255, 255, .5);
        box-shadow: none;
    }

    .kt-approval-card .btn,
    .kt-approval-modal .btn,
    .kt-approval-modal .form-control,
    .kt-approval-modal .form-select {
        border-radius: 14px;
    }

    @media (max-width: 575.98px) {
        .kt-approval-grid {
            grid-template-columns: 1fr;
        }

        .kt-approval-card__contact-row {
            flex-direction: column;
            gap: 2px;
        }

        .kt-approval-card__contact-label {
            width: auto;
            flex: 0 0 auto;
        }
    }
</style>
@endpush

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
            @php
                $isWalkInRequest = (bool)($r->is_walk_in_request ?? false);
            @endphp
            <div class="col-lg-6 col-xl-4"
                 data-appointment-id="{{ $r->id }}"
                 data-service-id="{{ $r->service_id }}"
                 data-doctor-id="{{ $r->doctor_id ?? '' }}"
                 data-date-raw="{{ $r->appointment_date ? \Carbon\Carbon::parse($r->appointment_date)->toDateString() : '' }}"
                 data-time-raw="{{ $r->appointment_time ? \Carbon\Carbon::parse($r->appointment_time)->format('H:i') : '' }}"
                 data-is-walkin-request="{{ $isWalkInRequest ? '1' : '0' }}"
                 data-note-raw="{{ $r->staff_note ?? '' }}"
                 data-patient-name="{{ $r->public_name ?? trim(($r->public_first_name ?? '').' '.($r->public_middle_name ? $r->public_middle_name.' ' : '').($r->public_last_name ?? '')) }}"
                 data-service-name="{{ optional($r->service)->name ?? 'Service not set' }}"
                 data-doctor-name="{{ optional($r->doctor)->name ?? $r->dentist_name ?? 'Doctor to be assigned' }}"
                 data-email="{{ $r->public_email ?? 'Not provided' }}"
                 data-phone="{{ $r->public_phone ?? 'Not provided' }}"
                 data-address="{{ $r->public_address ?? 'Not provided' }}"
                 data-approve-url="{{ route('admin.approvals.approve', $r) }}">
                <div class="card kt-approval-card h-100">
                    <div class="card-body p-4">
                        <div class="kt-approval-card__top">
                            <div>
                                <h4 class="kt-approval-card__name">
                                    {{ $r->public_name ?? trim(($r->public_first_name ?? '').' '.($r->public_middle_name ? $r->public_middle_name.' ' : '').($r->public_last_name ?? '')) }}
                                </h4>
                                <div class="kt-approval-card__sub">Request #{{ $r->id }} ready for review</div>
                            </div>

                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="kt-approval-badge kt-approval-badge--pending">Pending</span>
                                @if($isWalkInRequest)
                                    <span class="kt-approval-badge kt-approval-badge--walkin">Walk-in</span>
                                @endif
                            </div>
                        </div>

                        <div class="kt-approval-grid">
                            <div class="kt-approval-grid__item">
                                <span class="kt-approval-grid__label">Requested Date</span>
                                <span class="kt-approval-grid__value">{{ $r->appointment_date ?: 'Not set yet' }}</span>
                            </div>
                            <div class="kt-approval-grid__item">
                                <span class="kt-approval-grid__label">Requested Time</span>
                                <span class="kt-approval-grid__value">{{ $isWalkInRequest ? 'Walk-in request' : ($r->appointment_time ?: 'Not set yet') }}</span>
                            </div>
                            <div class="kt-approval-grid__item">
                                <span class="kt-approval-grid__label">Service</span>
                                <span class="kt-approval-grid__value">{{ optional($r->service)->name ?? 'Not assigned yet' }}</span>
                            </div>
                            <div class="kt-approval-grid__item">
                                <span class="kt-approval-grid__label">Doctor</span>
                                <span class="kt-approval-grid__value">{{ optional($r->doctor)->name ?? $r->dentist_name ?? 'To be assigned' }}</span>
                            </div>
                        </div>

                        <div class="kt-approval-card__contact">
                            <div class="kt-approval-card__contact-row">
                                <span class="kt-approval-card__contact-label">Email</span>
                                <span>{{ $r->public_email ?? 'Not provided' }}</span>
                            </div>
                            <div class="kt-approval-card__contact-row">
                                <span class="kt-approval-card__contact-label">Phone</span>
                                <span>{{ $r->public_phone ?? 'Not provided' }}</span>
                            </div>
                            <div class="kt-approval-card__contact-row">
                                <span class="kt-approval-card__contact-label">Address</span>
                                <span>{{ $r->public_address ?? 'Not provided' }}</span>
                            </div>
                        </div>

                        <div class="kt-approval-card__actions">
                            <button type="button" class="btn btn-primary btn-sm btn-edit-approve kt-approval-primary-btn">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Review & Approve
                            </button>

                            <form method="POST" action="{{ route('admin.approvals.approve', $r) }}" data-ajax="1">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit">
                                    <i class="fa-solid fa-check me-1"></i> Quick Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.approvals.decline', $r) }}" data-ajax="1">
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
                <div class="card kt-approval-empty">
                    <div class="card-body text-center py-5">
                        <div class="fw-bold mb-2">No pending requests</div>
                        <div class="text-muted">You are all caught up for now.</div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $requests->links() }}
    </div>
</div>

{{-- Edit & Approve Modal --}}
<div class="modal fade kt-approval-modal" id="editApproveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
            <h5 class="modal-title mb-1">Review booking before approving</h5>
            <div class="small text-muted">Confirm the final doctor, date, time, and patient note before this request moves forward.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editApproveForm">
        <div class="modal-body">
            <input type="hidden" id="eaApproveUrl" value="">
            <input type="hidden" id="eaServiceId" value="">
            <input type="hidden" id="eaAppointmentId" value="">
            <input type="hidden" id="eaIsWalkInRequest" value="0">

            <div class="kt-approval-modal__snapshot">
                <div class="kt-approval-modal__eyebrow">Request Snapshot</div>
                <div class="kt-approval-modal__patient" id="eaPatientLabel">Patient</div>
                <div class="kt-approval-modal__patient-meta" id="eaPatientMeta">Patient contact details will appear here.</div>

                <div class="kt-approval-summary">
                    <div class="kt-approval-summary__item">
                        <span class="kt-approval-summary__label">Current Request</span>
                        <div class="kt-approval-summary__value" id="eaCurrentSummary">No request details yet.</div>
                    </div>
                    <div class="kt-approval-summary__item kt-approval-summary__item--accent d-none" id="eaUpdatedWrap">
                        <span class="kt-approval-summary__label">Updated Approval</span>
                        <div class="kt-approval-summary__value" id="eaUpdatedSummary">No changes yet.</div>
                    </div>
                </div>
            </div>

            <div class="small text-muted mb-3" id="eaHint">Select the final doctor, date, and time. If anything changes from the original request, add a short reason so the patient understands the update.</div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Doctor</label>
                    <select class="form-select" id="eaDoctor" name="doctor_id">
                        <option value="">Select doctor</option>
                        @foreach($doctors ?? [] as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @if(empty($doctors) || count($doctors) === 0)
                        <div class="small text-muted mt-1">No doctors configured.</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" id="eaDate" name="appointment_date">
                    <div class="small text-muted mt-1">Choose today or a later clinic date.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Time</label>
                    <select class="form-select" id="eaTime" name="appointment_time">
                        <option value="">Select time</option>
                    </select>
                    <div class="small text-muted mt-1" id="eaTimeHelp"></div>
                </div>

                <div class="col-12">
                    <div class="kt-approval-note-meta mb-2">
                        <label class="form-label mb-0" for="eaNote">Note / Reason to patient</label>
                        <span class="kt-approval-note-required d-none" id="eaNoteRequired">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Required because the schedule changed
                        </span>
                    </div>
                    <textarea class="form-control" id="eaNote" name="staff_note" rows="4" placeholder="Example: We moved you to Dr. Santos at 2:00 PM because your requested doctor is unavailable at the original time."></textarea>
                    <div class="small text-muted mt-1">This message will appear in the approval email when you need to explain a change.</div>
                </div>
            </div>

            <div class="alert alert-danger d-none mt-3" id="eaError" style="border-radius:14px;"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="eaSubmitBtn">
            <i class="fa-solid fa-check me-1"></i> Save & Approve
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
    const widgetUrl = @json(route('admin.approvals.widget'));
    const slotsBase = @json(url('/book'));
    const doctorRequired = @json((bool)($doctorRequired ?? false));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());

    const grid = document.getElementById('approvalsGrid');
    const pendingBadge = document.getElementById('pendingCountBadge');
    const notice = document.getElementById('liveNotice');

    if(!grid || !pendingBadge) return;

    const modalEl = document.getElementById('editApproveModal');
    const editModal = modalEl ? new bootstrap.Modal(modalEl) : null;

    const eaForm = document.getElementById('editApproveForm');
    const eaApproveUrl = document.getElementById('eaApproveUrl');
    const eaServiceId = document.getElementById('eaServiceId');
    const eaAppointmentId = document.getElementById('eaAppointmentId');
    const eaIsWalkInRequest = document.getElementById('eaIsWalkInRequest');
    const eaDoctor = document.getElementById('eaDoctor');
    const eaDate = document.getElementById('eaDate');
    const eaTime = document.getElementById('eaTime');
    const eaTimeHelp = document.getElementById('eaTimeHelp');
    const eaError = document.getElementById('eaError');
    const eaPatientLabel = document.getElementById('eaPatientLabel');
    const eaPatientMeta = document.getElementById('eaPatientMeta');
    const eaCurrentSummary = document.getElementById('eaCurrentSummary');
    const eaUpdatedWrap = document.getElementById('eaUpdatedWrap');
    const eaUpdatedSummary = document.getElementById('eaUpdatedSummary');
    const eaHint = document.getElementById('eaHint');
    const eaNote = document.getElementById('eaNote');
    const eaNoteRequired = document.getElementById('eaNoteRequired');
    const eaSubmitBtn = document.getElementById('eaSubmitBtn');

    const seen = new Set(
        Array.from(grid.querySelectorAll('[data-appointment-id]'))
            .map((el) => parseInt(el.getAttribute('data-appointment-id') || '0', 10))
            .filter(Boolean)
    );

    let lastCount = parseInt(pendingBadge.textContent || '0', 10) || 0;
    let loading = false;
    let currentRequest = {
        appointmentId: '',
        patientName: 'Patient',
        serviceName: '',
        doctorId: '',
        doctorName: '',
        date: '',
        time: '',
        email: '',
        phone: '',
        address: '',
        isWalkIn: false,
    };

    const esc = (s) => String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function showNotice(html){
        if(!notice) return;
        notice.innerHTML = html;
        setTimeout(() => { notice.innerHTML = ''; }, 3200);
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
        eaTime.innerHTML = '<option value="">Select time</option>';
        eaTime.disabled = false;
        if(eaTimeHelp) eaTimeHelp.textContent = '';
    }

    function ensureEmptyState(){
        if(grid.querySelector('[data-appointment-id]')) return;
        if(document.getElementById('emptyState')) return;

        grid.insertAdjacentHTML('beforeend', `
            <div class="col-12" id="emptyState">
                <div class="card kt-approval-empty">
                    <div class="card-body text-center py-5">
                        <div class="fw-bold mb-2">No pending requests</div>
                        <div class="text-muted">You are all caught up for now.</div>
                    </div>
                </div>
            </div>
        `);
    }

    function setSubmitLoading(isLoading){
        if(!eaSubmitBtn) return;
        eaSubmitBtn.disabled = isLoading;
        eaSubmitBtn.style.opacity = isLoading ? '.7' : '';
    }

    function formatDateValue(raw){
        if(!raw) return 'Date to be confirmed';
        const parts = String(raw).split('-').map(Number);
        if(parts.length !== 3 || parts.some(Number.isNaN)) return raw;
        const dt = new Date(parts[0], parts[1] - 1, parts[2]);
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }).format(dt);
    }

    function formatTimeValue(raw, isWalkIn){
        if(isWalkIn) return 'Walk-in request';
        if(!raw) return 'Time to be confirmed';
        const parts = String(raw).split(':').map(Number);
        if(parts.length < 2 || parts.some(Number.isNaN)) return raw;
        const dt = new Date(2000, 0, 1, parts[0], parts[1]);
        return new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        }).format(dt);
    }

    function buildSummary(state){
        return [
            state.serviceName || 'Service not set',
            state.doctorName || 'Doctor to be assigned',
            formatDateValue(state.date),
            formatTimeValue(state.time, state.isWalkIn)
        ].join(' | ');
    }

    function getDoctorLabel(){
        const label = eaDoctor?.selectedOptions?.[0]?.textContent?.trim() || '';
        return label && label !== 'Select doctor' ? label : 'Doctor to be assigned';
    }

    function hasScheduleChanged(){
        const doctorChanged = (eaDoctor?.value || '') !== (currentRequest.doctorId || '');
        const dateChanged = (eaDate?.value || '') !== (currentRequest.date || '');
        const timeChanged = currentRequest.isWalkIn
            ? false
            : ((eaTime?.disabled ? '' : (eaTime?.value || '')) !== (currentRequest.time || ''));

        return doctorChanged || dateChanged || timeChanged;
    }

    function syncApprovalState(){
        if(eaCurrentSummary){
            eaCurrentSummary.textContent = buildSummary({
                serviceName: currentRequest.serviceName,
                doctorName: currentRequest.doctorName,
                date: currentRequest.date,
                time: currentRequest.time,
                isWalkIn: currentRequest.isWalkIn,
            });
        }

        const changed = hasScheduleChanged();
        const updatedState = {
            serviceName: currentRequest.serviceName,
            doctorName: getDoctorLabel(),
            date: eaDate?.value || currentRequest.date,
            time: currentRequest.isWalkIn ? '' : (eaTime?.disabled ? '' : (eaTime?.value || currentRequest.time)),
            isWalkIn: currentRequest.isWalkIn,
        };

        if(eaUpdatedSummary){
            eaUpdatedSummary.textContent = buildSummary(updatedState);
        }
        if(eaUpdatedWrap){
            eaUpdatedWrap.classList.toggle('d-none', !changed);
        }
        if(eaNoteRequired){
            eaNoteRequired.classList.toggle('d-none', !changed);
        }
        if(eaNote){
            eaNote.required = changed;
        }
        if(eaHint){
            eaHint.textContent = changed
                ? 'You changed the requested schedule. Add a short note so the patient understands the update.'
                : 'Approve as-is or make adjustments before approving.';
        }
    }

    async function loadSlots(preferredTime = ''){
        setError('');
        clearTime();

        if (eaIsWalkInRequest?.value === '1') {
            eaTime.innerHTML = '<option value="">Walk-in request (no time slot)</option>';
            eaTime.disabled = true;
            if (eaTimeHelp) eaTimeHelp.textContent = 'No time slot is required for this walk-in request.';
            syncApprovalState();
            return;
        }

        const serviceId = eaServiceId?.value;
        const date = eaDate?.value;
        const doctorId = eaDoctor?.value;

        if(!serviceId || !date){
            if(eaTimeHelp) eaTimeHelp.textContent = 'Select a date to load available times.';
            syncApprovalState();
            return;
        }

        if(doctorRequired && !doctorId){
            if(eaTimeHelp) eaTimeHelp.textContent = 'Select a doctor to load available times.';
            syncApprovalState();
            return;
        }

        const url = `${slotsBase}/${encodeURIComponent(serviceId)}/slots?date=${encodeURIComponent(date)}${doctorId ? `&doctor_id=${encodeURIComponent(doctorId)}` : ''}`;

        try{
            eaTime.disabled = true;
            if(eaTimeHelp) eaTimeHelp.textContent = 'Loading available times...';

            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });

            const data = await res.json().catch(() => ({}));

            if(!res.ok){
                throw new Error(data?.message || 'Failed to load slots.');
            }

            if(data?.meta?.walk_in){
                eaTime.innerHTML = '<option value="">Walk-in (no time slots)</option>';
                eaTime.disabled = true;
                if(eaTimeHelp) eaTimeHelp.textContent = 'This service is marked as walk-in.';
                syncApprovalState();
                return;
            }

            const slots = Array.isArray(data?.slots) ? data.slots : [];
            if(slots.length === 0){
                eaTime.innerHTML = '<option value="">No available times</option>';
                eaTime.disabled = true;
                if(eaTimeHelp) eaTimeHelp.textContent = 'Try another date or doctor.';
                syncApprovalState();
                return;
            }

            eaTime.innerHTML = '<option value="">Select time</option>' + slots.map((t) => {
                return `<option value="${esc(t)}">${esc(t)}</option>`;
            }).join('');

            eaTime.disabled = false;

            if(preferredTime){
                const option = eaTime.querySelector(`option[value="${CSS.escape(preferredTime)}"]`);
                if(option){
                    eaTime.value = preferredTime;
                    if(eaTimeHelp) eaTimeHelp.textContent = `${slots.length} available slot(s).`;
                }else if(eaTimeHelp){
                    eaTimeHelp.textContent = 'The original time is no longer available. Please choose a new slot.';
                }
            }else if(eaTimeHelp){
                eaTimeHelp.textContent = `${slots.length} available slot(s).`;
            }
        }catch(err){
            setError(err.message || 'Failed to load slots.');
            if(eaTimeHelp) eaTimeHelp.textContent = '';
            eaTime.disabled = false;
        } finally {
            syncApprovalState();
        }
    }

    function makeCard(item){
        const id = item.id;
        const serviceId = item.service_id ?? '';
        const doctorId = item.doctor_id ?? '';
        const dateRaw = item.date_raw ?? '';
        const timeRaw = item.time_raw ?? '';
        const noteRaw = item.staff_note ?? '';
        const patientName = item.patient ?? 'Patient';
        const serviceName = item.service ?? 'Not assigned yet';
        const doctorName = item.doctor ?? 'To be assigned';
        const email = item.email ?? 'Not provided';
        const phone = item.phone ?? 'Not provided';
        const address = item.address ?? 'Not provided';
        const isWalkInRequest = item.is_walk_in_request ? 1 : 0;
        const timeText = isWalkInRequest ? 'Walk-in request' : (item.time || 'Not set yet');

        return `
        <div class="col-lg-6 col-xl-4"
            data-appointment-id="${esc(id)}"
            data-service-id="${esc(serviceId)}"
            data-doctor-id="${esc(doctorId)}"
            data-date-raw="${esc(dateRaw)}"
            data-time-raw="${esc(timeRaw)}"
            data-is-walkin-request="${esc(isWalkInRequest)}"
            data-note-raw="${esc(noteRaw)}"
            data-patient-name="${esc(patientName)}"
            data-service-name="${esc(serviceName)}"
            data-doctor-name="${esc(doctorName)}"
            data-email="${esc(email)}"
            data-phone="${esc(phone)}"
            data-address="${esc(address)}"
            data-approve-url="${esc(item.approve_url)}">
            <div class="card kt-approval-card h-100">
                <div class="card-body p-4">
                    <div class="kt-approval-card__top">
                        <div>
                            <h4 class="kt-approval-card__name">${esc(patientName)}</h4>
                            <div class="kt-approval-card__sub">Request #${esc(id)} ready for review</div>
                        </div>

                        <div class="d-flex flex-column align-items-end gap-2">
                            <span class="kt-approval-badge kt-approval-badge--pending">Pending</span>
                            ${isWalkInRequest ? '<span class="kt-approval-badge kt-approval-badge--walkin">Walk-in</span>' : ''}
                        </div>
                    </div>

                    <div class="kt-approval-grid">
                        <div class="kt-approval-grid__item">
                            <span class="kt-approval-grid__label">Requested Date</span>
                            <span class="kt-approval-grid__value">${esc(item.date || 'Not set yet')}</span>
                        </div>
                        <div class="kt-approval-grid__item">
                            <span class="kt-approval-grid__label">Requested Time</span>
                            <span class="kt-approval-grid__value">${esc(timeText)}</span>
                        </div>
                        <div class="kt-approval-grid__item">
                            <span class="kt-approval-grid__label">Service</span>
                            <span class="kt-approval-grid__value">${esc(serviceName)}</span>
                        </div>
                        <div class="kt-approval-grid__item">
                            <span class="kt-approval-grid__label">Doctor</span>
                            <span class="kt-approval-grid__value">${esc(doctorName)}</span>
                        </div>
                    </div>

                    <div class="kt-approval-card__contact">
                        <div class="kt-approval-card__contact-row">
                            <span class="kt-approval-card__contact-label">Email</span>
                            <span>${esc(email)}</span>
                        </div>
                        <div class="kt-approval-card__contact-row">
                            <span class="kt-approval-card__contact-label">Phone</span>
                            <span>${esc(phone)}</span>
                        </div>
                        <div class="kt-approval-card__contact-row">
                            <span class="kt-approval-card__contact-label">Address</span>
                            <span>${esc(address)}</span>
                        </div>
                    </div>

                    <div class="kt-approval-card__actions">
                        <button type="button" class="btn btn-primary btn-sm btn-edit-approve kt-approval-primary-btn">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Review & Approve
                        </button>

                        <form method="POST" action="${esc(item.approve_url)}" data-ajax="1">
                            <input type="hidden" name="_token" value="${esc(csrf)}">
                            <button class="btn btn-success btn-sm" type="submit">
                                <i class="fa-solid fa-check me-1"></i> Quick Approve
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
        if (loading || document.hidden) return;

        loading = true;
        try{
            const res = await fetch(widgetUrl + '?limit=12', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });

            if(!res.ok) throw new Error('Polling failed.');

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
                setTimeout(() => { grid.style.boxShadow = ''; }, 600);
            }
        }catch(e){
            console.warn(e);
        }finally{
            loading = false;
        }
    }

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
            ensureEmptyState();

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
        const isWalkInRequest = card.getAttribute('data-is-walkin-request') === '1';
        const noteRaw = card.getAttribute('data-note-raw') || '';
        const approveUrl = card.getAttribute('data-approve-url') || '';

        currentRequest = {
            appointmentId: apptId,
            patientName: card.getAttribute('data-patient-name') || 'Patient',
            serviceName: card.getAttribute('data-service-name') || 'Service not set',
            doctorId,
            doctorName: card.getAttribute('data-doctor-name') || 'Doctor to be assigned',
            date: dateRaw,
            time: timeRaw,
            email: card.getAttribute('data-email') || 'Not provided',
            phone: card.getAttribute('data-phone') || 'Not provided',
            address: card.getAttribute('data-address') || 'Not provided',
            isWalkIn: isWalkInRequest,
        };

        eaApproveUrl.value = approveUrl;
        eaServiceId.value = serviceId;
        eaAppointmentId.value = apptId;
        if (eaIsWalkInRequest) eaIsWalkInRequest.value = isWalkInRequest ? '1' : '0';
        if (eaPatientLabel) eaPatientLabel.textContent = currentRequest.patientName;
        if (eaPatientMeta) eaPatientMeta.textContent = `${currentRequest.email} | ${currentRequest.phone} | ${currentRequest.address}`;
        if (eaDoctor) eaDoctor.value = doctorId;
        if (eaDate) eaDate.value = dateRaw;
        if (eaTime) eaTime.value = '';
        if (eaNote) eaNote.value = noteRaw;

        syncApprovalState();

        if (isWalkInRequest) {
            eaTime.innerHTML = '<option value="">Walk-in request (no time slot)</option>';
            eaTime.disabled = true;
            if (eaTimeHelp) eaTimeHelp.textContent = 'No time slot is required for this walk-in request.';
            syncApprovalState();
        } else {
            await loadSlots(timeRaw);
        }

        editModal?.show();
    });

    eaDoctor?.addEventListener('change', () => {
        if (currentRequest.isWalkIn) {
            syncApprovalState();
            return;
        }
        loadSlots(eaTime?.value || '');
    });

    eaDate?.addEventListener('change', () => {
        if (currentRequest.isWalkIn) {
            syncApprovalState();
            return;
        }
        loadSlots(eaTime?.value || '');
    });

    eaTime?.addEventListener('change', syncApprovalState);
    eaNote?.addEventListener('input', () => {
        if (eaNote.value.trim()) setError('');
    });

    modalEl?.addEventListener('hidden.bs.modal', () => {
        setError('');
        if (eaForm) eaForm.reset();
        clearTime();
        if (eaUpdatedWrap) eaUpdatedWrap.classList.add('d-none');
        if (eaNoteRequired) eaNoteRequired.classList.add('d-none');
        if (eaHint) eaHint.textContent = 'Approve as-is or make adjustments before approving.';
        currentRequest = {
            appointmentId: '',
            patientName: 'Patient',
            serviceName: '',
            doctorId: '',
            doctorName: '',
            date: '',
            time: '',
            email: '',
            phone: '',
            address: '',
            isWalkIn: false,
        };
    });

    eaForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        setError('');

        const approveUrl = eaApproveUrl?.value;
        if(!approveUrl){
            setError('Missing approve URL.');
            return;
        }

        if(hasScheduleChanged() && !String(eaNote?.value || '').trim()){
            setError('Please add a note for the patient when changing doctor, date, or time.');
            eaNote?.focus();
            return;
        }

        const fd = new FormData();
        fd.append('_token', csrf);
        fd.append('doctor_id', eaDoctor?.value || '');
        fd.append('appointment_date', eaDate?.value || '');
        fd.append('appointment_time', eaTime?.disabled ? '' : (eaTime?.value || ''));
        fd.append('staff_note', eaNote?.value || '');

        setSubmitLoading(true);

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

            const apptId = eaAppointmentId?.value;
            const card = apptId ? grid.querySelector(`[data-appointment-id="${CSS.escape(apptId)}"]`) : null;
            if(card) card.remove();
            ensureEmptyState();

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
            setSubmitLoading(false);
        }
    });

    poll();
    setInterval(poll, 5000);
})();
</script>
@endsection

