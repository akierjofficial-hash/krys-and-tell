
(function(){
    const widgetUrl = 'placeholder';
    const slotsBase = 'placeholder';
    const doctorRequired = 'placeholder';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || 'placeholder';

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
