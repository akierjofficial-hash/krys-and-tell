@extends('layouts.kt-public')

@section('title', 'My Profile | Krys & Tell Dental Center')

@section('content')
@php
    $badge = function ($status) {
        $s = strtolower(trim((string) ($status ?? 'pending')));

        if ($s === '' || $s === 'pending') {
            return ['Pending', 'pending'];
        }

        if (in_array($s, ['approved', 'confirmed', 'scheduled', 'accepted'], true)) {
            return ['Upcoming', 'upcoming'];
        }

        if (in_array($s, ['cancelled', 'canceled'], true)) {
            return ['Cancelled', 'cancelled'];
        }

        if (in_array($s, ['declined', 'rejected'], true)) {
            return ['Declined', 'declined'];
        }

        if (in_array($s, ['done', 'completed', 'finished'], true)) {
            return ['Completed', 'completed'];
        }

        if (in_array($s, ['walked_in', 'walked-in'], true)) {
            return ['Walked In', 'completed'];
        }

        return [ucfirst($s), 'upcoming'];
    };

    $fmtDate = function ($date) {
        try {
            return $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : '-';
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $fmtTime = function ($time) {
        try {
            if (!$time) {
                return null;
            }
            return \Carbon\Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            $time = (string) $time;
            return $time !== '' ? $time : null;
        }
    };

    $doctorName = function ($appointment) {
        return $appointment->doctor->name ?? ($appointment->dentist_name ?? '-');
    };

    $hasLocalPassword = (bool) ($user->password_set ?? false);

    $defaultTab = 'upcoming';
    $requestedTab = strtolower((string) request()->query('tab', ''));

    if (in_array($requestedTab, ['upcoming', 'history', 'account'], true)) {
        $defaultTab = $requestedTab;
    }

    foreach (['name', 'email', 'notify_24h', 'notify_1h', 'current_password', 'password', 'password_confirmation'] as $field) {
        if ($errors->has($field)) {
            $defaultTab = 'account';
            break;
        }
    }

    if ($defaultTab === 'upcoming' && request()->has('page')) {
        $defaultTab = 'history';
    }

    $historyPageUrl = function ($page) {
        $query = request()->query();
        $query['tab'] = 'history';
        $query['page'] = $page;

        return url()->current() . '?' . http_build_query($query);
    };
@endphp

<section class="kt-profile-page">
    <div class="kt-page-shell">
        <div class="kt-profile-page__head kt-reveal">
            <div class="kt-label">Patient Portal</div>
            <h1 class="kt-section-title">My <em>Profile</em></h1>
            <p class="kt-section-body">
                Review upcoming appointments, check booking history, and manage your account settings in one place.
            </p>
        </div>

        @if(session('success'))
            <div class="kt-form-success kt-reveal">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="kt-form-error-banner kt-reveal">
                <strong>Please review the highlighted fields below.</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="kt-profile-page__layout">
            <aside class="kt-profile-aside kt-reveal-left">
                <article class="kt-profile-card">
                    <div class="kt-profile-card__identity">
                        <div class="kt-profile-card__avatar">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="kt-profile-card__name">{{ $user->name }}</h2>
                            <p class="kt-profile-card__email">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="kt-profile-card__items">
                        <div class="kt-profile-card__item">
                            <span>Upcoming Appointments</span>
                            <strong>{{ $upcoming->count() }}</strong>
                        </div>
                        <div class="kt-profile-card__item">
                            <span>Total History</span>
                            <strong>{{ method_exists($history, 'total') ? $history->total() : $history->count() }}</strong>
                        </div>
                        <div class="kt-profile-card__item">
                            <span>Password Status</span>
                            <strong>{{ $hasLocalPassword ? 'Set' : 'Not Set' }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('public.services.index') }}" class="kt-btn-primary kt-profile-card__cta">
                        <span>Book Appointment</span>
                    </a>
                </article>
            </aside>

            <div class="kt-profile-main kt-reveal-right" id="ktProfileTabs" data-default-tab="{{ $defaultTab }}">
                <div class="kt-profile-tabs" role="tablist" aria-label="Profile sections">
                    <button
                        type="button"
                        class="kt-profile-tabs__button {{ $defaultTab === 'upcoming' ? 'is-active' : '' }}"
                        data-tab-btn="upcoming"
                        role="tab"
                        aria-controls="kt-tab-upcoming"
                        aria-selected="{{ $defaultTab === 'upcoming' ? 'true' : 'false' }}"
                    >
                        Upcoming
                    </button>
                    <button
                        type="button"
                        class="kt-profile-tabs__button {{ $defaultTab === 'history' ? 'is-active' : '' }}"
                        data-tab-btn="history"
                        role="tab"
                        aria-controls="kt-tab-history"
                        aria-selected="{{ $defaultTab === 'history' ? 'true' : 'false' }}"
                    >
                        History
                    </button>
                    <button
                        type="button"
                        class="kt-profile-tabs__button {{ $defaultTab === 'account' ? 'is-active' : '' }}"
                        data-tab-btn="account"
                        role="tab"
                        aria-controls="kt-tab-account"
                        aria-selected="{{ $defaultTab === 'account' ? 'true' : 'false' }}"
                    >
                        Account
                    </button>
                </div>

                <section
                    id="kt-tab-upcoming"
                    class="kt-profile-panel {{ $defaultTab === 'upcoming' ? 'is-active' : '' }}"
                    data-tab-panel="upcoming"
                    {{ $defaultTab === 'upcoming' ? '' : 'hidden' }}
                >
                    <div class="kt-profile-panel__head">
                        <h2>Upcoming Schedule</h2>
                        <p>Your next appointments appear here. Pending requests can still be edited.</p>
                    </div>

                    <div class="kt-profile-list">
                        @forelse($upcoming as $a)
                            @php
                                [$label, $tone] = $badge($a->status);
                                $dateValue = $fmtDate($a->appointment_date);
                                $timeValue = $fmtTime($a->appointment_time);
                            @endphp

                            <article class="kt-profile-appointment">
                                <div class="kt-profile-appointment__head">
                                    <h3>{{ $a->service->name ?? 'Service' }}</h3>
                                    <span class="kt-status kt-status--{{ $tone }}">{{ $label }}</span>
                                </div>

                                <div class="kt-profile-appointment__meta">
                                    <span>{{ $dateValue }}</span>
                                    <span>{{ $timeValue ?: 'Walk-in' }}</span>
                                </div>

                                <p class="kt-profile-appointment__doctor">Doctor: {{ $doctorName($a) }}</p>

                                @if(strtolower((string) ($a->status ?? '')) === 'pending')
                                    <a class="kt-inline-link" href="{{ route('public.booking.edit', $a->id) }}">Edit booking</a>
                                @endif
                            </article>
                        @empty
                            <div class="kt-profile-empty">
                                <h3>No upcoming appointments</h3>
                                <p>Book a service and your upcoming schedule will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section
                    id="kt-tab-history"
                    class="kt-profile-panel {{ $defaultTab === 'history' ? 'is-active' : '' }}"
                    data-tab-panel="history"
                    {{ $defaultTab === 'history' ? '' : 'hidden' }}
                >
                    <div class="kt-profile-panel__head">
                        <h2>Booking History</h2>
                        <p>All booking requests and updates are listed below.</p>
                    </div>

                    <div class="kt-profile-list" id="profile-history">
                        @forelse($history as $a)
                            @php
                                [$label, $tone] = $badge($a->status);
                                $dateValue = $fmtDate($a->appointment_date);
                                $timeValue = $fmtTime($a->appointment_time);
                            @endphp

                            <article class="kt-profile-appointment">
                                <div class="kt-profile-appointment__head">
                                    <h3>{{ $a->service->name ?? 'Service' }}</h3>
                                    <span class="kt-status kt-status--{{ $tone }}">{{ $label }}</span>
                                </div>

                                <div class="kt-profile-appointment__meta">
                                    <span>{{ $dateValue }}</span>
                                    <span>{{ $timeValue ?: 'Walk-in' }}</span>
                                </div>

                                <p class="kt-profile-appointment__doctor">Doctor: {{ $doctorName($a) }}</p>

                                @if(strtolower((string) ($a->status ?? '')) === 'pending')
                                    <a class="kt-inline-link" href="{{ route('public.booking.edit', $a->id) }}">Edit booking</a>
                                @endif
                            </article>
                        @empty
                            <div class="kt-profile-empty">
                                <h3>No booking history yet</h3>
                                <p>Once you submit a booking request, it will appear here.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($history->lastPage() > 1)
                        @php
                            $startPage = max(1, $history->currentPage() - 2);
                            $endPage = min($history->lastPage(), $history->currentPage() + 2);
                        @endphp

                        <nav class="kt-profile-pagination" aria-label="Booking history pages">
                            @if($history->currentPage() > 1)
                                <a href="{{ $historyPageUrl($history->currentPage() - 1) }}#profile-history" class="kt-profile-pagination__link">Previous</a>
                            @else
                                <span class="kt-profile-pagination__link is-disabled">Previous</span>
                            @endif

                            @for($page = $startPage; $page <= $endPage; $page++)
                                <a
                                    href="{{ $historyPageUrl($page) }}#profile-history"
                                    class="kt-profile-pagination__link {{ $page === $history->currentPage() ? 'is-active' : '' }}"
                                >
                                    {{ $page }}
                                </a>
                            @endfor

                            @if($history->currentPage() < $history->lastPage())
                                <a href="{{ $historyPageUrl($history->currentPage() + 1) }}#profile-history" class="kt-profile-pagination__link">Next</a>
                            @else
                                <span class="kt-profile-pagination__link is-disabled">Next</span>
                            @endif
                        </nav>
                    @endif
                </section>

                <section
                    id="kt-tab-account"
                    class="kt-profile-panel {{ $defaultTab === 'account' ? 'is-active' : '' }}"
                    data-tab-panel="account"
                    {{ $defaultTab === 'account' ? '' : 'hidden' }}
                >
                    <div class="kt-profile-forms">
                        <article class="kt-profile-form-card">
                            <h2>Profile Settings</h2>
                            <p>Update your account details and reminder preferences.</p>

                            <form method="POST" action="{{ route('user.profile.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="kt-form-row">
                                    <div class="kt-form-group">
                                        <label class="kt-form-label" for="kt_profile_name">Name</label>
                                        <input
                                            id="kt_profile_name"
                                            type="text"
                                            name="name"
                                            class="kt-form-input @error('name') kt-input--error @enderror"
                                            value="{{ old('name', $user->name) }}"
                                            required
                                        >
                                        @error('name')<span class="kt-form-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="kt-form-group">
                                        <label class="kt-form-label" for="kt_profile_email">Email</label>
                                        <input
                                            id="kt_profile_email"
                                            type="email"
                                            name="email"
                                            class="kt-form-input @error('email') kt-input--error @enderror"
                                            value="{{ old('email', $user->email) }}"
                                            required
                                        >
                                        @error('email')<span class="kt-form-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="kt-form-group">
                                    <label class="kt-form-label">Reminders</label>

                                    <div class="kt-profile-switches">
                                        <label class="kt-profile-switch" for="kt_notify_24h">
                                            <input
                                                id="kt_notify_24h"
                                                type="checkbox"
                                                name="notify_24h"
                                                value="1"
                                                {{ old('notify_24h', $user->notify_24h) ? 'checked' : '' }}
                                            >
                                            <span class="kt-profile-switch__slider" aria-hidden="true"></span>
                                            <span class="kt-profile-switch__text">24-hour reminder</span>
                                        </label>

                                        <label class="kt-profile-switch" for="kt_notify_1h">
                                            <input
                                                id="kt_notify_1h"
                                                type="checkbox"
                                                name="notify_1h"
                                                value="1"
                                                {{ old('notify_1h', $user->notify_1h) ? 'checked' : '' }}
                                            >
                                            <span class="kt-profile-switch__slider" aria-hidden="true"></span>
                                            <span class="kt-profile-switch__text">1-hour reminder</span>
                                        </label>
                                    </div>

                                    <p class="kt-form-help">
                                        Reminder delivery depends on scheduler availability on the server.
                                    </p>
                                </div>

                                <button type="submit" class="kt-btn-primary kt-profile-submit"><span>Save Changes</span></button>
                            </form>
                        </article>

                        <article class="kt-profile-form-card">
                            <h2>{{ $hasLocalPassword ? 'Change Password' : 'Set Password' }}</h2>
                            <p>
                                {{ $hasLocalPassword ? 'Keep your account secure by updating your password regularly.' : 'Set a password so you can also sign in with email and password.' }}
                            </p>

                            @if(!$hasLocalPassword)
                                <div class="kt-form-note">
                                    You can keep using Google sign in. Setting a password just adds another login option.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('user.profile.password') }}">
                                @csrf
                                @method('PUT')

                                <div class="kt-form-row kt-profile-password-grid {{ $hasLocalPassword ? 'kt-profile-password-grid--three' : '' }}">
                                    @if($hasLocalPassword)
                                        <div class="kt-form-group">
                                            <label class="kt-form-label" for="kt_current_password">Current Password</label>
                                            <input
                                                id="kt_current_password"
                                                type="password"
                                                name="current_password"
                                                class="kt-form-input @error('current_password') kt-input--error @enderror"
                                                required
                                            >
                                            @error('current_password')<span class="kt-form-error">{{ $message }}</span>@enderror
                                        </div>
                                    @endif

                                    <div class="kt-form-group">
                                        <label class="kt-form-label" for="kt_new_password">New Password</label>
                                        <input
                                            id="kt_new_password"
                                            type="password"
                                            name="password"
                                            class="kt-form-input @error('password') kt-input--error @enderror"
                                            required
                                        >
                                        @error('password')<span class="kt-form-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="kt-form-group">
                                        <label class="kt-form-label" for="kt_password_confirmation">Confirm New Password</label>
                                        <input
                                            id="kt_password_confirmation"
                                            type="password"
                                            name="password_confirmation"
                                            class="kt-form-input @error('password_confirmation') kt-input--error @enderror"
                                            required
                                        >
                                        @error('password_confirmation')<span class="kt-form-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <button type="submit" class="kt-btn-primary kt-profile-submit">
                                    <span>{{ $hasLocalPassword ? 'Update Password' : 'Set Password' }}</span>
                                </button>
                            </form>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var root = document.getElementById('ktProfileTabs');
    if (!root) {
        return;
    }

    var buttons = root.querySelectorAll('[data-tab-btn]');
    var panels = root.querySelectorAll('[data-tab-panel]');

    function setActiveTab(tabName) {
        buttons.forEach(function (button) {
            var active = button.getAttribute('data-tab-btn') === tabName;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            var active = panel.getAttribute('data-tab-panel') === tabName;
            panel.classList.toggle('is-active', active);
            if (active) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', 'hidden');
            }
        });

        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            if (tabName !== 'history') {
                url.searchParams.delete('page');
            }
            window.history.replaceState({}, '', url.toString());
        }
    }

    var defaultTab = root.getAttribute('data-default-tab') || 'upcoming';
    setActiveTab(defaultTab);

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var tab = button.getAttribute('data-tab-btn');
            setActiveTab(tab);
        });
    });
})();
</script>
@endpush
