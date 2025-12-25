@extends('layouts.public')
@section('title', 'Book — ' . $service->name)

@section('content')
<section class="section section-soft">
    <div class="container">
        <a href="{{ route('public.services.show', $service->id) }}" class="text-decoration-none fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>

        <div class="row g-4 mt-2">
            <div class="col-lg-6">
                <div class="card card-soft p-4">
                    <h2 class="sec-title mb-2">Book: {{ $service->name }}</h2>
                    <div class="sec-sub">
                        Select a doctor (if required), date, and available time slot. Appointment will be created as <b>Pending</b>.
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="mt-3" method="POST" action="{{ route('public.booking.store', $service->id) }}">
                        @csrf

                        {{-- Doctor selection (only show if doctors exist) --}}
                        @php $doctorRequired = ($doctors->count() > 0); @endphp

                        @if($doctorRequired)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Doctor</label>
                                <select class="form-select" name="doctor_id" id="doctor_id" required>
                                    <option value="">-- Choose Doctor --</option>
                                    @foreach($doctors as $d)
                                        <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>
                                            {{ $d->name ?? ('Doctor #' . $d->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1">Slots will be based on the doctor you choose.</div>
                                @error('doctor_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <input type="hidden" name="doctor_id" id="doctor_id" value="{{ old('doctor_id') }}">
                        @endif

                        {{-- Date + Time --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date</label>
                                <input type="date"
                                       class="form-control"
                                       name="date"
                                       id="date"
                                       value="{{ old('date') }}"
                                       min="{{ now()->toDateString() }}"
                                       required>
                                @error('date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Time</label>
                                <select class="form-select" name="time" id="time" required>
                                    <option value="">
                                        {{ $doctorRequired ? 'Select doctor + date first…' : 'Select date first…' }}
                                    </option>
                                </select>
                                <div class="small text-muted mt-1" id="timeHelp"></div>
                                @error('time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Patient Info --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">First Name</label>
                                <input class="form-control kt-input"
                                       type="text"
                                       name="first_name"
                                       value="{{ old('first_name') }}"
                                       required>
                                @error('first_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Middle Name (optional)</label>
                                <input class="form-control kt-input"
                                       type="text"
                                       name="middle_name"
                                       value="{{ old('middle_name') }}">
                                @error('middle_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Last Name</label>
                                <input class="form-control kt-input"
                                       type="text"
                                       name="last_name"
                                       value="{{ old('last_name') }}"
                                       required>
                                @error('last_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input class="form-control kt-input"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required>
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Number</label>
                                <input class="form-control kt-input"
                                       type="text"
                                       name="contact"
                                       value="{{ old('contact') }}"
                                       required>
                                @error('contact')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Address (optional)</label>
                                <input class="form-control kt-input"
                                       type="text"
                                       name="address"
                                       value="{{ old('address') }}"
                                       placeholder="House no., Street, Barangay, City">
                                @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Message (optional)</label>
                            <textarea class="form-control"
                                      name="message"
                                      rows="3"
                                      placeholder="Any notes for the clinic?">{{ old('message') }}</textarea>
                            @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <button class="btn kt-btn kt-btn-primary text-white mt-4" type="submit">
                            <i class="fa-solid fa-circle-check me-1"></i> Confirm Booking
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="img-tile" style="height:520px;">
                    <img src="{{ asset('assets/img/public/pic7.jpg') }}" alt="Clinic">
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
(function(){
    const serviceId = @json($service->id);
    const dateEl = document.getElementById('date');
    const timeEl = document.getElementById('time');
    const helpEl = document.getElementById('timeHelp');
    const doctorEl = document.getElementById('doctor_id');

    const doctorRequired = @json($doctors->count() > 0);

    function setLoading(msg){
        timeEl.innerHTML = `<option value="">${msg}</option>`;
        helpEl.textContent = '';
    }

    async function loadSlots(){
        const date = dateEl?.value;
        const doctorId = doctorEl?.value || '';

        if (doctorRequired && !doctorId){
            setLoading('Select doctor first…');
            return;
        }
        if(!date){
            setLoading('Select date first…');
            return;
        }

        setLoading('Loading available times…');

        const url = new URL(`/book/${serviceId}/slots`, window.location.origin);
        url.searchParams.set('date', date);
        if (doctorRequired) url.searchParams.set('doctor_id', doctorId);

        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });

        if(!res.ok){
            setLoading('Unable to load slots');
            helpEl.textContent = 'Please try again.';
            return;
        }

        const data = await res.json();
        const slots = data.slots || [];

        if(!slots.length){
            timeEl.innerHTML = `<option value="">No available slots</option>`;
            helpEl.textContent = 'Try another date.';
            return;
        }

        const oldTime = @json(old('time'));

        timeEl.innerHTML = `<option value="">Select time…</option>` + slots.map(t => {
            const selected = (oldTime && oldTime === t) ? 'selected' : '';
            return `<option value="${t}" ${selected}>${t}</option>`;
        }).join('');

        helpEl.textContent = `${slots.length} slot(s) available.`;
    }

    dateEl?.addEventListener('change', loadSlots);
    doctorEl?.addEventListener('change', loadSlots);

    if(dateEl?.value && (!doctorRequired || doctorEl?.value)) loadSlots();
})();
</script>
@endpush
@endsection
