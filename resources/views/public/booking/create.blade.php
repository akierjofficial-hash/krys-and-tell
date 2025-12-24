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
                    <div class="sec-sub">Select a doctor, date, and available time slot. Appointment will be created as <b>Pending</b>.</div>

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

                        @if($doctors->count())
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
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date</label>
                                <input type="date" class="form-control" name="date" id="date"
                                       value="{{ old('date') }}"
                                       min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Time</label>
                                <select class="form-select" name="time" id="time" required>
                                    <option value="">Select doctor + date first…</option>
                                </select>
                                <div class="small text-muted mt-1" id="timeHelp"></div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Contact Number</label>
                                <input type="text" class="form-control" name="contact" value="{{ old('contact') }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label fw-bold">Gender</label>
        <select class="form-select" name="gender" required>
            <option value="">-- Select Gender --</option>
            <option value="Male" @selected(old('gender') === 'Male')>Male</option>
            <option value="Female" @selected(old('gender') === 'Female')>Female</option>
            <option value="Other" @selected(old('gender') === 'Other')>Other</option>
            <option value="Prefer not to say" @selected(old('gender') === 'Prefer not to say')>Prefer not to say</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold">Birthdate</label>
        <input type="date"
               class="form-control"
               name="birthdate"
               value="{{ old('birthdate') }}"
               max="{{ now()->toDateString() }}"
               required>
    </div>
</div>


                        

                        <div class="mt-3">
                            <label class="form-label fw-bold">Message (optional)</label>
                            <textarea class="form-control" name="message" rows="3">{{ old('message') }}</textarea>
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

    function setLoading(msg){
        timeEl.innerHTML = `<option value="">${msg}</option>`;
        helpEl.textContent = '';
    }

    async function loadSlots(){
        const date = dateEl?.value;
        const doctorId = doctorEl?.value || '';

        if(!doctorId){
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
        url.searchParams.set('doctor_id', doctorId);

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

        timeEl.innerHTML = `<option value="">Select time…</option>` + slots.map(t => {
            return `<option value="${t}">${t}</option>`;
        }).join('');

        helpEl.textContent = `${slots.length} slot(s) available.`;
    }

    dateEl?.addEventListener('change', loadSlots);
    doctorEl?.addEventListener('change', loadSlots);

    if(dateEl?.value && doctorEl?.value) loadSlots();
})();
</script>
@endpush
@endsection
