@extends('layouts.public')

@section('title', 'Messenger Booking')

@php
    // Raw values coming from ManyChat query params
    $rawService = $service ?? '';
    $rawTime    = $time ?? '';

    $looksUnresolved = function ($v) {
        $v = (string) $v;
        return $v === '' || str_contains($v, '{{') || str_contains($v, 'cuf_');
    };

    $serviceInvalid = $looksUnresolved($rawService);
    $timeInvalid    = $looksUnresolved($rawTime);

    // Display-only (nice text)
    $displayService = $serviceInvalid ? '—' : $rawService;
    $displayTime    = $timeInvalid ? '—' : $rawTime;

    // Time options (value => label)
    $timeOptions = [
        '09:00:00' => '9:00 AM',
        '10:00:00' => '10:00 AM',
        '11:00:00' => '11:00 AM',
        '12:00:00' => '12:00 PM',
        '13:00:00' => '1:00 PM',
        '14:00:00' => '2:00 PM',
        '15:00:00' => '3:00 PM',
        '16:00:00' => '4:00 PM',
        '17:00:00' => '5:00 PM',
    ];

    // ✅ Use controller prefill values if old() doesn't exist
    $selectedServiceId = old('service_id', $prefillServiceId ?? null);
    $selectedTime      = old('appointment_time', $prefillTime ?? null);
@endphp

@section('content')
<div class="container py-4" style="max-width: 560px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <h4 class="mb-2">Finalize your appointment</h4>
            <p class="text-muted mb-3">Choose your preferred date and enter your details.</p>

            {{-- Optional Google sign-in prompt (NOT required) --}}
            @guest
                <div class="alert alert-info rounded-3">
                    <div class="fw-semibold mb-1">Tip:</div>
                    <div class="small mb-2">Sign in with Google so we can link your request to your account.</div>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('google.redirect') }}">
                        Continue with Google
                    </a>
                    <div class="small text-muted mt-2">
                        (You can still submit without signing in.)
                    </div>
                </div>
            @endguest

            <div class="bg-light rounded-3 p-3 mb-3">
                <div><strong>Service (from Messenger):</strong> {{ $displayService }}</div>
                <div><strong>Time (from Messenger):</strong> {{ $displayTime }}</div>

                @if($serviceInvalid || $timeInvalid)
                    <div class="text-danger small mt-2">
                        It looks like Messenger didn’t pass your selection (or it’s still in preview mode).
                        Please choose below.
                    </div>
                @else
                    <div class="text-success small mt-2">
                        We detected your selection — please confirm below.
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('messenger.book.store') }}">
                @csrf

                {{-- Debug/reference only (not used for saving service_id anymore) --}}
                <input type="hidden" name="service_text" value="{{ old('service_text', $serviceInvalid ? '' : $rawService) }}">
                <input type="hidden" name="time_text" value="{{ old('time_text', $timeInvalid ? '' : $rawTime) }}">

                {{-- ✅ REQUIRED: real service_id --}}
                <div class="mb-3">
                    <label class="form-label">Service</label>
                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                        <option value="" disabled {{ $selectedServiceId ? '' : 'selected' }}>Select a service</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" @selected((string)$selectedServiceId === (string)$s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ✅ REQUIRED: appointment_time --}}
                <div class="mb-3">
                    <label class="form-label">Preferred time</label>
                    <select name="appointment_time" class="form-select @error('appointment_time') is-invalid @enderror" required>
                        <option value="" disabled {{ $selectedTime ? '' : 'selected' }}>Select time</option>
                        @foreach($timeOptions as $val => $label)
                            <option value="{{ $val }}" @selected((string)$selectedTime === (string)$val)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('appointment_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Preferred date</label>
                    <input type="date" name="preferred_date"
                           class="form-control @error('preferred_date') is-invalid @enderror"
                           value="{{ old('preferred_date') }}" required>
                    @error('preferred_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Full name</label>
                    <input type="text" name="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', auth()->user()->name ?? '') }}"
                           placeholder="Juan Dela Cruz" required>
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mobile number</label>
                    <input type="text" name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone') }}"
                           placeholder="09xxxxxxxxx" required>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Any details you want us to know...">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button class="btn btn-primary w-100">
                    Submit Request
                </button>

                <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                    Your request will be marked as <strong>Pending</strong> and will appear in Staff Approvals.
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
