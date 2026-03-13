<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ request()->routeIs('login') ? 'Staff Portal Sign In | Krys & Tell' : 'Sign In | Krys & Tell' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@200;300;400;500;600&family=DM+Sans:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kt-login.css') }}?v={{ @filemtime(public_path('css/kt-login.css')) }}">
</head>
@php
    $isStaffPortal = request()->routeIs('login');
    $redirect = request('redirect');
    $backUrl = $redirect ?: route('public.home');
@endphp
<body class="kt-login-page {{ $isStaffPortal ? 'kt-login-page--staff' : '' }}">
<div class="kt-login-wrap">
    <section class="kt-login-left">
        <div class="kt-login-left__glow"></div>
        <div class="kt-login-left__deco" aria-hidden="true">K</div>
        <div class="kt-login-left__vline" aria-hidden="true"></div>

        <div class="kt-login-left__top">
            <a href="{{ route('public.home') }}" class="kt-login-brand" aria-label="Krys and Tell Dental Center">
                <div class="kt-login-brand__logo">
                    <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="" width="28" height="28" class="kt-login-brand__img" loading="eager">
                </div>
                <div>
                    <div class="kt-login-brand__name">KRYS &amp; TELL</div>
                    <div class="kt-login-brand__sub">Dental Center</div>
                </div>
            </a>

            @if($isStaffPortal)
                <h1 class="kt-login-left__headline">Restricted portal<br><em>authorized staff only.</em></h1>
                <p class="kt-login-left__sub">
                    This login grants access to internal clinic operations, approvals, scheduling, and protected patient data.
                </p>
            @else
                <h1 class="kt-login-left__headline">Your smile,<br><em>your account.</em></h1>
                <p class="kt-login-left__sub">
                    Access your appointments, treatment history, and manage your profile in one elegant portal.
                </p>
            @endif
        </div>

        <div class="kt-login-left__bottom">
            <ul class="kt-login-features">
                @if($isStaffPortal)
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <rect x="3" y="11" width="18" height="10" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Role-Gated Access</strong>
                            <span>Admin and staff accounts only</span>
                        </div>
                    </li>
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M12 2l8 4v6c0 5-3.4 9.7-8 11-4.6-1.3-8-6-8-11V6l8-4z"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Protected Data</strong>
                            <span>Patient and billing records are secured</span>
                        </div>
                    </li>
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M3 12h18"/><path d="M12 3v18"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Operational Control</strong>
                            <span>Scheduling, approvals, and clinic dashboards</span>
                        </div>
                    </li>
                @else
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Manage Appointments</strong>
                            <span>Book, reschedule, and track visits</span>
                        </div>
                    </li>
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Treatment Records</strong>
                            <span>Review your complete dental history</span>
                        </div>
                    </li>
                    <li class="kt-login-feature">
                        <div class="kt-login-feature__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="kt-login-feature__text">
                            <strong>Your Profile</strong>
                            <span>Update your details and preferences</span>
                        </div>
                    </li>
                @endif
            </ul>

            <blockquote class="kt-login-quote">
                @if($isStaffPortal)
                    <p>"Unauthorized access is monitored and subject to policy enforcement."</p>
                    <cite>Internal Security Notice</cite>
                @else
                    <p>"The portal made rescheduling effortless. Everything is clean and easy."</p>
                    <cite>Sarah M. | Patient since 2021</cite>
                @endif
            </blockquote>
        </div>
    </section>

    <section class="kt-login-right">
        <div class="kt-login-card">
            <a href="{{ $isStaffPortal ? route('public.home') : $backUrl }}" class="kt-back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
                </svg>
                Back to site
            </a>

            <div class="kt-login-eyebrow">
                <span class="kt-login-eyebrow__line"></span>
                <span class="kt-login-eyebrow__text">{{ $isStaffPortal ? 'Staff and Admin Portal' : 'Patient Portal' }}</span>
            </div>
            @if($isStaffPortal)
                <h2 class="kt-login-title">Sign In <em>Securely.</em></h2>
            @else
                <h2 class="kt-login-title">Welcome <em>Back.</em></h2>
            @endif
            <p class="kt-login-sub">{{ $isStaffPortal ? 'Use your assigned staff credentials to continue.' : 'Sign in to access your bookings, profile, and updates.' }}</p>

            @if (session('status'))
                <div class="kt-form-alert kt-form-alert--success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="kt-form-alert kt-form-alert--error">{{ session('error') }}</div>
            @elseif ($errors->any())
                <div class="kt-form-alert kt-form-alert--error">{{ $errors->first() }}</div>
            @endif

            @if($isStaffPortal)
                <div class="kt-form-alert kt-form-alert--warning">
                    Patient accounts must sign in via the <a href="{{ route('userlogin') }}">User Portal</a>.
                </div>
            @endif

            <form class="kt-form" method="POST" action="{{ $isStaffPortal ? route('login.submit') : route('userlogin.submit', ['redirect' => request('redirect')]) }}">
                @csrf

                <div class="kt-form-group">
                    <label for="email" class="kt-form-label">Email Address</label>
                    <div class="kt-input-wrap">
                        <svg class="kt-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="kt-input @error('email') kt-input--error @enderror"
                            value="{{ old('email') }}"
                            placeholder="{{ $isStaffPortal ? 'staff@clinic.com' : 'example@gmail.com' }}"
                            autocomplete="email"
                            autofocus
                            required>
                    </div>
                    @error('email')
                        <span class="kt-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="kt-form-group">
                    <label for="password" class="kt-form-label">Password</label>
                    <div class="kt-input-wrap">
                        <svg class="kt-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="kt-input @error('password') kt-input--error @enderror"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required>
                        <button type="button" class="kt-input-eye" id="ktEyeToggle" aria-label="Toggle password visibility">
                            <svg id="ktEyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="kt-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="kt-form-meta">
                    <label class="kt-checkbox-label">
                        <input type="checkbox" name="remember" class="kt-checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                        <span class="kt-checkbox-custom"></span>
                        <span class="kt-checkbox-text">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="kt-forgot-link">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="kt-btn-submit">
                    <span>Sign in</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            @unless($isStaffPortal)
                <div class="kt-or-divider"><span>or continue with</span></div>

                <a href="{{ route('google.redirect', ['redirect' => request('redirect')]) }}" class="kt-btn-google">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                        <path class="kt-google-blue" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path class="kt-google-green" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path class="kt-google-yellow" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path class="kt-google-red" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </a>
            @endunless

            <div class="kt-login-footer">
                @if($isStaffPortal)
                    <p>Not a staff member? <a href="{{ route('userlogin') }}">Go to Patient Login</a></p>
                @else
                    @if (Route::has('register'))
                        <p>Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
                    @endif
                    <p><a href="{{ route('public.home') }}">Back to site</a></p>
                @endif
                <p class="kt-login-copy">© {{ date('Y') }} Krys &amp; Tell Dental Center</p>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    'use strict';

    var eyeBtn = document.getElementById('ktEyeToggle');
    var eyeIcon = document.getElementById('ktEyeIcon');
    var pwField = document.getElementById('password');

    if (eyeBtn && pwField && eyeIcon) {
        eyeBtn.addEventListener('click', function () {
            var hidden = pwField.type === 'password';
            pwField.type = hidden ? 'text' : 'password';
            eyeIcon.innerHTML = hidden
                ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            eyeBtn.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
        });
    }
})();
</script>
</body>
</html>
