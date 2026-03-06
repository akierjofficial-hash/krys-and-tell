@extends('layouts.public')
@section('title', 'Edit Booking - ' . ($service->name ?? 'Service'))

@section('content')
<section class="section section-soft">
    <div class="container">
        <style>
            .kt-step-pill{
                display: inline-flex;
                align-items: center;
                gap: 8px;
                border: 1px solid var(--kt-border, rgba(15,23,42,.12));
                background: rgba(255,255,255,.75);
                border-radius: 999px;
                padding: 6px 10px;
                font-weight: 850;
                font-size: 12px;
            }
            .kt-slot-grid{
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-top: 10px;
            }
            @media (min-width: 576px){
                .kt-slot-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            .kt-slot{
                border: 1px solid var(--kt-border, rgba(15,23,42,.12));
                background: rgba(255,255,255,.88);
                border-radius: 16px;
                padding: 10px 10px;
                text-align: left;
                font-weight: 900;
                font-size: 13px;
                cursor: pointer;
                width: 100%;
            }
            .kt-slot.is-active{
                border-color: rgba(194,138,99,.55);
                background: rgba(194,138,99,.12);
            }
        </style>

        <x-back-button
            fallback="{{ route('profile.show') }}"
            class="text-decoration-none fw-bold"
            icon_class="fa-solid fa-arrow-left me-1"
            label="Back to Profile"
        />

        <div class="row g-4 mt-2 align-items-stretch">
            <div class="col-lg-7 d-flex">
                <div class="card card-soft p-4 w-100">
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                        <div>
                            <h2 class="sec-title mb-1">Edit Booking</h2>
                            <div class="sec-sub mb-0">
                                Update your pending request for <b>{{ $service->name ?? 'Service' }}</b>.
                            </div>
                        </div>

                        <span class="kt-step-pill">
                            <i class="fa-solid fa-hourglass-half"></i> Pending
                        </span>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success mt-3 mb-0">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mt-3 mb-0">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="mt-3" method="POST" action="{{ route('public.booking.update', $appointment->id) }}">
                        @csrf
                        @method('PUT')

                        @if($doctorRequired)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Doctor</label>
                                <select class="form-select" name="doctor_id" id="doctor_id" required>
                                    <option value="">-- Choose Doctor --</option>
                                    @foreach($doctors as $d)
                                        <option value="{{ $d->id }}" @selected((string)old('doctor_id', $prefillDoctorId) === (string)$d->id)>
                                            {{ $d->name ?? ('Doctor #' . $d->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="doctorHelp" class="small text-muted mt-1"></div>
                                @error('doctor_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <input type="hidden" name="doctor_id" id="doctor_id" value="{{ old('doctor_id', $prefillDoctorId) }}">
                        @endif

                        <div class="row g-3">
                            <div class="{{ $isWalkIn ? 'col-md-6' : 'col-md-4' }}">
                                <label class="form-label fw-bold">Date</label>
                                <input type="date"
                                       class="form-control"
                                       name="date"
                                       id="date"
                                       value="{{ old('date', $prefillDate) }}"
                                       min="{{ now()->toDateString() }}"
                                       required>
                                @error('date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            @if(!$isWalkIn)
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Time</label>
                                    <select class="form-select" name="time" id="time" required>
                                        <option value="">
                                            {{ $doctorRequired ? 'Select doctor + date first...' : 'Select date first...' }}
                                        </option>
                                    </select>

                                    <div class="d-md-none">
                                        <div class="small text-muted mt-2" style="font-weight:700;">Tap a time slot:</div>
                                        <div id="slotGrid" class="kt-slot-grid"></div>
                                    </div>

                                    <div class="small text-muted mt-1" id="timeHelp"></div>
                                    @error('time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @else
                                <input type="hidden" name="time" value="">
                            @endif
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Message (optional)</label>
                            <textarea class="form-control" name="message" rows="3" maxlength="500"
                                      placeholder="Anything you want us to know?">{{ old('message', $appointment->public_message ?? '') }}</textarea>
                            @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn kt-btn kt-btn-primary text-white" type="submit">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save changes
                            </button>
                            <a class="btn kt-btn kt-btn-outline" href="{{ route('profile.show') }}">
                                <i class="fa-solid fa-user me-1"></i> My profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 d-flex">
                <div class="img-tile w-100" style="height:100%;">
                    <img src="{{ asset('assets/img/public/pic7.jpg') }}" alt="Clinic">
                </div>
            </div>
        </div>
    </div>
</section>

@if(!$isWalkIn)
@push('scripts')
<script>
(function(){
    const serviceId = @json($service->id);
    const dateEl = document.getElementById('date');
    const timeEl = document.getElementById('time');
    const helpEl = document.getElementById('timeHelp');
    const doctorEl = document.getElementById('doctor_id');
    const doctorHelpEl = document.getElementById('doctorHelp');
    const gridEl = document.getElementById('slotGrid');
    const doctorRequired = @json((bool) $doctorRequired);
    const oldTime = @json(old('time', $prefillTime));
    const doctorLabelMap = new Map();

    if (!dateEl || !timeEl) return;

    if (doctorEl) {
        Array.from(doctorEl.options).forEach((opt) => {
            if (!opt.value) return;
            doctorLabelMap.set(String(opt.value), (opt.textContent || '').trim());
        });
    }

    function fmt12h(t){
        if(!t || typeof t !== 'string' || !t.includes(':')) return t;
        const [hh, mm] = t.split(':');
        let h = parseInt(hh, 10);
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = (h % 12) || 12;
        return `${h}:${mm} ${ampm}`;
    }

    function setLoading(msg){
        timeEl.innerHTML = `<option value="">${msg}</option>`;
        if (helpEl) helpEl.textContent = '';
        if (gridEl) gridEl.innerHTML = '';
    }

    function resetDoctorOptions(){
        if (!doctorEl) return;

        Array.from(doctorEl.options).forEach((opt) => {
            if (!opt.value) return;
            const key = String(opt.value);
            const baseLabel = doctorLabelMap.get(key) || (opt.textContent || '').replace(/\s+\(Unavailable\)\s*$/i, '').trim();
            doctorLabelMap.set(key, baseLabel);
            opt.textContent = baseLabel;
            opt.disabled = false;
            opt.hidden = false;
        });

        if (doctorHelpEl) doctorHelpEl.textContent = '';
    }

    async function syncDoctorsByDate(date){
        if (!doctorRequired || !doctorEl) return;

        if (!date) {
            resetDoctorOptions();
            return;
        }

        let res;
        try {
            const url = new URL(`/book/${serviceId}/doctors`, window.location.origin);
            url.searchParams.set('date', date);
            res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        } catch (e) {
            resetDoctorOptions();
            return;
        }

        if (!res.ok) {
            resetDoctorOptions();
            return;
        }

        const data = await res.json();
        const doctors = Array.isArray(data?.doctors) ? data.doctors : [];
        const byId = new Map(doctors.map((d) => [String(d.id), d]));

        let selectedUnavailableReason = '';

        Array.from(doctorEl.options).forEach((opt) => {
            if (!opt.value) return;

            const key = String(opt.value);
            const info = byId.get(key);
            const baseLabel = doctorLabelMap.get(key) || (opt.textContent || '').replace(/\s+\(Unavailable\)\s*$/i, '').trim();
            doctorLabelMap.set(key, baseLabel);

            if (!info || info.available) {
                opt.textContent = baseLabel;
                opt.disabled = false;
                opt.hidden = false;
                return;
            }

            opt.textContent = `${baseLabel} (Unavailable)`;
            opt.disabled = true;
            opt.hidden = true;

            if (doctorEl.value === key) {
                selectedUnavailableReason = info.reason || 'Unavailable on this date.';
            }
        });

        if (doctorEl.value) {
            const selectedInfo = byId.get(String(doctorEl.value));
            if (selectedInfo && !selectedInfo.available) {
                doctorEl.value = '';
            }
        }

        if (doctorHelpEl) {
            doctorHelpEl.textContent = selectedUnavailableReason
                ? `Selected dentist is unavailable: ${selectedUnavailableReason}`
                : '';
        }
    }

    function renderGrid(slots){
        if (!gridEl) return;
        gridEl.innerHTML = slots.map(t => `<button type="button" class="kt-slot" data-time="${t}">${fmt12h(t)}</button>`).join('');

        const btns = Array.from(gridEl.querySelectorAll('.kt-slot'));
        const markActive = (val) => btns.forEach(b => b.classList.toggle('is-active', b.dataset.time === val));

        markActive(timeEl.value);

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                timeEl.value = btn.dataset.time;
                markActive(btn.dataset.time);
            });
        });
    }

    async function loadSlots(){
        const date = dateEl.value;
        await syncDoctorsByDate(date);
        const doctorId = doctorEl?.value || '';

        if (doctorRequired && doctorEl && !doctorId){
            setLoading('Select an available doctor first...');
            return;
        }
        if(!date){
            setLoading('Select date first...');
            return;
        }

        setLoading('Loading available times...');

        const url = new URL(`/book/${serviceId}/slots`, window.location.origin);
        url.searchParams.set('date', date);
        if (doctorRequired) url.searchParams.set('doctor_id', doctorId);

        let res;
        try {
            res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        } catch (e) {
            setLoading('Unable to load slots');
            return;
        }

        if(!res.ok){
            setLoading('Unable to load slots');
            return;
        }

        const data = await res.json();
        const slots = data.slots || [];

        if(!slots.length){
            timeEl.innerHTML = '<option value="">No available slots</option>';
            const doctorUnavailable = Boolean(data?.meta?.doctor_unavailable);
            const doctorUnavailableReason = data?.meta?.doctor_unavailable_reason || 'Unavailable on this date.';
            if (helpEl) {
                helpEl.textContent = doctorUnavailable
                    ? `Selected dentist is unavailable: ${doctorUnavailableReason}`
                    : 'No slots available.';
            }
            if (gridEl) gridEl.innerHTML = '';
            return;
        }

        timeEl.innerHTML = '<option value="">Select time...</option>' + slots.map(t => {
            const selected = (oldTime && oldTime === t) ? 'selected' : '';
            return `<option value="${t}" ${selected}>${fmt12h(t)}</option>`;
        }).join('');

        if (oldTime && slots.includes(oldTime)) {
            timeEl.value = oldTime;
        }

        renderGrid(slots);
        if (helpEl) helpEl.textContent = `${slots.length} slot(s) available.`;
    }

    dateEl.addEventListener('change', loadSlots);
    if (doctorEl) doctorEl.addEventListener('change', loadSlots);

    if (dateEl.value && (!doctorRequired || (doctorEl && doctorEl.value))) {
        loadSlots();
    }
})();
</script>
@endpush
@endif
@endsection
