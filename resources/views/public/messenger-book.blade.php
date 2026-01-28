@extends('layouts.public')

@section('title', 'Messenger Booking')

@section('content')
@php
    // Support different variable names + query params
    $serviceVal = old('service_name', $service ?? request('service') ?? '');
    $timeVal    = old('preferred_time', $time ?? ($preferredTime ?? request('time')) ?? '');
@endphp

<div class="container py-4" style="max-width: 560px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h4 class="mb-2">Finalize your appointment</h4>
            <p class="text-muted mb-3">Please choose your preferred date and enter your details.</p>

            <div class="bg-light rounded-3 p-3 mb-3">
                <div><strong>Service:</strong> {{ $serviceVal !== '' ? $serviceVal : '—' }}</div>
                <div><strong>Time:</strong> {{ $timeVal !== '' ? $timeVal : '—' }}</div>
            </div>

            {{-- Show validation errors for hidden fields (if ever) --}}
            @if ($errors->has('service_name') || $errors->has('preferred_time'))
                <div class="alert alert-danger py-2">
                    @error('service_name') <div>{{ $message }}</div> @enderror
                    @error('preferred_time') <div>{{ $message }}</div> @enderror
                </div>
            @endif

            <form method="POST" action="{{ route('messenger.book.store') }}">
                @csrf

                {{-- Hidden values passed from ManyChat --}}
                <input type="hidden" name="service_name" value="{{ $serviceVal }}">
                <input type="hidden" name="preferred_time" value="{{ $timeVal }}">

                <div class="mb-3">
                    <label class="form-label">Preferred date</label>
                    <input
                        type="date"
                        name="preferred_date"
                        class="form-control @error('preferred_date') is-invalid @enderror"
                        value="{{ old('preferred_date') }}"
                        required
                    >
                    @error('preferred_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Full name</label>
                    <input
                        type="text"
                        name="full_name"
                        class="form-control @error('full_name') is-invalid @enderror"
                        value="{{ old('full_name') }}"
                        placeholder="Juan Dela Cruz"
                        required
                    >
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mobile number</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        placeholder="09xxxxxxxxx"
                        required
                    >
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea
                        name="notes"
                        rows="3"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Any details you want us to know..."
                    >{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button class="btn btn-primary w-100">
                    Submit Request
                </button>

                <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                    Your request will be marked as <strong>Pending</strong>. We’ll confirm via Messenger or phone.
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
