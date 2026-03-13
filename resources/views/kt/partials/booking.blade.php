@php
    $ktBookingServices = collect($services ?? [])->values()->map(function ($service) {
        if (is_array($service)) {
            $duration = $service['duration_minutes'] ?? null;
            $isWalkIn = ($duration === null || $duration === '' || (is_numeric($duration) && (int) $duration > 0 && (int) $duration <= 5));

            return [
                'id' => $service['id'] ?? null,
                'name' => $service['name'] ?? 'Service',
                'walk_in' => $isWalkIn,
            ];
        }

        $duration = $service->duration_minutes ?? null;
        $isWalkIn = ($duration === null || $duration === '' || (is_numeric($duration) && (int) $duration > 0 && (int) $duration <= 5));

        return [
            'id' => $service->id ?? null,
            'name' => $service->name ?? 'Service',
            'walk_in' => $isWalkIn,
        ];
    })->filter(fn ($service) => !empty($service['id']))->values();

    $selectedServiceId = old('service_id', $ktBookingServices->first()['id'] ?? '');
    $formAction = !empty($selectedServiceId)
        ? url('/book/' . $selectedServiceId)
        : route('public.services.index');

    $birthdateValue = old('birthdate');
    if (!$birthdateValue && !empty(optional(auth()->user())->birthdate)) {
        try {
            $birthdateValue = \Carbon\Carbon::parse(auth()->user()->birthdate)->toDateString();
        } catch (\Throwable $e) {
            $birthdateValue = '';
        }
    }
@endphp

