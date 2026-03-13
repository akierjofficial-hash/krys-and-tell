@extends('layouts.guest')

@section('title', 'Staff and Admin Login | Krys and Tell')

@section('content')
<style>
    :root {
        --kt-bronze: #B07848;
        --kt-bronze-light: #C99060;
        --kt-champagne: #D9C4A8;
        --kt-ivory: #F5EFE4;
        --kt-pearl: #FDFCF9;
        --kt-charcoal: #2A2520;
        --kt-soft: #8A837C;
        --kt-white: #FFFFFF;
        --kt-danger: #C63D3D;
    }

    .kt-auth {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
        background:
            radial-gradient(1100px 520px at 0% 0%, rgba(176, 120, 72, 0.20), transparent 56%),
            radial-gradient(920px 500px at 100% 10%, rgba(217, 196, 168, 0.44), transparent 58%),
            linear-gradient(180deg, var(--kt-pearl), #fff);
        padding: 24px;
    }

    .kt-auth::before {
        content: '';
        position: absolute;
        inset: -80px;
        background: radial-gradient(circle, rgba(42, 37, 32, 0.04) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }

    .kt-auth__orb {
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        filter: blur(2px);
    }

    .kt-auth__orb--a {
        width: 420px;
        height: 420px;
        left: -110px;
        top: -110px;
        background: radial-gradient(circle at 40% 40%, rgba(176, 120, 72, 0.26), transparent 62%);
    }

    .kt-auth__orb--b {
        width: 560px;
        height: 560px;
        right: -220px;
        top: -240px;
        background: radial-gradient(circle at 35% 35%, rgba(217, 196, 168, 0.65), transparent 58%);
    }

    .kt-auth__shell {
        width: 100%;
        max-width: 1100px;
        display: grid;
        grid-template-columns: 0.95fr 1.05fr;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid rgba(176, 120, 72, 0.16);
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 30px 80px rgba(42, 37, 32, 0.16);
        position: relative;
        z-index: 1;
        backdrop-filter: blur(8px);
    }

    .kt-auth__aside {
        padding: 40px 34px;
        background:
            linear-gradient(165deg, rgba(42, 37, 32, 0.95) 0%, rgba(42, 37, 32, 0.88) 48%, rgba(176, 120, 72, 0.80) 100%);
        color: var(--kt-white);
        display: grid;
        align-content: space-between;
        gap: 24px;
    }

    .kt-auth__brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .kt-auth__logo {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        object-fit: cover;
        border: 1px solid rgba(255, 255, 255, 0.32);
    }

    .kt-auth__brand-name {
        margin: 0;
        font-size: 14px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .kt-auth__brand-sub {
        margin: 2px 0 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.72);
    }

    .kt-auth__aside h1 {
        margin: 0;
        font-size: clamp(34px, 3vw, 46px);
        line-height: 1.06;
        letter-spacing: -0.02em;
    }

    .kt-auth__aside p {
        margin: 10px 0 0;
        font-size: 14px;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.78);
    }

    .kt-auth__chips {
        display: grid;
        gap: 10px;
    }

    .kt-auth__chip {
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 14px;
        padding: 12px 14px;
        background: rgba(255, 255, 255, 0.10);
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.92);
    }

    .kt-auth__panel {
        padding: 36px 34px;
        display: grid;
        align-content: center;
    }

    .kt-auth__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--kt-bronze);
        margin-bottom: 8px;
    }

    .kt-auth__title {
        margin: 0;
        font-size: clamp(28px, 2.7vw, 38px);
        letter-spacing: -0.02em;
        color: var(--kt-charcoal);
    }

    .kt-auth__subtitle {
        margin: 8px 0 18px;
        font-size: 14px;
        line-height: 1.7;
        color: var(--kt-soft);
    }

    .kt-auth__notice,
    .kt-auth__error {
        border-radius: 14px;
        padding: 11px 13px;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 14px;
    }

    .kt-auth__notice {
        border: 1px solid rgba(176, 120, 72, 0.28);
        background: rgba(176, 120, 72, 0.10);
        color: var(--kt-charcoal);
    }

    .kt-auth__notice a {
        color: var(--kt-bronze);
        font-weight: 700;
        text-decoration: none;
    }

    .kt-auth__notice a:hover {
        text-decoration: underline;
    }

    .kt-auth__error {
        border: 1px solid rgba(198, 61, 61, 0.30);
        background: rgba(198, 61, 61, 0.10);
        color: var(--kt-danger);
        font-weight: 600;
    }

    .kt-auth__field {
        margin-bottom: 12px;
    }

    .kt-auth__label {
        display: block;
        margin-bottom: 7px;
        font-size: 11px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--kt-soft);
        font-weight: 700;
    }

    .kt-auth__input-wrap {
        position: relative;
    }

    .kt-auth__icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(42, 37, 32, 0.48);
        pointer-events: none;
    }

    .kt-auth__input {
        width: 100%;
        border: 1px solid rgba(176, 120, 72, 0.18);
        border-radius: 14px;
        padding: 13px 42px 13px 40px;
        background: var(--kt-ivory);
        color: var(--kt-charcoal);
        outline: none;
        font-size: 14px;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .kt-auth__input:focus {
        border-color: rgba(176, 120, 72, 0.52);
        box-shadow: 0 0 0 4px rgba(176, 120, 72, 0.12);
        background: var(--kt-white);
    }

    .kt-auth__toggle {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: rgba(42, 37, 32, 0.58);
        cursor: pointer;
    }

    .kt-auth__toggle:hover {
        color: var(--kt-charcoal);
        background: rgba(176, 120, 72, 0.12);
    }

    .kt-auth__row {
        margin: 12px 0 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .kt-auth__check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--kt-charcoal);
    }

    .kt-auth__check input {
        width: 16px;
        height: 16px;
        accent-color: var(--kt-bronze);
    }

    .kt-auth__back {
        font-size: 13px;
        color: var(--kt-soft);
        text-decoration: none;
        font-weight: 600;
    }

    .kt-auth__back:hover {
        color: var(--kt-charcoal);
        text-decoration: underline;
    }

    .kt-auth__submit {
        width: 100%;
        border: 0;
        border-radius: 999px;
        padding: 14px 18px;
        background: linear-gradient(135deg, var(--kt-charcoal), var(--kt-bronze));
        color: var(--kt-white);
        font-size: 13px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .kt-auth__submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 34px rgba(176, 120, 72, 0.34);
    }

    .kt-auth__foot {
        margin-top: 14px;
        text-align: center;
        font-size: 12px;
        color: var(--kt-soft);
        line-height: 1.6;
    }

    .kt-auth__foot a {
        color: var(--kt-bronze);
        font-weight: 700;
        text-decoration: none;
    }

    .kt-auth__foot a:hover {
        text-decoration: underline;
    }

    @media (max-width: 980px) {
        .kt-auth {
            padding: 16px;
        }

        .kt-auth__shell {
            grid-template-columns: 1fr;
            max-width: 640px;
        }

        .kt-auth__aside {
            padding: 28px 24px;
        }

        .kt-auth__panel {
            padding: 28px 24px;
        }
    }

    @media (max-width: 520px) {
        .kt-auth__row {
            flex-direction: column;
            align-items: flex-start;
        }

        .kt-auth__title {
            font-size: 30px;
        }
    }
