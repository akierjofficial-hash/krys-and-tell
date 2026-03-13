@extends('layouts.kt-public')

@section('title', 'Contact - Krys & Tell Dental Center')

@section('content')
@php
    $mapRelative = 'images/map.png';
    $mapExists = file_exists(public_path($mapRelative));

    $user = auth()->user();
    $autoName = trim(old('name', $user->name ?? ''));
    $autoEmail = trim(old('email', $user->email ?? ''));
    $isLoggedIn = auth()->check();
@endphp

<section class="kt-contact-page">
    <div class="kt-page-shell">
        <div class="kt-contact-page__head kt-reveal">
            <div class="kt-label">Contact Us</div>
            <h1 class="kt-section-title">We Are Here To Help<br><em>Reach Out Anytime</em></h1>
            <p class="kt-section-body">
                Send us a message, ask about services, or request guidance before booking.
                We will get back to you as soon as possible.
            </p>
        </div>

        <div class="kt-contact-page__layout">
            <aside class="kt-contact-page__info kt-reveal-left">
                <h2>Clinic Information</h2>

                <div class="kt-contact-page__info-list">
                    <article class="kt-contact-page__info-item">
                        <strong>Address</strong>
                        <span>CT Building, Jose Romero Road, Bagacay, Dumaguete City, Philippines, 6200</span>
                    </article>

                    <article class="kt-contact-page__info-item">
                        <strong>Phone</strong>
                        <span>0977 244 3595</span>
                    </article>

                    <article class="kt-contact-page__info-item">
                        <strong>Email</strong>
                        <span>krysandt@gmail.com</span>
                    </article>

                    <article class="kt-contact-page__info-item">
                        <strong>Hours</strong>
                        <span>Mon-Sat: 9:00 AM - 6:00 PM</span>
                    </article>
                </div>

                <div class="kt-contact-page__map">
                    @if($mapExists)
                        <img src="{{ asset($mapRelative) }}" alt="" loading="lazy">
                    @else
                        <div class="kt-contact-page__map-missing">
                            <strong>Map image not found.</strong>
                            <span>Please add `public/images/map.png`.</span>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="kt-contact-page__form-wrap kt-reveal-right">
                @if(session('contact_success'))
                    <div class="kt-form-success">{{ session('contact_success') }}</div>
                @endif

                @if($errors->any())
                    <div class="kt-form-error-banner">
                        <strong>Please fix the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @guest
                    <div class="kt-form-note">
                        Tip: Sign in with Google so your name and email autofill.
                    </div>
                @endguest

                <div class="kt-contact-page__form-card">
                    <h2>Send A Message</h2>
                    <p>We will reply to your provided email address.</p>

                    <form method="POST" action="{{ route('public.contact.store') }}" class="kt-contact-page__form">
                        @csrf

                        <div class="kt-form-row">
                            <div class="kt-form-group">
                                <label class="kt-form-label" for="kt_contact_name">Name</label>
                                @if($isLoggedIn)
                                    <input id="kt_contact_name" type="text" class="kt-form-input" value="{{ $autoName }}" readonly aria-readonly="true">
                                    <input type="hidden" name="name" value="{{ $autoName }}">
                                @else
                                    <input id="kt_contact_name" type="text" name="name" class="kt-form-input @error('name') kt-input--error @enderror" value="{{ old('name') }}" placeholder="Your name" required>
                                    @error('name')<span class="kt-form-error">{{ $message }}</span>@enderror
                                @endif
                            </div>

                            <div class="kt-form-group">
                                <label class="kt-form-label" for="kt_contact_email">Email</label>
                                @if($isLoggedIn)
                                    <input id="kt_contact_email" type="email" class="kt-form-input" value="{{ $autoEmail }}" readonly aria-readonly="true">
                                    <input type="hidden" name="email" value="{{ $autoEmail }}">
                                @else
                                    <input id="kt_contact_email" type="email" name="email" class="kt-form-input @error('email') kt-input--error @enderror" value="{{ old('email') }}" placeholder="you@email.com" required>
                                    @error('email')<span class="kt-form-error">{{ $message }}</span>@enderror
                                @endif
                            </div>
                        </div>

                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_contact_message">Message</label>
                            <textarea id="kt_contact_message" name="message" rows="6" class="kt-form-input @error('message') kt-input--error @enderror" placeholder="How can we help?" required>{{ old('message') }}</textarea>
                            @error('message')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="kt-contact-page__form-actions">
                            <button type="submit" class="kt-form-submit"><span>Send Message ></span></button>
                            <a href="{{ route('public.services.index') }}" class="kt-btn-ghost">Book Instead ></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