<section class="kt-booking" id="booking">
    <div class="kt-booking__inner">
        <div class="kt-booking__left kt-reveal-left">
            <div class="kt-label">Get Started</div>
            <h2 class="kt-section-title">Book Your<br><em>Appointment</em></h2>
            <p class="kt-section-body">
                Take the first step toward your healthiest, most confident smile.
            </p>

            <div class="kt-booking__clinic-photo">
                <img src="{{ asset('images/pic2.jpg') }}" alt="" class="kt-booking__clinic-img" loading="lazy">
            </div>

            <div class="kt-booking__map">
                <img src="{{ asset('images/map.png') }}" alt="" class="kt-booking__map-img" loading="lazy">
            </div>

            <div class="kt-info-list">
                <div class="kt-info-item">
                    <div class="kt-info-item__icon">Hours</div>
                    <div class="kt-info-item__text">
                        <strong>Opening Hours</strong>
                        <span>Mon-Sat: 9:00 AM to 6:00 PM</span>
                    </div>
                </div>
                <div class="kt-info-item">
                    <div class="kt-info-item__icon">Phone</div>
                    <div class="kt-info-item__text">
                        <strong>Contact Number</strong>
                        <span>0977 244 3595</span>
                    </div>
                </div>
                <div class="kt-info-item">
                    <div class="kt-info-item__icon">Email</div>
                    <div class="kt-info-item__text">
                        <strong>Email</strong>
                        <span>krysandt@gmail.com</span>
                    </div>
                </div>
                <div class="kt-info-item">
                    <div class="kt-info-item__icon">Visit</div>
                    <div class="kt-info-item__text">
                        <strong>Location</strong>
                        <span>CT Building, Jose Romero Road, Bagacay, Dumaguete City</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-booking__right kt-reveal-right">
            @if(session('success'))
                <div class="kt-form-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="kt-form-error-banner">
                    Please check the highlighted fields and try again.
                </div>
            @endif

            @guest
                <div class="kt-form-note">
                    Please sign in before submitting a booking request.
                    <a href="{{ route('userlogin', ['redirect' => url()->current() . '#booking']) }}">Sign in</a>
                </div>
            @endguest

            <div class="kt-booking__form-card">
                <h3 class="kt-form-title">Request an Appointment</h3>
                <p class="kt-form-sub">We will confirm your preferred schedule after review.</p>

                <form action="{{ $formAction }}" method="POST" id="ktBookingForm" data-book-base="{{ url('/book') }}">
                    @csrf

                    <div class="kt-form-group">
                        <label class="kt-form-label" for="kt_service_id">Service Needed</label>
                        <select name="service_id" id="kt_service_id" class="kt-form-input @error('service_id') kt-input--error @enderror" required>
                            <option value="" disabled {{ $selectedServiceId ? '' : 'selected' }}>Select a service</option>
                            @foreach($ktBookingServices as $service)
                                <option value="{{ $service['id'] }}"
                                        data-walkin="{{ $service['walk_in'] ? '1' : '0' }}"
                                        {{ (string) $selectedServiceId === (string) $service['id'] ? 'selected' : '' }}>
                                    {{ $service['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="kt-form-row">
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_date">Preferred Date</label>
                            <input type="date" id="kt_date" name="date"
                                   class="kt-form-input @error('date') kt-input--error @enderror"
                                   value="{{ old('date') }}"
                                   data-old-time="{{ old('time') }}"
                                   data-old-doctor-id="{{ old('doctor_id') }}"
                                   min="{{ now()->toDateString() }}" required>
                            @error('date')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_doctor_id">Preferred Dentist</label>
                            <select name="doctor_id" id="kt_doctor_id" class="kt-form-input @error('doctor_id') kt-input--error @enderror">
                                <option value="">Select dentist</option>
                            </select>
                            @error('doctor_id')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="kt-form-group">
                        <label class="kt-form-label" for="kt_time">Preferred Time</label>
                        <select name="time" id="kt_time" class="kt-form-input @error('time') kt-input--error @enderror">
                            <option value="">Select date, service, and dentist first</option>
                        </select>
                        <div class="kt-form-help" id="kt_time_help">Slots are loaded based on your date and dentist.</div>
                        @error('time')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <input type="hidden" name="request_walkin" id="kt_request_walkin" value="{{ old('request_walkin', 0) ? '1' : '0' }}">

                    <div class="kt-form-row">
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_full_name">Full Name</label>
                            <input type="text" id="kt_full_name" name="full_name"
                                   class="kt-form-input @error('full_name') kt-input--error @enderror"
                                   value="{{ old('full_name', auth()->user()->name ?? '') }}" placeholder="Your full name" required>
                            @error('full_name')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_email">Email</label>
                            <input type="email" id="kt_email" name="email"
                                   class="kt-form-input @error('email') kt-input--error @enderror"
                                   value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="your@email.com" required>
                            @error('email')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="kt-form-row">
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_contact">Contact Number</label>
                            <input type="tel" id="kt_contact" name="contact"
                                   class="kt-form-input @error('contact') kt-input--error @enderror"
                                   value="{{ old('contact', auth()->user()->phone_number ?? '') }}" placeholder="09xx xxx xxxx" required>
                            @error('contact')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_birthdate">Birthdate</label>
                            <input type="date" id="kt_birthdate" name="birthdate"
                                   class="kt-form-input @error('birthdate') kt-input--error @enderror"
                                   value="{{ $birthdateValue }}"
                                   max="{{ now()->subDay()->toDateString() }}" required>
                            @error('birthdate')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="kt-form-group">
                        <label class="kt-form-label" for="kt_address">Address</label>
                        <input type="text" id="kt_address" name="address"
                               class="kt-form-input @error('address') kt-input--error @enderror"
                               value="{{ old('address', auth()->user()->address ?? '') }}" placeholder="Complete address" required>
                        @error('address')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="kt-form-group">
                        <label class="kt-form-label" for="kt_message">Notes (optional)</label>
                        <textarea name="message" id="kt_message" class="kt-form-input @error('message') kt-input--error @enderror" rows="3" placeholder="Any concerns or special requirements...">{{ old('message') }}</textarea>
                        @error('message')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="kt-form-submit">
                        <span>Request Appointment ></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