</style>

<div class="kt-auth">
    <span class="kt-auth__orb kt-auth__orb--a" aria-hidden="true"></span>
    <span class="kt-auth__orb kt-auth__orb--b" aria-hidden="true"></span>

    <div class="kt-auth__shell">
        <aside class="kt-auth__aside">
            <div>
                <div class="kt-auth__brand">
                    <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys and Tell" class="kt-auth__logo">
                    <div>
                        <p class="kt-auth__brand-name">Krys and Tell</p>
                        <p class="kt-auth__brand-sub">Clinic Management System</p>
                    </div>
                </div>

                <h1>Admin and Staff Portal</h1>
                <p>
                    Manage clinic operations, schedules, and patient workflows from one secure login.
                </p>
            </div>

            <div class="kt-auth__chips">
                <div class="kt-auth__chip">Admin dashboard access</div>
                <div class="kt-auth__chip">Staff workflow tools</div>
                <div class="kt-auth__chip">Role-based secure portal</div>
            </div>
        </aside>

        <section class="kt-auth__panel">
            <div class="kt-auth__eyebrow">Authorized Access</div>
            <h2 class="kt-auth__title">Sign In</h2>
            <p class="kt-auth__subtitle">Use your admin or staff account to continue.</p>

            <div class="kt-auth__notice">
                Patients should use the <a href="{{ route('userlogin') }}">User Login page</a> instead.
            </div>

            @if ($errors->any())
                <div class="kt-auth__error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="kt-auth__field">
                    <label class="kt-auth__label" for="email">Email</label>
                    <div class="kt-auth__input-wrap">
                        <span class="kt-auth__icon" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 8l-10 6L2 8"/>
                            </svg>
                        </span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            placeholder="staff@clinic.com"
                            class="kt-auth__input"
                            required
                        >
                    </div>
                </div>

                <div class="kt-auth__field">
                    <label class="kt-auth__label" for="password">Password</label>
                    <div class="kt-auth__input-wrap">
                        <span class="kt-auth__icon" aria-hidden="true">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 11V7a5 5 0 00-10 0v4"/>
                                <rect x="3" y="11" width="18" height="10" rx="2" ry="2" stroke-width="2"/>
                            </svg>
                        </span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="kt-auth__input"
                            required
                        >
                        <button type="button" class="kt-auth__toggle" id="ktTogglePassword" aria-label="Toggle password visibility">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="kt-auth__row">
                    <label class="kt-auth__check">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>

                    <a class="kt-auth__back" href="{{ route('public.home') }}">Back to site</a>
                </div>

                <button class="kt-auth__submit" type="submit">Sign In</button>

                <p class="kt-auth__foot">
                    Need a patient account? <a href="{{ route('userlogin') }}">Go to User Login</a><br>
                    Copyright {{ date('Y') }} Krys and Tell Dental Center
                </p>
            </form>
        </section>
    </div>
</div>

<script>
(function () {
    'use strict';

    var passwordInput = document.getElementById('password');
    var toggle = document.getElementById('ktTogglePassword');

    if (!passwordInput || !toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        var showing = passwordInput.type === 'text';
        passwordInput.type = showing ? 'password' : 'text';
        toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    });
})();
</script>
@endsection
